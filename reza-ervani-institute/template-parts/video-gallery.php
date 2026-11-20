<?php
$settings = $args['settings'];
?>
<div class="reza-video-grid">
    <?php foreach ($args['videos'] as $video) :
        $title = reza_ervani_editorial_title($video);
        $link = get_permalink($video);
        $image = reza_ervani_video_thumbnail($video->ID, 'medium_large', array('alt' => '', 'loading' => 'lazy'));
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
                $taxonomy = $video->post_type === 'post' ? 'category' : 'aiovg_categories';
                $terms = get_the_term_list($video->ID, $taxonomy, '', ', ');
                if ($terms && !is_wp_error($terms)) :
                    ?>
                    <p class="reza-video-meta"><?php echo wp_kses_post($terms); ?></p>
                <?php endif;
            endif; ?>
        </article>
    <?php endforeach; ?>
</div>
