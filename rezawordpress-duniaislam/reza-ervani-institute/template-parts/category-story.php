<?php
$featured = !empty($args['featured']);
$show_image = $args['show_image'] ?? true;
$show_excerpt = $args['show_excerpt'] ?? true;
$heading = 'h' . min(6, max(2, (int) ($args['heading_level'] ?? 2)));
$title = reza_ervani_editorial_title(get_post());
$url = get_permalink();
$protected = post_password_required();
$image = $show_image && !$protected
    ? get_the_post_thumbnail(get_the_ID(), $featured ? 'medium_large' : 'mh-magazine-lite-medium', array('alt' => ''))
    : '';

$excerpt = '';
if ($protected) {
    $excerpt = __('Artikel ini dilindungi kata sandi.', 'mh-healthmag');
} elseif (function_exists('pms_is_post_restricted') && pms_is_post_restricted(get_the_ID())) {
    $excerpt = __('Konten premium', 'mh-healthmag');
} elseif ($show_excerpt) {
    $excerpt = wp_trim_words(wp_strip_all_tags(get_the_excerpt()), $featured ? 38 : 22, '…');
}

$classes = array('reza-archive-story');
if ($featured) {
    $classes[] = 'reza-archive-story-featured';
}
if ($image !== '') {
    $classes[] = 'reza-archive-story-with-image';
}
?>
<article <?php post_class($classes); ?>>
    <?php if ($image !== '') : ?>
        <a class="reza-archive-story-image" href="<?php echo esc_url($url); ?>" aria-hidden="true" tabindex="-1"><?php echo $image; ?></a>
    <?php endif; ?>
    <div class="reza-archive-story-copy">
        <<?php echo $heading; ?> class="reza-archive-story-title"><a href="<?php echo esc_url($url); ?>" rel="bookmark"><?php echo esc_html($title); ?></a></<?php echo $heading; ?>>
        <?php if ($excerpt !== '') : ?>
            <p><?php echo esc_html($excerpt); ?></p>
        <?php endif; ?>
        <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_date()); ?></time>
    </div>
</article>
