<?php
$featured = $args['featured'];
$feed = $args['feed'];

if (!$featured) {
    return;
}

$latest = array_slice($feed, 0, 5);
$supporting = array_slice($feed, 5, 2);
$has_image = has_post_thumbnail($featured);
?>
<div class="reza-editorial-opening<?php echo $latest ? ' reza-editorial-opening-with-latest' : ''; ?>">
    <section class="reza-editorial-spotlight" aria-labelledby="reza-spotlight-title">
        <h2 id="reza-spotlight-title" class="reza-editorial-label"><?php esc_html_e('Sorotan Utama', 'mh-healthmag'); ?></h2>
        <article class="reza-editorial-lead<?php echo $has_image ? ' reza-editorial-lead-with-image' : ''; ?>">
            <div class="reza-editorial-lead-copy">
                <h3><a href="<?php echo esc_url(get_permalink($featured)); ?>"><?php echo esc_html(reza_ervani_editorial_title($featured)); ?></a></h3>
                <?php $excerpt = wp_trim_words(wp_strip_all_tags(get_the_excerpt($featured)), 32, '…'); ?>
                <?php if ($excerpt !== '') : ?>
                    <p><?php echo esc_html($excerpt); ?></p>
                <?php endif; ?>
                <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $featured)); ?>">
                    <?php echo esc_html(get_the_date('', $featured)); ?>
                </time>
            </div>
            <?php if ($has_image) : ?>
                <div class="reza-editorial-lead-media">
                    <?php echo get_the_post_thumbnail($featured, 'mh-magazine-lite-content', array('loading' => 'eager', 'fetchpriority' => 'high')); ?>
                </div>
            <?php endif; ?>
        </article>
        <?php if ($supporting) : ?>
            <ul class="reza-editorial-support">
                <?php foreach ($supporting as $story) : ?>
                    <li><a href="<?php echo esc_url(get_permalink($story)); ?>"><?php echo esc_html(reza_ervani_editorial_title($story)); ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
    <?php if ($latest) : ?>
        <section class="reza-editorial-latest" aria-labelledby="reza-latest-title">
            <h2 id="reza-latest-title" class="reza-editorial-label"><?php esc_html_e('Terbaru', 'mh-healthmag'); ?></h2>
            <ul>
                <?php foreach ($latest as $story) : ?>
                    <li>
                        <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $story)); ?>">
                            <?php echo esc_html(get_the_date('j M', $story)); ?>
                        </time>
                        <a href="<?php echo esc_url(get_permalink($story)); ?>"><?php echo esc_html(reza_ervani_editorial_title($story)); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
</div>
