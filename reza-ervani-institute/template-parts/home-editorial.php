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
?>
<main id="main-content" class="reza-editorial-wrap reza-editorial-main">
    <?php get_template_part('template-parts/editorial', 'spotlight', array('featured' => $featured, 'feed' => $feed)); ?>
    <?php if ($question_cards) : ?>
        <?php get_template_part('template-parts/editorial', 'questions', array('category' => $question_category, 'cards' => $question_cards)); ?>
    <?php endif; ?>
    <?php if (get_theme_mod('reza_ervani_show_home_categories', true)) : ?>
        <?php get_template_part('template-parts/editorial', 'categories', array(
            'featured_id' => $featured ? $featured->ID : 0,
            'excluded_category_id' => $question_cards ? $question_category->term_id : 0,
        )); ?>
    <?php endif; ?>
    <?php if (get_theme_mod('reza_ervani_editorial_widgets', true)) : ?>
        <div class="reza-editorial-widgets">
            <?php get_template_part('template-parts/home', 'widgets', array('editorial' => true)); ?>
        </div>
    <?php endif; ?>
</main>
