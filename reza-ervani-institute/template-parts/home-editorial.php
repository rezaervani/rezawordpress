<?php
$grid_category_ids = reza_ervani_home_segment_visible(Reza_Ervani_Category_Grid::HOME_AREA)
    ? reza_ervani_home_widget_category_ids(Reza_Ervani_Category_Grid::ID_BASE, array(Reza_Ervani_Category_Grid::HOME_AREA))
    : array();
$fatwa_series = reza_ervani_home_segment_visible('series-fatwa-lajnah')
    ? reza_ervani_editorial_series('fatwa-lajnah-daimah-saudi-arabia', __('Fatwa Lajnah Daimah', 'mh-healthmag'))
    : null;
$series = array_filter(array(
    reza_ervani_home_segment_visible('series-fiqh-muyassar') ? reza_ervani_editorial_series('fiqh-muyassar') : null,
    reza_ervani_home_segment_visible('series-fathul-qarib') ? reza_ervani_editorial_series('fathul-qarib-al-mujib', __('Fathul Qarib al-Mujib', 'mh-healthmag')) : null,
    $fatwa_series,
));
$manuscripts = reza_ervani_home_segment_visible('series-manuscripts')
    ? reza_ervani_editorial_series('manuskrip', __('Makhthuthah', 'mh-healthmag'))
    : null;
$old_fatwa_category = $fatwa_series ? get_category_by_slug('fatwa') : false;
$series_category_ids = array_filter(array_merge(array_column($series, 'category_id'), array(
    $manuscripts['category_id'] ?? 0,
    $old_fatwa_category ? (int) $old_fatwa_category->term_id : 0,
)));
$collection_areas = array_filter(array('home-1', Reza_Ervani_Posting_Ads::HOME_AREA), 'reza_ervani_home_segment_visible');
$collection_category_ids = reza_ervani_home_widget_category_ids(Reza_Ervani_Posting_Ads::ID_BASE, $collection_areas);
$english_sections = reza_ervani_home_segment_visible('english-sidebar') ? array_filter(array(
    reza_ervani_editorial_series('aqeedah'),
    reza_ervani_editorial_series('tadabbur-al-quran-in-english'),
    reza_ervani_editorial_series('hadith-explanation'),
    reza_ervani_editorial_series('islamic-question-answer'),
)) : array();
$english_category_ids = array_column($english_sections, 'category_id');
?>
<main id="main-content" class="reza-editorial-wrap reza-editorial-main">
    <?php if (reza_ervani_home_segment_visible(Reza_Ervani_Smart_Ad::TOP_AREA)) : ?>
        <?php get_template_part('template-parts/article', 'advertisement', array(
            'area' => Reza_Ervani_Smart_Ad::TOP_AREA, 'editorial' => true,
        )); ?>
    <?php endif; ?>
    <?php reza_ervani_home_sidebar(Reza_Ervani_Spotlight::HOME_AREA); ?>
    <?php if (reza_ervani_home_segment_visible('home-categories')) : ?>
        <?php $featured = reza_ervani_home_segment_visible(Reza_Ervani_Spotlight::HOME_AREA) && Reza_Ervani_Spotlight::has_home_widget() ? Reza_Ervani_Spotlight::featured_post() : null; ?>
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
    <?php reza_ervani_home_sidebar(Reza_Ervani_Category_Grid::HOME_AREA); ?>
    <?php if (reza_ervani_home_segment_visible('home-network-ad')) : ?>
        <?php get_template_part('template-parts/article', 'advertisement', array('area' => 'home-network-ad', 'editorial' => true)); ?>
    <?php endif; ?>
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
    <?php reza_ervani_home_sidebar(Reza_Ervani_Palestina_Topics::HOME_AREA); ?>
    <?php reza_ervani_home_sidebar(Reza_Ervani_Duniaislam_Topics::HOME_AREA); ?>
    <?php foreach ($series as $section) : ?>
        <?php get_template_part('template-parts/editorial', 'series', $section); ?>
    <?php endforeach; ?>
    <?php if (reza_ervani_home_segment_visible(Reza_Ervani_Smart_Ad::MIDDLE_AREA)
        && ($series || is_active_sidebar(Reza_Ervani_Smart_Ad::MIDDLE_AREA))) : ?>
        <?php get_template_part('template-parts/editorial', 'ad'); ?>
    <?php endif; ?>
    <div class="reza-editorial-widgets">
        <?php reza_ervani_home_sidebar('home-1'); ?>
        <?php reza_ervani_home_sidebar(Reza_Ervani_Posting_Ads::HOME_AREA); ?>
        <?php if (reza_ervani_home_sidebar_active('home-video-gallery')) : ?>
            <div class="reza-editorial-video-gallery">
                <?php dynamic_sidebar('home-video-gallery'); ?>
            </div>
        <?php endif; ?>
        <?php get_template_part('template-parts/home', 'category-widgets'); ?>
        <?php
        add_filter('widget_display_callback', 'reza_ervani_filter_editorial_widget', 10, 3);
        get_template_part('template-parts/home', 'widgets', array(
            'editorial' => true,
            'english_sections' => $english_sections,
        ));
        remove_filter('widget_display_callback', 'reza_ervani_filter_editorial_widget');
        ?>
        <?php if ($manuscripts) : ?>
            <?php get_template_part('template-parts/editorial', 'series', $manuscripts + array('square_images' => true)); ?>
        <?php endif; ?>
        <?php reza_ervani_home_sidebar('home-latest-posts'); ?>
    </div>
</main>
