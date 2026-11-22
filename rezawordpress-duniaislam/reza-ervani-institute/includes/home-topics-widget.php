<?php

class Reza_Ervani_Home_Topics extends WP_Widget {
    const ID_BASE = 'reza_home_topics';
    const HOME_AREA = 'home-duniaislam-topics';
    const MIGRATION_OPTION = 'reza_ervani_duniaislam_topics_initialized';
    const PRIORITY_COUNT = 4;
    const COMPACT_COUNT = 2;
    const MAX_COUNT = 30;

    private const PRIORITY_COUNTRIES = array('suriah', 'sudan', 'turki', 'qatar', 'afghanistan', 'saudi-arabia');
    private const REGIONS = array('afrika', 'asia', 'asia-selatan', 'asia-tengah', 'asia-tenggara', 'eropa-dan-kaukasus', 'timteng');
    private const OTHER_CATEGORIES = array(
        'afrika', 'analisa', 'arsip', 'asia', 'asia-selatan', 'asia-tengah', 'asia-tenggara', 'ekonomi',
        'ekonomi-islam', 'eropa-dan-kaukasus', 'liga-arab', 'majalah-mujtama-kuwait', 'opini', 'sejarah',
        'tanpa-kategori', 'timteng', 'tokoh', 'uncategorized',
    );

    public function __construct() {
        parent::__construct(self::ID_BASE, __('Rubrik Beranda Dunia Islam', 'mh-healthmag'), array(
            'classname' => 'reza-home-topics-widget',
            'description' => __('Atur gaya, subkategori, dan jumlah judul tiap bagian rubrik negara.', 'mh-healthmag'),
            'customize_selective_refresh' => true,
        ));
    }

    public static function categories() {
        $categories = get_categories(array('hide_empty' => false, 'orderby' => 'name'));
        $slugs = wp_list_pluck($categories, 'slug', 'term_id');
        $regions = array_keys(array_intersect($slugs, self::REGIONS));

        // A country can sit below a region, but its subcategories belong inside its own rubric.
        return array_filter($categories, static function ($category) use ($regions) {
            return !array_diff(get_ancestors($category->term_id, 'category', 'taxonomy'), $regions);
        });
    }

    public static function subcategory($country, $id) {
        $subcategory = $id ? get_category(absint($id)) : null;
        return $subcategory && !is_wp_error($subcategory) && term_is_ancestor_of($country, $subcategory, 'category')
            ? $subcategory : null;
    }

    public static function settings($instance = array()) {
        $hidden = array_fill_keys(self::OTHER_CATEGORIES, 'hidden');
        if (isset($instance['layouts'])) {
            $sections = $instance['sections'] ?? array();
            if (!isset($instance['sections'])) {
                // Move the old filters into their own sections in the saved order.
                foreach ($instance['subcategories'] ?? array() as $slug => $ids) {
                    foreach (array_slice(wp_parse_id_list($ids), 0, 2) as $index => $id) {
                        $sections[$slug][$index + 2] = array('category' => $id);
                    }
                }
            }
            return array(
                'layouts' => $instance['layouts'] + $hidden,
                'counts' => $instance['counts'] ?? array(),
                'sections' => $sections,
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
        return array('layouts' => $layouts, 'counts' => $counts, 'sections' => array());
    }

    private static function sanitize_count($count) {
        return max(1, min(self::MAX_COUNT, (int) $count));
    }

    private static function layout_labels() {
        return array(
            'priority' => __('Gaya 3', 'mh-healthmag'),
            'double' => __('Gaya 2', 'mh-healthmag'),
            'compact' => __('Gaya 1', 'mh-healthmag'),
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
            foreach (array(2, 3) as $part) {
                $submitted = $new_instance['sections'][$slug][$part] ?? null;
                if (!is_array($submitted)) {
                    continue;
                }
                $section = $settings['sections'][$slug][$part] ?? array();
                if (isset($submitted['category'])) {
                    $term = self::subcategory($category, $submitted['category']);
                    $section['category'] = $term ? $term->term_id : 0;
                }
                if (isset($submitted['count'])) {
                    $count = $submitted['count'];
                    if (is_numeric($count)) {
                        $section['count'] = self::sanitize_count($count);
                    } else {
                        unset($section['count']);
                    }
                }
                $settings['sections'][$slug][$part] = $section;
            }
        }
        return $settings;
    }

    public function form($instance) {
        $settings = self::settings($instance);
        ?>
        <p><?php
            printf(
                esc_html__('Bagian 1 mengambil semua artikel negara. Batas bawaan %1$d judul untuk Gaya 1 dan %2$d judul per bagian untuk Gaya 2 dan 3.', 'mh-healthmag'),
                self::COMPACT_COUNT,
                self::PRIORITY_COUNT
            );
        ?></p>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th scope="col"><?php esc_html_e('Kategori', 'mh-healthmag'); ?></th>
                    <th scope="col"><?php esc_html_e('Gaya', 'mh-healthmag'); ?></th>
                    <th scope="col"><?php esc_html_e('Judul Bagian 1', 'mh-healthmag'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (self::categories() as $category) :
                    $slug = $category->slug;
                    $layout = $settings['layouts'][$slug] ?? 'compact';
                    ?>
                    <tr class="reza-country-widget-option">
                        <th scope="row"><?php echo esc_html($category->name); ?></th>
                        <td>
                            <select name="<?php echo esc_attr($this->get_field_name('layouts[' . $slug . ']')); ?>"
                                aria-label="<?php echo esc_attr(sprintf(__('Gaya %s', 'mh-healthmag'), $category->name)); ?>">
                                <?php foreach (self::layout_labels() as $value => $label) : ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($layout, $value); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input class="small-text" type="number" min="1" max="<?php echo esc_attr(self::MAX_COUNT); ?>"
                                name="<?php echo esc_attr($this->get_field_name('counts[' . $slug . ']')); ?>"
                                aria-label="<?php echo esc_attr(sprintf(__('Jumlah judul Bagian 1 %s', 'mh-healthmag'), $category->name)); ?>"
                                value="<?php echo esc_attr($settings['counts'][$slug] ?? ''); ?>">
                        </td>
                    </tr>
                    <?php
                    $children = get_categories(array('child_of' => $category->term_id, 'hide_empty' => false, 'orderby' => 'name'));
                    if (!$children) {
                        continue;
                    }
                    ?>
                    <tr class="reza-country-widget-subcategories">
                        <td colspan="3">
                            <?php foreach (array(2, 3) as $part) :
                                $section = $settings['sections'][$slug][$part] ?? array();
                                $field = 'sections[' . $slug . '][' . $part . ']';
                                ?>
                                <fieldset class="reza-country-widget-part reza-country-widget-part-<?php echo esc_attr($part); ?>">
                                    <legend><?php echo esc_html(sprintf(__('Bagian %1$d %2$s', 'mh-healthmag'), $part, $category->name)); ?></legend>
                                    <label>
                                        <?php esc_html_e('Subkategori', 'mh-healthmag'); ?>
                                        <select name="<?php echo esc_attr($this->get_field_name($field . '[category]')); ?>">
                                            <option value="0"><?php esc_html_e('Pilih subkategori', 'mh-healthmag'); ?></option>
                                            <?php foreach ($children as $child) : ?>
                                                <option value="<?php echo esc_attr($child->term_id); ?>" <?php selected($section['category'] ?? 0, $child->term_id); ?>>
                                                    <?php echo esc_html($child->name); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                    <label>
                                        <?php esc_html_e('Jumlah judul', 'mh-healthmag'); ?>
                                        <input class="small-text" type="number" min="1" max="<?php echo esc_attr(self::MAX_COUNT); ?>"
                                            name="<?php echo esc_attr($this->get_field_name($field . '[count]')); ?>"
                                            value="<?php echo esc_attr($section['count'] ?? ''); ?>">
                                    </label>
                                </fieldset>
                            <?php endforeach; ?>
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

function reza_ervani_enqueue_home_topics_controls() {
    wp_enqueue_style('reza-ervani-home-topics-controls', get_stylesheet_directory_uri() . '/admin/home-topics.css',
        array(), wp_get_theme()->get('Version'));
}
add_action('admin_enqueue_scripts', static function ($hook) {
    if ($hook === 'widgets.php') {
        reza_ervani_enqueue_home_topics_controls();
    }
});
add_action('customize_controls_enqueue_scripts', 'reza_ervani_enqueue_home_topics_controls');

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
