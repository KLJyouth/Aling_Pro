<?php
/**
 * 产品主页
 * 
 * 展示所有AlingAi Pro的产品
 */

// 引入配置文件
require_once __DIR__ . '/../config/config.php';

// 页面标题
$pageTitle = "产品 - AlingAi Pro";
$pageDescription = "探索AlingAi Pro提供的所有强大AI产品，包括AI助手、数据分析、自动化工具和系统集成解决方案。";

// 添加页面特定的CSS
$additionalCSS = ['/css/products.css'];

// 开始输出缓冲
ob_start();
?>

<!-- 页面主要内容 -->
<main class="products-page">
    <!-- 英雄区域 -->
    <section class="hero-section quantum-gradient">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">AlingAi Pro 产品</h1>
                <p class="hero-subtitle">探索我们的前沿AI技术解决方案</p>
            </div>
        </div>
    </section>

    <!-- 产品概述 -->
    <section class="products-overview">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">我们的产品线</h2>
                <p class="section-description">AlingAi Pro提供一套全面的AI解决方案，满足各种业务需求</p>
            </div>
            
            <div class="products-grid">
                <!-- AI助手 -->
                <div class="product-card glass-card">
                    <div class="product-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h3 class="product-title">AI 助手</h3>
                    <p class="product-description">智能对话助手，为用户提供实时信息和支持，可定制为特定领域的专家。</p>
                    <a href="/products/ai-assistant" class="product-link">了解更多 <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <!-- 数据分析 -->
                <div class="product-card glass-card">
                    <div class="product-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3 class="product-title">数据分析</h3>
                    <p class="product-description">强大的AI驱动数据分析工具，帮助企业从复杂数据中挖掘有价值的见解。</p>
                    <a href="/products/data-analysis" class="product-link">了解更多 <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <!-- 自动化工具 -->
                <div class="product-card glass-card">
                    <div class="product-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h3 class="product-title">自动化工具</h3>
                    <p class="product-description">自动化日常业务流程，提高效率，减少人为错误，让员工专注于创造性工作。</p>
                    <a href="/products/automation" class="product-link">了解更多 <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <!-- 系统集成 -->
                <div class="product-card glass-card">
                    <div class="product-icon">
                        <i class="fas fa-network-wired"></i>
                    </div>
                    <h3 class="product-title">系统集成</h3>
                    <p class="product-description">无缝集成我们的AI技术到您现有的系统和工作流程中，实现数字化转型。</p>
                    <a href="/products/integration" class="product-link">了解更多 <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 产品特点 -->
    <section class="products-features quantum-bg-light">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">产品特点</h2>
                <p class="section-description">我们的产品由先进技术和人性化设计驱动</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="feature-title">企业级安全</h3>
                    <p class="feature-description">所有产品都符合最严格的安全标准，保护您的数据安全。</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </div>
                    <h3 class="feature-title">可扩展架构</h3>
                    <p class="feature-description">随着业务增长，我们的解决方案可以轻松扩展以满足不断变化的需求。</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-plug"></i>
                    </div>
                    <h3 class="feature-title">灵活API</h3>
                    <p class="feature-description">通过丰富的API，轻松将我们的产品集成到现有系统中。</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <h3 class="feature-title">高性能</h3>
                    <p class="feature-description">优化的架构和算法确保快速响应和处理速度。</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <h3 class="feature-title">可定制</h3>
                    <p class="feature-description">根据特定需求定制解决方案，实现个性化体验。</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="feature-title">24/7支持</h3>
                    <p class="feature-description">全天候专家支持，确保您的系统始终正常运行。</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 客户案例 -->
    <section class="customer-success">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">客户成功案例</h2>
                <p class="section-description">了解全球企业如何使用我们的产品取得成功</p>
            </div>
            
            <div class="testimonials-slider">
                <div class="testimonial glass-card">
                    <div class="testimonial-content">
                        <p>"AlingAi Pro的AI助手彻底改变了我们的客户服务。响应时间减少了60%，客户满意度提高了40%。"</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="/assets/images/customers/customer-1.jpg" alt="客户头像" class="author-image">
                        <div class="author-info">
                            <h4>李明</h4>
                            <p>科技公司CTO</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial glass-card">
                    <div class="testimonial-content">
                        <p>"数据分析工具帮助我们发现了之前未注意到的业务模式，为我们带来了20%的收入增长。"</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="/assets/images/customers/customer-2.jpg" alt="客户头像" class="author-image">
                        <div class="author-info">
                            <h4>王芳</h4>
                            <p>零售连锁数据分析主管</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial glass-card">
                    <div class="testimonial-content">
                        <p>"自动化工具每月为我们节省了超过200小时的手动工作，让团队可以专注于战略任务。"</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="/assets/images/customers/customer-3.jpg" alt="客户头像" class="author-image">
                        <div class="author-info">
                            <h4>张伟</h4>
                            <p>金融服务公司运营总监</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 行动号召 -->
    <section class="cta-section quantum-gradient">
        <div class="container">
            <div class="cta-content glass-card">
                <h2>准备好探索我们的产品了吗？</h2>
                <p>联系我们的产品专家，获取个性化演示并了解如何为您的业务增加价值。</p>
                <div class="cta-buttons">
                    <a href="/contact" class="btn btn-light">联系我们</a>
                    <a href="/pricing" class="btn btn-outline-light">查看价格</a>
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