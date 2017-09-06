<?php
/**
 * Display a colored bar to help distinguish between different environments.
 *
 * @package Env_Bar
 * @version 0.2.1
 */

/*
Plugin Name: Env Bar
Plugin URI: https://wordpress.org/plugin/env-bar
Description: Display a colored bar to help distinguish between different environments.
Version: 0.2.1
Author: Justin Peacock
Author URI: https://byjust.in
Text Domain: env-bar
Domain Path: /languages/
License: GNU General Public License v2 or later
License URI: LICENSE
*/

// Make sure we don't expose any info if called directly.
if ( ! function_exists( 'add_action' ) ) {
	exit;
}

/**
 * Check for multisite.
 */
if ( is_multisite() ) {
	add_filter( 'network_admin_menu', 'env_bar_network_admin_menu' );
} else {
	add_filter( 'admin_menu', 'env_bar_admin_menu' );
}

/**
 * Add admin menu to Network sidebar.
 */
function env_bar_network_admin_menu() {

	// Create our options page.
	add_menu_page(
		__( 'Env Bar', 'env-bar' ),
		__( 'Environment', 'env-bar' ),
		'manage_network_options',
		'env_bar_network_options_page',
		'env_bar_network_options_page_callback',
		'dashicons-admin-site'
	);

	// Create a section (we won't need a section header).
	add_settings_section(
		'default',
		'',
		'',
		'env_bar_network_options_page'
	);

	// Create and register our option (we make the option id very explicit because
	// this is the key that will be used to store the options.
	register_setting( 'env_bar_network_options_page', 'environment_setting' );

	add_settings_field(
		'environment_setting',
		__( 'Environment', 'env-bar' ),
		'environment_setting_callback',
		'env_bar_network_options_page',
		'default'
	);
}

/**
 * Add admin menu
 */
function env_bar_admin_menu() {
	add_menu_page(
		__( 'Env Bar', 'env-bar' ),
		__( 'Environment', 'env-bar' ),
		'manage_options',
		'env_bar_page_callback',
		'env_bar_page_callback',
		'dashicons-admin-site'
	);

	add_settings_section(
		'default',
		'',
		'',
		'env_bar_options_page'
	);

	register_setting(
		'env_bar_options_page',
		'environment_setting'
	);

	add_settings_field(
		'environment_setting',
		__( 'Environment', 'env-bar' ),
		'environment_setting_callback',
		'env_bar_options_page',
		'default'
	);
}

/**
 * Add custom field to site options.
 */
function environment_setting_callback() {
	$environment_option = get_site_option( 'environment_setting' );
	?>
	<label for="environment_setting">
		<select name="environment_setting" id="environment_setting" aria-describedby="environment setting">
			<option value="0">— Select —</option>
			<option value="1" <?php selected( $environment_option, 1 ); ?>><?php echo esc_attr__( 'Development', 'env-bar' ); ?></option>
			<option value="2" <?php selected( $environment_option, 2 ); ?>><?php echo esc_attr__( 'Staging', 'env-bar' ); ?></option>
			<option value="3" <?php selected( $environment_option, 3 ); ?>><?php echo esc_attr__( 'Production', 'env-bar' ); ?></option>
		</select>
	</label>
	<?php
}

/**
 * Network options page output.
 */
function env_bar_network_options_page_callback() {

	$updated = filter_input( INPUT_GET, 'updated' );

	if ( isset( $updated ) ) :
		?>
		<div id="message" class="updated notice is-dismissible">
			<p><?php esc_attr_e( 'Options saved.', 'env-bar' ); ?></p>
		</div>
	<?php endif; ?>
	<div class="wrap">
		<h1><?php esc_attr_e( 'Env Bar Settings', 'env-bar' ); ?></h1>
		<form method="POST" action="edit.php?action=env_bar_update_network_options">
			<?php
			settings_fields( 'env_bar_network_options_page' );
			do_settings_sections( 'env_bar_network_options_page' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

add_action( 'network_admin_edit_env_bar_update_network_options', 'env_bar_update_network_options' );

/**
 * Options page output.
 */
function env_bar_page_callback() {
	?>
	<div class="wrap">
		<h1><?php esc_attr_e( 'Env Bar Settings', 'env-bar' ); ?></h1>
		<form method="POST" action="options.php">
			<?php
			settings_fields( 'env_bar_options_page' );
			do_settings_sections( 'env_bar_options_page' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Update the network options.
 */
function env_bar_update_network_options() {
	// Make sure we are posting from our options page. There's a little surprise
	// here, on the options page we used the 'env_bar_network_options_page'
	// slug when calling 'settings_fields' but we must add the '-options' postfix
	// when we check the referrer.
	check_admin_referer( 'env_bar_network_options_page-options' );

	// This is the list of registered options.
	global $new_whitelist_options;
	$options = $new_whitelist_options['env_bar_network_options_page'];

	// Go through the posted data and save only our options. This is a generic
	// way to do this, but you may want to address the saving of each option
	// individually.
	foreach ( $options as $option ) {

		$post_option = filter_input( INPUT_POST, $option );

		if ( isset( $post_option ) ) {
			// Save our option with the site's options.
			// If we registered a callback function to sanitizes the option's
			// value it will be called here (see register_setting).
			update_site_option( $option, $post_option );
		} else {
			// If the option is not here then delete it. It depends on how you
			// want to manage your defaults however.
			delete_site_option( $option );
		}
	}

	// At last we redirect back to our options page.
	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'env_bar_network_options_page',
				'updated' => 'true',
			), network_admin_url( 'settings.php' )
		)
	);
	exit;
}

/**
 * Output styles in head for admin and frontend.
 */
function env_bar_styles() {

	$environment_option = (int) get_site_option( 'environment_setting' );

	$admin_color = '';
	$environment = '';

	if ( 1 === $environment_option ) {
		$admin_color = '#28cb75';
		$environment = esc_attr__( 'Development', 'env-bar' );
	} elseif ( 2 === $environment_option ) {
		$admin_color = '#2199e8';
		$environment = esc_attr__( 'Staging', 'env-bar' );
	} elseif ( 3 === $environment_option ) {
		$admin_color = '#ed4f32';
		$environment = esc_attr__( 'Production', 'env-bar' );
	}

	if ( 0 !== $environment_option ) {
		?>
		<style type="text/css">
			.env-bar {
				padding-bottom: 28px;
			}

			.env-bar::after {
				position: fixed;
				bottom: 0;
				z-index: 99999;
				width: 100%;
				padding: 5px;
				background-color: <?php echo esc_attr( $admin_color ); ?>;
				content: '<?php echo esc_attr( $environment ) . ' ' . esc_attr__( 'Environment', 'env-bar' ); ?>';
				font-size: 13px;
				font-weight: 600;
				color: #fff;
				text-align: center;
			}
		</style>
		<?php
	}
}

/**
 * Add debugging classes to the body.
 *
 * @param string|array $classes One or more classes to add to the class list.
 *
 * @return array Array of classes.
 */
function env_bar_body_classes( $classes ) {

	$environment_option = (int) get_site_option( 'environment_setting' );
	$environment_classes[] = 'env-bar';

	// Adds a class of is-development to Development.
	if ( 1 === $environment_option ) {
		$environment_classes[] = 'is-development';
	} elseif ( 2 === $environment_option ) {
		$environment_classes[] = 'is-staging';
	} elseif ( 3 === $environment_option ) {
		$environment_classes[] = 'is-production';
	}

	$classes[] = join( ' ', $environment_classes );

	return $classes;
}

/**
 * Add debugging classes to admin body.
 *
 * @return string
 */
function env_bar_admin_body_classes() {

	$environment_option = (int) get_site_option( 'environment_setting' );
	$environment_classes[] = 'env-bar';

	if ( 1 === $environment_option ) {
		$environment_classes[] = 'is-development';
	} elseif ( 2 === $environment_option ) {
		$environment_classes[] = 'is-staging';
	} elseif ( 3 === $environment_option ) {
		$environment_classes[] = 'is-production';
	}

	$classes = join( ' ', $environment_classes );

	return $classes;
}

/**
 * Show environment bar if user is logged in.
 */
function env_bar_ajax_auth_init() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	add_action( 'admin_head', 'env_bar_styles' );
	add_action( 'wp_head', 'env_bar_styles' );
	add_filter( 'body_class', 'env_bar_body_classes' );
	add_filter( 'admin_body_class', 'env_bar_admin_body_classes' );
}

add_action( 'init', 'env_bar_ajax_auth_init' );
