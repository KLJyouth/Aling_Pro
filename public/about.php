<?php
/**
 * 关于我们页面
 * 
 * 展示公司信息、团队和使命愿景
 */

// 引入配置文件
require_once __DIR__ . '/config/config.php';

// 页面标题
$pageTitle = "关于我们 - AlingAi Pro";
$pageDescription = "了解AlingAi Pro团队、我们的使命愿景以及我们如何致力于通过先进的AI技术改变世界。";

// 添加页面特定的CSS
$additionalCSS = ['/css/about.css'];

// 开始输出缓冲
ob_start();
?>

<!-- 页面主要内容 -->
<main class="about-page">
    <!-- 英雄区域 -->
    <section class="hero-section quantum-gradient">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">关于 AlingAi Pro</h1>
                <p class="hero-subtitle">我们致力于通过量子级AI技术赋能未来</p>
            </div>
        </div>
    </section>

    <!-- 我们的故事 -->
    <section class="our-story-section">
        <div class="container">
            <h2 class="section-title">我们的故事</h2>
            <div class="story-content">
                <div class="story-text">
                    <p>AlingAi Pro 成立于2022年，由一群对人工智能充满热情的技术专家和企业家共同创立。我们的创始团队拥有来自顶尖科技公司和研究机构的丰富经验，致力于将最前沿的AI技术转化为实用的解决方案。</p>
                    <p>在短短几年内，我们已经发展成为AI领域的领导者，服务于全球数千家企业和组织，帮助他们实现数字化转型和智能化升级。</p>
                </div>
                <div class="story-image">
                    <img src="assets/images/about/company-story.jpg" alt="AlingAi Pro 发展历程" class="rounded-image">
                </div>
            </div>
        </div>
    </section>

    <!-- 使命与愿景 -->
    <section class="mission-vision-section quantum-bg-light">
        <div class="container">
            <div class="mission-vision-grid">
                <div class="mission-box glass-card">
                    <h3>我们的使命</h3>
                    <p>通过创新的AI技术赋能各行各业，让人工智能成为推动社会进步和解决全球挑战的关键力量。</p>
                </div>
                <div class="vision-box glass-card">
                    <h3>我们的愿景</h3>
                    <p>成为全球最受信赖的AI技术提供商，打造一个人类与人工智能和谐共存、共同进步的未来。</p>
                </div>
                <div class="values-box glass-card">
                    <h3>我们的价值观</h3>
                    <ul class="values-list">
                        <li><strong>创新</strong> - 不断探索技术边界</li>
                        <li><strong>诚信</strong> - 以道德和透明度为基础</li>
                        <li><strong>协作</strong> - 共同创造更大价值</li>
                        <li><strong>责任</strong> - 对社会和环境负责</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- 领导团队 -->
    <section class="team-section">
        <div class="container">
            <h2 class="section-title">我们的领导团队</h2>
            <p class="section-description">由行业专家组成的团队，致力于推动AI技术的边界</p>
            
            <div class="team-grid">
                <!-- 团队成员 1 -->
                <div class="team-member glass-card">
                    <div class="member-image">
                        <img src="assets/images/about/team-1.jpg" alt="张明 - 首席执行官" class="rounded-image">
                    </div>
                    <div class="member-info">
                        <h3 class="member-name">张明</h3>
                        <p class="member-title">首席执行官 & 联合创始人</p>
                        <p class="member-bio">前谷歌AI研究员，拥有15年人工智能和机器学习经验。斯坦福大学计算机科学博士。</p>
                        <div class="member-social">
                            <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- 团队成员 2 -->
                <div class="team-member glass-card">
                    <div class="member-image">
                        <img src="assets/images/about/team-2.jpg" alt="李华 - 首席技术官" class="rounded-image">
                    </div>
                    <div class="member-info">
                        <h3 class="member-name">李华</h3>
                        <p class="member-title">首席技术官</p>
                        <p class="member-bio">前亚马逊高级工程师，专注于大规模分布式系统和深度学习架构。麻省理工学院计算机科学硕士。</p>
                        <div class="member-social">
                            <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- 团队成员 3 -->
                <div class="team-member glass-card">
                    <div class="member-image">
                        <img src="assets/images/about/team-3.jpg" alt="王芳 - 首席产品官" class="rounded-image">
                    </div>
                    <div class="member-info">
                        <h3 class="member-name">王芳</h3>
                        <p class="member-title">首席产品官</p>
                        <p class="member-bio">拥有10年产品管理经验，曾在多家顶级科技公司领导AI产品开发。哈佛商学院MBA。</p>
                        <div class="member-social">
                            <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-medium"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- 团队成员 4 -->
                <div class="team-member glass-card">
                    <div class="member-image">
                        <img src="assets/images/about/team-4.jpg" alt="陈强 - 研究总监" class="rounded-image">
                    </div>
                    <div class="member-info">
                        <h3 class="member-name">陈强</h3>
                        <p class="member-title">AI研究总监</p>
                        <p class="member-bio">前OpenAI研究科学家，在自然语言处理和强化学习领域发表过多篇论文。剑桥大学计算机科学博士。</p>
                        <div class="member-social">
                            <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-researchgate"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 公司成就 -->
    <section class="achievements-section quantum-bg-light">
        <div class="container">
            <h2 class="section-title">我们的成就</h2>
            <div class="achievements-grid">
                <div class="achievement-card glass-card">
                    <div class="achievement-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3 class="achievement-title">2023年度AI创新企业</h3>
                    <p class="achievement-description">被《科技创新》杂志评为"年度最具创新力AI企业"</p>
                </div>
                
                <div class="achievement-card glass-card">
                    <div class="achievement-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="achievement-title">服务超过5000家企业</h3>
                    <p class="achievement-description">全球范围内帮助5000多家企业实现AI转型</p>
                </div>
                
                <div class="achievement-card glass-card">
                    <div class="achievement-icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h3 class="achievement-title">全球影响力</h3>
                    <p class="achievement-description">我们的解决方案已在30多个国家部署</p>
                </div>
                
                <div class="achievement-card glass-card">
                    <div class="achievement-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h3 class="achievement-title">12项AI专利</h3>
                    <p class="achievement-description">在自然语言处理和计算机视觉领域拥有多项专利</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 合作伙伴 -->
    <section class="partners-section">
        <div class="container">
            <h2 class="section-title">我们的合作伙伴</h2>
            <p class="section-description">与行业领导者共同创新</p>
            
            <div class="partners-logo-grid">
                <div class="partner-logo">
                    <img src="assets/images/partners/partner-1.png" alt="合作伙伴 1" class="grayscale-hover">
                </div>
                <div class="partner-logo">
                    <img src="assets/images/partners/partner-2.png" alt="合作伙伴 2" class="grayscale-hover">
                </div>
                <div class="partner-logo">
                    <img src="assets/images/partners/partner-3.png" alt="合作伙伴 3" class="grayscale-hover">
                </div>
                <div class="partner-logo">
                    <img src="assets/images/partners/partner-4.png" alt="合作伙伴 4" class="grayscale-hover">
                </div>
                <div class="partner-logo">
                    <img src="assets/images/partners/partner-5.png" alt="合作伙伴 5" class="grayscale-hover">
                </div>
                <div class="partner-logo">
                    <img src="assets/images/partners/partner-6.png" alt="合作伙伴 6" class="grayscale-hover">
                </div>
            </div>
        </div>
    </section>

    <!-- 加入我们 -->
    <section class="join-us-section quantum-gradient">
        <div class="container">
            <div class="join-us-content glass-card">
                <h2 class="section-title light">加入我们的团队</h2>
                <p class="section-description light">我们正在寻找热情、有才华的人才加入我们的团队，共同推动AI技术的发展</p>
                <div class="cta-buttons">
                    <a href="/careers.php" class="btn btn-light">查看职位空缺</a>
                    <a href="/contact.php" class="btn btn-outline-light">联系我们</a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
// 获取缓冲内容
$pageContent = ob_get_clean();

// 使用页面模板
require_once __DIR__ . '/templates/page.php';
?> 