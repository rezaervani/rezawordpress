<?php

class Reza_Ervani_Network_Feed_Widget extends WP_Widget {
    const ID_BASE = 'reza_network_feed';
    const HOME_AREA = 'home-network-feed';
    const MAX_POSTS = 30;

    public function __construct() {
        parent::__construct(self::ID_BASE, __('Artikel Jaringan Situs', 'mh-healthmag'), array(
            'classname' => 'reza-network-widget',
            'description' => __('Artikel terbaru dari Palestina atau Dunia Islam.', 'mh-healthmag'),
            'customize_selective_refresh' => true,
        ));
    }

    private function settings($instance) {
        $feed = $instance['feed'] ?? '';
        $parts = explode(':', is_string($feed) ? $feed : '', 2);
        $sources = Reza_Ervani_Network_Feed::sources();
        $source = isset($sources[$parts[0]]) ? $parts[0] : 'palestina';
        $category = isset($sources[$parts[0]]) && ctype_digit($parts[1] ?? '') ? (int) $parts[1] : 0;
        return array(
            'feed' => $source . ($category ? ':' . $category : ''),
            'source' => $source,
            'category' => $category,
            'title' => sanitize_text_field($instance['title'] ?? ''),
            'count' => max(1, min(self::MAX_POSTS, (int) ($instance['count'] ?? 10))),
            'thumbnails' => !empty($instance['thumbnails'] ?? true),
        );
    }

    public function update($new_instance, $old_instance) {
        $new_instance['thumbnails'] = !empty($new_instance['thumbnails']);
        $settings = $this->settings($new_instance);
        unset($settings['source'], $settings['category']);
        return $settings;
    }

    public function form($instance) {
        $settings = $this->settings($instance);
        $feed_id = $this->get_field_id('feed');
        $title_id = $this->get_field_id('title');
        $count_id = $this->get_field_id('count');
        $thumbnails_id = $this->get_field_id('thumbnails');
        ?>
        <p>
            <label for="<?php echo esc_attr($title_id); ?>"><?php esc_html_e('Judul (opsional)', 'mh-healthmag'); ?></label>
            <input type="text" class="widefat" id="<?php echo esc_attr($title_id); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" value="<?php echo esc_attr($settings['title']); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($feed_id); ?>"><?php esc_html_e('Situs dan rubrik', 'mh-healthmag'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($feed_id); ?>" name="<?php echo esc_attr($this->get_field_name('feed')); ?>">
                <?php foreach (Reza_Ervani_Network_Feed::sources() as $source => $site) :
                    $categories = Reza_Ervani_Network_Feed::categories($source);
                    if ($source === $settings['source'] && $settings['category'] && !isset($categories[$settings['category']])) {
                        /* translators: %d: category ID on the source website. */
                        $categories[$settings['category']] = sprintf(__('Rubrik tersimpan (%d)', 'mh-healthmag'), $settings['category']);
                    }
                    ?>
                    <optgroup label="<?php echo esc_attr($site['name']); ?>">
                        <option value="<?php echo esc_attr($source); ?>" <?php selected($settings['feed'], $source); ?>><?php echo esc_html($site['name'] . ' — ' . __('Semua artikel', 'mh-healthmag')); ?></option>
                        <?php foreach ($categories as $id => $name) :
                            $value = $source . ':' . $id;
                            ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['feed'], $value); ?>><?php echo esc_html($name); ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($count_id); ?>"><?php esc_html_e('Jumlah artikel', 'mh-healthmag'); ?></label>
            <input class="widefat" type="number" min="1" max="<?php echo esc_attr(self::MAX_POSTS); ?>" step="1" id="<?php echo esc_attr($count_id); ?>" name="<?php echo esc_attr($this->get_field_name('count')); ?>" value="<?php echo esc_attr($settings['count']); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($thumbnails_id); ?>">
                <input type="checkbox" id="<?php echo esc_attr($thumbnails_id); ?>" name="<?php echo esc_attr($this->get_field_name('thumbnails')); ?>" value="1" <?php checked($settings['thumbnails']); ?>>
                <?php esc_html_e('Tampilkan thumbnail', 'mh-healthmag'); ?>
            </label>
        </p>
        <?php
    }

    public function widget($args, $instance) {
        $settings = $this->settings($instance);
        $posts = Reza_Ervani_Network_Feed::posts($settings['source'], $settings['category'], $settings['count']);
        if (!$posts) {
            if (($args['id'] ?? '') === self::HOME_AREA) {
                // Reserve this widget's desktop position even when its feed is empty.
                echo $args['before_widget'] . $args['after_widget'];
            }
            return;
        }

        $site = Reza_Ervani_Network_Feed::sources()[$settings['source']];
        $title = apply_filters('widget_title', $settings['title'] ?: $site['name'], $instance, $this->id_base);
        echo $args['before_widget'];
        if ($title !== '') :
            echo $args['before_title'];
            ?><a href="<?php echo esc_url($site['url']); ?>"><?php echo esc_html($title); ?></a><?php
            echo $args['after_title'];
        endif;
        ?>
        <ul class="reza-network-articles">
            <?php foreach ($posts as $index => $post) :
                $show_image = $settings['thumbnails'] && $post['image'];
                ?>
                <li class="reza-network-article">
                    <a href="<?php echo esc_url($post['link']); ?>">
                        <?php if ($show_image) : ?>
                            <img src="<?php echo esc_url($post['image']); ?>" alt="" width="80" height="54" loading="lazy" decoding="async">
                        <?php endif; ?>
                        <span>
                            <span class="reza-network-article-title"><?php echo esc_html($post['title']); ?></span>
                            <?php if ($index === 0 && !empty($post['summary'])) : ?>
                                <span class="reza-network-article-summary"><?php echo esc_html($post['summary']); ?></span>
                            <?php endif; ?>
                            <?php if ($post['timestamp'] !== false) : ?>
                                <time datetime="<?php echo esc_attr(gmdate(DATE_W3C, $post['timestamp'])); ?>"><?php echo esc_html(wp_date(get_option('date_format'), $post['timestamp'])); ?></time>
                            <?php endif; ?>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
        echo $args['after_widget'];
    }
}

function reza_ervani_register_network_feed_widget() {
    register_widget(Reza_Ervani_Network_Feed_Widget::class);
}
add_action('widgets_init', 'reza_ervani_register_network_feed_widget');
