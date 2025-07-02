<?php
/**
 * 价格页面
 * 
 * @version 1.0.0
 * @author AlingAi Team
 * @copyright 2024 AlingAi Corporation
 */

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 启动会话
session_start();

// 页面信息设置
$pageTitle = '价格方案 - AlingAi Pro';
$pageDescription = 'AlingAi Pro 提供灵活的价格方案，满足不同规模企业和个人的需求';
$pageKeywords = '价格, 套餐, 订阅, 企业版, 专业版, 基础版, AlingAi Pro';

// 包含页面模板
require_once __DIR__ . '/templates/page.php';

// 渲染页面头部
renderPageHeader();
?>

<!-- 价格页面内容 -->
<section class="pricing-hero">
    <div class="container">
        <h1>灵活定价，按需选择</h1>
        <p>我们提供多种套餐选择，满足不同规模企业和个人的需求</p>
        
        <div class="pricing-toggle">
            <span class="toggle-label">按月付费</span>
            <label class="switch">
                <input type="checkbox" id="billingToggle">
                <span class="slider"></span>
            </label>
            <span class="toggle-label">按年付费</span>
            <span class="save-badge">省20%</span>
        </div>
    </div>
</section>

<section class="pricing-plans">
    <div class="container">
        <div class="pricing-grid">
            <!-- 基础版 -->
            <div class="pricing-card">
                <div class="pricing-header">
                    <h2>基础版</h2>
                    <div class="pricing-price">
                        <div class="price monthly">¥<span class="amount">99</span><span class="period">/月</span></div>
                        <div class="price annually" style="display: none;">¥<span class="amount">79</span><span class="period">/月</span></div>
                    </div>
                    <p>适合个人用户和小型团队</p>
                </div>
                
                <div class="pricing-features">
                    <ul>
                        <li><i class="fas fa-check"></i> 基础AI助手功能</li>
                        <li><i class="fas fa-check"></i> 5GB云存储空间</li>
                        <li><i class="fas fa-check"></i> 每月100小时使用时长</li>
                        <li><i class="fas fa-check"></i> 基础数据分析</li>
                        <li><i class="fas fa-check"></i> 最多3个用户</li>
                        <li><i class="fas fa-check"></i> 邮件支持</li>
                        <li class="not-included"><i class="fas fa-times"></i> API接口访问</li>
                        <li class="not-included"><i class="fas fa-times"></i> 高级数据分析与可视化</li>
                        <li class="not-included"><i class="fas fa-times"></i> 自定义集成</li>
                    </ul>
                </div>
                
                <div class="pricing-action">
                    <a href="/register?plan=basic" class="btn">开始使用</a>
                </div>
            </div>
            
            <!-- 专业版 -->
            <div class="pricing-card featured">
                <div class="pricing-badge">推荐</div>
                <div class="pricing-header">
                    <h2>专业版</h2>
                    <div class="pricing-price">
                        <div class="price monthly">¥<span class="amount">299</span><span class="period">/月</span></div>
                        <div class="price annually" style="display: none;">¥<span class="amount">239</span><span class="period">/月</span></div>
                    </div>
                    <p>适合中小型企业和专业团队</p>
                </div>
                
                <div class="pricing-features">
                    <ul>
                        <li><i class="fas fa-check"></i> 高级AI助手功能</li>
                        <li><i class="fas fa-check"></i> 50GB云存储空间</li>
                        <li><i class="fas fa-check"></i> 无限使用时长</li>
                        <li><i class="fas fa-check"></i> 高级数据分析与可视化</li>
                        <li><i class="fas fa-check"></i> 最多10个用户</li>
                        <li><i class="fas fa-check"></i> 优先技术支持</li>
                        <li><i class="fas fa-check"></i> API接口访问</li>
                        <li><i class="fas fa-check"></i> 基础自定义集成</li>
                        <li class="not-included"><i class="fas fa-times"></i> 专属客户经理</li>
                    </ul>
                </div>
                
                <div class="pricing-action">
                    <a href="/register?plan=pro" class="btn">开始使用</a>
                </div>
            </div>
            
            <!-- 企业版 -->
            <div class="pricing-card">
                <div class="pricing-header">
                    <h2>企业版</h2>
                    <div class="pricing-price">
                        <div class="price">联系我们</div>
                        <div class="period">定制方案</div>
                    </div>
                    <p>适合大型企业和高级需求</p>
                </div>
                
                <div class="pricing-features">
                    <ul>
                        <li><i class="fas fa-check"></i> 全部专业版功能</li>
                        <li><i class="fas fa-check"></i> 无限云存储空间</li>
                        <li><i class="fas fa-check"></i> 无限用户数量</li>
                        <li><i class="fas fa-check"></i> 私有化部署选项</li>
                        <li><i class="fas fa-check"></i> 定制化开发服务</li>
                        <li><i class="fas fa-check"></i> 专属客户经理</li>
                        <li><i class="fas fa-check"></i> 7×24小时技术支持</li>
                        <li><i class="fas fa-check"></i> 高级安全合规选项</li>
                        <li><i class="fas fa-check"></i> SLA保障</li>
                    </ul>
                </div>
                
                <div class="pricing-action">
                    <a href="/contact?subject=enterprise" class="btn">联系我们</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="pricing-features-compare">
    <div class="container">
        <h2>功能对比</h2>
        <div class="table-responsive">
            <table class="features-table">
                <thead>
                    <tr>
                        <th>功能</th>
                        <th>基础版</th>
                        <th>专业版</th>
                        <th>企业版</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="feature-name">AI助手</td>
                        <td>基础功能</td>
                        <td>高级功能</td>
                        <td>定制功能</td>
                    </tr>
                    <tr>
                        <td class="feature-name">云存储空间</td>
                        <td>5GB</td>
                        <td>50GB</td>
                        <td>无限</td>
                    </tr>
                    <tr>
                        <td class="feature-name">使用时长</td>
                        <td>每月100小时</td>
                        <td>无限</td>
                        <td>无限</td>
                    </tr>
                    <tr>
                        <td class="feature-name">用户数量</td>
                        <td>最多3个</td>
                        <td>最多10个</td>
                        <td>无限</td>
                    </tr>
                    <tr>
                        <td class="feature-name">数据分析</td>
                        <td>基础分析</td>
                        <td>高级分析与可视化</td>
                        <td>定制分析解决方案</td>
                    </tr>
                    <tr>
                        <td class="feature-name">API访问</td>
                        <td><i class="fas fa-times"></i></td>
                        <td><i class="fas fa-check"></i></td>
                        <td><i class="fas fa-check"></i></td>
                    </tr>
                    <tr>
                        <td class="feature-name">系统集成</td>
                        <td><i class="fas fa-times"></i></td>
                        <td>基础集成</td>
                        <td>高级定制集成</td>
                    </tr>
                    <tr>
                        <td class="feature-name">技术支持</td>
                        <td>邮件支持</td>
                        <td>优先技术支持</td>
                        <td>7×24小时专属支持</td>
                    </tr>
                    <tr>
                        <td class="feature-name">私有化部署</td>
                        <td><i class="fas fa-times"></i></td>
                        <td><i class="fas fa-times"></i></td>
                        <td><i class="fas fa-check"></i></td>
                    </tr>
                    <tr>
                        <td class="feature-name">安全合规</td>
                        <td>基础安全</td>
                        <td>高级安全</td>
                        <td>企业级安全与合规</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="pricing-faq">
    <div class="container">
        <h2>常见问题</h2>
        
        <div class="faq-grid">
            <div class="faq-item">
                <h3>如何选择适合我的方案？</h3>
                <p>根据您的团队规模、使用需求和预算选择合适的方案。基础版适合个人用户和小型团队，专业版适合中小型企业和专业团队，企业版则为大型企业提供定制化解决方案。</p>
            </div>
            
            <div class="faq-item">
                <h3>是否提供免费试用？</h3>
                <p>是的，我们为所有方案提供14天的免费试用期，无需信用卡即可开始试用。试用期结束后，您可以选择升级到付费方案或继续使用有限功能的免费版。</p>
            </div>
            
            <div class="faq-item">
                <h3>如何更改或取消订阅？</h3>
                <p>您可以随时在账户设置中更改或取消订阅。按月付费的方案可以随时取消，按年付费的方案取消后将继续有效至当前付费周期结束。</p>
            </div>
            
            <div class="faq-item">
                <h3>是否支持自定义功能？</h3>
                <p>专业版支持基础的自定义集成，企业版则提供全面的定制化开发服务，可以根据您的特定需求定制功能和流程。</p>
            </div>
            
            <div class="faq-item">
                <h3>付款方式有哪些？</h3>
                <p>我们支持信用卡、银行转账和支付宝等多种付款方式。企业版客户可以联系我们的销售团队安排合适的付款方式。</p>
            </div>
            
            <div class="faq-item">
                <h3>数据安全如何保障？</h3>
                <p>我们采用先进的加密技术和安全协议保护您的数据。所有方案都提供基础的安全保障，企业版则提供更高级的安全和合规选项，包括私有化部署。</p>
            </div>
        </div>
    </div>
</section>

<section class="pricing-cta">
    <div class="container">
        <h2>还有其他问题？</h2>
        <p>我们的团队随时为您提供帮助，解答您的疑问</p>
        <div class="cta-buttons">
            <a href="/contact" class="btn">联系我们</a>
            <a href="/register" class="btn btn-secondary">免费试用</a>
        </div>
    </div>
</section>

<!-- 页面样式 -->
<style>
    /* 价格页面样式 */
    .pricing-hero {
        padding: var(--spacing-xxl) 0 var(--spacing-xl);
        text-align: center;
        background: linear-gradient(to right, rgba(10, 132, 255, 0.05), rgba(191, 90, 242, 0.05));
        border-radius: var(--border-radius-lg);
        margin: 0 var(--spacing-lg) var(--spacing-xl);
    }
    
    .pricing-hero h1 {
        font-size: 3rem;
        margin-bottom: var(--spacing-md);
        background: linear-gradient(to right, var(--text-color), var(--secondary-color));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        display: inline-block;
    }
    
    .pricing-hero p {
        font-size: 1.25rem;
        max-width: 800px;
        margin: 0 auto var(--spacing-lg);
        opacity: 0.9;
    }
    
    .pricing-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: var(--spacing-lg);
    }
    
    .toggle-label {
        margin: 0 var(--spacing-sm);
        font-size: 1rem;
    }
    
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 30px;
    }
    
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255, 255, 255, 0.1);
        transition: .4s;
        border-radius: 34px;
    }
    
    .slider:before {
        position: absolute;
        content: "";
        height: 22px;
        width: 22px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    
    input:checked + .slider {
        background-color: var(--accent-color);
    }
    
    input:checked + .slider:before {
        transform: translateX(30px);
    }
    
    .save-badge {
        background-color: var(--success-color);
        color: var(--text-color);
        padding: 2px 8px;
        border-radius: var(--border-radius-sm);
        font-size: 0.8rem;
        margin-left: var(--spacing-sm);
    }
    
    /* 价格方案区域 */
    .pricing-plans {
        padding: var(--spacing-xl) 0;
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
    
    .pricing-header h2 {
        margin-bottom: var(--spacing-sm);
        font-size: 1.8rem;
        color: var(--secondary-color);
    }
    
    .pricing-header p {
        opacity: 0.7;
        font-size: 0.9rem;
    }
    
    .pricing-price {
        margin: var(--spacing-md) 0;
    }
    
    .price {
        display: flex;
        align-items: baseline;
        justify-content: center;
    }
    
    .amount {
        font-size: 3rem;
        font-weight: 700;
        color: var(--text-color);
    }
    
    .period {
        margin-left: 5px;
        opacity: 0.7;
    }
    
    .pricing-features {
        margin-bottom: var(--spacing-lg);
    }
    
    .pricing-features ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .pricing-features li {
        padding: 8px 0;
        display: flex;
        align-items: center;
    }
    
    .pricing-features li i {
        width: 20px;
        margin-right: 10px;
        text-align: center;
    }
    
    .pricing-features li i.fa-check {
        color: var(--success-color);
    }
    
    .pricing-features li i.fa-times {
        color: var(--error-color);
    }
    
    .pricing-features li.not-included {
        opacity: 0.5;
    }
    
    .pricing-action {
        text-align: center;
    }
    
    .pricing-action .btn {
        width: 100%;
        padding: 12px;
    }
    
    /* 功能对比区域 */
    .pricing-features-compare {
        padding: var(--spacing-xxl) 0;
    }
    
    .pricing-features-compare h2 {
        text-align: center;
        margin-bottom: var(--spacing-xl);
        font-size: 2.2rem;
        background: linear-gradient(to right, var(--secondary-color), var(--tertiary-color));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        display: inline-block;
        margin-left: auto;
        margin-right: auto;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    .features-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .features-table th,
    .features-table td {
        padding: 15px;
        text-align: center;
        border-bottom: 1px solid var(--glass-border);
    }
    
    .features-table th {
        background-color: rgba(255, 255, 255, 0.05);
        font-weight: 600;
    }
    
    .features-table th:first-child,
    .features-table td:first-child {
        text-align: left;
    }
    
    .features-table td.feature-name {
        font-weight: 500;
    }
    
    .features-table tr:hover {
        background-color: rgba(255, 255, 255, 0.02);
    }
    
    /* FAQ区域 */
    .pricing-faq {
        padding: var(--spacing-xxl) 0;
        background: linear-gradient(to bottom, transparent, rgba(10, 132, 255, 0.05), transparent);
    }
    
    .pricing-faq h2 {
        text-align: center;
        margin-bottom: var(--spacing-xl);
        font-size: 2.2rem;
        background: linear-gradient(to right, var(--secondary-color), var(--tertiary-color));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        display: inline-block;
        margin-left: auto;
        margin-right: auto;
    }
    
    .faq-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: var(--spacing-lg);
    }
    
    .faq-item {
        padding: var(--spacing-lg);
        background: var(--glass-background);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
    }
    
    .faq-item h3 {
        margin-bottom: var(--spacing-sm);
        font-size: 1.2rem;
        color: var(--accent-color);
    }
    
    .faq-item p {
        opacity: 0.8;
        line-height: 1.6;
    }
    
    /* CTA区域 */
    .pricing-cta {
        padding: var(--spacing-xxl) 0;
        text-align: center;
        background: linear-gradient(to right, rgba(10, 132, 255, 0.1), rgba(191, 90, 242, 0.1));
        border-radius: var(--border-radius-lg);
        margin: 0 var(--spacing-lg) var(--spacing-xl);
    }
    
    .pricing-cta h2 {
        font-size: 2rem;
        margin-bottom: var(--spacing-sm);
    }
    
    .pricing-cta p {
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto var(--spacing-lg);
        opacity: 0.9;
    }
    
    .cta-buttons {
        display: flex;
        justify-content: center;
        gap: var(--spacing-md);
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
    
    /* 响应式设计 */
    @media (max-width: 992px) {
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
        
        .faq-grid {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 768px) {
        .pricing-hero h1 {
            font-size: 2.5rem;
        }
        
        .pricing-hero p {
            font-size: 1.1rem;
        }
        
        .pricing-features-compare h2,
        .pricing-faq h2 {
            font-size: 1.8rem;
        }
        
        .cta-buttons {
            flex-direction: column;
            align-items: center;
        }
        
        .cta-buttons .btn {
            width: 100%;
            max-width: 300px;
            margin-bottom: var(--spacing-sm);
        }
    }
</style>

<!-- 价格页面脚本 -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 处理按月/按年付费切换
    const billingToggle = document.getElementById('billingToggle');
    const monthlyPrices = document.querySelectorAll('.price.monthly');
    const annuallyPrices = document.querySelectorAll('.price.annually');
    
    if (billingToggle && monthlyPrices.length && annuallyPrices.length) {
        billingToggle.addEventListener('change', function() {
            if (this.checked) {
                // 显示年付价格
                monthlyPrices.forEach(price => price.style.display = 'none');
                annuallyPrices.forEach(price => price.style.display = 'flex');
            } else {
                // 显示月付价格
                monthlyPrices.forEach(price => price.style.display = 'flex');
                annuallyPrices.forEach(price => price.style.display = 'none');
            }
        });
    }
});
</script>

<?php
// 渲染页面页脚
renderPageFooter();
?> 