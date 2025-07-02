<?php
/**
 * AI助手产品详情页面
 * 
 * 展示AI助手产品的特点、优势和应用场景
 */

// 引入配置文件
require_once __DIR__ . '/../config/config.php';

// 页面标题
$pageTitle = "AI助手 - AlingAi Pro";
$pageDescription = "AlingAi Pro的AI助手是一款基于最先进深度学习技术的智能对话系统，为企业和个人用户提供实时信息和支持，可定制为特定领域的专家。";

// 添加页面特定的CSS
$additionalCSS = ['/css/products.css'];

// 开始输出缓冲
ob_start();
?>

<!-- 页面主要内容 -->
<main class="products-page">
    <!-- 英雄区域 -->
    <section class="product-detail-hero quantum-gradient">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">AlingAi Pro AI助手</h1>
                <p class="hero-subtitle">智能对话系统，为您解决各种问题</p>
            </div>
        </div>
    </section>

    <!-- 产品介绍 -->
    <section class="product-detail-section">
        <div class="container">
            <div class="product-detail-grid">
                <div class="product-detail-image">
                    <img src="/assets/images/products/ai-assistant-main.jpg" alt="AlingAi Pro AI助手界面展示" class="rounded-image">
                </div>
                <div class="product-detail-content">
                    <h2>智能对话，自然交流</h2>
                    <p>AlingAi Pro AI助手采用最先进的自然语言处理技术，能够理解复杂的查询，进行自然流畅的对话，为用户提供精准的信息和支持。</p>
                    <ul>
                        <li>支持多语言对话，包括中文、英文、日文等</li>
                        <li>理解上下文，记住对话历史，提供连贯回答</li>
                        <li>基于深度学习，持续优化回答质量</li>
                        <li>支持语音输入和输出，实现真正的无障碍交互</li>
                    </ul>
                    <a href="/contact" class="btn btn-primary">联系我们获取演示</a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 核心功能 -->
    <section class="product-detail-section quantum-bg-light">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">核心功能</h2>
                <p class="section-description">全方位满足您的智能对话需求</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3 class="feature-title">知识库集成</h3>
                    <p class="feature-description">可以集成您的企业知识库，提供基于企业特定知识的回答。</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h3 class="feature-title">角色定制</h3>
                    <p class="feature-description">可以定制为客服、销售、技术支持等不同角色，满足不同场景需求。</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="feature-title">数据分析</h3>
                    <p class="feature-description">提供对话数据分析，洞察用户需求，持续优化服务。</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-puzzle-piece"></i>
                    </div>
                    <h3 class="feature-title">系统集成</h3>
                    <p class="feature-description">轻松集成到网站、APP、微信等多个平台，提供一致的用户体验。</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="feature-title">安全保障</h3>
                    <p class="feature-description">企业级数据加密和访问控制，保障敏感信息安全。</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-drafting-compass"></i>
                    </div>
                    <h3 class="feature-title">个性化体验</h3>
                    <p class="feature-description">根据用户历史交互记录，提供个性化服务和推荐。</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 应用场景 -->
    <section class="product-detail-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">应用场景</h2>
                <p class="section-description">AI助手如何为不同行业带来价值</p>
            </div>
            
            <div class="scenarios-grid products-grid">
                <div class="scenario-card glass-card">
                    <h3>客户服务</h3>
                    <p>7x24小时全天候智能客服，自动回答常见问题，处理简单服务请求，提升客户满意度。</p>
                </div>
                
                <div class="scenario-card glass-card">
                    <h3>内部支持</h3>
                    <p>为企业员工提供IT支持、HR政策咨询等内部服务，降低支持团队负担。</p>
                </div>
                
                <div class="scenario-card glass-card">
                    <h3>销售顾问</h3>
                    <p>担任智能销售顾问，回答产品询问，推荐适合的产品，提高转化率。</p>
                </div>
                
                <div class="scenario-card glass-card">
                    <h3>教育培训</h3>
                    <p>作为学习助手，回答学生问题，提供个性化学习建议和资料推荐。</p>
                </div>
                
                <div class="scenario-card glass-card">
                    <h3>医疗咨询</h3>
                    <p>提供基础健康咨询，帮助筛选症状，引导患者寻求适当医疗资源。</p>
                </div>
                
                <div class="scenario-card glass-card">
                    <h3>个人助理</h3>
                    <p>管理日程、提醒、信息查询，帮助用户提高日常工作和生活效率。</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 客户案例 -->
    <section class="product-detail-section quantum-bg-light">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">客户成功案例</h2>
                <p class="section-description">看看其他企业如何使用我们的AI助手</p>
            </div>
            
            <div class="case-studies">
                <!-- 案例1 -->
                <div class="case-study glass-card">
                    <div class="case-study-header">
                        <img src="/assets/images/customers/case-1-logo.png" alt="某电商企业logo" class="case-logo">
                        <h3>某领先电商平台</h3>
                    </div>
                    <div class="case-study-content">
                        <p>通过部署AlingAi Pro AI助手作为在线客服，该电商平台实现了：</p>
                        <ul>
                            <li>客服响应时间从平均2分钟缩短到10秒以内</li>
                            <li>人工客服工作量减少了60%</li>
                            <li>客户满意度提升了35%</li>
                            <li>每年节省客服成本超过500万元</li>
                        </ul>
                        <blockquote>
                            <p>"AlingAi Pro的AI助手彻底改变了我们的客户服务模式，不仅节省了大量成本，更为我们提供了宝贵的客户洞察。"</p>
                            <footer>— 张总监, 客户服务部</footer>
                        </blockquote>
                    </div>
                </div>
                
                <!-- 案例2 -->
                <div class="case-study glass-card">
                    <div class="case-study-header">
                        <img src="/assets/images/customers/case-2-logo.png" alt="某银行logo" class="case-logo">
                        <h3>某国有大型银行</h3>
                    </div>
                    <div class="case-study-content">
                        <p>该银行将AlingAi Pro AI助手应用于内部知识库查询和客户服务，结果：</p>
                        <ul>
                            <li>员工查找内部政策和流程的时间减少了80%</li>
                            <li>新员工培训时间缩短了30%</li>
                            <li>客户查询解决率提高到了92%</li>
                            <li>合规风险显著降低</li>
                        </ul>
                        <blockquote>
                            <p>"AI助手成为了我们员工最信赖的工作伙伴，大大提升了工作效率和服务质量。"</p>
                            <footer>— 李经理, 数字化转型部</footer>
                        </blockquote>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 定价部分 -->
    <section class="product-detail-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">定价方案</h2>
                <p class="section-description">灵活的定价方案满足不同规模企业需求</p>
            </div>
            
            <div class="pricing-grid products-grid">
                <!-- 基础版 -->
                <div class="pricing-card glass-card">
                    <div class="pricing-header">
                        <h3>基础版</h3>
                        <div class="price">
                            <span class="amount">¥2,999</span>
                            <span class="period">/ 月</span>
                        </div>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li>最多5个AI助手角色</li>
                            <li>每月10万次对话</li>
                            <li>基础知识库集成</li>
                            <li>标准报表和分析</li>
                            <li>邮件技术支持</li>
                        </ul>
                    </div>
                    <div class="pricing-action">
                        <a href="/contact" class="btn btn-primary">联系销售</a>
                    </div>
                </div>
                
                <!-- 专业版 -->
                <div class="pricing-card glass-card highlight">
                    <div class="pricing-tag">最受欢迎</div>
                    <div class="pricing-header">
                        <h3>专业版</h3>
                        <div class="price">
                            <span class="amount">¥5,999</span>
                            <span class="period">/ 月</span>
                        </div>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li>最多20个AI助手角色</li>
                            <li>每月50万次对话</li>
                            <li>高级知识库集成</li>
                            <li>高级报表和分析</li>
                            <li>优先技术支持</li>
                            <li>API集成</li>
                            <li>自定义品牌</li>
                        </ul>
                    </div>
                    <div class="pricing-action">
                        <a href="/contact" class="btn btn-primary">联系销售</a>
                    </div>
                </div>
                
                <!-- 企业版 -->
                <div class="pricing-card glass-card">
                    <div class="pricing-header">
                        <h3>企业版</h3>
                        <div class="price">
                            <span class="custom-price">定制价格</span>
                        </div>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li>无限AI助手角色</li>
                            <li>无限对话次数</li>
                            <li>企业级知识库集成</li>
                            <li>高级安全和合规功能</li>
                            <li>专属客户经理</li>
                            <li>7x24小时技术支持</li>
                            <li>定制开发服务</li>
                            <li>本地部署选项</li>
                        </ul>
                    </div>
                    <div class="pricing-action">
                        <a href="/contact" class="btn btn-primary">联系销售</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 常见问题 -->
    <section class="product-detail-section quantum-bg-light">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">常见问题</h2>
                <p class="section-description">关于AI助手的常见问题解答</p>
            </div>
            
            <div class="faq-grid">
                <div class="faq-item glass-card">
                    <h3>AI助手需要多长时间部署？</h3>
                    <p>基础版本可在1-3个工作日内完成部署。如需定制化开发和知识库集成，通常需要1-4周时间，具体取决于项目复杂度。</p>
                </div>
                
                <div class="faq-item glass-card">
                    <h3>AI助手支持哪些语言？</h3>
                    <p>我们的AI助手目前支持中文、英文、日文、韩文、法文、德文、西班牙文等20多种语言，并且还在不断扩展中。</p>
                </div>
                
                <div class="faq-item glass-card">
                    <h3>如何将AI助手与我的系统集成？</h3>
                    <p>我们提供多种集成方式，包括API、WebSocket、网页插件、APP SDK等，可以轻松集成到您现有的网站、APP或内部系统中。</p>
                </div>
                
                <div class="faq-item glass-card">
                    <h3>我的数据安全吗？</h3>
                    <p>是的，我们非常重视数据安全。所有数据传输采用TLS加密，存储采用高级加密标准，并符合GDPR、CCPA等隐私法规要求。</p>
                </div>
                
                <div class="faq-item glass-card">
                    <h3>如何训练AI助手理解我们的业务知识？</h3>
                    <p>我们有专业的知识工程师团队，会帮助您整理业务知识，建立定制化知识库，并通过迭代优化提升AI助手的回答质量。</p>
                </div>
                
                <div class="faq-item glass-card">
                    <h3>是否提供试用版本？</h3>
                    <p>是的，我们提供为期14天的免费试用，让您充分体验我们的AI助手功能，并评估是否符合您的业务需求。</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 行动号召 -->
    <section class="cta-section quantum-gradient">
        <div class="container">
            <div class="cta-content glass-card">
                <h2>准备好让AI助手为您服务了吗？</h2>
                <p>联系我们的产品专家，获取个性化演示，了解AI助手如何为您的业务创造价值。</p>
                <div class="cta-buttons">
                    <a href="/contact" class="btn btn-light">联系我们</a>
                    <a href="/register" class="btn btn-outline-light">注册试用</a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
// 获取缓冲内容
$pageContent = ob_get_clean();

// 使用页面模板
require_once __DIR__ . '/../templates/page.php';
?> 