<main id="main-content" class="reza-editorial-wrap reza-editorial-main">
    <?php mh_before_page_content(); ?>
    <?php if (!is_paged()) : ?>
        <?php if (reza_ervani_home_segment_visible(Reza_Ervani_Smart_Ad::TOP_AREA)) : ?>
            <?php get_template_part('template-parts/article', 'advertisement', array(
                'area' => Reza_Ervani_Smart_Ad::TOP_AREA, 'editorial' => true,
            )); ?>
        <?php endif; ?>
        <?php
        $show_spotlight = reza_ervani_home_segment_visible(Reza_Ervani_Spotlight::HOME_AREA);
        if ($show_spotlight) {
            if (!Reza_Ervani_Spotlight::has_home_widget()) {
                the_widget(Reza_Ervani_Spotlight::class);
            }
            dynamic_sidebar(Reza_Ervani_Spotlight::HOME_AREA);
        }

        if (reza_ervani_home_segment_visible('home-categories')) {
            $featured = $show_spotlight ? Reza_Ervani_Spotlight::featured_post() : null;
            $collection_areas = array_filter(array('home-1', Reza_Ervani_Posting_Ads::HOME_AREA), 'reza_ervani_home_segment_visible');
            $grid_category_ids = reza_ervani_home_segment_visible(Reza_Ervani_Category_Grid::HOME_AREA)
                ? reza_ervani_home_widget_category_ids(Reza_Ervani_Category_Grid::ID_BASE, array(Reza_Ervani_Category_Grid::HOME_AREA))
                : array();
            $collection_category_ids = reza_ervani_home_widget_category_ids(Reza_Ervani_Posting_Ads::ID_BASE, $collection_areas);
            get_template_part('template-parts/editorial', 'categories', array(
                'featured_id' => $featured ? $featured->ID : 0,
                'excluded_category_ids' => array_filter(array_merge($grid_category_ids, $collection_category_ids)),
            ));
        }

        if (reza_ervani_home_segment_visible(Reza_Ervani_Palestina_Topics::HOME_AREA)
            && !dynamic_sidebar(Reza_Ervani_Palestina_Topics::HOME_AREA)
            && !get_option(REZA_ERVANI_PALESTINA_TOPICS_INITIALIZED)) {
            the_widget(Reza_Ervani_Palestina_Topics::class);
        }
        reza_ervani_home_sidebar(Reza_Ervani_Duniaislam_Topics::HOME_AREA);
        reza_ervani_home_sidebar(Reza_Ervani_Category_Grid::HOME_AREA);

        if (reza_ervani_home_segment_visible('home-network-ad')) {
            get_template_part('template-parts/article', 'advertisement', array('area' => 'home-network-ad', 'editorial' => true));
        }
        ?>

        <?php foreach (Reza_Ervani_Network_Feed_Widget::home_sections() as $area => $section) :
            if (!reza_ervani_home_sidebar_active($area)) {
                continue;
            }
            ?>
            <section class="reza-network-section" aria-labelledby="<?php echo esc_attr($section['heading_id']); ?>">
                <h2 id="<?php echo esc_attr($section['heading_id']); ?>" class="reza-network-section-title"><?php echo esc_html($section['title']); ?></h2>
                <div class="reza-editorial-network" style="--reza-network-tracks: <?php echo esc_attr(Reza_Ervani_Network_Feed_Widget::MAX_POSTS + 1); ?>"><?php dynamic_sidebar($area); ?></div>
            </section>
        <?php endforeach; ?>

        <?php if (reza_ervani_home_segment_visible(Reza_Ervani_Smart_Ad::MIDDLE_AREA)) : ?>
            <?php get_template_part('template-parts/editorial', 'ad'); ?>
        <?php endif; ?>
        <?php reza_ervani_home_sidebar('home-1'); ?>
        <?php reza_ervani_home_sidebar(Reza_Ervani_Posting_Ads::HOME_AREA); ?>
        <?php if (reza_ervani_home_sidebar_active('home-video-gallery')) : ?>
            <div class="reza-editorial-video-gallery">
                <?php dynamic_sidebar('home-video-gallery'); ?>
            </div>
        <?php endif; ?>
        <?php get_template_part('template-parts/home', 'category-widgets'); ?>
        <?php get_template_part('template-parts/home', 'widgets', array('editorial' => true)); ?>
        <?php reza_ervani_home_sidebar('home-latest-posts'); ?>
    <?php endif; ?>
    <?php if (is_home() && (is_paged() || reza_ervani_home_segment_visible('home-news'))) : ?>
        <?php get_template_part('template-parts/home', 'news'); ?>
    <?php endif; ?>
</main>
