/**
 * AI Auth Helper
 * 认证辅助工具 - 处理登录状态检测和 token 管理
 */

(function() {
    'use strict';

    class AIAuthHelper {
        constructor(options = {}) {
            this.options = {
                tokenKeys: options.tokenKeys || ['auth_token', 'authorization', 'token'],
                apiBase: options.apiBase || '/api/v1/user',
                loginUrl: options.loginUrl || '/',
                onAuthRequired: options.onAuthRequired || null,
                onAuthExpired: options.onAuthExpired || null,
                checkInterval: options.checkInterval || 300000, // 5 minutes
                ...options
            };

            this.token = null;
            this.checkTimer = null;
            this.init();
        }

        init() {
            this.token = this.getToken();
            if (this.options.checkInterval > 0) {
                this.startPeriodicCheck();
            }
        }

        /**
         * 从 localStorage 获取 token
         */
        getToken() {
            for (const key of this.options.tokenKeys) {
                const token = localStorage.getItem(key);
                if (token) {
                    return token;
                }
            }
            return null;
        }

        /**
         * 设置 token
         */
        setToken(token, key = 'auth_token') {
            this.token = token;
            localStorage.setItem(key, token);
        }

        /**
         * 清除 token
         */
        clearToken() {
            this.token = null;
            for (const key of this.options.tokenKeys) {
                localStorage.removeItem(key);
            }
        }

        /**
         * 检查是否已登录
         */
        isAuthenticated() {
            return !!this.token;
        }

        /**
         * 验证 token 是否有效
         */
        async validateToken() {
            if (!this.token) {
                return false;
            }

            try {
                const response = await fetch(`${this.options.apiBase}/info`, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${this.token}`
                    }
                });

                if (response.status === 401 || response.status === 403) {
                    this.handleAuthExpired();
                    return false;
                }

                return response.ok;
            } catch (error) {
                console.error('Token validation error:', error);
                return false;
            }
        }

        /**
         * 处理需要认证的情况
         */
        handleAuthRequired() {
            if (this.options.onAuthRequired) {
                this.options.onAuthRequired();
            } else {
                this.showAuthRequiredDialog();
            }
        }

        /**
         * 处理认证过期的情况
         */
        handleAuthExpired() {
            this.clearToken();
            
            if (this.options.onAuthExpired) {
                this.options.onAuthExpired();
            } else {
                this.showAuthExpiredDialog();
            }
        }

        /**
         * 显示需要登录对话框
         */
        showAuthRequiredDialog() {
            this.showDialog({
                icon: '🔒',
                title: '需要登录',
                message: '请先登录以使用此功能',
                buttonText: '前往登录',
                onConfirm: () => {
                    window.location.href = this.options.loginUrl;
                }
            });
        }

        /**
         * 显示登录过期对话框
         */
        showAuthExpiredDialog() {
            this.showDialog({
                icon: '⏰',
                title: '登录已过期',
                message: '您的登录已过期，请重新登录',
                buttonText: '重新登录',
                onConfirm: () => {
                    window.location.href = this.options.loginUrl;
                }
            });
        }

        /**
         * 显示通用对话框
         */
        showDialog(config) {
            // Remove existing dialog
            const existing = document.getElementById('ai-auth-dialog');
            if (existing) {
                existing.remove();
            }

            const dialog = document.createElement('div');
            dialog.id = 'ai-auth-dialog';
            dialog.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 99999;
                animation: fadeIn 0.3s ease;
            `;

            dialog.innerHTML = `
                <div style="
                    background: white;
                    border-radius: 16px;
                    padding: 40px;
                    max-width: 400px;
                    width: 90%;
                    text-align: center;
                    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
                    animation: slideUp 0.3s ease;
                ">
                    <div style="font-size: 48px; margin-bottom: 16px;">${config.icon}</div>
                    <h2 style="font-size: 24px; margin-bottom: 12px; color: #111827; font-weight: 600;">${config.title}</h2>
                    <p style="color: #6b7280; margin-bottom: 24px; line-height: 1.6;">${config.message}</p>
                    <button id="ai-auth-dialog-btn" style="
                        width: 100%;
                        padding: 12px 24px;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        border: none;
                        border-radius: 8px;
                        font-size: 16px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: transform 0.2s;
                    ">${config.buttonText}</button>
                </div>
            `;

            // Add animations
            const style = document.createElement('style');
            style.textContent = `
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                @keyframes slideUp {
                    from { opacity: 0; transform: translateY(20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                #ai-auth-dialog-btn:hover {
                    transform: translateY(-2px);
                }
                #ai-auth-dialog-btn:active {
                    transform: translateY(0);
                }
            `;
            document.head.appendChild(style);

            document.body.appendChild(dialog);

            // Add event listener
            document.getElementById('ai-auth-dialog-btn').addEventListener('click', () => {
                dialog.remove();
                if (config.onConfirm) {
                    config.onConfirm();
                }
            });
        }

        /**
         * 显示提示横幅
         */
        showBanner(message, type = 'warning') {
            const colors = {
                warning: { bg: '#fef3c7', text: '#92400e', border: '#f59e0b' },
                error: { bg: '#fee2e2', text: '#991b1b', border: '#ef4444' },
                info: { bg: '#dbeafe', text: '#1e40af', border: '#3b82f6' },
                success: { bg: '#d1fae5', text: '#065f46', border: '#10b981' }
            };

            const color = colors[type] || colors.warning;

            const banner = document.createElement('div');
            banner.id = 'ai-auth-banner';
            banner.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                background: ${color.bg};
                color: ${color.text};
                padding: 12px 20px;
                text-align: center;
                z-index: 9999;
                font-size: 14px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                border-bottom: 2px solid ${color.border};
                animation: slideDown 0.3s ease;
            `;

            banner.innerHTML = `
                ${message}
                <button onclick="this.parentElement.remove()" style="
                    background: none;
                    border: none;
                    color: ${color.text};
                    font-size: 20px;
                    cursor: pointer;
                    margin-left: 16px;
                    padding: 0 8px;
                    opacity: 0.7;
                    transition: opacity 0.2s;
                " onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">×</button>
            `;

            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideDown {
                    from { transform: translateY(-100%); }
                    to { transform: translateY(0); }
                }
            `;
            document.head.appendChild(style);

            // Remove existing banner
            const existing = document.getElementById('ai-auth-banner');
            if (existing) {
                existing.remove();
            }

            document.body.prepend(banner);

            // Auto remove after 10 seconds
            setTimeout(() => {
                if (banner.parentElement) {
                    banner.remove();
                }
            }, 10000);
        }

        /**
         * 开始定期检查
         */
        startPeriodicCheck() {
            this.stopPeriodicCheck();
            this.checkTimer = setInterval(() => {
                this.validateToken();
            }, this.options.checkInterval);
        }

        /**
         * 停止定期检查
         */
        stopPeriodicCheck() {
            if (this.checkTimer) {
                clearInterval(this.checkTimer);
                this.checkTimer = null;
            }
        }

        /**
         * 销毁实例
         */
        destroy() {
            this.stopPeriodicCheck();
        }
    }

    // Export to global scope
    window.AIAuthHelper = AIAuthHelper;

    // Create default instance
    window.aiAuthHelper = new AIAuthHelper();

})();
