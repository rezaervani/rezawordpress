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
