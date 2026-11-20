<?php

class Reza_Ervani_Article_List extends WP_Widget {
    const ID_BASE = 'reza_article_list';
    const MAX_POSTS = 50;

    public function __construct() {
        parent::__construct(self::ID_BASE, __('Daftar Artikel', 'mh-healthmag'), array(
            'classname' => 'reza-article-list-widget',
            'description' => __('Daftar ringkas dengan thumbnail atau artikel utama bergambar serta ringkasan.', 'mh-healthmag'),
            'customize_selective_refresh' => true,
        ));
    }

    public function settings($instance) {
        $tags = array_filter(array_map('sanitize_title', explode(',', (string) ($instance['tags'] ?? ''))));
        return array(
            'title' => sanitize_text_field($instance['title'] ?? ''),
            'category' => absint($instance['category'] ?? 0),
            'layout' => ($instance['layout'] ?? '') === 'featured' ? 'featured' : 'compact',
            'postcount' => max(1, min(self::MAX_POSTS, (int) ($instance['postcount'] ?? 5))),
            'tags' => implode(', ', $tags),
            'offset' => max(0, min(self::MAX_POSTS, (int) ($instance['offset'] ?? 0))),
            'ignore_sticky' => !empty($instance['ignore_sticky']),
        );
    }

    public function update($new_instance, $old_instance) {
        $settings = $this->settings($new_instance);
        if (!empty($old_instance['legacy_widget'])) {
            $settings['legacy_widget'] = $old_instance['legacy_widget'];
        }
        return $settings;
    }

    public function form($instance) {
        $settings = $this->settings($instance + array('ignore_sticky' => true));
        $category_id = $this->get_field_id('category');
        $layout_id = $this->get_field_id('layout');
        $sticky_id = $this->get_field_id('ignore_sticky');
        ?>
        <?php foreach (array('title' => __('Judul', 'mh-healthmag'), 'tags' => __('Slug tag, pisahkan dengan koma', 'mh-healthmag')) as $field => $label) : ?>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id($field)); ?>"><?php echo esc_html($label); ?></label>
                <input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id($field)); ?>"
                    name="<?php echo esc_attr($this->get_field_name($field)); ?>" value="<?php echo esc_attr($settings[$field]); ?>">
            </p>
        <?php endforeach; ?>
        <p>
            <label for="<?php echo esc_attr($category_id); ?>"><?php esc_html_e('Kategori', 'mh-healthmag'); ?></label>
            <?php wp_dropdown_categories(array(
                'id' => $category_id,
                'name' => $this->get_field_name('category'),
                'class' => 'widefat',
                'show_option_all' => __('Semua kategori', 'mh-healthmag'),
                'hide_empty' => false,
                'hierarchical' => true,
                'selected' => $settings['category'],
            )); ?>
        </p>
        <p>
            <label for="<?php echo esc_attr($layout_id); ?>"><?php esc_html_e('Tampilan', 'mh-healthmag'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($layout_id); ?>" name="<?php echo esc_attr($this->get_field_name('layout')); ?>">
                <option value="compact" <?php selected($settings['layout'], 'compact'); ?>><?php esc_html_e('Ringkas', 'mh-healthmag'); ?></option>
                <option value="featured" <?php selected($settings['layout'], 'featured'); ?>><?php esc_html_e('Sorotan', 'mh-healthmag'); ?></option>
            </select>
        </p>
        <?php foreach (array(
            'postcount' => array(__('Jumlah artikel', 'mh-healthmag'), 1),
            'offset' => array(__('Lewati artikel terbaru', 'mh-healthmag'), 0),
        ) as $field => list($label, $min)) : ?>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id($field)); ?>"><?php echo esc_html($label); ?></label>
                <input class="widefat" type="number" min="<?php echo esc_attr($min); ?>" max="<?php echo esc_attr(self::MAX_POSTS); ?>" step="1"
                    id="<?php echo esc_attr($this->get_field_id($field)); ?>" name="<?php echo esc_attr($this->get_field_name($field)); ?>"
                    value="<?php echo esc_attr($settings[$field]); ?>">
            </p>
        <?php endforeach; ?>
        <p>
            <label for="<?php echo esc_attr($sticky_id); ?>">
                <input type="checkbox" id="<?php echo esc_attr($sticky_id); ?>" name="<?php echo esc_attr($this->get_field_name('ignore_sticky')); ?>" value="1" <?php checked($settings['ignore_sticky']); ?>>
                <?php esc_html_e('Abaikan prioritas artikel yang disematkan', 'mh-healthmag'); ?>
            </label>
        </p>
        <?php
    }

    public function widget($args, $instance) {
        $settings = $this->settings($instance);
        $query_args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $settings['postcount'],
            'no_found_rows' => true,
            'offset' => $settings['offset'],
            'ignore_sticky_posts' => $settings['ignore_sticky'],
        );
        if ($settings['category']) {
            $query_args['cat'] = $settings['category'];
        }
        if ($settings['tags'] !== '') {
            $query_args['tag_slug__in'] = explode(', ', $settings['tags']);
        }
        $stories = new WP_Query($query_args);
        if (!$stories->have_posts()) {
            return;
        }

        $title = apply_filters('widget_title', $settings['title'], $instance, $this->id_base);
        $archive = $settings['category'] ? get_category_link($settings['category']) : '';
        echo $args['before_widget'];
        if ($title !== '') {
            echo $args['before_title'];
            if ($archive && !is_wp_error($archive)) {
                echo '<a href="' . esc_url($archive) . '">' . esc_html($title) . '</a>';
            } else {
                echo esc_html($title);
            }
            echo $args['after_title'];
        }
        get_template_part('template-parts/editorial', 'article-list', array(
            'query' => $stories,
            'featured' => $settings['layout'] === 'featured',
        ));
        echo $args['after_widget'];
    }
}

function reza_ervani_register_article_list_widget() {
    // Older installs may still load the parent theme on the first upgrade request.
    remove_action('widgets_init', 'mh_magazine_lite_register_widgets');
    register_widget(Reza_Ervani_Article_List::class);
}
add_action('widgets_init', 'reza_ervani_register_article_list_widget', 9);
