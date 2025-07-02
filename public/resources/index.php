<?php
/**
 * 资源页面
 * 
 * 展示AlingAi Pro的所有资源，包括博客、案例研究、网络研讨会和白皮书等
 */

// 引入配置文件
require_once __DIR__ . '/../config/config.php';

// 页面标题
$pageTitle = "资源中心 - AlingAi Pro";
$pageDescription = "探索AlingAi Pro的学习资源，包括博客文章、案例研究、网络研讨会和白皮书等，帮助您充分利用AI技术。";

// 添加页面特定的CSS
$additionalCSS = ['/css/resources.css'];

// 开始输出缓冲
ob_start();
?>

<!-- 页面主要内容 -->
<main class="resources-page">
    <!-- 英雄区域 -->
    <section class="hero-section quantum-gradient">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">资源中心</h1>
                <p class="hero-subtitle">探索我们的知识库，了解如何最大化利用AlingAi Pro</p>
            </div>
        </div>
    </section>

    <!-- 资源导航 -->
    <section class="resources-nav-section">
        <div class="container">
            <div class="resources-nav">
                <a href="/resources" class="nav-item active">全部</a>
                <a href="/resources/blog" class="nav-item">博客</a>
                <a href="/resources/case-studies" class="nav-item">案例研究</a>
                <a href="/resources/webinars" class="nav-item">网络研讨会</a>
                <a href="/resources/white-papers" class="nav-item">白皮书</a>
            </div>
            
            <div class="resources-filter">
                <div class="search-box">
                    <input type="text" placeholder="搜索资源..." id="resourceSearch">
                    <i class="fas fa-search"></i>
                </div>
                
                <div class="filter-dropdown">
                    <button class="filter-button">
                        筛选 <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="filter-menu">
                        <div class="filter-group">
                            <h4>资源类型</h4>
                            <label class="filter-checkbox">
                                <input type="checkbox" value="blog" checked> 博客
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox" value="case-study" checked> 案例研究
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox" value="webinar" checked> 网络研讨会
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox" value="white-paper" checked> 白皮书
                            </label>
                        </div>
                        <div class="filter-group">
                            <h4>行业</h4>
                            <label class="filter-checkbox">
                                <input type="checkbox" value="all" checked> 全部行业
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox" value="finance"> 金融
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox" value="healthcare"> 医疗
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox" value="education"> 教育
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox" value="retail"> 零售
                            </label>
                        </div>
                        <div class="filter-actions">
                            <button class="btn btn-sm" id="resetFilters">重置</button>
                            <button class="btn btn-primary btn-sm" id="applyFilters">应用</button>
                        </div>
                    </div>
                </div>
                
                <div class="sort-dropdown">
                    <button class="sort-button">
                        最新发布 <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="sort-menu">
                        <a href="#" class="sort-option active" data-sort="date-desc">最新发布</a>
                        <a href="#" class="sort-option" data-sort="date-asc">最早发布</a>
                        <a href="#" class="sort-option" data-sort="popular">最受欢迎</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 特色资源 -->
    <section class="featured-resources">
        <div class="container">
            <div class="featured-resource glass-card">
                <div class="featured-content">
                    <div class="resource-tag">白皮书</div>
                    <h2>量子计算与人工智能：下一代AI技术的未来</h2>
                    <p>探索量子计算如何彻底改变AI领域，以及AlingAi Pro如何利用这些技术为企业带来革命性价值。</p>
                    <div class="resource-meta">
                        <span class="meta-date"><i class="far fa-calendar"></i> 2023年6月15日</span>
                        <span class="meta-read-time"><i class="far fa-clock"></i> 阅读时间: 12分钟</span>
                    </div>
                    <a href="/resources/white-papers/quantum-computing-and-ai" class="btn btn-primary">阅读白皮书</a>
                </div>
                <div class="featured-image">
                    <img src="/assets/images/resources/quantum-computing-ai.jpg" alt="量子计算与AI">
                </div>
            </div>
        </div>
    </section>
    
    <!-- 资源列表 -->
    <section class="resources-list-section">
        <div class="container">
            <div class="resources-grid" id="resourcesGrid">
                <!-- 博客文章 -->
                <div class="resource-card glass-card" data-type="blog" data-industry="finance,technology">
                    <div class="resource-image">
                        <img src="/assets/images/resources/blog-1.jpg" alt="金融科技AI应用趋势">
                        <div class="resource-tag">博客</div>
                    </div>
                    <div class="resource-content">
                        <h3><a href="/resources/blog/fintech-ai-trends-2023">2023年金融科技AI应用七大趋势</a></h3>
                        <p>探索AI如何改变金融服务行业，从智能风控到个性化投资建议。</p>
                        <div class="resource-meta">
                            <span class="meta-date">2023年7月2日</span>
                            <span class="meta-read-time">5分钟阅读</span>
                        </div>
                    </div>
                </div>
                
                <!-- 案例研究 -->
                <div class="resource-card glass-card" data-type="case-study" data-industry="healthcare">
                    <div class="resource-image">
                        <img src="/assets/images/resources/case-study-1.jpg" alt="医疗诊断AI应用案例">
                        <div class="resource-tag">案例研究</div>
                    </div>
                    <div class="resource-content">
                        <h3><a href="/resources/case-studies/healthcare-diagnosis-improvement">某三甲医院利用AI提升诊断准确率案例</a></h3>
                        <p>了解某知名医院如何通过AlingAi Pro的医疗AI助手将诊断准确率提高35%。</p>
                        <div class="resource-meta">
                            <span class="meta-date">2023年6月20日</span>
                            <span class="meta-read-time">8分钟阅读</span>
                        </div>
                    </div>
                </div>
                
                <!-- 网络研讨会 -->
                <div class="resource-card glass-card" data-type="webinar" data-industry="education">
                    <div class="resource-image">
                        <img src="/assets/images/resources/webinar-1.jpg" alt="AI教育应用网络研讨会">
                        <div class="resource-tag">网络研讨会</div>
                    </div>
                    <div class="resource-content">
                        <h3><a href="/resources/webinars/ai-in-education-2023">AI如何重塑个性化学习体验</a></h3>
                        <p>加入我们的网络研讨会，了解教育机构如何利用AI提供真正个性化的学习体验。</p>
                        <div class="resource-meta">
                            <span class="meta-date">2023年6月28日</span>
                            <span class="meta-duration">45分钟</span>
                        </div>
                    </div>
                </div>
                
                <!-- 白皮书 -->
                <div class="resource-card glass-card" data-type="white-paper" data-industry="retail">
                    <div class="resource-image">
                        <img src="/assets/images/resources/white-paper-1.jpg" alt="零售业AI转型白皮书">
                        <div class="resource-tag">白皮书</div>
                    </div>
                    <div class="resource-content">
                        <h3><a href="/resources/white-papers/retail-ai-transformation">零售业AI转型实战指南</a></h3>
                        <p>详细解析零售企业如何通过AI技术实现全渠道优化和个性化客户体验。</p>
                        <div class="resource-meta">
                            <span class="meta-date">2023年5月15日</span>
                            <span class="meta-read-time">15分钟阅读</span>
                        </div>
                    </div>
                </div>
                
                <!-- 博客文章 -->
                <div class="resource-card glass-card" data-type="blog" data-industry="technology">
                    <div class="resource-image">
                        <img src="/assets/images/resources/blog-2.jpg" alt="自然语言处理的未来">
                        <div class="resource-tag">博客</div>
                    </div>
                    <div class="resource-content">
                        <h3><a href="/resources/blog/future-of-nlp-2023">自然语言处理的未来：超越GPT的技术展望</a></h3>
                        <p>探索下一代NLP技术如何突破当前大语言模型的局限，带来更精确的语义理解。</p>
                        <div class="resource-meta">
                            <span class="meta-date">2023年6月10日</span>
                            <span class="meta-read-time">7分钟阅读</span>
                        </div>
                    </div>
                </div>
                
                <!-- 案例研究 -->
                <div class="resource-card glass-card" data-type="case-study" data-industry="finance">
                    <div class="resource-image">
                        <img src="/assets/images/resources/case-study-2.jpg" alt="银行业AI风控案例">
                        <div class="resource-tag">案例研究</div>
                    </div>
                    <div class="resource-content">
                        <h3><a href="/resources/case-studies/bank-fraud-detection">某国有银行利用AI降低欺诈风险案例</a></h3>
                        <p>了解某国有银行如何通过AlingAi Pro的智能风控系统减少40%的欺诈损失。</p>
                        <div class="resource-meta">
                            <span class="meta-date">2023年5月25日</span>
                            <span class="meta-read-time">10分钟阅读</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="resources-pagination">
                <button class="pagination-button active">1</button>
                <button class="pagination-button">2</button>
                <button class="pagination-button">3</button>
                <span class="pagination-ellipsis">...</span>
                <button class="pagination-button">8</button>
                <button class="pagination-button pagination-next">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </section>
    
    <!-- 订阅区块 -->
    <section class="subscribe-section quantum-gradient">
        <div class="container">
            <div class="subscribe-content glass-card">
                <h2>订阅我们的资源更新</h2>
                <p>及时获取最新AI技术趋势、案例研究和实用指南，帮助您的业务保持领先。</p>
                <form class="subscribe-form" id="subscribeForm">
                    <div class="form-group">
                        <input type="email" placeholder="您的邮箱地址" required>
                        <button type="submit" class="btn btn-light">订阅</button>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" id="privacyConsent" required>
                        <label for="privacyConsent">
                            我同意接收AlingAi Pro的电子邮件通讯，并已阅读<a href="/privacy-policy">隐私政策</a>。
                        </label>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 筛选功能
    const filterButton = document.querySelector('.filter-button');
    const filterMenu = document.querySelector('.filter-menu');
    
    filterButton.addEventListener('click', function() {
        filterMenu.classList.toggle('active');
    });
    
    // 排序功能
    const sortButton = document.querySelector('.sort-button');
    const sortMenu = document.querySelector('.sort-menu');
    
    sortButton.addEventListener('click', function() {
        sortMenu.classList.toggle('active');
    });
    
    const sortOptions = document.querySelectorAll('.sort-option');
    sortOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            
            // 移除所有选项的活跃状态
            sortOptions.forEach(opt => opt.classList.remove('active'));
            
            // 将当前选项设为活跃
            this.classList.add('active');
            
            // 更新排序按钮文本
            sortButton.innerHTML = this.textContent + ' <i class="fas fa-chevron-down"></i>';
            
            // 关闭排序菜单
            sortMenu.classList.remove('active');
            
            // 这里可以添加实际排序逻辑
            // sortResources(this.getAttribute('data-sort'));
        });
    });
    
    // 搜索功能
    const searchInput = document.getElementById('resourceSearch');
    searchInput.addEventListener('input', function() {
        // 这里可以添加实际搜索逻辑
        // searchResources(this.value);
    });
    
    // 筛选应用按钮
    const applyFiltersButton = document.getElementById('applyFilters');
    applyFiltersButton.addEventListener('click', function() {
        // 关闭筛选菜单
        filterMenu.classList.remove('active');
        
        // 获取选中的筛选条件
        const selectedTypes = Array.from(document.querySelectorAll('.filter-checkbox input[value^="blog"]:checked, .filter-checkbox input[value^="case"]:checked, .filter-checkbox input[value^="webinar"]:checked, .filter-checkbox input[value^="white"]:checked'))
            .map(checkbox => checkbox.value);
            
        const selectedIndustries = Array.from(document.querySelectorAll('.filter-checkbox input[value="all"]:checked, .filter-checkbox input[value="finance"]:checked, .filter-checkbox input[value="healthcare"]:checked, .filter-checkbox input[value="education"]:checked, .filter-checkbox input[value="retail"]:checked'))
            .map(checkbox => checkbox.value);
        
        // 这里可以添加实际筛选逻辑
        // filterResources(selectedTypes, selectedIndustries);
    });
    
    // 重置筛选按钮
    const resetFiltersButton = document.getElementById('resetFilters');
    resetFiltersButton.addEventListener('click', function() {
        // 重置所有复选框
        document.querySelectorAll('.filter-checkbox input').forEach(checkbox => {
            if (checkbox.value === 'all' || checkbox.value === 'blog' || checkbox.value === 'case-study' || checkbox.value === 'webinar' || checkbox.value === 'white-paper') {
                checkbox.checked = true;
            } else {
                checkbox.checked = false;
            }
        });
    });
    
    // 订阅表单提交
    const subscribeForm = document.getElementById('subscribeForm');
    subscribeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // 模拟表单提交
        const emailInput = this.querySelector('input[type="email"]');
        const email = emailInput.value.trim();
        
        if (email) {
            // 这里可以添加实际订阅逻辑
            // subscribeEmail(email);
            
            // 显示成功消息
            alert('订阅成功！感谢您的关注。');
            emailInput.value = '';
        }
    });
    
    // 点击页面其他位置关闭下拉菜单
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.filter-dropdown') && !e.target.closest('.filter-button')) {
            filterMenu.classList.remove('active');
        }
        
        if (!e.target.closest('.sort-dropdown') && !e.target.closest('.sort-button')) {
            sortMenu.classList.remove('active');
        }
    });
});
</script>

<?php
// 获取缓冲内容
$pageContent = ob_get_clean();

// 使用页面模板
require_once __DIR__ . '/../templates/page.php';
?> 