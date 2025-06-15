/**
 * Echo5 AI Chatbot Front-End JavaScript
 *
 * Handles chat interface interactions, user messages, bot responses (simulated),
 * name changes, and sending chat transcripts.
 *
 * @since 0.1.0
 */
document.addEventListener('DOMContentLoaded', function() {
    // DOM element references with error checking
    const elements = {
        chatContainer: document.getElementById('echo5-chat-container'),
        namePrompt: document.getElementById('echo5-chat-name-prompt'),
        userNameInput: document.getElementById('echo5-user-name-input'),
        submitNameButton: document.getElementById('echo5-submit-name-button'),
        messageInput: document.getElementById('echo5-chat-message-input'),
        sendMessageButton: document.getElementById('echo5-send-message-button'),
        chatMessages: document.getElementById('echo5-chat-messages'),
        chatHeader: document.getElementById('echo5-chat-header')
    };

    // Initialize variables
    let userName = localStorage.getItem('echo5_user_name') || '';
    let isMinimized = true; // Start minimized
    let checkResponseInterval = null;
    let chatSessionId = 'session_' + Date.now();
    let autoMaximizeTimeout = null;

    // Handle clicks on the chat container when minimized
    elements.chatContainer.addEventListener('click', function(e) {
        if (isMinimized) {
            maximizeChat();
            e.stopPropagation();
        }
    });

    // Update event handler for minimize button
    elements.minimizeButton = document.getElementById('echo5-minimize-button');
    if (elements.minimizeButton) {
        elements.minimizeButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            minimizeChat();
        });
    }

    // Modify existing header click handler
    elements.chatHeader.addEventListener('click', function(e) {
        if (!isMinimized || e.target === elements.minimizeButton) {
            return;
        }
        maximizeChat();
        e.stopPropagation();
    });

    // Add helper functions for minimize/maximize
    function minimizeChat() {
        isMinimized = true;
        elements.chatContainer.classList.add('minimized');
        clearTimeout(autoMaximizeTimeout); // Clear any pending auto-maximize
    }

    function maximizeChat() {
        isMinimized = false;
        elements.chatContainer.classList.remove('minimized');
        if (elements.messageInput) {
            setTimeout(() => elements.messageInput.focus(), 300);
        }
        clearTimeout(autoMaximizeTimeout); // Clear any pending auto-maximize
    }

    // Verify required elements
    const missingElements = Object.entries(elements)
        .filter(([key, element]) => !element)
        .map(([key]) => key);

    if (missingElements.length > 0) {
        console.error('Missing elements:', missingElements);
        return;
    }

    // State management
    let isLiveAgent = false;
    let welcomeBackTemplate = echo5_chatbot_data.default_welcome_known_user || '';

    let checkResponsesInterval = null;

    function startCheckingResponses() {
        // Clear any existing interval
        if (checkResponsesInterval) {
            clearInterval(checkResponsesInterval);
        }

        checkResponsesInterval = setInterval(() => {
            jQuery.ajax({
                url: echo5_chatbot_data.ajax_url,
                type: 'POST',
                data: {
                    action: 'echo5_check_live_agent_responses',
                    nonce: echo5_chatbot_data.send_message_nonce,
                    session_id: chatSessionId
                },
                success: function(response) {
                    if (response.success && response.data.messages && response.data.messages.length > 0) {
                        response.data.messages.forEach(function(msg) {
                            displayBotMessage(msg.message);
                        });
                    }
                }
            });
        }, 5000);
    }

    // Live agent toggle functionality
    const liveAgentToggle = document.getElementById('echo5-live-agent-toggle');
    if (liveAgentToggle) {
        liveAgentToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            isLiveAgent = this.classList.contains('active');
            
            this.innerHTML = `
                <span class="echo5-live-agent-indicator"></span>
                ${isLiveAgent ? 'Switch to AI' : 'Switch to Live Agent'}
            `;
            
            if (isLiveAgent) {
                displayBotMessage('Connecting to live agent...');
                startCheckingResponses();
            } else {
                displayBotMessage('Switching back to AI assistant mode.');
                if (checkResponsesInterval) {
                    clearInterval(checkResponsesInterval);
                }
            }
        });
    }

    // Initialize chat state
    function initializeChat() {
        console.log('initializeChat: Started with userName:', userName);
        
        if (!elements.messageInput || !elements.sendMessageButton) {
            console.error('Echo5 Chatbot: Chat input elements missing!');
            return;
        }

        // Set initial states
        if (!userName) {
            console.log('initializeChat: No userName found, showing name prompt');
            elements.namePrompt.style.display = 'block';
            elements.messageInput.disabled = true;
            elements.sendMessageButton.disabled = true;
        } else {
            console.log('initializeChat: userName found, enabling chat');
            elements.namePrompt.style.display = 'none';
            elements.messageInput.disabled = false;
            elements.sendMessageButton.disabled = false;
            displayBotMessage(getPersonalizedMessage(welcomeBackTemplate, userName));
        }

        // Always start minimized
        minimizeChat();

        // Set timeout to automatically maximize after 4 seconds
        autoMaximizeTimeout = setTimeout(() => {
            maximizeChat();
        }, 4000);
    }

    // Enable/disable chat functions
    function enableChat() {
        elements.messageInput.disabled = false;
        elements.sendMessageButton.disabled = false;
        elements.messageInput.focus();
    }

    function disableChat() {
        elements.messageInput.disabled = true;
        elements.sendMessageButton.disabled = true;
    }

    // Event handlers
    elements.submitNameButton.addEventListener('click', function() {
        const name = elements.userNameInput.value.trim();
        if (name) {
            userName = name;
            localStorage.setItem('echo5_user_name', userName);
            elements.namePrompt.style.display = 'none';
            enableChat();
            displayBotMessage(getPersonalizedMessage(echo5_chatbot_data.welcome_message_template, userName));
        }
    });

    elements.sendMessageButton.addEventListener('click', sendMessage);
    elements.messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Helper function to get personalized message
    function getPersonalizedMessage(template, name) {
        return template.replace(/%userName%/g, name);
    }

    // Message display functions
    function displayUserMessage(message, name) {
        const messageDiv = createMessageElement('user', name, message);
        elements.chatMessages.appendChild(messageDiv);
        elements.chatMessages.scrollTop = elements.chatMessages.scrollHeight;
    }    function displayBotMessage(message) {
        const messageDiv = createMessageElement('bot', 'Live Support', message);
        elements.chatMessages.appendChild(messageDiv);
        elements.chatMessages.scrollTop = elements.chatMessages.scrollHeight;
    }

    function createMessageElement(type, name, message) {
        const div = document.createElement('div');
        div.className = `echo5-message echo5-${type}-message`;
        div.innerHTML = `
            <div class="echo5-message-content">
                <strong>${name}</strong>
                <p>${message}</p>
            </div>`;
        return div;
    }

    // Add this function after the helper functions
    function calculateTypingDelay(message) {
        // Average reading speed (characters per millisecond)
        const charsPerMs = 0.04;
        // Base delay of 500ms + calculated time based on message length
        const delay = 500 + (message.length / charsPerMs);
        // Cap the maximum delay at 3 seconds
        return Math.min(delay, 3000);
    }

    // Modify the sendMessage function
    async function sendMessage() {
        const message = elements.messageInput.value.trim();
        if (!message) return;

        displayUserMessage(message, userName);
        elements.messageInput.value = '';
        disableChat();

        const template = document.getElementById('echo5-typing-indicator-template');
        const typingIndicator = template.content.cloneNode(true).querySelector('.echo5-typing-indicator');
        elements.chatMessages.appendChild(typingIndicator);
        typingIndicator.style.display = 'block';
        elements.chatMessages.scrollTop = elements.chatMessages.scrollHeight;

        try {
            const response = await jQuery.ajax({
                url: echo5_chatbot_data.ajax_url,
                type: 'POST',
                data: {
                    action: 'echo5_chatbot_send_message',
                    nonce: echo5_chatbot_data.send_message_nonce,
                    message: message,
                    user_name: userName,
                    is_live_agent: isLiveAgent,
                    session_id: chatSessionId
                }
            });

            if (response.success) {
                if (!isLiveAgent) {
                    // Calculate and apply typing delay
                    const delay = calculateTypingDelay(response.data.reply);
                    await new Promise(resolve => setTimeout(resolve, delay));
                }
            }
            
            // Remove typing indicator
            typingIndicator.remove();

            if (response.success) {
                if (!isLiveAgent) {
                    displayBotMessage(response.data.reply);
                }
            } else {
                displayBotMessage('Error: ' + (response.data?.message || 'Something went wrong'));
            }
        } catch (error) {
            typingIndicator.remove();
            console.error('AJAX error:', error);
            displayBotMessage('Error: Could not connect to the server.');
        } finally {
            enableChat();
        }
    }

    // Initialize
    initializeChat();
});
