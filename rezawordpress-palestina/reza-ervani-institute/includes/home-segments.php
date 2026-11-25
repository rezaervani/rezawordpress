<?php

function reza_ervani_home_segments() {
    $editorial = get_theme_mod('reza_ervani_editorial_home', true);
    $show_categories = get_theme_mod('reza_ervani_show_home_categories', $editorial);
    $lower_widgets = !$editorial || get_theme_mod('reza_ervani_editorial_widgets', true);

    return array(
        Reza_Ervani_Smart_Ad::TOP_AREA => array(__('Iklan sebelum Sorotan Utama', 'mh-healthmag'), true),
        Reza_Ervani_Spotlight::HOME_AREA => array(__('Sorotan Utama', 'mh-healthmag'), true),
        'home-categories' => array(__('Artikel per kategori', 'mh-healthmag'), $show_categories),
        Reza_Ervani_Category_Grid::HOME_AREA => array(__('Kisi Kategori', 'mh-healthmag'), true),
        'home-network-ad' => array(__('Iklan setelah Kisi Kategori', 'mh-healthmag'), false),
        'home-network-feed' => array(__('Jaringan Situs – Palestina dan Dunia Islam', 'mh-healthmag'), false),
        'home-network-duniaislam' => array(__('Jaringan Situs – Dunia Islam', 'mh-healthmag'), false),
        Reza_Ervani_Palestina_Topics::HOME_AREA => array(__('Rubrik Palestina', 'mh-healthmag'), $show_categories),
        Reza_Ervani_Duniaislam_Topics::HOME_AREA => array(__('Rubrik Dunia Islam', 'mh-healthmag'), false),
        Reza_Ervani_Smart_Ad::MIDDLE_AREA => array(__('Iklan Tengah', 'mh-healthmag'), true),
        'home-1' => array(__('Widget Lebar Penuh', 'mh-healthmag'), $lower_widgets),
        Reza_Ervani_Posting_Ads::HOME_AREA => array(__('Posting dan Iklan', 'mh-healthmag'), $lower_widgets),
        'home-video-gallery' => array(__('Galeri Video', 'mh-healthmag'), !$editorial),
        'home-editorial-category-widgets' => array(__('Tiga Kolom Kategori', 'mh-healthmag'), $lower_widgets),
        'home-editorial-category-widgets-2' => array(__('Tiga Kolom Kategori (Baris Kedua)', 'mh-healthmag'), $lower_widgets),
        'home-2' => array(__('Area Warisan 2', 'mh-healthmag'), !$editorial),
        'home-3' => array(__('Area Warisan 3', 'mh-healthmag'), !$editorial),
        'home-4' => array(__('Area Warisan 4', 'mh-healthmag'), !$editorial),
        'home-5' => array(__('Daftar Artikel Utama', 'mh-healthmag'), !$editorial),
        'home-6' => array(__('Area Warisan 6', 'mh-healthmag'), !$editorial),
        'home-news' => array(__('Terkini', 'mh-healthmag'), true),
        'home-latest-posts' => array(__('Posting Terakhir', 'mh-healthmag'), $lower_widgets && get_option('show_on_front') === 'page'),
    );
}

function reza_ervani_home_segment_setting($segment) {
    return 'reza_ervani_segment_' . str_replace('-', '_', $segment);
}

function reza_ervani_home_segment_visible($segment) {
    $segments = reza_ervani_home_segments();
    return isset($segments[$segment]) && (bool) get_theme_mod(reza_ervani_home_segment_setting($segment), $segments[$segment][1]);
}

function reza_ervani_home_sidebar_active($area) {
    return reza_ervani_home_segment_visible($area) && is_active_sidebar($area);
}

function reza_ervani_home_sidebar($area) {
    if (reza_ervani_home_segment_visible($area)) {
        dynamic_sidebar($area);
    }
}

function reza_ervani_customize_home_segments($customizer) {
    $section = 'reza_ervani_home_segments';
    $customizer->add_section($section, array(
        'title' => __('Segmen Beranda', 'mh-healthmag'),
        'description' => __('Centang segmen yang ingin ditampilkan. Pengaturan dan urutan widget tetap tersimpan saat segmen disembunyikan.', 'mh-healthmag'),
    ));

    foreach (reza_ervani_home_segments() as $key => list($label, $default)) {
        if (in_array($key, reza_ervani_legacy_home_widget_areas(), true) && !is_registered_sidebar($key)) {
            continue;
        }
        $setting = reza_ervani_home_segment_setting($key);
        $customizer->add_setting($setting, array(
            'default' => (bool) $default,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ));
        $customizer->add_control($setting, array(
            'label' => $label,
            'section' => $section,
            'type' => 'checkbox',
        ));
    }
}
add_action('customize_register', 'reza_ervani_customize_home_segments', 20);
