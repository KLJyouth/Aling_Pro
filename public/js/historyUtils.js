/**
 * 历史会话工具函数
 * 提供会话历史的加载、删除等功能
 */

/**
 * 加载用户的会话历史
 * @param {string} userName - 用户名
 * @returns {Promise<Object>} - 包含历史会话的对象
 */
async function loadConversationHistory(userName) {
    try {
        // 检查参数
        if (!userName) {
            console.error('加载历史会话失败: 用户名不能为空');
            return { success: false, error: '用户名不能为空', history: [] };
        }

        // 构建请求URL
        const requestUrl = `${API_ENDPOINTS.HISTORY_MESSAGES}?user=${encodeURIComponent(userName)}`;
        

        // 发送请求
        const response = await authFetch(requestUrl);
        
        // 检查响应状态
        if (!response.ok) {
            const errorText = await response.text();
            console.error('历史会话API响应错误:', {
                status: response.status,
                statusText: response.statusText,
                errorText
            });
            return { 
                success: false, 
                error: `请求失败: ${response.status}`, 
                history: [] 
            };
        }

        // 解析响应数据
        const data = await response.json();
        

        // 验证响应格式
        if (!data || typeof data !== 'object') {
            return { 
                success: false, 
                error: '无效的响应格式', 
                history: [] 
            };
        }

        // 返回处理后的数据
        return {
            success: data.success === false ? false : true,
            error: data.error || '',
            history: data.history || data.messages || []
        };
    } catch (error) {
        console.error('加载历史会话异常:', error);
        return { 
            success: false, 
            error: error.message || '加载历史会话失败', 
            history: [] 
        };
    }
}

/**
 * 删除指定的会话消息
 * @param {string} messageId - 消息ID
 * @param {string} userName - 用户名
 * @returns {Promise<boolean>} - 是否删除成功
 */
async function deleteConversation(messageId, userName) {
    try {
        // 检查参数
        if (!messageId) {
            console.error('删除会话失败: 消息ID不能为空');
            return false;
        }

        // 构建请求URL
        const requestUrl = `${API_ENDPOINTS.HISTORY_MESSAGES}/${messageId}`;
        const queryParams = userName ? `?user=${encodeURIComponent(userName)}` : '';
        
        // 发送删除请求
        const response = await authFetch(`${requestUrl}${queryParams}`, { 
            method: 'DELETE' 
        });
        
        // 检查响应状态
        if (!response.ok) {
            const errorText = await response.text();
            console.error('删除会话API响应错误:', {
                status: response.status,
                statusText: response.statusText,
                errorText
            });
            return false;
        }

        // 解析响应数据
        const data = await response.json();
        return data.success === true;
    } catch (error) {
        console.error('删除会话异常:', error);
        return false;
    }
}

// 如果在浏览器环境，将函数挂载到window对象上
if (typeof window !== 'undefined') {
    window.loadConversationHistory = loadConversationHistory;
    window.deleteConversation = deleteConversation;
}

// 如果是Node.js环境，则导出函数
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { 
        loadConversationHistory,
        deleteConversation
    };
}