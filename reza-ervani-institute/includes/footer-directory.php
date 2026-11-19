<?php

function reza_ervani_register_footer_directory() {
    register_nav_menu('footer_directory', __('Direktori Footer', 'mh-healthmag'));
}
add_action('after_setup_theme', 'reza_ervani_register_footer_directory');

function reza_ervani_default_footer_groups() {
    $categories = get_categories(array(
        'slug' => array('tadabbur-al-quran', 'hadits', 'sirah-nabawiyah', 'bahasa-arab', 'tanya-jawab', 'sejarah-palestina', 'dunia-islam', 'english-article', 'spain', 'pribadi'),
        'hide_empty' => false,
    ));

    $category_links = array();
    foreach ($categories as $category) {
        $url = get_category_link($category->term_id);
        if (!is_wp_error($url)) {
            $category_links[$category->slug] = array($category->name, $url);
        }
    }

    return array(
        __('Al Quran & Kitab', 'mh-healthmag') => array(
            $category_links['tadabbur-al-quran'] ?? null,
            array(__('Koleksi Kitab', 'mh-healthmag'), home_url('/maktabah/')),
            array(__('Al Quran Per Halaman', 'mh-healthmag'), home_url('/maktabah/alquranperhalaman.php')),
            array(__('Al Quran Per Ayat', 'mh-healthmag'), home_url('/maktabah/alquran.php')),
        ),
        __('Ilmu Islam', 'mh-healthmag') => array(
            $category_links['hadits'] ?? null,
            $category_links['sirah-nabawiyah'] ?? null,
            $category_links['bahasa-arab'] ?? null,
            $category_links['tanya-jawab'] ?? null,
        ),
        __('Dunia & Sejarah', 'mh-healthmag') => array(
            $category_links['dunia-islam'] ?? null,
            $category_links['sejarah-palestina'] ?? null,
            array(__('Timeline Palestina', 'mh-healthmag'), home_url('/timeline/')),
            array(__('Peta Palestina', 'mh-healthmag'), home_url('/palestina/peta')),
        ),
        __('Bahasa & Proyek', 'mh-healthmag') => array(
            $category_links['english-article'] ?? null,
            isset($category_links['spain']) ? array(__('Artículos en español', 'mh-healthmag'), $category_links['spain'][1]) : null,
            array(__('Peta Pengetahuan', 'mh-healthmag'), 'https://talaqqi.or.id/'),
            array(__('Mikronote', 'mh-healthmag'), home_url('/social')),
        ),
        __('Institut', 'mh-healthmag') => array(
            array(__('Beranda', 'mh-healthmag'), home_url('/')),
            $category_links['pribadi'] ?? null,
            array(__('Beriklan', 'mh-healthmag'), home_url('/iklan-di-rezaervani-com/')),
        ),
    );
}
