<?php

function reza_ervani_uses_editorial_home() {
    return is_page_template('homepage.php') && get_theme_mod('reza_ervani_editorial_home', true);
}

function reza_ervani_uses_editorial_page() {
    return is_page() && !is_page_template('homepage.php');
}

function reza_ervani_editorial_body_class($classes) {
    if (reza_ervani_uses_editorial_home() || reza_ervani_uses_editorial_page() || is_category() || is_singular('post')) {
        $classes[] = 'reza-editorial';
    }

    return $classes;
}
add_filter('body_class', 'reza_ervani_editorial_body_class');

function reza_ervani_editorial_hijri_date(DateTimeImmutable $date) {
    $utc = new DateTimeZone('UTC');
    $civil_date = new DateTimeImmutable($date->format('Y-m-d'), $utc);
    $epoch = new DateTimeImmutable('0622-07-19', $utc);
    $days = (int) $epoch->diff($civil_date)->format('%r%a');

    // The civil Hijri calendar uses a 30-year cycle with 11 leap days.
    $year = intdiv(30 * $days + 10646, 10631);
    $day_of_year = $days - (354 * ($year - 1) + intdiv(3 + 11 * $year, 30));

    // Months alternate between 30 and 29 days. The leap day stays in Zulhijah.
    $month = min(12, 1 + intdiv(2 * $day_of_year, 59));
    $day = $day_of_year - intdiv(59 * ($month - 1) + 1, 2) + 1;
    $months = array(
        __('Muharam', 'mh-healthmag'),
        __('Safar', 'mh-healthmag'),
        __('Rabiulawal', 'mh-healthmag'),
        __('Rabiulakhir', 'mh-healthmag'),
        __('Jumadilawal', 'mh-healthmag'),
        __('Jumadilakhir', 'mh-healthmag'),
        __('Rajab', 'mh-healthmag'),
        __('Syakban', 'mh-healthmag'),
        __('Ramadan', 'mh-healthmag'),
        __('Syawal', 'mh-healthmag'),
        __('Zulkaidah', 'mh-healthmag'),
        __('Zulhijah', 'mh-healthmag'),
    );

    /* translators: 1: Hijri day, 2: Hijri month, 3: Hijri year. */
    return sprintf(__('%1$d %2$s %3$d H', 'mh-healthmag'), $day, $months[$month - 1], $year);
}

function reza_ervani_filter_editorial_widget($instance, $widget, $sidebar) {
    if ($sidebar['id'] === 'home-6' && $widget->id_base === 'custom_html'
        && strcasecmp(trim(wp_strip_all_tags($instance['title'] ?? '')), 'Disclaimer') === 0) {
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

function reza_ervani_home_widget_category_ids($base, $areas) {
    $sidebars = wp_get_sidebars_widgets();
    $instances = (array) get_option('widget_' . $base, array());
    $categories = array();
    foreach ($areas as $area) {
        foreach ($sidebars[$area] ?? array() as $id) {
            if (preg_match('/^' . $base . '-(\d+)$/D', $id, $matches)) {
                $categories[] = absint($instances[$matches[1]]['category'] ?? 0);
            }
        }
    }

    return array_values(array_unique(array_filter($categories)));
}

function reza_ervani_editorial_question_topics($category, $scope) {
    if ($scope === 'root') {
        return array($category);
    }

    $selection = $scope === 'grandchildren' ? 'child_of' : 'parent';
    $descendants = get_categories(array(
        $selection => $category->term_id,
        'orderby' => 'name',
        'hide_empty' => false,
    ));
    $by_parent = array();
    foreach ($descendants as $term) {
        $by_parent[$term->parent][] = $term;
    }

    $children = $by_parent[$category->term_id] ?? array();
    if ($scope === 'child_cards') {
        return array_merge($children, array($category));
    }

    $topics = array($category);
    foreach ($children as $child) {
        $topics[] = $child;
        foreach ($by_parent[$child->term_id] ?? array() as $grandchild) {
            $topics[] = $grandchild;
        }
    }

    return $topics;
}

function reza_ervani_editorial_question_cards($category, $scope = 'child_cards', $limit = 3) {
    $topics = reza_ervani_editorial_question_topics($category, $scope);
    $group_child_stories = $scope === 'child_cards';
    $cards = array();
    $labels = array();
    foreach ($topics as $topic) {
        $is_parent = $topic->term_id === $category->term_id;
        if ($group_child_stories && $is_parent && $cards) {
            break;
        }

        $term_filter = $group_child_stories
            ? array('cat' => $topic->term_id)
            : array('category__in' => array($topic->term_id));
        $stories = reza_ervani_editorial_posts(array('numberposts' => $limit) + $term_filter);
        $url = get_category_link($topic->term_id);
        if (!$stories || is_wp_error($url)) {
            continue;
        }

        $label = $topic->name;
        if ($category->slug === 'tanya-jawab') {
            if (!$is_parent) {
                $label = ucfirst(preg_replace('/^Tanya Jawab\s+/i', '', $label));
            } elseif ($group_child_stories) {
                $label = __('Pertanyaan Terbaru', 'mh-healthmag');
            }
        }
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

    if ($group_child_stories) {
        usort($cards, static function ($left, $right) {
            return strnatcasecmp($left['title'], $right['title']);
        });
    }

    return $cards;
}

function reza_ervani_legacy_collection_category_ids() {
    // Suppress the older sidebar duplicates of the sections at the top of the homepage.
    $ids = get_terms(array(
        'taxonomy' => 'category',
        'slug' => array('tadabbur-al-quran', 'tanya-jawab', 'sejarah-palestina'),
        'fields' => 'ids',
        'hide_empty' => false,
    ));

    return is_wp_error($ids) ? array() : $ids;
}
