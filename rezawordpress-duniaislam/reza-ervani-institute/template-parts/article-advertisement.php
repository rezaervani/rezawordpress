<?php
$area = $args['area'];
if (!is_active_sidebar($area)) {
    return;
}

$minimum_words = $args['minimum_words'] ?? 0;
$band_class = empty($args['editorial']) ? 'reza-article-ad' : 'reza-editorial-ad';
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
<aside class="<?php echo esc_attr($band_class . ' reza-widget-ad-band' . ($minimum_words ? ' reza-article-ad-inline' : '')); ?>" aria-label="<?php esc_attr_e('Iklan', 'mh-healthmag'); ?>">
    <p class="<?php echo esc_attr($band_class . '-label'); ?>"><?php esc_html_e('Iklan', 'mh-healthmag'); ?></p>
    <?php echo $widgets; ?>
</aside>
<?php if ($minimum_words) : ?>
    </template>
<?php endif; ?>
