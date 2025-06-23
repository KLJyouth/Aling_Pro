// 消息处理相关工具
export class MessageProcessor {
    static formatDate(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleString('zh-CN', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    static processMarkdown(text) {
        try {
            // 处理代码块
            const processCodeBlocks = (text) => {
                const codeBlockRegex = /```(\w*)\n([\s\S]*?)```/g;
                return text.replace(codeBlockRegex, (match, lang, code) => {
                    try {
                        const highlighted = hljs.highlightAuto(code.trim(), lang ? [lang] : undefined).value;
                        return `<pre><code class="hljs ${lang || ''}">${highlighted}</code></pre>`;
                    } catch (error) {
                        console.warn('Code highlighting failed:', error);
                        return `<pre><code class="${lang || ''}">${this.escapeHtml(code.trim())}</code></pre>`;
                    }
                });
            };

            // 处理行内代码
            const processInlineCode = (text) => {
                return text.replace(/`([^`]+)`/g, '<code>$1</code>');
            };

            // 处理数学公式
            const processMathExpressions = (text) => {
                // 行内公式
                text = text.replace(/\$([^\$]+)\$/g, '<span class="math-inline">$1</span>');
                // 块级公式
                text = text.replace(/\$\$([^\$]+)\$\$/g, '<div class="math-block">$1</div>');
                return text;
            };

            // 处理表格
            const processTables = (text) => {
                return text.replace(/\|.*\|/g, (match) => {
                    if (match.includes('|-')) return match; // 表头分隔行
                    return `<div class="table-row">${match}</div>`;
                });
            };

            // 应用所有处理器
            text = processCodeBlocks(text);
            text = processInlineCode(text);
            text = processMathExpressions(text);
            text = processTables(text);

            // 使用marked处理其他Markdown语法
            return marked.parse(text, {
                gfm: true,
                breaks: true,
                sanitize: true
            });
        } catch (error) {
            console.error('Markdown processing failed:', error);
            return this.escapeHtml(text);
        }
    }

    static escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    static async createMessageElement(role, content, metadata = {}) {
        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        
        try {
            // 处理不同类型的消息内容
            switch (metadata.type) {
                case 'image':
                    await this.handleImageMessage(contentDiv, content, metadata);
                    break;
                    
                case 'code':
                    this.handleCodeMessage(contentDiv, content, metadata);
                    break;
                    
                case 'error':
                    this.handleErrorMessage(contentDiv, content);
                    break;
                    
                default:
                    contentDiv.innerHTML = this.processMarkdown(content);
            }
            
            // 添加消息元数据
            if (Object.keys(metadata).length > 0) {
                this.appendMetadata(contentDiv, metadata);
            }
            
        } catch (error) {
            console.error('Message processing failed:', error);
            contentDiv.innerHTML = `<div class="error-message">消息处理失败</div>`;
        }
        
        return contentDiv;
    }

    static async handleImageMessage(container, content, metadata) {
        const img = document.createElement('img');
        img.src = content;
        img.alt = metadata.description || '生成的图片';
        img.className = 'img-fluid rounded';
        img.loading = 'lazy';

        // 添加图片加载错误处理
        img.onerror = () => {
            container.innerHTML = '<div class="error-message">图片加载失败</div>';
        };

        container.appendChild(img);

        if (metadata.description) {
            const description = document.createElement('p');
            description.className = 'mt-2 text-muted small';
            description.textContent = metadata.description;
            container.appendChild(description);
        }
    }

    static handleCodeMessage(container, content, metadata) {
        const pre = document.createElement('pre');
        const code = document.createElement('code');
        code.className = `language-${metadata.language || 'plaintext'}`;
        code.textContent = content;
        pre.appendChild(code);
        container.appendChild(pre);
        
        // 添加复制按钮
        const copyButton = document.createElement('button');
        copyButton.className = 'copy-button';
        copyButton.innerHTML = '<i class="bi bi-clipboard"></i>';
        copyButton.onclick = () => navigator.clipboard.writeText(content);
        container.appendChild(copyButton);
    }

    static handleErrorMessage(container, content) {
        container.innerHTML = `
            <div class="error-message">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>${this.escapeHtml(content)}</span>
            </div>
        `;
    }    static appendMetadata(container, metadata) {
        const metaDiv = document.createElement('div');
        metaDiv.className = 'message-metadata';
        
        // 添加模型信息
        if (metadata.model) {
            const modelSpan = document.createElement('span');
            modelSpan.className = 'model-info';
            modelSpan.textContent = `Model: ${metadata.model}`;
            metaDiv.appendChild(modelSpan);
        }
        
        // 添加处理时间
        if (metadata.processingTime) {
            const timeSpan = document.createElement('span');
            timeSpan.className = 'processing-time';
            timeSpan.textContent = `处理时间: ${metadata.processingTime}ms`;
            metaDiv.appendChild(timeSpan);
        }
        
        container.appendChild(metaDiv);
    }

    // 处理用户消息内容
    static processUserMessage(content) {
        if (!content || typeof content !== 'string') {
            return '';
        }
        
        try {
            // 对用户消息进行HTML转义处理
            const escaped = this.escapeHtml(content);
            
            // 处理换行符
            const withBreaks = escaped.replace(/\n/g, '<br>');
            
            // 处理简单的markdown格式（如果用户输入了）
            let processed = withBreaks;
            processed = processed.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            processed = processed.replace(/\*(.*?)\*/g, '<em>$1</em>');
            processed = processed.replace(/`(.*?)`/g, '<code>$1</code>');
            
            return processed;
        } catch (error) {
            console.error('Error processing user message:', error);
            return this.escapeHtml(content);
        }
    }

    // 处理AI助手消息内容
    static processAssistantMessage(content) {
        if (!content || typeof content !== 'string') {
            return '';
        }
        
        try {
            // AI消息通常支持完整的Markdown格式
            return this.processMarkdown(content);
        } catch (error) {
            console.error('Error processing assistant message:', error);
            // 如果Markdown处理失败，至少进行HTML转义
            return this.escapeHtml(content).replace(/\n/g, '<br>');
        }
    }
}
