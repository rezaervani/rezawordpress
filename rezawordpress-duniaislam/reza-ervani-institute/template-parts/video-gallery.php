<?php
$settings = $args['settings'];
?>
<div class="reza-video-grid">
    <?php foreach ($args['videos'] as $video) :
        $title = reza_ervani_editorial_title($video);
        $link = get_permalink($video);
        $image = get_the_post_thumbnail($video, 'medium_large', array('alt' => '', 'loading' => 'lazy'));
        if (!$image) {
            $poster = esc_url(get_post_meta($video->ID, '_reza_video_poster', true), array('http', 'https'));
            $image = $poster ? sprintf('<img src="%s" alt="" loading="lazy" decoding="async">', $poster) : '';
        }
        $duration = $settings['show_duration'] ? get_post_meta($video->ID, 'duration', true) : '';
        $views = $settings['show_views'] ? get_post_meta($video->ID, 'views', true) : '';
        ?>
        <article class="reza-video-card">
            <a class="reza-video-card-link" href="<?php echo esc_url($link); ?>">
                <span class="reza-video-preview">
                    <?php echo $image; ?>
                    <span class="reza-video-play" aria-hidden="true">▶</span>
                    <?php if ($duration !== '') : ?>
                        <span class="reza-video-duration"><?php echo esc_html($duration); ?></span>
                    <?php endif; ?>
                </span>
                <h3><?php echo esc_html($title); ?></h3>
            </a>
            <?php if ($views !== '') : ?>
                <p class="reza-video-meta"><?php echo esc_html(sprintf(__('%s tayangan', 'mh-healthmag'), number_format_i18n((int) $views))); ?></p>
            <?php endif; ?>
            <?php if ($settings['show_category']) :
                $terms = get_the_term_list($video->ID, 'category', '', ', ');
                if ($terms && !is_wp_error($terms)) :
                    ?>
                    <p class="reza-video-meta"><?php echo wp_kses_post($terms); ?></p>
                <?php endif;
            endif; ?>
        </article>
    <?php endforeach; ?>
</div>
