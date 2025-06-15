<?php
defined('WPINC') || die;

class Echo5_Telegram_Integration {
    private $bot_token;
    private $chat_id;

    public function __construct() {
        $this->bot_token = get_option('echo5_chatbot_telegram_bot_token', '');
        $this->chat_id = get_option('echo5_chatbot_telegram_chat_id', '');
    }

    public function send_to_telegram($user_name, $message, $chat_session_id) {
        if (empty($this->bot_token) || empty($this->chat_id)) {
            return new WP_Error('telegram_config', 'Telegram bot not configured');
        }

        $text = "🔔 New message from: {$user_name}\n" .
                "Session ID: {$chat_session_id}\n\n" .
                "Message: {$message}";

        $response = wp_remote_post("https://api.telegram.org/bot{$this->bot_token}/sendMessage", [
            'body' => [
                'chat_id' => $this->chat_id,
                'text' => $text,
                'parse_mode' => 'HTML'
            ]
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!isset($body['ok']) || !$body['ok']) {
            return new WP_Error('telegram_error', $body['description'] ?? 'Unknown error');
        }

        // Store the message ID for future reference
        $this->store_telegram_message_id($chat_session_id, $body['result']['message_id']);

        return true;
    }

    private function store_telegram_message_id($chat_session_id, $message_id) {
        $messages = get_option('echo5_telegram_messages', []);
        $messages[$chat_session_id] = [
            'message_id' => $message_id,
            'timestamp' => time()
        ];
        update_option('echo5_telegram_messages', $messages);
    }

    public function check_telegram_updates() {
        if (empty($this->bot_token)) {
            return false;
        }

        $offset = get_option('echo5_telegram_update_offset', 0);
        $response = wp_remote_get("https://api.telegram.org/bot{$this->bot_token}/getUpdates?offset={$offset}");

        if (is_wp_error($response)) {
            return false;
        }

        $updates = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!isset($updates['ok']) || !$updates['ok']) {
            return false;
        }

        foreach ($updates['result'] as $update) {
            if (isset($update['message']['text'])) {
                $this->process_telegram_response($update['message']);
            }
            // Update offset
            update_option('echo5_telegram_update_offset', $update['update_id'] + 1);
        }

        return true;
    }

    private function process_telegram_response($message) {
        // Store the response in a transient
        $response_key = 'echo5_telegram_response_' . time();
        set_transient($response_key, [
            'message' => $message['text'],
            'timestamp' => time()
        ], 3600); // Store for 1 hour
    }

    public function get_pending_responses() {
        global $wpdb;
        $responses = [];
        
        $pattern = $wpdb->esc_like('_transient_echo5_telegram_response_') . '%';
        $transients = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE %s",
                $pattern
            )
        );

        foreach ($transients as $transient) {
            $name = str_replace('_transient_', '', $transient->option_name);
            $value = maybe_unserialize($transient->option_value);
            if ($value) {
                $responses[] = $value;
                delete_transient($name);
            }
        }

        return $responses;
    }
}
