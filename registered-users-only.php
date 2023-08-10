<?php /*

**************************************************************************

Plugin Name:  Registered Users Only
Plugin URI:   https://alex.blog/wordpress-plugins/registered-users-only/
Description:  Redirects all non-logged in users to your login form. Make sure to <a href="options-general.php?page=registered-users-only">disable registration</a> if you want your blog truly private.
Version:      1.3.0
Author:       Alex Mills (Viper007Bond)
Author URI:   https://alex.blog/
Text Domain:  registered-users-only
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html

**************************************************************************

Copyright (C) 2015-2018 Alex Mills (Viper007Bond)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

**************************************************************************/

class RegisteredUsersOnly {
	public $exclusions = array();

	// Class initialization
	function __construct() {
		// Register our hooks
		add_action( 'wp', array( $this, 'MaybeRedirect' ) );
		add_action( 'rest_api_init', array( $this, 'MaybeRedirect' ) );
		add_action( 'init', array( $this, 'LoginFormMessage' ) );
		add_action( 'admin_menu', array( $this, 'AddAdminMenu' ) );
		add_filter( 'rest_authentication_errors', array( $this, 'restrict_rest_api' ) );

		if ( isset( $_POST['regusersonly_action'] ) && 'update' == $_POST['regusersonly_action'] ) {
			add_action( 'init', array( $this, 'POSTHandle' ) );
		}
	}


	// Register the options page
	public function AddAdminMenu() {
		add_options_page(
			__( 'Registered Users Only Options', 'registered-users-only' ),
			__( 'Registered Only', 'registered-users-only' ),
			'manage_options',
			'registered-users-only',
			array( $this, 'OptionsPage' )
		);
	}


	// Depending on conditions, run an authentication check
	public function MaybeRedirect() {
		// If the user is logged in, then abort
		if ( current_user_can( 'read' ) ) {
			return;
		}

		$settings = get_option( 'registered-users-only' );

		// Feeds
		if ( ! empty( $settings['feeds'] ) && is_feed() ) {
			return;
		}

		// Rest
		$is_rest = defined('REST_REQUEST');
		if ( ! empty( $settings['rest'] ) && $is_rest ) {
			return;
		}

		// Authenticated Rest 
		if ( ! empty( $settings['rest_auth'] ) && $is_rest ) {
			return;
		}

		// This is a base array of pages that will be EXCLUDED from being blocked
		$this->exclusions = array(
			'wp-login.php',
			'wp-register.php',
			'wp-cron.php', // Just incase
			'wp-trackback.php',
			'wp-app.php',
			'xmlrpc.php',
		);

		// If the current script name is in the exclusion list, abort
		if ( in_array(
			basename( $_SERVER['PHP_SELF'] ),
			apply_filters( 'registered-users-only_exclusions', $this->exclusions )
		) ) {
			return;
		}

		// Still here? Okay, then redirect to the login form
		auth_redirect();
	}

	// Allow authenticated users to access the rest API.
	public function restrict_rest_api( $result ) {
		$settings = get_option( 'registered-users-only' );

		if ( ! empty( $settings['rest'] ) ) {
			return $result;
		}

		// If a previous authentication check was applied,
		// pass that result along without modification.
		if ( true === $result || is_wp_error( $result ) ) {
			return $result;
		}
	
		// No authentication has been performed yet.
		// Return an error if user is not logged in.
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You are not currently logged in.', 'registered-users-only' ),
				array( 'status' => 401 )
			);
		}
	
		return $result;
	}

	// Use some deprecate code (yeah, I know) to insert a "You must login" error message to the login form
	// If this breaks in the future, oh well, it's just a pretty message for users
	public function LoginFormMessage() {
		// Don't show the error message if anything else is going on (registration, etc.)
		if (
			'wp-login.php' != basename( $_SERVER['PHP_SELF'] ) ||
			! empty( $_POST ) ||
			( ! empty( $_GET ) && empty( $_GET['redirect_to'] ) )
		) {
			return;
		}

		global $error;
		$error = __( 'Only registered and logged in users are allowed to view this site. Please log in now.', 'registered-users-only' );
	}

	// Update options submitted from the options form
	public function POSTHandle() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		check_admin_referer( 'registered-users-only' );

		$settings = array(
			'feeds' => ( ! empty( $_POST['regusersonly_feeds'] ) ) ? 1 : 0,
			'rest'  => ( ! empty( $_POST['regusersonly_rest'] ) ) ? 1 : 0,
			'rest_auth'  => ( ! empty( $_POST['regusersonly_rest_auth'] ) ) ? 1 : 0,
		);

		update_option( 'registered-users-only', $settings );

		update_option(
			'users_can_register',
			( ! empty( $_POST['users_can_register'] ) ) ? 1 : 0
		);

		wp_redirect( add_query_arg( 'updated', 'true' ) );
		exit();
	}


	// Output the configuration page for the plugin
	public function OptionsPage() {
		$settings = get_option( 'registered-users-only' );
		?>

		<div class="wrap">
			<h2><?php _e( 'Registered Users Only', 'registered-users-only' ); ?></h2>

			<form method="post" action="">
				<?php wp_nonce_field( 'registered-users-only' ) ?>

				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e( 'Membership' ); ?></th>
						<td>
							<label for="users_can_register">
								<input name="users_can_register" type="checkbox" id="users_can_register" value="1"<?php checked( '1', get_option( 'users_can_register' ) ); ?> />
								<?php _e( 'Anyone can register' ) ?>
							</label><br />
							<?php _e( 'This is a default WordPress option placed here for easy changing.', 'registered-users-only' ); ?><br /><br />
							<label for="regusersonly_rest_auth">
								<input name="regusersonly_rest_auth" type="checkbox" id="regusersonly_rest_auth" value="1"<?php checked( '1', ! empty( $settings['rest_auth'] ) ); ?> />
								<?php _e( 'Allow authenticated access to your REST APIs', 'registered-users-only' ); ?>
							</label><br />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Guest Access', 'registered-users-only' ); ?></th>
						<td>
							<label for="regusersonly_feeds">
								<input name="regusersonly_feeds" type="checkbox" id="regusersonly_feeds" value="1"<?php checked( '1', ! empty( $settings['feeds'] ) ); ?> />
								<?php _e( 'Allow access to your post and comment feeds (Warning: this will reveal all post contents to guests!)', 'registered-users-only' ); ?>
							</label><br />
							<label for="regusersonly_rest">
								<input name="regusersonly_rest" type="checkbox" id="regusersonly_rest" value="1"<?php checked( '1', ! empty( $settings['rest'] ) ); ?> />
								<?php _e( 'Allow access to your REST APIs (Warning: this will reveal all post contents to guests!)', 'registered-users-only' ); ?>
							</label><br />
						</td>
					</tr>
				</table>

				<p class="submit">
					<?php submit_button(); ?>
					<input type="hidden" name="regusersonly_action" value="update" />
				</p>
			</form>

		</div>

	<?php
	}
}

function RegisteredUsersOnly() {
	global $RegisteredUsersOnly;

	$RegisteredUsersOnly = new RegisteredUsersOnly();
}

// Start this plugin once all other plugins are fully loaded
add_action( 'plugins_loaded', 'RegisteredUsersOnly' );
