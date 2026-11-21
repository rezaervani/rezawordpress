<?php

function reza_ervani_legacy_media_url($url) {
    $url = trim((string) $url);
    if (strpos($url, '//') === 0) {
        $url = set_url_scheme($url);
    } elseif (strpos($url, '/') === 0) {
        $url = home_url($url);
    }

    $url = esc_url_raw($url, array('http', 'https'));
    return wp_parse_url($url, PHP_URL_HOST) ? $url : '';
}

function reza_ervani_legacy_video_block($post, $poster) {
    if (has_block('core/video', $post)) {
        return '';
    }

    $type = get_post_meta($post->ID, 'type', true);
    if (in_array($type, array('youtube', 'vimeo', 'dailymotion'), true)) {
        $url = reza_ervani_legacy_media_url(get_post_meta($post->ID, $type, true));
        if ($url && _wp_oembed_get_object()->get_provider($url, array('discover' => false))) {
            $html = '<figure class="wp-block-embed is-type-video is-provider-' . esc_attr($type) . ' wp-block-embed-' . esc_attr($type) . '"><div class="wp-block-embed__wrapper">' . "\n"
                . esc_url_raw($url) . "\n" . '</div></figure>';
            return serialize_block(array(
                'blockName' => 'core/embed',
                'attrs' => array('url' => $url, 'type' => 'video', 'providerNameSlug' => $type),
                'innerContent' => array($html),
            ));
        }
    } elseif (!$type || $type === 'default') {
        $media_id = absint(get_post_meta($post->ID, 'mp4_id', true));
        $source = get_post_meta($post->ID, 'mp4', true) ?: ($media_id ? wp_get_attachment_url($media_id) : '');
        $source = $source ?: (get_post_meta($post->ID, 'webm', true) ?: get_post_meta($post->ID, 'ogv', true));
        $url = reza_ervani_legacy_media_url($source);
        if ($url) {
            if (strpos((string) wp_parse_url($url, PHP_URL_PATH), '/all-in-one-video-gallery/') !== false) {
                return new WP_Error('plugin_owned_media', 'Sumber video masih tersimpan di folder AIOVG. Pindahkan sumbernya ke Pustaka Media terlebih dahulu.');
            }

            $attrs = $media_id && wp_get_attachment_url($media_id) === $url ? array('id' => $media_id) : array();
            $poster_html = $poster ? ' poster="' . esc_url($poster) . '"' : '';
            $html = '<figure class="wp-block-video"><video controls preload="metadata" src="' . esc_url($url) . '"' . $poster_html . '></video></figure>';
            return serialize_block(array(
                'blockName' => 'core/video',
                'attrs' => $attrs,
                'innerContent' => array($html),
            ));
        }
    }

    return new WP_Error('missing_video_source', 'Sumber video belum tersedia dalam format yang bisa dipindahkan. Gunakan berkas video atau sematan YouTube, Vimeo, atau Dailymotion.');
}

function reza_ervani_prepare_video_migration($post) {
    list($access) = reza_ervani_legacy_video_policy($post->ID);
    if ($access !== 0) {
        return new WP_Error('restricted_video', 'Video mempunyai pembatasan akses AIOVG. Tetapkan aksesnya di WordPress sebelum memindahkannya.');
    }
    if (preg_match('/\[\/?aiovg_[a-z_]+\b/i', $post->post_content)) {
        return new WP_Error('legacy_shortcode', 'Konten mengandung shortcode AIOVG. Ganti shortcode dengan blok WordPress sebelum memindahkannya.');
    }

    $thumbnail = get_post_thumbnail_id($post) ?: absint(get_post_meta($post->ID, 'image_id', true));
    $image_url = reza_ervani_legacy_media_url(get_post_meta($post->ID, 'image', true));
    if (!wp_attachment_is_image($thumbnail)) {
        $thumbnail = $image_url ? attachment_url_to_postid($image_url) : 0;
    }

    $poster = ($thumbnail ? wp_get_attachment_image_url($thumbnail, 'large') : '') ?: $image_url;
    if (strpos((string) wp_parse_url($poster, PHP_URL_PATH), '/all-in-one-video-gallery/') !== false) {
        return new WP_Error('plugin_owned_poster', 'Gambar mini masih tersimpan di folder AIOVG. Pindahkan gambarnya ke Pustaka Media terlebih dahulu.');
    }

    $block = reza_ervani_legacy_video_block($post, $poster);
    if (is_wp_error($block)) {
        return $block;
    }

    $path = reza_ervani_local_video_path(get_permalink($post));
    return array(
        'path' => $path,
        'content' => $block ? $block . "\n\n" . $post->post_content : $post->post_content,
        'thumbnail' => $thumbnail,
        'poster' => $poster,
    );
}

/**
 * Pindahkan video AIOVG menjadi artikel WordPress di kategori Video.
 *
 * ## OPTIONS
 *
 * [--dry-run]
 * : Periksa video yang akan dipindah dan laporkan kendala tanpa mengubah artikel.
 */
function reza_ervani_migrate_video_posts($args, $assoc_args) {
    $category = get_category_by_slug('video');
    if (!$category) {
        WP_CLI::error('Kategori artikel dengan slug video belum tersedia.');
    }

    $videos = get_posts(array(
        'post_type' => 'aiovg_videos',
        'post_status' => array_values(array_diff(get_post_stati(), array('auto-draft', 'inherit'))),
        'numberposts' => -1,
        'orderby' => 'ID',
        'order' => 'ASC',
    ));
    $plans = array();
    $blocked = 0;
    foreach ($videos as $post) {
        $plan = reza_ervani_prepare_video_migration($post);
        if (is_wp_error($plan)) {
            WP_CLI::warning(sprintf('#%d %s — %s', $post->ID, $post->post_title, $plan->get_error_message()));
            $blocked++;
            continue;
        }

        $plans[$post->ID] = $plan;
        WP_CLI::log(sprintf('#%d %s [%s]', $post->ID, $post->post_title, $post->post_status));
    }

    if ($blocked) {
        WP_CLI::error(sprintf('%d video perlu diperiksa sebelum pemindahan dapat dilanjutkan.', $blocked));
    }
    if (isset($assoc_args['dry-run'])) {
        WP_CLI::success(sprintf('%d video siap dipindahkan ke kategori Video.', count($plans)));
        return;
    }

    $paths = (array) get_option('reza_ervani_migrated_video_paths', array());
    foreach ($plans as $post_id => $plan) {
        if ($plan['path'] !== '') {
            $paths[$plan['path']] = $post_id;
        }
    }
    if (get_option('reza_ervani_migrated_video_paths', array()) !== $paths
        && !update_option('reza_ervani_migrated_video_paths', $paths)) {
        WP_CLI::error('Pemetaan tautan video lama gagal disimpan.');
    }

    foreach ($plans as $post_id => $plan) {
        $categories = array_unique(array_merge(wp_get_post_categories($post_id), array((int) $category->term_id)));
        $metadata = $plan['thumbnail'] ? array('_thumbnail_id' => $plan['thumbnail']) : array('_reza_video_poster' => $plan['poster']);
        $result = wp_update_post(wp_slash(array(
            'ID' => $post_id,
            'post_type' => 'post',
            'post_content' => $plan['content'],
            'post_category' => $categories,
            'meta_input' => $metadata,
            'edit_date' => true,
        )), true);

        if (is_wp_error($result) || !$result || get_post_type($post_id) !== 'post' || !has_category($category->term_id, $post_id)) {
            $error = is_wp_error($result) ? $result->get_error_message() : 'Periksa jenis artikel dan kategori Video.';
            WP_CLI::error(sprintf('Pemindahan berhenti pada #%d. %s', $post_id, $error));
        }
        WP_CLI::log(sprintf('Dipindahkan #%d — %s', $post_id, get_permalink($post_id)));
    }

    update_option('reza_ervani_video_posts_migrated', true, false);
    WP_CLI::success(sprintf('%d video dipindahkan. Tidak ada video yang tersisa untuk pemindahan.', count($plans)));
}
WP_CLI::add_command('reza video migrate', 'reza_ervani_migrate_video_posts');
