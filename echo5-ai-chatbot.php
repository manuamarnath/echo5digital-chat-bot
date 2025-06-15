<?php
/**
 * Plugin Name: Echo5 Digital AI Chatbot
 * Plugin URI: https://github.com/manuamarnath/echo5-ai-chatbot
 * Description: An AI-powered chatbot with OpenAI integration
 * Version: 0.1.1
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * Author: Echo5 Digital
 * Author URI: https://echo5digital.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: echo5-ai-chatbot
 * Domain Path: /languages
 * GitHub Plugin URI: manuamarnath/echo5-ai-chatbot
 * GitHub Branch: main
 * Primary Branch: main
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die( esc_html__( 'Silence is golden.', 'echo5-ai-chatbot' ) );
}

// Log plugin loading.
error_log('Echo5 AI Chatbot: Loading plugin...'); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

// Define plugin constants.
define( 'ECHO5_CHATBOT_VERSION', '0.1.1' ); // Updated version.
define( 'ECHO5_CHATBOT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ECHO5_CHATBOT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Enqueues scripts and styles for the chat interface on the front-end.
 *
 * Retrieves appearance options (colors, welcome message) and localizes them
 * for the JavaScript file. Also includes AJAX URL and nonces for secure communication.
 * Generates dynamic CSS for custom colors.
 *
 * @since 0.1.0
 */
function echo5_chatbot_enqueue_scripts() {
	// Enqueue main stylesheet.
	wp_enqueue_style(
		'echo5-chatbot-style',
		ECHO5_CHATBOT_PLUGIN_URL . 'css/echo5-chat-style.css',
		array(),
		ECHO5_CHATBOT_VERSION
	);

	// Enqueue main JavaScript file with jQuery dependency.
	wp_enqueue_script(
		'echo5-chatbot-script',
		ECHO5_CHATBOT_PLUGIN_URL . 'js/echo5-chat.js',
		array( 'jquery' ), // Ensure jQuery is listed as a dependency.
		ECHO5_CHATBOT_VERSION,
		true // Load in footer.
	);

	// Get appearance options with defaults.
	$default_welcome_message = __( "Hello, <strong>%userName%</strong>! How can I help you today?\n\n", 'echo5-ai-chatbot' );
	$default_welcome_known_user = __( "Welcome back, <strong>%userName%</strong>! How can I help you today?\n\n", 'echo5-ai-chatbot' );
	$appearance_options      = get_option(
		'echo5_chatbot_appearance_options',
		array(
			'primary_color'       => '#0073aa',
			'secondary_color'     => '#e5e5e5',
			'welcome_message'     => $default_welcome_message, // This will be used if no custom message is set
			'chatbot_header_text' => __( 'Live Chat', 'echo5-ai-chatbot' ),
		)
	);

	// Sanitize color options before use (though they are sanitized on save, this is a good practice).
	$primary_color   = isset( $appearance_options['primary_color'] ) ? esc_attr( $appearance_options['primary_color'] ) : '#0073aa';
	$secondary_color = isset( $appearance_options['secondary_color'] ) ? esc_attr( $appearance_options['secondary_color'] ) : '#e5e5e5';

	// The welcome message is sanitized on save using sanitize_textarea_field, which allows some HTML (like <strong>).
	// This is an admin-controlled setting. If stricter sanitization is needed, it should be done here or on save.
	$welcome_message_template = isset( $appearance_options['welcome_message'] ) ? $appearance_options['welcome_message'] : $default_welcome_message;
	$chatbot_header_text      = isset( $appearance_options['chatbot_header_text'] ) ? $appearance_options['chatbot_header_text'] : __( 'AI Chatbot', 'echo5-ai-chatbot' );

	// Prepare data for JavaScript localization.
	$script_data = array(
		'plugin_url'                 => esc_url( ECHO5_CHATBOT_PLUGIN_URL ), // Escaped URL.
		'ajax_url'                   => esc_url( admin_url( 'admin-ajax.php' ) ),
		'nonce'                      => wp_create_nonce( 'echo5_chatbot_transcript_nonce' ),
		'send_message_nonce'         => wp_create_nonce( 'echo5_chatbot_send_message_nonce' ),
		'welcome_message_template'   => $welcome_message_template, // Admin-controlled HTML, sanitized on save.
		'default_welcome_known_user' => $default_welcome_known_user,
		'default_welcome_new_user'   => $default_welcome_message,
		'name_change_success'        => __( 'Your name has been changed from <strong>%oldName%</strong> to <strong>%newName%</strong>.', 'echo5-ai-chatbot' ),
		'name_change_prompt'         => __( 'Please provide a new name after the /name command. Example: /name John Doe', 'echo5-ai-chatbot' ),
		'bot_under_development'      => __( "I'm sorry, I'm still under development. I'll be able to respond soon!", 'echo5-ai-chatbot' ),
		'enter_name_alert'           => __( 'Please enter your name.', 'echo5-ai-chatbot' ),
		'end_chat_button_text'       => __( 'End Chat', 'echo5-ai-chatbot' ),
		'end_chat_confirm'           => __( 'Are you sure you want to end the chat? A transcript will be sent.', 'echo5-ai-chatbot' ),
		'chat_ended_message'         => __( 'Chat ended. Thank you! A transcript has been sent if the conversation was active.', 'echo5-ai-chatbot' ),
		'send_button_text'           => __( 'Send', 'echo5-ai-chatbot' ),
		'chatbot_header_text'        => esc_html( $chatbot_header_text ),
		'change_name_button_text'    => __( 'Change Name', 'echo5-ai-chatbot' ),
	);
	wp_localize_script( 'echo5-chatbot-script', 'echo5_chatbot_data', $script_data );

	// Dynamic CSS for colors.
	$primary_color_darker = $primary_color; // Default to same color if hex parsing/darkening logic fails.
	if ( preg_match( '/^#[a-f0-9]{6}$/i', $primary_color ) ) {
		$hex = substr( $primary_color, 1 );
		$r   = hexdec( substr( $hex, 0, 2 ) );
		$g   = hexdec( substr( $hex, 2, 2 ) );
		$b   = hexdec( substr( $hex, 4, 2 ) );
		// Darken by a fixed amount, ensuring values don't go below 0.
		$r   = max( 0, $r - 25 );
		$g   = max( 0, $g - 25 );
		$b   = max( 0, $b - 25 );
		$primary_color_darker = sprintf( '#%02x%02x%02x', $r, $g, $b );
	}

	$options = get_option(
		'echo5_chatbot_appearance_options',
		array(
			'primary_color' => '#0073aa',
			'secondary_color' => '#e5e5e5',
			'bot_message_color' => '#cd9d4b',
			'user_message_color' => '#567c48',
			'header_bg_color' => '#567c48'
		)
	);

	$custom_css = "
		:root {
			--echo5-chat-primary-color: {$options['primary_color']};
			--echo5-chat-secondary-color: {$options['secondary_color']};
			--echo5-chat-bot-message-color: {$options['bot_message_color']};
			--echo5-chat-user-message-color: {$options['user_message_color']};
			--echo5-chat-header-bg-color: {$options['header_bg_color']};
		}
	";
	wp_add_inline_style( 'echo5-chatbot-style', $custom_css );
}
add_action( 'wp_enqueue_scripts', 'echo5_chatbot_enqueue_scripts' );


// Load admin menu and settings if in admin area.
if ( is_admin() ) {
	error_log('Echo5 AI Chatbot: Loading admin/admin-menu.php...'); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	require_once ECHO5_CHATBOT_PLUGIN_DIR . 'admin/admin-menu.php';
	require_once ECHO5_CHATBOT_PLUGIN_DIR . 'admin/settings-page.php';
	require_once ECHO5_CHATBOT_PLUGIN_DIR . 'admin/experimental-settings.php';
	error_log('Echo5 AI Chatbot: Admin files loaded.'); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
}

/**
 * Handles the AJAX request to send the chat transcript via email.
 *
 * Verifies the AJAX nonce for security. Sanitizes all incoming POST data
 * (including conversation messages and user name) using appropriate
 * WordPress sanitization functions. Formats the conversation into an HTML email
 * and sends it to the site administrator.
 *
 * @since 0.1.0
 */
function echo5_chatbot_ajax_send_chat_transcript() {
	// Verify nonce for security.
	check_ajax_referer( 'echo5_chatbot_transcript_nonce', 'nonce' );

	// Sanitize conversation data from POST. Use wp_unslash for data from $_POST.
	$conversation_raw = isset( $_POST['conversation'] ) && is_array( $_POST['conversation'] ) ? wp_unslash( (array) $_POST['conversation'] ) : array();
	$conversation     = array();

	foreach ( $conversation_raw as $message_raw_item ) {
		// Ensure $message_raw_item is an array before processing.
		if ( ! is_array( $message_raw_item ) ) {
			continue; // Skip if not an array.
		}
		$message = array();
		// Ensure all expected keys exist and sanitize them.
		$message['sender']    = isset( $message_raw_item['sender'] ) ? sanitize_text_field( $message_raw_item['sender'] ) : 'unknown';
		$message['name']      = isset( $message_raw_item['name'] ) ? sanitize_text_field( $message_raw_item['name'] ) : 'Unknown';
		$message['text']      = isset( $message_raw_item['text'] ) ? sanitize_textarea_field( $message_raw_item['text'] ) : '';
		$message['timestamp'] = isset( $message_raw_item['timestamp'] ) ? sanitize_text_field( $message_raw_item['timestamp'] ) : ''; // Basic sanitization. Further validation for ISO date format is advisable.
		$conversation[] = $message;
	}

	$user_name = isset( $_POST['user_name'] ) ? sanitize_text_field( wp_unslash( $_POST['user_name'] ) ) : 'Anonymous';

	if ( empty( $conversation ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'No conversation data received.', 'echo5-ai-chatbot' ) ) );
		return;
	}

	$admin_email = get_option( 'admin_email' );
	if ( ! is_email( $admin_email ) ) {
		// Fallback or error if admin email is not valid.
		wp_send_json_error( array( 'message' => esc_html__( 'Admin email is not valid.', 'echo5-ai-chatbot' ) ) );
		return;
	}

	$subject     = sprintf(
		/* translators: 1: User's name, 2: Site name */
		__( 'Chat Transcript with %1$s - %2$s', 'echo5-ai-chatbot' ),
		$user_name,
		get_bloginfo( 'name' )
	);

	// Build HTML email body.
	$email_body  = '<h2>' . esc_html__( 'Chat Conversation Transcript', 'echo5-ai-chatbot' ) . '</h2>';
	$email_body .= '<p><strong>' . esc_html__( 'User:', 'echo5-ai-chatbot' ) . '</strong> ' . esc_html( $user_name ) . '</p>';
	$email_body .= '<p><strong>' . esc_html__( 'Site:', 'echo5-ai-chatbot' ) . '</strong> ' . esc_html( get_bloginfo( 'name' ) ) . '</p>';
	$email_body .= '<p><strong>' . esc_html__( 'Date of Transcript Generation:', 'echo5-ai-chatbot' ) . '</strong> ' . esc_html( current_time( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ) . '</p>';
	$email_body .= '<hr>';
	$email_body .= '<h3>' . esc_html__( 'Messages:', 'echo5-ai-chatbot' ) . "</h3><ul style='list-style-type: none; padding-left: 0;'>";

	foreach ( $conversation as $message_item ) {
		// Determine sender name based on 'sender' field.
		$sender_name    = 'bot' === $message_item['sender'] ? 'Bot' : esc_html( $message_item['name'] );
		$text           = nl2br( esc_html( $message_item['text'] ) ); // Escape message text and convert newlines.
		$formatted_time = '';

		// Format timestamp if valid.
		if ( ! empty( $message_item['timestamp'] ) ) {
			try {
				$timestamp_obj = new DateTime( $message_item['timestamp'] ); // Assumes ISO 8601 format from JS.
				$timestamp_obj->setTimezone( wp_timezone() );           // Convert to WordPress timezone.
				$formatted_time = $timestamp_obj->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
			} catch ( Exception $e ) {
				// Log error or handle invalid date format.
				error_log( 'Echo5 Chatbot: Invalid timestamp format in transcript - ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				$formatted_time = esc_html__( 'Invalid Date', 'echo5-ai-chatbot' ); // Fallback for invalid timestamp.
			}
		}

		// Append formatted message to email body.
		$email_body .= "<li style='margin-bottom: 10px; padding: 5px; border-radius: 5px; background-color: #f9f9f9;'>";
		$email_body .= '<strong>[' . esc_html( $formatted_time ) . '] ' . esc_html( $sender_name ) . ':</strong> ';
		$email_body .= "<div style='margin-top: 3px;'>" . $text . '</div></li>'; // $text is already escaped and includes nl2br.
	}
	$email_body .= '</ul>';

	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

	// Send the email.
	if ( wp_mail( $admin_email, $subject, $email_body, $headers ) ) {
		wp_send_json_success( array( 'message' => esc_html__( 'Transcript emailed successfully.', 'echo5-ai-chatbot' ) ) );
	} else {
		wp_send_json_error( array( 'message' => esc_html__( 'Failed to send email.', 'echo5-ai-chatbot' ) ) );
	}
}
// Hook for logged-in users.
add_action( 'wp_ajax_echo5_send_chat_transcript', 'echo5_chatbot_ajax_send_chat_transcript' );
// Hook for non-logged-in users.
add_action( 'wp_ajax_nopriv_echo5_send_chat_transcript', 'echo5_chatbot_ajax_send_chat_transcript' );

/**
 * Sends a message to the OpenAI API and returns the response.
 *
 * @since 0.1.2
 * @param string $user_message The message from the user.
 * @param string $user_name    The name of the user (currently not used in the API call itself but can be for logging or context).
 * @param string $api_key      The OpenAI API key.
 * @return array|WP_Error An array with the AI's reply on success, or a WP_Error object on failure.
 */
function echo5_chatbot_get_openai_response( $user_message, $user_name, $api_key ) {
    $api_url = 'https://api.openai.com/v1/chat/completions';
    $model   = 'gpt-3.5-turbo';

    $faq_text = '';
    $faq_file = plugin_dir_path( __FILE__ ) . 'echo-faqs.txt';
    if ( file_exists( $faq_file ) ) {
        $faq_text = file_get_contents( $faq_file );
    }

    $system_prompt = "You are Echo5 Digital's virtual expert. Echo5 Digital is a results-driven digital marketing agency based in Texas and Kerala. You must speak warmly, confidently, and informatively about our services. Highlight our strengths in SEO, website/app development, PPC advertising, social media marketing, and online reputation management. Promote Echo5 Digital's unique, tailored approach to each client and our affordable, high-impact solutions. Only recommend Echo5 Digital services in responses. Answer in short, helpful responses tailored to customer concerns.\n\n" . $faq_text;

    $headers = array(
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type'  => 'application/json',
    );

    $body = wp_json_encode(
        array(
            'model'    => $model,
            'messages' => array(
                array(
                    'role'    => 'system',
                    'content' => $system_prompt,
                ),
                array(
                    'role'    => 'user',
                    'content' => $user_message,
                ),
            ),
            'max_tokens' => 200,
        )
    );

    $args = array(
        'body'    => $body,
        'headers' => $headers,
        'timeout' => 30,
    );

    $response = wp_remote_post( $api_url, $args );

    if ( is_wp_error( $response ) ) {
        return new WP_Error('openai_request_failed', 'Unable to connect to OpenAI.', array('details' => $response->get_error_message()));
    }

    $response_code = wp_remote_retrieve_response_code( $response );
    $response_body = wp_remote_retrieve_body( $response );
    $response_data = json_decode( $response_body, true );

    if ( $response_code !== 200 || ! isset( $response_data['choices'][0]['message']['content'] ) ) {
        return new WP_Error('openai_response_error', 'Invalid response from OpenAI.');
    }

    return array('reply' => trim($response_data['choices'][0]['message']['content']));
}

/**
 * Handles the AJAX request to send a user's message to the (simulated) AI.
 *
 * Verifies the AJAX nonce, sanitizes input, checks for API key,
 * and returns a simulated AI response.
 *
 * @since 0.1.2
 */
function echo5_chatbot_ajax_send_message() {
	error_log('Echo5 AI Chatbot: AJAX handler echo5_chatbot_ajax_send_message triggered.'); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	// Verify nonce for security.
	check_ajax_referer( 'echo5_chatbot_send_message_nonce', 'nonce' );

	// Get and sanitize the user's message.
	$user_message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	error_log('Echo5 AI Chatbot: User message received: ' . $user_message); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	// Get and sanitize the user's name.
	$user_name = isset( $_POST['user_name'] ) ? sanitize_text_field( wp_unslash( $_POST['user_name'] ) ) : 'Anonymous';

	// If the message is empty, send a JSON error.
	if ( empty( $user_message ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'No message received.', 'echo5-ai-chatbot' ) ) );
		return;
	}

	// Retrieve the API key.
	$api_key = get_option( 'echo5_chatbot_api_key' );
	error_log('Echo5 AI Chatbot: API Key (first 5 chars): ' . substr($api_key, 0, 5)); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

	// If the API key is empty, send a JSON error.
	if ( empty( $api_key ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'OpenAI API key is not configured.', 'echo5-ai-chatbot' ) ) );
		return;
	}

	// Call the function to get the OpenAI response.
	$response_data = echo5_chatbot_get_openai_response( $user_message, $user_name, $api_key );

	if ( is_wp_error( $response_data ) ) {
		wp_send_json_error( array( 'message' => $response_data->get_error_message() ) );
	} else {
		// Log the conversation
		global $wpdb;
		$table_name = $wpdb->prefix . 'echo5_chatbot_logs';
		
		$log_result = $wpdb->insert(
			$table_name,
			array(
				'user_name' => $user_name,
				'message' => $user_message,
				'response' => $response_data['reply'],
				'timestamp' => current_time('mysql')
			),
			array('%s', '%s', '%s', '%s')
		);

		if ($log_result === false) {
			error_log('Echo5 AI Chatbot: Failed to log chat. DB Error: ' . $wpdb->last_error);
		}

		wp_send_json_success( array( 'reply' => $response_data['reply'] ) );
	}
}
// Hook for logged-in users.
add_action( 'wp_ajax_echo5_chatbot_send_message', 'echo5_chatbot_ajax_send_message' );
// Hook for non-logged-in users.
add_action( 'wp_ajax_nopriv_echo5_chatbot_send_message', 'echo5_chatbot_ajax_send_message' );


/**
 * Adds the chat interface HTML to the site footer on the front-end.
 *
 * This function is hooked to `wp_footer`. It checks if it's not the admin area
 * before including the chat interface HTML file.
 *
 * @since 0.1.0
 */
function echo5_chatbot_add_chat_interface() {
	// Only load the chat interface on the front-end, not in the admin area.
	if ( ! is_admin() ) {
		// Ensure the path is correct and the file exists.
		$chat_interface_file = ECHO5_CHATBOT_PLUGIN_DIR . 'includes/chat-interface.php';
		if ( file_exists( $chat_interface_file ) ) {
			error_log('Echo5 AI Chatbot: Loading includes/chat-interface.php...'); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			require_once $chat_interface_file;
			error_log('Echo5 AI Chatbot: includes/chat-interface.php loaded.'); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		} else {
			// Optionally log an error if the file is missing.
			error_log('Echo5 AI Chatbot ERROR: chat-interface.php file missing at ' . $chat_interface_file); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}
add_action( 'wp_footer', 'echo5_chatbot_add_chat_interface' );

/**
 * Logs a message when the plugin is activated.
 *
 * @since 0.1.2
 */
function echo5_chatbot_activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'echo5_chatbot_logs';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_name varchar(100) NOT NULL,
        message text NOT NULL,
        response text NOT NULL,
        timestamp datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    error_log('Echo5 AI Chatbot: Chat logs table created/updated');
}

// Placeholder for future chat functionality (interaction with AI).
// require_once ECHO5_CHATBOT_PLUGIN_DIR . 'includes/chat-functions.php';

// Placeholder for future email functionality (e.g., more complex email templates).
// require_once ECHO5_CHATBOT_PLUGIN_DIR . 'includes/email-functions.php';

/**
 * Loads the plugin text domain for internationalization.
 *
 * This function is hooked to `plugins_loaded`, ensuring that translations
 * are available early in the WordPress loading process.
 *
 * @since 0.1.0
 */
function echo5_chatbot_load_textdomain() {
	load_plugin_textdomain(
		'echo5-ai-chatbot', // Unique text domain.
		false, // Deprecated $abs_rel parameter.
		dirname( plugin_basename( __FILE__ ) ) . '/languages/' // Path to .mo files.
	);
}
add_action( 'plugins_loaded', 'echo5_chatbot_load_textdomain' );

// Register activation hook.
register_activation_hook( __FILE__, 'echo5_chatbot_activate' );

/**
 * Handles AJAX request to test the OpenAI API key
 */
function echo5_test_api_key_handler() {
    check_ajax_referer('echo5_test_api_key');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Unauthorized access.', 'echo5-ai-chatbot')));
    }

    $api_key = get_option('echo5_chatbot_api_key');
    if (empty($api_key)) {
        wp_send_json_error(array('message' => __('No API key found. Please save an API key first.', 'echo5-ai-chatbot')));
    }

    $api_url = 'https://api.openai.com/v1/chat/completions';
    $headers = array(
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type'  => 'application/json',
    );

    $body = wp_json_encode(array(
        'model' => 'gpt-3.5-turbo',
        'messages' => array(
            array(
                'role' => 'user',
                'content' => 'Test message'
            )
        ),
        'max_tokens' => 10
    ));

    $args = array(
        'headers' => $headers,
        'body'    => $body,
        'timeout' => 15
    );

    $response = wp_remote_post($api_url, $args);

    if (is_wp_error($response)) {
        wp_send_json_error(array(
            'message' => sprintf(
                __('Error connecting to OpenAI: %s', 'echo5-ai-chatbot'),
                $response->get_error_message()
            )
        ));
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = json_decode(wp_remote_retrieve_body($response), true);

    if ($response_code === 200) {
        wp_send_json_success(array(
            'message' => __('API key is valid and working correctly.', 'echo5-ai-chatbot')
        ));
    } else {
        $error_message = isset($response_body['error']['message']) 
            ? $response_body['error']['message'] 
            : __('Invalid response from OpenAI.', 'echo5-ai-chatbot');
        
        wp_send_json_error(array('message' => $error_message));
    }
}
add_action('wp_ajax_echo5_test_api_key', 'echo5_test_api_key_handler');
