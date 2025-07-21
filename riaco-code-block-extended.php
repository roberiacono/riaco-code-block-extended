<?php
/**
 * Plugin Name:       Code Block Extended
 * Description:       Extends the Code block to display different style for php and js codes using prism.js.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Roberto Iacono
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       copyright-date-block
 *
 * @package CreateBlock
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



/**
 * Enqueue frontend styles for the block.
 */
function riaco_code_block_extended_enqueue_frontend_css() {

	if ( ! function_exists( 'register_block_type' ) ) {
		// Block editor is not available.
		return;
	}

	if ( has_block( 'core/code' ) ) {

		wp_enqueue_script(
			'riaco-code-block-extended-scripts',
			plugin_dir_url( __FILE__ ) . 'assets/prism.min.js',
			array(),
			filemtime( __DIR__ . '/assets/prism.min.js' ),
			true
		);

		wp_enqueue_style( 'riaco-code-block-extended-styles', plugin_dir_url( __FILE__ ) . 'assets/prism.min.css', array(), filemtime( __DIR__ . '/assets/prism.min.css' ) );
	}
}
add_action( 'wp_enqueue_scripts', 'riaco_code_block_extended_enqueue_frontend_css' );



function riaco_cbe_guess_language_from_code( $code ) {
	// Heuristic detection for PHP
	if ( preg_match( '/<\?php|echo|->|require|include/', $code ) ) {
		return 'php';
	}

	// Heuristic detection for JavaScript
	if ( preg_match( '/console\.log|let\s|const\s|import\s/', $code ) ) {
		return 'javascript';
	}

	// Heuristic detection for CSS
	if ( preg_match( '/\{[^}]*\}|;\s*$/m', $code ) && preg_match( '/[\.\#a-zA-Z][^{]*\s*\{/', $code ) ) {
		return 'css';
	}

	return 'php'; // fallback
}


function riaco_cbe_add_prism_class_by_detected_language( $block_content, $block ) {
	// Make sure it's the core/code block and has content
	if ( empty( $block['innerContent'][0] ) ) {
		return $block_content;
	}

	// Extract and sanitize code
	$raw_code = trim( strip_tags( $block['innerContent'][0] ) );

	// Detect language
	$language = riaco_cbe_guess_language_from_code( $raw_code );

	// Inject Prism classes into <pre> and <code>
	$processor = new WP_HTML_Tag_Processor( $block_content );

	if ( $processor->next_tag( 'code' ) ) {
		$processor->add_class( 'language-' . esc_attr( $language ) );
	}

	return $processor->get_updated_html();
}
add_filter( 'render_block_core/code', 'riaco_cbe_add_prism_class_by_detected_language', 10, 2 );
