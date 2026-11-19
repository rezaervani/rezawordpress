<?php mh_before_footer(); ?>
<div class="reza-site-footer">
    <?php get_template_part('template-parts/footer', 'directory'); ?>
    <?php mh_magazine_lite_footer_widgets(); ?>
    <div class="mh-copyright-wrap">
        <div class="reza-footer-credits">
            <p class="mh-copyright">
                <?php
                printf(
                    esc_html__('Copyright &copy; %1$s | WordPress Theme by %2$s', 'mh-magazine-lite'),
                    esc_html(wp_date('Y')),
                    '<a href="' . esc_url('https://github.com/rezaervani/rezawordpress') . '">Reza Ervani Institute Themes</a>'
                );
                ?>
            </p>
            <?php the_privacy_policy_link(); ?>
        </div>
    </div>
</div>
<?php mh_after_footer(); ?>
<?php wp_footer(); ?>
</body>
</html>
