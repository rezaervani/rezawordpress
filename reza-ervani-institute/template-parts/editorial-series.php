<?php
$category = $args['category'];
$archive_url = get_category_link($category->term_id);
?>
<section class="reza-editorial-series" aria-labelledby="reza-editorial-series-title">
    <header class="reza-editorial-series-header">
        <h2 id="reza-editorial-series-title"><?php echo esc_html($category->name); ?></h2>
        <?php if (!is_wp_error($archive_url)) : ?>
            <a class="reza-editorial-archive-link" href="<?php echo esc_url($archive_url); ?>">
                <?php esc_html_e('Lihat semua', 'mh-healthmag'); ?> <span aria-hidden="true">→</span>
            </a>
        <?php endif; ?>
    </header>
    <ul class="reza-editorial-series-grid">
        <?php foreach ($args['stories'] as $story) : ?>
            <li class="reza-editorial-series-card">
                <a href="<?php echo esc_url(get_permalink($story)); ?>">
                    <?php echo get_the_post_thumbnail($story, 'medium_large', array(
                        'class' => 'reza-editorial-series-image',
                        'loading' => 'lazy',
                    )); ?>
                    <h3><?php echo esc_html(reza_ervani_editorial_title($story)); ?></h3>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
