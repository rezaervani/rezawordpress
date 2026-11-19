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

function reza_ervani_editorial_question_cards($category) {
    $topics = get_categories(array(
        'parent' => $category->term_id,
        'orderby' => 'name',
        'pad_counts' => true,
    ));

    $cards = array();
    $labels = array();
    foreach (array_merge($topics, array($category)) as $topic) {
        $is_parent = $topic->term_id === $category->term_id;
        if ($is_parent && $cards) {
            break;
        }

        $stories = reza_ervani_editorial_posts(array(
            'cat' => $topic->term_id,
            'numberposts' => 3,
        ));
        $url = get_category_link($topic->term_id);
        if (!$stories || is_wp_error($url)) {
            continue;
        }

        $label = $is_parent ? __('Pertanyaan Terbaru', 'mh-healthmag') : ucfirst(preg_replace('/^Tanya Jawab\s+/i', '', $topic->name));
        $label_key = sanitize_title($label);
        if (isset($labels[$label_key])) {
            $label = $topic->name;
        }
        $labels[$label_key] = true;

        $thumbnail_post = null;
        foreach ($stories as $story) {
            if (has_post_thumbnail($story)) {
                $thumbnail_post = $story;
                break;
            }
        }

        $cards[] = array(
            'title' => $label,
            'url' => $url,
            'thumbnail_post' => $thumbnail_post,
            'stories' => $stories,
        );
    }

    usort($cards, static function ($left, $right) {
        return strnatcasecmp($left['title'], $right['title']);
    });

    return $cards;
}
