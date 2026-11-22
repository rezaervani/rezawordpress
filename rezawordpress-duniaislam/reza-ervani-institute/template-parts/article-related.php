<?php
$categories = wp_list_pluck(get_the_category(), 'term_id');
if (!$categories || post_password_required()) {
    return;
}

$related = reza_ervani_editorial_posts(array(
    'category__in' => $categories,
    'post__not_in' => array(get_the_ID()),
    'numberposts' => 3,
));
if (!$related) {
    return;
}
?>
<section class="reza-article-related" aria-labelledby="reza-article-related-title">
    <h2 id="reza-article-related-title"><?php esc_html_e('Bacaan terkait', 'mh-healthmag'); ?></h2>
    <ul class="reza-article-related-list">
        <?php foreach ($related as $story) : ?>
            <li>
                <a href="<?php echo esc_url(get_permalink($story)); ?>">
                    <?php echo get_the_post_thumbnail($story, 'medium_large', array('alt' => '', 'loading' => 'lazy')); ?>
                    <h3><?php echo esc_html(reza_ervani_editorial_title($story)); ?></h3>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
