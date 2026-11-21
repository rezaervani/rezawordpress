<?php

$slugs = reza_ervani_home_category_slugs(get_theme_mod('reza_ervani_home_categories', ''));
$categories = get_categories($slugs ? array(
    'slug' => $slugs,
    'orderby' => 'slug__in',
) : array(
    'parent' => 0,
    'orderby' => 'name',
    'pad_counts' => true,
));

if (!$categories) {
    return;
}
?>
<section class="reza-home-categories" aria-labelledby="reza-home-categories-title">
    <h2 id="reza-home-categories-title"><?php esc_html_e('Artikel per Kategori', 'mh-healthmag'); ?></h2>
    <div class="reza-home-category-grid">
        <?php foreach ($categories as $category) :
            $archive_url = get_category_link($category->term_id);
            if (is_wp_error($archive_url)) {
                continue;
            }

            $articles = new WP_Query(array(
                'cat' => $category->term_id,
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => 3,
                'ignore_sticky_posts' => true,
                'no_found_rows' => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            ));

            if (!$articles->have_posts()) {
                continue;
            }
            ?>
            <section class="mh-widget reza-home-category">
                <h3 class="mh-widget-title">
                    <span class="mh-widget-title-inner">
                        <a href="<?php echo esc_url($archive_url); ?>"><?php echo esc_html($category->name); ?></a>
                    </span>
                </h3>
                <ul>
                    <?php while ($articles->have_posts()) : $articles->the_post(); ?>
                        <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
                    <?php endwhile; ?>
                </ul>
            </section>
            <?php wp_reset_postdata(); ?>
        <?php endforeach; ?>
    </div>
</section>
