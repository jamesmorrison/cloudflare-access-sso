<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase WordPress.Files.FileName.InvalidClassFileName
/**
 * Plugin
 *
 * @package CloudflareAccessSSO
 */

namespace CloudflareAccessSSO;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;

// Exit if accessed directly.
defined( 'ABSPATH' ) || die();

/**
 * The main Plugin class. This class is used as a singleton
 * and should not be instantiated directly.
 */
class Plugin {

	/**
	 * Singleton instance of the Plugin.
	 *
	 * @var \CloudflareAccessSSO\Plugin
	 */
	public static $instance = null;

	/**
	 * Common cache group
	 *
	 * @var \CloudflareAccessSSO\Plugin
	 */
	protected static $cache_group = 'cf_access_sso_cache_group';

	/**
	 * Cloudflare API URl
	 *
	 * @var \CloudflareAccessSSO\Plugin
	 */
	protected static $cloudflare_api_url = 'https://' . CF_ACCESS_TEAM_NAME . '.cloudflareaccess.com/cdn-cgi/access/certs';

	/**
	 * Conditionally creates the singleton instance if absent, else
	 * returns the previously saved instance.
	 *
	 * @return Plugin The singleton instance
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new Plugin();
		}

		return self::$instance;
	}

	/**
	 * Setup the plugin.
	 *
	 * @return void
	 */
	public function setup() {
		// Process SSO login on the 1st hook available on the login page.
		add_action( 'login_head', array( $this, 'process_login' ) );

		// Logout from Cloudflare Access once WP logout is complete.
		add_filter( 'logout_redirect', array( $this, 'set_cloudflare_access_logout_url' ), 10, 3 );
	}

	/**
	 * Process Login
	 *
	 * @return void|\WP_Error
	 */
	public function process_login() {
		$authorisation_header = $this->get_authorisation_header();
		$certificates         = $this->get_cloudflare_certificates();
		$login_attempts       = 0;
		$user                 = false;

		// On cache error, force update the certificates
		if ( is_wp_error( $certificates ) ) {
			$certificates = $this->get_cloudflare_certificates( true );
		}

		if ( is_wp_error( $certificates ) || ! $authorisation_header || wp_doing_ajax() ) {
			return;
		}

		while ( $login_attempts < CF_ACCESS_ATTEMPTS ) {
			try {
				JWT::$leeway = CF_ACCESS_LEEWAY;

				$jwt = JWT::decode( $authorisation_header, JWK::parseKeySet( $certificates ) );

				if ( $this->validate_jwt( $jwt ) ) {
					$user = get_user_by( 'email', $jwt->email );

					// If a matching user is found, facilitate log in.
					if ( is_a( $user, '\WP_User' ) ) {
						wp_set_auth_cookie( $user->ID );
						wp_set_current_user( $user->ID );
						do_action( 'wp_login', $user->name, $user );
						wp_safe_redirect( esc_url( admin_url() ) );
						exit;
					}
				}
			} catch ( \UnexpectedValueException $e ) {
				// Force update the certificates again just in case there was an issue here
				$certificates = $this->get_cloudflare_certificates( true );
			}

			++$login_attempts;
		}
	}

	/**
	 * Set Cloudflare Access Logout URL
	 *
	 * @param string  $redirect_to           The redirect destination URL.
	 * @param string  $requested_redirect_to The requested redirect destination URL passed as a parameter.
	 * @param WP_User $user                  The WP_User object for the user that's logging out.
	 *
	 * @return string Logout URL.
	 */
	public function set_cloudflare_access_logout_url( $redirect_to, $requested_redirect_to, $user ) {
		// Set the redirect URL to logout from Cloudflare Access - but only where the authorisation header exists.
		if ( $this->get_authorisation_header() ) {
			return esc_url( home_url( '/cdn-cgi/access/logout' ) );
		}

		return $redirect_to;
	}

	/**
	 * Get Authorisation Header
	 *
	 * The CF_Authorization cookie is not set when a user has not passed through Cloudflare Access.
	 * The most likely scenario is that the login URL is not correctly protected.
	 *
	 * @return string|bool
	 */
	protected function get_authorisation_header() {
		if ( ! isset( $_COOKIE['CF_Authorization'] ) ) {
			return false;
		}

		return esc_attr( $_COOKIE['CF_Authorization'] );
	}

	/**
	 * Get Cloudflare Certificates
	 *
	 * Retrieves Cloudflare certificates from cache or source
	 *
	 * @param bool $force Whether to bypass cache and force an update.
	 * @return array|\WP_Error
	 */
	protected function get_cloudflare_certificates( $force = false ) {
		$certificates = wp_cache_get( 'cf_access_sso_certficates', self::$cache_group );

		if ( ! $certificates || $force ) {
			try {
				$response     = wp_remote_get( esc_url( self::$cloudflare_api_url ) );
				$certificates = json_decode( wp_remote_retrieve_body( $response ), true );
				wp_cache_set( 'cf_access_sso_certficates', $certificates, self::$cache_group, 7 * DAY_IN_SECONDS );
				wp_cache_set( 'cf_access_sso_certficates_last_updated', time(), self::$cache_group, 30 * DAY_IN_SECONDS );
			} catch ( \Exception $e ) {
				return new WP_Error( 'cf_access_sso_certificates_error', $e->getMessage(), self::$cloudflare_api_url );
			}
		}

		return $certificates;
	}

	/**
	 * Get Cloudflare Certificates Last Updated
	 *
	 * @return int
	 */
	protected function get_cloudflare_certificates_last_updated() {
		return wp_cache_get( 'cf_access_sso_certficates_last_updated', self::$cache_group );
	}

	/**
	 * Get Cloudflare Certificates Next Update
	 *
	 * @return int
	 */
	protected function get_cloudflare_certificates_next_update() {
		return $this->get_cloudflare_certificates_last_updated() + ( 7 * DAY_IN_SECONDS );
	}

	/**
	 * Validate JWT
	 *
	 * @param object $jwt The JWT to validate.
	 * @return bool
	 */
	protected function validate_jwt( $jwt ) {
		return isset( $jwt->email ) && isset( $jwt->aud ) && isset( $jwt->aud[0] ) && $this->verify_aud( $jwt->aud[0] );
	}

	/**
	 * Verify AUD
	 *
	 * @return bool
	 */
	protected function verify_aud( $aud ) {
		if ( is_array( CF_ACCESS_AUD ) ) {
			return in_array( $aud, CF_ACCESS_AUD, true );
		} elseif ( is_string( $aud ) ) {
				return CF_ACCESS_AUD === $aud;
		}
		return false;
	}
}
