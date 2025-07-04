<?php
/**
 * AlingAi Pro - 数据分析产品
 * 
 * 数据分析和可视化工具
 */

// 设置页面信息
$pageTitle = "数据分析 - AlingAi Pro";
$pageDescription = "强大的AI驱动数据分析和可视化工具，帮助企业从数据中获取洞察";
$additionalCSS = [
    "/css/products.css"
];
$additionalJS = [
    ["src" => "/js/products.js", "defer" => true]
];

// 包含页面模板
require_once __DIR__ . "/../templates/page.php";

// 渲染页面头部
renderPageHeader();
?>

<div class="product-hero">
    <div class="container">
        <div class="hero-content">
            <span class="product-badge">AlingAi Pro</span>
            <h1>数据分析</h1>
            <p class="hero-subtitle">AI驱动的数据分析和可视化平台，让数据洞察触手可及</p>
            <div class="hero-cta">
                <a href="/pricing" class="btn btn-primary">查看价格</a>
                <a href="#demo" class="btn btn-outline">申请演示</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="/assets/images/products/data-analysis-hero.svg" alt="AlingAi Pro 数据分析平台">
        </div>
    </div>
</div>

<div class="product-features">
    <div class="container">
        <div class="section-header">
            <h2>为什么选择AlingAi数据分析平台？</h2>
            <p>我们的AI驱动数据分析平台让复杂数据变得简单易懂</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-brain"></i>
                </div>
                <h3>AI驱动分析</h3>
                <p>利用先进的机器学习算法自动发现数据中的模式和洞察</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3>交互式可视化</h3>
                <p>直观的交互式数据可视化，让数据故事一目了然</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h3>自然语言查询</h3>
                <p>使用自然语言提问，获取数据洞察，无需复杂查询语言</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-database"></i>
                </div>
                <h3>多源数据集成</h3>
                <p>轻松连接和整合多种数据源，构建统一数据视图</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3>实时分析</h3>
                <p>实时数据处理和分析，支持即时业务决策</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h3>企业级安全</h3>
                <p>严格的数据安全和访问控制，保护您的敏感信息</p>
            </div>
        </div>
    </div>
</div>

<div class="product-showcase">
    <div class="container">
        <div class="showcase-content">
            <div class="showcase-text">
                <h2>将数据转化为洞察</h2>
                <p>AlingAi数据分析平台帮助您从海量数据中发现有价值的洞察，支持更明智的业务决策。</p>
                <ul class="feature-list">
                    <li>自动异常检测和趋势分析</li>
                    <li>预测性分析和预测建模</li>
                    <li>交互式仪表板和报表</li>
                    <li>自定义分析工作流</li>
                    <li>数据驱动的推荐引擎</li>
                </ul>
            </div>
            <div class="showcase-image">
                <img src="/assets/images/products/data-analysis-showcase.png" alt="数据分析平台功能展示">
            </div>
        </div>
    </div>
</div>

<div class="product-use-cases">
    <div class="container">
        <div class="section-header">
            <h2>应用场景</h2>
            <p>AlingAi数据分析平台适用于各行各业的数据分析需求</p>
        </div>
        
        <div class="use-cases-grid">
            <div class="use-case-card">
                <div class="use-case-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>商业智能</h3>
                <p>构建交互式仪表板，监控关键业务指标，发现增长机会</p>
            </div>
            
            <div class="use-case-card">
                <div class="use-case-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3>销售和营销分析</h3>
                <p>分析销售趋势，评估营销活动效果，优化销售策略</p>
            </div>
            
            <div class="use-case-card">
                <div class="use-case-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>客户行为分析</h3>
                <p>深入了解客户行为模式，提供个性化体验，提高客户留存</p>
            </div>
            
            <div class="use-case-card">
                <div class="use-case-icon">
                    <i class="fas fa-industry"></i>
                </div>
                <h3>运营优化</h3>
                <p>识别运营瓶颈，优化流程，提高资源利用效率</p>
            </div>
            
            <div class="use-case-card">
                <div class="use-case-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <h3>财务分析</h3>
                <p>分析财务数据，预测现金流，识别成本节约机会</p>
            </div>
            
            <div class="use-case-card">
                <div class="use-case-icon">
                    <i class="fas fa-flask"></i>
                </div>
                <h3>研发数据分析</h3>
                <p>分析实验数据，加速研发进程，提高创新效率</p>
            </div>
        </div>
    </div>
</div>

<div class="product-testimonials">
    <div class="container">
        <div class="section-header">
            <h2>客户评价</h2>
            <p>听听我们的客户如何评价AlingAi数据分析平台</p>
        </div>
        
        <div class="testimonials-slider">
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"AlingAi的数据分析平台彻底改变了我们分析业务数据的方式。以前需要数据团队花费数天完成的分析工作，现在我们的业务人员可以在几分钟内完成。"</p>
                </div>
                <div class="testimonial-author">
                    <img src="/assets/images/testimonials/testimonial-1.jpg" alt="张明">
                    <div class="author-info">
                        <h4>张明</h4>
                        <p>某科技公司 数据分析总监</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"我们使用AlingAi的数据分析平台后，销售转化率提升了35%。平台的预测分析功能帮助我们提前发现市场趋势，调整产品策略。"</p>
                </div>
                <div class="testimonial-author">
                    <img src="/assets/images/testimonials/testimonial-2.jpg" alt="李华">
                    <div class="author-info">
                        <h4>李华</h4>
                        <p>某电商平台 营销总监</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"作为一家中小企业，我们没有专业的数据分析团队。AlingAi的平台让我们能够像大企业一样利用数据驱动决策，这在竞争中给了我们巨大优势。"</p>
                </div>
                <div class="testimonial-author">
                    <img src="/assets/images/testimonials/testimonial-3.jpg" alt="王芳">
                    <div class="author-info">
                        <h4>王芳</h4>
                        <p>某零售企业 CEO</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="product-pricing" id="pricing">
    <div class="container">
        <div class="section-header">
            <h2>灵活的价格方案</h2>
            <p>选择最适合您业务需求的方案</p>
        </div>
        
        <div class="pricing-cards">
            <div class="pricing-card">
                <div class="pricing-header">
                    <h3>入门版</h3>
                    <div class="price">
                        <span class="amount">999</span>
                        <span class="period">/月</span>
                    </div>
                    <p>适合小型团队和初创企业</p>
                </div>
                <div class="pricing-features">
                    <ul>
                        <li><i class="fas fa-check"></i> 5个用户</li>
                        <li><i class="fas fa-check"></i> 100GB数据存储</li>
                        <li><i class="fas fa-check"></i> 基础数据可视化</li>
                        <li><i class="fas fa-check"></i> 5个数据源连接</li>
                        <li><i class="fas fa-check"></i> 标准报表模板</li>
                        <li><i class="fas fa-check"></i> 邮件支持</li>
                        <li class="disabled"><i class="fas fa-times"></i> 高级AI分析</li>
                        <li class="disabled"><i class="fas fa-times"></i> 自定义分析模型</li>
                    </ul>
                </div>
                <div class="pricing-cta">
                    <a href="/contact?plan=data-analysis-starter" class="btn btn-outline btn-block">联系销售</a>
                </div>
            </div>
            
            <div class="pricing-card featured">
                <div class="pricing-badge">最受欢迎</div>
                <div class="pricing-header">
                    <h3>专业版</h3>
                    <div class="price">
                        <span class="amount">2,999</span>
                        <span class="period">/月</span>
                    </div>
                    <p>适合中型企业和成长型团队</p>
                </div>
                <div class="pricing-features">
                    <ul>
                        <li><i class="fas fa-check"></i> 20个用户</li>
                        <li><i class="fas fa-check"></i> 500GB数据存储</li>
                        <li><i class="fas fa-check"></i> 高级数据可视化</li>
                        <li><i class="fas fa-check"></i> 20个数据源连接</li>
                        <li><i class="fas fa-check"></i> 自定义报表</li>
                        <li><i class="fas fa-check"></i> 优先邮件和电话支持</li>
                        <li><i class="fas fa-check"></i> 基础AI分析功能</li>
                        <li class="disabled"><i class="fas fa-times"></i> 自定义分析模型</li>
                    </ul>
                </div>
                <div class="pricing-cta">
                    <a href="/contact?plan=data-analysis-professional" class="btn btn-primary btn-block">联系销售</a>
                </div>
            </div>
            
            <div class="pricing-card">
                <div class="pricing-header">
                    <h3>企业版</h3>
                    <div class="price">
                        <span class="amount">定制</span>
                    </div>
                    <p>适合大型企业和高级需求</p>
                </div>
                <div class="pricing-features">
                    <ul>
                        <li><i class="fas fa-check"></i> 无限用户</li>
                        <li><i class="fas fa-check"></i> 定制数据存储</li>
                        <li><i class="fas fa-check"></i> 企业级数据可视化</li>
                        <li><i class="fas fa-check"></i> 无限数据源连接</li>
                        <li><i class="fas fa-check"></i> 完全自定义报表</li>
                        <li><i class="fas fa-check"></i> 24/7专属支持</li>
                        <li><i class="fas fa-check"></i> 高级AI分析功能</li>
                        <li><i class="fas fa-check"></i> 自定义分析模型</li>
                    </ul>
                </div>
                <div class="pricing-cta">
                    <a href="/contact?plan=data-analysis-enterprise" class="btn btn-outline btn-block">联系销售</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="product-demo" id="demo">
    <div class="container">
        <div class="demo-content">
            <div class="demo-text">
                <h2>亲身体验AlingAi数据分析平台</h2>
                <p>申请免费演示，了解AlingAi数据分析平台如何帮助您的企业从数据中获取更多价值。</p>
                <ul class="demo-features">
                    <li><i class="fas fa-check-circle"></i> 个性化产品演示</li>
                    <li><i class="fas fa-check-circle"></i> 针对您业务的定制方案</li>
                    <li><i class="fas fa-check-circle"></i> 专家解答您的问题</li>
                    <li><i class="fas fa-check-circle"></i> 免费试用机会</li>
                </ul>
            </div>
            <div class="demo-form">
                <form id="demoRequestForm" action="/api/demo-request" method="post">
                    <h3>申请演示</h3>
                    <div class="form-group">
                        <label for="name">姓名</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="company">公司名称</label>
                        <input type="text" id="company" name="company" required>
                    </div>
                    <div class="form-group">
                        <label for="email">企业邮箱</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">电话</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="message">您的需求</label>
                        <textarea id="message" name="message" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">提交申请</button>
                    </div>
                    <p class="form-note">我们将在1个工作日内联系您安排演示</p>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="product-faq">
    <div class="container">
        <div class="section-header">
            <h2>常见问题</h2>
            <p>关于AlingAi数据分析平台的常见问题解答</p>
        </div>
        
        <div class="faq-list">
            <div class="faq-item">
                <div class="faq-question">
                    <h3>AlingAi数据分析平台支持哪些数据源？</h3>
                    <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                </div>
                <div class="faq-answer">
                    <p>我们的平台支持多种数据源，包括但不限于：SQL数据库（MySQL、PostgreSQL、SQL Server等）、NoSQL数据库（MongoDB、Elasticsearch等）、Excel/CSV文件、Google Analytics、Salesforce、各类API数据源等。如果您有特殊的数据源需求，我们的团队可以为您提供定制化的连接器。</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>使用AlingAi数据分析平台需要具备编程技能吗？</h3>
                    <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                </div>
                <div class="faq-answer">
                    <p>不需要。AlingAi数据分析平台设计为对非技术用户友好的系统。通过直观的拖拽界面和自然语言查询功能，业务人员无需编程技能即可创建复杂的数据可视化和分析报告。当然，对于希望进行更高级自定义的用户，我们也提供了编程接口。</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>AlingAi数据分析平台如何保障数据安全？</h3>
                    <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                </div>
                <div class="faq-answer">
                    <p>我们非常重视数据安全。平台采用企业级安全措施，包括端到端加密、严格的访问控制、定期安全审计等。我们遵守GDPR、CCPA等全球数据保护法规，并可以根据需求提供本地部署选项，确保敏感数据不离开您的网络环境。</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>平台是否支持实时数据分析？</h3>
                    <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                </div>
                <div class="faq-answer">
                    <p>是的，我们的平台支持实时数据处理和分析。您可以设置实时数据流，创建动态更新的仪表板，实时监控关键业务指标。专业版和企业版用户可以设置基于实时数据的自动化警报和通知。</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>我可以将AlingAi数据分析平台与现有系统集成吗？</h3>
                    <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                </div>
                <div class="faq-answer">
                    <p>可以。我们提供全面的API和集成选项，可以无缝连接到您现有的CRM、ERP、营销自动化等系统。企业版用户还可以获得定制化集成服务，确保与您特定的业务系统完美配合。</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // FAQ切换效果
        const faqItems = document.querySelectorAll(".faq-item");
        
        faqItems.forEach(item => {
            const question = item.querySelector(".faq-question");
            const answer = item.querySelector(".faq-answer");
            const toggle = item.querySelector(".faq-toggle");
            
            question.addEventListener("click", function() {
                // 关闭其他所有FAQ
                faqItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove("active");
                        otherItem.querySelector(".faq-answer").style.maxHeight = "0px";
                        otherItem.querySelector(".faq-toggle i").className = "fas fa-plus";
                    }
                });
                
                // 切换当前FAQ
                item.classList.toggle("active");
                
                if (item.classList.contains("active")) {
                    answer.style.maxHeight = answer.scrollHeight + "px";
                    toggle.innerHTML = "<i class=\"fas fa-minus\"></i>";
                } else {
                    answer.style.maxHeight = "0px";
                    toggle.innerHTML = "<i class=\"fas fa-plus\"></i>";
                }
            });
        });
        
        // 表单提交处理
        const demoForm = document.getElementById("demoRequestForm");
        if (demoForm) {
            demoForm.addEventListener("submit", function(e) {
                e.preventDefault();
                
                // 这里可以添加表单验证和AJAX提交
                alert("感谢您的申请！我们的团队将在1个工作日内与您联系。");
                demoForm.reset();
            });
        }
    });
</script>

<?php
// 渲染页面页脚
renderPageFooter();
?>
