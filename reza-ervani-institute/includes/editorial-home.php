<?php

function reza_ervani_uses_editorial_home() {
    return is_page_template('homepage.php') && get_theme_mod('reza_ervani_editorial_home', true);
}

function reza_ervani_editorial_body_class($classes) {
    if (reza_ervani_uses_editorial_home()) {
        $classes[] = 'reza-editorial';
    }

    return $classes;
}
add_filter('body_class', 'reza_ervani_editorial_body_class');

function reza_ervani_skip_home_slider($instance, $widget, $sidebar) {
    if ($sidebar['id'] === 'home-1' && $widget->id_base === 'mh_slider_hp') {
        return false;
    }

    return $instance;
}

function reza_ervani_editorial_category_defaults() {
    return 'tadabbur-al-quran, hadits, sirah-nabawiyah, bahasa-arab, sejarah-palestina, dunia-islam, english-article, spain';
}

function reza_ervani_editorial_wide_defaults() {
    return 'tadabbur-al-quran, sejarah-palestina';
}

function reza_ervani_editorial_posts($args) {
    return get_posts(array_replace(array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'has_password' => false,
        'suppress_filters' => false,
    ), $args));
}

function reza_ervani_editorial_title($post) {
    $title = get_the_title($post);
    return $title !== '' ? $title : __('Tanpa judul', 'mh-healthmag');
}
