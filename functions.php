<?php
/**
 * Blogsite theme setup.
 *
 * @package Blogsite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cache-busting version for a theme file, based on when it last changed.
 * Deploying rewrites changed files, so browsers pick up new CSS automatically.
 */
function blogsite_asset_version( string $relative_path ): string {
	$mtime = filemtime( get_theme_file_path( $relative_path ) );
	return $mtime ? (string) $mtime : wp_get_theme()->get( 'Version' );
}

add_action(
	'after_setup_theme',
	function () {
		// CV component styles also load inside the block editor, so the page looks the same while editing.
		add_editor_style( 'assets/css/cv.css' );
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'blogsite', get_stylesheet_uri(), array(), blogsite_asset_version( 'style.css' ) );
		wp_enqueue_style( 'blogsite-cv', get_theme_file_uri( 'assets/css/cv.css' ), array(), blogsite_asset_version( 'assets/css/cv.css' ) );
		wp_enqueue_style( 'blogsite-print', get_theme_file_uri( 'assets/css/print.css' ), array(), blogsite_asset_version( 'assets/css/print.css' ), 'print' );
	}
);

add_action(
	'init',
	function () {
		register_block_pattern_category( 'cv', array( 'label' => __( 'CV', 'blogsite' ) ) );
	}
);
