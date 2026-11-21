<?php

function reza_ervani_home_category_slugs($value) {
    if (!is_string($value)) {
        return array();
    }

    $slugs = array_map('sanitize_title', explode(',', $value));
    return array_values(array_unique(array_filter($slugs, 'strlen')));
}

function reza_ervani_sanitize_home_categories($value) {
    return implode(', ', reza_ervani_home_category_slugs($value));
}

function reza_ervani_sanitize_home_article_count($value) {
    return max(1, min(8, (int) $value));
}

function reza_ervani_sanitize_adsense_publisher($value) {
    $value = is_string($value) ? trim($value) : '';
    return preg_match('/\Aca-pub-[0-9]{16}\z/', $value) ? $value : '';
}

function reza_ervani_sanitize_adsense_slot($value) {
    $value = is_string($value) || is_int($value) ? trim((string) $value) : '';
    return preg_match('/\A[0-9]+\z/', $value) ? $value : '';
}

function reza_ervani_customize_homepage($customizer) {
    $section = 'reza_ervani_homepage';

    $customizer->add_section($section, array(
        'title' => __('Kategori Beranda', 'mh-healthmag'),
        'panel' => 'mh_theme_options',
    ));

    $editorial_controls = array(
        'reza_ervani_editorial_home' => array(
            'default' => true,
            'label' => __('Gunakan beranda editorial', 'mh-healthmag'),
            'type' => 'checkbox',
            'sanitize' => 'rest_sanitize_boolean',
        ),
        'reza_ervani_editorial_featured' => array(
            'default' => 0,
            'label' => __('ID artikel sorotan', 'mh-healthmag'),
            'description' => __('Masukkan ID artikel yang telah terbit. Isi 0 agar sorotan mengikuti artikel terbaru.', 'mh-healthmag'),
            'type' => 'number',
            'input_attrs' => array('min' => 0, 'step' => 1),
            'sanitize' => 'absint',
        ),
        'reza_ervani_show_home_categories' => array(
            'default' => get_theme_mod('reza_ervani_editorial_home', true),
            'label' => __('Tampilkan bagian artikel per kategori', 'mh-healthmag'),
            'type' => 'checkbox',
            'sanitize' => 'rest_sanitize_boolean',
        ),
        'reza_ervani_home_categories' => array(
            'default' => get_theme_mod('reza_ervani_editorial_home', true) ? reza_ervani_editorial_category_defaults() : '',
            'label' => __('Kategori dan urutannya', 'mh-healthmag'),
            'description' => __('Pisahkan slug dengan koma sesuai urutan yang diinginkan. Kosongkan untuk menampilkan semua kategori induk yang berisi artikel, menurut abjad.', 'mh-healthmag'),
            'type' => 'text',
            'sanitize' => 'reza_ervani_sanitize_home_categories',
        ),
        'reza_ervani_editorial_wide' => array(
            'default' => reza_ervani_editorial_wide_defaults(),
            'label' => __('Kategori lebar penuh', 'mh-healthmag'),
            'description' => __('Pisahkan slug dengan koma. Kategori lainnya masuk ke kisi kolom.', 'mh-healthmag'),
            'type' => 'text',
            'sanitize' => 'reza_ervani_sanitize_home_categories',
        ),
        'reza_ervani_editorial_compact_count' => array(
            'default' => 3,
            'label' => __('Jumlah artikel per kategori kolom', 'mh-healthmag'),
            'type' => 'number',
            'input_attrs' => array('min' => 1, 'max' => 8, 'step' => 1),
            'sanitize' => 'reza_ervani_sanitize_home_article_count',
        ),
        'reza_ervani_editorial_wide_count' => array(
            'default' => 4,
            'label' => __('Jumlah artikel per kategori lebar penuh', 'mh-healthmag'),
            'type' => 'number',
            'input_attrs' => array('min' => 1, 'max' => 8, 'step' => 1),
            'sanitize' => 'reza_ervani_sanitize_home_article_count',
        ),
        'reza_ervani_editorial_widgets' => array(
            'default' => true,
            'label' => __('Tampilkan widget di bawah beranda editorial', 'mh-healthmag'),
            'type' => 'checkbox',
            'sanitize' => 'rest_sanitize_boolean',
        ),
    );

    foreach ($editorial_controls as $id => $control) {
        $customizer->add_setting($id, array(
            'default' => $control['default'],
            'sanitize_callback' => $control['sanitize'],
        ));
        unset($control['sanitize'], $control['default']);
        $customizer->add_control($id, array('section' => $section) + $control);
    }
}

add_action('customize_register', 'reza_ervani_customize_homepage', 20);

function reza_ervani_customize_home_ad($customizer) {
    $section = 'reza_ervani_home_ad';
    $customizer->add_section($section, array(
        'title' => __('Iklan Beranda', 'mh-healthmag'),
        'description' => __('Slot responsif setelah Fiqh Muyassar, menggunakan skrip AdSense yang sudah terpasang di situs.', 'mh-healthmag'),
        'panel' => 'mh_theme_options',
    ));

    $customizer->add_setting('reza_ervani_home_adsense_publisher', array(
        'default' => '',
        'sanitize_callback' => 'reza_ervani_sanitize_adsense_publisher',
    ));
    $customizer->add_control('reza_ervani_home_adsense_publisher', array(
        'section' => $section,
        'label' => __('ID penayang AdSense', 'mh-healthmag'),
        'description' => __('Salin nilai data-ad-client (ca-pub-…) dari kode unit iklan.', 'mh-healthmag'),
        'type' => 'text',
    ));

    $customizer->add_setting('reza_ervani_home_adsense_slot', array(
        'default' => '',
        'sanitize_callback' => 'reza_ervani_sanitize_adsense_slot',
    ));
    $customizer->add_control('reza_ervani_home_adsense_slot', array(
        'section' => $section,
        'label' => __('ID unit iklan', 'mh-healthmag'),
        'description' => __('Salin angka data-ad-slot dari unit Display responsif. Kosongkan untuk menonaktifkan slot.', 'mh-healthmag'),
        'type' => 'text',
        'input_attrs' => array('inputmode' => 'numeric'),
    ));
}

add_action('customize_register', 'reza_ervani_customize_home_ad', 20);
