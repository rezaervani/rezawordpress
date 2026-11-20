<?php
$featured_id = absint(get_theme_mod('reza_ervani_editorial_featured', 0));
$featured = $featured_id ? get_post($featured_id) : null;

if (!$featured || $featured->post_status !== 'publish' || $featured->post_type !== 'post' || $featured->post_password !== '') {
    $featured = null;
}

$feed = reza_ervani_editorial_posts(array(
    'numberposts' => $featured ? 7 : 8,
    'post__not_in' => $featured ? array($featured->ID) : array(),
));

if (!$featured) {
    $featured = array_shift($feed);
}

$question_category = get_theme_mod('reza_ervani_show_home_questions', true) ? get_category_by_slug('tanya-jawab') : false;
$question_cards = $question_category ? reza_ervani_editorial_question_cards($question_category) : array();
$fiqh_category = get_category_by_slug('fiqh-muyassar');
$fiqh_stories = $fiqh_category ? reza_ervani_editorial_posts(array('cat' => $fiqh_category->term_id, 'numberposts' => 5)) : array();
$fiqh_category_id = $fiqh_stories ? $fiqh_category->term_id : 0;
$collections = reza_ervani_editorial_collections();
$collection_category_ids = array_column($collections, 'category_id');
$show_widgets = get_theme_mod('reza_ervani_editorial_widgets', true);
?>
<main id="main-content" class="reza-editorial-wrap reza-editorial-main">
    <?php get_template_part('template-parts/editorial', 'spotlight', array('featured' => $featured, 'feed' => $feed)); ?>
    <?php if (get_theme_mod('reza_ervani_show_home_categories', true)) : ?>
        <?php get_template_part('template-parts/editorial', 'categories', array(
            'featured_id' => $featured ? $featured->ID : 0,
            'excluded_category_ids' => array_filter(array_merge(array(
                $question_cards ? $question_category->term_id : 0,
                $fiqh_category_id,
            ), $collection_category_ids)),
        )); ?>
    <?php endif; ?>
    <?php if ($question_cards) : ?>
        <?php get_template_part('template-parts/editorial', 'questions', array('category' => $question_category, 'cards' => $question_cards)); ?>
    <?php endif; ?>
    <?php if ($fiqh_stories) : ?>
        <?php get_template_part('template-parts/editorial', 'series', array('category' => $fiqh_category, 'stories' => $fiqh_stories)); ?>
        <?php get_template_part('template-parts/editorial', 'ad'); ?>
    <?php endif; ?>
    <?php if ($show_widgets || $collections) : ?>
        <div class="reza-editorial-widgets">
            <?php if ($show_widgets) :
                $replaced_categories = array_filter(array_merge(
                    array($fiqh_category_id),
                    $collection_category_ids,
                    array_column($collections, 'replaces_id')
                ));
                $filter = static function ($instance, $widget, $sidebar) use ($replaced_categories) {
                    return reza_ervani_filter_editorial_widget($instance, $widget, $sidebar, $replaced_categories);
                };
                add_filter('widget_display_callback', $filter, 10, 3);
                dynamic_sidebar('home-1');
                remove_filter('widget_display_callback', $filter, 10);
            endif; ?>
            <?php
            if ($show_widgets) {
                add_filter('widget_display_callback', $filter, 10, 3);
            }
            get_template_part('template-parts/home', 'widgets', array(
                'show_widgets' => $show_widgets,
                'collections' => $collections,
            ));
            if ($show_widgets) {
                remove_filter('widget_display_callback', $filter, 10);
            }
            ?>
        </div>
    <?php endif; ?>
</main>
