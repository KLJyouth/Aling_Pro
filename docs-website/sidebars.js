/**
 * Creating a sidebar enables you to:
 - create an ordered group of docs
 - render a sidebar for each doc of that group
 - provide next/previous navigation

 The sidebars can be generated from the filesystem, or explicitly defined here.

 Create as many sidebars as you want.
 */

module.exports = {
  // Main documentation sidebar
  docs: [
    {
      type: 'doc',
      id: 'intro',
      label: '介绍'
    },
    {
      type: 'category',
      label: '编码标准',
      items: [
        'standards/php-namespace-standards',
        'standards/php-best-practices-guide',
        'standards/chinese-encoding-standards'
      ],
    },
    {
      type: 'category',
      label: '使用指南',
      items: [
        'guides/php-development-guidelines',
        'guides/php-code-quality-automation',
        'guides/php-maintenance-plan',
        'guides/php-error-fix-manual-guide'
      ],
    },
    {
      type: 'category',
      label: '参考资料',
      items: [
        'references/php81-syntax-error-common-fixes',
        'references/readme'
      ],
    },
    {
      type: 'category',
      label: 'IT运维中心',
      items: [
        'operations/operations-overview',
        'operations/security-management',
        'operations/reports-management',
        'operations/logs-management'
      ],
    },
  ],
};
