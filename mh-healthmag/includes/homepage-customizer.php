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

function reza_ervani_customize_homepage($customizer) {
    $section = 'reza_ervani_homepage';

    $customizer->add_section($section, array(
        'title' => __('Kategori Beranda', 'mh-healthmag'),
        'panel' => 'mh_theme_options',
    ));

    $customizer->add_setting('reza_ervani_show_home_categories', array(
        'default' => false,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ));
    $customizer->add_control('reza_ervani_show_home_categories', array(
        'section' => $section,
        'label' => __('Tampilkan kisi kategori', 'mh-healthmag'),
        'type' => 'checkbox',
    ));

    $customizer->add_setting('reza_ervani_home_categories', array(
        'default' => '',
        'sanitize_callback' => 'reza_ervani_sanitize_home_categories',
    ));
    $customizer->add_control('reza_ervani_home_categories', array(
        'section' => $section,
        'label' => __('Slug kategori', 'mh-healthmag'),
        'description' => __('Pisahkan slug dengan koma sesuai urutan yang diinginkan. Kosongkan untuk menampilkan semua kategori induk yang berisi artikel, menurut abjad.', 'mh-healthmag'),
        'type' => 'text',
    ));
}

add_action('customize_register', 'reza_ervani_customize_homepage', 20);
