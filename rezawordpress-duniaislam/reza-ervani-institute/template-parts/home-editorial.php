<main id="main-content" class="reza-editorial-wrap reza-editorial-main">
    <?php mh_before_page_content(); ?>
    <?php if (!is_paged()) : ?>
        <?php
        if (!Reza_Ervani_Spotlight::has_home_widget()) {
            the_widget(Reza_Ervani_Spotlight::class);
        }
        dynamic_sidebar(Reza_Ervani_Spotlight::HOME_AREA);
        $show_widgets = get_theme_mod('reza_ervani_editorial_widgets', true);
        ?>
        <?php if (get_theme_mod('reza_ervani_show_home_categories', true)) :
            $featured = Reza_Ervani_Spotlight::featured_post();
            $grid_category_ids = reza_ervani_home_widget_category_ids(Reza_Ervani_Category_Grid::ID_BASE, array(Reza_Ervani_Category_Grid::HOME_AREA));
            $collection_category_ids = $show_widgets ? reza_ervani_home_posting_ads_categories() : array();
            get_template_part('template-parts/editorial', 'categories', array(
                'featured_id' => $featured ? $featured->ID : 0,
                'excluded_category_ids' => array_filter(array_merge($grid_category_ids, $collection_category_ids)),
            ));
        endif;
        if (!dynamic_sidebar(Reza_Ervani_Home_Topics::HOME_AREA) && !get_option(Reza_Ervani_Home_Topics::MIGRATION_OPTION)) :
            the_widget(Reza_Ervani_Home_Topics::class);
        endif; ?>
        <?php dynamic_sidebar(Reza_Ervani_Category_Grid::HOME_AREA); ?>
        <?php get_template_part('template-parts/editorial', 'ad'); ?>
        <?php if ($show_widgets) :
            dynamic_sidebar('home-1');
            dynamic_sidebar(Reza_Ervani_Posting_Ads::HOME_AREA);
            get_template_part('template-parts/home', 'category-widgets');
            if (!is_home()) {
                dynamic_sidebar('home-latest-posts');
            }
        endif; ?>
    <?php endif; ?>
    <?php if (is_home()) : ?>
        <?php get_template_part('template-parts/home', 'news'); ?>
    <?php endif; ?>
</main>
