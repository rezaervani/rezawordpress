<main id="main-content" class="reza-editorial-wrap reza-article-layout" itemprop="mainContentOfPage">
    <?php while (have_posts()) : the_post(); ?>
        <?php get_template_part('template-parts/article', 'advertisement', array('area' => 'posts-1')); ?>
        <div class="reza-article-reading">
            <?php mh_before_post_content(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('reza-article'); ?>>
                <header class="entry-header reza-article-header">
                    <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                    <?php mh_post_header(); ?>
                </header>
                <div class="entry-content mh-clearfix reza-article-content">
                    <?php if (!post_password_required() && has_post_thumbnail() && absint(get_query_var('page')) < 2) : ?>
                        <figure class="reza-article-hero">
                            <?php the_post_thumbnail('large'); ?>
                            <?php $caption = get_the_post_thumbnail_caption(); ?>
                            <?php if ($caption) : ?>
                                <figcaption><?php echo wp_kses_post($caption); ?></figcaption>
                            <?php endif; ?>
                        </figure>
                    <?php endif; ?>
                    <?php the_content(); ?>
                    <?php if (!post_password_required()) : ?>
                        <?php foreach (reza_ervani_article_inline_ad_areas() as $area => $minimum_words) : ?>
                            <?php get_template_part('template-parts/article', 'advertisement', array('area' => $area, 'minimum_words' => $minimum_words)); ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php the_tags('<div class="entry-tags mh-clearfix"><i class="fa fa-tag"></i><ul><li>', '</li><li>', '</li></ul></div>'); ?>
            </article>
        </div>
        <?php get_template_part('template-parts/article', 'advertisement', array('area' => 'posts-2')); ?>
        <div class="reza-article-reading">
            <?php mh_after_post_content(); ?>
        </div>
        <?php get_template_part('template-parts/article', 'related'); ?>
        <div class="reza-article-reading reza-article-comments">
            <?php comments_template(); ?>
        </div>
    <?php endwhile; ?>
</main>
