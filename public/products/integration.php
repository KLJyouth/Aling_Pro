<?php
/**
 * AlingAi Pro - 系统集成产品
 * 
 * AI驱动的系统集成解决方案
 */

// 设置页面信息
$pageTitle = "系统集成 - AlingAi Pro";
$pageDescription = "AI驱动的系统集成解决方案，实现跨系统数据互通和业务流程整合";
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
            <h1>系统集成</h1>
            <p class="hero-subtitle">AI驱动的系统集成平台，实现无缝数据互通和业务流程整合</p>
            <div class="hero-cta">
                <a href="/pricing" class="btn btn-primary">查看价格</a>
                <a href="#demo" class="btn btn-outline">申请演示</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="/assets/images/products/integration-hero.svg" alt="AlingAi Pro 系统集成平台">
        </div>
    </div>
</div>

<div class="product-features">
    <div class="container">
        <div class="section-header">
            <h2>为什么选择AlingAi系统集成平台？</h2>
            <p>我们的AI驱动集成平台让系统互联更简单、更智能</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <h3>智能连接器</h3>
                <p>AI辅助的系统连接器，自动适应API变化和数据结构</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h3>数据转换</h3>
                <p>智能数据映射和转换，确保跨系统数据一致性</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-code"></i>
                </div>
                <h3>无代码集成</h3>
                <p>直观的可视化界面，无需编程即可创建复杂集成</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
                <h3>实时监控</h3>
                <p>全面的集成监控和故障自动修复</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>安全传输</h3>
                <p>端到端加密和严格的访问控制，确保数据安全</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <h3>高性能架构</h3>
                <p>分布式处理引擎，支持高吞吐量数据传输</p>
            </div>
        </div>
    </div>
</div>

<div class="product-showcase">
    <div class="container">
        <div class="showcase-content">
            <div class="showcase-text">
                <h2>打破系统孤岛，实现数据互通</h2>
                <p>AlingAi系统集成平台帮助您连接企业内外的各类系统，实现数据互通和业务流程整合。</p>
                <ul class="feature-list">
                    <li>跨系统数据同步和整合</li>
                    <li>API管理和监控</li>
                    <li>事件驱动的集成流程</li>
                    <li>批处理和实时数据处理</li>
                    <li>遗留系统现代化集成</li>
                </ul>
            </div>
            <div class="showcase-image">
                <img src="/assets/images/products/integration-showcase.png" alt="系统集成平台功能展示">
            </div>
        </div>
    </div>
</div>

<div class="product-use-cases">
    <div class="container">
        <div class="section-header">
            <h2>应用场景</h2>
            <p>AlingAi系统集成平台适用于各行各业的系统集成需求</p>
        </div>
        
        <div class="use-cases-grid">
            <div class="use-case-card">
                <div class="use-case-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3>电商平台集成</h3>
                <p>连接电商平台、库存系统、物流系统和支付系统，实现端到端业务流程</p>
            </div>
            
            <div class="use-case-card">
                <div class="use-case-icon">
                    <i class="fas fa-hospital"></i>
                </div>
                <h3>医疗系统互联</h3>
                <p>整合电子病历、实验室系统、医疗设备和保险系统，提供全面患者视图</p>
            </div>
            
            <div class="use-case-card">
                <div class="use-case-icon">
                    <i class="fas fa-university"></i>
                </div>
                <h3>金融服务集成</h3>
                <p>连接核心银行系统、支付网关、风控系统和客户服务平台</p>
            </div>
            
            <div class="use-case-card">
                <div class="use-case-icon">
                    <i class="fas fa-industry"></i>
                </div>
                <h3>制造业系统整合</h3>
                <p>集成ERP、MES、SCM和CRM系统，实现从订单到交付的全流程管理</p>
            </div>
            
            <div class="use-case-card">
                <div class="use-case-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3>教育平台互联</h3>
                <p>整合学习管理系统、学生信息系统、评估工具和内容库</p>
            </div>
            
            <div class="use-case-card">
                <div class="use-case-icon">
                    <i class="fas fa-cloud"></i>
                </div>
                <h3>云服务集成</h3>
                <p>连接各类云服务和本地系统，构建混合云架构</p>
            </div>
        </div>
    </div>
</div>

<div class="product-testimonials">
    <div class="container">
        <div class="section-header">
            <h2>客户评价</h2>
            <p>听听我们的客户如何评价AlingAi系统集成平台</p>
        </div>
        
        <div class="testimonials-slider">
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"AlingAi的系统集成平台帮助我们将10多个独立系统整合成一个统一的业务平台。以前需要几个月的集成项目，现在只需几周就能完成。"</p>
                </div>
                <div class="testimonial-author">
                    <img src="/assets/images/testimonials/testimonial-7.jpg" alt="杨军">
                    <div class="author-info">
                        <h4>杨军</h4>
                        <p>某制造企业 IT总监</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"我们使用AlingAi的集成平台连接了我们的电商系统、ERP和物流系统，实现了实时库存更新和订单处理。系统稳定性和性能都令人印象深刻。"</p>
                </div>
                <div class="testimonial-author">
                    <img src="/assets/images/testimonials/testimonial-8.jpg" alt="周丽">
                    <div class="author-info">
                        <h4>周丽</h4>
                        <p>某电商企业 技术总监</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"作为一家金融机构，系统安全和稳定性对我们至关重要。AlingAi的集成平台不仅满足了我们严格的安全要求，还大大提升了我们的业务敏捷性。"</p>
                </div>
                <div class="testimonial-author">
                    <img src="/assets/images/testimonials/testimonial-9.jpg" alt="吴刚">
                    <div class="author-info">
                        <h4>吴刚</h4>
                        <p>某金融机构 CIO</p>
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
                        <span class="amount">1,999</span>
                        <span class="period">/月</span>
                    </div>
                    <p>适合小型集成需求</p>
                </div>
                <div class="pricing-features">
                    <ul>
                        <li><i class="fas fa-check"></i> 5个集成端点</li>
                        <li><i class="fas fa-check"></i> 每月100万次数据传输</li>
                        <li><i class="fas fa-check"></i> 50个预构建连接器</li>
                        <li><i class="fas fa-check"></i> 基础数据转换</li>
                        <li><i class="fas fa-check"></i> 标准监控工具</li>
                        <li><i class="fas fa-check"></i> 邮件支持</li>
                        <li class="disabled"><i class="fas fa-times"></i> 高级API管理</li>
                        <li class="disabled"><i class="fas fa-times"></i> 自定义连接器</li>
                    </ul>
                </div>
                <div class="pricing-cta">
                    <a href="/contact?plan=integration-starter" class="btn btn-outline btn-block">联系销售</a>
                </div>
            </div>
            
            <div class="pricing-card featured">
                <div class="pricing-badge">最受欢迎</div>
                <div class="pricing-header">
                    <h3>专业版</h3>
                    <div class="price">
                        <span class="amount">4,999</span>
                        <span class="period">/月</span>
                    </div>
                    <p>适合中型企业和复杂集成</p>
                </div>
                <div class="pricing-features">
                    <ul>
                        <li><i class="fas fa-check"></i> 20个集成端点</li>
                        <li><i class="fas fa-check"></i> 每月500万次数据传输</li>
                        <li><i class="fas fa-check"></i> 200个预构建连接器</li>
                        <li><i class="fas fa-check"></i> 高级数据转换</li>
                        <li><i class="fas fa-check"></i> 高级监控和警报</li>
                        <li><i class="fas fa-check"></i> 优先邮件和电话支持</li>
                        <li><i class="fas fa-check"></i> 基础API管理</li>
                        <li class="disabled"><i class="fas fa-times"></i> 自定义连接器</li>
                    </ul>
                </div>
                <div class="pricing-cta">
                    <a href="/contact?plan=integration-professional" class="btn btn-primary btn-block">联系销售</a>
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
                        <li><i class="fas fa-check"></i> 无限集成端点</li>
                        <li><i class="fas fa-check"></i> 无限数据传输</li>
                        <li><i class="fas fa-check"></i> 300+预构建连接器</li>
                        <li><i class="fas fa-check"></i> 企业级数据转换</li>
                        <li><i class="fas fa-check"></i> 全面监控和自动修复</li>
                        <li><i class="fas fa-check"></i> 24/7专属支持</li>
                        <li><i class="fas fa-check"></i> 高级API管理</li>
                        <li><i class="fas fa-check"></i> 自定义连接器开发</li>
                    </ul>
                </div>
                <div class="pricing-cta">
                    <a href="/contact?plan=integration-enterprise" class="btn btn-outline btn-block">联系销售</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="product-demo" id="demo">
    <div class="container">
        <div class="demo-content">
            <div class="demo-text">
                <h2>亲身体验AlingAi系统集成平台</h2>
                <p>申请免费演示，了解AlingAi系统集成平台如何帮助您的企业实现系统互联和数据整合。</p>
                <ul class="demo-features">
                    <li><i class="fas fa-check-circle"></i> 个性化产品演示</li>
                    <li><i class="fas fa-check-circle"></i> 针对您业务的集成方案</li>
                    <li><i class="fas fa-check-circle"></i> 专家解答您的问题</li>
                    <li><i class="fas fa-check-circle"></i> 免费概念验证机会</li>
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
                        <label for="message">您的集成需求</label>
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
            <p>关于AlingAi系统集成平台的常见问题解答</p>
        </div>
        
        <div class="faq-list">
            <div class="faq-item">
                <div class="faq-question">
                    <h3>AlingAi系统集成平台支持哪些系统和应用？</h3>
                    <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                </div>
                <div class="faq-answer">
                    <p>我们的平台支持300多种常见企业系统和应用，包括但不限于：ERP系统（SAP、Oracle等）、CRM系统（Salesforce、Microsoft Dynamics等）、HR系统、财务系统、电商平台、支付系统、物流系统、制造系统等。我们还提供REST API、SOAP、JDBC、FTP等通用连接器，以及自定义连接器开发服务，可以连接几乎任何系统。</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>实施一个集成项目通常需要多长时间？</h3>
                    <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                </div>
                <div class="faq-answer">
                    <p>这取决于集成的复杂性和涉及的系统数量。使用我们的预构建连接器和模板，简单的集成项目可以在几天内完成。中等复杂度的项目通常需要2-4周，而大型企业级集成可能需要1-3个月。我们的AI辅助设计和自动化测试工具可以显著缩短传统集成项目的时间线。</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>AlingAi系统集成平台如何确保数据安全？</h3>
                    <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                </div>
                <div class="faq-answer">
                    <p>我们采用多层次的安全措施保护您的数据：所有数据传输采用TLS/SSL加密；支持数据加密存储；严格的访问控制和身份验证；详细的审计日志；合规性认证（如ISO 27001、SOC 2等）。我们还提供本地部署选项，敏感数据可以完全保留在您的网络环境中。</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>平台如何处理系统API变更？</h3>
                    <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                </div>
                <div class="faq-answer">
                    <p>我们的AI驱动连接器能够智能适应API变化。平台会持续监控API健康状态，自动检测变更，并在许多情况下自动调整集成流程。对于重大API变更，系统会提前通知管理员，并提供调整建议。我们的连接器团队也会定期更新预构建连接器，确保与最新API版本兼容。</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>平台能处理多大规模的数据传输？</h3>
                    <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                </div>
                <div class="faq-answer">
                    <p>我们的平台采用分布式架构设计，可以根据需求自动扩展处理能力。企业版客户已经在使用我们的平台每天处理数亿条记录。平台支持批处理和实时流处理，可以根据您的业务需求和性能要求进行优化配置。对于特别大规模的数据传输需求，我们的解决方案架构师可以设计专门的高性能集成方案。</p>
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
