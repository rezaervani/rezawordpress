<?php

function reza_ervani_archive_topic_sections($category_id, &$previewed_posts) {
    $descendants = get_categories(array(
        'child_of' => $category_id,
        'orderby' => 'name',
    ));
    $by_parent = array();
    foreach ($descendants as $term) {
        $by_parent[$term->parent][] = $term;
    }

    return reza_ervani_archive_topic_branch($category_id, $by_parent, $previewed_posts);
}

function reza_ervani_archive_topic_branch($parent_id, $by_parent, &$previewed_posts) {
    $sections = array();
    foreach ($by_parent[$parent_id] ?? array() as $term) {
        $url = get_category_link($term->term_id);
        if (is_wp_error($url)) {
            continue;
        }

        // Give descendants their own previews even when the parent has articles of its own.
        $stories = $term->count ? reza_ervani_editorial_posts(array(
            'category__in' => array($term->term_id),
            'numberposts' => 4,
            'has_password' => null,
        )) : array();
        $children = reza_ervani_archive_topic_branch($term->term_id, $by_parent, $previewed_posts);
        if (!$stories && !$children) {
            continue;
        }

        foreach ($stories as $story) {
            $previewed_posts[$story->ID] = true;
        }
        $sections[] = array(
            'term' => $term,
            'url' => $url,
            'stories' => $stories,
            'children' => $children,
        );
    }
    return $sections;
}
