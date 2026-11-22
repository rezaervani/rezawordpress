<?php

class Reza_Ervani_Latest_Posts extends WP_Widget {
    public function __construct() {
        parent::__construct('reza_latest_posts', __('Posting Terakhir – Reza Ervani Institute', 'mh-healthmag'), array(
            'description' => __('Daftar artikel terbaru dalam tiga kolom dengan judul dan tanggal terbit.', 'mh-healthmag'),
            'customize_selective_refresh' => true,
        ));
    }

    private function settings($instance) {
        return array(
            'title' => sanitize_text_field($instance['title'] ?? __('Posting Terakhir', 'mh-healthmag')),
            'posts_per_column' => max(1, min(50, (int) ($instance['posts_per_column'] ?? 20))),
        );
    }

    public function update($new_instance, $old_instance) {
        return $this->settings($new_instance);
    }

    public function form($instance) {
        $settings = $this->settings($instance);
        $title_id = $this->get_field_id('title');
        $count_id = $this->get_field_id('posts_per_column');
        ?>
        <p>
            <label for="<?php echo esc_attr($title_id); ?>"><?php esc_html_e('Judul', 'mh-healthmag'); ?></label>
            <input class="widefat" type="text" id="<?php echo esc_attr($title_id); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>" value="<?php echo esc_attr($settings['title']); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($count_id); ?>"><?php esc_html_e('Jumlah artikel per kolom', 'mh-healthmag'); ?></label>
            <input class="widefat" type="number" min="1" max="50" required id="<?php echo esc_attr($count_id); ?>"
                name="<?php echo esc_attr($this->get_field_name('posts_per_column')); ?>" value="<?php echo esc_attr($settings['posts_per_column']); ?>">
        </p>
        <?php
    }

    public function widget($args, $instance) {
        $settings = $this->settings($instance);
        $per_column = $settings['posts_per_column'];
        $stories = reza_ervani_editorial_posts(array(
            'numberposts' => 3 * $per_column,
            'orderby' => array('date' => 'DESC', 'ID' => 'DESC'),
        ));
        if (!$stories) {
            return;
        }

        $title = apply_filters('widget_title', $settings['title'], $instance, $this->id_base);
        echo $args['before_widget'];
        if ($title !== '') {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }
        get_template_part('template-parts/latest', 'posts', array(
            'columns' => array_chunk($stories, $per_column),
            'posts_per_column' => $per_column,
        ));
        echo $args['after_widget'];
    }
}

function reza_ervani_register_latest_posts_widget() {
    register_widget(Reza_Ervani_Latest_Posts::class);
}
add_action('widgets_init', 'reza_ervani_register_latest_posts_widget');

function reza_ervani_migrate_latest_posts_widget() {
    $migration = 'reza_ervani_latest_posts_widget_1_2_18';
    if (get_option($migration) || is_customize_preview()) {
        return;
    }

    $sidebars = (array) get_option('sidebars_widgets', array());
    $legacy = (array) get_option('widget_recent-posts', array());
    $destination = 'home-latest-posts';
    $legacy_id = '';
    foreach ($sidebars['home-6'] ?? array() as $id) {
        if (preg_match('/^recent-posts-(\d+)$/D', $id, $match)
            && strcasecmp(trim(wp_strip_all_tags($legacy[$match[1]]['title'] ?? '')), 'Posting Terakhir') === 0) {
            $legacy_id = $id;
            break;
        }
    }

    if ($legacy_id === '') {
        update_option($migration, 1, false);
        return;
    }

    $placements = (array) ($sidebars[$destination] ?? array());
    if (!preg_grep('/^reza_latest_posts-\d+$/D', $placements)) {
        $instances = (array) get_option('widget_reza_latest_posts', array());
        $number = 2;
        while (isset($instances[$number])) {
            $number++;
        }

        $instances[$number] = array('title' => __('Posting Terakhir', 'mh-healthmag'), 'posts_per_column' => 20);
        $instances['_multiwidget'] = 1;
        if (!update_option('widget_reza_latest_posts', $instances) && get_option('widget_reza_latest_posts') !== $instances) {
            return;
        }
        $sidebars[$destination][] = 'reza_latest_posts-' . $number;
    }

    $sidebars['home-6'] = array_values(array_diff($sidebars['home-6'], array($legacy_id)));
    $sidebars['wp_inactive_widgets'] = array_values(array_unique(array_merge($sidebars['wp_inactive_widgets'] ?? array(), array($legacy_id))));
    wp_set_sidebars_widgets($sidebars);
    $sidebars['array_version'] = $sidebars['array_version'] ?? 3;
    if (get_option('sidebars_widgets') === $sidebars) {
        update_option($migration, 1, false);
    }
}
// Saved widget instances are registered by WP_Widget_Factory at priority 100.
add_action('widgets_init', 'reza_ervani_migrate_latest_posts_widget', 95);
