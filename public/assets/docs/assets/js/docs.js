// 文档系统的主要JavaScript功能
document.addEventListener('DOMContentLoaded', () => {
    // 初始化功能
    initializeNavigation();
    initializeSearch();
    initializeThemeToggle();
    highlightCurrentPage();
    setupMobileMenu();
});

// 导航初始化
function initializeNavigation() {
    const docNav = document.getElementById('doc-nav');
    if (!docNav) return;

    // 获取当前页面路径
    const currentPath = window.location.pathname;
    const isAlingAiDocs = currentPath.includes('AlingAi_docs');
    const isStanfaiDocs = currentPath.includes('Stanfai_docs');

    // 清除加载占位符
    docNav.innerHTML = '';

    // 根据当前路径生成相应的导航
    if (isAlingAiDocs) {
        generateAlingAiNav(docNav);
    } else if (isStanfaiDocs) {
        generateStanfaiNav(docNav);
    } else {
        generateHomeNav(docNav);
    }
}

// 生成AlingAI文档导航
function generateAlingAiNav(container) {
    const navItems = [
        {
            title: '开始使用',
            items: [
                { title: '快速开始', path: '/docs/AlingAi_docs/README.md' },
                { title: '安装指南', path: '/docs/AlingAi_docs/installation.md' },
                { title: '基础概念', path: '/docs/AlingAi_docs/concepts.md' }
            ]
        },
        {
            title: '核心功能',
            items: [
                { title: '自然语言处理', path: '/docs/AlingAi_docs/nlp.md' },
                { title: '知识图谱', path: '/docs/AlingAi_docs/knowledge-graph.md' },
                { title: '对话系统', path: '/docs/AlingAi_docs/dialogue.md' }
            ]
        },
        {
            title: 'API参考',
            items: [
                { title: 'REST API', path: '/docs/AlingAi_docs/api-rest.md' },
                { title: 'WebSocket API', path: '/docs/AlingAi_docs/api-websocket.md' },
                { title: 'SDK文档', path: '/docs/AlingAi_docs/sdk.md' }
            ]
        }
    ];

    renderNavigation(container, navItems);
}

// 生成Stanfai文档导航
function generateStanfaiNav(container) {
    const navItems = [
        {
            title: '入门指南',
            items: [
                { title: '平台概述', path: '/docs/Stanfai_docs/README.md' },
                { title: '快速开始', path: '/docs/Stanfai_docs/quickstart.md' },
                { title: '基础配置', path: '/docs/Stanfai_docs/configuration.md' }
            ]
        },
        {
            title: '核心功能',
            items: [
                { title: '模型管理', path: '/docs/Stanfai_docs/model-management.md' },
                { title: '服务部署', path: '/docs/Stanfai_docs/deployment.md' },
                { title: '监控与日志', path: '/docs/Stanfai_docs/monitoring.md' }
            ]
        },
        {
            title: '最佳实践',
            items: [
                { title: '性能优化', path: '/docs/Stanfai_docs/performance.md' },
                { title: '安全指南', path: '/docs/Stanfai_docs/security.md' },
                { title: '常见问题', path: '/docs/Stanfai_docs/faq.md' }
            ]
        }
    ];

    renderNavigation(container, navItems);
}

// 生成首页导航
function generateHomeNav(container) {
    const navItems = [
        {
            title: '文档中心',
            items: [
                { title: 'AlingAI文档', path: '/docs/AlingAi_docs' },
                { title: 'Stanfai文档', path: '/docs/Stanfai_docs' }
            ]
        },
        {
            title: '资源',
            items: [
                { title: '技术白皮书', path: '/docs/whitepaper/technical.pdf' },
                { title: 'API文档', path: '/docs/api' },
                { title: '示例代码', path: '/docs/examples' }
            ]
        }
    ];

    renderNavigation(container, navItems);
}

// 渲染导航菜单
function renderNavigation(container, items) {
    items.forEach(section => {
        const sectionEl = document.createElement('div');
        sectionEl.className = 'mb-6';

        const titleEl = document.createElement('h3');
        titleEl.className = 'text-sm font-semibold text-gray-900 mb-2 uppercase tracking-wide';
        titleEl.textContent = section.title;

        const listEl = document.createElement('ul');
        listEl.className = 'space-y-1';

        section.items.forEach(item => {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = item.path;
            a.className = 'block px-3 py-2 text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded-md text-sm';
            a.textContent = item.title;
            
            // 如果是当前页面，添加active类
            if (window.location.pathname === item.path) {
                a.classList.add('bg-blue-50', 'text-blue-700', 'font-medium');
            }

            li.appendChild(a);
            listEl.appendChild(li);
        });

        sectionEl.appendChild(titleEl);
        sectionEl.appendChild(listEl);
        container.appendChild(sectionEl);
    });
}

// 搜索功能初始化
function initializeSearch() {
    const searchInput = document.getElementById('search-input');
    if (!searchInput) return;

    searchInput.addEventListener('input', debounce(async (e) => {
        const query = e.target.value.trim();
        if (query.length < 2) return;

        try {
            // 这里可以实现实际的搜索逻辑
            const results = await searchDocs(query);
            displaySearchResults(results);
        } catch (error) {
            console.error('搜索出错:', error);
        }
    }, 300));
}

// 搜索文档
async function searchDocs(query) {
    // 这里可以实现实际的搜索逻辑
    // 返回示例数据
    return [
        {
            title: '快速开始',
            path: '/docs/AlingAi_docs/quickstart',
            excerpt: '包含关键字的文档片段...'
        }
    ];
}

// 显示搜索结果
function displaySearchResults(results) {
    const resultsContainer = document.getElementById('search-results');
    if (!resultsContainer) return;

    resultsContainer.innerHTML = '';
    
    if (results.length === 0) {
        resultsContainer.innerHTML = '<p class="p-4 text-gray-500">未找到相关结果</p>';
        return;
    }

    const list = document.createElement('ul');
    list.className = 'divide-y divide-gray-200';

    results.forEach(result => {
        const li = document.createElement('li');
        li.className = 'p-4 hover:bg-gray-50';

        const a = document.createElement('a');
        a.href = result.path;
        a.className = 'block';

        const title = document.createElement('h4');
        title.className = 'text-sm font-medium text-gray-900';
        title.textContent = result.title;

        const excerpt = document.createElement('p');
        excerpt.className = 'mt-1 text-sm text-gray-500';
        excerpt.textContent = result.excerpt;

        a.appendChild(title);
        a.appendChild(excerpt);
        li.appendChild(a);
        list.appendChild(li);
    });

    resultsContainer.appendChild(list);
}

// 主题切换初始化
function initializeThemeToggle() {
    const themeToggle = document.getElementById('theme-toggle');
    if (!themeToggle) return;

    // 检查用户偏好
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const savedTheme = localStorage.getItem('theme');
    
    // 设置初始主题
    if (savedTheme) {
        document.documentElement.classList.toggle('dark', savedTheme === 'dark');
    } else {
        document.documentElement.classList.toggle('dark', prefersDark);
    }

    // 监听主题切换
    themeToggle.addEventListener('click', () => {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });
}

// 高亮当前页面
function highlightCurrentPage() {
    const currentPath = window.location.pathname;
    const links = document.querySelectorAll('#doc-nav a');
    
    links.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('bg-blue-50', 'text-blue-700', 'font-medium');
        }
    });
}

// 移动端菜单
function setupMobileMenu() {
    const menuButton = document.getElementById('mobile-menu-button');
    const sidebar = document.getElementById('sidebar');
    
    if (!menuButton || !sidebar) return;

    menuButton.addEventListener('click', () => {
        sidebar.classList.toggle('hidden');
    });
}

// 工具函数：防抖
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// 处理代码高亮
function highlightCode() {
    document.querySelectorAll('pre code').forEach((block) => {
        hljs.highlightBlock(block);
    });
}

// 处理页面内锚点平滑滚动
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// 添加复制代码按钮
function addCopyButtons() {
    document.querySelectorAll('pre code').forEach((codeBlock) => {
        const container = codeBlock.parentNode;
        const copyButton = document.createElement('button');
        copyButton.className = 'copy-button';
        copyButton.textContent = '复制';
        
        copyButton.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(codeBlock.textContent);
                copyButton.textContent = '已复制!';
                setTimeout(() => {
                    copyButton.textContent = '复制';
                }, 2000);
            } catch (err) {
                console.error('复制失败:', err);
                copyButton.textContent = '复制失败';
            }
        });
        
        container.appendChild(copyButton);
    });
}

// 初始化页面功能
window.addEventListener('load', () => {
    highlightCode();
    addCopyButtons();
});