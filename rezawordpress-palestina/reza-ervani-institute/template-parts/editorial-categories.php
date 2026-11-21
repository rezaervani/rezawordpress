<?php
$slugs = reza_ervani_home_category_slugs(get_theme_mod('reza_ervani_home_categories', reza_ervani_editorial_category_defaults()));
$wide_slugs = reza_ervani_home_category_slugs(get_theme_mod('reza_ervani_editorial_wide', reza_ervani_editorial_wide_defaults()));
$categories = get_categories($slugs ? array(
    'slug' => $slugs,
    'orderby' => 'slug__in',
) : array(
    'parent' => 0,
    'orderby' => 'name',
    'pad_counts' => true,
));

if (!empty($args['excluded_category_ids'])) {
    $excluded_ids = $args['excluded_category_ids'];
    $categories = array_filter($categories, static function ($category) use ($excluded_ids) {
        return !in_array($category->term_id, $excluded_ids, true);
    });
}

if (!$categories) {
    return;
}

$featured_id = $args['featured_id'];
$compact_count = reza_ervani_sanitize_home_article_count(get_theme_mod('reza_ervani_editorial_compact_count', 3));
$wide_count = reza_ervani_sanitize_home_article_count(get_theme_mod('reza_ervani_editorial_wide_count', 4));
$category_labels = array(
    'spain' => __('Artículos en español', 'mh-healthmag'),
    'sejarah-palestina' => __('Palestina', 'mh-healthmag'),
);

?>
<div class="reza-editorial-categories" style="--reza-compact-tracks: <?php echo esc_attr($compact_count + 2); ?>">
    <?php foreach ($categories as $category) : ?>
        <?php
        $wide = in_array($category->slug, $wide_slugs, true);
        $palestine = $category->slug === 'sejarah-palestina';
        $archive_url = get_category_link($category->term_id);
        $count = $wide ? ($palestine ? 6 : $wide_count) : $compact_count;
        $stories = reza_ervani_editorial_posts(array(
            'cat' => $category->term_id,
            'numberposts' => $count + (int) ($featured_id > 0),
        ));

        if ($featured_id && count($stories) > 1) {
            $stories = array_values(array_filter($stories, static function ($story) use ($featured_id) {
                return $story->ID !== $featured_id;
            }));
        }
        $stories = array_slice($stories, 0, $count);

        if (!$stories || is_wp_error($archive_url)) {
            continue;
        }

        $lead = $wide ? array_shift($stories) : null;
        $title_id = 'reza-category-' . $category->term_id;
        ?>
        <section class="reza-editorial-category<?php echo $wide ? ' reza-editorial-category-wide' : ''; ?><?php echo $palestine ? ' reza-editorial-category-palestine' : ''; ?>" aria-labelledby="<?php echo esc_attr($title_id); ?>">
            <header class="reza-editorial-category-header">
                <h2 id="<?php echo esc_attr($title_id); ?>">
                    <a href="<?php echo esc_url($archive_url); ?>"><?php echo esc_html($category_labels[$category->slug] ?? $category->name); ?></a>
                </h2>
                <?php if ($wide) : ?>
                    <a class="reza-editorial-archive-link" href="<?php echo esc_url($archive_url); ?>">
                        <?php esc_html_e('Lihat semua', 'mh-healthmag'); ?> <span aria-hidden="true">→</span>
                    </a>
                <?php endif; ?>
            </header>
            <div class="reza-editorial-category-content<?php echo $wide && $stories ? ' reza-editorial-category-content-split' : ''; ?>">
                <?php if ($lead) : ?>
                    <article class="reza-editorial-category-lead<?php echo has_post_thumbnail($lead) ? ' reza-editorial-category-lead-with-image' : ''; ?>">
                        <?php echo get_the_post_thumbnail($lead, 'mh-magazine-lite-medium', array('loading' => 'lazy')); ?>
                        <div>
                            <h3><a href="<?php echo esc_url(get_permalink($lead)); ?>"><?php echo esc_html(reza_ervani_editorial_title($lead)); ?></a></h3>
                            <?php $excerpt = wp_trim_words(wp_strip_all_tags(get_the_excerpt($lead)), 20, '…'); ?>
                            <?php if ($excerpt !== '') : ?>
                                <p><?php echo esc_html($excerpt); ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endif; ?>
                <?php if ($stories) : ?>
                    <ul>
                        <?php foreach ($stories as $story) : ?>
                            <li><a href="<?php echo esc_url(get_permalink($story)); ?>"><?php echo esc_html(reza_ervani_editorial_title($story)); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <?php if ($palestine) : ?>
                <div class="reza-editorial-category-resources">
                    <a href="<?php echo esc_url(home_url('/timeline/')); ?>"><?php esc_html_e('Timeline Palestina', 'mh-healthmag'); ?></a>
                    <a href="<?php echo esc_url(home_url('/palestina/peta')); ?>"><?php esc_html_e('Peta Palestina', 'mh-healthmag'); ?></a>
                </div>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
</div>
