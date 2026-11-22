<?php
if (is_active_sidebar(Reza_Ervani_Smart_Ad::MIDDLE_AREA)) {
    get_template_part('template-parts/article', 'advertisement', array(
        'area' => Reza_Ervani_Smart_Ad::MIDDLE_AREA, 'editorial' => true,
    ));
    return;
}

$publisher = reza_ervani_sanitize_adsense_publisher(get_theme_mod('reza_ervani_home_adsense_publisher', ''));
$slot = reza_ervani_sanitize_adsense_slot(get_theme_mod('reza_ervani_home_adsense_slot', ''));

if (!$publisher || !$slot) {
    return;
}
?>
<aside class="reza-editorial-ad" aria-label="<?php esc_attr_e('Iklan', 'mh-healthmag'); ?>">
    <p class="reza-editorial-ad-label"><?php esc_html_e('Iklan', 'mh-healthmag'); ?></p>
    <?php if (is_customize_preview()) : ?>
        <p class="reza-editorial-ad-preview"><?php esc_html_e('Pratinjau slot iklan', 'mh-healthmag'); ?></p>
    <?php else : ?>
        <ins class="adsbygoogle reza-editorial-ad-unit"
             data-ad-client="<?php echo esc_attr($publisher); ?>"
             data-ad-slot="<?php echo esc_attr($slot); ?>"
             data-ad-format="horizontal"
             data-full-width-responsive="false"></ins>
        <script>(window.adsbygoogle = window.adsbygoogle || []).push({});</script>
    <?php endif; ?>
</aside>
