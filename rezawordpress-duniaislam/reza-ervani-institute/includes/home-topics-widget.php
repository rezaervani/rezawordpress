<?php

class Reza_Ervani_Home_Topics extends WP_Widget {
    const ID_BASE = 'reza_home_topics';
    const HOME_AREA = 'home-duniaislam-topics';
    const MIGRATION_OPTION = 'reza_ervani_duniaislam_topics_initialized';
    const PRIORITY_COUNT = 4;
    const COMPACT_COUNT = 2;
    const MAX_COUNT = 30;

    private const PRIORITY_COUNTRIES = array('suriah', 'sudan', 'turki', 'qatar', 'afghanistan', 'saudi-arabia');
    private const OTHER_CATEGORIES = array(
        'afrika', 'analisa', 'arsip', 'asia', 'asia-selatan', 'asia-tengah', 'asia-tenggara', 'ekonomi',
        'ekonomi-islam', 'eropa-dan-kaukasus', 'liga-arab', 'majalah-mujtama-kuwait', 'opini', 'sejarah',
        'tanpa-kategori', 'timteng', 'tokoh', 'uncategorized',
    );

    public function __construct() {
        parent::__construct(self::ID_BASE, __('Rubrik Beranda Dunia Islam', 'mh-healthmag'), array(
            'classname' => 'reza-home-topics-widget',
            'description' => __('Tampilkan negara dalam rubrik tiga kolom atau satu kolom.', 'mh-healthmag'),
            'customize_selective_refresh' => true,
        ));
    }

    public static function categories() {
        return get_categories(array('hide_empty' => false, 'orderby' => 'name'));
    }

    public static function settings($instance = array()) {
        $hidden = array_fill_keys(self::OTHER_CATEGORIES, 'hidden');
        if (isset($instance['layouts'])) {
            return array(
                'layouts' => $instance['layouts'] + $hidden,
                'counts' => $instance['counts'] ?? array(),
            );
        }

        // Carry across the category and article limit from each old slot.
        $layouts = $hidden;
        $counts = array();
        foreach (self::PRIORITY_COUNTRIES as $slot) {
            $category = $instance[$slot]['category'] ?? $slot;
            if (!$category || isset($layouts[$category])) {
                continue;
            }
            $layouts[$category] = 'priority';
            if (isset($instance[$slot]['count'])) {
                $counts[$category] = self::sanitize_count($instance[$slot]['count']);
            }
        }
        return array('layouts' => $layouts, 'counts' => $counts);
    }

    private static function sanitize_count($count) {
        return max(1, min(self::MAX_COUNT, (int) $count));
    }

    private static function layout_labels() {
        return array(
            'priority' => __('Tiga kolom', 'mh-healthmag'),
            'compact' => __('Satu kolom', 'mh-healthmag'),
            'hidden' => __('Sembunyikan', 'mh-healthmag'),
        );
    }

    public function update($new_instance, $old_instance) {
        $settings = self::settings($old_instance);
        foreach (self::categories() as $category) {
            $slug = $category->slug;
            $layout = $new_instance['layouts'][$slug] ?? '';
            if (is_string($layout) && isset(self::layout_labels()[$layout])) {
                $settings['layouts'][$slug] = $layout;
            }
            if (isset($new_instance['counts'][$slug])) {
                $count = $new_instance['counts'][$slug];
                if (is_numeric($count)) {
                    $settings['counts'][$slug] = self::sanitize_count($count);
                } else {
                    unset($settings['counts'][$slug]);
                }
            }
        }
        return $settings;
    }

    public function form($instance) {
        $settings = self::settings($instance);
        ?>
        <p><?php
            printf(
                esc_html__('Jumlah artikel bawaan %1$d untuk rubrik tiga kolom dan %2$d untuk rubrik satu kolom.', 'mh-healthmag'),
                self::PRIORITY_COUNT,
                self::COMPACT_COUNT
            );
        ?></p>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th scope="col"><?php esc_html_e('Kategori', 'mh-healthmag'); ?></th>
                    <th scope="col"><?php esc_html_e('Tampilan', 'mh-healthmag'); ?></th>
                    <th scope="col"><?php esc_html_e('Artikel', 'mh-healthmag'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (self::categories() as $category) :
                    $slug = $category->slug;
                    $layout = $settings['layouts'][$slug] ?? 'compact';
                    ?>
                    <tr>
                        <th scope="row"><?php echo esc_html($category->name); ?></th>
                        <td>
                            <select name="<?php echo esc_attr($this->get_field_name('layouts[' . $slug . ']')); ?>"
                                aria-label="<?php echo esc_attr(sprintf(__('Tampilan %s', 'mh-healthmag'), $category->name)); ?>">
                                <?php foreach (self::layout_labels() as $value => $label) : ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($layout, $value); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input class="small-text" type="number" min="1" max="<?php echo esc_attr(self::MAX_COUNT); ?>"
                                name="<?php echo esc_attr($this->get_field_name('counts[' . $slug . ']')); ?>"
                                aria-label="<?php echo esc_attr(sprintf(__('Jumlah artikel %s', 'mh-healthmag'), $category->name)); ?>"
                                value="<?php echo esc_attr($settings['counts'][$slug] ?? ''); ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
