/**
 * AI Chat Widget
 * 可嵌入式AI聊天组件
 */

(function() {
    'use strict';

    class AIChatWidget {
        constructor(options = {}) {
            this.options = {
                apiBase: options.apiBase || '/api/v1/user',
                authToken: options.authToken || localStorage.getItem('auth_token') || '',
                position: options.position || 'bottom-right', // bottom-right, bottom-left
                greeting: options.greeting || '你好！我是AI助手，有什么可以帮助你的吗？',
                placeholder: options.placeholder || '输入消息...',
                streaming: options.streaming !== false,
                ...options
            };

            this.isOpen = false;
            this.sessionId = null;
            this.messages = [];
            this.init();
        }

        init() {
            this.createStyles();
            this.createWidget();
            this.attachEventListeners();
            if (this.options.greeting) {
                this.addMessage('assistant', this.options.greeting);
            }
        }

        createStyles() {
            if (document.getElementById('ai-chat-widget-styles')) return;

            const styles = `
                .ai-chat-widget {
                    position: fixed;
                    ${this.options.position.includes('right') ? 'right: 24px;' : 'left: 24px;'}
                    bottom: 24px;
                    z-index: 9998;
                }

                .ai-chat-trigger {
                    width: 56px;
                    height: 56px;
                    border-radius: 50%;
                    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                    border: none;
                    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: all 0.3s ease;
                    position: relative;
                }

                .ai-chat-trigger:hover {
                    transform: scale(1.1);
                    box-shadow: 0 6px 16px rgba(59, 130, 246, 0.5);
                }

                .ai-chat-trigger svg {
                    width: 24px;
                    height: 24px;
                    color: white;
                }

                .ai-chat-badge {
                    position: absolute;
                    top: -4px;
                    right: -4px;
                    width: 20px;
                    height: 20px;
                    background: #ef4444;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 11px;
                    font-weight: 600;
                    color: white;
                }

                .ai-chat-window {
                    position: fixed;
                    ${this.options.position.includes('right') ? 'right: 24px;' : 'left: 24px;'}
                    bottom: 96px;
                    width: 380px;
                    height: 600px;
                    max-width: calc(100vw - 48px);
                    max-height: calc(100vh - 120px);
                    background: white;
                    border-radius: 16px;
                    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                    display: none;
                    flex-direction: column;
                    overflow: hidden;
                    animation: slideUp 0.3s ease;
                }

                .ai-chat-window.open {
                    display: flex;
                }

                @keyframes slideUp {
                    from {
                        opacity: 0;
                        transform: translateY(20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                .ai-chat-header {
                    padding: 16px;
                    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                    color: white;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }

                .ai-chat-header-info {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                }

                .ai-chat-avatar {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.2);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 20px;
                }

                .ai-chat-header-text h3 {
                    font-size: 16px;
                    font-weight: 600;
                    margin: 0 0 2px 0;
                }

                .ai-chat-header-text p {
                    font-size: 12px;
                    margin: 0;
                    opacity: 0.9;
                }

                .ai-chat-actions {
                    display: flex;
                    gap: 8px;
                }

                .ai-chat-action-btn {
                    background: rgba(255, 255, 255, 0.2);
                    border: none;
                    width: 32px;
                    height: 32px;
                    border-radius: 8px;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: background 0.2s;
                }

                .ai-chat-action-btn:hover {
                    background: rgba(255, 255, 255, 0.3);
                }

                .ai-chat-action-btn svg {
                    width: 18px;
                    height: 18px;
                    color: white;
                }

                .ai-chat-messages {
                    flex: 1;
                    overflow-y: auto;
                    padding: 16px;
                    background: #f9fafb;
                }

                .ai-chat-message {
                    margin-bottom: 16px;
                    display: flex;
                    gap: 8px;
                    animation: fadeIn 0.3s ease;
                }

                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(10px); }
                    to { opacity: 1; transform: translateY(0); }
                }

                .ai-chat-message.user {
                    flex-direction: row-reverse;
                }

                .ai-chat-message-avatar {
                    width: 32px;
                    height: 32px;
                    border-radius: 50%;
                    background: #3b82f6;
                    color: white;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 16px;
                    flex-shrink: 0;
                }

                .ai-chat-message.user .ai-chat-message-avatar {
                    background: #10b981;
                }

                .ai-chat-message-content {
                    max-width: 75%;
                    padding: 10px 14px;
                    border-radius: 12px;
                    background: white;
                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
                    font-size: 14px;
                    line-height: 1.5;
                }

                .ai-chat-message.user .ai-chat-message-content {
                    background: #3b82f6;
                    color: white;
                }

                .ai-chat-message-sources {
                    margin-top: 8px;
                    padding-top: 8px;
                    border-top: 1px solid #e5e7eb;
                    font-size: 12px;
                }

                .ai-chat-source-link {
                    display: inline-block;
                    margin-right: 8px;
                    color: #3b82f6;
                    text-decoration: none;
                    transition: color 0.2s;
                }

                .ai-chat-source-link:hover {
                    color: #2563eb;
                    text-decoration: underline;
                }

                .ai-chat-typing {
                    display: flex;
                    gap: 4px;
                    padding: 10px 14px;
                }

                .ai-chat-typing-dot {
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                    background: #9ca3af;
                    animation: typing 1.4s infinite;
                }

                .ai-chat-typing-dot:nth-child(2) {
                    animation-delay: 0.2s;
                }

                .ai-chat-typing-dot:nth-child(3) {
                    animation-delay: 0.4s;
                }

                @keyframes typing {
                    0%, 60%, 100% { transform: translateY(0); }
                    30% { transform: translateY(-10px); }
                }

                .ai-chat-input-container {
                    padding: 16px;
                    border-top: 1px solid #e5e7eb;
                    background: white;
                }

                .ai-chat-input-wrapper {
                    display: flex;
                    gap: 8px;
                    align-items: flex-end;
                }

                .ai-chat-input {
                    flex: 1;
                    padding: 10px 12px;
                    border: 2px solid #e5e7eb;
                    border-radius: 8px;
                    font-size: 14px;
                    resize: none;
                    font-family: inherit;
                    max-height: 100px;
                    transition: border-color 0.2s;
                }

                .ai-chat-input:focus {
                    outline: none;
                    border-color: #3b82f6;
                }

                .ai-chat-send-btn {
                    width: 40px;
                    height: 40px;
                    background: #3b82f6;
                    color: white;
                    border: none;
                    border-radius: 8px;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: background 0.2s;
                    flex-shrink: 0;
                }

                .ai-chat-send-btn:hover {
                    background: #2563eb;
                }

                .ai-chat-send-btn:disabled {
                    opacity: 0.5;
                    cursor: not-allowed;
                }

                .ai-chat-send-btn svg {
                    width: 20px;
                    height: 20px;
                }

                @media (max-width: 640px) {
                    .ai-chat-widget {
                        bottom: 16px;
                        ${this.options.position.includes('right') ? 'right: 16px;' : 'left: 16px;'}
                    }

                    .ai-chat-trigger {
                        width: 48px;
                        height: 48px;
                    }

                    .ai-chat-window {
                        bottom: 80px;
                        ${this.options.position.includes('right') ? 'right: 16px;' : 'left: 16px;'}
                        width: calc(100vw - 32px);
                        height: calc(100vh - 100px);
                    }

                    .ai-chat-message-content {
                        max-width: 85%;
                    }
                }

                /* Scrollbar */
                .ai-chat-messages::-webkit-scrollbar {
                    width: 6px;
                }

                .ai-chat-messages::-webkit-scrollbar-track {
                    background: transparent;
                }

                .ai-chat-messages::-webkit-scrollbar-thumb {
                    background: #d1d5db;
                    border-radius: 3px;
                }

                .ai-chat-messages::-webkit-scrollbar-thumb:hover {
                    background: #9ca3af;
                }
            `;

            const styleSheet = document.createElement('style');
            styleSheet.id = 'ai-chat-widget-styles';
            styleSheet.textContent = styles;
            document.head.appendChild(styleSheet);
        }

        createWidget() {
            const widget = document.createElement('div');
            widget.className = 'ai-chat-widget';
            widget.innerHTML = `
                <button class="ai-chat-trigger" id="aiChatTrigger">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                </button>
                <div class="ai-chat-window" id="aiChatWindow">
                    <div class="ai-chat-header">
                        <div class="ai-chat-header-info">
                            <div class="ai-chat-avatar">🤖</div>
                            <div class="ai-chat-header-text">
                                <h3>AI 助手</h3>
                                <p>在线</p>
                            </div>
                        </div>
                        <div class="ai-chat-actions">
                            <button class="ai-chat-action-btn" id="aiChatMinimize" title="最小化">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="ai-chat-messages" id="aiChatMessages"></div>
                    <div class="ai-chat-input-container">
                        <div class="ai-chat-input-wrapper">
                            <textarea 
                                class="ai-chat-input" 
                                id="aiChatInput" 
                                placeholder="${this.options.placeholder}"
                                rows="1"
                            ></textarea>
                            <button class="ai-chat-send-btn" id="aiChatSendBtn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(widget);
            this.widget = widget;
        }

        attachEventListeners() {
            const trigger = document.getElementById('aiChatTrigger');
            const minimize = document.getElementById('aiChatMinimize');
            const input = document.getElementById('aiChatInput');
            const sendBtn = document.getElementById('aiChatSendBtn');

            trigger.addEventListener('click', () => this.toggle());
            minimize.addEventListener('click', () => this.close());
            sendBtn.addEventListener('click', () => this.sendMessage());
            
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });

            input.addEventListener('input', () => {
                input.style.height = 'auto';
                input.style.height = Math.min(input.scrollHeight, 100) + 'px';
            });
        }

        toggle() {
            this.isOpen ? this.close() : this.open();
        }

        open() {
            const window = document.getElementById('aiChatWindow');
            window.classList.add('open');
            this.isOpen = true;
            document.getElementById('aiChatInput').focus();
        }

        close() {
            const window = document.getElementById('aiChatWindow');
            window.classList.remove('open');
            this.isOpen = false;
        }

        async sendMessage() {
            const input = document.getElementById('aiChatInput');
            const message = input.value.trim();
            if (!message) return;

            const sendBtn = document.getElementById('aiChatSendBtn');
            sendBtn.disabled = true;

            this.addMessage('user', message);
            input.value = '';
            input.style.height = 'auto';

            if (!this.sessionId) {
                await this.createSession();
            }

            this.showTyping();

            try {
                if (this.options.streaming) {
                    await this.sendStreamingMessage(message);
                } else {
                    await this.sendNormalMessage(message);
                }
            } catch (error) {
                console.error('Chat error:', error);
                this.hideTyping();
                this.addMessage('assistant', '抱歉，发送失败。请稍后再试。');
            } finally {
                sendBtn.disabled = false;
            }
        }

        async createSession() {
            try {
                const response = await fetch(`${this.options.apiBase}/ai/chat/session`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${this.options.authToken}`
                    },
                    body: JSON.stringify({})
                });

                const data = await response.json();
                if (data.data && data.data.session_id) {
                    this.sessionId = data.data.session_id;
                }
            } catch (error) {
                console.error('Session creation error:', error);
            }
        }

        async sendNormalMessage(message) {
            const response = await fetch(`${this.options.apiBase}/ai/chat`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.options.authToken}`
                },
                body: JSON.stringify({
                    message: message,
                    session_id: this.sessionId
                })
            });

            const data = await response.json();
            this.hideTyping();

            if (data.data) {
                this.sessionId = data.data.session_id;
                this.addMessage('assistant', data.data.response, data.data.sources);
            } else {
                this.addMessage('assistant', '抱歉，我现在无法回答。');
            }
        }

        async sendStreamingMessage(message) {
            // Streaming implementation would go here
            // For now, fall back to normal message
            await this.sendNormalMessage(message);
        }

        addMessage(role, content, sources = null) {
            const messagesContainer = document.getElementById('aiChatMessages');
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `ai-chat-message ${role}`;
            
            const avatar = role === 'user' ? '👤' : '🤖';
            
            let sourcesHtml = '';
            if (sources && sources.length > 0) {
                sourcesHtml = `
                    <div class="ai-chat-message-sources">
                        <strong>参考：</strong>
                        ${sources.map(s => `<a href="/knowledge/${s.id}" class="ai-chat-source-link" target="_blank">${this.escapeHtml(s.title)}</a>`).join('')}
                    </div>
                `;
            }

            messageDiv.innerHTML = `
                <div class="ai-chat-message-avatar">${avatar}</div>
                <div class="ai-chat-message-content">
                    ${this.escapeHtml(content).replace(/\n/g, '<br>')}
                    ${sourcesHtml}
                </div>
            `;

            messagesContainer.appendChild(messageDiv);
            this.scrollToBottom();
            
            this.messages.push({ role, content, sources });
        }

        showTyping() {
            const messagesContainer = document.getElementById('aiChatMessages');
            const typingDiv = document.createElement('div');
            typingDiv.className = 'ai-chat-message assistant';
            typingDiv.id = 'aiChatTyping';
            typingDiv.innerHTML = `
                <div class="ai-chat-message-avatar">🤖</div>
                <div class="ai-chat-message-content">
                    <div class="ai-chat-typing">
                        <div class="ai-chat-typing-dot"></div>
                        <div class="ai-chat-typing-dot"></div>
                        <div class="ai-chat-typing-dot"></div>
                    </div>
                </div>
            `;
            messagesContainer.appendChild(typingDiv);
            this.scrollToBottom();
        }

        hideTyping() {
            const typing = document.getElementById('aiChatTyping');
            if (typing) typing.remove();
        }

        scrollToBottom() {
            const messagesContainer = document.getElementById('aiChatMessages');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        destroy() {
            if (this.widget) {
                this.widget.remove();
            }
        }
    }

    // Export to global scope
    window.AIChatWidget = AIChatWidget;

    // Auto-initialize if data attribute is present
    if (document.querySelector('[data-ai-chat-widget]')) {
        document.addEventListener('DOMContentLoaded', () => {
            new AIChatWidget();
        });
    }
})();
