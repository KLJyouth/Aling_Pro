<!-- 快速开始页面 -->
<div class="docs-article">
    <section class="docs-section">
        <h2 id="introduction">AlingAi Pro 快速开始</h2>
        <p class="lead">本指南将帮助您快速上手并开始使用AlingAi Pro的各项功能。无论您是开发人员、业务分析师还是企业决策者，都能通过本指南迅速了解如何利用AlingAi Pro提升工作效率。</p>
        
        <div class="docs-alert docs-alert-info">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>提示：</strong> 如果您是首次使用AlingAi Pro，建议先阅读<a href="?page=overview">概述</a>页面，了解系统的基本概念和架构。
            </div>
        </div>
    </section>
    
    <section class="docs-section">
        <h2 id="prerequisites">准备工作</h2>
        <p>在开始使用AlingAi Pro之前，请确保您已准备好以下条件：</p>
        
        <ul>
            <li>已注册AlingAi Pro账户（<a href="/register">注册地址</a>）</li>
            <li>拥有适当的访问权限（基础版、专业版或企业版）</li>
            <li>现代浏览器，如Chrome、Firefox、Safari或Edge的最新版本</li>
            <li>稳定的网络连接</li>
        </ul>
        
        <h3 id="system-requirements">系统要求</h3>
        <div class="docs-table-container">
            <table class="docs-table">
                <thead>
                    <tr>
                        <th>需求类型</th>
                        <th>最低配置</th>
                        <th>推荐配置</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>操作系统</td>
                        <td>Windows 10, macOS 10.15, Ubuntu 18.04</td>
                        <td>Windows 11, macOS 13.0+, Ubuntu 22.04+</td>
                    </tr>
                    <tr>
                        <td>CPU</td>
                        <td>双核处理器</td>
                        <td>四核或更高</td>
                    </tr>
                    <tr>
                        <td>内存</td>
                        <td>4 GB RAM</td>
                        <td>8 GB RAM或更高</td>
                    </tr>
                    <tr>
                        <td>存储</td>
                        <td>10 GB可用空间</td>
                        <td>20 GB可用空间</td>
                    </tr>
                    <tr>
                        <td>网络</td>
                        <td>2 Mbps连接</td>
                        <td>10+ Mbps连接</td>
                    </tr>
                    <tr>
                        <td>浏览器</td>
                        <td>Chrome 90+, Firefox 90+, Safari 14+, Edge 90+</td>
                        <td>最新版浏览器</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
    
    <section class="docs-section">
        <h2 id="login">登录系统</h2>
        <p>完成注册后，您可以通过以下步骤登录AlingAi Pro系统：</p>
        
        <ol class="docs-steps">
            <li>
                <h4>访问登录页面</h4>
                <p>打开浏览器，访问<a href="/login">AlingAi Pro登录页面</a>。</p>
            </li>
            <li>
                <h4>输入凭据</h4>
                <p>输入您的注册邮箱和密码。</p>
            </li>
            <li>
                <h4>双因素验证（如已启用）</h4>
                <p>如果您已启用双因素认证，系统会要求您输入验证码。</p>
            </li>
            <li>
                <h4>进入控制台</h4>
                <p>成功登录后，您将进入AlingAi Pro的主控制台。</p>
            </li>
        </ol>
        
        <div class="docs-tip">
            <h4>忘记密码？</h4>
            <p>如果您忘记了密码，可以在登录页面点击"忘记密码"链接，系统将发送密码重置邮件到您的注册邮箱。</p>
        </div>
    </section>
    
    <section class="docs-section">
        <h2 id="dashboard">了解控制台</h2>
        <p>AlingAi Pro控制台是您管理和使用所有功能的中心。控制台分为以下几个主要区域：</p>
        
        <div class="docs-image">
            <img src="/assets/images/docs/dashboard-overview.jpg" alt="AlingAi Pro控制台概述">
            <p class="docs-caption">AlingAi Pro控制台界面</p>
        </div>
        
        <h3>主要控制台功能区域：</h3>
        <ul>
            <li><strong>侧边导航栏</strong> - 访问系统各个主要功能模块</li>
            <li><strong>顶部工具栏</strong> - 包含搜索、通知、用户设置和帮助</li>
            <li><strong>数据仪表盘</strong> - 显示关键性能指标和系统状态</li>
            <li><strong>快速操作</strong> - 常用功能的快捷入口</li>
            <li><strong>最近活动</strong> - 展示最近的操作和系统活动</li>
        </ul>
    </section>
    
    <section class="docs-section">
        <h2 id="first-ai-assistant">创建您的第一个AI助手</h2>
        <p>AlingAi Pro的核心功能之一是创建自定义AI助手。按照以下步骤创建您的第一个AI助手：</p>
        
        <div class="docs-code-tabs">
            <div class="tabs">
                <button class="tab active" data-target="web-interface">使用Web界面</button>
                <button class="tab" data-target="api">使用API</button>
            </div>
            <div class="tab-content active" id="web-interface">
                <ol>
                    <li>在控制台侧边栏中，点击"<strong>AI助手</strong>"选项。</li>
                    <li>点击页面右上角的"<strong>创建新助手</strong>"按钮。</li>
                    <li>在创建表单中填写以下信息：
                        <ul>
                            <li><strong>助手名称</strong>：为您的助手起一个描述性名称</li>
                            <li><strong>助手描述</strong>：简要描述助手的用途和功能</li>
                            <li><strong>助手角色</strong>：从下拉菜单中选择预定义角色，或选择"自定义"</li>
                            <li><strong>对话风格</strong>：选择适合您业务场景的沟通风格</li>
                            <li><strong>基础知识库</strong>：选择助手应该拥有的基础知识领域</li>
                        </ul>
                    </li>
                    <li>点击"<strong>保存并继续</strong>"，进入知识库设置。</li>
                    <li>上传相关文档或输入专业知识条目，帮助AI更好地理解您的业务领域。</li>
                    <li>点击"<strong>完成创建</strong>"，您的AI助手就准备好了！</li>
                </ol>
            </div>
            <div class="tab-content" id="api">
                <p>通过API创建AI助手，您需要发送POST请求到以下端点：</p>
                <pre><code class="language-bash">curl -X POST https://api.alingai.pro/v1/assistants \
-H "Authorization: Bearer YOUR_API_KEY" \
-H "Content-Type: application/json" \
-d '{
  "name": "客户服务助手",
  "description": "处理客户查询和问题排解",
  "role": "customer_support",
  "style": "professional",
  "knowledge_base": ["customer_service", "product_info"],
  "custom_instructions": "优先解决客户问题，保持专业友好的语气。"
}'</code></pre>
                <p>成功创建后，API将返回助手ID和详细信息。</p>
            </div>
        </div>
    </section>
    
    <section class="docs-section">
        <h2 id="integrate-assistant">集成AI助手到您的应用</h2>
        <p>创建好AI助手后，您可以将它集成到您的网站、应用或其他系统中。下面是几种常见的集成方法：</p>
        
        <h3>Web小部件集成</h3>
        <p>最简单的方式是使用我们的Web小部件，只需添加几行代码到您的网站：</p>
        
        <pre><code class="language-html">&lt;!-- 将此代码添加到您网站的&lt;head&gt;部分 --&gt;
&lt;script src="https://cdn.alingai.pro/widget.js"&gt;&lt;/script&gt;

&lt;!-- 将此代码添加到您网站的&lt;body&gt;部分 --&gt;
&lt;script&gt;
  AlingAi.init({
    assistantId: 'YOUR_ASSISTANT_ID',
    position: 'bottom-right',
    theme: 'light',
    welcomeMessage: '您好！有什么我可以帮您的吗？'
  });
&lt;/script&gt;</code></pre>
        
        <h3>API集成</h3>
        <p>对于需要更深度定制的应用，您可以使用我们的RESTful API：</p>
        
        <pre><code class="language-javascript">// 向AI助手发送消息并获取回复
async function sendMessageToAssistant(message) {
  const response = await fetch('https://api.alingai.pro/v1/chat', {
    method: 'POST',
    headers: {
      'Authorization': 'Bearer YOUR_API_KEY',
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      assistant_id: 'YOUR_ASSISTANT_ID',
      message: message,
      conversation_id: 'optional_conversation_id' // 用于跟踪对话上下文
    })
  });
  
  return await response.json();
}</code></pre>
        
        <h3>移动SDK集成</h3>
        <p>对于移动应用，我们提供了iOS和Android SDK：</p>
        
        <div class="docs-tabs">
            <div class="tabs">
                <button class="tab active" data-target="android">Android</button>
                <button class="tab" data-target="ios">iOS</button>
            </div>
            <div class="tab-content active" id="android">
                <p>添加依赖到您的<code>build.gradle</code>文件：</p>
                <pre><code class="language-groovy">dependencies {
    implementation 'com.alingai:assistant-sdk:6.0.0'
}</code></pre>

                <p>初始化并使用SDK：</p>
                <pre><code class="language-java">// 在应用启动时初始化
AlingAiAssistant.init(this, "YOUR_API_KEY");

// 创建助手实例
AlingAiAssistant assistant = new AlingAiAssistant.Builder()
    .setAssistantId("YOUR_ASSISTANT_ID")
    .build();

// 发送消息并接收回复
assistant.sendMessage("您好，我需要帮助", new AssistantCallback() {
    @Override
    public void onResponse(AssistantResponse response) {
        String reply = response.getMessage();
        // 处理助手回复
    }
    
    @Override
    public void onError(Exception e) {
        // 处理错误
    }
});</code></pre>
            </div>
            <div class="tab-content" id="ios">
                <p>使用CocoaPods添加SDK：</p>
                <pre><code class="language-ruby">pod 'AlingAiAssistant', '~> 6.0.0'</code></pre>

                <p>初始化并使用SDK：</p>
                <pre><code class="language-swift">// 在应用启动时初始化
AlingAiAssistant.initialize(withApiKey: "YOUR_API_KEY")

// 创建助手实例
let assistant = AlingAiAssistant(assistantId: "YOUR_ASSISTANT_ID")

// 发送消息并接收回复
assistant.sendMessage("您好，我需要帮助") { result in
    switch result {
    case .success(let response):
        let reply = response.message
        // 处理助手回复
    case .failure(let error):
        // 处理错误
    }
}</code></pre>
            </div>
        </div>
    </section>
    
    <section class="docs-section">
        <h2 id="next-steps">后续步骤</h2>
        <p>现在您已经成功创建并集成了第一个AI助手，可以继续探索AlingAi Pro的其他功能：</p>
        
        <div class="docs-cards">
            <a href="?page=api-reference" class="docs-card">
                <div class="docs-card-icon">
                    <i class="fas fa-code"></i>
                </div>
                <div class="docs-card-content">
                    <h3>API参考</h3>
                    <p>深入了解所有可用的API端点和参数</p>
                </div>
            </a>
            
            <a href="?page=sdk" class="docs-card">
                <div class="docs-card-icon">
                    <i class="fas fa-puzzle-piece"></i>
                </div>
                <div class="docs-card-content">
                    <h3>SDK文档</h3>
                    <p>使用我们的开发工具包进行更深入的集成</p>
                </div>
            </a>
            
            <a href="?page=tutorials" class="docs-card">
                <div class="docs-card-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="docs-card-content">
                    <h3>教程</h3>
                    <p>按照分步指南学习更高级的使用场景</p>
                </div>
            </a>
            
            <a href="?page=examples" class="docs-card">
                <div class="docs-card-icon">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <div class="docs-card-content">
                    <h3>示例代码</h3>
                    <p>浏览完整的代码示例，加速您的开发</p>
                </div>
            </a>
        </div>
    </section>
    
    <section class="docs-section">
        <h2 id="support">获取支持</h2>
        <p>如果您在使用过程中遇到任何问题或需要进一步的帮助，可以通过以下渠道联系我们：</p>
        
        <ul>
            <li><a href="/contact">联系支持团队</a></li>
            <li>发送邮件至 <a href="mailto:support@alingai.pro">support@alingai.pro</a></li>
            <li>查阅我们的<a href="?page=faq">常见问题解答</a></li>
            <li>访问我们的<a href="https://community.alingai.pro" target="_blank">开发者社区</a>提问</li>
        </ul>
        
        <div class="docs-alert docs-alert-success">
            <i class="fas fa-lightbulb"></i>
            <div>
                <strong>提示：</strong> 企业版用户可以联系您的专属客户经理获取优先支持服务。
            </div>
        </div>
    </section>
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
        <li><a href="#introduction">快速开始</a></li>
        <li><a href="#prerequisites">准备工作</a>
            <ul>
                <li><a href="#system-requirements">系统要求</a></li>
            </ul>
        </li>
        <li><a href="#login">登录系统</a></li>
        <li><a href="#dashboard">了解控制台</a></li>
        <li><a href="#first-ai-assistant">创建您的第一个AI助手</a></li>
        <li><a href="#integrate-assistant">集成AI助手到您的应用</a></li>
        <li><a href="#next-steps">后续步骤</a></li>
        <li><a href="#support">获取支持</a></li>
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
    
    // 标签页切换逻辑
    const tabButtons = document.querySelectorAll('.tab');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // 移除同组中所有按钮的active类
            const tabGroup = this.parentElement;
            tabGroup.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // 为当前按钮添加active类
            this.classList.add('active');
            
            // 获取目标内容ID
            const targetId = this.getAttribute('data-target');
            
            // 隐藏所有内容，显示目标内容
            const tabContentsContainer = tabGroup.parentElement;
            tabContentsContainer.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            tabContentsContainer.querySelector(`#${targetId}`).classList.add('active');
        });
    });
});
</script> 