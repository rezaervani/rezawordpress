<?php

class Reza_Ervani_Smart_Ad extends WP_Widget {
    const TOP_AREA = 'home-top-ad';
    const MIDDLE_AREA = 'home-middle-ad';
    const MAX_IMAGES = 3;

    public function __construct() {
        parent::__construct('reza_smart_ad', __('Iklan Cerdas', 'mh-healthmag'), array(
            'classname' => 'reza-smart-ad-widget',
            'description' => __('Tampilkan AdSense Display atau satu dari maksimal tiga gambar cadangan.', 'mh-healthmag'),
        ));
    }

    public static function adsense_unit($code) {
        $tags = new WP_HTML_Tag_Processor($code);
        if (!$tags->next_tag(array('tag_name' => 'INS', 'class_name' => 'adsbygoogle'))) {
            return array();
        }

        $client = reza_ervani_sanitize_adsense_publisher($tags->get_attribute('data-ad-client'));
        $slot = reza_ervani_sanitize_adsense_slot($tags->get_attribute('data-ad-slot'));
        if (!$client || !$slot) {
            return array();
        }

        $unit = array('data-ad-client' => $client, 'data-ad-slot' => $slot);
        $style = (string) $tags->get_attribute('style');
        if (preg_match('/(?:^|;)\s*width\s*:\s*([1-9]\d{0,3})px\s*(?:;|$)/i', $style, $width)
            && preg_match('/(?:^|;)\s*height\s*:\s*([1-9]\d{0,3})px\s*(?:;|$)/i', $style, $height)) {
            return $unit + array('data-width' => $width[1], 'data-height' => $height[1]);
        }

        $format = $tags->get_attribute('data-ad-format');
        return $unit + array(
            'data-ad-format' => in_array($format, array('auto', 'horizontal', 'vertical', 'rectangle'), true) ? $format : 'auto',
            'data-full-width-responsive' => $tags->get_attribute('data-full-width-responsive') === 'false' ? 'false' : 'true',
        );
    }

    private function images($instance) {
        $images = array();
        for ($i = 0; $i < self::MAX_IMAGES; $i++) {
            $image = $instance['images'][$i] ?? array();
            $images[] = array(
                'src' => esc_url_raw((string) ($image['src'] ?? ''), array('http', 'https')),
                'link' => esc_url_raw((string) ($image['link'] ?? ''), array('http', 'https')),
                'alt' => sanitize_text_field($image['alt'] ?? ''),
            );
        }
        return $images;
    }

    public function update($new_instance, $old_instance) {
        return array(
            'adsense' => (string) ($new_instance['adsense'] ?? ''),
            'images' => $this->images($new_instance),
        );
    }

    public function form($instance) {
        $code = $instance['adsense'] ?? '';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('adsense')); ?>"><?php esc_html_e('Kode unit AdSense Display', 'mh-healthmag'); ?></label>
            <textarea class="widefat code" rows="7" id="<?php echo esc_attr($this->get_field_id('adsense')); ?>"
                name="<?php echo esc_attr($this->get_field_name('adsense')); ?>"><?php echo esc_textarea($code); ?></textarea>
            <span class="description"><?php esc_html_e('Tempel kode unit yang memuat data-ad-client dan data-ad-slot. Kosongkan untuk memakai gambar saja.', 'mh-healthmag'); ?></span>
        </p>
        <?php if ($code !== '' && !self::adsense_unit($code)) : ?>
            <p><?php esc_html_e('Kode ini belum memuat unit AdSense Display yang valid.', 'mh-healthmag'); ?></p>
        <?php endif; ?>
        <?php foreach ($this->images($instance) as $index => $image) : ?>
            <fieldset class="reza-smart-ad-fields">
                <legend><?php echo esc_html(sprintf(__('Gambar cadangan %d', 'mh-healthmag'), $index + 1)); ?></legend>
                <p>
                    <label for="<?php echo esc_attr($this->get_field_id("images[$index][src]")); ?>"><?php esc_html_e('Alamat gambar', 'mh-healthmag'); ?></label>
                    <input class="widefat" type="url" data-reza-smart-image id="<?php echo esc_attr($this->get_field_id("images[$index][src]")); ?>"
                        name="<?php echo esc_attr($this->get_field_name("images[$index][src]")); ?>" value="<?php echo esc_attr($image['src']); ?>">
                </p>
                <p><button type="button" class="button" data-reza-smart-pick><?php esc_html_e('Pilih dari Pustaka Media', 'mh-healthmag'); ?></button></p>
                <p>
                    <label for="<?php echo esc_attr($this->get_field_id("images[$index][link]")); ?>"><?php esc_html_e('Tautan tujuan (opsional)', 'mh-healthmag'); ?></label>
                    <input class="widefat" type="url" id="<?php echo esc_attr($this->get_field_id("images[$index][link]")); ?>"
                        name="<?php echo esc_attr($this->get_field_name("images[$index][link]")); ?>" value="<?php echo esc_attr($image['link']); ?>">
                </p>
                <p>
                    <label for="<?php echo esc_attr($this->get_field_id("images[$index][alt]")); ?>"><?php esc_html_e('Teks alternatif', 'mh-healthmag'); ?></label>
                    <input class="widefat" type="text" data-reza-smart-alt id="<?php echo esc_attr($this->get_field_id("images[$index][alt]")); ?>"
                        name="<?php echo esc_attr($this->get_field_name("images[$index][alt]")); ?>" value="<?php echo esc_attr($image['alt']); ?>">
                </p>
            </fieldset>
        <?php endforeach;
    }

    public function widget($args, $instance) {
        $unit = is_customize_preview() ? array() : self::adsense_unit($instance['adsense'] ?? '');
        $fallbacks = array_filter($this->images($instance), static function ($image) {
            return $image['src'] !== '';
        });
        if (!$unit && !$fallbacks) {
            return;
        }

        $dependencies = wp_script_is('reza-ervani-article-ads', 'enqueued') ? array('reza-ervani-article-ads') : array();
        wp_enqueue_script('reza-ervani-smart-ad', get_stylesheet_directory_uri() . '/js/smart-ad.js', $dependencies, wp_get_theme()->get('Version'), true);

        echo $args['before_widget'];
        ?>
        <div class="reza-smart-ad" data-reza-smart-ad data-ad-state="<?php echo $fallbacks ? 'fallback' : 'empty'; ?>">
            <?php if ($unit) : ?>
                <div class="reza-smart-ad-unit" hidden
                    <?php foreach ($unit as $name => $value) : ?>
                        <?php echo esc_attr($name); ?>="<?php echo esc_attr($value); ?>"
                    <?php endforeach; ?>></div>
            <?php endif; ?>
            <?php foreach (array_values($fallbacks) as $index => $image) : ?>
                <div class="reza-smart-ad-fallback"<?php if ($index !== 0) { echo ' hidden'; } ?>>
                    <?php if ($image['link'] !== '') : ?>
                        <a href="<?php echo esc_url($image['link']); ?>" rel="sponsored">
                    <?php endif; ?>
                    <img src="<?php echo esc_url($image['src']); ?>" alt="<?php echo esc_attr($image['alt'] ?: __('Iklan', 'mh-healthmag')); ?>" loading="lazy" decoding="async">
                    <?php if ($image['link'] !== '') : ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        echo $args['after_widget'];
    }
}

add_action('widgets_init', static function () {
    register_widget(Reza_Ervani_Smart_Ad::class);
});

function reza_ervani_enqueue_smart_ad_media() {
    wp_enqueue_media();
    wp_enqueue_script('reza-ervani-smart-ad-admin', get_stylesheet_directory_uri() . '/js/smart-ad-admin.js',
        array('media-editor'), wp_get_theme()->get('Version'), true);
}
add_action('admin_enqueue_scripts', static function ($hook) {
    if ($hook === 'widgets.php') {
        reza_ervani_enqueue_smart_ad_media();
    }
});
add_action('customize_controls_enqueue_scripts', 'reza_ervani_enqueue_smart_ad_media');
