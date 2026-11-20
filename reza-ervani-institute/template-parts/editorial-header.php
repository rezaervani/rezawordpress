<?php
$menus = array(
    'main_nav' => __('Menu utama', 'mh-healthmag'),
    'top_nav' => __('Layanan dan kategori', 'mh-healthmag'),
);
$today = current_datetime();
$brand_tag = is_category() ? 'div' : 'h1';
?>
<a class="screen-reader-text" href="#main-content"><?php esc_html_e('Lewati ke konten', 'mh-healthmag'); ?></a>
<header class="reza-masthead google-anno-skip">
    <div class="reza-editorial-wrap">
        <div class="reza-masthead-utility">
            <time class="reza-masthead-date" datetime="<?php echo esc_attr($today->format('Y-m-d')); ?>">
                <span><?php echo esc_html(wp_date('l, j F Y', $today->getTimestamp(), $today->getTimezone())); ?></span>
                <span class="reza-masthead-date-hijri"><?php echo esc_html(reza_ervani_editorial_hijri_date($today)); ?></span>
            </time>
            <details class="reza-masthead-search">
                <summary><?php esc_html_e('Cari', 'mh-healthmag'); ?></summary>
                <form method="get" action="<?php echo esc_url(home_url('/')); ?>" role="search">
                    <label class="screen-reader-text" for="reza-masthead-query"><?php esc_html_e('Cari di situs', 'mh-healthmag'); ?></label>
                    <input id="reza-masthead-query" type="search" name="s" placeholder="<?php esc_attr_e('Cari artikel…', 'mh-healthmag'); ?>" required>
                    <button type="submit"><?php esc_html_e('Cari', 'mh-healthmag'); ?></button>
                </form>
            </details>
        </div>
        <div class="reza-masthead-brand">
            <?php if (has_custom_logo()) : ?>
                <?php the_custom_logo(); ?>
            <?php endif; ?>
            <<?php echo $brand_tag; ?> class="reza-masthead-brand-name"><a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a></<?php echo $brand_tag; ?>>
            <?php if (get_bloginfo('description')) : ?>
                <p><?php bloginfo('description'); ?></p>
            <?php endif; ?>
        </div>
        <?php foreach ($menus as $location => $label) : ?>
            <?php wp_nav_menu(array(
                'theme_location' => $location,
                'container' => 'nav',
                'container_class' => 'reza-editorial-nav reza-editorial-nav-' . $location,
                'container_aria_label' => $label,
                'fallback_cb' => false,
                'depth' => 0,
                'menu_class' => 'reza-editorial-menu',
            )); ?>
        <?php endforeach; ?>
    </div>
</header>
