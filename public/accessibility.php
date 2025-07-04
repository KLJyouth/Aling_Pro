<?php
/**
 * 无障碍页面
 * 介绍网站的无障碍特性和支持
 */

// 设置页面信息
$pageTitle = "无障碍支持 - AlingAi Pro";
$pageDescription = "了解AlingAi Pro如何支持各种无障碍功能，确保所有用户都能便捷地使用我们的服务";
$pageKeywords = "无障碍, 辅助功能, 高对比度, 屏幕阅读器, 键盘导航";
$additionalCSS = ["/css/accessibility.css"];

// 包含页面模板
require_once __DIR__ . "/templates/page.php";

// 渲染页面头部
renderPageHeader();
?>


<!-- 无障碍页面内容 -->
<section class="accessibility-hero">
    <div class="container">
        <h1>无障碍支持</h1>
        <p class="lead">我们致力于确保所有用户都能便捷地访问和使用AlingAi Pro的服务，无论其能力如何。</p>
    </div>
</section>

<section class="accessibility-content">
    <div class="container">
        <div class="content-grid">
            <div class="content-main">
                <h2>我们的无障碍承诺</h2>
                <p>AlingAi Pro团队努力遵循<a href="https://www.w3.org/WAI/standards-guidelines/wcag/" target="_blank">Web内容无障碍指南(WCAG) 2.1</a>的AA级标准，以确保我们的网站和应用程序对所有用户都尽可能地易于访问和使用。</p>
                <p>我们持续监控和改进我们的无障碍表现，定期进行测试，并根据用户反馈进行优化。</p>
                
                <h2>无障碍功能</h2>
                <p>我们的网站提供多种无障碍功能，帮助各种需求的用户更好地访问内容：</p>
                
                <h3>高对比度模式</h3>
                <p>为视力敏感或色盲用户提供高对比度视觉体验，使文本和界面元素更加清晰可辨。</p>
                <div class="feature-usage">
                    <h4>如何使用：</h4>
                    <ol>
                        <li>点击页面右下角的无障碍图标</li>
                        <li>在弹出的控制面板中，找到"高对比度"选项</li>
                        <li>点击"开关"按钮启用或关闭高对比度模式</li>
                    </ol>
                </div>
                
                <h3>字体大小调整</h3>
                <p>允许用户根据个人需求和偏好调整网站上的文本大小，提高可读性。</p>
                <div class="feature-usage">
                    <h4>如何使用：</h4>
                    <ol>
                        <li>点击页面右下角的无障碍图标</li>
                        <li>在弹出的控制面板中，找到"字体大小"选项</li>
                        <li>使用"A-"减小字体，"A+"增大字体，或"A"重置为默认大小</li>
                    </ol>
                </div>
                
                <h3>键盘导航</h3>
                <p>所有网站功能和内容都可以通过键盘完全访问，无需使用鼠标。</p>
                <div class="feature-usage">
                    <h4>常用键盘快捷键：</h4>
                    <ul>
                        <li><strong>Tab键</strong>：在页面元素间导航</li>
                        <li><strong>Shift+Tab</strong>：反向在页面元素间导航</li>
                        <li><strong>Enter/Space键</strong>：激活链接、按钮或控件</li>
                        <li><strong>方向键</strong>：在菜单、选项卡或其他组件内导航</li>
                        <li><strong>Esc键</strong>：关闭弹出窗口或返回上一级</li>
                    </ul>
                </div>
                
                <h3>跳过导航链接</h3>
                <p>允许键盘用户直接跳过重复的导航元素，快速访问页面主要内容。</p>
                <div class="feature-usage">
                    <h4>如何使用：</h4>
                    <ol>
                        <li>访问任何页面时，按Tab键</li>
                        <li>第一个焦点元素将是"跳到主要内容"链接</li>
                        <li>按Enter键可直接跳转到页面主要内容区域</li>
                    </ol>
                </div>
                
                <h3>屏幕阅读器支持</h3>
                <p>我们的网站针对主流屏幕阅读器进行了优化，包括NVDA、JAWS、VoiceOver和TalkBack。</p>
                <div class="feature-usage">
                    <h4>支持的功能：</h4>
                    <ul>
                        <li>适当的ARIA标签和角色</li>
                        <li>语义化HTML结构</li>
                        <li>状态变更公告</li>
                        <li>图像的替代文本</li>
                        <li>表单输入的清晰标签</li>
                    </ul>
                </div>
                
                <h3>用户反馈</h3>
                <p>我们重视您对我们无障碍支持的反馈。如果您在使用我们的网站或应用程序时遇到任何问题，或有改进建议，请通过以下方式告诉我们：</p>
                <ul>
                    <li>使用页面右侧的用户反馈面板，选择"无障碍反馈"类型</li>
                    <li>发送邮件至：<a href="mailto:accessibility@alingai.pro">accessibility@alingai.pro</a></li>
                    <li>通过<a href="/contact">联系我们</a>页面提交详细反馈</li>
                </ul>
            </div>
            
            <div class="content-sidebar">
                <div class="sidebar-card">
                    <h3>快速指南</h3>
                    <ul class="quick-guide">
                        <li><a href="#keyboard-navigation">键盘导航</a></li>
                        <li><a href="#screen-readers">屏幕阅读器</a></li>
                        <li><a href="#high-contrast">高对比度模式</a></li>
                        <li><a href="#font-size">字体大小调整</a></li>
                        <li><a href="#feedback">提交反馈</a></li>
                    </ul>
                </div>
                
                <div class="sidebar-card">
                    <h3>无障碍资源</h3>
                    <ul class="resources-list">
                        <li><a href="https://www.w3.org/WAI/" target="_blank">W3C网页无障碍倡议</a></li>
                        <li><a href="https://www.w3.org/WAI/standards-guidelines/wcag/" target="_blank">WCAG 2.1指南</a></li>
                        <li><a href="https://www.w3.org/WAI/ARIA/apg/" target="_blank">ARIA实践指南</a></li>
                    </ul>
                </div>
                
                <div class="sidebar-card">
                    <h3>屏幕阅读器</h3>
                    <ul class="resources-list">
                        <li><a href="https://www.nvaccess.org/download/" target="_blank">NVDA (免费)</a></li>
                        <li><a href="https://www.freedomscientific.com/products/software/jaws/" target="_blank">JAWS</a></li>
                        <li><a href="https://www.apple.com/accessibility/vision/" target="_blank">VoiceOver (苹果设备)</a></li>
                        <li><a href="https://support.google.com/accessibility/android/answer/6283677" target="_blank">TalkBack (安卓设备)</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* 无障碍页面特定样式 */
    .accessibility-hero {
        background: var(--glass-background);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-bottom: 1px solid var(--glass-border);
        padding: var(--spacing-xxl) 0;
        margin-bottom: var(--spacing-xl);
        text-align: center;
    }
    
    .accessibility-hero h1 {
        font-size: 2.5rem;
        margin-bottom: var(--spacing-md);
        background: linear-gradient(90deg, var(--accent-color), var(--tertiary-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .accessibility-hero .lead {
        font-size: 1.2rem;
        max-width: 800px;
        margin: 0 auto;
        color: var(--text-color-light);
    }
    
    .accessibility-content {
        padding: var(--spacing-xl) 0;
    }
    
    .content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: var(--spacing-xl);
    }
    
    .content-main h2 {
        margin: var(--spacing-lg) 0 var(--spacing-md);
        color: var(--secondary-color);
    }
    
    .content-main h3 {
        margin: var(--spacing-lg) 0 var(--spacing-sm);
        color: var(--accent-color);
    }
    
    .content-main p {
        margin-bottom: var(--spacing-md);
        line-height: 1.7;
    }
    
    .feature-usage {
        background: var(--glass-background);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-md);
        margin: var(--spacing-md) 0;
    }
    
    .feature-usage h4 {
        margin-bottom: var(--spacing-sm);
        color: var(--text-color);
    }
    
    .feature-usage ul,
    .feature-usage ol {
        padding-left: var(--spacing-lg);
    }
    
    .feature-usage li {
        margin-bottom: var(--spacing-xs);
    }
    
    .sidebar-card {
        background: var(--glass-background);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-md);
        margin-bottom: var(--spacing-lg);
    }
    
    .sidebar-card h3 {
        margin-bottom: var(--spacing-md);
        color: var(--secondary-color);
    }
    
    .quick-guide,
    .resources-list {
        list-style: none;
        padding: 0;
    }
    
    .quick-guide li,
    .resources-list li {
        margin-bottom: var(--spacing-sm);
    }
    
    .quick-guide a,
    .resources-list a {
        color: var(--accent-color);
        text-decoration: none;
        display: block;
        padding: var(--spacing-xs) 0;
        transition: all var(--transition-fast);
    }
    
    .quick-guide a:hover,
    .resources-list a:hover {
        color: var(--tertiary-color);
        transform: translateX(5px);
    }
    
    @media (max-width: 992px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
        
        .content-sidebar {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: var(--spacing-md);
        }
    }
    
    @media (max-width: 768px) {
        .accessibility-hero h1 {
            font-size: 2rem;
        }
        
        .content-sidebar {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php
// 渲染页面页脚
renderPageFooter();
?>
