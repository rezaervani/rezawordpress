<?php
$lead = $args['stories'][0];
$columns = array(array_slice($args['stories'], 1, 3));
$second_list = array_slice($args['stories'], 4, 3);
$title_id = $args['title_id'];
$archive_url = get_category_link($args['category_id']);
$image = get_the_post_thumbnail($lead, 'medium_large', array(
    'class' => 'reza-editorial-collection-image',
    'loading' => 'lazy',
));
if ($image !== '' || $second_list) {
    $columns[] = $second_list;
}

$excerpt = function_exists('pms_is_post_restricted') && pms_is_post_restricted($lead->ID)
    ? __('Konten premium', 'mh-healthmag')
    : wp_trim_words(wp_strip_all_tags(get_the_excerpt($lead)), 29, '…');
?>
<section class="reza-editorial-collection reza-editorial-collection-with-ad-column" aria-labelledby="<?php echo esc_attr($title_id); ?>">
    <div class="reza-editorial-collection-content">
        <header class="reza-editorial-collection-header">
            <h2 id="<?php echo esc_attr($title_id); ?>"><?php echo esc_html($args['title']); ?></h2>
            <?php if (!is_wp_error($archive_url)) : ?>
                <a class="reza-editorial-archive-link" href="<?php echo esc_url($archive_url); ?>">
                    <?php esc_html_e('Lihat semua', 'mh-healthmag'); ?> <span aria-hidden="true">→</span>
                </a>
            <?php endif; ?>
        </header>
        <div class="reza-editorial-collection-layout<?php echo count($columns) > 1 ? ' reza-editorial-collection-layout-split' : ''; ?>">
            <?php foreach ($columns as $index => $stories) : ?>
                <div class="reza-editorial-collection-column">
                    <?php if ($index === 0) : ?>
                        <article class="reza-editorial-collection-lead">
                            <h3><a href="<?php echo esc_url(get_permalink($lead)); ?>"><?php echo esc_html(reza_ervani_editorial_title($lead)); ?></a></h3>
                            <?php if ($excerpt !== '') : ?>
                                <p><?php echo esc_html($excerpt); ?></p>
                            <?php endif; ?>
                        </article>
                    <?php elseif ($image !== '') : ?>
                        <div class="reza-editorial-collection-media"><?php echo $image; ?></div>
                    <?php endif; ?>
                    <?php if ($stories) : ?>
                        <ul class="reza-editorial-collection-support">
                            <?php foreach ($stories as $story) : ?>
                                <li><a href="<?php echo esc_url(get_permalink($story)); ?>"><?php echo esc_html(reza_ervani_editorial_title($story)); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php if ($args['ad_html'] !== '') : ?>
        <aside class="reza-editorial-collection-ad" aria-label="<?php echo esc_attr(sprintf(__('Iklan %s', 'mh-healthmag'), $args['title'])); ?>">
            <div class="mh-widget">
                <?php if ($args['ad_title'] !== '') : ?>
                    <h3 class="mh-widget-title"><span class="mh-widget-title-inner"><?php echo esc_html($args['ad_title']); ?></span></h3>
                <?php endif; ?>
                <div class="textwidget custom-html-widget"><?php echo $args['ad_html']; ?></div>
            </div>
        </aside>
    <?php endif; ?>
</section>
