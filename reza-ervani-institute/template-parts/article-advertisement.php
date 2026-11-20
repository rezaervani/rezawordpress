<?php
$area = $args['area'];
if (!is_active_sidebar($area)) {
    return;
}

$minimum_words = $args['minimum_words'] ?? 0;
ob_start();
dynamic_sidebar($area);
$widgets = ob_get_clean();
if (trim($widgets) === '') {
    return;
}
?>
<?php if ($minimum_words) : ?>
    <template data-reza-inline-ad-minimum="<?php echo esc_attr($minimum_words); ?>">
<?php endif; ?>
<aside class="reza-article-ad<?php echo $minimum_words ? ' reza-article-ad-inline' : ''; ?>" aria-label="<?php esc_attr_e('Iklan', 'mh-healthmag'); ?>">
    <p class="reza-article-ad-label"><?php esc_html_e('Iklan', 'mh-healthmag'); ?></p>
    <?php echo $widgets; ?>
</aside>
<?php if ($minimum_words) : ?>
    </template>
<?php endif; ?>
