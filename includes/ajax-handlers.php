<?php
/**
 * AJAX Handlers for Echo5 AI Chatbot
 *
 * @package Echo5_AI_Chatbot
 * @since 0.1.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die('Direct access not allowed.');
}

/**
 * Handle chat messages and OpenAI integration
 */
function echo5_chatbot_handle_message() {
    check_ajax_referer('echo5_chatbot_send_message_nonce', 'nonce');

    $message = sanitize_textarea_field($_POST['message']);
    $user_name = sanitize_text_field($_POST['user_name']);
    $is_live_agent = isset($_POST['is_live_agent']) ? (bool)$_POST['is_live_agent'] : false;

    if ($is_live_agent) {
        $response = "Live support is connecting. Your position in queue: 1\nA representative will be with you shortly.";
        
        // Log live agent request
        global $wpdb;
        $table_name = $wpdb->prefix . 'echo5_chatbot_logs';
        $wpdb->insert(
            $table_name,
            array(
                'user_name' => $user_name,
                'message' => '[LIVE_AGENT_REQUEST] ' . $message,
                'response' => $response,
                'timestamp' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s')
        );

        wp_send_json_success(array('reply' => $response));
        return;
    }

    // Get OpenAI API key
    $api_key = get_option('echo5_chatbot_api_key');
    
    if (empty($api_key)) {
        wp_send_json_error(array('message' => 'OpenAI API key not configured.'));
        return;
    }

    // Get FAQ content for context
    $faq_file = ECHO5_CHATBOT_PLUGIN_DIR . 'echo-faqs.txt';
    $faq_text = file_exists($faq_file) ? file_get_contents($faq_file) : '';

    // Prepare OpenAI API request
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'model' => 'gpt-3.5-turbo',
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => "You are Bhumi Blend's virtual Ayurvedic beauty expert. Use this FAQ for context:\n\n" . $faq_text
                ),
                array(
                    'role' => 'user',
                    'content' => $message
                )
            ),
            'max_tokens' => 150
        )),
        'timeout' => 15
    ));

    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => $response->get_error_message()));
        return;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (isset($body['error'])) {
        wp_send_json_error(array('message' => $body['error']['message']));
        return;
    }

    if (isset($body['choices'][0]['message']['content'])) {
        $ai_response = $body['choices'][0]['message']['content'];
        
        // Log the chat
        global $wpdb;
        $table_name = $wpdb->prefix . 'echo5_chatbot_logs';
        $wpdb->insert(
            $table_name,
            array(
                'user_name' => $user_name,
                'message' => $message,
                'response' => $ai_response,
                'timestamp' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s')
        );

        wp_send_json_success(array('reply' => $ai_response));
    } else {
        wp_send_json_error(array('message' => 'Invalid response from AI'));
    }
}
add_action('wp_ajax_echo5_chatbot_send_message', 'echo5_chatbot_handle_message');
add_action('wp_ajax_nopriv_echo5_chatbot_send_message', 'echo5_chatbot_handle_message');
