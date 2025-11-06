/**
 * AI Search Widget
 * 可嵌入式AI搜索组件
 */

(function() {
    'use strict';

    class AISearchWidget {
        constructor(options = {}) {
            this.options = {
                apiBase: options.apiBase || '/api/v1/user',
                authToken: options.authToken || localStorage.getItem('auth_token') || '',
                placeholder: options.placeholder || '搜索知识库...',
                minSimilarity: options.minSimilarity || 0.7,
                limit: options.limit || 5,
                onResultClick: options.onResultClick || null,
                ...options
            };

            this.isOpen = false;
            this.results = [];
            this.init();
        }

        init() {
            this.createStyles();
            this.createWidget();
            this.attachEventListeners();
        }

        createStyles() {
            if (document.getElementById('ai-search-widget-styles')) return;

            const styles = `
                .ai-search-widget {
                    position: fixed;
                    bottom: 24px;
                    right: 24px;
                    z-index: 9999;
                }

                .ai-search-trigger {
                    width: 56px;
                    height: 56px;
                    border-radius: 50%;
                    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                    border: none;
                    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: all 0.3s ease;
                }

                .ai-search-trigger:hover {
                    transform: scale(1.1);
                    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.5);
                }

                .ai-search-trigger svg {
                    width: 24px;
                    height: 24px;
                    color: white;
                }

                .ai-search-panel {
                    position: fixed;
                    bottom: 96px;
                    right: 24px;
                    width: 400px;
                    max-width: calc(100vw - 48px);
                    max-height: 600px;
                    background: white;
                    border-radius: 16px;
                    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                    display: none;
                    flex-direction: column;
                    overflow: hidden;
                    animation: slideUp 0.3s ease;
                }

                .ai-search-panel.open {
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

                .ai-search-header {
                    padding: 16px;
                    border-bottom: 1px solid #e5e7eb;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }

                .ai-search-header h3 {
                    font-size: 16px;
                    font-weight: 600;
                    color: #111827;
                    margin: 0;
                }

                .ai-search-close {
                    background: none;
                    border: none;
                    cursor: pointer;
                    padding: 4px;
                    color: #6b7280;
                    transition: color 0.2s;
                }

                .ai-search-close:hover {
                    color: #111827;
                }

                .ai-search-input-wrapper {
                    padding: 16px;
                    border-bottom: 1px solid #e5e7eb;
                }

                .ai-search-input-container {
                    position: relative;
                    display: flex;
                    gap: 8px;
                }

                .ai-search-input {
                    flex: 1;
                    padding: 10px 12px;
                    border: 2px solid #e5e7eb;
                    border-radius: 8px;
                    font-size: 14px;
                    transition: border-color 0.2s;
                }

                .ai-search-input:focus {
                    outline: none;
                    border-color: #10b981;
                }

                .ai-search-btn {
                    padding: 10px 16px;
                    background: #10b981;
                    color: white;
                    border: none;
                    border-radius: 8px;
                    font-size: 14px;
                    font-weight: 500;
                    cursor: pointer;
                    transition: background 0.2s;
                }

                .ai-search-btn:hover {
                    background: #059669;
                }

                .ai-search-btn:disabled {
                    opacity: 0.5;
                    cursor: not-allowed;
                }

                .ai-search-results {
                    flex: 1;
                    overflow-y: auto;
                    padding: 8px;
                }

                .ai-search-result-item {
                    padding: 12px;
                    border-radius: 8px;
                    cursor: pointer;
                    transition: background 0.2s;
                    margin-bottom: 4px;
                }

                .ai-search-result-item:hover {
                    background: #f3f4f6;
                }

                .ai-search-result-title {
                    font-size: 14px;
                    font-weight: 600;
                    color: #111827;
                    margin-bottom: 4px;
                }

                .ai-search-result-meta {
                    display: flex;
                    gap: 8px;
                    font-size: 12px;
                    color: #6b7280;
                    align-items: center;
                }

                .ai-search-similarity {
                    display: inline-block;
                    padding: 2px 6px;
                    background: #10b981;
                    color: white;
                    border-radius: 4px;
                    font-size: 11px;
                    font-weight: 500;
                }

                .ai-search-empty {
                    text-align: center;
                    padding: 40px 20px;
                    color: #6b7280;
                }

                .ai-search-loading {
                    text-align: center;
                    padding: 40px 20px;
                }

                .ai-search-spinner {
                    display: inline-block;
                    width: 24px;
                    height: 24px;
                    border: 3px solid #e5e7eb;
                    border-top-color: #10b981;
                    border-radius: 50%;
                    animation: spin 0.8s linear infinite;
                }

                @keyframes spin {
                    to { transform: rotate(360deg); }
                }

                @media (max-width: 640px) {
                    .ai-search-widget {
                        bottom: 16px;
                        right: 16px;
                    }

                    .ai-search-trigger {
                        width: 48px;
                        height: 48px;
                    }

                    .ai-search-panel {
                        bottom: 80px;
                        right: 16px;
                        left: 16px;
                        width: auto;
                    }
                }
            `;

            const styleSheet = document.createElement('style');
            styleSheet.id = 'ai-search-widget-styles';
            styleSheet.textContent = styles;
            document.head.appendChild(styleSheet);
        }

        createWidget() {
            const widget = document.createElement('div');
            widget.className = 'ai-search-widget';
            widget.innerHTML = `
                <button class="ai-search-trigger" id="aiSearchTrigger">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
                <div class="ai-search-panel" id="aiSearchPanel">
                    <div class="ai-search-header">
                        <h3>🔍 AI 智能搜索</h3>
                        <button class="ai-search-close" id="aiSearchClose">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="ai-search-input-wrapper">
                        <div class="ai-search-input-container">
                            <input 
                                type="text" 
                                class="ai-search-input" 
                                id="aiSearchInput" 
                                placeholder="${this.options.placeholder}"
                            >
                            <button class="ai-search-btn" id="aiSearchButton">搜索</button>
                        </div>
                    </div>
                    <div class="ai-search-results" id="aiSearchResults">
                        <div class="ai-search-empty">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin: 0 auto 12px; opacity: 0.3;">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                            <p>输入关键词开始搜索</p>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(widget);
            this.widget = widget;
        }

        attachEventListeners() {
            const trigger = document.getElementById('aiSearchTrigger');
            const close = document.getElementById('aiSearchClose');
            const input = document.getElementById('aiSearchInput');
            const button = document.getElementById('aiSearchButton');

            trigger.addEventListener('click', () => this.toggle());
            close.addEventListener('click', () => this.close());
            button.addEventListener('click', () => this.search());
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') this.search();
            });

            // Close on outside click
            document.addEventListener('click', (e) => {
                if (this.isOpen && !this.widget.contains(e.target)) {
                    this.close();
                }
            });
        }

        toggle() {
            this.isOpen ? this.close() : this.open();
        }

        open() {
            const panel = document.getElementById('aiSearchPanel');
            panel.classList.add('open');
            this.isOpen = true;
            document.getElementById('aiSearchInput').focus();
        }

        close() {
            const panel = document.getElementById('aiSearchPanel');
            panel.classList.remove('open');
            this.isOpen = false;
        }

        async search() {
            const input = document.getElementById('aiSearchInput');
            const query = input.value.trim();
            if (!query) return;

            const button = document.getElementById('aiSearchButton');
            const resultsContainer = document.getElementById('aiSearchResults');

            button.disabled = true;
            button.textContent = '搜索中...';
            resultsContainer.innerHTML = '<div class="ai-search-loading"><div class="ai-search-spinner"></div></div>';

            try {
                const response = await fetch(`${this.options.apiBase}/ai/search`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${this.options.authToken}`
                    },
                    body: JSON.stringify({
                        query: query,
                        limit: this.options.limit,
                        min_similarity: this.options.minSimilarity
                    })
                });

                const data = await response.json();
                
                if (data.data && data.data.results) {
                    this.displayResults(data.data.results);
                } else {
                    this.showEmpty('未找到相关结果');
                }
            } catch (error) {
                console.error('Search error:', error);
                this.showEmpty('搜索失败，请稍后重试');
            } finally {
                button.disabled = false;
                button.textContent = '搜索';
            }
        }

        displayResults(results) {
            const container = document.getElementById('aiSearchResults');
            
            if (results.length === 0) {
                this.showEmpty('未找到相关结果');
                return;
            }

            this.results = results;
            container.innerHTML = results.map((result, index) => `
                <div class="ai-search-result-item" data-index="${index}">
                    <div class="ai-search-result-title">${this.escapeHtml(result.title)}</div>
                    <div class="ai-search-result-meta">
                        <span class="ai-search-similarity">${Math.round(result.similarity * 100)}%</span>
                        <span>${result.category || '未分类'}</span>
                    </div>
                </div>
            `).join('');

            // Attach click handlers
            container.querySelectorAll('.ai-search-result-item').forEach(item => {
                item.addEventListener('click', () => {
                    const index = parseInt(item.dataset.index);
                    this.handleResultClick(this.results[index]);
                });
            });
        }

        showEmpty(message) {
            const container = document.getElementById('aiSearchResults');
            container.innerHTML = `
                <div class="ai-search-empty">
                    <p>${message}</p>
                </div>
            `;
        }

        handleResultClick(result) {
            if (this.options.onResultClick) {
                this.options.onResultClick(result);
            } else {
                // Default behavior: open in new tab
                window.open(`/knowledge/${result.id}`, '_blank');
            }
            this.close();
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
    window.AISearchWidget = AISearchWidget;

    // Auto-initialize if data attribute is present
    if (document.querySelector('[data-ai-search-widget]')) {
        document.addEventListener('DOMContentLoaded', () => {
            new AISearchWidget();
        });
    }
})();
