<?php
/**
 * Chat Interface HTML structure for Echo5 AI Chatbot.
 *
 * This file provides the basic HTML layout for the chat widget.
 * It is included by the main plugin file and populated dynamically by JavaScript.
 *
 * @package Echo5_AI_Chatbot
 * @since   0.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die( esc_html__( 'Silence is golden.', 'echo5-ai-chatbot' ) );
}
?>
<div id="echo5-chat-container" class="echo5-chat-widget minimized">    <div id="echo5-chat-header">
        <h2>Live Chat</h2>
        <button type="button" id="echo5-minimize-button" aria-label="<?php esc_attr_e('Minimize Chat', 'echo5-ai-chatbot'); ?>">
            −
        </button>
    </div>
    
    <div id="echo5-chat-messages">
        <!-- Template for typing indicator -->
        <template id="echo5-typing-indicator-template">
            <div class="echo5-typing-indicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </template>
    </div>
    
    <div id="echo5-chat-name-prompt">
        <p><?php esc_html_e('Please enter your name to start chatting', 'echo5-ai-chatbot'); ?></p>
        <input type="text" 
               id="echo5-user-name-input" 
               autocomplete="off" 
               placeholder="<?php esc_attr_e('Your Name', 'echo5-ai-chatbot'); ?>">
        <button id="echo5-submit-name-button" type="button">
            <?php esc_html_e('Start Chat', 'echo5-ai-chatbot'); ?>
        </button>
    </div>

    <div id="echo5-chat-controls">
        <div id="echo5-chat-input-area">
            <input type="text" 
                   id="echo5-chat-message-input" 
                   placeholder="<?php esc_attr_e('Type your message...', 'echo5-ai-chatbot'); ?>"
                   autocomplete="off"
                   disabled>
            <button id="echo5-send-message-button" type="button" disabled>
                <?php esc_html_e('Send', 'echo5-ai-chatbot'); ?>
            </button>
        </div>
    </div>
</div>
