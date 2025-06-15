<?php
/**
 * AJAX Handlers for Echo5 AI Chatbot
 *
 * @package Echo5_AI_Chatbot
 * @since 0.1.0
 */

if (!defined('WPINC')) {
    die('Direct access not allowed.');
}

function echo5_chatbot_handle_message() {
    check_ajax_referer('echo5_chatbot_send_message_nonce', 'nonce');

    $message = sanitize_textarea_field($_POST['message']);
    $user_name = sanitize_text_field($_POST['user_name']);
    $is_live_agent = isset($_POST['is_live_agent']) ? (bool)$_POST['is_live_agent'] : false;
    $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';

    if ($is_live_agent) {
        // Get Telegram settings from experimental options
        $experimental_options = get_option('echo5_chatbot_experimental_options', array());
        $bot_token = isset($experimental_options['telegram_bot_token']) ? $experimental_options['telegram_bot_token'] : '';
        $chat_id = isset($experimental_options['telegram_chat_id']) ? $experimental_options['telegram_chat_id'] : '';

        if (empty($bot_token) || empty($chat_id)) {
            error_log('Telegram Error: Missing bot token or chat ID');
            wp_send_json_error(['message' => 'Telegram settings not configured']);
            return;
        }

        $telegram_message = sprintf(
            "\xF0\x9F\x97\xA8\xEF\xB8\x8F *New Message*\n\xF0\x9F\x91\xA4 From: %s\n\xF0\x9F\x92\xAC Message: %s\n\xF0\x9F\x94\x91 Session: %s",
            $user_name,
            $message,
            $session_id
        );

        $args = array(
            'body' => array(
                'chat_id' => $chat_id,
                'text' => $telegram_message,
                'parse_mode' => 'Markdown'
            ),
            'timeout' => 15
        );

        $response = wp_remote_post("https://api.telegram.org/bot{$bot_token}/sendMessage", $args);

        error_log('Telegram Response: ' . print_r($response, true));

        if (is_wp_error($response)) {
            error_log('Telegram Error: ' . $response->get_error_message());
            wp_send_json_error(['message' => 'Failed to send message: ' . $response->get_error_message()]);
            return;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($response_code !== 200 || !isset($body['ok']) || !$body['ok']) {
            $error = isset($body['description']) ? $body['description'] : 'Unknown error';
            error_log('Telegram Error: ' . $error);
            wp_send_json_error(['message' => 'Failed to send message: ' . $error]);
            return;
        }

        wp_send_json_success(['reply' => 'Message sent to live agent. Please wait for a response.']);
        return;
    }

    // OpenAI integration remains unchanged...
}
add_action('wp_ajax_echo5_chatbot_send_message', 'echo5_chatbot_handle_message');
add_action('wp_ajax_nopriv_echo5_chatbot_send_message', 'echo5_chatbot_handle_message');

// Webhook endpoint for Telegram responses
function echo5_chatbot_telegram_webhook() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        status_header(405);
        exit;
    }

    $input = file_get_contents('php://input');
    $update = json_decode($input, true);

    if (!isset($update['message']['chat']['id']) || !isset($update['message']['text'])) {
        status_header(400);
        echo 'Invalid data';
        exit;
    }

    $chat_id = $update['message']['chat']['id'];
    $text = $update['message']['text'];

    // Store response as a transient for 5 minutes
    set_transient("echo5_live_agent_response_{$chat_id}", $text, 300);

    status_header(200);
    echo 'OK';
    exit;
}

add_action('init', function() {
    add_rewrite_rule('^echo5-telegram-webhook/?$', 'index.php?echo5_telegram_webhook=1', 'top');
});

add_filter('query_vars', function($vars) {
    $vars[] = 'echo5_telegram_webhook';
    return $vars;
});

add_action('parse_request', function($wp) {
    if (isset($wp->query_vars['echo5_telegram_webhook'])) {
        echo5_chatbot_telegram_webhook();
        exit;
    }
});
