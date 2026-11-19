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
