<?php
$page_id = get_the_ID();
$protected = post_password_required();
$ancestors = array_filter(array_reverse(get_post_ancestors($page_id)), static function ($ancestor) {
    return (int) $ancestor !== (int) get_option('page_on_front') && get_post_status($ancestor) === 'publish';
});
$excerpt = !$protected && has_excerpt() ? trim(wp_strip_all_tags(get_the_excerpt())) : '';
$children = $protected ? '' : wp_list_pages(array(
    'child_of' => $page_id,
    'depth' => 1,
    'title_li' => '',
    'sort_column' => 'menu_order,post_title',
    'echo' => false,
));
?>
<article id="page-<?php the_ID(); ?>" <?php post_class('reza-page'); ?>>
    <header class="reza-page-header reza-article-header">
        <?php if ($ancestors) : ?>
            <nav class="reza-page-path" aria-label="<?php esc_attr_e('Lokasi halaman', 'mh-healthmag'); ?>">
                <ol>
                    <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Beranda', 'mh-healthmag'); ?></a></li>
                    <?php foreach ($ancestors as $ancestor) : ?>
                        <li><a href="<?php echo esc_url(get_permalink($ancestor)); ?>"><?php echo esc_html(reza_ervani_editorial_title($ancestor)); ?></a></li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>
        <h1 class="entry-title"><?php echo esc_html(reza_ervani_editorial_title(get_post())); ?></h1>
        <?php if ($excerpt !== '') : ?>
            <p class="reza-page-summary"><?php echo esc_html($excerpt); ?></p>
        <?php endif; ?>
    </header>

    <?php if (!$protected && has_post_thumbnail()) :
        $caption = get_the_post_thumbnail_caption();
        ?>
        <figure class="reza-page-hero reza-article-hero">
            <?php the_post_thumbnail('large'); ?>
            <?php if ($caption) : ?>
                <figcaption><?php echo esc_html($caption); ?></figcaption>
            <?php endif; ?>
        </figure>
    <?php endif; ?>

    <div class="entry-content reza-page-content reza-article-content mh-clearfix">
        <?php the_content(); ?>
    </div>

    <?php wp_link_pages(array(
        'before' => '<nav class="reza-page-pagination" aria-label="' . esc_attr__('Navigasi halaman', 'mh-healthmag') . '">',
        'after' => '</nav>',
    )); ?>

    <?php if ($children) : ?>
        <nav class="reza-page-children" aria-labelledby="reza-page-children-<?php the_ID(); ?>">
            <h2 id="reza-page-children-<?php the_ID(); ?>"><?php esc_html_e('Halaman dalam bagian ini', 'mh-healthmag'); ?></h2>
            <ul><?php echo $children; ?></ul>
        </nav>
    <?php endif; ?>
</article>
