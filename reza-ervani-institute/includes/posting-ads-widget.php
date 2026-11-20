<?php

class Reza_Ervani_Posting_Ads extends WP_Widget {
    const ID_BASE = 'reza_posting_ads';
    const HOME_AREA = 'home-posting-ads';
    const DEFAULT_TITLE_COUNT = 3;
    const MAX_TITLE_COUNT = 50;

    public function __construct() {
        parent::__construct(self::ID_BASE, __('Posting dan Iklan', 'mh-healthmag'), array(
            'classname' => 'reza-posting-ads-widget',
            'description' => __('Artikel dari satu kategori di dua kolom kiri dan iklan HTML di kolom kanan.', 'mh-healthmag'),
            'customize_selective_refresh' => true,
        ));
    }

    private function column_title_counts($instance) {
        $counts = array();
        foreach (array('left_title_count', 'right_title_count') as $field) {
            $value = $instance[$field] ?? null;
            $counts[$field] = is_numeric($value)
                ? (int) max(0, min(self::MAX_TITLE_COUNT, $value))
                : self::DEFAULT_TITLE_COUNT;
        }

        return $counts;
    }

    public function update($new_instance, $old_instance) {
        $html = (string) ($new_instance['ad_html'] ?? '');
        $instance = array(
            'category' => absint($new_instance['category'] ?? 0),
            'title' => sanitize_text_field($new_instance['title'] ?? ''),
            'ad_title' => sanitize_text_field($new_instance['ad_title'] ?? ''),
            'ad_html' => current_user_can('unfiltered_html') ? $html : wp_kses_post($html),
        ) + $this->column_title_counts($new_instance + $old_instance);

        if (!empty($old_instance['legacy_source'])) {
            $instance['legacy_source'] = $old_instance['legacy_source'];
        }

        return $instance;
    }

    public function form($instance) {
        $category_id = $this->get_field_id('category');
        $html_id = $this->get_field_id('ad_html');
        $counts = $this->column_title_counts($instance);
        ?>
        <p>
            <label for="<?php echo esc_attr($category_id); ?>"><?php esc_html_e('Kategori posting', 'mh-healthmag'); ?></label>
            <?php wp_dropdown_categories(array(
                'id' => $category_id,
                'name' => $this->get_field_name('category'),
                'class' => 'widefat',
                'show_option_none' => __('Pilih kategori', 'mh-healthmag'),
                'option_none_value' => 0,
                'orderby' => 'name',
                'hierarchical' => true,
                'hide_empty' => false,
                'selected' => absint($instance['category'] ?? 0),
            )); ?>
        </p>
        <?php foreach (array(
            'left_title_count' => __('Jumlah judul tambahan di kolom 1', 'mh-healthmag'),
            'right_title_count' => __('Jumlah judul tambahan di kolom 2', 'mh-healthmag'),
        ) as $field => $label) : ?>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id($field)); ?>"><?php echo esc_html($label); ?></label>
                <input class="widefat" type="number" min="0" max="<?php echo esc_attr(self::MAX_TITLE_COUNT); ?>" step="1"
                    id="<?php echo esc_attr($this->get_field_id($field)); ?>" name="<?php echo esc_attr($this->get_field_name($field)); ?>"
                    value="<?php echo esc_attr($counts[$field]); ?>">
            </p>
        <?php endforeach; ?>
        <p class="description"><?php esc_html_e('Isi 0 untuk menyembunyikan daftar judul tambahan di kolom tersebut.', 'mh-healthmag'); ?></p>
        <?php foreach (array(
            'title' => __('Judul bagian (opsional)', 'mh-healthmag'),
            'ad_title' => __('Judul iklan (opsional)', 'mh-healthmag'),
        ) as $field => $label) : ?>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id($field)); ?>"><?php echo esc_html($label); ?></label>
                <input class="widefat" type="text" id="<?php echo esc_attr($this->get_field_id($field)); ?>"
                    name="<?php echo esc_attr($this->get_field_name($field)); ?>" value="<?php echo esc_attr($instance[$field] ?? ''); ?>">
            </p>
        <?php endforeach; ?>
        <p>
            <label for="<?php echo esc_attr($html_id); ?>"><?php esc_html_e('HTML iklan', 'mh-healthmag'); ?></label>
            <textarea class="widefat code" rows="8" id="<?php echo esc_attr($html_id); ?>"
                name="<?php echo esc_attr($this->get_field_name('ad_html')); ?>"><?php echo esc_textarea($instance['ad_html'] ?? ''); ?></textarea>
        </p>
        <?php
    }

    public function widget($args, $instance) {
        $category = get_term(absint($instance['category'] ?? 0), 'category');
        if (!$category || is_wp_error($category)) {
            return;
        }

        $counts = $this->column_title_counts($instance);
        $stories = reza_ervani_editorial_posts(array(
            'cat' => $category->term_id,
            'numberposts' => 1 + array_sum($counts),
        ));
        if (!$stories) {
            return;
        }

        $title = $instance['title'] ?? '';
        $title = apply_filters('widget_title', $title !== '' ? $title : $category->name, $instance, $this->id_base);
        $title_prefix = 'reza-editorial-collection-';
        echo $args['before_widget'];
        get_template_part('template-parts/editorial', 'collection', array(
            'title_id' => empty($args['widget_id']) ? wp_unique_id($title_prefix) : $title_prefix . $args['widget_id'],
            'title' => $title,
            'category_id' => $category->term_id,
            'stories' => $stories,
            'ad_title' => $instance['ad_title'] ?? '',
            'ad_html' => trim($instance['ad_html'] ?? ''),
        ) + $counts);
        echo $args['after_widget'];
    }
}

function reza_ervani_register_posting_ads_widget() {
    register_widget(Reza_Ervani_Posting_Ads::class);
}
add_action('widgets_init', 'reza_ervani_register_posting_ads_widget');

function reza_ervani_home_posting_ads_categories() {
    $sidebars = wp_get_sidebars_widgets();
    $instances = (array) get_option('widget_' . Reza_Ervani_Posting_Ads::ID_BASE, array());
    $ids = array_merge($sidebars['home-1'] ?? array(), $sidebars[Reza_Ervani_Posting_Ads::HOME_AREA] ?? array());
    $categories = array();
    foreach ($ids as $id) {
        if (preg_match('/^' . Reza_Ervani_Posting_Ads::ID_BASE . '-(\d+)$/D', $id, $matches)) {
            $categories[] = absint($instances[$matches[1]]['category'] ?? 0);
        }
    }

    return array_values(array_unique(array_filter($categories)));
}

function reza_ervani_migrate_posting_ads_widgets() {
    $migration = 'reza_ervani_posting_ads_widgets_1_2_29';
    if (get_option($migration) || is_customize_preview()) {
        return;
    }

    // These categories seed the old rows once. Subsequent rendering uses the saved widgets.
    $legacy = array(
        'home-ad-kunuuz' => array('kunuuz-riyadhus-shaalihin', __('Kunuuz Riyadhus Shalihin', 'mh-healthmag'), 17, 'Aplikasi Android rezaervani.com'),
        'home-ad-jazariyyah' => array('manzhumah-al-jazariyah', __('Jazariyyah', 'mh-healthmag'), 4, 'Donasi'),
        'home-ad-rasm' => array('ilmu-rasm-dan-dhabth', __('Ilmu Rasm dan Dhabth', 'mh-healthmag'), 0, ''),
    );
    $destination = Reza_Ervani_Posting_Ads::HOME_AREA;
    $option = 'widget_' . Reza_Ervani_Posting_Ads::ID_BASE;
    $instances = (array) get_option($option, array());
    $sidebars = (array) get_option('sidebars_widgets', array());
    $original_sidebars = $sidebars;
    $html_widgets = (array) get_option('widget_custom_html', array());
    $older_upgrade_done = (bool) get_option('reza_ervani_editorial_widgets_1_2_12');
    $migrated_ids = array();
    foreach ($instances as $number => $instance) {
        if (!empty($instance['legacy_source'])) {
            $migrated_ids[$instance['legacy_source']] = $number;
        }
    }

    foreach ($legacy as $source => list($slug, $title, $old_number, $old_title)) {
        $widget_ids = $sidebars[$source] ?? array();
        $previous_id = 'custom_html-' . $old_number;
        if (!$widget_ids && !$older_upgrade_done && $old_number
            && in_array($previous_id, $sidebars['home-6'] ?? array(), true)
            && strcasecmp(trim($html_widgets[$old_number]['title'] ?? ''), $old_title) === 0) {
            $widget_ids = array($previous_id);
            $sidebars['home-6'] = array_values(array_diff($sidebars['home-6'], $widget_ids));
        }

        $category = get_category_by_slug($slug);
        if (isset($migrated_ids[$source])) {
            $number = $migrated_ids[$source];
        } elseif ($category) {
            $number = 2;
            while (isset($instances[$number])) {
                $number++;
            }
            $ads = reza_ervani_copy_legacy_posting_ads($widget_ids, $html_widgets);
            $instances[$number] = $ads + array(
                'category' => (int) $category->term_id,
                'title' => $title,
                'legacy_source' => $source,
            );
        } else {
            $number = 0;
        }

        if ($number) {
            $widget_id = Reza_Ervani_Posting_Ads::ID_BASE . '-' . $number;
            if (!in_array($widget_id, $sidebars[$destination] ?? array(), true)) {
                $sidebars[$destination][] = $widget_id;
            }
        }
        if ($widget_ids) {
            $sidebars['wp_inactive_widgets'] = array_values(array_unique(array_merge($sidebars['wp_inactive_widgets'] ?? array(), $widget_ids)));
        }
        unset($sidebars[$source]);
    }

    // Save instances first. The source marker lets a retry reuse the same instances.
    if ($instances) {
        $instances['_multiwidget'] = 1;
        if (!update_option($option, $instances) && get_option($option) !== $instances) {
            return;
        }
    }
    if ($sidebars !== $original_sidebars) {
        $sidebars['array_version'] = $sidebars['array_version'] ?? 3;
        wp_set_sidebars_widgets($sidebars);
        if (get_option('sidebars_widgets') !== $sidebars) {
            return;
        }
    }
    update_option($migration, 1, false);
}
// Run before WordPress registers the saved widget instances.
add_action('widgets_init', 'reza_ervani_migrate_posting_ads_widgets', 95);

function reza_ervani_copy_legacy_posting_ads($widget_ids, $html_widgets) {
    $ads = array();
    foreach ($widget_ids as $id) {
        if (preg_match('/^custom_html-(\d+)$/D', $id, $matches) && isset($html_widgets[$matches[1]]['content'])) {
            $ads[] = $html_widgets[$matches[1]];
        }
    }

    if (count($ads) === 1) {
        return array('ad_title' => $ads[0]['title'] ?? '', 'ad_html' => $ads[0]['content']);
    }

    $html = array();
    foreach ($ads as $ad) {
        $title = $ad['title'] ?? '';
        $html[] = ($title !== '' ? '<h3 class="mh-widget-title">' . esc_html($title) . '</h3>' : '') . $ad['content'];
    }

    return array('ad_title' => '', 'ad_html' => implode("\n\n", $html));
}
