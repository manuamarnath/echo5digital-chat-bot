<?php
/**
 * Admin Settings Page for Echo5 AI Chatbot.
 *
 * This file contains functions to register and display settings for the
 * Echo5 AI Chatbot plugin, specifically for the "Appearance" settings page.
 * It includes color pickers and a welcome message text area.
 *
 * @package Echo5_AI_Chatbot
 * @since   0.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die( esc_html__( 'Silence is golden.', 'echo5-ai-chatbot' ) );
}

/**
 * Sanitizes the API key input.
 *
 * @since 0.1.2
 * @param string $input The API key to sanitize.
 * @return string The sanitized API key.
 */
function echo5_chatbot_sanitize_api_key( $input ) {
	error_log('Echo5 AI Chatbot DEBUG: sanitize_api_key - Input received: ' . $input); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	$sanitized_key = sanitize_text_field( $input );
	error_log('Echo5 AI Chatbot DEBUG: sanitize_api_key - Sanitized key: ' . $sanitized_key); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	return $sanitized_key;
}

/**
 * Renders the description for the API Key Configuration section.
 *
 * @since 0.1.2
 */
function echo5_chatbot_api_key_section_callback() {
	echo '<p>' . esc_html__( 'Enter your OpenAI API key below. This key is required for the chatbot to communicate with OpenAI.', 'echo5-ai-chatbot' ) . '</p>';
}

/**
 * Renders the input field for the OpenAI API Key.
 *
 * @since 0.1.2
 */
function echo5_chatbot_api_key_field_callback() {
    $api_key = get_option('echo5_chatbot_api_key', '');
    echo '<input type="password" name="echo5_chatbot_api_key" value="' . esc_attr($api_key) . '" class="regular-text">';
    echo '<button type="button" id="echo5-test-api-key" class="button button-secondary">' . 
         esc_html__('Test API Key', 'echo5-ai-chatbot') . '</button>';
    echo '<div id="echo5-api-key-test-result" style="margin-top: 10px;"></div>';
    echo '<p class="description">' . 
         esc_html__('Enter your OpenAI API key. After saving, use the Test button to verify it works.', 'echo5-ai-chatbot') . 
         '</p>';
    
    // Add JavaScript for API key testing
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#echo5-test-api-key').on('click', function() {
            const button = $(this);
            const resultDiv = $('#echo5-api-key-test-result');
            
            button.prop('disabled', true);
            button.text('Testing...');
            resultDiv.html('');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'echo5_test_api_key',
                    nonce: '<?php echo wp_create_nonce("echo5_test_api_key"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        resultDiv.html('<div class="notice notice-success inline"><p>' + 
                            response.data.message + '</p></div>');
                    } else {
                        resultDiv.html('<div class="notice notice-error inline"><p>' + 
                            response.data.message + '</p></div>');
                    }
                },
                error: function() {
                    resultDiv.html('<div class="notice notice-error inline"><p><?php 
                        esc_html_e("Error testing API key. Please try again.", "echo5-ai-chatbot"); 
                    ?></p></div>');
                },
                complete: function() {
                    button.prop('disabled', false);
                    button.text('<?php esc_html_e("Test API Key", "echo5-ai-chatbot"); ?>');
                }
            });
        });
    });
    </script>
    <?php
}

/**
 * Registers plugin settings, sections, and fields for API key configuration.
 *
 * This function is hooked to 'admin_init'.
 *
 * @since 0.1.2
 */
function echo5_chatbot_register_api_key_settings() {
	// Register a setting for the API key.
	register_setting(
		'echo5_chatbot_api_key_settings_group', // Option group name.
		'echo5_chatbot_api_key',                // Option name (stored in wp_options).
		'echo5_chatbot_sanitize_api_key'        // Sanitize callback function.
	);

	// Add a settings section for API Key Configuration.
	add_settings_section(
		'echo5_chatbot_api_key_section',        // ID of the section.
		esc_html__( 'API Key Configuration', 'echo5-ai-chatbot' ), // Title of the section.
		'echo5_chatbot_api_key_section_callback',// Callback function to render the section description.
		'echo5_chatbot_main_settings'           // Page slug where this section will be shown.
	);

	// Add API Key settings field.
	add_settings_field(
		'echo5_chatbot_api_key_field',          // ID of the field.
		esc_html__( 'OpenAI API Key', 'echo5-ai-chatbot' ), // Title of the field.
		'echo5_chatbot_api_key_field_callback', // Callback function to render the field.
		'echo5_chatbot_main_settings',          // Page slug.
		'echo5_chatbot_api_key_section'         // Section ID.
	);
}
add_action( 'admin_init', 'echo5_chatbot_register_api_key_settings' );

/**
 * Registers plugin settings, sections, and fields for appearance options.
 *
 * This function is hooked to 'admin_init'. It defines the settings group,
 * sections for colors and welcome message, and their respective fields.
 *
 * @since 0.1.0
 */
function echo5_chatbot_register_appearance_settings() {
	// Register a setting for appearance options.
	register_setting(
		'echo5_chatbot_appearance',                 // Option group name.
		'echo5_chatbot_appearance_options',         // Option name (stored in wp_options).
		'echo5_chatbot_sanitize_appearance_options' // Sanitize callback function.
	);

	// Add a settings section for Chat Interface Colors.
	add_settings_section(
		'echo5_chatbot_color_section',              // ID of the section.
		esc_html__( 'Chat Interface Colors', 'echo5-ai-chatbot' ), // Title of the section.
		'echo5_chatbot_color_section_callback',     // Callback function to render the section description.
		'echo5_chatbot_appearance_settings'         // Page slug where this section will be shown (matches menu slug).
	);

	// Add Primary Color settings field.
	add_settings_field(
		'echo5_chatbot_primary_color',              // ID of the field.
		esc_html__( 'Primary Color', 'echo5-ai-chatbot' ), // Title of the field.
		'echo5_chatbot_primary_color_callback',     // Callback function to render the field.
		'echo5_chatbot_appearance_settings',        // Page slug.
		'echo5_chatbot_color_section'               // Section ID.
	);

	// Add Secondary Color settings field.
	add_settings_field(
		'echo5_chatbot_secondary_color',            // ID of the field.
		esc_html__( 'Secondary Color (Bot Messages)', 'echo5-ai-chatbot' ), // Title of the field.
		'echo5_chatbot_secondary_color_callback',   // Callback function to render the field.
		'echo5_chatbot_appearance_settings',        // Page slug.
		'echo5_chatbot_color_section'               // Section ID.
	);

	// Add a settings section for the Welcome Message.
	add_settings_section(
		'echo5_chatbot_welcome_message_section',    // ID of the section.
		esc_html__( 'Welcome Message Customization', 'echo5-ai-chatbot' ), // Title of the section.
		'echo5_chatbot_welcome_message_section_callback', // Callback function.
		'echo5_chatbot_appearance_settings'         // Page slug.
	);

	// Add Welcome Message settings field.
	add_settings_field(
		'echo5_chatbot_welcome_message',            // ID of the field.
		esc_html__( 'Custom Welcome Message', 'echo5-ai-chatbot' ), // Title of the field.
		'echo5_chatbot_welcome_message_callback',   // Callback function to render the field.
		'echo5_chatbot_appearance_settings',        // Page slug.
		'echo5_chatbot_welcome_message_section'     // Section ID.
	);

	// Add Chatbot Header Text settings field.
	add_settings_field(
		'echo5_chatbot_header_text_field',          // ID of the field.
		esc_html__( 'Chatbot Header Text', 'echo5-ai-chatbot' ), // Title of the field.
		'echo5_chatbot_header_text_field_callback', // Callback function to render the field.
		'echo5_chatbot_appearance_settings',        // Page slug.
		'echo5_chatbot_welcome_message_section'     // Section ID (placing it in the same section as welcome message for now).
	);
}
add_action( 'admin_init', 'echo5_chatbot_register_appearance_settings' );
// Note: The remove_action for 'echo5_chatbot_register_color_settings' is removed as it's no longer necessary.

/**
 * Sanitizes the appearance options before saving to the database.
 *
 * Ensures that color values are valid hex codes and the welcome message
 * is appropriately sanitized for textarea content.
 *
 * @since 0.1.0
 * @param array $input The array of options to sanitize.
 * @return array The sanitized array of options.
 */
function echo5_chatbot_sanitize_appearance_options( $input ) {
	$sanitized_input = array();
	$defaults        = array(
		'primary_color'       => '#0073aa',
		'secondary_color'     => '#e5e5e5',
		'welcome_message'     => __( 'Hello, <strong>%userName%</strong>! How can I help you? (You can change your name using /name [new_name])', 'echo5-ai-chatbot' ),
		'chatbot_header_text' => __( 'AI Chatbot', 'echo5-ai-chatbot' ),
	);

	// Sanitize primary color.
	if ( isset( $input['primary_color'] ) ) {
		$sanitized_input['primary_color'] = sanitize_hex_color( $input['primary_color'] );
	} else {
		$sanitized_input['primary_color'] = $defaults['primary_color'];
	}

	// Sanitize secondary color.
	if ( isset( $input['secondary_color'] ) ) {
		$sanitized_input['secondary_color'] = sanitize_hex_color( $input['secondary_color'] );
	} else {
		$sanitized_input['secondary_color'] = $defaults['secondary_color'];
	}

	// Sanitize welcome message.
	// sanitize_textarea_field allows some HTML tags like <strong>, <a>, etc.
	// which is acceptable for an admin-controlled welcome message.
	if ( isset( $input['welcome_message'] ) ) {
		$sanitized_input['welcome_message'] = sanitize_textarea_field( $input['welcome_message'] );
	} else {
		$sanitized_input['welcome_message'] = $defaults['welcome_message'];
	}

	// Sanitize chatbot header text
	if ( isset( $input['chatbot_header_text'] ) && !empty( trim( $input['chatbot_header_text'] ) ) ) {
		$sanitized_input['chatbot_header_text'] = sanitize_text_field( $input['chatbot_header_text'] );
	} else {
		$sanitized_input['chatbot_header_text'] = $defaults['chatbot_header_text']; // Default if empty
	}

	return $sanitized_input;
}

/**
 * Renders the description for the Chat Interface Colors section.
 *
 * @since 0.1.0
 */
function echo5_chatbot_color_section_callback() {
	echo '<p>' . esc_html__( 'Customize the main colors of the chat interface.', 'echo5-ai-chatbot' ) . '</p>';
}

/**
 * Renders the input field for the Primary Color option.
 *
 * Uses the WordPress color picker.
 *
 * @since 0.1.0
 */
function echo5_chatbot_primary_color_callback() {
	$options = get_option(
		'echo5_chatbot_appearance_options',
		array( 'primary_color' => '#0073aa' )
	);
	$color   = isset( $options['primary_color'] ) ? $options['primary_color'] : '#0073aa';
	echo '<input type="text" name="echo5_chatbot_appearance_options[primary_color]" value="' . esc_attr( $color ) . '" class="echo5-color-picker" />';
	echo '<p class="description">' . esc_html__( 'Used for chat header, user messages, and buttons.', 'echo5-ai-chatbot' ) . '</p>';
}

/**
 * Renders the input field for the Secondary Color option.
 *
 * Uses the WordPress color picker.
 *
 * @since 0.1.0
 */
function echo5_chatbot_secondary_color_callback() {
	$options = get_option(
		'echo5_chatbot_appearance_options',
		array( 'secondary_color' => '#e5e5e5' )
	);
	$color   = isset( $options['secondary_color'] ) ? $options['secondary_color'] : '#e5e5e5';
	echo '<input type="text" name="echo5_chatbot_appearance_options[secondary_color]" value="' . esc_attr( $color ) . '" class="echo5-color-picker" />';
	echo '<p class="description">' . esc_html__( 'Used for bot message backgrounds.', 'echo5-ai-chatbot' ) . '</p>';
}


/**
 * Renders the description for the Welcome Message section.
 *
 * @since 0.1.0
 */
function echo5_chatbot_welcome_message_section_callback() {
	echo '<p>' . wp_kses_post( __( 'Customize the initial message shown to the user. Use <code>%userName%</code> as a placeholder for the user\'s name.', 'echo5-ai-chatbot' ) ) . '</p>';
}

/**
 * Renders the textarea field for the Custom Welcome Message option.
 *
 * @since 0.1.0
 */
function echo5_chatbot_welcome_message_callback() {
	$default_message = __( 'Hello, <strong>%userName%</strong>! How can I help you? (You can change your name using /name [new_name])', 'echo5-ai-chatbot' );
	$options         = get_option(
		'echo5_chatbot_appearance_options',
		array( 'welcome_message' => $default_message )
	);
	$message         = isset( $options['welcome_message'] ) ? $options['welcome_message'] : $default_message;
	echo '<textarea name="echo5_chatbot_appearance_options[welcome_message]" rows="5" cols="50" class="large-text">' . esc_textarea( $message ) . '</textarea>';
	echo '<p class="description">' . wp_kses_post( __( 'This message is shown when the chat starts. <code>%userName%</code> will be replaced by the user\'s actual name.', 'echo5-ai-chatbot' ) ) . '</p>';
}

/**
 * Renders the input field for the Chatbot Header Text option.
 *
 * @since 0.1.2
 */
function echo5_chatbot_header_text_field_callback() {
	$options = get_option( 'echo5_chatbot_appearance_options' );
	$header_text = isset( $options['chatbot_header_text'] ) ? $options['chatbot_header_text'] : __( 'AI Chatbot', 'echo5-ai-chatbot' );
	echo '<input type="text" name="echo5_chatbot_appearance_options[chatbot_header_text]" value="' . esc_attr( $header_text ) . '" class="regular-text">';
	echo '<p class="description">' . esc_html__( 'Customize the title shown in the chatbot header.', 'echo5-ai-chatbot' ) . '</p>';
}

/**
 * Enqueues the WordPress color picker scripts and styles.
 *
 * Only loads on the plugin's Appearance settings page.
 *
 * @since 0.1.0
 * @param string $hook_suffix The current admin page hook.
 */
function echo5_chatbot_enqueue_color_picker( $hook_suffix ) {
	// Hook for the Appearance submenu: 'echo5_chatbot_main_settings_page_echo5_chatbot_appearance_settings'.
	// Only load on the Appearance settings page.
	if ( 'echo5_chatbot_main_settings_page_echo5_chatbot_appearance_settings' !== $hook_suffix ) {
		return;
	}
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script(
		'echo5-admin-script',
		esc_url( ECHO5_CHATBOT_PLUGIN_URL . 'admin/js/admin-scripts.js' ), // Escaped URL.
		array( 'wp-color-picker', 'jquery' ),
		ECHO5_CHATBOT_VERSION,
		true // Load in footer.
	);
}
add_action( 'admin_enqueue_scripts', 'echo5_chatbot_enqueue_color_picker' );
