<?php

class Reza_Ervani_Home_Topics extends WP_Widget {
    const ID_BASE = 'reza_home_topics';
    const HOME_AREA = 'home-duniaislam-topics';
    const MIGRATION_OPTION = 'reza_ervani_duniaislam_topics_initialized';
    const DEFAULT_COUNT = 4;
    const MAX_COUNT = 30;

    public function __construct() {
        parent::__construct(self::ID_BASE, __('Rubrik Beranda Dunia Islam', 'mh-healthmag'), array(
            'classname' => 'reza-home-topics-widget',
            'description' => __('Pilih kategori dan jumlah artikel untuk tiap kolom rubrik.', 'mh-healthmag'),
            'customize_selective_refresh' => true,
        ));
    }

    public static function topics() {
        return array(
            'suriah' => 'feature',
            'sudan' => 'grid',
            'turki' => 'list',
            'qatar' => 'list',
            'afghanistan' => 'feature',
            'saudi-arabia' => 'grid',
        );
    }

    public static function settings($instance = array()) {
        $settings = array();
        foreach (array_keys(self::topics()) as $slug) {
            $category = $instance[$slug]['category'] ?? $slug;
            $count = $instance[$slug]['count'] ?? self::DEFAULT_COUNT;
            $settings[$slug] = array(
                'category' => is_string($category) ? sanitize_title($category) : $slug,
                'count' => is_numeric($count) ? max(1, min(self::MAX_COUNT, (int) $count)) : self::DEFAULT_COUNT,
            );
        }
        return $settings;
    }

    public function update($new_instance, $old_instance) {
        return self::settings($new_instance + $old_instance);
    }

    public function form($instance) {
        $column = 0;
        foreach (self::settings($instance) as $slug => $settings) :
            $column++;
            $category_field = $slug . '[category]';
            $count_field = $slug . '[count]';
            ?>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id($category_field)); ?>">
                    <?php echo esc_html(sprintf(__('Kategori kolom %d', 'mh-healthmag'), $column)); ?>
                </label>
                <?php wp_dropdown_categories(array(
                    'name' => $this->get_field_name($category_field),
                    'id' => $this->get_field_id($category_field),
                    'class' => 'widefat',
                    'value_field' => 'slug',
                    'selected' => $settings['category'],
                    'show_option_none' => __('Sembunyikan kolom', 'mh-healthmag'),
                    'option_none_value' => '',
                    'hide_empty' => false,
                    'hierarchical' => true,
                )); ?>
                <label for="<?php echo esc_attr($this->get_field_id($count_field)); ?>"><?php esc_html_e('Jumlah artikel', 'mh-healthmag'); ?></label>
                <input class="widefat" type="number" min="1" max="<?php echo esc_attr(self::MAX_COUNT); ?>" step="1"
                    id="<?php echo esc_attr($this->get_field_id($count_field)); ?>" name="<?php echo esc_attr($this->get_field_name($count_field)); ?>"
                    value="<?php echo esc_attr($settings['count']); ?>">
            </p>
        <?php endforeach; ?>
        <p class="description"><?php esc_html_e('Jika rubrik memakai sorotan, artikel sorotan termasuk dalam jumlah di atas.', 'mh-healthmag'); ?></p>
        <?php
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        get_template_part('template-parts/home', 'topics', array(
            'settings' => self::settings($instance),
            'widget_id' => $args['widget_id'] ?? wp_unique_id('reza-home-topics-'),
        ));
        echo $args['after_widget'];
    }
}

function reza_ervani_register_home_topics_widget() {
    register_widget(Reza_Ervani_Home_Topics::class);
}
add_action('widgets_init', 'reza_ervani_register_home_topics_widget');

function reza_ervani_migrate_home_topics_widget() {
    $migration = Reza_Ervani_Home_Topics::MIGRATION_OPTION;
    if (get_option($migration) || is_customize_preview()) {
        return;
    }

    $area = Reza_Ervani_Home_Topics::HOME_AREA;
    $sidebars = (array) get_option('sidebars_widgets', array());
    if (empty($sidebars[$area])) {
        $base = Reza_Ervani_Home_Topics::ID_BASE;
        $option = 'widget_' . $base;
        $instances = (array) get_option($option, array());
        $defaults = Reza_Ervani_Home_Topics::settings();
        // Reuse the saved defaults if their sidebar assignment did not finish.
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
        if (empty($sidebars[$area])) {
            $sidebars[$area] = array($base . '-' . $number);
        }
        $sidebars['array_version'] = $sidebars['array_version'] ?? 3;
        wp_set_sidebars_widgets($sidebars);
        if (get_option('sidebars_widgets') !== $sidebars) {
            return;
        }
    }

    update_option($migration, 1, false);
}
add_action('widgets_init', 'reza_ervani_migrate_home_topics_widget', 95);
