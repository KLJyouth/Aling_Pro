try {
// 设置API基础URL，默认使用当前域名
const API_BASE_URL = window.location.origin;

const API_ENDPOINTS = {
    // Auth
    LOGIN: `${API_BASE_URL}/api/login`,
    REGISTER: `${API_BASE_URL}/api/register`,
    AUTH_REFRESH: `${API_BASE_URL}/api/auth/refresh`,
    AUTH_FORGOT_PASSWORD: `${API_BASE_URL}/api/auth/forgot`,
    AUTH_RESET_PASSWORD: `${API_BASE_URL}/api/auth/reset`,
    LOGOUT: `${API_BASE_URL}/api/logout`,
    
    // User Management
    USER_PROFILE: `${API_BASE_URL}/api/user`,
    USER_UPDATE: (userId) => `${API_BASE_URL}/api/user/${userId}`,
    USER_DELETE: (userId) => `${API_BASE_URL}/api/user/${userId}`,
    USER_CHANGE_PASSWORD: `${API_BASE_URL}/api/user/change-password`,
    USER_LIST: `${API_BASE_URL}/api/user`,
    
    // Chat & History
    CHAT_DEEPSEEK: `${API_BASE_URL}/api/chat/chat`,
    HISTORY_SESSIONS: `${API_BASE_URL}/api/history/sessions`,
    HISTORY_MESSAGES: `${API_BASE_URL}/api/history`,
    SAVE_HISTORY: `${API_BASE_URL}/api/history`,

    // TTS
    TTS_SPEAK: `${API_BASE_URL}/api/tts/speak`,

    // Image
    IMAGE_GENERATE: `${API_BASE_URL}/api/image/generate`,

    // Memory
    MEMORY_LOAD_SAVE: `${API_BASE_URL}/api/memory`,
    MEMORY_COUNT: `${API_BASE_URL}/api/memory/count`, // 修复API路径以匹配规范

    // Agents
    AGENTS_STATUS: `${API_BASE_URL}/api/agents/status`,
    AGENT_RESTART: (agentId) => `${API_BASE_URL}/api/agents/${agentId}/restart`,
    AGENTS_REGISTER: `${API_BASE_URL}/agents/register-agent`,
    AGENTS_ALL: `${API_BASE_URL}/agents/all`,
    AGENT_DETAIL: (agentId) => `${API_BASE_URL}/agents/${agentId}`,
    AGENT_DELETE: (agentId) => `${API_BASE_URL}/agents/${agentId}`,

    // Sentiment
    SENTIMENT_ANALYSIS: (userId) => `${API_BASE_URL}/api/v1/sentiment/${userId}`,

    // User Settings
    USER_PROFILE: `${API_BASE_URL}/api/user/profile`,
    USER_SETTINGS: (userId) => `${API_BASE_URL}/settings/user-settings/${userId}`,

    // Exports
    EXPORT_CONVERSATION: (format, userId) => `${API_BASE_URL}/api/v1/exports/${format}/${userId}`
};

// 如果在浏览器环境，可以将 API_ENDPOINTS 挂载到 window 对象上，方便全局访问
if (typeof window !== 'undefined') {
    window.API_ENDPOINTS = API_ENDPOINTS;
    window.API_BASE_URL = API_BASE_URL;
}

// 如果是 Node.js 环境 (例如用于测试或 SSR)，则导出
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { API_BASE_URL, API_ENDPOINTS };
}
} catch (error) {
    console.error(error);
    // 处理错误
}
