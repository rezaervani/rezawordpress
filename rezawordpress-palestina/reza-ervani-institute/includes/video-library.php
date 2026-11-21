<?php

function reza_ervani_register_video_library() {
    if (post_type_exists('aiovg_videos') || get_option('reza_ervani_video_posts_migrated')) {
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
    $mode = 'articles';
    if (post_type_exists('aiovg_videos')) {
        $mode = has_filter('the_content', 'reza_ervani_video_content') !== false ? 'theme' : 'plugin';
    }
    if (get_option('reza_ervani_video_rewrite_mode') !== $mode) {
        flush_rewrite_rules(false);
        update_option('reza_ervani_video_rewrite_mode', $mode, false);
    }
}
add_action('wp_loaded', 'reza_ervani_refresh_video_rewrites');

function reza_ervani_legacy_video_policy($post_id) {
    $settings = (array) get_option('aiovg_restrictions_settings', array());
    if (empty($settings['enable_restrictions'])) {
        return array(0, array());
    }

    $access = (int) ($settings['access_control'] ?? 2);
    $roles = (array) ($settings['restricted_roles'] ?? array());
    $override = get_post_meta($post_id, 'access_control', true);
    if (in_array((string) $override, array('0', '1', '2'), true)) {
        $access = (int) $override;
        $roles = (array) (get_post_meta($post_id, 'restricted_roles', true) ?: $roles);
    }
    return array($access, array_filter($roles));
}

function reza_ervani_video_access_allowed($post_id) {
    if (function_exists('pms_is_post_restricted') && pms_is_post_restricted($post_id)) {
        return false;
    }
    if (current_user_can('edit_post', $post_id) || current_user_can('manage_aiovg_options')) {
        return true;
    }

    list($access, $roles) = reza_ervani_legacy_video_policy($post_id);

    return $access === 0
        || ($access === 1 && !is_user_logged_in())
        || ($access === 2 && is_user_logged_in() && (!$roles || array_intersect($roles, wp_get_current_user()->roles)));
}

function reza_ervani_local_video_path($url) {
    $path = trim((string) wp_parse_url($url, PHP_URL_PATH), '/');
    $base = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');
    if ($path === $base) {
        return '';
    }
    if ($base !== '' && strpos($path, $base . '/') === 0) {
        $path = substr($path, strlen($base) + 1);
    }
    return rawurldecode($path);
}

function reza_ervani_redirect_migrated_video($wp) {
    if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', array('GET', 'HEAD'), true)) {
        return;
    }

    $path = rawurldecode(trim((string) $wp->request, '/'));
    $paths = (array) get_option('reza_ervani_migrated_video_paths', array());
    $post_id = $paths[$path] ?? 0;
    if (!$post_id || get_post_type($post_id) !== 'post') {
        return;
    }

    $status = get_post_status($post_id);
    if ($status !== 'publish' && !($status === 'private' && current_user_can('read_post', $post_id))) {
        return;
    }

    $url = get_permalink($post_id);
    if ($url && $path === reza_ervani_local_video_path($url)) {
        $wp->query_vars = array('p' => $post_id, 'post_type' => 'post');
    } elseif ($url && wp_safe_redirect($url, 301)) {
        exit;
    }
}
add_action('parse_request', 'reza_ervani_redirect_migrated_video');

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
