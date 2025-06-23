// 简单的API模拟服务器，用于测试数据库集成
// 运行命令: node test-api-server.js

const http = require('http');
const url = require('url');

// 模拟数据存储
let conversations = [];
let authUsers = new Map();

// 模拟用户认证状态
const authenticatedUserId = 'user123';
authUsers.set(authenticatedUserId, {
    id: authenticatedUserId,
    username: 'testuser',
    authenticated: true
});

const server = http.createServer((req, res) => {
    // 设置CORS头
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With, Authorization');
    
    if (req.method === 'OPTIONS') {
        res.writeHead(200);
        res.end();
        return;
    }

    const parsedUrl = url.parse(req.url, true);
    const path = parsedUrl.pathname;
    const method = req.method;

    console.log(`${method} ${path}`);

    try {
        // 认证检查端点
        if (path === '/api/v1/auth/check' && method === 'GET') {
            // 随机返回认证状态（50%概率认证）
            const isAuthenticated = Math.random() > 0.5;
            
            res.writeHead(200, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({
                success: true,
                data: {
                    authenticated: isAuthenticated,
                    userId: isAuthenticated ? authenticatedUserId : null
                }
            }));
            return;
        }

        // 聊天对话端点
        if (path === '/api/v1/chat/conversations') {
            if (method === 'GET') {
                // 获取对话列表
                const source = parsedUrl.query.source || 'general';
                const limit = parseInt(parsedUrl.query.limit) || 10;
                
                const filteredConversations = conversations
                    .filter(conv => conv.source === source)
                    .slice(-limit);

                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({
                    success: true,
                    data: filteredConversations
                }));
                return;
            }

            if (method === 'POST') {
                // 创建新对话
                let body = '';
                req.on('data', chunk => {
                    body += chunk.toString();
                });

                req.on('end', () => {
                    try {
                        const data = JSON.parse(body);
                        const conversation = {
                            id: Date.now().toString(),
                            title: data.title || '新对话',
                            messages: data.messages || [],
                            source: data.source || 'general',
                            createdAt: new Date().toISOString(),
                            updatedAt: new Date().toISOString()
                        };

                        conversations.push(conversation);
                        
                        res.writeHead(201, { 'Content-Type': 'application/json' });
                        res.end(JSON.stringify({
                            success: true,
                            data: conversation
                        }));
                    } catch (error) {
                        res.writeHead(400, { 'Content-Type': 'application/json' });
                        res.end(JSON.stringify({
                            success: false,
                            error: 'Invalid JSON'
                        }));
                    }
                });
                return;
            }

            if (method === 'DELETE') {
                // 删除对话
                const source = parsedUrl.query.source || 'general';
                const initialLength = conversations.length;
                
                conversations = conversations.filter(conv => conv.source !== source);
                
                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({
                    success: true,
                    data: {
                        deletedCount: initialLength - conversations.length
                    }
                }));
                return;
            }
        }

        // 404 未找到
        res.writeHead(404, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
            success: false,
            error: 'Endpoint not found'
        }));

    } catch (error) {
        console.error('Server error:', error);
        res.writeHead(500, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
            success: false,
            error: 'Internal server error'
        }));
    }
});

const PORT = 3001;
server.listen(PORT, () => {
    console.log(`🚀 API模拟服务器运行在 http://localhost:${PORT}`);
    console.log('📋 可用端点:');
    console.log('  GET    /api/v1/auth/check');
    console.log('  GET    /api/v1/chat/conversations');
    console.log('  POST   /api/v1/chat/conversations');
    console.log('  DELETE /api/v1/chat/conversations');
    console.log('');
    console.log('💡 提示: 在浏览器中访问测试页面时，确保此服务器正在运行');
});

// 优雅关闭
process.on('SIGINT', () => {
    console.log('\n🛑 服务器正在关闭...');
    server.close(() => {
        console.log('✅ 服务器已关闭');
        process.exit(0);
    });
});
