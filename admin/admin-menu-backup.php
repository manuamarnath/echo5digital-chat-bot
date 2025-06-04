<?php
/**
 * Admin Menu Setup for Echo5 AI Chatbot.
 *
 * This file contains the functions to create and manage the admin menu pages
 * for the Echo5 AI Chatbot plugin.
 *
 * @package Echo5_AI_Chatbot
 * @since   0.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die( esc_html__( 'Silence is golden.', 'echo5-ai-chatbot' ) );
}

/**
 * Adds the main admin menu and submenu pages for the Echo5 Chatbot.
 *
 * This function is hooked to the 'admin_menu' action.
 * It creates a top-level menu "Echo5 Chatbot" and submenus for
 * "API Key", "Appearance", and "Chat Logs".
 *
 * @since 0.1.0
 */
function echo5_chatbot_admin_menu() {
	// Add top-level menu page.
	// The first submenu item will inherit this page's title and slug if not explicitly defined.
	add_menu_page(
		esc_html__( 'Echo5 AI Chatbot Settings', 'echo5-ai-chatbot' ), // Page title (used for the first submenu if not overridden).
		esc_html__( 'Echo5 Chatbot', 'echo5-ai-chatbot' ),            // Menu title (for the top-level menu).
		'manage_options',                                              // Capability required.
		'echo5_chatbot_main_settings',                                 // Menu slug (this will be the slug for the API Key page).
		'echo5_chatbot_api_key_page_html',                             // Function to display the API Key page content.
		'dashicons-format-chat',                                       // Icon URL.
		75                                                             // Position in the menu.
	);

	// Add submenu page for API Key Settings.
	// This explicitly defines the "API Key" submenu, making it the default page for 'echo5_chatbot_main_settings'.
	add_submenu_page(
		'echo5_chatbot_main_settings',                                  // Parent slug.
		esc_html__( 'API Key Settings', 'echo5-ai-chatbot' ),          // Page title.
		esc_html__( 'API Key', 'echo5-ai-chatbot' ),                   // Menu title.
		'manage_options',                                               // Capability.
		'echo5_chatbot_main_settings',                                  // Menu slug (same as parent's, makes this the landing page).
		'echo5_chatbot_api_key_page_html'                               // Function to display page content.
	);

	// Add submenu page for Appearance Settings.
	add_submenu_page(
		'echo5_chatbot_main_settings',                                  // Parent slug.
		esc_html__( 'Appearance Settings', 'echo5-ai-chatbot' ),       // Page title.
		esc_html__( 'Appearance', 'echo5-ai-chatbot' ),                // Menu title.
		'manage_options',                                               // Capability.
		'echo5_chatbot_appearance_settings',                            // Menu slug (unique for this page).
		'echo5_chatbot_appearance_settings_page_html'                   // Function to display page content.
	);

	// Add submenu page for Chat Logs (Placeholder).
	add_submenu_page(
		'echo5_chatbot_main_settings',                                  // Parent slug.
		esc_html__( 'Chat Logs', 'echo5-ai-chatbot' ),                 // Page title.
		esc_html__( 'Chat Logs', 'echo5-ai-chatbot' ),                 // Menu title.
		'manage_options',                                               // Capability.
		'echo5_chatbot_chat_logs',                                      // Menu slug.
		'echo5_chatbot_chat_logs_page_html'                             // Function to display page content.
	);
}
add_action( 'admin_menu', 'echo5_chatbot_admin_menu' );


if ( ! function_exists( 'echo5_chatbot_api_key_page_html' ) ) {
	/**
	 * Renders the HTML for the API Key settings page.
	 *
	 * This page is currently a placeholder.
	 *
	 * @since 0.1.0
	 */
	function echo5_chatbot_api_key_page_html() {
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'echo5-ai-chatbot' ) );
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="POST" action="options.php">
				<?php
				// Output nonce, action, and option_page fields for the settings group.
				settings_fields( 'echo5_chatbot_api_key_settings_group' ); // This MUST match the group name used in register_setting().

				// Output the settings sections and their fields for this page.
				// The page slug 'echo5_chatbot_main_settings' MUST match the page slug used in add_settings_section() and add_settings_field().
				do_settings_sections( 'echo5_chatbot_main_settings' );

				// Output submit button.
				submit_button( esc_html__( 'Save API Key', 'echo5-ai-chatbot' ) );
				?>
			</form>
		</div>
		<?php
	}
}


if ( ! function_exists( 'echo5_chatbot_appearance_settings_page_html' ) ) {
	/**
	 * Renders the HTML for the Appearance Settings page.
	 *
	 * This page allows administrators to customize the appearance of the chat interface,
	 * including colors and the welcome message. It uses the WordPress Settings API.
	 *
	 * @since 0.1.0
	 */
	function echo5_chatbot_appearance_settings_page_html() {
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'echo5-ai-chatbot' ) );
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="POST" action="options.php">
				<?php
				// Output nonce, action, and option_page fields for a settings page.
				settings_fields( 'echo5_chatbot_appearance' ); // Matches the option group in settings-page.php.

				// Output the settings sections and their fields for this page.
				do_settings_sections( 'echo5_chatbot_appearance_settings' ); // Matches the page slug used in add_settings_section.

				// Output submit button.
				submit_button( esc_html__( 'Save Appearance Settings', 'echo5-ai-chatbot' ) );
				?>
			</form>
		</div>
		<?php
	}
}


if ( ! function_exists( 'echo5_chatbot_chat_logs_page_html' ) ) {
	/**
	 * Renders the HTML for the Chat Logs page.
	 *
	 * This page is currently a placeholder for displaying chat logs.
	 *
	 * @since 0.1.0
	 */
	function echo5_chatbot_chat_logs_page_html() {
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'echo5-ai-chatbot' ) );
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p><?php esc_html_e( 'View chat logs here. This feature is under development.', 'echo5-ai-chatbot' ); ?></p>
			<!-- Chat logs display functionality will be added here in a future update. -->
		</div>
		<?php
	}
}
