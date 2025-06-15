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
 * "API Key", "Appearance", "Chat Logs", and "AI Training".
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

	// Add submenu page for AI Training
	add_submenu_page(
		'echo5_chatbot_main_settings',                                  // Parent slug
		esc_html__('AI Training Data', 'echo5-ai-chatbot'),           // Page title
		esc_html__('AI Training', 'echo5-ai-chatbot'),                // Menu title
		'manage_options',                                               // Capability
		'echo5_chatbot_ai_training',                                    // Menu slug
		'echo5_chatbot_ai_training_page_html'                          // Function to display page content
	);

	// Add submenu page for Experimental Features
	add_submenu_page(
		'echo5_chatbot_main_settings',
		esc_html__('Experimental Features', 'echo5-ai-chatbot'),
		esc_html__('Experimental', 'echo5-ai-chatbot'),
		'manage_options',
		'echo5_chatbot_experimental',
		'echo5_chatbot_experimental_page_html'
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
	 * This page displays the chat logs with pagination.
	 *
	 * @since 0.1.0
	 */
	function echo5_chatbot_chat_logs_page_html() {
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'echo5-ai-chatbot'));
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'echo5_chatbot_logs';

		// First, check if the table exists
		$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
		if (!$table_exists) {
			echo '<div class="notice notice-error"><p>' . 
				 esc_html__('Chat logs table does not exist. Please deactivate and reactivate the plugin.', 'echo5-ai-chatbot') . 
				 '</p></div>';
			return;
		}

		// Handle bulk delete action
		if (isset($_POST['action']) && $_POST['action'] === 'bulk_delete' && isset($_POST['delete_logs'])) {
			check_admin_referer('echo5_bulk_delete_logs');
			$ids = array_map('intval', $_POST['delete_logs']);
			$ids_string = implode(',', array_fill(0, count($ids), '%d'));
			$wpdb->query($wpdb->prepare(
				"DELETE FROM $table_name WHERE id IN ($ids_string)",
				$ids
			));
			echo '<div class="notice notice-success"><p>' . 
				 esc_html__('Selected logs deleted successfully.', 'echo5-ai-chatbot') . 
				 '</p></div>';
		}

		// Pagination settings
		$per_page = 20;
		$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
		$offset = ($current_page - 1) * $per_page;

		// Get total count and logs
		$total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");
		$logs = $wpdb->get_results($wpdb->prepare(
			"SELECT * FROM $table_name ORDER BY timestamp DESC LIMIT %d OFFSET %d",
			$per_page,
			$offset
		));
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php settings_errors('echo5_messages'); ?>

			<!-- Filter Form -->
			<div class="tablenav top">
				<form method="get" class="alignleft actions">
					<input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>">
					<input type="text" name="search" value="<?php echo esc_attr($search_term); ?>" placeholder="<?php esc_attr_e('Search logs...', 'echo5-ai-chatbot'); ?>">
					<input type="date" name="date_from" value="<?php echo esc_attr($date_from); ?>" placeholder="<?php esc_attr_e('From date', 'echo5-ai-chatbot'); ?>">
					<input type="date" name="date_to" value="<?php echo esc_attr($date_to); ?>" placeholder="<?php esc_attr_e('To date', 'echo5-ai-chatbot'); ?>">
					<input type="submit" class="button" value="<?php esc_attr_e('Filter', 'echo5-ai-chatbot'); ?>">
				</form>
			</div>

			<form method="post">
				<?php wp_nonce_field('echo5_bulk_delete_logs'); ?>
				<input type="hidden" name="action" value="bulk_delete">
				
				<div class="tablenav top">
					<div class="alignleft actions bulkactions">
						<input type="submit" class="button action" value="<?php esc_attr_e('Delete Selected', 'echo5-ai-chatbot'); ?>" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete selected logs?', 'echo5-ai-chatbot'); ?>');">
					</div>
					<div class="tablenav-pages">
						<span class="displaying-num">
							<?php printf(esc_html(_n('%s item', '%s items', $total_items, 'echo5-ai-chatbot')), number_format_i18n($total_items)); ?>
						</span>
					</div>
				</div>

				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<td class="manage-column column-cb check-column">
								<input type="checkbox" id="cb-select-all-1">
							</td>
							<th><?php esc_html_e('ID', 'echo5-ai-chatbot'); ?></th>
							<th><?php esc_html_e('Time', 'echo5-ai-chatbot'); ?></th>
							<th><?php esc_html_e('User', 'echo5-ai-chatbot'); ?></th>
							<th><?php esc_html_e('Message', 'echo5-ai-chatbot'); ?></th>
							<th><?php esc_html_e('Response', 'echo5-ai-chatbot'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($logs as $log): ?>
							<tr>
								<th scope="row" class="check-column">
									<input type="checkbox" name="delete_logs[]" value="<?php echo esc_attr($log->id); ?>">
								</th>
								<td><?php echo esc_html($log->id); ?></td>
								<td><?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->timestamp))); ?></td>
								<td><?php echo esc_html($log->user_name); ?></td>
								<td><?php echo nl2br(esc_html($log->message)); ?></td>
								<td><?php echo nl2br(esc_html($log->response)); ?></td>
							</tr>
						<?php endforeach; ?>
						<?php if (empty($logs)): ?>
							<tr>
								<td colspan="6"><?php esc_html_e('No chat logs found.', 'echo5-ai-chatbot'); ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>

				<?php
				$total_pages = ceil($total_items / $per_page);
				if ($total_pages > 1): ?>
					<div class="tablenav bottom">
						<div class="tablenav-pages">
							<?php
							echo paginate_links(array(
								'base' => add_query_arg('paged', '%#%'),
								'format' => '',
								'prev_text' => __('&laquo;'),
								'next_text' => __('&raquo;'),
								'total' => $total_pages,
								'current' => $current_page,
								'add_args' => array(
									'search' => $search_term,
									'date_from' => $date_from,
									'date_to' => $date_to
								)
							));
							?>
						</div>
					</div>
				<?php endif; ?>
			</form>
		</div>
		<?php
	}
}


if ( ! function_exists( 'echo5_chatbot_ai_training_page_html' ) ) {
	/**
	 * Renders the HTML for the AI Training page.
	 *
	 * This page allows administrators to edit the FAQs used for AI training.
	 *
	 * @since 0.1.0
	 */
	function echo5_chatbot_ai_training_page_html() {
		// Check user capabilities
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'echo5-ai-chatbot'));
		}

		// Handle form submission
		if (isset($_POST['echo5_faq_content']) && check_admin_referer('echo5_save_faq')) {
			$content = wp_unslash($_POST['echo5_faq_content']);
			$file = ECHO5_CHATBOT_PLUGIN_DIR . 'echo-faqs.txt';
			
			if (file_put_contents($file, $content) !== false) {
				add_settings_error(
					'echo5_messages',
					'echo5_message',
					__('FAQ content updated successfully.', 'echo5-ai-chatbot'),
					'updated'
				);
			} else {
				add_settings_error(
					'echo5_messages',
					'echo5_message',
					__('Error saving FAQ content. Please check file permissions.', 'echo5-ai-chatbot'),
					'error'
				);
			}
		}

		// Get current FAQ content
		$faq_content = '';
		$faq_file = ECHO5_CHATBOT_PLUGIN_DIR . 'echo-faqs.txt';
		if (file_exists($faq_file)) {
			$faq_content = file_get_contents($faq_file);
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
			
			<?php settings_errors('echo5_messages'); ?>

			<div class="card">
				<h2><?php esc_html_e('FAQ Training Data', 'echo5-ai-chatbot'); ?></h2>
				<p><?php esc_html_e('Edit the FAQ content below. This data will be used to train the AI chatbot for better responses.', 'echo5-ai-chatbot'); ?></p>
				<p><?php esc_html_e('Format: Use Q: for questions and A: for answers. Each Q&A pair should be separated by a blank line.', 'echo5-ai-chatbot'); ?></p>
			</div>

			<form method="post" action="">
				<?php wp_nonce_field('echo5_save_faq'); ?>
				<table class="form-table">
					<tr>
						<td>
							<textarea name="echo5_faq_content" rows="20" style="width: 100%; font-family: monospace;"><?php echo esc_textarea($faq_content); ?></textarea>
						</td>
					</tr>
				</table>
				<?php submit_button(__('Save FAQ Content', 'echo5-ai-chatbot')); ?>
			</form>
		</div>
		<?php
	}
}


if ( ! function_exists( 'echo5_chatbot_experimental_page_html' ) ) {
	/**
	 * Renders the HTML for the Experimental Features page.
	 *
	 * This page allows administrators to configure experimental features for the AI Chatbot.
	 *
	 * @since 0.1.0
	 */
	function echo5_chatbot_experimental_page_html() {
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'echo5-ai-chatbot'));
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
			
			<div class="notice notice-warning">
				<p><strong><?php esc_html_e('Warning:', 'echo5-ai-chatbot'); ?></strong> 
				<?php esc_html_e('These features are experimental and may not work as expected.', 'echo5-ai-chatbot'); ?></p>
			</div>

			<form method="post" action="options.php">
				<?php
				settings_fields('echo5_chatbot_experimental_options');
				do_settings_sections('echo5_chatbot_experimental');
				submit_button(__('Save Experimental Settings', 'echo5-ai-chatbot'));
				?>
			</form>
		</div>
		<?php
	}
}
