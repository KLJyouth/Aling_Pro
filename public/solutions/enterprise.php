<?php
/**
 * 企业应用解决方案页面
 * 
 * 展示AlingAi Pro的企业应用解决方案
 */

// 引入配置文件
require_once __DIR__ . '/../config/config_loader.php';

// 页面标题
$pageTitle = "企业应用解决方案 - AlingAi Pro";
$pageDescription = "探索AlingAi Pro提供的企业应用解决方案，帮助企业优化业务流程，提升团队协作效率，实现企业智能化转型。";

// 添加页面特定的CSS
$additionalCSS = ['/css/solutions.css'];

// 开始输出缓冲
ob_start();
?>

<!-- 页面主要内容 -->
<main class="solution-page">
    <!-- 英雄区域 -->
    <section class="hero-section quantum-gradient">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">企业应用解决方案</h1>
                <p class="hero-subtitle">优化业务流程，提升团队协作效率，实现企业智能化转型</p>
            </div>
        </div>
    </section>

    <!-- 解决方案概述 -->
    <section class="solution-overview">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">解决方案概述</h2>
                <p class="section-description">AlingAi Pro的企业应用解决方案专为现代企业设计，帮助您实现业务流程优化和数字化转型</p>
            </div>
            
            <div class="solution-content">
                <div class="solution-text">
                    <p>在当今竞争激烈的商业环境中，企业需要不断优化业务流程，提高运营效率，以保持竞争优势。AlingAi Pro的企业应用解决方案通过先进的AI技术，帮助企业实现智能化转型，从而提升生产力、降低成本、增强创新能力。</p>
                    <p>我们的解决方案覆盖企业运营的各个方面，包括智能文档处理、自动化工作流、智能客户服务、数据分析和决策支持等。无论您是寻求优化特定业务流程，还是进行全面的数字化转型，我们都能提供量身定制的解决方案。</p>
                </div>
                <div class="solution-image">
                    <img src="/assets/images/solutions/enterprise-overview.jpg" alt="企业应用解决方案概述" class="rounded-image">
                </div>
            </div>
        </div>
    </section>
    
    <!-- 核心功能 -->
    <section class="features-section quantum-bg-light">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">核心功能</h2>
                <p class="section-description">我们的企业应用解决方案提供全面的功能，满足企业各种需求</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card glass-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3 class="feature-title">智能文档处理</h3>
                    <p class="feature-description">自动提取、分类和处理各种文档中的信息，大幅减少手动数据录入工作。</p>
                </div>
                
                <div class="feature-card glass-card">
                    <div class="feature-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <h3 class="feature-title">工作流自动化</h3>
                    <p class="feature-description">自动化重复性任务和工作流程，提高效率，减少人为错误。</p>
                </div>
                
                <div class="feature-card glass-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="feature-title">智能客户服务</h3>
                    <p class="feature-description">AI驱动的客户服务解决方案，提供24/7全天候支持，提高客户满意度。</p>
                </div>
                
                <div class="feature-card glass-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="feature-title">商业智能分析</h3>
                    <p class="feature-description">从企业数据中挖掘有价值的见解，支持数据驱动的决策。</p>
                </div>
                
                <div class="feature-card glass-card">
                    <div class="feature-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h3 class="feature-title">团队协作工具</h3>
                    <p class="feature-description">增强团队沟通和协作的智能工具，提高团队生产力。</p>
                </div>
                
                <div class="feature-card glass-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="feature-title">企业安全管理</h3>
                    <p class="feature-description">保护企业数据和系统安全的先进解决方案。</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 应用场景 -->
    <section class="use-cases-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">应用场景</h2>
                <p class="section-description">我们的企业应用解决方案适用于各种业务场景</p>
            </div>
            
            <div class="use-cases-grid">
                <div class="use-case glass-card">
                    <h3>人力资源管理</h3>
                    <p>自动化简历筛选、员工入职流程和绩效评估，提高HR团队效率。</p>
                </div>
                
                <div class="use-case glass-card">
                    <h3>财务流程优化</h3>
                    <p>自动化发票处理、费用报销和财务报告生成，减少财务部门工作量。</p>
                </div>
                
                <div class="use-case glass-card">
                    <h3>客户关系管理</h3>
                    <p>智能客户数据分析和个性化营销，提升客户转化率和满意度。</p>
                </div>
                
                <div class="use-case glass-card">
                    <h3>供应链优化</h3>
                    <p>预测性分析和自动化库存管理，优化供应链效率。</p>
                </div>
                
                <div class="use-case glass-card">
                    <h3>项目管理</h3>
                    <p>智能项目规划、资源分配和进度跟踪，确保项目按时完成。</p>
                </div>
                
                <div class="use-case glass-card">
                    <h3>合规与风险管理</h3>
                    <p>自动化合规检查和风险评估，降低企业合规风险。</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 客户案例 -->
    <section class="case-study-section quantum-bg-light">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">客户案例</h2>
                <p class="section-description">了解企业如何使用我们的解决方案取得成功</p>
            </div>
            
            <div class="case-study glass-card">
                <div class="case-study-image">
                    <img src="/assets/images/case-studies/enterprise-case-1.jpg" alt="某大型制造企业案例" class="rounded-image">
                </div>
                <div class="case-study-content">
                    <h3>某大型制造企业流程优化</h3>
                    <p>该制造企业通过部署AlingAi Pro的企业应用解决方案，实现了生产流程的智能化管理和优化。系统自动分析生产数据，识别瓶颈环节，并提供优化建议，帮助企业提高了生产效率30%，减少了能源消耗25%。</p>
                    <div class="case-study-results">
                        <div class="result-item">
                            <span class="result-number">30%</span>
                            <span class="result-label">生产效率提升</span>
                        </div>
                        <div class="result-item">
                            <span class="result-number">25%</span>
                            <span class="result-label">能源消耗减少</span>
                        </div>
                        <div class="result-item">
                            <span class="result-number">40%</span>
                            <span class="result-label">产品缺陷率降低</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="case-study glass-card">
                <div class="case-study-content">
                    <h3>某跨国金融服务公司客户服务优化</h3>
                    <p>该金融服务公司部署了AlingAi Pro的智能客户服务解决方案，实现了客户查询的自动处理和智能路由。系统能够理解客户意图，提供个性化回复，并在必要时将客户转接给合适的人工客服。实施后，客户服务响应时间减少了60%，客户满意度提高了35%。</p>
                    <div class="case-study-results">
                        <div class="result-item">
                            <span class="result-number">60%</span>
                            <span class="result-label">响应时间减少</span>
                        </div>
                        <div class="result-item">
                            <span class="result-number">35%</span>
                            <span class="result-label">客户满意度提升</span>
                        </div>
                        <div class="result-item">
                            <span class="result-number">45%</span>
                            <span class="result-label">客服成本降低</span>
                        </div>
                    </div>
                </div>
                <div class="case-study-image">
                    <img src="/assets/images/case-studies/enterprise-case-2.jpg" alt="某跨国金融服务公司案例" class="rounded-image">
                </div>
            </div>
        </div>
    </section>
    
    <!-- 实施流程 -->
    <section class="implementation-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">实施流程</h2>
                <p class="section-description">我们提供端到端的实施服务，确保解决方案顺利部署并发挥最大价值</p>
            </div>
            
            <div class="implementation-steps">
                <div class="step glass-card">
                    <div class="step-number">01</div>
                    <div class="step-content">
                        <h3>需求评估</h3>
                        <p>我们的专家团队将与您深入交流，了解您的业务流程、痛点和目标，确定最适合的解决方案方向。</p>
                    </div>
                </div>
                
                <div class="step glass-card">
                    <div class="step-number">02</div>
                    <div class="step-content">
                        <h3>解决方案设计</h3>
                        <p>基于评估结果，我们设计符合您特定需求的解决方案架构，包括技术选型、功能规划和集成方案。</p>
                    </div>
                </div>
                
                <div class="step glass-card">
                    <div class="step-number">03</div>
                    <div class="step-content">
                        <h3>定制开发</h3>
                        <p>我们的开发团队根据设计方案进行定制开发，确保解决方案满足您的特定需求。</p>
                    </div>
                </div>
                
                <div class="step glass-card">
                    <div class="step-number">04</div>
                    <div class="step-content">
                        <h3>系统集成</h3>
                        <p>将解决方案无缝集成到您现有的系统和工作流程中，确保数据流通和功能协同。</p>
                    </div>
                </div>
                
                <div class="step glass-card">
                    <div class="step-number">05</div>
                    <div class="step-content">
                        <h3>培训与部署</h3>
                        <p>为您的团队提供全面培训，并协助您顺利部署解决方案，确保平稳过渡。</p>
                    </div>
                </div>
                
                <div class="step glass-card">
                    <div class="step-number">06</div>
                    <div class="step-content">
                        <h3>持续优化</h3>
                        <p>部署后，我们提供持续的支持和优化服务，根据使用情况和反馈不断完善解决方案。</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 常见问题 -->
    <section class="faq-section quantum-bg-light">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">常见问题</h2>
                <p class="section-description">关于企业应用解决方案的常见问题解答</p>
            </div>
            
            <div class="faq-list">
                <div class="faq-item glass-card">
                    <div class="faq-question">
                        <h3>实施企业应用解决方案需要多长时间？</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>实施时间取决于解决方案的复杂性和范围。简单的流程自动化可能只需几周，而全面的企业应用集成可能需要3-6个月。我们会在项目开始前提供详细的时间表。</p>
                    </div>
                </div>
                
                <div class="faq-item glass-card">
                    <div class="faq-question">
                        <h3>解决方案是否可以与我们现有的系统集成？</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>是的，我们的解决方案设计为可与各种企业系统集成，包括ERP、CRM、HR系统等。我们提供标准API和自定义集成选项，确保与您现有系统的无缝协作。</p>
                    </div>
                </div>
                
                <div class="faq-item glass-card">
                    <div class="faq-question">
                        <h3>如何确保数据安全？</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>数据安全是我们的首要考虑因素。我们的解决方案采用企业级加密技术、严格的访问控制和合规性措施，确保您的数据安全。我们还定期进行安全审计和更新，以应对新的安全威胁。</p>
                    </div>
                </div>
                
                <div class="faq-item glass-card">
                    <div class="faq-question">
                        <h3>实施后是否提供技术支持？</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>是的，我们提供全面的技术支持服务，包括24/7在线支持、定期维护和系统更新。我们还提供不同级别的服务等级协议(SLA)，以满足您的特定需求。</p>
                    </div>
                </div>
                
                <div class="faq-item glass-card">
                    <div class="faq-question">
                        <h3>解决方案的投资回报周期是多久？</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>根据我们的客户经验，企业应用解决方案的投资回报周期通常在6-18个月之间。具体取决于实施范围、业务流程复杂性和优化目标。我们会在项目初期提供详细的ROI分析。</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 行动号召 -->
    <section class="cta-section quantum-gradient">
        <div class="container">
            <div class="cta-content glass-card">
                <h2>准备好优化您的企业流程了吗？</h2>
                <p>联系我们的解决方案专家，了解如何通过AI技术提升您企业的运营效率和竞争力。</p>
                <div class="cta-buttons">
                    <a href="/contact" class="btn btn-light">联系我们</a>
                    <a href="/demo" class="btn btn-outline-light">预约演示</a>
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
