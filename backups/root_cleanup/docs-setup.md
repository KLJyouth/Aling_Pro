# AlingAi_pro 文档中心搭建指南

## 前台文档中心 - Docusaurus 实施方案

### 1. 环境准备

确保您的系统已安装以下软件：

- Node.js 14 或更高版本
- npm 或 yarn

### 2. 创建 Docusaurus 项目

在项目根目录下执行以下命令：

```bash
# 创建一个新的 Docusaurus 项目
npx @docusaurus/init@latest init docs-website classic
```

这将创建一个名为 `docs-website` 的新目录，其中包含 Docusaurus 的基本结构。

### 3. 项目结构

Docusaurus 项目的主要目录结构如下：

```
docs-website/
├── blog/                 # 博客文章
├── docs/                 # 文档
├── src/                  # 自定义页面和组件
│   ├── css/              # CSS 文件
│   └── pages/            # 自定义页面
├── static/               # 静态资源
├── docusaurus.config.js  # 配置文件
├── package.json          # 项目依赖
└── sidebars.js           # 侧边栏配置
```

### 4. 内容迁移

1. 将我们的 `docs` 目录中的文档迁移到 Docusaurus 项目：

```bash
# 创建文档分类目录
mkdir -p docs-website/docs/standards
mkdir -p docs-website/docs/guides
mkdir -p docs-website/docs/references
mkdir -p docs-website/docs/maintenance

# 复制文档
cp docs/standards/* docs-website/docs/standards/
cp docs/guides/* docs-website/docs/guides/
cp docs/references/* docs-website/docs/references/
```

2. 为每个文档添加 Docusaurus 元数据头部：

```markdown
---
id: document-id
title: 文档标题
sidebar_label: 侧边栏标签
---

文档内容...
```

### 5. 配置侧边栏

编辑 `docs-website/sidebars.js` 文件，组织文档结构：

```javascript
module.exports = {
  docs: [
    {
      type: 'category',
      label: '编码标准',
      items: ['standards/php-namespace-standards', 'standards/php-best-practices', 'standards/chinese-encoding'],
    },
    {
      type: 'category',
      label: '使用指南',
      items: ['guides/php-development', 'guides/php-code-quality', 'guides/php-maintenance', 'guides/php-error-fix'],
    },
    {
      type: 'category',
      label: '参考资料',
      items: ['references/php81-syntax-errors', 'references/readme'],
    },
  ],
};
```

### 6. 配置网站

编辑 `docs-website/docusaurus.config.js` 文件，自定义网站配置：

```javascript
module.exports = {
  title: 'AlingAi_pro 文档中心',
  tagline: '全面的项目文档和技术指南',
  url: 'https://your-domain.com',
  baseUrl: '/',
  favicon: 'img/favicon.ico',
  organizationName: 'your-org',
  projectName: 'AlingAi_pro',
  themeConfig: {
    navbar: {
      title: 'AlingAi_pro',
      logo: {
        alt: 'AlingAi_pro Logo',
        src: 'img/logo.svg',
      },
      items: [
        {
          to: 'docs/',
          activeBasePath: 'docs',
          label: '文档',
          position: 'left',
        },
        {
          to: 'blog',
          label: '博客',
          position: 'left'
        },
        {
          href: 'https://github.com/your-org/AlingAi_pro',
          label: 'GitHub',
          position: 'right',
        },
      ],
    },
    footer: {
      style: 'dark',
      links: [
        {
          title: '文档',
          items: [
            {
              label: '编码标准',
              to: 'docs/standards/php-namespace-standards',
            },
            {
              label: '使用指南',
              to: 'docs/guides/php-development',
            },
          ],
        },
        {
          title: '社区',
          items: [
            {
              label: 'Stack Overflow',
              href: 'https://stackoverflow.com/questions/tagged/alingai',
            },
          ],
        },
      ],
      copyright: `Copyright © ${new Date().getFullYear()} AlingAi_pro. Built with Docusaurus.`,
    },
  },
  presets: [
    [
      '@docusaurus/preset-classic',
      {
        docs: {
          sidebarPath: require.resolve('./sidebars.js'),
          editUrl: 'https://github.com/your-org/AlingAi_pro/edit/master/docs-website/',
        },
        blog: {
          showReadingTime: true,
          editUrl: 'https://github.com/your-org/AlingAi_pro/edit/master/docs-website/blog/',
        },
        theme: {
          customCss: require.resolve('./src/css/custom.css'),
        },
      },
    ],
  ],
};
```

### 7. 添加搜索功能

Docusaurus 可以集成 Algolia DocSearch 来提供强大的搜索功能：

1. 注册 Algolia DocSearch：https://docsearch.algolia.com/apply/
2. 获取 API 密钥后，在 `docusaurus.config.js` 中添加配置：

```javascript
themeConfig: {
  // ...其他配置
  algolia: {
    apiKey: 'YOUR_API_KEY',
    indexName: 'alingai',
    appId: 'YOUR_APP_ID',
    contextualSearch: true,
  },
}
```

### 8. 本地运行和测试

```bash
cd docs-website
npm start
```

这将启动一个本地开发服务器，通常在 http://localhost:3000 访问。

### 9. 构建静态网站

```bash
npm run build
```

构建完成后，静态文件将位于 `build` 目录中，可以部署到任何静态网站托管服务。

### 10. 部署

可以选择多种部署方式：

- **GitHub Pages**：使用 `docusaurus deploy` 命令
- **Netlify**：连接到 GitHub 仓库，设置构建命令为 `npm run build`，发布目录为 `build`
- **Vercel**：类似 Netlify 的配置
- **自托管服务器**：将 `build` 目录上传到您的 Web 服务器

### 11. 持续集成

可以设置 GitHub Actions 或 Jenkins 等 CI/CD 工具，在代码更新时自动构建和部署文档网站。

### 下一步：后台IT技术运维中心

完成文档中心搭建后，我们将开始开发后台IT技术运维中心，请参考 `NEXT_STEPS_AFTER_REORGANIZATION.md` 文档中的第二部分内容。 