/**
 * Markdown渲染器 - 用于动态加载和渲染Markdown文件
 */

class MarkdownRenderer {
    constructor(options = {}) {
        this.options = {
            contentSelector: '#markdown-content',
            sidebarSelector: '#doc-sidebar',
            baseUrl: '/docs/',
            ...options
        };
        
        this.converter = window.markdownit({
            html: true,
            linkify: true,
            typographer: true,
            highlight: function (str, lang) {
                if (lang && hljs.getLanguage(lang)) {
                    try {
                        return hljs.highlight(str, { language: lang }).value;
                    } catch (__) {}
                }
                return ''; // 使用外部默认转义
            }
        });
        
        this.currentPath = '';
        this.sidebarItems = [];
        this.init();
    }
    
    init() {
        // 获取当前路径
        const path = window.location.pathname;
        const parts = path.split('/');
        
        // 确定文档类型 (AlingAi_docs 或 Stanfai_docs)
        if (path.includes('/AlingAi_docs/') || path.includes('/Stanfai_docs/')) {
            const docType = path.includes('/AlingAi_docs/') ? 'AlingAi_docs' : 'Stanfai_docs';
            this.currentPath = docType;
            
            // 如果URL没有指定具体文件，默认加载README.md
            let mdFile = parts[parts.length - 1];
            if (mdFile === '' || mdFile === docType) {
                mdFile = 'README.md';
            } else if (!mdFile.endsWith('.md') && !mdFile.endsWith('.adoc')) {
                mdFile += '.md';
            }
            
            this.loadSidebar(docType);
            this.loadMarkdown(`${docType}/${mdFile}`);
        }
        
        // 添加事件监听器，处理侧边栏链接点击
        document.addEventListener('click', (e) => {
            if (e.target.matches('.doc-nav a') || e.target.closest('.doc-nav a')) {
                const link = e.target.closest('a');
                e.preventDefault();
                
                const href = link.getAttribute('href');
                if (href && href.endsWith('.md')) {
                    this.loadMarkdown(href.replace(this.options.baseUrl, ''));
                    
                    // 更新URL，但不重新加载页面
                    window.history.pushState({}, '', href);
                    
                    // 在移动设备上关闭侧边栏
                    const sidebar = document.querySelector(this.options.sidebarSelector);
                    if (window.innerWidth <= 768 && sidebar) {
                        sidebar.classList.remove('active');
                    }
                    
                    // 更新活动链接
                    document.querySelectorAll('.doc-nav a').forEach(a => {
                        a.classList.remove('active');
                    });
                    link.classList.add('active');
                }
            }
        });
        
        // 添加移动端侧边栏切换按钮事件
        const toggleBtn = document.getElementById('sidebar-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const sidebar = document.querySelector(this.options.sidebarSelector);
                if (sidebar) {
                    sidebar.classList.toggle('active');
                }
            });
        }
    }
    
    async loadSidebar(docType) {
        try {
            // 获取文档目录下的所有.md文件
            const response = await fetch(`${this.options.baseUrl}api/list-files?path=${docType}`);
            if (!response.ok) throw new Error('Failed to load file list');
            
            const data = await response.json();
            this.sidebarItems = data.files.filter(file => 
                file.endsWith('.md') || file.endsWith('.adoc')
            );
            
            this.renderSidebar();
        } catch (error) {
            console.error('Error loading sidebar:', error);
            this.renderSidebarFallback(docType);
        }
    }
    
    renderSidebarFallback(docType) {
        // 如果API不可用，使用硬编码的常见文档文件
        const commonFiles = [
            { name: '首页', path: 'README.md' },
            { name: '架构', path: 'architecture.md' },
            { name: '安装', path: 'installation.adoc' },
            { name: 'API文档', path: 'api.md' },
            { name: '部署指南', path: 'deployment.md' },
            { name: '故障排除', path: 'troubleshooting.adoc' }
        ];
        
        const sidebarEl = document.querySelector(this.options.sidebarSelector);
        if (!sidebarEl) return;
        
        let html = `<h3>${docType === 'AlingAi_docs' ? 'AlingAI 文档' : 'Stanfai 文档'}</h3>`;
        html += '<ul class="doc-nav">';
        
        commonFiles.forEach(file => {
            html += `<li><a href="${this.options.baseUrl}${docType}/${file.path}">${file.name}</a></li>`;
        });
        
        html += '</ul>';
        sidebarEl.innerHTML = html;
    }
    
    renderSidebar() {
        const sidebarEl = document.querySelector(this.options.sidebarSelector);
        if (!sidebarEl || !this.sidebarItems.length) return;
        
        // 按文件名排序，但README.md始终在最前面
        this.sidebarItems.sort((a, b) => {
            if (a === 'README.md') return -1;
            if (b === 'README.md') return 1;
            return a.localeCompare(b);
        });
        
        let html = `<h3>${this.currentPath === 'AlingAi_docs' ? 'AlingAI 文档' : 'Stanfai 文档'}</h3>`;
        html += '<ul class="doc-nav">';
        
        this.sidebarItems.forEach(file => {
            // 从文件名生成友好的显示名称
            let displayName = file.replace(/\.(md|adoc)$/, '');
            
            // 特殊处理README.md
            if (displayName === 'README') {
                displayName = '首页';
            } else {
                // 将驼峰命名或下划线命名转换为空格分隔的标题
                displayName = displayName
                    .replace(/([A-Z])/g, ' $1')
                    .replace(/_/g, ' ')
                    .replace(/-/g, ' ')
                    .trim();
                
                // 首字母大写
                displayName = displayName.charAt(0).toUpperCase() + displayName.slice(1);
            }
            
            const isActive = window.location.pathname.endsWith(file);
            html += `<li><a href="${this.options.baseUrl}${this.currentPath}/${file}" class="${isActive ? 'active' : ''}">${displayName}</a></li>`;
        });
        
        html += '</ul>';
        sidebarEl.innerHTML = html;
    }
    
    async loadMarkdown(path) {
        const contentEl = document.querySelector(this.options.contentSelector);
        if (!contentEl) return;
        
        try {
            contentEl.innerHTML = '<div class="loading">加载中...</div>';
            
            const response = await fetch(`${this.options.baseUrl}${path}`);
            if (!response.ok) throw new Error(`Failed to load ${path}`);
            
            const markdown = await response.text();
            
            // 根据文件类型选择渲染方式
            if (path.endsWith('.adoc')) {
                // 如果是AsciiDoc文件，使用Asciidoctor.js渲染
                if (window.Asciidoctor) {
                    const asciidoctor = window.Asciidoctor();
                    contentEl.innerHTML = asciidoctor.convert(markdown);
                } else {
                    contentEl.innerHTML = '<div class="error">需要Asciidoctor.js来渲染此内容</div>';
                }
            } else {
                // Markdown文件使用markdown-it渲染
                contentEl.innerHTML = this.converter.render(markdown);
            }
            
            // 处理代码高亮
            contentEl.querySelectorAll('pre code').forEach((block) => {
                hljs.highlightElement(block);
            });
            
            // 更新页面标题
            const h1 = contentEl.querySelector('h1');
            if (h1) {
                document.title = `${h1.textContent} | 珑凌科技文档中心`;
            }
        } catch (error) {
            console.error('Error loading markdown:', error);
            contentEl.innerHTML = `<div class="error">加载文档失败: ${error.message}</div>`;
        }
    }
}

// 当页面加载完成后初始化渲染器
document.addEventListener('DOMContentLoaded', () => {
    window.markdownRenderer = new MarkdownRenderer();
});