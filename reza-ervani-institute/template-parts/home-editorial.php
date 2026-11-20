<?php
$grid_category_ids = reza_ervani_home_widget_category_ids(Reza_Ervani_Category_Grid::ID_BASE, array(Reza_Ervani_Category_Grid::HOME_AREA));
$fatwa_series = reza_ervani_editorial_series('fatwa-lajnah-daimah-saudi-arabia', __('Fatwa Lajnah Daimah', 'mh-healthmag'));
$series = array_filter(array(
    reza_ervani_editorial_series('fiqh-muyassar'),
    reza_ervani_editorial_series('fathul-qarib-al-mujib', __('Fathul Qarib al-Mujib', 'mh-healthmag')),
    $fatwa_series,
));
$manuscripts = reza_ervani_editorial_series('manuskrip', __('Makhthuthah', 'mh-healthmag'));
$old_fatwa_category = $fatwa_series ? get_category_by_slug('fatwa') : false;
$series_category_ids = array_filter(array_merge(array_column($series, 'category_id'), array(
    $manuscripts['category_id'] ?? 0,
    $old_fatwa_category ? (int) $old_fatwa_category->term_id : 0,
)));
$show_widgets = get_theme_mod('reza_ervani_editorial_widgets', true);
$collection_category_ids = $show_widgets ? reza_ervani_home_posting_ads_categories() : array();
$english_sections = $show_widgets ? array_filter(array(
    reza_ervani_editorial_series('aqeedah'),
    reza_ervani_editorial_series('tadabbur-al-quran-in-english'),
    reza_ervani_editorial_series('hadith-explanation'),
    reza_ervani_editorial_series('islamic-question-answer'),
)) : array();
$english_category_ids = array_column($english_sections, 'category_id');
?>
<main id="main-content" class="reza-editorial-wrap reza-editorial-main">
    <?php dynamic_sidebar(Reza_Ervani_Spotlight::HOME_AREA); ?>
    <?php if (get_theme_mod('reza_ervani_show_home_categories', true)) : ?>
        <?php $featured = Reza_Ervani_Spotlight::has_home_widget() ? Reza_Ervani_Spotlight::featured_post() : null; ?>
        <?php get_template_part('template-parts/editorial', 'categories', array(
            'featured_id' => $featured ? $featured->ID : 0,
            'excluded_category_ids' => array_filter(array_merge(
                $grid_category_ids,
                $series_category_ids,
                $collection_category_ids,
                $english_category_ids
            )),
        )); ?>
    <?php endif; ?>
    <?php dynamic_sidebar(Reza_Ervani_Category_Grid::HOME_AREA); ?>
    <?php foreach ($series as $section) : ?>
        <?php get_template_part('template-parts/editorial', 'series', $section); ?>
    <?php endforeach; ?>
    <?php if ($series) : ?>
        <?php get_template_part('template-parts/editorial', 'ad'); ?>
    <?php endif; ?>
    <?php if ($show_widgets || $manuscripts) : ?>
        <div class="reza-editorial-widgets">
            <?php if ($show_widgets) :
                dynamic_sidebar('home-1');
                dynamic_sidebar(Reza_Ervani_Posting_Ads::HOME_AREA);
            endif; ?>
            <?php if ($show_widgets && is_active_sidebar('home-video-gallery')) : ?>
                <div class="reza-editorial-video-gallery">
                    <?php dynamic_sidebar('home-video-gallery'); ?>
                </div>
            <?php endif; ?>
            <?php if ($show_widgets) :
                get_template_part('template-parts/home', 'category-widgets');
                add_filter('widget_display_callback', 'reza_ervani_filter_editorial_widget', 10, 3);
                get_template_part('template-parts/home', 'widgets', array(
                    'editorial' => true,
                    'english_sections' => $english_sections,
                ));
                remove_filter('widget_display_callback', 'reza_ervani_filter_editorial_widget');
            endif; ?>
            <?php if ($manuscripts) : ?>
                <?php get_template_part('template-parts/editorial', 'series', $manuscripts + array('square_images' => true)); ?>
            <?php endif; ?>
            <?php if ($show_widgets) : ?>
                <?php dynamic_sidebar('home-latest-posts'); ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>
