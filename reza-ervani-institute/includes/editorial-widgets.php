<?php

function reza_ervani_register_editorial_sidebars() {
    $widget_markup = array(
        'before_widget' => '<div id="%1$s" class="mh-widget %2$s">',
        'after_widget' => '</div>',
    );

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
        'id' => 'home-editorial-category-widgets',
        'name' => __('Beranda – Tiga Kolom Kategori', 'mh-healthmag'),
        'description' => __('Susun widget MH Custom Posts untuk memilih kategori, judul, dan jumlah artikel di deretan bawah galeri video.', 'mh-healthmag'),
        'before_title' => '<h2 class="mh-widget-title">',
        'after_title' => '</h2>',
    ) + $widget_markup);
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
    $migration = 'reza_ervani_category_widgets_1_2_16';
    if (get_option($migration) || is_customize_preview()) {
        return;
    }

    $destination = 'home-editorial-category-widgets';
    $sidebars = (array) get_option('sidebars_widgets', array());
    if (!empty($sidebars[$destination])) {
        update_option($migration, 1, false);
        return;
    }

    $titles = array(
        'manzhumah-syatibiyah' => __("Qiroah Sab'ah", 'mh-healthmag'),
        'ilmu-qiroaat' => __('Ilmu Qiroat', 'mh-healthmag'),
        'hilyatut-tilawah' => __('Hilyatut Tilawah', 'mh-healthmag'),
    );
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
// Saved widget instances are registered by WP_Widget_Factory at priority 100.
add_action('widgets_init', 'reza_ervani_migrate_category_widgets', 95);
