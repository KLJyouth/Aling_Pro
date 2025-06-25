# AlingAi_pro Docusaurus文档中心设置指南

本指南将帮助您完成Docusaurus文档中心的设置、配置和定制化。

## 一、环境准备

### 1.1 必要软件
- **Node.js**: 版本14或更高
- **npm** 或 **yarn**
- **Git**

### 1.2 检查环境

运行以下命令检查Node.js是否正确安装:

```bash
node -v
npm -v
```

## 二、安装和初始化

### 2.1 使用批处理脚本安装

1. 在项目根目录找到 `setup_docusaurus.bat`
2. 双击运行此批处理文件或在命令行中执行
3. 脚本将自动:
   - 创建Docusaurus项目 (`docs-website`)
   - 创建文档分类目录
   - 复制现有文档到相应目录

### 2.2 手动安装

如果批处理脚本无法正常运行，您可以手动执行以下步骤:

```bash
# 创建Docusaurus项目
npx @docusaurus/init@latest init docs-website classic

# 进入项目目录
cd docs-website

# 创建文档分类目录
mkdir docs\standards
mkdir docs\guides
mkdir docs\references

# 复制文档
xcopy /E /I /Y ..\docs\standards\*.* docs\standards\
xcopy /E /I /Y ..\docs\guides\*.* docs\guides\
xcopy /E /I /Y ..\docs\references\*.* docs\references\
```

## 三、文档准备与配置

### 3.1 添加Docusaurus元数据头部

每个Markdown文档需要添加元数据头部。例如，对于`PHP_NAMESPACE_STANDARDS.md`:

```markdown
---
id: php-namespace-standards
title: PHP命名空间标准
sidebar_label: 命名空间标准
---

(原文内容...)
```

#### 标准文档
为以下文件添加元数据:
- `docs/standards/PHP_NAMESPACE_STANDARDS.md`
- `docs/standards/PHP_BEST_PRACTICES_GUIDE.md`
- `docs/standards/CHINESE_ENCODING_STANDARDS.md`

#### 指南文档
为以下文件添加元数据:
- `docs/guides/PHP_DEVELOPMENT_GUIDELINES.md`
- `docs/guides/PHP_CODE_QUALITY_AUTOMATION.md`
- `docs/guides/PHP_MAINTENANCE_PLAN.md`
- `docs/guides/PHP_ERROR_FIX_MANUAL_GUIDE.md`

#### 参考文档
为以下文件添加元数据:
- `docs/references/PHP81_SYNTAX_ERROR_COMMON_FIXES.md`
- `docs/references/README.md`

### 3.2 配置侧边栏

编辑 `sidebars.js` 文件，替换为以下内容:

```javascript
module.exports = {
  docs: [
    {
      type: 'category',
      label: '编码标准',
      items: [
        'standards/php-namespace-standards',
        'standards/php-best-practices-guide',
        'standards/chinese-encoding-standards',
      ],
    },
    {
      type: 'category',
      label: '使用指南',
      items: [
        'guides/php-development-guidelines',
        'guides/php-code-quality-automation',
        'guides/php-maintenance-plan',
        'guides/php-error-fix-manual-guide',
      ],
    },
    {
      type: 'category',
      label: '参考资料',
      items: [
        'references/php81-syntax-error-common-fixes',
        'references/readme',
      ],
    },
  ],
};
```

**注意**: ID必须与Markdown文件的元数据头部中的`id`字段一致。

### 3.3 配置网站信息

编辑 `docusaurus.config.js` 文件，修改以下部分:

```javascript
module.exports = {
  title: 'AlingAi_pro文档中心',
  tagline: '项目技术文档和开发指南',
  url: 'https://your-domain.com',  // 替换为实际域名
  baseUrl: '/',
  favicon: 'img/favicon.ico',
  organizationName: 'your-org',    // 替换为组织名
  projectName: 'AlingAi_pro',

  themeConfig: {
    navbar: {
      title: 'AlingAi_pro文档中心',
      logo: {
        alt: 'AlingAi Logo',
        src: 'img/logo.svg',
      },
      items: [
        {
          to: 'docs/standards/php-namespace-standards',
          activeBasePath: 'docs/standards',
          label: '编码标准',
          position: 'left',
        },
        {
          to: 'docs/guides/php-development-guidelines',
          activeBasePath: 'docs/guides',
          label: '使用指南',
          position: 'left',
        },
        {
          to: 'docs/references/php81-syntax-error-common-fixes',
          activeBasePath: 'docs/references',
          label: '参考资料',
          position: 'left',
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
              to: 'docs/guides/php-development-guidelines',
            },
          ],
        },
        {
          title: '更多',
          items: [
            {
              label: '项目GitHub',
              href: 'https://github.com/your-org/AlingAi_pro',
            },
          ],
        },
      ],
      copyright: `Copyright © ${new Date().getFullYear()} AlingAi_pro`,
    },
  },
};
```

## 四、定制化

### 4.1 修改样式

您可以通过编辑 `src/css/custom.css` 文件来自定义网站样式:

```css
:root {
  --ifm-color-primary: #25c2a0;
  --ifm-color-primary-dark: rgb(33, 175, 144);
  --ifm-color-primary-darker: rgb(31, 165, 136);
  --ifm-color-primary-darkest: rgb(26, 136, 112);
  --ifm-color-primary-light: rgb(70, 203, 174);
  --ifm-color-primary-lighter: rgb(102, 212, 189);
  --ifm-color-primary-lightest: rgb(146, 224, 208);
}
```

### 4.2 添加徽标和图片

1. 将您的徽标文件替换 `static/img/logo.svg`
2. 将网站图标替换 `static/img/favicon.ico`

### 4.3 添加搜索功能

#### Algolia DocSearch

1. 注册 [Algolia DocSearch](https://docsearch.algolia.com/apply/)
2. 获取API密钥后，在 `docusaurus.config.js` 中添加:

```javascript
themeConfig: {
  // ... 其他配置
  algolia: {
    apiKey: '您的API密钥',
    indexName: 'alingai',
    appId: '您的应用ID',
  },
}
```

## 五、本地开发与部署

### 5.1 本地开发

在 `docs-website` 目录中运行:

```bash
npm start
```

浏览器将自动打开 http://localhost:3000

### 5.2 构建静态网站

```bash
npm run build
```

构建结果将位于 `build` 目录中。

### 5.3 部署选项

#### GitHub Pages

1. 修改 `docusaurus.config.js`:

```javascript
module.exports = {
  // ... 其他配置
  url: 'https://your-username.github.io',
  baseUrl: '/AlingAi_pro/',
  organizationName: 'your-username',
  projectName: 'AlingAi_pro',
}
```

2. 部署命令:

```bash
GIT_USER=your-username npm run deploy
```

#### 自定义服务器

将 `build` 目录上传至您的服务器:

```bash
# 构建项目
npm run build

# 将构建结果复制到服务器
scp -r build/* user@your-server:/path/to/www/
```

## 六、进阶功能

### 6.1 多语言支持

修改 `docusaurus.config.js`:

```javascript
module.exports = {
  // ... 其他配置
  i18n: {
    defaultLocale: 'zh-CN',
    locales: ['zh-CN', 'en'],
    localeConfigs: {
      'zh-CN': {
        label: '中文',
      },
      en: {
        label: 'English',
      },
    },
  },
}
```

### 6.2 版本管理

```bash
# 创建版本
npm run docusaurus docs:version 1.0.0
```

### 6.3 添加博客

编辑 `docusaurus.config.js`:

```javascript
module.exports = {
  // ... 其他配置
  presets: [
    [
      '@docusaurus/preset-classic',
      {
        // ... 其他配置
        blog: {
          showReadingTime: true,
          editUrl: 'https://github.com/your-username/AlingAi_pro/edit/master/website/blog/',
        },
      },
    ],
  ],
}
```

## 七、常见问题

### 7.1 图片无法显示

确保图片路径正确:

```markdown
![图片描述](../../static/img/your-image.png)
```

或使用相对路径:

```markdown
![图片描述](/img/your-image.png)
```

### 7.2 侧边栏不显示文档

检查以下几点:
- 文档元数据中的 `id` 字段是否与 `sidebars.js` 中的引用一致
- Markdown文件是否位于正确的目录中
- 是否正确配置了 `docusaurus.config.js` 中的 `baseUrl`

### 7.3 搜索功能不工作

Algolia DocSearch配置通常需要24-48小时才能生效。确保:
- 网站已部署并可以公开访问
- 已在Algolia控制台中正确配置爬虫
- `docusaurus.config.js` 中的Algolia配置信息正确

## 八、维护与更新

### 8.1 更新Docusaurus版本

```bash
npm update @docusaurus/core @docusaurus/preset-classic
```

### 8.2 添加新文档

1. 在适当的目录中创建Markdown文件
2. 添加元数据头部
3. 在 `sidebars.js` 中添加文件引用

### 8.3 备份与恢复

定期备份以下文件:
- `/docs` 目录 (您的文档)
- `docusaurus.config.js` (网站配置)
- `sidebars.js` (侧边栏配置)
- `src/css/custom.css` (自定义样式)
- `static` 目录 (静态资源) 