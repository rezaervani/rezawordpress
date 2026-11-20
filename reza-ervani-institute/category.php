<?php
get_header();
$category = get_queried_object();
$ancestors = array_reverse(get_ancestors($category->term_id, 'category', 'taxonomy'));
$children = get_categories(array(
    'parent' => $category->term_id,
    'orderby' => 'name',
    'pad_counts' => true,
));
?>
<main id="main-content" class="reza-editorial-wrap reza-category-archive">
    <?php mh_before_page_content(); ?>
    <header class="reza-archive-header">
        <nav class="reza-archive-path" aria-label="<?php esc_attr_e('Jejak navigasi', 'mh-healthmag'); ?>">
            <ul>
                <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Beranda', 'mh-healthmag'); ?></a></li>
                <?php foreach ($ancestors as $ancestor_id) :
                    $ancestor = get_term($ancestor_id, 'category');
                    if (!$ancestor || is_wp_error($ancestor)) {
                        continue;
                    }
                    $url = get_category_link($ancestor->term_id);
                    if (!is_wp_error($url)) : ?>
                        <li><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($ancestor->name); ?></a></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </nav>
        <h1><?php echo esc_html(single_cat_title('', false)); ?></h1>
        <?php the_archive_description('<div class="reza-archive-description">', '</div>'); ?>
        <?php if ($children) : ?>
            <nav class="reza-archive-subcategories" aria-label="<?php esc_attr_e('Subkategori', 'mh-healthmag'); ?>">
                <ul>
                    <?php foreach ($children as $child) :
                        $url = get_category_link($child->term_id);
                        if (!is_wp_error($url)) : ?>
                            <li><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($child->name); ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </header>

    <div class="reza-archive-feed">
        <?php if (have_posts()) : ?>
            <?php if (!is_paged()) :
                $remaining_posts = $wp_query->post_count - 1;
                $companion_count = min(3, $remaining_posts);
                $top_grid_count = min(4, $remaining_posts - $companion_count);
                ?>
                <div class="reza-archive-spread<?php echo $companion_count ? ' reza-archive-spread-with-companions' : ''; ?>">
                    <?php
                    the_post();
                    get_template_part('template-parts/category', 'story', array('featured' => true));
                    ?>
                    <?php if ($companion_count) : ?>
                        <div class="reza-archive-companions">
                            <?php for ($index = 0; $index < $companion_count; $index++) :
                                the_post();
                                get_template_part('template-parts/category', 'story', array('show_image' => $index === 0));
                            endfor; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($top_grid_count) : ?>
                        <div class="reza-archive-grid reza-archive-grid-top">
                            <?php for ($index = 0; $index < $top_grid_count; $index++) :
                                the_post();
                                get_template_part('template-parts/category', 'story');
                            endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if (have_posts()) : ?>
                <div class="reza-archive-grid">
                    <?php while (have_posts()) :
                        the_post();
                        get_template_part('template-parts/category', 'story');
                    endwhile; ?>
                </div>
            <?php endif; ?>
            <?php the_posts_pagination(array(
                'mid_size' => 1,
                'prev_text' => __('Sebelumnya', 'mh-healthmag'),
                'next_text' => __('Berikutnya', 'mh-healthmag'),
                'screen_reader_text' => __('Navigasi arsip kategori', 'mh-healthmag'),
            )); ?>
        <?php else : ?>
            <p class="reza-archive-empty"><?php esc_html_e('Belum ada artikel di kategori ini.', 'mh-healthmag'); ?></p>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
