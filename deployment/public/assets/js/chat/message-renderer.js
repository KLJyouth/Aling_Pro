import { UIUtils } from './utils/ui-utils.js';
import { MessageProcessor } from './message-processor.js';

export class MessageRenderer {
    constructor(container) {
        this.container = container;
        this.messageQueue = [];
        this.isProcessingQueue = false;
        this.currentTypingEffect = null;
    }

    async render(message) {
        this.messageQueue.push(message);
        if (!this.isProcessingQueue) {
            await this.processMessageQueue();
        }
    }

    async processMessageQueue() {
        if (this.messageQueue.length === 0) {
            this.isProcessingQueue = false;
            return;
        }

        this.isProcessingQueue = true;
        const message = this.messageQueue.shift();

        try {
            const messageElement = await this.createMessageElement(message);
            this.container.appendChild(messageElement);
            messageElement.scrollIntoView({ behavior: 'smooth' });

            // 处理打字机效果
            if (message.type === 'assistant' && message.useTypingEffect) {
                await this.applyTypingEffect(messageElement, message.content);
            }

            // 继续处理队列中的下一条消息
            await this.processMessageQueue();
        } catch (error) {
            console.error('Error processing message:', error);
            UIUtils.showError('消息渲染失败');
            this.isProcessingQueue = false;
        }
    }

    async createMessageElement(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${message.type} fade-in`;
        messageDiv.dataset.messageId = message.id;
        messageDiv.dataset.timestamp = message.timestamp;

        // 根据消息类型创建不同的内容
        switch (message.type) {
            case 'loading':
                return this.createLoadingMessage(message);
            case 'error':
                return this.createErrorMessage(message);
            case 'user':
                return this.createUserMessage(message);
            case 'assistant':
            case 'ai':
                return this.createAssistantMessage(message);
            default:
                return this.createGenericMessage(message);
        }
    }

    createLoadingMessage(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message loading fade-in';
        messageDiv.dataset.messageId = message.id;

        messageDiv.innerHTML = `
            <div class="message-content ai-message">
                <div class="loading-indicator">
                    <div class="loading-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <span class="loading-text">${message.content}</span>
                </div>
            </div>
        `;

        return messageDiv;
    }

    createErrorMessage(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message error fade-in';
        messageDiv.dataset.messageId = message.id;

        messageDiv.innerHTML = `
            <div class="message-content error-message">
                <div class="error-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div class="error-content">
                    <div class="error-title">${message.content}</div>
                    ${message.details ? `<div class="error-details">${message.details}</div>` : ''}
                    <button class="retry-button" onclick="window.chatInstance?.ui?.retryLastMessage?.()">
                        <i class="bi bi-arrow-clockwise"></i>
                        重试
                    </button>
                </div>
            </div>
        `;

        return messageDiv;
    }

    createUserMessage(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message user fade-in';
        messageDiv.dataset.messageId = message.id;

        const processedContent = MessageProcessor.processUserMessage(message.content);

        messageDiv.innerHTML = `
            <div class="message-content user-message">
                <div class="message-text">${processedContent}</div>
                <div class="message-time">${this.formatTime(message.timestamp)}</div>
            </div>
        `;

        return messageDiv;
    }    createAssistantMessage(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message assistant fade-in';
        messageDiv.dataset.messageId = message.id;

        const processedContent = MessageProcessor.processAssistantMessage(message.content);

        messageDiv.innerHTML = `
            <div class="message-content ai-message">
                <div class="message-text">${processedContent}</div>
                <div class="message-actions">
                    <button class="action-button copy-button" title="复制" data-message-id="${message.id}">
                        <i class="bi bi-clipboard"></i>
                    </button>
                    <button class="action-button regenerate-button" title="重新生成" data-message-id="${message.id}">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <button class="action-button speak-button" title="朗读" data-message-id="${message.id}">
                        <i class="bi bi-volume-up"></i>
                    </button>
                </div>
                <div class="message-time">${this.formatTime(message.timestamp)}</div>
            </div>
        `;

        // 添加事件监听器
        this.attachMessageEventListeners(messageDiv, message);

        return messageDiv;
    }

    createGenericMessage(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${message.type} fade-in`;
        messageDiv.dataset.messageId = message.id;

        messageDiv.innerHTML = `
            <div class="message-content">
                <div class="message-text">${message.content}</div>
                <div class="message-time">${this.formatTime(message.timestamp)}</div>
            </div>
        `;

        return messageDiv;
    }    // 格式化时间显示
    formatTime(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleTimeString('zh-CN', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // 为消息元素添加事件监听器
    attachMessageEventListeners(messageDiv, message) {
        const copyButton = messageDiv.querySelector('.copy-button');
        const regenerateButton = messageDiv.querySelector('.regenerate-button');
        const speakButton = messageDiv.querySelector('.speak-button');

        if (copyButton) {
            copyButton.addEventListener('click', () => {
                this.copyMessageContent(message.content);
            });
        }

        if (regenerateButton) {
            regenerateButton.addEventListener('click', () => {
                if (this.onRegenerate) {
                    this.onRegenerate(message.id);
                } else {
                    console.warn('Regenerate callback not set');
                }
            });
        }

        if (speakButton) {
            speakButton.addEventListener('click', () => {
                this.speakMessageContent(message.content);
            });
        }
    }

    async applyTypingEffect(element, content) {
        const contentDiv = element.querySelector('.message-content');
        if (!contentDiv) return;

        // 清除之前未完成的打字效果
        if (this.currentTypingEffect) {
            this.currentTypingEffect.cancel();
        }

        return new Promise((resolve) => {
            let index = 0;
            const processedContent = MessageProcessor.processMarkdown(content);
            contentDiv.innerHTML = '';

            const type = () => {
                if (index < processedContent.length) {
                    contentDiv.innerHTML += processedContent[index];
                    index++;
                    this.currentTypingEffect = requestAnimationFrame(type);
                } else {
                    this.currentTypingEffect = null;
                    resolve();
                }
            };

            this.currentTypingEffect = requestAnimationFrame(type);
        });
    }

    // 工具方法
    async copyMessageContent(content) {
        try {
            await navigator.clipboard.writeText(content);
            UIUtils.showToast('内容已复制到剪贴板');
        } catch (error) {
            UIUtils.showError('复制失败');
        }
    }

    speakMessageContent(content) {
        const utterance = new SpeechSynthesisUtterance(content);
        utterance.lang = 'zh-CN';
        window.speechSynthesis.speak(utterance);
    }

    // 设置重新生成回调
    setRegenerateCallback(callback) {
        this.onRegenerate = callback;
    }

    // 清理资源
    cleanup() {
        if (this.currentTypingEffect) {
            cancelAnimationFrame(this.currentTypingEffect);
            this.currentTypingEffect = null;
        }
        this.messageQueue = [];
        this.isProcessingQueue = false;
    }

    // 显示重试按钮
    showRetryButton(messageId) {
        const messageElement = this.container.querySelector(`[data-message-id="${messageId}"]`);
        if (messageElement) {
            const retryButton = document.createElement('button');
            retryButton.className = 'btn btn-sm btn-outline-primary mt-2';
            retryButton.innerHTML = '<i class="bi bi-arrow-clockwise"></i> 重试';
            retryButton.onclick = () => {
                this.regenerateCallback(messageId);
                retryButton.remove();
            };
            messageElement.appendChild(retryButton);
        }
    }
}
