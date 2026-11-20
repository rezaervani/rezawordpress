<?php
$featured = $args['featured'];
if (!$featured) {
    return;
}

$latest = $args['latest'];
$has_image = has_post_thumbnail($featured);
$columns = array($args['left']);
if ($has_image || $args['photo']) {
    $columns[] = $args['photo'];
}
$spotlight_id = 'reza-spotlight-title-' . $args['widget_id'];
$latest_id = 'reza-latest-title-' . $args['widget_id'];
?>
<div class="reza-editorial-opening<?php echo $latest ? ' reza-editorial-opening-with-latest' : ''; ?>">
    <section class="reza-editorial-spotlight" aria-labelledby="<?php echo esc_attr($spotlight_id); ?>">
        <h2 id="<?php echo esc_attr($spotlight_id); ?>" class="reza-editorial-label"><?php esc_html_e('Sorotan Utama', 'mh-healthmag'); ?></h2>
        <article class="reza-editorial-lead<?php echo count($columns) > 1 ? ' reza-editorial-lead-split' : ''; ?>">
            <?php foreach ($columns as $index => $stories) : ?>
                <div class="reza-editorial-lead-column">
                    <?php if ($index === 0) : ?>
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
                    <?php elseif ($has_image) : ?>
                        <div class="reza-editorial-lead-media">
                            <?php echo get_the_post_thumbnail($featured, 'mh-magazine-lite-content', array('loading' => 'eager', 'fetchpriority' => 'high')); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($stories) : ?>
                        <ul class="reza-editorial-support">
                            <?php foreach ($stories as $story) : ?>
                                <li><a href="<?php echo esc_url(get_permalink($story)); ?>"><?php echo esc_html(reza_ervani_editorial_title($story)); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </article>
    </section>
    <?php if ($latest) : ?>
        <section class="reza-editorial-latest" aria-labelledby="<?php echo esc_attr($latest_id); ?>">
            <h2 id="<?php echo esc_attr($latest_id); ?>" class="reza-editorial-label"><?php esc_html_e('Terbaru', 'mh-healthmag'); ?></h2>
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
