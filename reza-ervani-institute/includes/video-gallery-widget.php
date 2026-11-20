<?php

class Reza_Ervani_Video_Gallery extends WP_Widget {
    public function __construct() {
        parent::__construct('reza_video_gallery', __('Galeri Video – Reza Ervani Institute', 'mh-healthmag'), array(
            'description' => __('Pilih video untuk ditampilkan di beranda.', 'mh-healthmag'),
        ));
    }

    private static function sort_labels() {
        return array(
            'date' => __('Tanggal terbit', 'mh-healthmag'),
            'title' => __('Judul', 'mh-healthmag'),
            'views' => __('Jumlah tayangan', 'mh-healthmag'),
            'likes' => __('Jumlah suka', 'mh-healthmag'),
            'dislikes' => __('Jumlah tidak suka', 'mh-healthmag'),
            'rand' => __('Acak', 'mh-healthmag'),
            'menu_order' => __('Urutan manual', 'mh-healthmag'),
        );
    }

    public static function settings($instance) {
        $instance = (array) $instance;
        $sort = sanitize_key($instance['orderby'] ?? 'date');

        return array(
            'title' => sanitize_text_field($instance['title'] ?? __('Video Gallery', 'mh-healthmag')),
            'limit' => max(1, min(5, (int) ($instance['limit'] ?? 5))),
            'category' => wp_parse_id_list($instance['category'] ?? array()),
            'tag' => wp_parse_id_list($instance['tag'] ?? array()),
            'orderby' => isset(self::sort_labels()[$sort]) ? $sort : 'date',
            'order' => sanitize_key($instance['order'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC',
            'featured' => !empty($instance['featured']),
            'show_duration' => !empty($instance['show_duration'] ?? true),
            'show_views' => !empty($instance['show_views'] ?? true),
            'show_category' => !empty($instance['show_category'] ?? true),
        );
    }

    public function update($new_instance, $old_instance) {
        return self::settings($new_instance + array_fill_keys(array('show_duration', 'show_views', 'show_category'), false));
    }

    public function form($instance) {
        $instance = self::settings($instance);
        $text_fields = array('title' => __('Judul', 'mh-healthmag'), 'limit' => __('Jumlah video', 'mh-healthmag'));
        foreach ($text_fields as $key => $label) {
            $id = $this->get_field_id($key);
            ?>
            <p>
                <label for="<?php echo esc_attr($id); ?>"><?php echo esc_html($label); ?></label>
                <input class="widefat" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($this->get_field_name($key)); ?>"
                    value="<?php echo esc_attr($instance[$key]); ?>" <?php echo $key === 'limit' ? 'type="number" min="1" max="5"' : 'type="text"'; ?>>
            </p>
            <?php
        }

        $term_fields = array(
            'category' => array('aiovg_categories', __('Kategori video', 'mh-healthmag')),
            'tag' => array('aiovg_tags', __('Tag video', 'mh-healthmag')),
        );
        foreach ($term_fields as $key => list($taxonomy, $label)) {
            $terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false));
            if (is_wp_error($terms) || !$terms) {
                continue;
            }
            ?>
            <fieldset>
                <legend><?php echo esc_html($label); ?></legend>
                <div class="categorychecklist" style="max-height: 9em; overflow: auto;">
                    <?php foreach ($terms as $term) : ?>
                        <label style="display: block;">
                            <input type="checkbox" name="<?php echo esc_attr($this->get_field_name($key)); ?>[]"
                                value="<?php echo esc_attr($term->term_id); ?>" <?php checked(in_array((int) $term->term_id, $instance[$key], true)); ?>>
                            <?php echo esc_html($term->name); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <?php
        }

        $order_fields = array(
            'orderby' => array(__('Urutkan berdasarkan', 'mh-healthmag'), self::sort_labels()),
            'order' => array(__('Arah urutan', 'mh-healthmag'), array('DESC' => __('Menurun', 'mh-healthmag'), 'ASC' => __('Menaik', 'mh-healthmag'))),
        );
        foreach ($order_fields as $key => list($label, $choices)) {
            $id = $this->get_field_id($key);
            ?>
            <p>
                <label for="<?php echo esc_attr($id); ?>"><?php echo esc_html($label); ?></label>
                <select class="widefat" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($this->get_field_name($key)); ?>">
                    <?php foreach ($choices as $value => $choice) : ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($instance[$key], $value); ?>><?php echo esc_html($choice); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <?php
        }

        $toggles = array(
            'featured' => __('Hanya video unggulan dari galeri lama', 'mh-healthmag'),
            'show_duration' => __('Tampilkan durasi jika tersedia', 'mh-healthmag'),
            'show_views' => __('Tampilkan jumlah tayangan tersimpan', 'mh-healthmag'),
            'show_category' => __('Tampilkan kategori', 'mh-healthmag'),
        );
        foreach ($toggles as $key => $label) {
            ?>
            <p><label>
                <input type="checkbox" name="<?php echo esc_attr($this->get_field_name($key)); ?>"
                    value="1" <?php checked($instance[$key]); ?>>
                <?php echo esc_html($label); ?>
            </label></p>
            <?php
        }
    }

    private function videos($settings) {
        $query = array(
            'post_type' => 'aiovg_videos',
            'post_status' => 'publish',
            'has_password' => false,
            'suppress_filters' => false,
            'numberposts' => $settings['limit'],
            'orderby' => $settings['orderby'],
            'order' => $settings['order'],
        );
        foreach (array('category' => 'aiovg_categories', 'tag' => 'aiovg_tags') as $key => $taxonomy) {
            if ($settings[$key]) {
                $query['tax_query'][] = array(
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'terms' => $settings[$key],
                    'include_children' => false,
                );
            }
        }
        if ($settings['featured']) {
            $query['meta_query'][] = array('key' => 'featured', 'value' => 1);
        }
        if (in_array($settings['orderby'], array('views', 'likes', 'dislikes'), true)) {
            // Include videos without a counter, which have zero recorded interactions.
            $query['meta_query'][] = array(
                'relation' => 'OR',
                'counter' => array('key' => $settings['orderby'], 'compare' => 'EXISTS', 'type' => 'NUMERIC'),
                array('key' => $settings['orderby'], 'compare' => 'NOT EXISTS'),
            );
            $query['orderby'] = array('counter' => $settings['order'], 'date' => 'DESC');
        }

        return get_posts($query);
    }

    public function widget($args, $instance) {
        $settings = self::settings($instance);
        $videos = $this->videos($settings);
        if (!$videos) {
            return;
        }

        $title = apply_filters('widget_title', $settings['title'], $instance, $this->id_base);
        echo $args['before_widget'];
        if ($title !== '') {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }
        get_template_part('template-parts/video', 'gallery', array('videos' => $videos, 'settings' => $settings));
        echo $args['after_widget'];
    }
}

function reza_ervani_register_video_gallery() {
    register_widget(Reza_Ervani_Video_Gallery::class);
}
add_action('widgets_init', 'reza_ervani_register_video_gallery');

function reza_ervani_migrate_video_gallery() {
    if (is_customize_preview()) {
        return;
    }

    $sidebars = (array) get_option('sidebars_widgets', array());
    $area = 'home-video-gallery';
    $gallery = (array) ($sidebars[$area] ?? array());
    if (!$gallery && !get_option('reza_ervani_editorial_widgets_1_2_12')
        && in_array('aiovg-widget-videos-2', $sidebars['home-2'] ?? array(), true)) {
        $gallery[] = 'aiovg-widget-videos-2';
        $sidebars['home-2'] = array_values(array_diff($sidebars['home-2'], $gallery));
    }

    $legacy = (array) get_option('widget_aiovg-widget-videos', array());
    $native = (array) get_option('widget_reza_video_gallery', array());
    $original = $gallery;
    foreach ($gallery as $position => $widget_id) {
        if (!preg_match('/^aiovg-widget-videos-(\d+)$/D', $widget_id, $match) || !isset($legacy[$match[1]])) {
            continue;
        }

        $legacy_id = (int) $match[1];
        $imported = array_filter($native, static function ($instance) use ($legacy_id) {
            return ($instance['legacy_widget'] ?? null) === $legacy_id;
        });
        $number = array_key_first($imported);
        if ($number === null) {
            $number = max(1, $legacy_id);
            while (isset($native[$number])) {
                $number++;
            }

            $settings = Reza_Ervani_Video_Gallery::settings(array('limit' => 5) + (array) $legacy[$legacy_id]);
            $native[$number] = $settings + array('legacy_widget' => $legacy_id);
        }
        $gallery[$position] = 'reza_video_gallery-' . $number;
    }

    if ($gallery === $original) {
        return;
    }

    $native['_multiwidget'] = 1;
    if (!update_option('widget_reza_video_gallery', $native) && get_option('widget_reza_video_gallery') !== $native) {
        return;
    }

    $sidebars[$area] = $gallery;
    wp_set_sidebars_widgets($sidebars);
}
// Migrate before WordPress registers saved widget instances in WP_Widget_Factory at priority 100.
add_action('widgets_init', 'reza_ervani_migrate_video_gallery', 95);
