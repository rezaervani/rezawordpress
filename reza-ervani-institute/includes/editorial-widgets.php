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

    foreach (reza_ervani_editorial_collection_topics() as $topic) {
        register_sidebar(array(
            'id' => $topic['sidebar'],
            'name' => sprintf(__('Iklan Beranda – %s', 'mh-healthmag'), $topic['title']),
            'description' => __('Kolom kanan koleksi pada beranda editorial.', 'mh-healthmag'),
            'before_title' => '<h3 class="mh-widget-title"><span class="mh-widget-title-inner">',
            'after_title' => '</span></h3>',
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
            'description' => __('Susun widget MH Custom Posts untuk memilih kategori, judul, dan jumlah artikel di deretan bawah galeri video.', 'mh-healthmag'),
            'before_title' => '<h2 class="mh-widget-title">',
            'after_title' => '</h2>',
        ) + $widget_markup);
    }
}
add_action('widgets_init', 'reza_ervani_register_editorial_sidebars');

function reza_ervani_migrate_editorial_widgets() {
    $migration = 'reza_ervani_editorial_widgets_1_2_12';
    if (get_option($migration) || is_customize_preview()) {
        return;
    }

    $sidebars = get_option('sidebars_widgets', array());
    $original = $sidebars;
    $html_widgets = get_option('widget_custom_html', array());
    $moves = array();

    // The existing site uses these Custom HTML instances for its two promotions.
    if (strcasecmp(trim($html_widgets[17]['title'] ?? ''), 'Aplikasi Android rezaervani.com') === 0) {
        $moves['custom_html-17'] = array('home-6', 'home-ad-kunuuz');
    }
    if (strcasecmp(trim($html_widgets[4]['title'] ?? ''), 'Donasi') === 0) {
        $moves['custom_html-4'] = array('home-6', 'home-ad-jazariyyah');
    }

    foreach ($moves as $widget_id => list($from, $to)) {
        if (!empty($sidebars[$to]) || !in_array($widget_id, $sidebars[$from] ?? array(), true)) {
            continue;
        }

        $sidebars[$from] = array_values(array_diff($sidebars[$from], array($widget_id)));
        $sidebars[$to] = array($widget_id);
    }

    if ($sidebars !== $original) {
        wp_set_sidebars_widgets($sidebars);
        $sidebars['array_version'] = $sidebars['array_version'] ?? 3;
        if (get_option('sidebars_widgets') !== $sidebars) {
            return;
        }
    }

    update_option($migration, 1, false);
}
add_action('wp_loaded', 'reza_ervani_migrate_editorial_widgets');

function reza_ervani_migrate_category_widgets() {
    if (is_customize_preview()) {
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
