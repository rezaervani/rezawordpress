<?php
$area = $args['area'];
if (!is_active_sidebar($area)) {
    return;
}
?>
<aside class="reza-article-ad" aria-label="<?php esc_attr_e('Iklan', 'mh-healthmag'); ?>">
    <p class="reza-article-ad-label"><?php esc_html_e('Iklan', 'mh-healthmag'); ?></p>
    <?php dynamic_sidebar($area); ?>
</aside>
