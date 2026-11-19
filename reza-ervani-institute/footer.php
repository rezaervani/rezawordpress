<?php mh_before_footer(); ?>
<?php mh_magazine_lite_footer_widgets(); ?>
<div class="mh-copyright-wrap">
	<div class="mh-container mh-container-inner mh-clearfix">
		<p class="mh-copyright">
			<?php
			printf(
				esc_html__('Copyright &copy; %1$s | WordPress Theme by %2$s', 'mh-magazine-lite'),
				esc_html(wp_date('Y')),
				'<a href="' . esc_url('https://github.com/rezaervani/rezawordpress') . '">Reza Ervani Institute Themes</a>'
			);
			?>
		</p>
	</div>
</div>
<?php mh_after_footer(); ?>
<?php wp_footer(); ?>
</body>
</html>
