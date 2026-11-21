<?php

class Reza_Ervani_Network_Feed {
    public static function sources() {
        return array(
            'palestina' => array('name' => __('Palestina', 'mh-healthmag'), 'url' => 'https://rezaervani.com/palestina/'),
            'duniaislam' => array('name' => __('Dunia Islam', 'mh-healthmag'), 'url' => 'https://rezaervani.com/duniaislam/'),
        );
    }

    public static function categories($source) {
        return self::cached('categories-' . $source, HOUR_IN_SECONDS, static function () use ($source) {
            $categories = array();
            $page = 1;
            do {
                $response = self::request($source, 'categories', array(
                    'per_page' => 100,
                    'page' => $page,
                    'hide_empty' => 'true',
                    '_fields' => 'id,name',
                ));
                if (is_wp_error($response)) {
                    return $response;
                }
                foreach ($response['items'] as $item) {
                    $id = absint($item['id'] ?? 0);
                    $name = self::plain_text($item['name'] ?? '');
                    if ($id && $name !== '') {
                        $categories[$id] = $name;
                    }
                }
            } while (++$page <= $response['pages']);

            return $categories;
        });
    }

    public static function posts($source, $category, $count) {
        $sources = self::sources();
        if (!isset($sources[$source])) {
            return array();
        }
        $url = $sources[$source]['url'];
        return self::cached("posts-$source-$category-$count", 15 * MINUTE_IN_SECONDS, static function () use ($source, $url, $category, $count) {
            $query = array(
                'per_page' => $count,
                'orderby' => 'date',
                'order' => 'desc',
                '_embed' => 'wp:featuredmedia',
                '_fields' => 'date_gmt,link,title,excerpt,_links,_embedded',
            );
            if ($category) {
                $query['categories'] = $category;
            }

            $response = self::request($source, 'posts', $query);
            if (is_wp_error($response)) {
                return $response;
            }
            $posts = array();
            foreach ($response['items'] as $item) {
                $link = esc_url_raw($item['link'] ?? '', array('https'));
                $title = self::plain_text($item['title']['rendered'] ?? '');
                if (strpos($link, $url) !== 0 || $title === '') {
                    continue;
                }
                $media = $item['_embedded']['wp:featuredmedia'][0] ?? array();
                $image = esc_url_raw($media['media_details']['sizes']['medium']['source_url'] ?? $media['source_url'] ?? '', array('https'));
                $date = $item['date_gmt'] ?? '';
                $posts[] = array(
                    'title' => $title,
                    'link' => $link,
                    'image' => wp_parse_url($image, PHP_URL_HOST) === 'rezaervani.com' ? $image : '',
                    'timestamp' => $date ? strtotime($date . ' UTC') : false,
                    'summary' => !$posts ? self::summary($item['excerpt']['rendered'] ?? '', $title) : '',
                );
            }
            return $posts;
        });
    }

    private static function plain_text($value) {
        return trim(wp_strip_all_tags(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private static function summary($excerpt, $title) {
        $summary = self::plain_text($excerpt);
        if (strpos($summary, $title . ' ') === 0) {
            $summary = substr($summary, strlen($title) + 1);
        }
        $summary = preg_replace('/^rezaervani\.com\s*[-–—]\s*/iu', '', $summary);
        return wp_trim_words($summary, 28, '…');
    }

    private static function request($source, $endpoint, $query) {
        $sources = self::sources();
        if (!isset($sources[$source])) {
            return new WP_Error('reza_network_source');
        }
        $url = add_query_arg($query, $sources[$source]['url'] . 'wp-json/wp/v2/' . $endpoint);
        $response = wp_safe_remote_get($url, array(
            'timeout' => 3,
            'redirection' => 0,
            'limit_response_size' => MB_IN_BYTES,
        ));
        if (is_wp_error($response)) {
            return $response;
        }
        $body = wp_remote_retrieve_body($response);
        $items = json_decode($body, true);
        if (wp_remote_retrieve_response_code($response) !== 200 || !is_array($items) || substr(ltrim($body), 0, 1) !== '[') {
            return new WP_Error('reza_network_response');
        }
        return array(
            'items' => $items,
            'pages' => max(1, (int) wp_remote_retrieve_header($response, 'x-wp-totalpages')),
        );
    }

    private static function cached($id, $lifetime, $load) {
        $key = 'reza_network_' . $id;
        $entry = get_transient($key);
        $items = $entry['items'] ?? array();
        if (($entry['refresh_after'] ?? 0) > time() || get_transient($key . '_retry')) {
            return $items;
        }

        // Keep the last successful result and space out retries if a source is down.
        set_transient($key . '_retry', 1, 3 * MINUTE_IN_SECONDS);
        $loaded = $load();
        if (is_wp_error($loaded)) {
            return $items;
        }
        set_transient($key, array('items' => $loaded, 'refresh_after' => time() + $lifetime), 7 * DAY_IN_SECONDS);
        delete_transient($key . '_retry');
        return $loaded;
    }
}
