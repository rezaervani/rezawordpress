<?php

function reza_ervani_article_inline_ad_areas() {
    return array(
        'posts-inline-1' => 800,
        'posts-inline-2' => 1500,
    );
}

function reza_ervani_editorial_category_widget_rows() {
    return array(
        'home-editorial-category-widgets' => array(
            'name' => __('Beranda – Tiga Kolom Kategori', 'mh-healthmag'),
            'migration' => 'reza_ervani_category_widgets_1_2_16',
            'titles' => array(
                'manzhumah-syatibiyah' => __("Qiroah Sab'ah", 'mh-healthmag'),
                'ilmu-qiroaat' => __('Ilmu Qiroat', 'mh-healthmag'),
                'hilyatut-tilawah' => __('Hilyatut Tilawah', 'mh-healthmag'),
            ),
        ),
        'home-editorial-category-widgets-2' => array(
            'name' => __('Beranda – Tiga Kolom Kategori (Baris Kedua)', 'mh-healthmag'),
            'migration' => 'reza_ervani_category_widgets_1_2_17',
            'titles' => array(
                'adab' => __('Serial Adab', 'mh-healthmag'),
                'syarh-qashiidah-al-khaaqaaniyyah' => __('Syarh al Khaaqaaniyyah', 'mh-healthmag'),
                'makkiyah-dan-madaniyah' => __('Makkiyyah dan Madaniyyah', 'mh-healthmag'),
            ),
        ),
    );
}

function reza_ervani_register_editorial_sidebars() {
    $widget_markup = array(
        'before_widget' => '<div id="%1$s" class="mh-widget %2$s">',
        'after_widget' => '</div>',
    );

    foreach (array(
        Reza_Ervani_Smart_Ad::TOP_AREA => __('Beranda – Iklan sebelum Sorotan Utama', 'mh-healthmag'),
        Reza_Ervani_Smart_Ad::MIDDLE_AREA => __('Beranda – Iklan Tengah', 'mh-healthmag'),
    ) as $id => $name) {
        register_sidebar(array(
            'id' => $id,
            'name' => $name,
            'description' => __('Tambahkan widget Iklan Cerdas atau HTML Khusus.', 'mh-healthmag'),
            'before_title' => '<h3 class="mh-widget-title">',
            'after_title' => '</h3>',
        ) + $widget_markup);
    }

    $slot_number = 0;
    foreach (reza_ervani_article_inline_ad_areas() as $id => $minimum_words) {
        register_sidebar(array(
            'id' => $id,
            /* translators: %d: inline advertisement position. */
            'name' => sprintf(__('Iklan Tengah Artikel %d', 'mh-healthmag'), ++$slot_number),
            /* translators: %d: minimum article word count. */
            'description' => sprintf(__('Tambahkan satu unit iklan responsif melalui widget HTML Khusus. Slot dapat tampil mulai dari %d kata jika ada jeda paragraf yang cukup.', 'mh-healthmag'), $minimum_words),
            'before_title' => '<h4 class="mh-widget-title"><span class="mh-widget-title-inner">',
            'after_title' => '</span></h4>',
        ) + $widget_markup);
    }

    register_sidebar(array(
        'id' => Reza_Ervani_Spotlight::HOME_AREA,
        'name' => __('Beranda – Sorotan Utama', 'mh-healthmag'),
        'description' => __('Atur widget Sorotan Utama di bagian atas beranda.', 'mh-healthmag'),
        'before_title' => '<h2 class="mh-widget-title">',
        'after_title' => '</h2>',
    ) + $widget_markup);

    foreach (Reza_Ervani_Network_Feed_Widget::home_sections() as $id => $section) {
        register_sidebar(array(
            'id' => $id,
            'name' => $section['name'],
            /* translators: %s: homepage section title. */
            'description' => sprintf(__('Urutkan widget Artikel Jaringan Situs di segmen %s. Pada desktop, widget pertama menempati sorotan selebar dua kolom.', 'mh-healthmag'), $section['title']),
            'before_widget' => '<section id="%1$s" class="%2$s">',
            'after_widget' => '</section>',
            'before_title' => '<h3 class="reza-network-heading">',
            'after_title' => '</h3>',
        ));
    }

    register_sidebar(array(
        'id' => 'home-network-ad',
        'name' => __('Beranda – Iklan setelah Kisi Kategori', 'mh-healthmag'),
        'description' => __('Tambahkan unit iklan melalui widget HTML Khusus untuk tampil di antara Kisi Kategori dan Jaringan Situs.', 'mh-healthmag'),
        'before_title' => '<h3 class="mh-widget-title"><span class="mh-widget-title-inner">',
        'after_title' => '</span></h3>',
    ) + $widget_markup);

    register_sidebar(array(
        'id' => Reza_Ervani_Category_Grid::HOME_AREA,
        'name' => __('Beranda – Kisi Kategori', 'mh-healthmag'),
        'description' => __('Tambahkan dan urutkan widget Kisi Kategori untuk ditampilkan sebelum deretan seri di beranda.', 'mh-healthmag'),
        'before_title' => '<h2 class="mh-widget-title">',
        'after_title' => '</h2>',
    ) + $widget_markup);

    register_sidebar(array(
        'id' => Reza_Ervani_Posting_Ads::HOME_AREA,
        'name' => __('Beranda – Posting dan Iklan', 'mh-healthmag'),
        'description' => __('Tambahkan dan urutkan widget Posting dan Iklan untuk ditampilkan sebelum galeri video.', 'mh-healthmag'),
        'before_title' => '<h3 class="mh-widget-title"><span class="mh-widget-title-inner">',
        'after_title' => '</span></h3>',
    ) + $widget_markup);

    foreach (array(
        Reza_Ervani_Palestina_Topics::HOME_AREA => __('Beranda – Rubrik Palestina', 'mh-healthmag'),
        Reza_Ervani_Duniaislam_Topics::HOME_AREA => __('Beranda – Rubrik Dunia Islam', 'mh-healthmag'),
    ) as $id => $name) {
        register_sidebar(array(
            'id' => $id,
            'name' => $name,
            'description' => __('Tambahkan widget rubrik kategori untuk ditampilkan di beranda.', 'mh-healthmag'),
            'before_title' => '<h2 class="mh-widget-title">',
            'after_title' => '</h2>',
        ) + $widget_markup);
    }

    register_sidebar(array(
        'id' => 'home-video-gallery',
        'name' => __('Video Gallery Beranda', 'mh-healthmag'),
        'description' => __('Area lebar penuh di bawah koleksi untuk widget Galeri Video – Reza Ervani Institute.', 'mh-healthmag'),
        'before_title' => '<h2 class="mh-widget-title"><span class="mh-widget-title-inner">',
        'after_title' => '</span></h2>',
    ) + $widget_markup);

    register_sidebar(array(
        'id' => 'home-latest-posts',
        'name' => __('Beranda – Posting Terakhir', 'mh-healthmag'),
        'description' => __('Segmen penutup beranda sebelum footer.', 'mh-healthmag'),
        'before_widget' => '<section id="%1$s" class="reza-latest-posts %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h2 class="reza-latest-posts-heading">',
        'after_title' => '</h2>',
    ));

    foreach (reza_ervani_editorial_category_widget_rows() as $id => $row) {
        register_sidebar(array(
            'id' => $id,
            'name' => $row['name'],
        'description' => __('Susun widget Daftar Artikel untuk memilih kategori, judul, dan jumlah artikel di deretan bawah galeri video.', 'mh-healthmag'),
            'before_title' => '<h2 class="mh-widget-title">',
            'after_title' => '</h2>',
        ) + $widget_markup);
    }
}
add_action('widgets_init', 'reza_ervani_register_editorial_sidebars');

function reza_ervani_migrate_category_widgets() {
    if (get_option('reza_ervani_retired_mh_widgets_1_2_34') || is_customize_preview()) {
        return;
    }

    foreach (reza_ervani_editorial_category_widget_rows() as $destination => $row) {
        if (!get_option($row['migration'])) {
            reza_ervani_migrate_category_widget_row($destination, $row['migration'], $row['titles']);
        }
    }
}
// Saved widget instances are registered by WP_Widget_Factory at priority 100.
add_action('widgets_init', 'reza_ervani_migrate_category_widgets', 95);

function reza_ervani_migrate_category_widget_row($destination, $migration, $titles) {
    $sidebars = (array) get_option('sidebars_widgets', array());
    if (!empty($sidebars[$destination])) {
        update_option($migration, 1, false);
        return;
    }

    $instances = (array) get_option('widget_mh_custom_posts', array());
    $legacy = array();
    foreach (array('home-3', 'home-4', 'home-2', 'home-5') as $source) {
        foreach ($sidebars[$source] ?? array() as $widget_id) {
            if (preg_match('/^mh_custom_posts-(\d+)$/D', $widget_id, $match) && !empty($instances[$match[1]]['category'])) {
                $category_id = (int) $instances[$match[1]]['category'];
                $legacy[$category_id] = $legacy[$category_id] ?? array($source, (int) $match[1]);
            }
        }
    }

    foreach ($titles as $slug => $title) {
        $category = get_category_by_slug($slug);
        if (!$category) {
            continue;
        }

        $category_id = (int) $category->term_id;
        if (isset($legacy[$category_id])) {
            list($source, $number) = $legacy[$category_id];
            $sidebars[$source] = array_values(array_diff($sidebars[$source], array('mh_custom_posts-' . $number)));
        } else {
            $number = 2;
            while (isset($instances[$number])) {
                $number++;
            }
            $instances[$number] = array('category' => $category_id);
        }

        $instances[$number]['title'] = $title;
        $sidebars[$destination][] = 'mh_custom_posts-' . $number;
    }

    if (empty($sidebars[$destination])) {
        return;
    }

    $instances['_multiwidget'] = 1;
    if (!update_option('widget_mh_custom_posts', $instances) && get_option('widget_mh_custom_posts') !== $instances) {
        return;
    }

    wp_set_sidebars_widgets($sidebars);
    $sidebars['array_version'] = $sidebars['array_version'] ?? 3;
    if (get_option('sidebars_widgets') === $sidebars) {
        update_option($migration, 1, false);
    }
}

function reza_ervani_move_home_topics_to_inactive_widgets() {
    $migration = 'reza_ervani_home_topics_inactive_1_2_17';
    if (get_option($migration) || is_customize_preview()) {
        return;
    }

    $sidebars = (array) get_option('sidebars_widgets', array());
    $widgets = (array) get_option('widget_custom_html', array());
    $widget_id = 'custom_html-12';
    $title = trim(wp_strip_all_tags($widgets[12]['title'] ?? ''));
    if (in_array($widget_id, $sidebars['home-6'] ?? array(), true) && strcasecmp($title, 'Topik Lain di Web rezaervani.com') === 0) {
        $sidebars['home-6'] = array_values(array_diff($sidebars['home-6'], array($widget_id)));
        $sidebars['wp_inactive_widgets'] = array_values(array_unique(array_merge($sidebars['wp_inactive_widgets'] ?? array(), array($widget_id))));
        wp_set_sidebars_widgets($sidebars);
        $sidebars['array_version'] = $sidebars['array_version'] ?? 3;
        if (get_option('sidebars_widgets') !== $sidebars) {
            return;
        }
    }

    update_option($migration, 1, false);
}
add_action('wp_loaded', 'reza_ervani_move_home_topics_to_inactive_widgets');

function reza_ervani_move_legacy_jazariyyah_to_inactive_widgets() {
    $migration = 'reza_ervani_legacy_jazariyyah_inactive_1_2_33';
    if (get_option($migration) || is_customize_preview()) {
        return;
    }

    $sidebars = (array) get_option('sidebars_widgets', array());
    $widget_id = 'mh_custom_posts-4';
    if (in_array($widget_id, $sidebars['home-4'] ?? array(), true)) {
        $widgets = (array) get_option('widget_mh_custom_posts', array());
        $category = get_category_by_slug('manzhumah-al-jazariyah');
        if ($category && (int) ($widgets[4]['category'] ?? 0) === (int) $category->term_id) {
            $sidebars['home-4'] = array_values(array_diff($sidebars['home-4'], array($widget_id)));
            $sidebars['wp_inactive_widgets'] = array_values(array_unique(array_merge($sidebars['wp_inactive_widgets'] ?? array(), array($widget_id))));
            $sidebars['array_version'] = $sidebars['array_version'] ?? 3;
            wp_set_sidebars_widgets($sidebars);
            if (get_option('sidebars_widgets') !== $sidebars) {
                return;
            }
        }
    }

    update_option($migration, 1, false);
}
add_action('widgets_init', 'reza_ervani_move_legacy_jazariyyah_to_inactive_widgets', 95);

function reza_ervani_retired_mh_widget_types() {
    return array(
        'mh_custom_posts' => 'compact',
        'mh_magazine_lite_posts_focus' => 'featured',
        'mh_magazine_lite_posts_large' => '',
        'mh_magazine_lite_posts_stacked' => '',
        'mh_magazine_lite_tabbed' => '',
        'mh_slider_hp' => '',
    );
}

function reza_ervani_retire_mh_widgets() {
    $migration = 'reza_ervani_retired_mh_widgets_1_2_34';
    if (get_option($migration) || is_customize_preview()) {
        return;
    }

    $retired = reza_ervani_retired_mh_widget_types();
    $sidebars = (array) get_option('sidebars_widgets', array());
    $before = $sidebars;
    $base = Reza_Ervani_Article_List::ID_BASE;
    $option = 'widget_' . $base;
    $instances = (array) get_option($option, array());
    $saved = array();
    foreach ($instances as $number => $instance) {
        if (!empty($instance['legacy_widget'])) {
            $saved[$instance['legacy_widget']] = $number;
        }
    }

    $editorial = get_theme_mod('reza_ervani_editorial_home', true);
    $replaced = $editorial ? reza_ervani_retired_editorial_category_ids() : array();
    $legacy_settings = array();
    $backup = array();
    $number = 2;
    $widget = new Reza_Ervani_Article_List();
    foreach ($sidebars as $area => $ids) {
        if (!is_array($ids)) {
            continue;
        }
        foreach ($ids as $index => $id) {
            if (!preg_match('/^([a-z_]+)-(\d+)$/D', $id, $match) || !isset($retired[$match[1]])) {
                continue;
            }
            $old_base = $match[1];
            $backup[$area] = $ids;
            unset($sidebars[$area][$index]);
            if ($area === 'wp_inactive_widgets' || $retired[$old_base] === '') {
                continue;
            }

            $legacy_settings[$old_base] = $legacy_settings[$old_base] ?? (array) get_option('widget_' . $old_base, array());
            $old = $legacy_settings[$old_base][$match[2]] ?? null;
            $editorial_area = $editorial && in_array($area, array('home-1', 'home-2', 'home-3', 'home-4', 'home-5', 'home-6'), true);
            $latest = $old_base === 'mh_magazine_lite_posts_focus' && strcasecmp(trim(wp_strip_all_tags($old['title'] ?? '')), 'Terbaru') === 0;
            $duplicate = $latest || in_array((int) ($old['category'] ?? 0), $replaced, true);
            if (!$old || ($editorial_area && $duplicate)) {
                continue;
            }

            if (!isset($saved[$id])) {
                while (isset($instances[$number])) {
                    $number++;
                }
                $instances[$number] = $widget->settings(array(
                    'layout' => $retired[$old_base],
                    'ignore_sticky' => !empty($old['sticky'] ?? 1),
                ) + $old) + array('legacy_widget' => $id);
                $saved[$id] = $number;
            }
            $sidebars[$area][$index] = $base . '-' . $saved[$id];
        }
        if ($sidebars[$area] !== $ids) {
            ksort($sidebars[$area]);
            $sidebars[$area] = array_values(array_unique($sidebars[$area]));
        }
    }

    if ($backup) {
        $backup_option = 'reza_ervani_retired_mh_widget_placements';
        if (!get_option($backup_option) && !add_option($backup_option, $backup, '', false) && !get_option($backup_option)) {
            return;
        }
        // Save the replacements first so a retry can reuse their recorded widget IDs.
        if ($instances) {
            $instances['_multiwidget'] = 1;
            if (!update_option($option, $instances) && get_option($option) !== $instances) {
                return;
            }
        }
        if ($before !== $sidebars) {
            $sidebars['array_version'] = $sidebars['array_version'] ?? 3;
            wp_set_sidebars_widgets($sidebars);
            if (get_option('sidebars_widgets') !== $sidebars) {
                return;
            }
        }
    }

    update_option($migration, 1, false);
}
// Run after the older homepage migrations and before the widget factory registers instances.
add_action('widgets_init', 'reza_ervani_retire_mh_widgets', 97);

// Clear stale placements even on sites that already ran the original migration.
function reza_ervani_clear_retired_mh_widget_placements() {
    if (is_customize_preview()) {
        return;
    }

    $legacy = reza_ervani_retired_mh_widget_types();
    $sidebars = (array) get_option('sidebars_widgets', array());
    $instances = null;
    $detached = array();
    foreach ($sidebars as $area => $ids) {
        if (!is_array($ids)) {
            continue;
        }
        foreach ($ids as $index => $id) {
            $base = _get_widget_id_base($id);
            if ($base === 'block') {
                $instances = $instances ?? (array) get_option('widget_block', array());
                $content = $instances[substr($id, 6)]['content'] ?? '';
                if (!str_contains($content, '<!-- wp:legacy-widget')) {
                    continue;
                }
                $blocks = array_values(array_filter(parse_blocks($content), static function ($block) {
                    return $block['blockName'] || trim($block['innerHTML']) !== '';
                }));
                if (count($blocks) !== 1 || $blocks[0]['blockName'] !== 'core/legacy-widget') {
                    continue;
                }
                $attrs = $blocks[0]['attrs'];
                $base = $attrs['idBase'] ?? _get_widget_id_base($attrs['id'] ?? '');
            }

            if (is_string($base) && isset($legacy[$base])) {
                $detached[$area][] = $id;
                unset($sidebars[$area][$index]);
            }
        }
        if (isset($detached[$area])) {
            $sidebars[$area] = array_values($sidebars[$area]);
        }
    }

    if (!$detached) {
        return;
    }

    $option = 'reza_ervani_retired_mh_widget_placements';
    $backup = (array) get_option($option, array());
    foreach ($detached as $area => $ids) {
        $backup[$area] = array_values(array_unique(array_merge($backup[$area] ?? array(), $ids)));
    }
    if (!update_option($option, $backup, false) && get_option($option) !== $backup) {
        return;
    }

    wp_set_sidebars_widgets($sidebars);
}
add_action('wp_loaded', 'reza_ervani_clear_retired_mh_widget_placements', 20);

function reza_ervani_legacy_home_widget_areas() {
    return array('home-2', 'home-3', 'home-4', 'home-6');
}

function reza_ervani_remove_empty_legacy_home_widget_areas() {
    $sidebars = wp_get_sidebars_widgets();
    foreach (reza_ervani_legacy_home_widget_areas() as $area) {
        if (empty($sidebars[$area])) {
            unregister_sidebar($area);
        }
    }
}
add_action('wp_loaded', 'reza_ervani_remove_empty_legacy_home_widget_areas', 25);


function reza_ervani_retired_editorial_category_ids() {
    $ids = array_merge(reza_ervani_legacy_collection_category_ids(), reza_ervani_home_posting_ads_categories());
    $slugs = array('fiqh-muyassar', 'fathul-qarib-al-mujib', 'fatwa-lajnah-daimah-saudi-arabia', 'manuskrip',
        'aqeedah', 'tadabbur-al-quran-in-english', 'hadith-explanation', 'islamic-question-answer');
    foreach ($slugs as $slug) {
        $series = reza_ervani_editorial_series($slug);
        if ($series) {
            $ids[] = $series['category_id'];
            if ($slug === 'fatwa-lajnah-daimah-saudi-arabia') {
                $fatwa = get_category_by_slug('fatwa');
                if ($fatwa) {
                    $ids[] = (int) $fatwa->term_id;
                }
            }
        }
    }
    return $ids;
}
