<?php
list($lead, $support, $aside) = array_pad($args['stories'], 3, null);
$title_id = 'reza-editorial-collection-' . $args['category_id'];
$archive_url = get_category_link($args['category_id']);
$image = get_the_post_thumbnail($lead, 'medium_large', array(
    'class' => 'reza-editorial-collection-image',
    'loading' => 'lazy',
));

$excerpt = function_exists('pms_is_post_restricted') && pms_is_post_restricted($lead->ID)
    ? __('Konten premium', 'mh-healthmag')
    : wp_trim_words(wp_strip_all_tags(get_the_excerpt($lead)), 29, '…');
?>
<section class="reza-editorial-collection" aria-labelledby="<?php echo esc_attr($title_id); ?>">
    <header class="reza-editorial-collection-header">
        <h2 id="<?php echo esc_attr($title_id); ?>"><?php echo esc_html($args['title']); ?></h2>
        <?php if (!is_wp_error($archive_url)) : ?>
            <a class="reza-editorial-archive-link" href="<?php echo esc_url($archive_url); ?>">
                <?php esc_html_e('Lihat semua', 'mh-healthmag'); ?> <span aria-hidden="true">→</span>
            </a>
        <?php endif; ?>
    </header>
    <div class="reza-editorial-collection-layout<?php echo $image ? ' reza-editorial-collection-with-image' : ''; ?><?php echo $aside ? ' reza-editorial-collection-with-aside' : ''; ?>">
        <article class="reza-editorial-collection-lead">
            <h3><a href="<?php echo esc_url(get_permalink($lead)); ?>"><?php echo esc_html(reza_ervani_editorial_title($lead)); ?></a></h3>
            <?php if ($excerpt !== '') : ?>
                <p><?php echo esc_html($excerpt); ?></p>
            <?php endif; ?>
        </article>
        <?php if ($image) : ?>
            <div class="reza-editorial-collection-media"><?php echo $image; ?></div>
        <?php endif; ?>
        <?php if ($support) : ?>
            <article class="reza-editorial-collection-support">
                <h3><a href="<?php echo esc_url(get_permalink($support)); ?>"><?php echo esc_html(reza_ervani_editorial_title($support)); ?></a></h3>
            </article>
        <?php endif; ?>
        <?php if ($aside) : ?>
            <article class="reza-editorial-collection-aside">
                <h3><a href="<?php echo esc_url(get_permalink($aside)); ?>"><?php echo esc_html(reza_ervani_editorial_title($aside)); ?></a></h3>
            </article>
        <?php endif; ?>
    </div>
</section>
