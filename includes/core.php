<?php
/**
 * Core plugin functionality.
 *
 * @package CloudflareAccessSSO
 */

namespace CloudflareAccessSSO\Core;

/**
 * Default setup routine
 *
 * @return void
 */
function setup() {
	$n = function( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_action( 'init', $n( 'i18n' ) );
	add_action( 'init', $n( 'init' ) );

	do_action( 'cloudflare_access_sso_plugin_loaded' );
}

/**
 * Registers the default textdomain.
 *
 * @return void
 */
function i18n() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'cloudflare-access-sso' );
	load_textdomain( 'cloudflare-access-sso', WP_LANG_DIR . '/cloudflare-access-sso/cloudflare-access-sso-' . $locale . '.mo' );
	load_plugin_textdomain( 'cloudflare-access-sso', false, plugin_basename( CLOUDFLARE_ACCESS_SSO_PLUGIN_PATH ) . '/languages/' );
}

/**
 * Initializes the plugin and fires an action other plugins can hook into.
 *
 * @return void
 */
function init() {

	if ( class_exists( '\CloudflareAccessSSO\Plugin' ) ) {
		\CloudflareAccessSSO\Plugin::get_instance()->setup();
	}

	do_action( 'cloudflare_access_sso_plugin_init' );
}

/**
 * Activate the plugin
 *
 * @return void
 */
function activate() {
	// Load the plugin
	init();
}

/**
 * Deactivate the plugin
 *
 * @return void
 */
function deactivate() {
	// Flush cache to remove Cloudflare Certificates
	wp_cache_flush();
}
