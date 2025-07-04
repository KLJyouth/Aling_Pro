<?php
/**
 * AlingAi Pro - 自动化工具产品
 * 
 * AI驱动的业务流程自动化工具
 */

// 设置页面信息
$pageTitle = "自动化工具 - AlingAi Pro";
$pageDescription = "AI驱动的业务流程自动化工具，提高工作效率，减少重复性任务";
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
            <h1>自动化工具</h1>
            <p class="hero-subtitle">AI驱动的业务流程自动化，释放团队创造力</p>
            <div class="hero-cta">
                <a href="/pricing" class="btn btn-primary">查看价格</a>
                <a href="#demo" class="btn btn-outline">申请演示</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="/assets/images/products/automation-hero.svg" alt="AlingAi Pro 自动化工具">
        </div>
    </div>
</div>

<div class="product-features">
    <div class="container">
        <div class="section-header">
            <h2>为什么选择AlingAi自动化工具？</h2>
            <p>我们的AI驱动自动化平台让业务流程更高效、更智能</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-robot"></i>
                </div>
                <h3>智能工作流</h3>
                <p>AI辅助工作流设计，自动优化业务流程</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h3>文档处理</h3>
                <p>自动提取、分类和处理各类文档数据</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-code"></i>
                </div>
                <h3>无代码构建</h3>
                <p>直观的拖拽界面，无需编程即可创建自动化流程</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-plug"></i>
                </div>
                <h3>广泛集成</h3>
                <p>与300+常用业务应用无缝集成</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>安全可靠</h3>
                <p>企业级安全保障，确保数据和流程安全</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>性能分析</h3>
                <p>全面的自动化流程分析和优化建议</p>
            </div>
        </div>
    </div>
</div>

<div class="product-showcase">
    <div class="container">
        <div class="showcase-content">
            <div class="showcase-text">
                <h2>自动化重复性工作，释放团队创造力</h2>
                <p>AlingAi自动化工具帮助您自动化日常重复性任务，让团队专注于更有价值的创造性工作。</p>
                <ul class="feature-list">
                    <li>智能表单处理和数据录入</li>
                    <li>自动化报告生成和分发</li>
                    <li>客户服务流程自动化</li>
                    <li>审批流程自动化</li>
                    <li>跨系统数据同步</li>
                </ul>
            </div>
            <div class="showcase-image">
                <img src="/assets/images/products/automation-showcase.png" alt="自动化工具功能展示">
            </div>
        </div>
    </div>
</div>

<div class="product-use-cases">
    <div class="container">
        <div class="section-header">
            <h2>应用场景</h2>
            <p>AlingAi自动化工具适用于各行各业的流程自动化需求</p>
        </div>
        
        <div class="use-cases-grid">
            <div class="use-case-card">
                <div class="use-case-icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <h3>财务流程</h3>
                <p>自动化发票处理、费用报销、预算跟踪和财务报告生成</p>
            </div>
            
            <div class="use-case-card">
                <div class="use-case-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>人力资源</h3>
                <p>简化招聘流程、员工入职、绩效评估和培训管理</p>
            </div>
            
            <div class="use-case-card">
                <div class="use-case-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3>客户服务</h3>
                <p>自动化客户请求分类、响应和跟进，提高服务效率</p>
            </div>
            
            <div class="use-case-card">
                <div class="use-case-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <h3>项目管理</h3>
                <p>自动化任务分配、进度跟踪、提醒和报告生成</p>
            </div>
            
            <div class="use-case-card">
                <div class="use-case-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3>销售流程</h3>
                <p>自动化销售线索管理、报价生成、合同处理和客户跟进</p>
            </div>
            
            <div class="use-case-card">
                <div class="use-case-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h3>供应链管理</h3>
                <p>自动化订单处理、库存管理、供应商沟通和物流跟踪</p>
            </div>
        </div>
    </div>
</div>

<div class="product-testimonials">
    <div class="container">
        <div class="section-header">
            <h2>客户评价</h2>
            <p>听听我们的客户如何评价AlingAi自动化工具</p>
        </div>
        
        <div class="testimonials-slider">
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"使用AlingAi的自动化工具后，我们将文档处理时间减少了80%。以前需要团队花费整天处理的工作，现在只需几分钟就能完成。"</p>
                </div>
                <div class="testimonial-author">
                    <img src="/assets/images/testimonials/testimonial-4.jpg" alt="陈伟">
                    <div class="author-info">
                        <h4>陈伟</h4>
                        <p>某物流公司 运营总监</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"AlingAi的自动化平台帮助我们将客户响应时间从几小时缩短到几分钟，客户满意度提升了40%。最棒的是，我们不需要任何编程知识就能创建复杂的自动化流程。"</p>
                </div>
                <div class="testimonial-author">
                    <img src="/assets/images/testimonials/testimonial-5.jpg" alt="刘敏">
                    <div class="author-info">
                        <h4>刘敏</h4>
                        <p>某服务企业 客户服务经理</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"作为一家快速成长的初创企业，我们需要高效利用有限的资源。AlingAi的自动化工具让我们能够用更少的人力完成更多工作，为业务扩张提供了强大支持。"</p>
                </div>
                <div class="testimonial-author">
                    <img src="/assets/images/testimonials/testimonial-6.jpg" alt="赵强">
                    <div class="author-info">
                        <h4>赵强</h4>
                        <p>某科技初创企业 创始人</p>
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
                        <span class="amount">799</span>
                        <span class="period">/月</span>
                    </div>
                    <p>适合小型团队和初创企业</p>
                </div>
                <div class="pricing-features">
                    <ul>
                        <li><i class="fas fa-check"></i> 5个用户</li>
                        <li><i class="fas fa-check"></i> 10个自动化流程</li>
                        <li><i class="fas fa-check"></i> 每月5,000次自动化执行</li>
                        <li><i class="fas fa-check"></i> 50个集成连接器</li>
                        <li><i class="fas fa-check"></i> 基础模板库</li>
                        <li><i class="fas fa-check"></i> 邮件支持</li>
                        <li class="disabled"><i class="fas fa-times"></i> 高级AI自动化</li>
                        <li class="disabled"><i class="fas fa-times"></i> 自定义连接器</li>
                    </ul>
                </div>
                <div class="pricing-cta">
                    <a href="/contact?plan=automation-starter" class="btn btn-outline btn-block">联系销售</a>
                </div>
            </div>
            
            <div class="pricing-card featured">
                <div class="pricing-badge">最受欢迎</div>
                <div class="pricing-header">
                    <h3>专业版</h3>
                    <div class="price">
                        <span class="amount">2,499</span>
                        <span class="period">/月</span>
                    </div>
                    <p>适合中型企业和成长型团队</p>
                </div>
                <div class="pricing-features">
                    <ul>
                        <li><i class="fas fa-check"></i> 20个用户</li>
                        <li><i class="fas fa-check"></i> 50个自动化流程</li>
                        <li><i class="fas fa-check"></i> 每月50,000次自动化执行</li>
                        <li><i class="fas fa-check"></i> 200个集成连接器</li>
                        <li><i class="fas fa-check"></i> 高级模板库</li>
                        <li><i class="fas fa-check"></i> 优先邮件和电话支持</li>
                        <li><i class="fas fa-check"></i> 基础AI自动化功能</li>
                        <li class="disabled"><i class="fas fa-times"></i> 自定义连接器</li>
                    </ul>
                </div>
                <div class="pricing-cta">
                    <a href="/contact?plan=automation-professional" class="btn btn-primary btn-block">联系销售</a>
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
                        <li><i class="fas fa-check"></i> 无限自动化流程</li>
                        <li><i class="fas fa-check"></i> 无限自动化执行</li>
                        <li><i class="fas fa-check"></i> 300+集成连接器</li>
                        <li><i class="fas fa-check"></i> 企业级模板库</li>
                        <li><i class="fas fa-check"></i> 24/7专属支持</li>
                        <li><i class="fas fa-check"></i> 高级AI自动化功能</li>
                        <li><i class="fas fa-check"></i> 自定义连接器</li>
                    </ul>
                </div>
                <div class="pricing-cta">
                    <a href="/contact?plan=automation-enterprise" class="btn btn-outline btn-block">联系销售</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="product-demo" id="demo">
    <div class="container">
        <div class="demo-content">
            <div class="demo-text">
                <h2>亲身体验AlingAi自动化工具</h2>
                <p>申请免费演示，了解AlingAi自动化工具如何帮助您的企业提高工作效率，减少重复性任务。</p>
                <ul class="demo-features">
                    <li><i class="fas fa-check-circle"></i> 个性化产品演示</li>
                    <li><i class="fas fa-check-circle"></i> 针对您业务流程的自动化建议</li>
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
                        <label for="message">您希望自动化的业务流程</label>
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
            <p>关于AlingAi自动化工具的常见问题解答</p>
        </div>
        
        <div class="faq-list">
            <div class="faq-item">
                <div class="faq-question">
                    <h3>使用AlingAi自动化工具需要编程知识吗？</h3>
                    <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                </div>
                <div class="faq-answer">
                    <p>不需要。AlingAi自动化工具采用直观的拖拽式界面设计，无需编程知识即可创建复杂的自动化流程。我们提供丰富的预设模板和向导，帮助您快速上手。当然，对于希望进行高级自定义的用户，我们也提供了API和脚本接口。</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>AlingAi自动化工具支持哪些第三方应用集成？</h3>
                    <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                </div>
                <div class="faq-answer">
                    <p>我们支持300多个常用业务应用的集成，包括但不限于：CRM系统（Salesforce、HubSpot等）、协作工具（Office 365、Google Workspace等）、项目管理工具（Asana、Trello等）、营销平台、财务软件、人力资源系统等。企业版用户还可以获得自定义连接器开发服务，连接专有系统。</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>AlingAi自动化工具如何保障数据安全？</h3>
                    <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                </div>
                <div class="faq-answer">
                    <p>我们将数据安全视为首要任务。平台采用企业级安全措施，包括端到端加密、严格的访问控制、定期安全审计等。我们遵守GDPR、CCPA等全球数据保护法规，并提供本地部署选项，确保敏感数据不离开您的网络环境。所有自动化流程的执行记录都会被安全记录，便于审计和合规。</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>如何衡量自动化带来的效益？</h3>
                    <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                </div>
                <div class="faq-answer">
                    <p>我们的平台提供全面的分析仪表板，帮助您量化自动化带来的效益。您可以查看节省的时间、减少的错误率、提高的处理速度等关键指标。专业版和企业版用户还可以获得详细的ROI报告，帮助您评估自动化投资回报。我们的顾问团队也可以帮助您设计适合贵公司的效益衡量框架。</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>我们的团队需要多长时间才能上手使用？</h3>
                    <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                </div>
                <div class="faq-answer">
                    <p>大多数用户在几小时内就能创建第一个自动化流程。我们提供全面的在线培训资源、视频教程和文档，帮助您的团队快速上手。专业版和企业版用户还可以获得专属培训课程和实施支持。我们的目标是确保您的团队能够在最短时间内获得自动化带来的效益。</p>
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
