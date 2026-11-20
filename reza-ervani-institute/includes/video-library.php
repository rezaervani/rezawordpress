<?php

function reza_ervani_register_video_library() {
    if (post_type_exists('aiovg_videos')) {
        return;
    }

    // Keep the original post type and taxonomy identifiers so existing posts and term relationships still resolve.
    $permalinks = (array) get_option('aiovg_permalink_settings', array());
    $archive_id = (int) ($permalinks['video_archive_page'] ?? 0);
    $archive_page = $archive_id > 0 ? get_post($archive_id) : null;
    $archive_path = $archive_page && $archive_page->post_type === 'page' && $archive_page->post_status === 'publish'
        ? get_page_uri($archive_page)
        : '';
    $administrator = get_role('administrator');
    $legacy_permissions = $administrator && $administrator->has_cap('edit_aiovg_videos');

    register_post_type('aiovg_videos', array(
        'labels' => array(
            'name' => __('Galeri Video', 'mh-healthmag'),
            'singular_name' => __('Video', 'mh-healthmag'),
            'all_items' => __('Semua Video', 'mh-healthmag'),
            'add_new_item' => __('Tambah Video', 'mh-healthmag'),
            'edit_item' => __('Edit Video', 'mh-healthmag'),
        ),
        'public' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-video-alt3',
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'revisions', 'page-attributes'),
        'has_archive' => !$archive_path,
        'rewrite' => array('slug' => $archive_path ?: (sanitize_title($permalinks['video'] ?? '') ?: 'video'), 'with_front' => false),
        'capability_type' => $legacy_permissions ? array('aiovg_video', 'aiovg_videos') : 'post',
        'map_meta_cap' => true,
    ));

    $pages = (array) get_option('aiovg_page_settings', array());
    $taxonomies = array(
        'category' => array('aiovg_categories', __('Kategori Video', 'mh-healthmag'), 'video-category'),
        'tag' => array('aiovg_tags', __('Tag Video', 'mh-healthmag'), 'video-tag'),
    );
    foreach ($taxonomies as $key => list($taxonomy, $label, $slug)) {
        $page_id = (int) ($pages[$key] ?? 0);
        if ($page_id > 0 && get_post_status($page_id) === 'publish') {
            $slug = get_page_uri($page_id) ?: $slug;
        }

        $args = array(
            'label' => $label,
            'public' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'hierarchical' => $key === 'category',
            'rewrite' => array('slug' => $slug, 'with_front' => false),
        );
        if ($legacy_permissions) {
            $args['capabilities'] = array(
                'manage_terms' => 'manage_aiovg_options',
                'edit_terms' => 'manage_aiovg_options',
                'delete_terms' => 'manage_aiovg_options',
                'assign_terms' => 'edit_aiovg_videos',
            );
        }
        register_taxonomy($taxonomy, 'aiovg_videos', $args);
    }

    add_filter('the_content', 'reza_ervani_video_content', 9);
}
add_action('init', 'reza_ervani_register_video_library', 20);

function reza_ervani_refresh_video_rewrites() {
    $native_library = has_filter('the_content', 'reza_ervani_video_content') !== false;
    $mode = $native_library ? 'theme' : 'plugin';
    if (get_option('reza_ervani_video_rewrite_mode') !== $mode) {
        flush_rewrite_rules(false);
        update_option('reza_ervani_video_rewrite_mode', $mode, false);
    }
}
add_action('wp_loaded', 'reza_ervani_refresh_video_rewrites');

function reza_ervani_video_thumbnail($post_id, $size, $attributes = array()) {
    $thumbnail_id = get_post_thumbnail_id($post_id) ?: absint(get_post_meta($post_id, 'image_id', true));
    $image = $thumbnail_id ? wp_get_attachment_image($thumbnail_id, $size, false, $attributes) : '';
    if ($image) {
        return $image;
    }

    $url = esc_url(get_post_meta($post_id, 'image', true), array('http', 'https'));
    return $url ? sprintf('<img src="%s" alt="" loading="lazy" decoding="async">', $url) : '';
}

function reza_ervani_video_access_allowed($post_id) {
    if (function_exists('pms_is_post_restricted') && pms_is_post_restricted($post_id)) {
        return false;
    }

    $settings = (array) get_option('aiovg_restrictions_settings', array());
    if (empty($settings['enable_restrictions']) || current_user_can('edit_post', $post_id) || current_user_can('manage_aiovg_options')) {
        return true;
    }

    $access = (int) ($settings['access_control'] ?? 2);
    $roles = (array) ($settings['restricted_roles'] ?? array());
    $override = get_post_meta($post_id, 'access_control', true);
    if (in_array((string) $override, array('0', '1', '2'), true)) {
        $access = (int) $override;
        $roles = (array) (get_post_meta($post_id, 'restricted_roles', true) ?: $roles);
    }
    $roles = array_filter($roles);

    return $access === 0
        || ($access === 1 && !is_user_logged_in())
        || ($access === 2 && is_user_logged_in() && (!$roles || array_intersect($roles, wp_get_current_user()->roles)));
}

function reza_ervani_video_content($content) {
    if (!is_singular('aiovg_videos') || !in_the_loop() || !is_main_query() || post_password_required()) {
        return $content;
    }

    $post_id = get_the_ID();
    if (!reza_ervani_video_access_allowed($post_id)) {
        return '<p>' . esc_html__('Video ini hanya tersedia untuk pengguna yang memiliki akses.', 'mh-healthmag') . '</p>';
    }

    $source_type = get_post_meta($post_id, 'type', true);
    if (in_array($source_type, array('youtube', 'vimeo', 'dailymotion'), true)) {
        $url = esc_url_raw(get_post_meta($post_id, $source_type, true), array('http', 'https'));
        if ($url) {
            $embed = wp_oembed_get($url, array('discover' => false));
            return ($embed ?: '<p><a href="' . esc_url($url) . '">' . esc_html__('Buka video', 'mh-healthmag') . '</a></p>') . $content;
        }
    }

    if ($source_type && $source_type !== 'default') {
        return $content;
    }

    $sources = array();
    foreach (array('mp4', 'webm', 'ogv') as $format) {
        $url = esc_url_raw(get_post_meta($post_id, $format, true), array('http', 'https'));
        if ($url) {
            $sources[$format] = $url;
        }
    }
    if (!isset($sources['mp4'])) {
        $attachment = wp_get_attachment_url(absint(get_post_meta($post_id, 'mp4_id', true)));
        if ($attachment) {
            $sources['mp4'] = $attachment;
        }
    }
    if (!$sources) {
        return $content;
    }

    $image_id = get_post_thumbnail_id($post_id) ?: absint(get_post_meta($post_id, 'image_id', true));
    $poster = wp_get_attachment_image_url($image_id, 'large') ?: get_post_meta($post_id, 'image', true);
    return wp_video_shortcode($sources + array('preload' => 'none', 'poster' => esc_url_raw($poster))) . $content;
}
