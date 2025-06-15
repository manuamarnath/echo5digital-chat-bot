<?php
/**
 * Experimental Features Settings for Echo5 AI Chatbot.
 *
 * @package Echo5_AI_Chatbot
 * @since   0.1.0
 */

if (!defined('WPINC')) {
    die;
}

function echo5_chatbot_register_experimental_settings() {
    // Register main experimental options
    register_setting(
        'echo5_chatbot_experimental_options',
        'echo5_chatbot_experimental_options',
        'echo5_chatbot_sanitize_experimental_options'
    );

    // Add experimental section
    add_settings_section(
        'echo5_chatbot_experimental_section',
        __('Experimental Features', 'echo5-ai-chatbot'),
        'echo5_chatbot_experimental_section_callback',
        'echo5_chatbot_experimental'
    );

    // Add fields
    add_settings_field(
        'live_agent_toggle',
        __('Live Agent Toggle', 'echo5-ai-chatbot'),
        'echo5_chatbot_live_agent_toggle_callback',
        'echo5_chatbot_experimental',
        'echo5_chatbot_experimental_section'
    );

    add_settings_field(
        'telegram_settings',
        __('Telegram Settings', 'echo5-ai-chatbot'),
        'echo5_chatbot_telegram_settings_callback',
        'echo5_chatbot_experimental',
        'echo5_chatbot_experimental_section'
    );

    // Add webhook field
    add_settings_field(
        'telegram_webhook_url',
        __('Telegram Webhook URL', 'echo5-ai-chatbot'),
        'echo5_webhook_field_callback',
        'echo5_chatbot_experimental',
        'echo5_chatbot_experimental_section'
    );
}

add_action('admin_init', 'echo5_chatbot_register_experimental_settings');

function echo5_chatbot_experimental_section_callback() {
    echo '<p>' . esc_html__('Enable or disable experimental features. Use with caution.', 'echo5-ai-chatbot') . '</p>';
}

function echo5_chatbot_live_agent_toggle_callback() {
    $options = get_option('echo5_chatbot_experimental_options', array(
        'live_agent_toggle' => false
    ));
    ?>
    <label>
        <input type="checkbox" name="echo5_chatbot_experimental_options[live_agent_toggle]" 
               value="1" <?php checked(1, $options['live_agent_toggle']); ?>>
        <?php esc_html_e('Enable live agent toggle button in chat header', 'echo5-ai-chatbot'); ?>
    </label>
    <p class="description">
        <?php esc_html_e('Adds a button to switch between AI and live agent support.', 'echo5-ai-chatbot'); ?>
    </p>
    <?php
}

function echo5_chatbot_telegram_settings_callback() {
    $options = get_option('echo5_chatbot_experimental_options', array());
    ?>
    <div style="margin-bottom: 20px;">
        <p>
            <label style="display: block; margin-bottom: 5px;">
                <strong><?php esc_html_e('Telegram Bot Token', 'echo5-ai-chatbot'); ?></strong><br>
                <input type="text" 
                    name="echo5_chatbot_experimental_options[telegram_bot_token]" 
                    value="<?php echo esc_attr(isset($options['telegram_bot_token']) ? $options['telegram_bot_token'] : ''); ?>" 
                    class="regular-text"
                    placeholder="Enter your bot token from @BotFather">
            </label>
        </p>
        <p>
            <label style="display: block; margin-bottom: 5px;">
                <strong><?php esc_html_e('Telegram Chat ID', 'echo5-ai-chatbot'); ?></strong><br>
                <input type="text" 
                    name="echo5_chatbot_experimental_options[telegram_chat_id]" 
                    value="<?php echo esc_attr(isset($options['telegram_chat_id']) ? $options['telegram_chat_id'] : ''); ?>" 
                    class="regular-text"
                    placeholder="Enter your chat ID">
            </label>
        </p>
        <p class="description">
            <?php esc_html_e('These settings are required for the live agent feature to work with Telegram.', 'echo5-ai-chatbot'); ?>
        </p>
    </div>
    <?php
}

function echo5_webhook_field_callback() {
    $webhook_url = site_url('echo5-telegram-webhook');
    ?>
    <div class="webhook-container">
        <input type="text" 
               id="echo5-webhook-url" 
               value="<?php echo esc_url($webhook_url); ?>" 
               readonly 
               class="regular-text">
        <button type="button" 
                class="button button-secondary" 
                onclick="copyWebhookUrl()">
            <?php esc_html_e('Copy URL', 'echo5-ai-chatbot'); ?>
        </button>
    </div>
    <p class="description">
        <?php esc_html_e('Use this webhook URL in your Telegram bot settings.', 'echo5-ai-chatbot'); ?>
    </p>
    <script>
    function copyWebhookUrl() {
        var copyText = document.getElementById("echo5-webhook-url");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        
        var button = document.querySelector('.webhook-container button');
        var originalText = button.innerHTML;
        button.innerHTML = '<?php esc_html_e('Copied!', 'echo5-ai-chatbot'); ?>';
        setTimeout(function() {
            button.innerHTML = originalText;
        }, 2000);
    }
    </script>
    <style>
    .webhook-container {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-bottom: 5px;
    }
    #echo5-webhook-url {
        background: #f0f0f1;
    }
    </style>
    <?php
}

function echo5_chatbot_sanitize_experimental_options($input) {
    $sanitized = array();
    
    // Sanitize live agent toggle
    $sanitized['live_agent_toggle'] = isset($input['live_agent_toggle']) ? (bool) $input['live_agent_toggle'] : false;
    
    // Sanitize Telegram settings
    $sanitized['telegram_bot_token'] = isset($input['telegram_bot_token']) ? sanitize_text_field($input['telegram_bot_token']) : '';
    $sanitized['telegram_chat_id'] = isset($input['telegram_chat_id']) ? sanitize_text_field($input['telegram_chat_id']) : '';
    
    return $sanitized;
}

// Clean up any existing options and transients
add_action('admin_init', function() {
    delete_option('echo5_chatbot_telegram_bot_token');
    delete_option('echo5_chatbot_telegram_chat_id');
    global $wpdb;
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_echo5_telegram_%'");
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_echo5_telegram_%'");
});
