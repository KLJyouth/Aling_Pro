<?php
/**
 * 解决方案主页
 * 
 * 展示AlingAi Pro的所有解决方案
 */

// 引入配置文件
require_once __DIR__ . '/../config/config_loader.php';

// 页面标题
$pageTitle = "解决方案 - AlingAi Pro";
$pageDescription = "探索AlingAi Pro提供的行业解决方案，包括企业应用、教育培训、医疗健康、金融科技和零售商业等多个领域。";

// 添加页面特定的CSS
$additionalCSS = ['/css/solutions.css'];

// 开始输出缓冲
ob_start();
?>

<!-- 页面主要内容 -->
<main class="solutions-page">
    <!-- 英雄区域 -->
    <section class="hero-section quantum-gradient">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">行业解决方案</h1>
                <p class="hero-subtitle">为各行业量身打造的AI赋能方案</p>
            </div>
        </div>
    </section>

    <!-- 解决方案概述 -->
    <section class="solutions-overview">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">我们的行业解决方案</h2>
                <p class="section-description">针对不同行业的特定需求和挑战，AlingAi Pro提供量身定制的AI解决方案</p>
            </div>
            
            <div class="solutions-grid">
                <!-- 企业应用 -->
                <div class="solution-card glass-card">
                    <div class="solution-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3 class="solution-title">企业应用</h3>
                    <p class="solution-description">优化业务流程，提升团队协作效率，实现企业智能化转型。</p>
                    <a href="/solutions/enterprise.php" class="solution-link">了解更多 <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <!-- 教育培训 -->
                <div class="solution-card glass-card">
                    <div class="solution-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3 class="solution-title">教育培训</h3>
                    <p class="solution-description">个性化学习体验，智能内容生成，提高教学效率和学习成效。</p>
                    <a href="/solutions/education.php" class="solution-link">了解更多 <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <!-- 医疗健康 -->
                <div class="solution-card glass-card">
                    <div class="solution-icon">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <h3 class="solution-title">医疗健康</h3>
                    <p class="solution-description">辅助诊断、医疗数据分析和患者管理，提升医疗服务质量。</p>
                    <a href="/solutions/healthcare.php" class="solution-link">了解更多 <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <!-- 金融科技 -->
                <div class="solution-card glass-card">
                    <div class="solution-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="solution-title">金融科技</h3>
                    <p class="solution-description">智能风控、量化分析、客户服务，推动金融服务智能化升级。</p>
                    <a href="/solutions/finance.php" class="solution-link">了解更多 <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <!-- 零售商业 -->
                <div class="solution-card glass-card">
                    <div class="solution-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3 class="solution-title">零售商业</h3>
                    <p class="solution-description">智能客服、个性化推荐、供应链优化，提升零售业务绩效。</p>
                    <a href="/solutions/retail.php" class="solution-link">了解更多 <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <!-- 量子安全 -->
                <div class="solution-card glass-card">
                    <div class="solution-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="solution-title">量子安全</h3>
                    <p class="solution-description">前沿量子加密和安全架构，为企业数据和系统提供最高级别保护。</p>
                    <a href="/solutions/quantum-security.php" class="solution-link">了解更多 <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 我们的方法论 -->
    <section class="methodology-section quantum-bg-light">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">我们的方法论</h2>
                <p class="section-description">AlingAi Pro的解决方案实施方法论，确保每个项目成功落地</p>
            </div>
            
            <div class="methodology-steps">
                <div class="step glass-card">
                    <div class="step-number">01</div>
                    <div class="step-content">
                        <h3>需求评估</h3>
                        <p>深入了解您的业务流程、痛点和目标，确定最适合的解决方案方向。</p>
                    </div>
                </div>
                
                <div class="step glass-card">
                    <div class="step-number">02</div>
                    <div class="step-content">
                        <h3>解决方案设计</h3>
                        <p>基于评估结果，我们的专家团队设计符合您特定需求的解决方案架构。</p>
                    </div>
                </div>
                
                <div class="step glass-card">
                    <div class="step-number">03</div>
                    <div class="step-content">
                        <h3>模型训练与优化</h3>
                        <p>利用您的业务数据训练AI模型，持续优化以达到最佳效果。</p>
                    </div>
                </div>
                
                <div class="step glass-card">
                    <div class="step-number">04</div>
                    <div class="step-content">
                        <h3>系统集成</h3>
                        <p>将解决方案无缝集成到您现有的系统和工作流程中，确保平稳过渡。</p>
                    </div>
                </div>
                
                <div class="step glass-card">
                    <div class="step-number">05</div>
                    <div class="step-content">
                        <h3>培训与支持</h3>
                        <p>为您的团队提供全面培训，确保他们能够充分利用解决方案的所有功能。</p>
                    </div>
                </div>
                
                <div class="step glass-card">
                    <div class="step-number">06</div>
                    <div class="step-content">
                        <h3>持续优化</h3>
                        <p>根据实际使用情况和反馈，持续优化解决方案，确保长期价值。</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 核心优势 -->
    <section class="advantages-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">我们的核心优势</h2>
                <p class="section-description">为什么选择AlingAi Pro的解决方案</p>
            </div>
            
            <div class="advantages-grid">
                <div class="advantage-card glass-card">
                    <div class="advantage-icon">
                        <i class="fas fa-microchip"></i>
                    </div>
                    <h3>先进AI技术</h3>
                    <p>采用最先进的深度学习和自然语言处理技术，确保解决方案的智能性和准确性。</p>
                </div>
                
                <div class="advantage-card glass-card">
                    <div class="advantage-icon">
                        <i class="fas fa-industry"></i>
                    </div>
                    <h3>行业专业知识</h3>
                    <p>我们的团队拥有丰富的行业经验，深入了解各行业特点和需求。</p>
                </div>
                
                <div class="advantage-card glass-card">
                    <div class="advantage-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h3>定制化能力</h3>
                    <p>每个解决方案都可以根据您的特定需求进行定制，确保最佳匹配度。</p>
                </div>
                
                <div class="advantage-card glass-card">
                    <div class="advantage-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h3>可衡量的ROI</h3>
                    <p>我们关注实际业务成果，确保解决方案能带来明确的投资回报。</p>
                </div>
                
                <div class="advantage-card glass-card">
                    <div class="advantage-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>安全与合规</h3>
                    <p>所有解决方案都符合最高安全标准和行业法规要求。</p>
                </div>
                
                <div class="advantage-card glass-card">
                    <div class="advantage-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>全程支持</h3>
                    <p>从需求分析到实施后支持，我们全程陪伴您的AI转型之旅。</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 成功案例 -->
    <section class="case-studies-section quantum-bg-light">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">成功案例</h2>
                <p class="section-description">看看我们如何帮助客户解决业务挑战</p>
            </div>
            
            <div class="case-studies-grid">
                <!-- 案例1 -->
                <div class="case-study-card glass-card">
                    <div class="case-study-image">
                        <img src="/assets/images/case-studies/case-1.jpg" alt="某大型制造企业案例" class="rounded-image">
                    </div>
                    <div class="case-study-content">
                        <div class="case-study-tag">制造业</div>
                        <h3>某大型制造企业智能工厂转型</h3>
                        <p>通过部署我们的AI解决方案，该制造企业实现了生产效率提升30%，能源消耗减少25%，产品缺陷率降低40%。</p>
                        <a href="/case-studies/manufacturing" class="read-more">阅读详情 <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                
                <!-- 案例2 -->
                <div class="case-study-card glass-card">
                    <div class="case-study-image">
                        <img src="/assets/images/case-studies/case-2.jpg" alt="某在线教育平台案例" class="rounded-image">
                    </div>
                    <div class="case-study-content">
                        <div class="case-study-tag">教育</div>
                        <h3>某在线教育平台个性化学习系统</h3>
                        <p>我们帮助该教育平台构建了AI驱动的个性化学习系统，学生学习效率提升35%，课程完成率提高50%。</p>
                        <a href="/case-studies/education" class="read-more">阅读详情 <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                
                <!-- 案例3 -->
                <div class="case-study-card glass-card">
                    <div class="case-study-image">
                        <img src="/assets/images/case-studies/case-3.jpg" alt="某商业银行案例" class="rounded-image">
                    </div>
                    <div class="case-study-content">
                        <div class="case-study-tag">金融</div>
                        <h3>某商业银行智能风控系统</h3>
                        <p>为该银行开发的AI风控系统帮助其降低了45%的欺诈损失，同时提高了20%的贷款审批速度。</p>
                        <a href="/case-studies/banking" class="read-more">阅读详情 <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="view-all-cases">
                <a href="/case-studies" class="btn btn-primary">查看全部案例</a>
            </div>
        </div>
    </section>
    
    <!-- 客户评价 -->
    <section class="testimonials-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">客户评价</h2>
                <p class="section-description">听听我们客户的真实反馈</p>
            </div>
            
            <div class="testimonials-grid">
                <div class="testimonial glass-card">
                    <div class="testimonial-content">
                        <p>"AlingAi Pro的解决方案帮助我们完成了数字化转型，极大地提升了业务效率和客户体验。他们不仅提供技术，更是我们的战略伙伴。"</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="/assets/images/testimonials/testimonial-1.jpg" alt="客户头像" class="author-image">
                        <div class="author-info">
                            <h4>张总监</h4>
                            <p>某大型制造企业 CIO</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial glass-card">
                    <div class="testimonial-content">
                        <p>"我们选择AlingAi Pro是因为他们真正理解教育行业的需求。他们的解决方案不仅技术先进，而且真正解决了我们的核心痛点。"</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="/assets/images/testimonials/testimonial-2.jpg" alt="客户头像" class="author-image">
                        <div class="author-info">
                            <h4>李院长</h4>
                            <p>某知名高校 信息化办公室</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial glass-card">
                    <div class="testimonial-content">
                        <p>"AlingAi Pro的量子安全解决方案为我们提供了最高级别的数据保护。在当今数字威胁不断增加的环境中，这给了我们极大的安心。"</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="/assets/images/testimonials/testimonial-3.jpg" alt="客户头像" class="author-image">
                        <div class="author-info">
                            <h4>王经理</h4>
                            <p>某金融机构 安全负责人</p>
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
                <h2>准备好开始您的AI转型之旅了吗？</h2>
                <p>联系我们的行业专家，了解AlingAi Pro如何为您的业务带来变革性价值。</p>
                <div class="cta-buttons">
                    <a href="/contact" class="btn btn-light">联系我们</a>
                    <a href="/request-demo" class="btn btn-outline-light">申请演示</a>
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