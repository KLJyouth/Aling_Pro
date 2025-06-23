// 历史记录渲染模块
export class HistoryRenderer {    constructor(historyManager) {
        this.historyManager = historyManager;
        this.callbacks = {
            onSelectSession: () => console.warn('onSelectSession callback not set')
        };
        
        this.dom = {
            historyList: null,
            searchInput: null
        };

        // 延迟初始化 DOM 元素
        this.initDOMElements();
        
        // 如果 DOM 元素存在，初始化事件监听
        if (this.dom.historyList && this.dom.searchInput) {
            this.initEventListeners();
        }
    }    // 初始化 DOM 元素
    initDOMElements() {
        this.dom.historyList = document.getElementById('historyList');
        this.dom.searchInput = document.getElementById('historySearch');
        
        if (!this.dom.historyList) {
            console.warn('历史记录列表元素未找到 (ID: historyList)');
        }
        if (!this.dom.searchInput) {
            console.warn('历史记录搜索输入框未找到 (ID: historySearch)');
        }
    }

    // 设置回调函数
    setCallback(name, fn) {
        if (typeof fn === 'function') {
            this.callbacks[name] = fn;
        } else {
            console.error(`Invalid callback for ${name}`);
        }
    }

    // 安全调用回调
    callCallback(name, ...args) {
        try {
            return this.callbacks[name](...args);
        } catch (error) {
            console.error(`Error in ${name} callback:`, error);
        }
    }    // 初始化事件监听
    initEventListeners() {
        // 搜索输入事件
        if (this.dom.searchInput) {
            this.dom.searchInput.addEventListener('input', (e) => {
                this.renderSessions(this.historyManager.searchSessions(e.target.value));
            });
        }
    }    // 渲染会话列表
    renderSessions(sessions) {
        if (!this.dom.historyList) {
            console.warn('无法渲染会话：historyList 元素不存在');
            return;
        }
        
        this.dom.historyList.innerHTML = '';

        if (!sessions || sessions.length === 0) {
            this.renderEmptyState();
            return;
        }

        sessions.forEach(session => {
            const sessionElement = document.createElement('div');
            sessionElement.className = 'list-group-item list-group-item-action';
            
            const lastMessage = this.getLastMessage(session);
            const formattedDate = this.formatDate(session.lastActive);
            
            sessionElement.innerHTML = `
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">会话 ${this.formatSessionTitle(session)}</h6>
                    <small class="text-muted">${formattedDate}</small>
                </div>
                <p class="mb-1 text-truncate">${lastMessage}</p>
                <div class="session-actions mt-2">
                    <button class="btn btn-sm btn-outline-primary me-2" data-action="continue">
                        <i class="bi bi-chat"></i> 继续对话
                    </button>
                    <button class="btn btn-sm btn-outline-danger" data-action="delete">
                        <i class="bi bi-trash"></i> 删除
                    </button>
                </div>
            `;

            // 绑定事件
            const continueBtn = sessionElement.querySelector('[data-action="continue"]');
            const deleteBtn = sessionElement.querySelector('[data-action="delete"]');

            continueBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.callCallback('onSelectSession', session.id);
            });

            deleteBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.deleteSession(session.id);
            });

            sessionElement.addEventListener('click', () => {
                this.callCallback('onSelectSession', session.id);
            });

            this.dom.historyList.appendChild(sessionElement);
        });
    }    // 渲染空状态
    renderEmptyState() {
        if (!this.dom.historyList) {
            return;
        }
        
        const emptyElement = document.createElement('div');
        emptyElement.className = 'text-center p-4';
        emptyElement.innerHTML = `
            <div class="text-muted mb-3">
                <i class="bi bi-chat-square-text" style="font-size: 2rem;"></i>
            </div>
            <h6>暂无会话历史</h6>
            <p class="text-muted small">开始一个新的对话吧！</p>
        `;
        this.dom.historyList.appendChild(emptyElement);
    }

    // 格式化会话标题
    formatSessionTitle(session) {
        const date = new Date(session.createdAt);
        return `${date.toLocaleDateString()} #${session.id.slice(-4)}`;
    }

    // 获取最后一条消息
    getLastMessage(session) {
        if (!session.messages || session.messages.length === 0) {
            return '暂无消息';
        }
        const lastMsg = session.messages[session.messages.length - 1];
        return lastMsg.content.length > 50 ? 
            lastMsg.content.substring(0, 50) + '...' : 
            lastMsg.content;
    }

    // 格式化日期
    formatDate(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));

        if (days === 0) {
            return date.toLocaleTimeString();
        } else if (days === 1) {
            return '昨天';
        } else if (days < 7) {
            return `${days}天前`;
        } else {
            return date.toLocaleDateString();
        }
    }

    // 删除会话
    async deleteSession(sessionId) {
        try {
            const result = await this.historyManager.deleteSession(sessionId);
            if (result) {
                const sessions = await this.historyManager.loadSessions();
                this.renderSessions(sessions);
            }
        } catch (error) {
            console.error('删除会话失败:', error);
            // 可以添加错误提示UI
        }
    }

    // 搜索会话
    searchSessions(keyword) {
        if (!keyword) {
            return this.historyManager.sessions;
        }
        
        return this.historyManager.sessions.filter(session => {
            const lastMessage = this.getLastMessage(session);
            const title = this.formatSessionTitle(session);
            const searchText = [title, lastMessage].join(' ').toLowerCase();
            return searchText.includes(keyword.toLowerCase());
        });
    }
}

// 初始化函数
export function initHistoryRenderer(historyManager) {
    return new HistoryRenderer(historyManager);
}