<!-- API参考文档页面 -->
<div class="docs-article">
    <section class="docs-section">
        <h2 id="api-introduction">API参考概述</h2>
        <p class="lead">AlingAi Pro提供强大的RESTful API，让您能够以编程方式访问和控制所有功能。本文档详细介绍了API的使用方法、认证机制和所有可用端点。</p>
        
        <div class="docs-alert docs-alert-info">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>版本说明：</strong> 当前API版本为v6，我们会保持向后兼容，但建议使用最新版本以获取全部功能。
            </div>
        </div>
    </section>
    
    <section class="docs-section">
        <h2 id="authentication">认证与授权</h2>
        <p>所有API请求都需要通过API密钥进行认证。按照以下步骤获取和使用您的API密钥：</p>
        
        <h3>获取API密钥</h3>
        <ol>
            <li>登录到您的<a href="/dashboard">AlingAi Pro控制台</a></li>
            <li>导航到"<strong>设置</strong>" > "<strong>API密钥</strong>"</li>
            <li>点击"<strong>创建新密钥</strong>"按钮</li>
            <li>为您的API密钥指定一个描述性名称和可选的权限范围</li>
            <li>系统将生成并显示您的API密钥</li>
        </ol>
        
        <div class="docs-alert docs-alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>安全警告：</strong> API密钥只会显示一次。请确保将其保存在安全的地方，不要在客户端代码中暴露您的API密钥。
            </div>
        </div>
        
        <h3>使用API密钥</h3>
        <p>在每个API请求中，通过在请求头中包含您的API密钥来进行身份验证：</p>
        
        <pre><code class="language-bash">curl -X GET https://api.alingai.pro/v6/assistants \
-H "Authorization: Bearer YOUR_API_KEY"</code></pre>
    </section>
    
    <section class="docs-section">
        <h2 id="base-url">基本URL</h2>
        <p>所有API请求的基本URL为：</p>
        
        <div class="docs-code-block">
            <code>https://api.alingai.pro/v6/</code>
        </div>
        
        <p>对于中国区用户，请使用以下基本URL：</p>
        
        <div class="docs-code-block">
            <code>https://api.alingai.com.cn/v6/</code>
        </div>
    </section>
    
    <section class="docs-section">
        <h2 id="rate-limits">访问限制</h2>
        <p>API调用受到速率限制，以确保系统稳定性。限制取决于您的订阅计划：</p>
        
        <div class="docs-table-container">
            <table class="docs-table">
                <thead>
                    <tr>
                        <th>计划</th>
                        <th>请求限制</th>
                        <th>并发请求</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>基础版</td>
                        <td>60次/分钟</td>
                        <td>5</td>
                    </tr>
                    <tr>
                        <td>专业版</td>
                        <td>300次/分钟</td>
                        <td>15</td>
                    </tr>
                    <tr>
                        <td>企业版</td>
                        <td>1000次/分钟</td>
                        <td>50</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <p>超过限制时，API将返回429状态码。您可以通过响应头监控您的使用情况：</p>
        <ul>
            <li><code>X-RateLimit-Limit</code>: 限制周期内的请求上限</li>
            <li><code>X-RateLimit-Remaining</code>: 当前周期内剩余的请求次数</li>
            <li><code>X-RateLimit-Reset</code>: 重置计数器的时间戳</li>
        </ul>
    </section>
    
    <section class="docs-section">
        <h2 id="error-handling">错误处理</h2>
        <p>当API请求失败时，服务器将返回适当的HTTP状态码和一个包含错误详情的JSON响应：</p>
        
        <pre><code class="language-json">{
  "error": {
    "code": "invalid_request",
    "message": "API密钥无效或已过期",
    "request_id": "req_1234567890",
    "details": {
      "field": "Authorization",
      "reason": "API key format is incorrect"
    }
  }
}</code></pre>
        
        <h3>常见错误代码</h3>
        <div class="docs-table-container">
            <table class="docs-table">
                <thead>
                    <tr>
                        <th>HTTP状态码</th>
                        <th>错误代码</th>
                        <th>描述</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>400</td>
                        <td>invalid_request</td>
                        <td>请求格式错误或缺少必要参数</td>
                    </tr>
                    <tr>
                        <td>401</td>
                        <td>unauthorized</td>
                        <td>认证失败或API密钥无效</td>
                    </tr>
                    <tr>
                        <td>403</td>
                        <td>permission_denied</td>
                        <td>无权执行请求的操作</td>
                    </tr>
                    <tr>
                        <td>404</td>
                        <td>not_found</td>
                        <td>请求的资源不存在</td>
                    </tr>
                    <tr>
                        <td>429</td>
                        <td>rate_limit_exceeded</td>
                        <td>超出API调用速率限制</td>
                    </tr>
                    <tr>
                        <td>500</td>
                        <td>internal_error</td>
                        <td>服务器内部错误</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>

<!-- API端点文档 -->
<div class="docs-article">
    <section class="docs-section">
        <h2 id="endpoints">API端点</h2>
        <p>以下是AlingAi Pro API提供的主要端点类别。点击每个类别查看详细文档。</p>
        
        <div class="docs-cards">
            <div class="docs-card api-endpoint-card" data-target="assistants">
                <div class="docs-card-icon">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="docs-card-content">
                    <h3>AI助手</h3>
                    <p>创建和管理自定义AI助手</p>
                </div>
                <div class="docs-card-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
            
            <div class="docs-card api-endpoint-card" data-target="chat">
                <div class="docs-card-icon">
                    <i class="fas fa-comment-dots"></i>
                </div>
                <div class="docs-card-content">
                    <h3>聊天接口</h3>
                    <p>发送消息并接收AI回复</p>
                </div>
                <div class="docs-card-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
            
            <div class="docs-card api-endpoint-card" data-target="knowledge">
                <div class="docs-card-icon">
                    <i class="fas fa-brain"></i>
                </div>
                <div class="docs-card-content">
                    <h3>知识库</h3>
                    <p>管理AI助手的知识来源</p>
                </div>
                <div class="docs-card-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
            
            <div class="docs-card api-endpoint-card" data-target="analytics">
                <div class="docs-card-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="docs-card-content">
                    <h3>分析统计</h3>
                    <p>获取使用统计和性能数据</p>
                </div>
                <div class="docs-card-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
        </div>
    </section>
    
    <!-- AI助手端点 -->
    <section id="assistants" class="docs-section api-section">
        <h2>AI助手端点</h2>
        <p>AI助手API允许您创建、查询、更新和删除自定义AI助手。</p>
        
        <div class="endpoint">
            <div class="endpoint-header">
                <span class="http-method http-get">GET</span>
                <span class="endpoint-path">/assistants</span>
            </div>
            <div class="endpoint-content">
                <h3>列出所有助手</h3>
                <p>获取当前账户下的所有AI助手列表。</p>
                
                <h4>查询参数</h4>
                <div class="docs-table-container">
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>参数</th>
                                <th>类型</th>
                                <th>必填</th>
                                <th>描述</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>limit</td>
                                <td>integer</td>
                                <td>否</td>
                                <td>每页返回的结果数量，默认为20，最大100</td>
                            </tr>
                            <tr>
                                <td>offset</td>
                                <td>integer</td>
                                <td>否</td>
                                <td>分页偏移量，默认为0</td>
                            </tr>
                            <tr>
                                <td>status</td>
                                <td>string</td>
                                <td>否</td>
                                <td>按状态筛选：active, inactive, training</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <h4>响应示例</h4>
                <pre><code class="language-json">{
  "data": [
    {
      "id": "asst_1234567890",
      "name": "客户服务助手",
      "description": "处理客户查询和问题排解",
      "role": "customer_support",
      "status": "active",
      "created_at": "2023-06-15T08:30:00Z",
      "updated_at": "2023-06-15T08:30:00Z"
    },
    {
      "id": "asst_0987654321",
      "name": "营销助手",
      "description": "帮助创建营销内容和分析活动效果",
      "role": "marketing",
      "status": "active",
      "created_at": "2023-06-10T14:20:00Z",
      "updated_at": "2023-06-12T09:15:00Z"
    }
  ],
  "total": 2,
  "limit": 20,
  "offset": 0
}</code></pre>
            </div>
        </div>
        
        <div class="endpoint">
            <div class="endpoint-header">
                <span class="http-method http-post">POST</span>
                <span class="endpoint-path">/assistants</span>
            </div>
            <div class="endpoint-content">
                <h3>创建新助手</h3>
                <p>创建一个新的AI助手。</p>
                
                <h4>请求参数</h4>
                <div class="docs-table-container">
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>参数</th>
                                <th>类型</th>
                                <th>必填</th>
                                <th>描述</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>name</td>
                                <td>string</td>
                                <td>是</td>
                                <td>助手名称，最多64个字符</td>
                            </tr>
                            <tr>
                                <td>description</td>
                                <td>string</td>
                                <td>否</td>
                                <td>助手描述，最多512个字符</td>
                            </tr>
                            <tr>
                                <td>role</td>
                                <td>string</td>
                                <td>是</td>
                                <td>预定义角色或"custom"</td>
                            </tr>
                            <tr>
                                <td>style</td>
                                <td>string</td>
                                <td>否</td>
                                <td>对话风格：professional, friendly, technical等</td>
                            </tr>
                            <tr>
                                <td>knowledge_base</td>
                                <td>array</td>
                                <td>否</td>
                                <td>基础知识领域ID列表</td>
                            </tr>
                            <tr>
                                <td>custom_instructions</td>
                                <td>string</td>
                                <td>否</td>
                                <td>自定义指令，引导助手的行为</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <h4>响应示例</h4>
                <pre><code class="language-json">{
  "id": "asst_2468135790",
  "name": "技术支持助手",
  "description": "解决用户技术问题和故障排查",
  "role": "tech_support",
  "style": "technical",
  "status": "training",
  "knowledge_base": ["technical_documentation", "troubleshooting"],
  "custom_instructions": "提供准确的技术信息，必要时引导用户执行故障排除步骤。",
  "created_at": "2023-07-01T10:00:00Z",
  "updated_at": "2023-07-01T10:00:00Z"
}</code></pre>
            </div>
        </div>
    </section>
    
    <!-- 更多端点部分(省略) -->
</div>

<!-- 页面内导航 -->
<div class="docs-toc">
    <div class="docs-toc-header">
        <h3>目录</h3>
        <button class="docs-toc-toggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    <ul class="docs-toc-list">
        <li><a href="#api-introduction">API参考概述</a></li>
        <li><a href="#authentication">认证与授权</a></li>
        <li><a href="#base-url">基本URL</a></li>
        <li><a href="#rate-limits">访问限制</a></li>
        <li><a href="#error-handling">错误处理</a></li>
        <li><a href="#endpoints">API端点</a>
            <ul>
                <li><a href="#assistants">AI助手端点</a></li>
                <li><a href="#chat">聊天接口</a></li>
                <li><a href="#knowledge">知识库</a></li>
                <li><a href="#analytics">分析统计</a></li>
            </ul>
        </li>
    </ul>
</div>

<script>
// 页面内导航逻辑
document.addEventListener('DOMContentLoaded', function() {
    const tocToggle = document.querySelector('.docs-toc-toggle');
    const tocList = document.querySelector('.docs-toc-list');
    
    if (tocToggle && tocList) {
        tocToggle.addEventListener('click', function() {
            tocList.classList.toggle('show');
        });
    }
    
    // API端点卡片点击事件
    const apiCards = document.querySelectorAll('.api-endpoint-card');
    apiCards.forEach(card => {
        card.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                // 滚动到目标元素
                targetElement.scrollIntoView({ behavior: 'smooth' });
                
                // 高亮显示目标元素
                targetElement.classList.add('highlight');
                setTimeout(() => {
                    targetElement.classList.remove('highlight');
                }, 2000);
            }
        });
    });
});
</script>

<style>
/* API参考文档特定样式 */
.http-method {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: bold;
    font-size: 0.9em;
    color: white;
}

.http-get {
    background-color: #61affe;
}

.http-post {
    background-color: #49cc90;
}

.http-put {
    background-color: #fca130;
}

.http-delete {
    background-color: #f93e3e;
}

.endpoint {
    margin-bottom: 30px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

.endpoint-header {
    background-color: rgba(0, 0, 0, 0.03);
    padding: 15px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
}

.endpoint-path {
    font-family: monospace;
    font-size: 1.1em;
    margin-left: 10px;
}

.endpoint-content {
    padding: 20px;
}

.api-endpoint-card {
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    align-items: center;
}

.api-endpoint-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.docs-card-arrow {
    margin-left: auto;
    color: var(--primary-color);
    opacity: 0.5;
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.api-endpoint-card:hover .docs-card-arrow {
    opacity: 1;
    transform: translateX(5px);
}

.api-section {
    transition: background-color 0.5s ease;
}

.api-section.highlight {
    background-color: rgba(var(--primary-color-rgb), 0.1);
}
</style> 