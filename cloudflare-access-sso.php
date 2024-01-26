<?php
/**
 * Cloudflare Access SSO
 *
 * @package   CloudflareAccessSSO
 * @link      https://github.com/jamesmorrison/cloudflare-access-sso
 * @author    James Morrison
 * @copyright James Morrison 2023
 * @license   GPL v2 or later
 *
 * Plugin Name: Cloudflare Access SSO
 * Description: Facilitates automatic login to WordPress when domain is protected with Cloudflare Access
 * Version:     1.0.3
 * Plugin URI:  https://github.com/jamesmorrison/cloudflare-access-sso
 * Author:      James Morrison
 * Author URI:  https://jamesmorrison.uk/
 * Text Domain: cloudflare-access-sso
 * Domain Path: /languages/
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 **/

// Security check
defined( 'ABSPATH' ) || exit;

// The Cloudflare Team Name is required
if ( ! defined( 'CF_ACCESS_TEAM_NAME' ) ) {
	error_log( 'Cloudflare Access SSO Error: CF_ACCESS_TEAM_NAME is not defined.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	return;
}

// The Cloudflare Application ID is required
if ( ! defined( 'CF_ACCESS_AUD' ) ) {
	error_log( 'Cloudflare Access SSO Error: CF_ACCESS_AUD is not defined.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	return;
}

// Default to 3 attempts to complete authentication
if ( ! defined( 'CF_ACCESS_ATTEMPTS' ) ) {
	define( 'CF_ACCESS_ATTEMPTS', 3 );
}

// Default to 60 second leeway
if ( ! defined( 'CF_ACCESS_LEEWAY' ) ) {
	define( 'CF_ACCESS_LEEWAY', 60 );
}

// Useful global constants
define( 'CLOUDFLARE_ACCESS_SSO_PLUGIN_VERSION', '1.0.3' );
define( 'CLOUDFLARE_ACCESS_SSO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CLOUDFLARE_ACCESS_SSO_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'CLOUDFLARE_ACCESS_SSO_PLUGIN_INC', CLOUDFLARE_ACCESS_SSO_PLUGIN_PATH . 'includes/' );

// Load plugin classes
if ( ! file_exists( CLOUDFLARE_ACCESS_SSO_PLUGIN_PATH . 'vendor/autoload.php' ) ) {
	error_log( 'Cloudflare Access SSO Error: Composer dependencies are missing. Please run `composer install` in the plugin directory.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	return;
}

// Load composer dependencies
require_once CLOUDFLARE_ACCESS_SSO_PLUGIN_PATH . 'vendor/autoload.php';

// Load plugin files
require_once CLOUDFLARE_ACCESS_SSO_PLUGIN_INC . '/core.php';

// Activation / Deactivation
register_activation_hook( __FILE__, '\CloudflareAccessSSO\Core\activate' );
register_deactivation_hook( __FILE__, '\CloudflareAccessSSO\Core\deactivate' );

// Bootstrap
CloudflareAccessSSO\Core\setup();
