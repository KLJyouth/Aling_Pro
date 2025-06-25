const lightCodeTheme = require('prism-react-renderer/themes/github');
const darkCodeTheme = require('prism-react-renderer/themes/dracula');

// With JSDoc @type annotations, IDEs can provide config autocompletion
/** @type {import('@docusaurus/types').DocusaurusConfig} */
(module.exports = {
  title: 'AlingAi_pro文档中心',
  tagline: '项目技术文档和开发指南',
  url: 'https://your-domain.com',
  baseUrl: '/',
  onBrokenLinks: 'throw',
  onBrokenMarkdownLinks: 'warn',
  favicon: 'img/favicon.ico',
  organizationName: 'alingai', // 替换为您的组织名
  projectName: 'AlingAi_pro', // 项目名称

  // 添加多语言支持
  i18n: {
    defaultLocale: 'zh-CN',
    locales: ['zh-CN', 'en'],
    localeConfigs: {
      'zh-CN': {
        label: '简体中文',
        direction: 'ltr',
      },
      'en': {
        label: 'English',
        direction: 'ltr',
      },
    },
  },

  presets: [
    [
      '@docusaurus/preset-classic',
      /** @type {import('@docusaurus/preset-classic').Options} */
      ({
        docs: {
          sidebarPath: require.resolve('./sidebars.js'),
          // 请根据实际情况修改
          editUrl: 'https://github.com/your-username/AlingAi_pro/edit/main/docs-website/',
        },
        blog: {
          showReadingTime: true,
          // 请根据实际情况修改
          editUrl:
            'https://github.com/your-username/AlingAi_pro/edit/main/docs-website/blog/',
        },
        theme: {
          customCss: require.resolve('./src/css/custom.css'),
        },
      }),
    ],
  ],

  themeConfig:
    /** @type {import('@docusaurus/preset-classic').ThemeConfig} */
    ({
      // 添加搜索配置
      algolia: {
        // 这是一个占位符配置，实际部署时需要注册Algolia并获取真实的appId和apiKey
        appId: 'YOUR_APP_ID',
        apiKey: 'YOUR_API_KEY',
        indexName: 'alingai_pro',
        contextualSearch: true,
        placeholder: '搜索文档...',
        // 可选：添加搜索页面路径
        searchPagePath: 'search',
      },
      navbar: {
        title: 'AlingAi_pro文档中心',
        logo: {
          alt: 'AlingAi Logo',
          src: 'img/logo.svg',
        },
        items: [
          {
            type: 'doc',
            docId: 'intro',
            position: 'left',
            label: '介绍',
          },
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
          // 添加语言切换下拉菜单
          {
            type: 'localeDropdown',
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
                to: 'docs/guides/php-development-guidelines',
              },
              {
                label: '参考资料',
                to: 'docs/references/php81-syntax-error-common-fixes',
              },
            ],
          },
          {
            title: '资源',
            items: [
              {
                label: 'PHP官方文档',
                href: 'https://www.php.net/docs.php',
              },
              {
                label: 'PHP标准规范',
                href: 'https://www.php-fig.org/psr/',
              },
            ],
          },
          {
            title: '更多',
            items: [
              {
                label: '项目GitHub',
                href: 'https://github.com/your-username/AlingAi_pro',
              },
            ],
          },
        ],
        copyright: `Copyright © ${new Date().getFullYear()} AlingAi_pro`,
      },
      prism: {
        theme: lightCodeTheme,
        darkTheme: darkCodeTheme,
        additionalLanguages: ['php', 'bash'],
      },
    }),
});
