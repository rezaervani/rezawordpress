<?php
get_header();
$category = get_queried_object();
$ancestors = array_reverse(get_ancestors($category->term_id, 'category', 'taxonomy'));
$children = get_categories(array(
    'parent' => $category->term_id,
    'orderby' => 'name',
    'pad_counts' => true,
));
$previewed_posts = array();
$topic_sections = array();
if ($children && !is_paged()) {
    require_once __DIR__ . '/includes/category-archive.php';
    $topic_sections = reza_ervani_archive_topic_sections($category->term_id, $previewed_posts);
}
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
            <?php if ($topic_sections) :
                get_template_part('template-parts/category', 'topics', array('sections' => $topic_sections));
                wp_reset_postdata();
            elseif (!is_paged()) :
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
            <?php
            $has_remaining_posts = !$topic_sections || array_diff(wp_list_pluck($wp_query->posts, 'ID'), array_keys($previewed_posts));
            if ($has_remaining_posts && have_posts()) : ?>
                <?php if ($topic_sections) : ?>
                    <h2 class="reza-archive-more-title"><?php esc_html_e('Artikel lainnya', 'mh-healthmag'); ?></h2>
                <?php endif; ?>
                <div class="reza-archive-grid">
                    <?php while (have_posts()) :
                        the_post();
                        if (isset($previewed_posts[get_the_ID()])) {
                            continue;
                        }
                        get_template_part('template-parts/category', 'story', array('heading_level' => $topic_sections ? 3 : 2));
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
