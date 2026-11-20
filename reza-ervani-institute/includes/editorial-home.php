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

function reza_ervani_filter_editorial_widget($instance, $widget, $sidebar, $replaced_categories) {
    if ($sidebar['id'] === 'home-1' && $widget->id_base === 'mh_slider_hp') {
        return false;
    }

    if ($widget->id_base === 'mh_magazine_lite_posts_focus' && strcasecmp(trim(wp_strip_all_tags($instance['title'] ?? '')), 'Terbaru') === 0) {
        return false;
    }

    if ($sidebar['id'] === 'home-5' && $widget->id_base === 'mh_magazine_lite_tabbed'
        && strcasecmp(trim(wp_strip_all_tags($instance['title'] ?? '')), 'Pintasan') === 0) {
        return false;
    }

    if ($sidebar['id'] === 'home-6' && $widget->id_base === 'custom_html'
        && strcasecmp(trim(wp_strip_all_tags($instance['title'] ?? '')), 'Disclaimer') === 0) {
        return false;
    }

    if (in_array($widget->id_base, array('mh_custom_posts', 'mh_magazine_lite_posts_focus'), true) &&
        in_array((int) ($instance['category'] ?? 0), $replaced_categories, true)) {
        return false;
    }

    return $instance;
}

function reza_ervani_style_hadits_widget($params) {
    if ($params[0]['id'] !== 'home-5'
        || !preg_match('/^mh_magazine_lite_posts_focus-(\d+)$/D', $params[0]['widget_id'], $match)) {
        return $params;
    }

    $widgets = (array) get_option('widget_mh_magazine_lite_posts_focus', array());
    $category_id = absint($widgets[$match[1]]['category'] ?? 0);
    $hadits = get_category_by_slug('ilmu-hadits');
    if ($hadits && $category_id === (int) $hadits->term_id) {
        $params[0]['before_widget'] = str_replace('class="mh-widget ', 'class="mh-widget reza-editorial-hadits-widget ', $params[0]['before_widget']);
    }

    return $params;
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

function reza_ervani_editorial_series($slug, $title = null) {
    $category = get_category_by_slug($slug);
    if (!$category) {
        return array();
    }

    $stories = reza_ervani_editorial_posts(array('cat' => $category->term_id, 'numberposts' => 5));
    if (!$stories) {
        return array();
    }

    return array(
        'category' => $category,
        'category_id' => (int) $category->term_id,
        'title' => $title ?? $category->name,
        'stories' => $stories,
    );
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

function reza_ervani_editorial_collection_topics() {
    return array(
        'kunuuz-riyadhus-shaalihin' => array(
            'title' => __('Kunuuz Riyadhus Shalihin', 'mh-healthmag'),
            'replaces' => 'tadabbur-al-quran',
            'sidebar' => 'home-ad-kunuuz',
        ),
        'manzhumah-al-jazariyah' => array(
            'title' => __('Jazariyyah', 'mh-healthmag'),
            'replaces' => 'tanya-jawab',
            'sidebar' => 'home-ad-jazariyyah',
        ),
        'ilmu-rasm-dan-dhabth' => array(
            'title' => __('Ilmu Rasm dan Dhabth', 'mh-healthmag'),
            'replaces' => 'sejarah-palestina',
            'sidebar' => 'home-ad-rasm',
        ),
    );
}

function reza_ervani_editorial_collections() {
    $collections = array();
    foreach (reza_ervani_editorial_collection_topics() as $slug => $topic) {
        $category = get_category_by_slug($slug);
        if (!$category) {
            continue;
        }

        $stories = reza_ervani_editorial_posts(array('cat' => $category->term_id, 'numberposts' => 3));
        if (!$stories) {
            continue;
        }

        $previous = get_category_by_slug($topic['replaces']);
        $collections[] = array(
            'title' => $topic['title'],
            'sidebar' => $topic['sidebar'],
            'category_id' => $category->term_id,
            'replaces_id' => $previous ? $previous->term_id : 0,
            'stories' => $stories,
        );
    }

    return $collections;
}
