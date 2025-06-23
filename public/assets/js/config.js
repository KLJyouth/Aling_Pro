try {
// 前端API基础配置 const API_BASE_URL = '/api'; const WEBSOCKET_URL = 'ws://' + window.location.host + '/ws'; export { API_BASE_URL, WEBSOCKET_URL };
} catch (error) {
    console.error(error);
    // 处理错误
}
