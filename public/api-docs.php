<?php
/**
 * AlingAi Pro - API文档页面
 * 
 * 提供API使用文档、示例和参考资料
 */

// 设置页面信息
$pageTitle = "API文档 - AlingAi Pro";
$pageDescription = "AlingAi Pro API文档、示例和参考资料";
$additionalCSS = [
    "/css/api-docs.css"
];
$additionalJS = [
    ["src" => "/js/api-docs.js", "defer" => true]
];

// 包含页面模板
require_once __DIR__ . "/templates/page.php";

// 渲染页面头部
renderPageHeader();
?>

<div class="api-docs-container">
    <div class="api-docs-sidebar">
        <div class="api-docs-logo">
            <img src="/assets/images/logo.svg" alt="AlingAi Pro API" class="logo-img">
            <span class="api-version">v1.0</span>
        </div>
        
        <div class="api-docs-search">
            <input type="text" id="apiSearch" placeholder="搜索API文档..." class="search-input">
            <i class="fas fa-search search-icon"></i>
        </div>
        
        <nav class="api-docs-nav">
            <ul>
                <li><a href="#introduction" class="active">介绍</a></li>
                <li><a href="#authentication">认证</a></li>
                <li><a href="#rate-limits">速率限制</a></li>
                <li>
                    <a href="#endpoints" class="has-submenu">API端点</a>
                    <ul class="submenu">
                        <li><a href="#chat-completions">聊天补全</a></li>
                        <li><a href="#completions">文本补全</a></li>
                        <li><a href="#embeddings">文本嵌入</a></li>
                        <li><a href="#models">模型</a></li>
                    </ul>
                </li>
                <li><a href="#errors">错误处理</a></li>
                <li><a href="#sdks">SDK</a></li>
                <li><a href="#examples">示例代码</a></li>
                <li><a href="#best-practices">最佳实践</a></li>
            </ul>
        </nav>
    </div>
    
    <div class="api-docs-content">
        <section id="introduction" class="api-section">
            <h1>AlingAi Pro API文档</h1>
            <p class="api-intro">
                欢迎使用AlingAi Pro API。我们的API允许开发者将AlingAi Pro的强大AI功能集成到自己的应用程序中。
                本文档提供了API的详细说明、示例和最佳实践。
            </p>
            
            <div class="api-cards">
                <div class="api-card">
                    <div class="card-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h3>快速开始</h3>
                    <p>只需几行代码即可开始使用AlingAi Pro API</p>
                    <a href="#quick-start" class="card-link">查看指南 <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="api-card">
                    <div class="card-icon">
                        <i class="fas fa-code"></i>
                    </div>
                    <h3>SDK</h3>
                    <p>使用我们的官方SDK简化API集成</p>
                    <a href="#sdks" class="card-link">浏览SDK <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="api-card">
                    <div class="card-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3>参考文档</h3>
                    <p>详细的API参考和示例</p>
                    <a href="#endpoints" class="card-link">查看文档 <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            
            <div id="quick-start" class="quick-start-section">
                <h2>快速开始</h2>
                <p>通过以下简单步骤开始使用AlingAi Pro API：</p>
                
                <div class="steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h3>创建账户</h3>
                            <p>如果您还没有AlingAi Pro账户，请先<a href="/register">注册</a>。</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h3>获取API密钥</h3>
                            <p>在<a href="/user/api-keys">API密钥</a>页面创建您的API密钥。</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h3>发送请求</h3>
                            <p>使用您的API密钥发送请求。</p>
                            <div class="code-block">
                                <pre><code>curl -X POST \\
  https://api.alingai.pro/v1/chat/completions \\
  -H "Content-Type: application/json" \\
  -H "Authorization: Bearer YOUR_API_KEY" \\
  -d "{
    \"model\": \"alingai-pro\",
    \"messages\": [
      {\"role\": \"user\", \"content\": \"你好，请介绍一下你自己。\"}
    ]
  }"</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <section id="authentication" class="api-section">
            <h2>认证</h2>
            <p>
                所有API请求都需要使用API密钥进行认证。您可以在<a href="/user/api-keys">API密钥</a>页面创建和管理您的API密钥。
            </p>
            
            <div class="info-box">
                <div class="info-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="info-content">
                    <p><strong>安全提示：</strong> 请妥善保管您的API密钥，不要在客户端代码中暴露它们。</p>
                </div>
            </div>
            
            <h3>认证方式</h3>
            <p>在所有API请求中，您需要在请求头中包含您的API密钥：</p>
            
            <div class="code-block">
                <pre><code>Authorization: Bearer YOUR_API_KEY</code></pre>
            </div>
        </section>
        
        <section id="rate-limits" class="api-section">
            <h2>速率限制</h2>
            <p>
                为了确保服务质量和公平使用，我们对API请求实施了速率限制。速率限制根据您的订阅计划而有所不同。
            </p>
            
            <table class="api-table">
                <thead>
                    <tr>
                        <th>计划</th>
                        <th>请求限制</th>
                        <th>Token限制</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>免费</td>
                        <td>100次/天</td>
                        <td>100,000/月</td>
                    </tr>
                    <tr>
                        <td>基础</td>
                        <td>500次/天</td>
                        <td>500,000/月</td>
                    </tr>
                    <tr>
                        <td>专业</td>
                        <td>2,000次/天</td>
                        <td>2,000,000/月</td>
                    </tr>
                    <tr>
                        <td>企业</td>
                        <td>10,000次/天</td>
                        <td>10,000,000/月</td>
                    </tr>
                </tbody>
            </table>
            
            <h3>速率限制响应</h3>
            <p>当您超过速率限制时，API将返回429状态码（Too Many Requests）。响应头中包含以下信息：</p>
            
            <div class="code-block">
                <pre><code>X-RateLimit-Limit: 100
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1619644800</code></pre>
            </div>
        </section>
        
        <section id="endpoints" class="api-section">
            <h2>API端点</h2>
            <p>
                AlingAi Pro API提供了多种端点，用于不同类型的AI任务。以下是主要端点的概述。
            </p>
            
            <section id="chat-completions" class="api-subsection">
                <h3>聊天补全</h3>
                <p>
                    聊天补全API允许您与AI进行多轮对话。这是最常用的API端点，适用于聊天机器人和对话应用。
                </p>
                
                <div class="endpoint-info">
                    <div class="endpoint-method">POST</div>
                    <div class="endpoint-url">/v1/chat/completions</div>
                </div>
                
                <h4>请求参数</h4>
                <table class="api-table">
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
                            <td>model</td>
                            <td>string</td>
                            <td>是</td>
                            <td>要使用的模型ID，例如"alingai-pro"</td>
                        </tr>
                        <tr>
                            <td>messages</td>
                            <td>array</td>
                            <td>是</td>
                            <td>对话消息数组，每条消息包含"role"和"content"</td>
                        </tr>
                        <tr>
                            <td>temperature</td>
                            <td>number</td>
                            <td>否</td>
                            <td>采样温度，介于0和2之间。较高的值会使输出更随机，较低的值会使输出更确定。默认为1</td>
                        </tr>
                        <tr>
                            <td>max_tokens</td>
                            <td>integer</td>
                            <td>否</td>
                            <td>生成的最大token数。默认为2048</td>
                        </tr>
                    </tbody>
                </table>
                
                <h4>示例请求</h4>
                <div class="code-block">
                    <pre><code>curl -X POST \\
  https://api.alingai.pro/v1/chat/completions \\
  -H "Content-Type: application/json" \\
  -H "Authorization: Bearer YOUR_API_KEY" \\
  -d "{
    \"model\": \"alingai-pro\",
    \"messages\": [
      {\"role\": \"system\", \"content\": \"你是一个有用的AI助手。\"},
      {\"role\": \"user\", \"content\": \"你好，请介绍一下你自己。\"}
    ],
    \"temperature\": 0.7,
    \"max_tokens\": 500
  }"</code></pre>
                </div>
                
                <h4>响应示例</h4>
                <div class="code-block">
                    <pre><code>{
  "id": "chatcmpl-123abc",
  "object": "chat.completion",
  "created": 1677858242,
  "model": "alingai-pro",
  "choices": [
    {
      "message": {
        "role": "assistant",
        "content": "你好！我是AlingAi Pro，一个由人工智能驱动的助手。我可以帮助回答问题、提供信息、协助创作内容，以及完成各种任务。我的目标是提供有用、准确和有帮助的回应。有什么我可以帮助你的吗？"
      },
      "index": 0,
      "finish_reason": "stop"
    }
  ],
  "usage": {
    "prompt_tokens": 29,
    "completion_tokens": 85,
    "total_tokens": 114
  }
}</code></pre>
                </div>
            </section>
            
            <section id="completions" class="api-subsection">
                <h3>文本补全</h3>
                <p>
                    文本补全API允许您生成或操作文本。这适用于文本生成、摘要和其他非对话任务。
                </p>
                
                <div class="endpoint-info">
                    <div class="endpoint-method">POST</div>
                    <div class="endpoint-url">/v1/completions</div>
                </div>
                
                <h4>请求参数</h4>
                <table class="api-table">
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
                            <td>model</td>
                            <td>string</td>
                            <td>是</td>
                            <td>要使用的模型ID</td>
                        </tr>
                        <tr>
                            <td>prompt</td>
                            <td>string</td>
                            <td>是</td>
                            <td>用于生成补全的提示</td>
                        </tr>
                        <tr>
                            <td>temperature</td>
                            <td>number</td>
                            <td>否</td>
                            <td>采样温度，介于0和2之间。默认为1</td>
                        </tr>
                        <tr>
                            <td>max_tokens</td>
                            <td>integer</td>
                            <td>否</td>
                            <td>生成的最大token数。默认为2048</td>
                        </tr>
                    </tbody>
                </table>
            </section>
            
            <section id="embeddings" class="api-subsection">
                <h3>文本嵌入</h3>
                <p>
                    文本嵌入API将文本转换为数值向量表示，可用于语义搜索、聚类和其他机器学习任务。
                </p>
                
                <div class="endpoint-info">
                    <div class="endpoint-method">POST</div>
                    <div class="endpoint-url">/v1/embeddings</div>
                </div>
                
                <h4>请求参数</h4>
                <table class="api-table">
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
                            <td>model</td>
                            <td>string</td>
                            <td>是</td>
                            <td>要使用的嵌入模型ID</td>
                        </tr>
                        <tr>
                            <td>input</td>
                            <td>string或array</td>
                            <td>是</td>
                            <td>要嵌入的文本或文本数组</td>
                        </tr>
                    </tbody>
                </table>
            </section>
            
            <section id="models" class="api-subsection">
                <h3>模型</h3>
                <p>
                    模型API允许您查看可用的模型列表及其详细信息。
                </p>
                
                <div class="endpoint-info">
                    <div class="endpoint-method">GET</div>
                    <div class="endpoint-url">/v1/models</div>
                </div>
            </section>
        </section>
        
        <section id="errors" class="api-section">
            <h2>错误处理</h2>
            <p>
                AlingAi Pro API使用标准HTTP状态码来指示请求的成功或失败。以下是常见错误码及其含义：
            </p>
            
            <table class="api-table">
                <thead>
                    <tr>
                        <th>状态码</th>
                        <th>描述</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>400</td>
                        <td>错误请求 - 请求参数有误</td>
                    </tr>
                    <tr>
                        <td>401</td>
                        <td>未授权 - API密钥无效</td>
                    </tr>
                    <tr>
                        <td>403</td>
                        <td>禁止访问 - 没有权限访问请求的资源</td>
                    </tr>
                    <tr>
                        <td>404</td>
                        <td>未找到 - 请求的资源不存在</td>
                    </tr>
                    <tr>
                        <td>429</td>
                        <td>请求过多 - 超出速率限制</td>
                    </tr>
                    <tr>
                        <td>500</td>
                        <td>服务器错误 - 服务器内部错误</td>
                    </tr>
                </tbody>
            </table>
            
            <h3>错误响应格式</h3>
            <p>错误响应的JSON格式如下：</p>
            
            <div class="code-block">
                <pre><code>{
  "error": {
    "code": "invalid_api_key",
    "message": "API密钥无效或已过期",
    "param": null,
    "type": "authentication_error"
  }
}</code></pre>
            </div>
        </section>
        
        <section id="sdks" class="api-section">
            <h2>SDK</h2>
            <p>
                我们提供多种编程语言的官方SDK，以简化API集成。
            </p>
            
            <div class="sdk-cards">
                <div class="sdk-card">
                    <div class="sdk-icon">
                        <i class="fab fa-python"></i>
                    </div>
                    <h3>Python</h3>
                    <div class="code-block">
                        <pre><code>pip install alingai</code></pre>
                    </div>
                    <a href="/docs/sdk/python" class="sdk-link">文档 <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="sdk-card">
                    <div class="sdk-icon">
                        <i class="fab fa-js"></i>
                    </div>
                    <h3>JavaScript</h3>
                    <div class="code-block">
                        <pre><code>npm install alingai</code></pre>
                    </div>
                    <a href="/docs/sdk/javascript" class="sdk-link">文档 <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="sdk-card">
                    <div class="sdk-icon">
                        <i class="fab fa-php"></i>
                    </div>
                    <h3>PHP</h3>
                    <div class="code-block">
                        <pre><code>composer require alingai/alingai-php</code></pre>
                    </div>
                    <a href="/docs/sdk/php" class="sdk-link">文档 <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="sdk-card">
                    <div class="sdk-icon">
                        <i class="fab fa-java"></i>
                    </div>
                    <h3>Java</h3>
                    <div class="code-block">
                        <pre><code>// Maven
&lt;dependency&gt;
  &lt;groupId&gt;pro.alingai&lt;/groupId&gt;
  &lt;artifactId&gt;alingai-java&lt;/artifactId&gt;
  &lt;version&gt;1.0.0&lt;/version&gt;
&lt;/dependency&gt;</code></pre>
                    </div>
                    <a href="/docs/sdk/java" class="sdk-link">文档 <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </section>
        
        <section id="examples" class="api-section">
            <h2>示例代码</h2>
            <p>
                以下是使用AlingAi Pro API的一些常见用例示例。
            </p>
            
            <div class="code-tabs">
                <div class="tabs">
                    <button class="tab-btn active" data-lang="python">Python</button>
                    <button class="tab-btn" data-lang="javascript">JavaScript</button>
                    <button class="tab-btn" data-lang="php">PHP</button>
                    <button class="tab-btn" data-lang="curl">cURL</button>
                </div>
                
                <div class="tab-content active" data-lang="python">
                    <div class="code-block">
                        <pre><code>import alingai

# 设置API密钥
client = alingai.Client(api_key="YOUR_API_KEY")

# 聊天补全
chat_completion = client.chat.completions.create(
    model="alingai-pro",
    messages=[
        {"role": "system", "content": "你是一个有用的AI助手。"},
        {"role": "user", "content": "写一篇关于人工智能的短文。"}
    ]
)

print(chat_completion.choices[0].message.content)</code></pre>
                    </div>
                </div>
                
                <div class="tab-content" data-lang="javascript">
                    <div class="code-block">
                        <pre><code>import { AlingAi } from "alingai";

// 设置API密钥
const client = new AlingAi({
  apiKey: "YOUR_API_KEY",
});

async function main() {
  // 聊天补全
  const chatCompletion = await client.chat.completions.create({
    model: "alingai-pro",
    messages: [
      { role: "system", content: "你是一个有用的AI助手。" },
      { role: "user", content: "写一篇关于人工智能的短文。" }
    ],
  });

  console.log(chatCompletion.choices[0].message.content);
}

main();</code></pre>
                    </div>
                </div>
                
                <div class="tab-content" data-lang="php">
                    <div class="code-block">
                        <pre><code>require "vendor/autoload.php";

// 设置API密钥
$client = AlingAi\Client::create("YOUR_API_KEY");

// 聊天补全
$result = $client->chat()->create([
    "model" => "alingai-pro",
    "messages" => [
        ["role" => "system", "content" => "你是一个有用的AI助手。"],
        ["role" => "user", "content" => "写一篇关于人工智能的短文。"]
    ]
]);

echo $result->choices[0]->message->content;</code></pre>
                    </div>
                </div>
                
                <div class="tab-content" data-lang="curl">
                    <div class="code-block">
                        <pre><code>curl -X POST \\
  https://api.alingai.pro/v1/chat/completions \\
  -H "Content-Type: application/json" \\
  -H "Authorization: Bearer YOUR_API_KEY" \\
  -d "{
    \"model\": \"alingai-pro\",
    \"messages\": [
      {\"role\": \"system\", \"content\": \"你是一个有用的AI助手。\"},
      {\"role\": \"user\", \"content\": \"写一篇关于人工智能的短文。\"}
    ]
  }"</code></pre>
                    </div>
                </div>
            </div>
        </section>
        
        <section id="best-practices" class="api-section">
            <h2>最佳实践</h2>
            <p>
                以下是使用AlingAi Pro API的一些最佳实践，可以帮助您获得最佳结果并优化成本。
            </p>
            
            <div class="best-practices">
                <div class="practice-item">
                    <div class="practice-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <div class="practice-content">
                        <h3>提供清晰的指令</h3>
                        <p>在提示中提供清晰、具体的指令，以获得更准确的结果。</p>
                    </div>
                </div>
                
                <div class="practice-item">
                    <div class="practice-icon">
                        <i class="fas fa-thermometer-half"></i>
                    </div>
                    <div class="practice-content">
                        <h3>调整温度参数</h3>
                        <p>对于需要创造性输出的任务，使用较高的温度值；对于需要准确、一致回答的任务，使用较低的温度值。</p>
                    </div>
                </div>
                
                <div class="practice-item">
                    <div class="practice-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <div class="practice-content">
                        <h3>限制token使用</h3>
                        <p>设置合理的max_tokens值，避免生成不必要的内容，从而优化成本和响应时间。</p>
                    </div>
                </div>
                
                <div class="practice-item">
                    <div class="practice-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="practice-content">
                        <h3>实现缓存</h3>
                        <p>对于频繁请求的相同或相似查询，实现客户端缓存以减少API调用。</p>
                    </div>
                </div>
                
                <div class="practice-item">
                    <div class="practice-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="practice-content">
                        <h3>保护API密钥</h3>
                        <p>永远不要在客户端代码中暴露API密钥，始终从服务器端发送API请求。</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>


<style>
    /* API文档页面特定样式 */
    .api-docs-container {
        display: flex;
        min-height: calc(100vh - var(--header-height) - var(--footer-height));
    }
    
    .api-docs-sidebar {
        width: 280px;
        background: var(--glass-background);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-right: 1px solid var(--glass-border);
        padding: var(--spacing-md);
        position: sticky;
        top: var(--header-height);
        height: calc(100vh - var(--header-height));
        overflow-y: auto;
    }
    
    .api-docs-logo {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: var(--spacing-md);
        padding-bottom: var(--spacing-md);
        border-bottom: 1px solid var(--glass-border);
    }
    
    .api-docs-logo .logo-img {
        height: 32px;
    }
    
    .api-version {
        font-size: 0.8rem;
        padding: 2px 6px;
        background-color: var(--accent-color);
        color: white;
        border-radius: 12px;
    }
    
    .api-docs-search {
        position: relative;
        margin-bottom: var(--spacing-md);
    }
    
    .search-input {
        width: 100%;
        padding: 8px 12px;
        padding-left: 32px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
        color: var(--text-color);
    }
    
    .search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-color-light);
    }
    
    .api-docs-nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .api-docs-nav li {
        margin-bottom: 2px;
    }
    
    .api-docs-nav a {
        display: block;
        padding: 8px 12px;
        color: var(--text-color);
        text-decoration: none;
        border-radius: var(--border-radius-sm);
        transition: all var(--transition-fast);
    }
    
    .api-docs-nav a:hover {
        background: rgba(255, 255, 255, 0.1);
    }
    
    .api-docs-nav a.active {
        background: var(--accent-color);
        color: white;
    }
    
    .api-docs-nav a.has-submenu {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .api-docs-nav a.has-submenu::after {
        content: "\\f078";
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        font-size: 0.8rem;
    }
    
    .api-docs-nav .submenu {
        padding-left: var(--spacing-md);
        margin-top: 2px;
    }
    
    .api-docs-content {
        flex: 1;
        padding: var(--spacing-lg) var(--spacing-xl);
        max-width: 1000px;
        margin: 0 auto;
    }
    
    .api-section {
        margin-bottom: var(--spacing-xl);
        scroll-margin-top: var(--header-height);
    }
    
    .api-subsection {
        margin-top: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
        padding-left: var(--spacing-md);
        border-left: 2px solid var(--accent-color);
    }
    
    .api-intro {
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: var(--spacing-lg);
        color: var(--text-color-light);
    }
    
    .api-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-lg);
    }
    
    .api-card {
        background: var(--glass-background);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
        transition: transform var(--transition-fast);
    }
    
    .api-card:hover {
        transform: translateY(-5px);
    }
    
    .card-icon {
        font-size: 2rem;
        color: var(--accent-color);
        margin-bottom: var(--spacing-sm);
    }
    
    .api-card h3 {
        margin-bottom: var(--spacing-xs);
        color: var(--secondary-color);
    }
    
    .api-card p {
        margin-bottom: var(--spacing-md);
        color: var(--text-color-light);
        font-size: 0.9rem;
    }
    
    .card-link {
        color: var(--accent-color);
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
    }
    
    .card-link i {
        margin-left: 5px;
        font-size: 0.8rem;
    }
    
    .quick-start-section {
        margin-top: var(--spacing-xl);
    }
    
    .steps {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-md);
        margin-top: var(--spacing-md);
    }
    
    .step {
        display: flex;
        gap: var(--spacing-md);
    }
    
    .step-number {
        width: 32px;
        height: 32px;
        background-color: var(--accent-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        flex-shrink: 0;
    }
    
    .step-content {
        flex: 1;
    }
    
    .step-content h3 {
        margin-bottom: var(--spacing-xs);
        color: var(--secondary-color);
    }
    
    .code-block {
        background: var(--surface-color);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
        margin: var(--spacing-md) 0;
        overflow-x: auto;
    }
    
    .code-block pre {
        margin: 0;
    }
    
    .code-block code {
        font-family: monospace;
        color: var(--text-color);
    }
    
    .info-box {
        display: flex;
        gap: var(--spacing-md);
        background: rgba(10, 132, 255, 0.1);
        border-left: 3px solid var(--accent-color);
        padding: var(--spacing-md);
        border-radius: var(--border-radius-md);
        margin: var(--spacing-md) 0;
    }
    
    .info-icon {
        color: var(--accent-color);
        font-size: 1.5rem;
    }
    
    .api-table {
        width: 100%;
        border-collapse: collapse;
        margin: var(--spacing-md) 0;
    }
    
    .api-table th,
    .api-table td {
        padding: var(--spacing-sm) var(--spacing-md);
        text-align: left;
        border-bottom: 1px solid var(--glass-border);
    }
    
    .api-table th {
        font-weight: 600;
        color: var(--secondary-color);
    }
    
    .endpoint-info {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        margin: var(--spacing-md) 0;
    }
    
    .endpoint-method {
        background-color: var(--accent-color);
        color: white;
        padding: 4px 8px;
        border-radius: var(--border-radius-sm);
        font-weight: 500;
        font-size: 0.9rem;
    }
    
    .endpoint-url {
        font-family: monospace;
        background: var(--surface-color);
        padding: 4px 8px;
        border-radius: var(--border-radius-sm);
    }
    
    .sdk-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: var(--spacing-md);
    }
    
    .sdk-card {
        background: var(--glass-background);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
    }
    
    .sdk-icon {
        font-size: 2rem;
        color: var(--accent-color);
        margin-bottom: var(--spacing-sm);
    }
    
    .sdk-link {
        display: inline-flex;
        align-items: center;
        color: var(--accent-color);
        text-decoration: none;
        font-weight: 500;
        margin-top: var(--spacing-sm);
    }
    
    .sdk-link i {
        margin-left: 5px;
        font-size: 0.8rem;
    }
    
    .code-tabs {
        margin: var(--spacing-md) 0;
    }
    
    .tabs {
        display: flex;
        border-bottom: 1px solid var(--glass-border);
        margin-bottom: var(--spacing-md);
    }
    
    .tab-btn {
        background: none;
        border: none;
        padding: var(--spacing-sm) var(--spacing-md);
        color: var(--text-color-light);
        cursor: pointer;
        position: relative;
        transition: color var(--transition-fast);
    }
    
    .tab-btn:hover {
        color: var(--text-color);
    }
    
    .tab-btn.active {
        color: var(--accent-color);
    }
    
    .tab-btn.active::after {
        content: "";
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: var(--accent-color);
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
    }
    
    .best-practices {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: var(--spacing-lg);
    }
    
    .practice-item {
        display: flex;
        gap: var(--spacing-md);
    }
    
    .practice-icon {
        font-size: 1.5rem;
        color: var(--accent-color);
        flex-shrink: 0;
    }
    
    .practice-content h3 {
        margin-bottom: var(--spacing-xs);
        color: var(--secondary-color);
    }
    
    .practice-content p {
        color: var(--text-color-light);
        font-size: 0.9rem;
    }
    
    @media (max-width: 992px) {
        .api-docs-container {
            flex-direction: column;
        }
        
        .api-docs-sidebar {
            width: 100%;
            height: auto;
            position: relative;
            top: 0;
            border-right: none;
            border-bottom: 1px solid var(--glass-border);
        }
        
        .api-docs-content {
            padding: var(--spacing-md);
        }
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 侧边栏导航滚动监听
        const sections = document.querySelectorAll(".api-section, .api-subsection");
        const navLinks = document.querySelectorAll(".api-docs-nav a");
        
        // 平滑滚动到锚点
        navLinks.forEach(link => {
            link.addEventListener("click", function(e) {
                const targetId = this.getAttribute("href");
                
                if (targetId.startsWith("#")) {
                    e.preventDefault();
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 80,
                            behavior: "smooth"
                        });
                        
                        // 更新URL但不滚动
                        history.pushState(null, null, targetId);
                        
                        // 更新活动链接
                        navLinks.forEach(link => link.classList.remove("active"));
                        this.classList.add("active");
                    }
                }
            });
        });
        
        // 滚动时更新活动链接
        window.addEventListener("scroll", function() {
            let current = "";
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop - 100;
                const sectionHeight = section.offsetHeight;
                
                if (window.pageYOffset >= sectionTop && window.pageYOffset < sectionTop + sectionHeight) {
                    current = "#" + section.getAttribute("id");
                }
            });
            
            navLinks.forEach(link => {
                link.classList.remove("active");
                if (link.getAttribute("href") === current) {
                    link.classList.add("active");
                }
            });
        });
        
        // 代码选项卡切换
        const tabBtns = document.querySelectorAll(".tab-btn");
        const tabContents = document.querySelectorAll(".tab-content");
        
        tabBtns.forEach(btn => {
            btn.addEventListener("click", function() {
                const lang = this.getAttribute("data-lang");
                
                // 更新按钮状态
                tabBtns.forEach(btn => btn.classList.remove("active"));
                this.classList.add("active");
                
                // 更新内容显示
                tabContents.forEach(content => {
                    content.classList.remove("active");
                    if (content.getAttribute("data-lang") === lang) {
                        content.classList.add("active");
                    }
                });
            });
        });
        
        // 搜索功能
        const searchInput = document.getElementById("apiSearch");
        
        if (searchInput) {
            searchInput.addEventListener("input", function() {
                const query = this.value.toLowerCase();
                
                if (query.length < 2) {
                    // 重置显示
                    sections.forEach(section => {
                        section.style.display = "block";
                    });
                    return;
                }
                
                // 搜索标题和内容
                sections.forEach(section => {
                    const title = section.querySelector("h1, h2, h3, h4")?.textContent.toLowerCase() || "";
                    const content = section.textContent.toLowerCase();
                    
                    if (title.includes(query) || content.includes(query)) {
                        section.style.display = "block";
                    } else {
                        section.style.display = "none";
                    }
                });
            });
        }
        
        // 处理页面加载时的锚点
        if (window.location.hash) {
            const targetElement = document.querySelector(window.location.hash);
            if (targetElement) {
                setTimeout(() => {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: "smooth"
                    });
                    
                    // 更新活动链接
                    navLinks.forEach(link => {
                        link.classList.remove("active");
                        if (link.getAttribute("href") === window.location.hash) {
                            link.classList.add("active");
                        }
                    });
                }, 100);
            }
        }
    });
</script>

<?php
// 渲染页面页脚
renderPageFooter();
?>
