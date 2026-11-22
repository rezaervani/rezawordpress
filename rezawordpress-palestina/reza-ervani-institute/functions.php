<?php

require_once __DIR__ . '/includes/homepage-customizer.php';
require_once __DIR__ . '/includes/editorial-home.php';
require_once __DIR__ . '/includes/spotlight-widget.php';
require_once __DIR__ . '/includes/category-grid-widget.php';
require_once __DIR__ . '/includes/home-topics-widget.php';
require_once __DIR__ . '/includes/article-list-widget.php';
require_once __DIR__ . '/includes/network-feed.php';
require_once __DIR__ . '/includes/network-feed-widget.php';
require_once __DIR__ . '/includes/posting-ads-widget.php';
require_once __DIR__ . '/includes/editorial-widgets.php';
require_once __DIR__ . '/includes/palestina-footer-widgets.php';
require_once __DIR__ . '/includes/latest-posts-widget.php';
require_once __DIR__ . '/includes/video-library.php';
require_once __DIR__ . '/includes/video-gallery-widget.php';
if (defined('WP_CLI') && WP_CLI) {
    require_once __DIR__ . '/includes/video-migration.php';
}

/***** Fetch Theme Data *****/

$mh_healthmag_data = wp_get_theme('reza-ervani-institute');
$mh_healthmag_version = $mh_healthmag_data['Version'];

/***** Carry Customizer Settings Across the Directory Rename *****/

function reza_ervani_migrate_theme_mods($old_name, $old_theme) {
    if ($old_theme->get_stylesheet() !== 'mh-healthmag') {
        return;
    }

    $previous_mods = (array) get_option('theme_mods_mh-healthmag', array());
    // WordPress maps menus and widgets when a theme is switched.
    unset($previous_mods['nav_menu_locations'], $previous_mods['sidebars_widgets']);

    $current_mods = (array) get_theme_mods();
    $migrated_mods = $current_mods + $previous_mods;
    if ($migrated_mods !== $current_mods) {
        update_option('theme_mods_reza-ervani-institute', $migrated_mods);
    }
}
add_action('after_switch_theme', 'reza_ervani_migrate_theme_mods', 5, 2);

/***** Load Google Fonts *****/

function mh_healthmag_fonts() {
	wp_dequeue_style('mh-google-fonts');
	wp_enqueue_style('mh-healthmag-fonts', 'https://fonts.googleapis.com/css?family=Josefin+Sans:400,700|Muli:400,400i,700,700i', array(), null);
}
add_action('wp_enqueue_scripts', 'mh_healthmag_fonts', 11);

/***** Load Stylesheets *****/

function mh_healthmag_styles() {
	global $mh_healthmag_version;
    $legacy_parent = get_template() === 'mh-magazine-lite';
    $base_css = $legacy_parent ? '/style.css' : '/magazine.css';
    wp_enqueue_style('mh-magazine-lite', get_template_directory_uri() . $base_css, array(), $mh_healthmag_version);
    wp_enqueue_style('mh-healthmag', get_stylesheet_uri(), array('mh-magazine-lite'), $mh_healthmag_version);
    $editorial_masthead = reza_ervani_uses_editorial_home() || is_category();
    if ($editorial_masthead || is_singular('post') || is_active_widget(false, false, Reza_Ervani_Posting_Ads::ID_BASE, true)
        || is_active_widget(false, false, Reza_Ervani_Category_Grid::ID_BASE, true)
        || is_active_widget(false, false, Reza_Ervani_Article_List::ID_BASE, true)
        || is_active_widget(false, false, Reza_Ervani_Spotlight::ID_BASE, true)) {
        wp_enqueue_style('reza-ervani-editorial', get_stylesheet_directory_uri() . '/editorial.css', array('mh-healthmag'), $mh_healthmag_version);
    }
    if ($editorial_masthead) {
        wp_enqueue_script('reza-ervani-editorial', get_stylesheet_directory_uri() . '/js/editorial.js', array(), $mh_healthmag_version, true);
    }
    if ($editorial_masthead) {
        wp_enqueue_style('reza-ervani-category', get_stylesheet_directory_uri() . '/category.css', array('reza-ervani-editorial'), $mh_healthmag_version);
    }
    if (is_singular('post')) {
        wp_enqueue_style('reza-ervani-article', get_stylesheet_directory_uri() . '/article.css', array('reza-ervani-editorial'), $mh_healthmag_version);
        if (!post_password_required() && array_filter(array_keys(reza_ervani_article_inline_ad_areas()), 'is_active_sidebar')) {
            wp_enqueue_script('reza-ervani-article-ads', get_stylesheet_directory_uri() . '/js/article-ads.js', array(), $mh_healthmag_version, true);
        }
    }
    if (is_rtl()) {
        $base_rtl_css = $legacy_parent ? '/rtl.css' : '/magazine-rtl.css';
        wp_enqueue_style('mh-magazine-lite-rtl', get_template_directory_uri() . $base_rtl_css, array('mh-healthmag'), $mh_healthmag_version);
	}
}
add_action('wp_enqueue_scripts', 'mh_healthmag_styles');

/***** Load Translations *****/

function mh_healthmag_theme_setup(){
	load_theme_textdomain('mh-healthmag', __DIR__ . '/languages/mh-healthmag');
}
add_action('after_setup_theme', 'mh_healthmag_theme_setup');

/***** Change Defaults for Custom Colors *****/

function mh_healthmag_custom_colors() {
	remove_theme_support('custom-header');
	add_theme_support('custom-header', array('default-image' => '', 'default-text-color' => '4c2b2b', 'width' => 1080, 'height' => 250, 'flex-width' => true, 'flex-height' => true));
}
add_action('after_setup_theme', 'mh_healthmag_custom_colors');

/***** Display Posts on Archives as Grid *****/

if (!function_exists('mh_magazine_lite_loop_layout')) {
	function mh_magazine_lite_loop_layout() {
		global $wp_query;
		$counter = 1;
		$max_posts = $wp_query->post_count;
		while (have_posts()) : the_post();
			if ($counter === 1) {
				echo '<div class="mh-row mh-posts-grid mh-clearfix">' . "\n";
			}
			if ($counter >= 1) {
				echo '<div class="mh-col-1-2 mh-posts-grid-col mh-clearfix">' . "\n";
					get_template_part('content', 'grid');
				echo '</div>' . "\n";
			}
			if ($counter % 2 === 0 && $counter != $max_posts) {
				echo '</div>' . "\n";
				echo '<div class="mh-row mh-posts-grid mh-posts-grid-more mh-clearfix">' . "\n";
			}
			if ($counter >= 1 && $counter === $max_posts) {
				echo '</div>' . "\n";
			}
			$counter++;
		endwhile;
	}
}


add_filter('rest_prepare_post', 'my_unescape_content', 10, 3);
function my_unescape_content($response, $post, $request) {
    if (isset($response->data['content']['rendered'])) {
        // Gunakan html_entity_decode untuk menghapus escape sequence
        $response->data['content']['rendered'] = html_entity_decode($response->data['content']['rendered']);
    }
    return $response;
}

/***** Upgrade an Active Installation from the Child Theme *****/

if (get_template() === 'mh-magazine-lite') {
    // WordPress already scheduled the legacy parent to load in this request.
    // Change the stored template for the next request without switching the stylesheet.
    add_action('shutdown', 'reza_ervani_finish_standalone_upgrade');
} else {
    require_once __DIR__ . '/includes/theme-core.php';
}

function reza_ervani_finish_standalone_upgrade() {
    if (get_option('stylesheet') === 'reza-ervani-institute' && get_option('template') === 'mh-magazine-lite') {
        update_option('template', 'reza-ervani-institute');
    }
}
