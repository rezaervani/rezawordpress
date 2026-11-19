<?php /* Template Name: Homepage */ ?>
<?php get_header(); ?>
<?php if (reza_ervani_uses_editorial_home()) : ?>
    <?php get_template_part('template-parts/home', 'editorial'); ?>
<?php else : ?>
    <div class="mh-wrapper mh-home mh-clearfix">
        <?php dynamic_sidebar('home-1'); ?>
        <main id="main-content" class="mh-home-content">
            <?php get_template_part('template-parts/home', 'widgets'); ?>
            <?php if (get_theme_mod('reza_ervani_show_home_categories', false)) : ?>
                <?php get_template_part('template-parts/home', 'categories'); ?>
            <?php endif; ?>
        </main>
    </div>
<?php endif; ?>
<?php get_footer(); ?>
