<?php

class Reza_Ervani_Home_Topics extends WP_Widget {
    const ID_BASE = 'reza_home_topics';
    const HOME_AREA = 'home-palestina-topics';
    const MIGRATION_OPTION = 'reza_ervani_migrate_palestina_topics_1_2_53';
    const DEFAULT_COUNT = 4;
    const MAX_COUNT = 30;

    public function __construct() {
        parent::__construct(self::ID_BASE, __('Rubrik Beranda Palestina', 'mh-healthmag'), array(
            'classname' => 'reza-home-topics-widget',
            'description' => __('Atur jumlah artikel di kolom Ensiklopedia al Quds, Tokoh, dan Jurnal.', 'mh-healthmag'),
            'customize_selective_refresh' => true,
        ));
    }

    public static function topics() {
        return array(
            'ensiklopedi-al-quds' => array(__('Ensiklopedia al Quds', 'mh-healthmag'), 'feature'),
            'tokoh' => array(__('Tokoh', 'mh-healthmag'), 'grid'),
            'jurnal' => array(__('Jurnal', 'mh-healthmag'), 'list'),
        );
    }

    private function counts($instance) {
        $counts = array();
        foreach (array_keys(self::topics()) as $slug) {
            $value = $instance[$slug] ?? null;
            $counts[$slug] = is_numeric($value) ? max(1, min(self::MAX_COUNT, (int) $value)) : self::DEFAULT_COUNT;
        }
        return $counts;
    }

    public function update($new_instance, $old_instance) {
        return $this->counts($new_instance + $old_instance);
    }

    public function form($instance) {
        $counts = $this->counts($instance);
        foreach (self::topics() as $slug => list($title)) :
            /* translators: %s: category title. */
            $label = sprintf(__('Jumlah artikel — %s', 'mh-healthmag'), $title);
            ?>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id($slug)); ?>"><?php echo esc_html($label); ?></label>
                <input class="widefat" type="number" min="1" max="<?php echo esc_attr(self::MAX_COUNT); ?>" step="1"
                    id="<?php echo esc_attr($this->get_field_id($slug)); ?>" name="<?php echo esc_attr($this->get_field_name($slug)); ?>"
                    value="<?php echo esc_attr($counts[$slug]); ?>">
            </p>
        <?php endforeach; ?>
        <p class="description"><?php esc_html_e('Jika rubrik memakai sorotan, artikel sorotan termasuk dalam jumlah di atas.', 'mh-healthmag'); ?></p>
        <?php
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        get_template_part('template-parts/home', 'topics', array(
            'counts' => $this->counts($instance),
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
        $defaults = array_fill_keys(array_keys(Reza_Ervani_Home_Topics::topics()), Reza_Ervani_Home_Topics::DEFAULT_COUNT);
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
