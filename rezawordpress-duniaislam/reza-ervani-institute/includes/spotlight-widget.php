<?php

class Reza_Ervani_Spotlight extends WP_Widget {
    const ID_BASE = 'reza_spotlight';
    const HOME_AREA = 'home-spotlight';
    const MAX_COUNT = 50;
    const DEFAULT_COUNTS = array('left_count' => 1, 'photo_count' => 1, 'latest_count' => 8);

    public function __construct() {
        parent::__construct(self::ID_BASE, __('Sorotan Utama', 'mh-healthmag'), array(
            'classname' => 'reza-spotlight-widget',
            'description' => __('Headline, foto utama, dan daftar artikel dalam tiga kolom di beranda.', 'mh-healthmag'),
            'customize_selective_refresh' => true,
        ));
    }

    private function counts($instance) {
        $counts = array();
        foreach (self::DEFAULT_COUNTS as $field => $default) {
            $value = $instance[$field] ?? null;
            $counts[$field] = is_numeric($value) ? (int) max(0, min(self::MAX_COUNT, $value)) : $default;
        }
        return $counts;
    }

    public function update($new_instance, $old_instance) {
        return $this->counts($new_instance + $old_instance);
    }

    public function form($instance) {
        $counts = $this->counts($instance);
        foreach (array(
            'left_count' => __('Jumlah judul di bawah headline', 'mh-healthmag'),
            'photo_count' => __('Jumlah judul di bawah foto utama', 'mh-healthmag'),
            'latest_count' => __('Jumlah judul di kolom Terbaru', 'mh-healthmag'),
        ) as $field => $label) :
            ?>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id($field)); ?>"><?php echo esc_html($label); ?></label>
                <input class="widefat" type="number" min="0" max="<?php echo esc_attr(self::MAX_COUNT); ?>" step="1"
                    id="<?php echo esc_attr($this->get_field_id($field)); ?>" name="<?php echo esc_attr($this->get_field_name($field)); ?>"
                    value="<?php echo esc_attr($counts[$field]); ?>">
            </p>
        <?php endforeach; ?>
        <p class="description"><?php esc_html_e('Isi 0 untuk menyembunyikan daftar judul di kolom tersebut.', 'mh-healthmag'); ?></p>
        <?php
    }

    public static function featured_post() {
        $id = absint(get_theme_mod('reza_ervani_editorial_featured', 0));
        $post = $id ? get_post($id) : null;
        if ($post && $post->post_type === 'post' && $post->post_status === 'publish' && $post->post_password === '') {
            return $post;
        }

        $posts = reza_ervani_editorial_posts(array('numberposts' => 1));
        return $posts[0] ?? null;
    }

    public static function has_home_widget() {
        $sidebars = wp_get_sidebars_widgets();
        return (bool) preg_grep('/^' . self::ID_BASE . '-\d+$/D', $sidebars[self::HOME_AREA] ?? array());
    }

    public function widget($args, $instance) {
        $featured = self::featured_post();
        if (!$featured) {
            return;
        }

        $counts = $this->counts($instance);
        $total = array_sum($counts);
        $feed = $total ? reza_ervani_editorial_posts(array(
            'numberposts' => $total,
            'post__not_in' => array($featured->ID),
        )) : array();
        $widget_id = !empty($args['widget_id']) ? $args['widget_id'] : wp_unique_id('reza-spotlight-');

        echo $args['before_widget'];
        get_template_part('template-parts/editorial', 'spotlight', array(
            'featured' => $featured,
            'latest' => array_slice($feed, 0, $counts['latest_count']),
            'left' => array_slice($feed, $counts['latest_count'], $counts['left_count']),
            'photo' => array_slice($feed, $counts['latest_count'] + $counts['left_count'], $counts['photo_count']),
            'widget_id' => $widget_id,
        ));
        echo $args['after_widget'];
    }
}

function reza_ervani_register_spotlight_widget() {
    register_widget(Reza_Ervani_Spotlight::class);
}
add_action('widgets_init', 'reza_ervani_register_spotlight_widget');

function reza_ervani_migrate_spotlight_widget() {
    $migration = 'reza_ervani_spotlight_widget_1_2_37';
    if (get_option($migration) || is_customize_preview()) {
        return;
    }

    if (get_theme_mod('reza_ervani_editorial_home', true) && !Reza_Ervani_Spotlight::has_home_widget()) {
        $base = Reza_Ervani_Spotlight::ID_BASE;
        $option = 'widget_' . $base;
        $instances = (array) get_option($option, array());
        $defaults = Reza_Ervani_Spotlight::DEFAULT_COUNTS;
        // Reuse the existing instance if the previous assignment did not finish.
        $number = array_search($defaults, $instances, true);
        if ($number === false) {
            $number = 2;
            while (isset($instances[$number])) {
                $number++;
            }
        }
        $instances[$number] = $defaults;
        $instances['_multiwidget'] = 1;
        if (!update_option($option, $instances) && get_option($option) !== $instances) {
            return;
        }

        $sidebars = (array) get_option('sidebars_widgets', array());
        $sidebars[Reza_Ervani_Spotlight::HOME_AREA][] = $base . '-' . $number;
        $sidebars['array_version'] = $sidebars['array_version'] ?? 3;
        wp_set_sidebars_widgets($sidebars);
        if (get_option('sidebars_widgets') !== $sidebars) {
            return;
        }
    }

    update_option($migration, 1, false);
}
add_action('widgets_init', 'reza_ervani_migrate_spotlight_widget', 95);
