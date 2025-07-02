<?php
/**
 * 文档页面
 * 
 * @version 1.0.0
 * @author AlingAi Team
 * @copyright 2024 AlingAi Corporation
 */

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 启动会话
session_start();

// 获取文档类型（默认为概述）
$docType = $_GET['type'] ?? 'overview';

// 页面信息设置
$pageTitle = '文档中心 - AlingAi Pro';
$pageDescription = 'AlingAi Pro 的开发文档、API参考和使用指南';
$pageKeywords = 'AlingAi, 文档, API, 开发, 指南, 教程';

// 包含页面模板
require_once __DIR__ . '/templates/page.php';

// 渲染页面头部
renderPageHeader();

// 获取当前文档页面
$docPage = isset($_GET['page']) ? $_GET['page'] : 'overview';
$docPage = preg_replace('/[^a-zA-Z0-9_\-]/', '', $docPage); // 安全过滤

// 可用的文档页面列表
$availableDocs = [
    'overview' => '概述',
    'getting-started' => '快速开始',
    'api-reference' => 'API参考',
    'sdk' => 'SDK文档',
    'tutorials' => '教程',
    'examples' => '示例代码',
    'faq' => '常见问题'
];

// 检查文档页面是否存在
if (!array_key_exists($docPage, $availableDocs)) {
    $docPage = 'overview';
}

// 文档分类
$docCategories = [
    'guide' => [
        'title' => '使用指南',
        'pages' => [
            'overview' => '概述',
            'getting-started' => '快速开始',
            'tutorials' => '教程',
            'faq' => '常见问题'
        ]
    ],
    'api' => [
        'title' => 'API文档',
        'pages' => [
            'api-reference' => 'API参考',
            'sdk' => 'SDK文档',
            'examples' => '示例代码'
        ]
    ]
];
?>

<!-- 文档页面容器 -->
<div class="docs-container">
    <!-- 侧边栏导航 -->
    <aside class="docs-sidebar">
        <div class="docs-sidebar-header">
            <h2>文档中心</h2>
            <button id="sidebarToggle" class="sidebar-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <div class="docs-sidebar-content">
            <div class="docs-search">
                <input type="text" placeholder="搜索文档..." id="docsSearch">
                <i class="fas fa-search"></i>
            </div>
            
            <nav class="docs-nav">
                <?php foreach ($docCategories as $categoryKey => $category): ?>
                    <div class="docs-nav-section">
                        <h3><?= htmlspecialchars($category['title']) ?></h3>
                        <ul>
                            <?php foreach ($category['pages'] as $pageKey => $pageTitle): ?>
                                <li class="<?= $docPage === $pageKey ? 'active' : '' ?>">
                                    <a href="?page=<?= $pageKey ?>">
                                        <?= htmlspecialchars($pageTitle) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </nav>
            
            <div class="docs-sidebar-footer">
                <a href="/contact" class="btn btn-outline btn-sm">
                    <i class="fas fa-question-circle"></i> 需要帮助？
                </a>
                <a href="https://github.com/alingai/alingai-pro" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fab fa-github"></i> GitHub
                </a>
            </div>
        </div>
    </aside>
    
    <!-- 文档内容 -->
    <main class="docs-content">
        <div class="docs-content-header">
            <h1><?= htmlspecialchars($availableDocs[$docPage]) ?></h1>
            <div class="docs-actions">
                <button class="btn btn-sm" id="fontSizeToggle">
                    <i class="fas fa-font"></i>
                </button>
                <button class="btn btn-sm" id="printDoc">
                    <i class="fas fa-print"></i>
                </button>
                <div class="docs-version-selector">
                    <span>v6.0.0</span>
                    <i class="fas fa-chevron-down"></i>
                    <div class="version-dropdown">
                        <a href="#" class="active">v6.0.0 (当前)</a>
                        <a href="#">v5.2.1</a>
                        <a href="#">v5.1.0</a>
                        <a href="#">v5.0.0</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="docs-content-body">
            <?php
            // 包含文档内容
            $docFilePath = __DIR__ . '/templates/docs/' . $docPage . '.php';
            if (file_exists($docFilePath)) {
                include $docFilePath;
            } else {
                echo '<div class="docs-error">
                        <h2>文档页面不存在</h2>
                        <p>抱歉，您请求的文档页面不存在或尚未创建。</p>
                        <a href="?page=overview" class="btn">返回概述</a>
                      </div>';
            }
            ?>
        </div>
        
        <div class="docs-content-footer">
            <div class="docs-pagination">
                <?php
                // 获取当前页面在所有页面中的索引
                $allPages = array_keys($availableDocs);
                $currentIndex = array_search($docPage, $allPages);
                
                // 上一页
                if ($currentIndex > 0) {
                    $prevPage = $allPages[$currentIndex - 1];
                    echo '<a href="?page=' . $prevPage . '" class="pagination-prev">
                            <i class="fas fa-arrow-left"></i>
                            ' . htmlspecialchars($availableDocs[$prevPage]) . '
                          </a>';
                }
                
                // 下一页
                if ($currentIndex < count($allPages) - 1) {
                    $nextPage = $allPages[$currentIndex + 1];
                    echo '<a href="?page=' . $nextPage . '" class="pagination-next">
                            ' . htmlspecialchars($availableDocs[$nextPage]) . '
                            <i class="fas fa-arrow-right"></i>
                          </a>';
                }
                ?>
            </div>
            
            <div class="docs-feedback">
                <p>此文档对您有帮助吗？</p>
                <div class="feedback-buttons">
                    <button class="btn btn-sm btn-outline" id="feedbackYes">
                        <i class="fas fa-thumbs-up"></i> 有帮助
                    </button>
                    <button class="btn btn-sm btn-outline" id="feedbackNo">
                        <i class="fas fa-thumbs-down"></i> 需改进
                    </button>
                </div>
                <div class="feedback-form" id="feedbackForm" style="display: none;">
                    <textarea placeholder="请告诉我们如何改进这篇文档..."></textarea>
                    <button class="btn btn-sm">提交反馈</button>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- 文档页面样式 -->
<style>
    /* 文档页面容器 */
    .docs-container {
        display: flex;
        min-height: calc(100vh - 80px);
    }
    
    /* 侧边栏样式 */
    .docs-sidebar {
        width: 280px;
        background: var(--glass-background);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-right: 1px solid var(--glass-border);
        position: fixed;
        top: 80px;
        left: 0;
        bottom: 0;
        z-index: 100;
        transition: transform var(--transition-normal);
        display: flex;
        flex-direction: column;
    }
    
    .docs-sidebar-header {
        padding: var(--spacing-md) var(--spacing-lg);
        border-bottom: 1px solid var(--glass-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .docs-sidebar-header h2 {
        margin: 0;
        font-size: 1.3rem;
        color: var(--secondary-color);
    }
    
    .sidebar-toggle {
        display: none;
        background: none;
        border: none;
        color: var(--text-color);
        cursor: pointer;
        font-size: 1.2rem;
    }
    
    .docs-sidebar-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        padding: var(--spacing-md) 0;
    }
    
    .docs-search {
        padding: 0 var(--spacing-lg) var(--spacing-md);
        position: relative;
    }
    
    .docs-search input {
        width: 100%;
        padding: var(--spacing-sm) var(--spacing-lg);
        padding-right: 40px;
        background: rgba(30, 40, 60, 0.5);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
        color: var(--text-color);
        font-family: var(--font-main);
    }
    
    .docs-search input:focus {
        outline: none;
        border-color: var(--accent-color);
    }
    
    .docs-search i {
        position: absolute;
        right: 25px;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0.7;
    }
    
    .docs-nav {
        flex: 1;
    }
    
    .docs-nav-section {
        margin-bottom: var(--spacing-lg);
    }
    
    .docs-nav-section h3 {
        padding: 0 var(--spacing-lg);
        margin: 0 0 var(--spacing-sm);
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.7;
    }
    
    .docs-nav-section ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .docs-nav-section li {
        padding: 0;
        margin: 0;
    }
    
    .docs-nav-section li a {
        display: block;
        padding: var(--spacing-sm) var(--spacing-lg);
        color: var(--text-color);
        text-decoration: none;
        opacity: 0.8;
        transition: all var(--transition-fast);
        border-left: 3px solid transparent;
    }
    
    .docs-nav-section li a:hover {
        opacity: 1;
        background-color: rgba(255, 255, 255, 0.05);
    }
    
    .docs-nav-section li.active a {
        border-left-color: var(--accent-color);
        background-color: rgba(10, 132, 255, 0.1);
        color: var(--accent-color);
        opacity: 1;
    }
    
    .docs-sidebar-footer {
        padding: var(--spacing-md) var(--spacing-lg);
        border-top: 1px solid var(--glass-border);
        display: flex;
        justify-content: space-between;
    }
    
    .btn-sm {
        padding: var(--spacing-xs) var(--spacing-sm);
        font-size: 0.9rem;
    }
    
    /* 文档内容样式 */
    .docs-content {
        flex: 1;
        margin-left: 280px;
        padding: var(--spacing-lg) var(--spacing-xl);
    }
    
    .docs-content-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-xl);
        padding-bottom: var(--spacing-md);
        border-bottom: 1px solid var(--glass-border);
    }
    
    .docs-content-header h1 {
        margin: 0;
        font-size: 2rem;
        background: linear-gradient(to right, var(--text-color), var(--secondary-color));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    
    .docs-actions {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }
    
    .docs-version-selector {
        position: relative;
        padding: var(--spacing-xs) var(--spacing-sm);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-sm);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: var(--spacing-xs);
    }
    
    .version-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: var(--glass-background);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-sm);
        min-width: 150px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px);
        transition: all var(--transition-fast);
        z-index: 10;
    }
    
    .docs-version-selector:hover .version-dropdown {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .version-dropdown a {
        display: block;
        padding: var(--spacing-xs) var(--spacing-sm);
        color: var(--text-color);
        text-decoration: none;
        transition: background-color var(--transition-fast);
    }
    
    .version-dropdown a:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
    
    .version-dropdown a.active {
        color: var(--accent-color);
    }
    
    .docs-content-body {
        margin-bottom: var(--spacing-xl);
    }
    
    .docs-content-footer {
        padding-top: var(--spacing-lg);
        border-top: 1px solid var(--glass-border);
    }
    
    .docs-pagination {
        display: flex;
        justify-content: space-between;
        margin-bottom: var(--spacing-lg);
    }
    
    .pagination-prev,
    .pagination-next {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        color: var(--text-color);
        text-decoration: none;
        padding: var(--spacing-sm) var(--spacing-md);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
        transition: all var(--transition-fast);
    }
    
    .pagination-prev:hover,
    .pagination-next:hover {
        background-color: rgba(255, 255, 255, 0.05);
        border-color: var(--accent-color);
    }
    
    .docs-feedback {
        text-align: center;
        padding: var(--spacing-lg) 0;
    }
    
    .docs-feedback p {
        margin-bottom: var(--spacing-sm);
        opacity: 0.8;
    }
    
    .feedback-buttons {
        display: flex;
        justify-content: center;
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-md);
    }
    
    .feedback-form textarea {
        width: 100%;
        height: 100px;
        padding: var(--spacing-sm);
        margin-bottom: var(--spacing-sm);
        background: rgba(30, 40, 60, 0.5);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-sm);
        color: var(--text-color);
        font-family: var(--font-main);
    }
    
    .docs-error {
        text-align: center;
        padding: var(--spacing-xxl) 0;
    }
    
    .docs-error h2 {
        margin-bottom: var(--spacing-md);
        color: var(--error-color);
    }
    
    .docs-error p {
        margin-bottom: var(--spacing-lg);
        opacity: 0.8;
    }
    
    /* 响应式设计 */
    @media (max-width: 992px) {
        .docs-sidebar {
            transform: translateX(-100%);
        }
        
        .docs-sidebar.active {
            transform: translateX(0);
        }
        
        .sidebar-toggle {
            display: block;
        }
        
        .docs-content {
            margin-left: 0;
        }
    }
    
    @media (max-width: 768px) {
        .docs-content {
            padding: var(--spacing-md);
        }
        
        .docs-content-header {
            flex-direction: column;
            align-items: flex-start;
            gap: var(--spacing-md);
        }
        
        .docs-actions {
            width: 100%;
            justify-content: space-between;
        }
        
        .docs-pagination {
            flex-direction: column;
            gap: var(--spacing-md);
        }
    }
</style>

<!-- 文档页面脚本 -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 侧边栏切换
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.docs-sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // 文档搜索功能
    const searchInput = document.getElementById('docsSearch');
    const navItems = document.querySelectorAll('.docs-nav-section li');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            navItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
    
    // 字体大小切换
    const fontSizeToggle = document.getElementById('fontSizeToggle');
    const contentBody = document.querySelector('.docs-content-body');
    let fontSize = 16; // 默认字体大小
    
    if (fontSizeToggle && contentBody) {
        fontSizeToggle.addEventListener('click', function() {
            fontSize = fontSize === 16 ? 18 : fontSize === 18 ? 14 : 16;
            contentBody.style.fontSize = `${fontSize}px`;
        });
    }
    
    // 打印文档
    const printDoc = document.getElementById('printDoc');
    
    if (printDoc) {
        printDoc.addEventListener('click', function() {
            window.print();
        });
    }
    
    // 文档反馈
    const feedbackYes = document.getElementById('feedbackYes');
    const feedbackNo = document.getElementById('feedbackNo');
    const feedbackForm = document.getElementById('feedbackForm');
    
    if (feedbackYes && feedbackNo && feedbackForm) {
        feedbackYes.addEventListener('click', function() {
            alert('感谢您的反馈！');
        });
        
        feedbackNo.addEventListener('click', function() {
            feedbackForm.style.display = 'block';
            this.disabled = true;
        });
    }
});
</script>

<?php
// 渲染页面页脚
renderPageFooter();
?> 