<?php
$menu = wp_nav_menu(array(
    'theme_location' => 'footer_directory',
    'container' => false,
    'fallback_cb' => false,
    'menu_class' => 'reza-footer-directory-list',
    'echo' => false,
));
?>
<div class="reza-footer-directory">
    <div class="reza-footer-masthead">
        <a class="reza-footer-brand" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
        <a class="reza-footer-top" href="#mh-mobile">
            <?php esc_html_e('Kembali ke atas', 'mh-healthmag'); ?> <span aria-hidden="true">↑</span>
        </a>
    </div>
    <nav aria-label="<?php esc_attr_e('Direktori Footer', 'mh-healthmag'); ?>">
        <?php if ($menu) : ?>
            <?php echo $menu; ?>
        <?php else : ?>
            <ul class="reza-footer-directory-list">
                <?php foreach (reza_ervani_default_footer_groups() as $label => $links) : ?>
                    <?php $links = array_filter($links); ?>
                    <?php if ($links) : ?>
                        <li>
                            <span><?php echo esc_html($label); ?></span>
                            <ul class="sub-menu">
                                <?php foreach ($links as list($title, $url)) : ?>
                                    <li><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($title); ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </nav>
</div>
