<?php get_header(); ?>
<main id="main-content" class="reza-editorial-wrap reza-page-layout" itemprop="mainContentOfPage">
    <?php while (have_posts()) : the_post(); ?>
        <?php mh_before_page_content(); ?>
        <?php get_template_part('content', 'page'); ?>
        <?php if (!post_password_required() && (comments_open() || get_comments_number())) : ?>
            <section class="reza-page-comments reza-article-comments" aria-label="<?php esc_attr_e('Komentar', 'mh-healthmag'); ?>">
                <?php comments_template(); ?>
            </section>
        <?php endif; ?>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
