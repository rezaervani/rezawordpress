<?php /* Template Name: Homepage */ ?>
<?php get_header(); ?>
<?php if (reza_ervani_uses_editorial_home()) : ?>
    <?php get_template_part('template-parts/home', 'editorial'); ?>
<?php else : ?>
    <div class="mh-wrapper mh-home mh-clearfix">
        <?php reza_ervani_home_sidebar(Reza_Ervani_Spotlight::HOME_AREA); ?>
        <?php reza_ervani_home_sidebar('home-1'); ?>
        <?php reza_ervani_home_sidebar(Reza_Ervani_Posting_Ads::HOME_AREA); ?>
        <main id="main-content" class="mh-home-content">
            <?php reza_ervani_home_sidebar(Reza_Ervani_Category_Grid::HOME_AREA); ?>
            <?php get_template_part('template-parts/home', 'category-widgets'); ?>
            <?php get_template_part('template-parts/home', 'widgets'); ?>
            <?php if (reza_ervani_home_segment_visible('home-categories')) : ?>
                <?php get_template_part('template-parts/home', 'categories'); ?>
            <?php endif; ?>
            <?php reza_ervani_home_sidebar('home-latest-posts'); ?>
        </main>
    </div>
<?php endif; ?>
<?php get_footer(); ?>
