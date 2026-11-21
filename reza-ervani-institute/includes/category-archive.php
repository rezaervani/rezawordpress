<?php

function reza_ervani_archive_topic_sections($category, &$previewed_posts) {
    $descendants = get_categories(array(
        'child_of' => $category->term_id,
        'orderby' => 'name',
    ));
    $by_parent = array();
    foreach ($descendants as $term) {
        $by_parent[$term->parent][] = $term;
    }

    $topic_settings = $category->slug === 'english-article' ? array(
        'encyclopedia-of-jews' => array('layout' => 'feature'),
        'family-education' => array('layout' => 'grid', 'thumbnails' => 8),
        'hadith-explanation' => array('layout' => 'list', 'thumbnails' => 8),
    ) : array();

    return reza_ervani_archive_topic_branch($category->term_id, $by_parent, $previewed_posts, $topic_settings);
}

function reza_ervani_archive_topic_branch($parent_id, $by_parent, &$previewed_posts, $topic_settings = array()) {
    $sections = array();
    foreach ($by_parent[$parent_id] ?? array() as $term) {
        $url = get_category_link($term->term_id);
        if (is_wp_error($url)) {
            continue;
        }

        $settings = $topic_settings[$term->slug] ?? array();
        $thumbnails = $settings['thumbnails'] ?? 0;
        $query = array(
            'category__in' => array($term->term_id),
            'numberposts' => $thumbnails ?: 4,
            'has_password' => $thumbnails ? false : null,
        );
        if ($thumbnails) {
            $query['meta_query'] = array(array(
                'key' => '_thumbnail_id',
                'value' => 0,
                'compare' => '>',
                'type' => 'NUMERIC',
            ));
        }

        // Give descendants their own previews even when the parent has articles of its own.
        $stories = $term->count ? reza_ervani_editorial_posts($query) : array();
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
            'layout' => $settings['layout'] ?? null,
        );
    }
    return $sections;
}
