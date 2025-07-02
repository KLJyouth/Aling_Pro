<?php
/**
 * AlingAi Pro 6.0 - 主入口文件
 * Enhanced Multi-AI Integration Platform
 * 
 * @version 6.0.0
 * @author AlingAi Team
 * @copyright 2024 AlingAi Corporation
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__);
}

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 定义应用常量
define('APP_START_TIME', microtime(true));
define('APP_ROOT', dirname(__DIR__));
define('APP_PUBLIC', __DIR__);
define('APP_VERSION', '6.0.0');
define('APP_NAME', 'AlingAi Pro - Enhanced');

// 错误报告设置
$isProduction = (getenv('APP_ENV') === 'production');

if ($isProduction) {
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('log_errors', '1');
}

// 性能优化设置
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');
ini_set('post_max_size', '128M');
ini_set('upload_max_filesize', '64M');

// 安全设置
ini_set('expose_php', 'Off');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', $isProduction ? '1' : '0');
ini_set('session.use_strict_mode', '1');

// 启动会话
session_start();

// 页面信息设置
$pageTitle = 'AlingAi Pro - 量子科技风格的AI助手平台';
$pageDescription = '探索未来科技，体验智能交互的无限可能';
$pageKeywords = 'AI, 人工智能, 量子科技, 助手, 深度学习, 量子计算, 智能交互';
$pageAuthor = 'AlingAi Team';

// 包含页面模板
require_once __DIR__ . '/templates/page.php';

// 渲染页面头部
renderPageHeader();
?>

<!-- 英雄区域 -->
<section class="hero">
    <div class="container">
        <h1>未来科技，现在体验</h1>
        <p>AlingAi Pro 是一个量子科技风格的AI助手平台，结合了最前沿的人工智能技术和极致的用户体验，为您提供智能化的解决方案。</p>
        
        <div class="hero-buttons">
            <a href="/register" class="btn">免费试用</a>
            <a href="/docs" class="btn btn-secondary">了解更多</a>
        </div>
        
        <div class="hero-image">
            <img src="/assets/images/hero-dashboard.png" alt="AlingAi Pro 仪表板" class="dashboard-preview">
        </div>
    </div>
</section>

<!-- 特性区域 -->
<section class="features" id="features">
    <div class="container">
        <h2>强大功能，简单易用</h2>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-brain"></i>
                </div>
                <h3>先进的AI模型</h3>
                <p>集成多种顶尖AI模型，为不同场景提供最佳解决方案，支持自然语言处理、图像识别和数据分析。</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>量子级安全</h3>
                <p>采用先进的加密技术和安全协议，保护您的数据和隐私，让您安心使用。</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <h3>无缝集成</h3>
                <p>轻松与现有系统和工作流程集成，提供API接口和SDK，满足各种定制化需求。</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
                <h3>高性能处理</h3>
                <p>基于云计算架构，提供快速响应和高并发处理能力，满足企业级应用需求。</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>数据可视化</h3>
                <p>直观的数据展示和分析工具，帮助您理解数据背后的价值，做出明智决策。</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>多用户协作</h3>
                <p>支持团队协作和权限管理，提高工作效率，适合各种规模的团队使用。</p>
            </div>
        </div>
    </div>
</section>

<!-- 解决方案区域 -->
<section class="solutions">
    <div class="container">
        <h2>行业解决方案</h2>
        <p class="section-desc">针对不同行业的特定需求，我们提供量身定制的解决方案</p>
        
        <div class="solutions-grid">
            <a href="/solutions/enterprise" class="solution-card">
                <div class="solution-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h3>企业应用</h3>
                <p>提升业务效率，降低运营成本，实现数字化转型</p>
            </a>
            
            <a href="/solutions/education" class="solution-card">
                <div class="solution-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3>教育培训</h3>
                <p>个性化学习体验，智能教学辅助，提高教学质量</p>
            </a>
            
            <a href="/solutions/healthcare" class="solution-card">
                <div class="solution-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h3>医疗健康</h3>
                <p>辅助诊断，医疗数据分析，提高医疗服务效率</p>
            </a>
            
            <a href="/solutions/finance" class="solution-card">
                <div class="solution-icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <h3>金融科技</h3>
                <p>风险评估，智能投顾，金融数据分析与预测</p>
            </a>
        </div>
    </div>
</section>

<!-- 客户评价区域 -->
<section class="testimonials">
    <div class="container">
        <h2>客户评价</h2>
        
        <div class="testimonials-slider">
            <div class="testimonial-item">
                <div class="testimonial-content">
                    <p>"AlingAi Pro 帮助我们提高了30%的工作效率，AI助手功能极大地简化了我们的日常工作流程。"</p>
                </div>
                <div class="testimonial-author">
                    <img src="/assets/images/avatar-1.jpg" alt="客户头像" class="author-avatar">
                    <div class="author-info">
                        <h4>张明</h4>
                        <p>科技公司 CTO</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-item">
                <div class="testimonial-content">
                    <p>"量子级安全保障让我们对数据安全非常放心，系统集成也非常顺畅，是我们见过的最好用的AI平台。"</p>
                </div>
                <div class="testimonial-author">
                    <img src="/assets/images/avatar-2.jpg" alt="客户头像" class="author-avatar">
                    <div class="author-info">
                        <h4>李华</h4>
                        <p>金融机构 IT主管</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-item">
                <div class="testimonial-content">
                    <p>"数据分析功能非常强大，可视化展示让我们能够快速理解数据并做出决策，为我们带来了显著的业务增长。"</p>
                </div>
                <div class="testimonial-author">
                    <img src="/assets/images/avatar-3.jpg" alt="客户头像" class="author-avatar">
                    <div class="author-info">
                        <h4>王芳</h4>
                        <p>电商平台 运营总监</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 价格区域 -->
<section class="pricing">
    <div class="container">
        <h2>灵活定价，按需选择</h2>
        <p class="section-desc">我们提供多种套餐选择，满足不同规模企业和个人的需求</p>
        
        <div class="pricing-grid">
            <div class="pricing-card">
                <div class="pricing-header">
                    <h3>基础版</h3>
                    <div class="pricing-price">
                        <span class="price">¥99</span>
                        <span class="period">/月</span>
                    </div>
                </div>
                <div class="pricing-features">
                    <ul>
                        <li><i class="fas fa-check"></i> 基础AI助手功能</li>
                        <li><i class="fas fa-check"></i> 5GB云存储空间</li>
                        <li><i class="fas fa-check"></i> 每月100小时使用时长</li>
                        <li><i class="fas fa-check"></i> 基础数据分析</li>
                        <li><i class="fas fa-check"></i> 邮件支持</li>
                    </ul>
                </div>
                <div class="pricing-action">
                    <a href="/register?plan=basic" class="btn">开始使用</a>
                </div>
            </div>
            
            <div class="pricing-card featured">
                <div class="pricing-badge">推荐</div>
                <div class="pricing-header">
                    <h3>专业版</h3>
                    <div class="pricing-price">
                        <span class="price">¥299</span>
                        <span class="period">/月</span>
                    </div>
                </div>
                <div class="pricing-features">
                    <ul>
                        <li><i class="fas fa-check"></i> 高级AI助手功能</li>
                        <li><i class="fas fa-check"></i> 50GB云存储空间</li>
                        <li><i class="fas fa-check"></i> 无限使用时长</li>
                        <li><i class="fas fa-check"></i> 高级数据分析与可视化</li>
                        <li><i class="fas fa-check"></i> 优先技术支持</li>
                        <li><i class="fas fa-check"></i> API接口访问</li>
                    </ul>
                </div>
                <div class="pricing-action">
                    <a href="/register?plan=pro" class="btn">开始使用</a>
                </div>
            </div>
            
            <div class="pricing-card">
                <div class="pricing-header">
                    <h3>企业版</h3>
                    <div class="pricing-price">
                        <span class="price">联系我们</span>
                        <span class="period">定制方案</span>
                    </div>
                </div>
                <div class="pricing-features">
                    <ul>
                        <li><i class="fas fa-check"></i> 全部专业版功能</li>
                        <li><i class="fas fa-check"></i> 无限云存储空间</li>
                        <li><i class="fas fa-check"></i> 私有化部署选项</li>
                        <li><i class="fas fa-check"></i> 定制化开发服务</li>
                        <li><i class="fas fa-check"></i> 专属客户经理</li>
                        <li><i class="fas fa-check"></i> 7×24小时技术支持</li>
                    </ul>
                </div>
                <div class="pricing-action">
                    <a href="/contact?subject=enterprise" class="btn">联系我们</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 行动召唤区域 -->
<section class="cta">
    <div class="container">
        <h2>准备好开始您的智能化之旅了吗？</h2>
        <p>立即注册，免费体验AlingAi Pro的强大功能</p>
        <div class="cta-buttons">
            <a href="/register" class="btn">免费注册</a>
            <a href="/contact" class="btn btn-secondary">联系我们</a>
        </div>
    </div>
</section>

<!-- 页面样式 -->
<style>
    /* 英雄区域 */
    .hero {
        position: relative;
        padding: var(--spacing-xxl) 0;
        text-align: center;
        min-height: calc(100vh - 80px);
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .hero h1 {
        font-size: 3rem;
        margin-bottom: var(--spacing-md);
        background: linear-gradient(to right, var(--text-color), var(--secondary-color));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        display: inline-block;
    }
    
    .hero p {
        font-size: 1.25rem;
        max-width: 800px;
        margin: 0 auto var(--spacing-xl);
        opacity: 0.9;
    }
    
    .hero-buttons {
        display: flex;
        justify-content: center;
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-xxl);
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: var(--spacing-sm) var(--spacing-lg);
        background-color: var(--accent-color);
        color: var(--text-color);
        border: none;
        border-radius: var(--border-radius-md);
        font-weight: 500;
        cursor: pointer;
        transition: all var(--transition-fast);
        text-decoration: none;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px var(--accent-glow);
    }
    
    .btn.btn-secondary {
        background-color: transparent;
        border: 1px solid var(--accent-color);
    }
    
    .hero-image {
        max-width: 1000px;
        margin: 0 auto;
    }
    
    .dashboard-preview {
        width: 100%;
        border-radius: var(--border-radius-lg);
        box-shadow: 0 20px 40px var(--shadow-color);
    }
    
    /* 特性区域 */
    .features {
        padding: var(--spacing-xxl) 0;
    }
    
    .features h2, .solutions h2, .testimonials h2, .pricing h2, .cta h2 {
        text-align: center;
        font-size: 2.5rem;
        margin-bottom: var(--spacing-xl);
        background: linear-gradient(to right, var(--secondary-color), var(--tertiary-color));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        display: inline-block;
        margin-left: auto;
        margin-right: auto;
    }
    
    .section-desc {
        text-align: center;
        max-width: 800px;
        margin: 0 auto var(--spacing-xl);
        opacity: 0.8;
    }
    
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: var(--spacing-xl);
        margin-top: var(--spacing-xl);
    }
    
    .feature-card {
        padding: var(--spacing-xl);
        background: var(--glass-background);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
        transition: transform var(--transition-normal);
    }
    
    .feature-card:hover {
        transform: translateY(-5px);
    }
    
    .feature-icon {
        font-size: 2.5rem;
        margin-bottom: var(--spacing-md);
        color: var(--accent-color);
    }
    
    .feature-card h3 {
        font-size: 1.5rem;
        margin-bottom: var(--spacing-md);
    }
    
    .feature-card p {
        opacity: 0.8;
    }
    
    /* 解决方案区域 */
    .solutions {
        padding: var(--spacing-xxl) 0;
        background: linear-gradient(to bottom, transparent, rgba(10, 132, 255, 0.05), transparent);
    }
    
    .solutions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--spacing-lg);
    }
    
    .solution-card {
        padding: var(--spacing-lg);
        background: var(--glass-background);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
        text-align: center;
        transition: all var(--transition-normal);
        text-decoration: none;
        color: var(--text-color);
    }
    
    .solution-card:hover {
        transform: translateY(-5px);
        border-color: var(--accent-color);
    }
    
    .solution-icon {
        font-size: 2.5rem;
        margin-bottom: var(--spacing-md);
        color: var(--secondary-color);
    }
    
    .solution-card h3 {
        font-size: 1.3rem;
        margin-bottom: var(--spacing-sm);
    }
    
    .solution-card p {
        opacity: 0.8;
        font-size: 0.9rem;
    }
    
    /* 客户评价区域 */
    .testimonials {
        padding: var(--spacing-xxl) 0;
    }
    
    .testimonials-slider {
        display: flex;
        flex-wrap: wrap;
        gap: var(--spacing-lg);
        justify-content: center;
    }
    
    .testimonial-item {
        flex: 1;
        min-width: 300px;
        max-width: 400px;
        padding: var(--spacing-lg);
        background: var(--glass-background);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
    }
    
    .testimonial-content {
        margin-bottom: var(--spacing-md);
    }
    
    .testimonial-content p {
        font-style: italic;
        opacity: 0.9;
    }
    
    .testimonial-author {
        display: flex;
        align-items: center;
    }
    
    .author-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        margin-right: var(--spacing-md);
        object-fit: cover;
    }
    
    .author-info h4 {
        margin: 0;
        font-size: 1.1rem;
    }
    
    .author-info p {
        margin: 0;
        opacity: 0.7;
        font-size: 0.9rem;
    }
    
    /* 价格区域 */
    .pricing {
        padding: var(--spacing-xxl) 0;
        background: linear-gradient(to bottom, transparent, rgba(191, 90, 242, 0.05), transparent);
    }
    
    .pricing-grid {
        display: flex;
        flex-wrap: wrap;
        gap: var(--spacing-lg);
        justify-content: center;
    }
    
    .pricing-card {
        flex: 1;
        min-width: 280px;
        max-width: 350px;
        padding: var(--spacing-lg);
        background: var(--glass-background);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
        position: relative;
        transition: transform var(--transition-normal);
    }
    
    .pricing-card:hover {
        transform: translateY(-5px);
    }
    
    .pricing-card.featured {
        border-color: var(--accent-color);
        transform: scale(1.05);
    }
    
    .pricing-card.featured:hover {
        transform: scale(1.05) translateY(-5px);
    }
    
    .pricing-badge {
        position: absolute;
        top: -12px;
        right: 20px;
        background-color: var(--accent-color);
        color: var(--text-color);
        padding: 4px 12px;
        border-radius: var(--border-radius-sm);
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .pricing-header {
        text-align: center;
        margin-bottom: var(--spacing-lg);
        padding-bottom: var(--spacing-md);
        border-bottom: 1px solid var(--glass-border);
    }
    
    .pricing-header h3 {
        margin-bottom: var(--spacing-sm);
    }
    
    .pricing-price {
        display: flex;
        align-items: baseline;
        justify-content: center;
    }
    
    .pricing-price .price {
        font-size: 2rem;
        font-weight: 700;
        color: var(--secondary-color);
    }
    
    .pricing-price .period {
        margin-left: 5px;
        opacity: 0.7;
    }
    
    .pricing-features ul {
        list-style: none;
        padding: 0;
        margin-bottom: var(--spacing-lg);
    }
    
    .pricing-features li {
        padding: 8px 0;
        display: flex;
        align-items: center;
    }
    
    .pricing-features li i {
        color: var(--success-color);
        margin-right: 10px;
    }
    
    .pricing-action {
        text-align: center;
    }
    
    /* 行动召唤区域 */
    .cta {
        padding: var(--spacing-xxl) 0;
        text-align: center;
        background: linear-gradient(to right, rgba(10, 132, 255, 0.1), rgba(191, 90, 242, 0.1));
        border-radius: var(--border-radius-lg);
        margin: var(--spacing-xxl) var(--spacing-lg);
    }
    
    .cta h2 {
        margin-bottom: var(--spacing-sm);
    }
    
    .cta p {
        max-width: 600px;
        margin: 0 auto var(--spacing-lg);
        opacity: 0.9;
    }
    
    .cta-buttons {
        display: flex;
        justify-content: center;
        gap: var(--spacing-md);
    }
    
    /* 响应式设计 */
    @media (max-width: 768px) {
        .hero h1 {
            font-size: 2.5rem;
        }
        
        .hero p {
            font-size: 1.1rem;
        }
        
        .features h2, .solutions h2, .testimonials h2, .pricing h2, .cta h2 {
            font-size: 2rem;
        }
        
        .pricing-grid {
            flex-direction: column;
            align-items: center;
        }
        
        .pricing-card {
            width: 100%;
            max-width: 400px;
        }
        
        .pricing-card.featured {
            transform: none;
            order: -1;
        }
        
        .pricing-card.featured:hover {
            transform: translateY(-5px);
        }
    }
</style>

<!-- 加载字体图标 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<?php
// 渲染页面页脚
renderPageFooter();

// 输出执行时间
$executionTime = microtime(true) - APP_START_TIME;
echo "<!-- 应用程序执行完成，耗时 " . round($executionTime, 4) . " 秒 -->";
?>

