<?php
/**
 * Header layout with optional Top Bar Menu above the site title/logo.
 */
?>

<div class="mh-header-mobile-nav mh-clearfix"></div>

<header class="mh-header" itemscope="itemscope" itemtype="https://schema.org/WPHeader">

	<?php if ( has_nav_menu( 'top_nav' ) ) : ?>
		<div class="mh-main-nav-wrap mh-top-nav-wrap"><!-- pakai wrapper sama dengan main nav -->
			<nav class="mh-navigation mh-main-nav mh-top-nav mh-container mh-container-inner mh-clearfix"
				 itemscope="itemscope"
				 itemtype="https://schema.org/SiteNavigationElement"
				 aria-label="<?php esc_attr_e( 'Top Bar Menu', 'mh-magazine-lite' ); ?>">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'top_nav',
					'fallback_cb'    => false,
				) );
				?>
			</nav>
		</div>
	<?php endif; ?>

	<div class="mh-container mh-container-inner mh-row mh-clearfix">
		<?php mh_magazine_lite_custom_header(); ?>
	</div>

	<div class="mh-main-nav-wrap">
		<nav class="mh-navigation mh-main-nav mh-container mh-container-inner mh-clearfix"
			 itemscope="itemscope"
			 itemtype="https://schema.org/SiteNavigationElement"
			 aria-label="<?php esc_attr_e( 'Main Navigation', 'mh-magazine-lite' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'main_nav',
				'fallback_cb'    => false,
			) );
			?>
		</nav>
	</div>
</header>
