<?php

namespace AlingAi\Migration;

use AlingAi\Core\Logger;
use DOMDocument;
use DOMXPath;
use Exception;

/**
 * 前端PHP迁移系统
 * 将HTML5文件转换为PHP8.0+模板系统
 */
class FrontendMigrationSystem
{
    private $logger;
    private $config;
    private $templateEngine;
    private $conversionRules;
    
    // PHP模板组件映射
    private $componentMapping = [
        'header' => 'Header.php',
        'nav' => 'Navigation.php',
        'sidebar' => 'Sidebar.php',
        'footer' => 'Footer.php',
        'modal' => 'Modal.php',
        'form' => 'Form.php',
        'table' => 'Table.php',
        'card' => 'Card.php'
    ];
    
    // 现代PHP8.0+特性
    private $modernPHPFeatures = [
        'nullable_types' => true,
        'union_types' => true,
        'match_expressions' => true,
        'constructor_promotion' => true,
        'attributes' => true,
        'readonly_properties' => true
    ];

    public function __construct($config = [])
    {
        $this->config = array_merge([
            'source_dir' => 'public',
            'target_dir' => 'resources/views',
            'component_dir' => 'resources/components',
            'layout_dir' => 'resources/layouts',
            'backup_dir' => 'migration_backup',
            'template_engine' => 'blade', // blade, twig, or native
            'preserve_js' => true,
            'preserve_css' => true,
            'minify_output' => true
        ], $config);
        
        $this->logger = new Logger('FrontendMigrationSystem');
        $this->initializeConversionRules();
        $this->setupDirectories();
    }

    /**
     * 初始化转换规则
     */
    private function initializeConversionRules()
    {
        $this->conversionRules = [
            // HTML到PHP组件转换规则
            'component_rules' => [
                'header' => ['header', '.header', '#header'],
                'navigation' => ['nav', '.nav', '.navbar', '#navigation'],
                'sidebar' => ['.sidebar', '.side-nav', '#sidebar'],
                'footer' => ['footer', '.footer', '#footer'],
                'modal' => ['.modal', '.popup', '.dialog'],
                'form' => ['form', '.form-container'],
                'table' => ['table', '.table-container', '.data-table'],
                'card' => ['.card', '.panel', '.widget']
            ],
            
            // JavaScript集成规则
            'js_integration' => [
                'inline_to_external' => true,
                'minify_js' => true,
                'modern_syntax' => true
            ],
            
            // CSS处理规则
            'css_processing' => [
                'extract_critical' => true,
                'defer_non_critical' => true,
                'sass_conversion' => true
            ]
        ];
    }

    /**
     * 设置目录结构
     */
    private function setupDirectories()
    {
        $directories = [
            $this->config['target_dir'],
            $this->config['component_dir'],
            $this->config['layout_dir'],
            $this->config['backup_dir'],
            $this->config['target_dir'] . '/pages',
            $this->config['component_dir'] . '/partials',
            $this->config['layout_dir'] . '/master'
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                $this->logger->info("创建目录: {$dir}");
            }
        }
    }

    /**
     * 执行完整迁移
     */
    public function executeMigration()
    {
        $this->logger->info('开始前端迁移');
        
        try {
            // 1. 扫描HTML文件
            $htmlFiles = $this->scanHtmlFiles();
            $this->logger->info('发现 ' . count($htmlFiles) . ' 个HTML文件');
            
            // 2. 备份原文件
            $this->backupFiles($htmlFiles);
            
            // 3. 分析文件结构
            $analysis = $this->analyzeFileStructure($htmlFiles);
            
            // 4. 生成基础布局
            $this->generateBaseLayouts($analysis);
            
            // 5. 提取可复用组件
            $this->extractComponents($analysis);
            
            // 6. 转换页面文件
            $this->convertPages($htmlFiles);
            
            // 7. 生成路由配置
            $this->generateRoutes($htmlFiles);
            
            // 8. 生成控制器
            $this->generateControllers($htmlFiles);
            
            // 9. 优化资源文件
            $this->optimizeAssets();
            
            // 10. 生成迁移报告
            $report = $this->generateMigrationReport($htmlFiles);
            
            $this->logger->info('前端迁移完成');
            return $report;
            
        } catch (Exception $e) {
            $this->logger->error('迁移失败: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 扫描HTML文件
     */
    private function scanHtmlFiles()
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->config['source_dir'])
        );
        
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'html') {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }

    /**
     * 备份文件
     */
    private function backupFiles($files)
    {
        foreach ($files as $file) {
            $backupPath = $this->config['backup_dir'] . '/' . basename($file);
            copy($file, $backupPath);
        }
        
        $this->logger->info('文件备份完成');
    }

    /**
     * 分析文件结构
     */
    private function analyzeFileStructure($files)
    {
        $analysis = [
            'common_components' => [],
            'page_types' => [],
            'asset_dependencies' => [],
            'layout_patterns' => []
        ];
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $dom = new DOMDocument();
            @$dom->loadHTML($content);
            $xpath = new DOMXPath($dom);
            
            // 分析组件使用
            $components = $this->analyzeComponents($xpath);
            $analysis['common_components'] = array_merge($analysis['common_components'], $components);
            
            // 分析页面类型
            $pageType = $this->determinePageType($file, $xpath);
            $analysis['page_types'][$file] = $pageType;
            
            // 分析资源依赖
            $assets = $this->analyzeAssets($xpath);
            $analysis['asset_dependencies'][$file] = $assets;
            
            // 分析布局模式
            $layout = $this->analyzeLayout($xpath);
            $analysis['layout_patterns'][$file] = $layout;
        }
        
        return $analysis;
    }

    /**
     * 分析组件
     */
    private function analyzeComponents($xpath)
    {
        $components = [];
        
        foreach ($this->conversionRules['component_rules'] as $component => $selectors) {
            foreach ($selectors as $selector) {
                if (strpos($selector, '.') === 0) {
                    // CSS类选择器
                    $nodes = $xpath->query("//*[contains(@class, '" . substr($selector, 1) . "')]");
                } elseif (strpos($selector, '#') === 0) {
                    // ID选择器
                    $nodes = $xpath->query("//*[@id='" . substr($selector, 1) . "']");
                } else {
                    // 元素选择器
                    $nodes = $xpath->query("//{$selector}");
                }
                
                if ($nodes->length > 0) {
                    $components[] = $component;
                }
            }
        }
        
        return array_unique($components);
    }

    /**
     * 确定页面类型
     */
    private function determinePageType($file, $xpath)
    {
        $filename = basename($file, '.html');
        
        // 基于文件名判断
        if (strpos($filename, 'admin') !== false) {
            return 'admin';
        } elseif (strpos($filename, 'login') !== false) {
            return 'auth';
        } elseif (strpos($filename, 'dashboard') !== false) {
            return 'dashboard';
        }
        
        // 基于内容判断
        $forms = $xpath->query('//form');
        $tables = $xpath->query('//table');
        $charts = $xpath->query("//*[contains(@class, 'chart')]");
        
        if ($forms->length > 3) {
            return 'form-heavy';
        } elseif ($tables->length > 0 || $charts->length > 0) {
            return 'data-display';
        }
        
        return 'general';
    }

    /**
     * 分析资源文件
     */
    private function analyzeAssets($xpath)
    {
        $assets = [
            'css' => [],
            'js' => [],
            'images' => []
        ];
        
        // CSS文件
        $cssNodes = $xpath->query('//link[@rel="stylesheet"]');
        foreach ($cssNodes as $node) {
            $href = $node->getAttribute('href');
            if ($href) {
                $assets['css'][] = $href;
            }
        }
        
        // JavaScript文件
        $jsNodes = $xpath->query('//script[@src]');
        foreach ($jsNodes as $node) {
            $src = $node->getAttribute('src');
            if ($src) {
                $assets['js'][] = $src;
            }
        }
        
        // 图片文件
        $imgNodes = $xpath->query('//img[@src]');
        foreach ($imgNodes as $node) {
            $src = $node->getAttribute('src');
            if ($src) {
                $assets['images'][] = $src;
            }
        }
        
        return $assets;
    }

    /**
     * 分析布局结构
     */
    private function analyzeLayout($xpath)
    {
        $layout = [
            'has_header' => $xpath->query('//header')->length > 0,
            'has_nav' => $xpath->query('//nav')->length > 0,
            'has_sidebar' => $xpath->query("//*[contains(@class, 'sidebar')]")->length > 0,
            'has_footer' => $xpath->query('//footer')->length > 0,
            'container_type' => $this->detectContainerType($xpath)
        ];
        
        return $layout;
    }

    /**
     * 检测容器类型
     */
    private function detectContainerType($xpath)
    {
        if ($xpath->query("//*[contains(@class, 'container-fluid')]")->length > 0) {
            return 'fluid';
        } elseif ($xpath->query("//*[contains(@class, 'container')]")->length > 0) {
            return 'fixed';
        }
        
        return 'none';
    }

    /**
     * 生成基础布局
     */
    private function generateBaseLayouts($analysis)
    {
        // 生成主布局文件
        $this->generateMasterLayout();
        
        // 生成管理后台布局
        $this->generateAdminLayout();
        
        // 生成认证布局
        $this->generateAuthLayout();
        
        $this->logger->info('基础布局生成完成');
    }

    /**
     * 生成主布局
     */
    private function generateMasterLayout()
    {
        $content = <<<'PHP'
<?php

namespace AlingAi\Views\Layouts;

use AlingAi\Core\View;

/**
 * 主布局模板
 * 使用现代PHP8.0+特性和组件系统
 */
class MasterLayout
{
    public function __construct(
        private readonly string $title = 'AlingAi Pro',
        private readonly array $meta = [],
        private readonly array $styles = [],
        private readonly array $scripts = [],
        private readonly ?string $bodyClass = null
    ) {}

    public function render(string $content, array $data = []): string
    {
        return View::render('layouts.master', [
            'title' => $this->title,
            'meta' => $this->meta,
            'styles' => $this->styles,
            'scripts' => $this->scripts,
            'bodyClass' => $this->bodyClass,
            'content' => $content,
            ...$data
        ]);
    }

    public function withTitle(string $title): self
    {
        return new self($title, $this->meta, $this->styles, $this->scripts, $this->bodyClass);
    }

    public function withMeta(array $meta): self
    {
        return new self($this->title, [...$this->meta, ...$meta], $this->styles, $this->scripts, $this->bodyClass);
    }

    public function withStyles(array $styles): self
    {
        return new self($this->title, $this->meta, [...$this->styles, ...$styles], $this->scripts, $this->bodyClass);
    }

    public function withScripts(array $scripts): self
    {
        return new self($this->title, $this->meta, $this->styles, [...$this->scripts, ...$scripts], $this->bodyClass);
    }

    public function withBodyClass(string $bodyClass): self
    {
        return new self($this->title, $this->meta, $this->styles, $this->scripts, $bodyClass);
    }
}
PHP;

        file_put_contents($this->config['layout_dir'] . '/MasterLayout.php', $content);
    }

    /**
     * 生成管理后台布局
     */
    private function generateAdminLayout()
    {
        $content = <<<'PHP'
<?php

namespace AlingAi\Views\Layouts;

use AlingAi\Core\View;

/**
 * 管理后台布局模板
 */
class AdminLayout extends MasterLayout
{
    public function __construct(
        string $title = 'AlingAi Pro - 管理后台',
        array $meta = [],
        array $styles = ['admin.css', 'dashboard.css'],
        array $scripts = ['admin.js', 'dashboard.js'],
        ?string $bodyClass = 'admin-layout'
    ) {
        parent::__construct($title, $meta, $styles, $scripts, $bodyClass);
    }

    public function render(string $content, array $data = []): string
    {
        return View::render('layouts.admin', [
            'title' => $this->title,
            'meta' => $this->meta,
            'styles' => $this->styles,
            'scripts' => $this->scripts,
            'bodyClass' => $this->bodyClass,
            'content' => $content,
            'sidebar' => $this->renderSidebar($data),
            'header' => $this->renderHeader($data),
            ...$data
        ]);
    }

    private function renderSidebar(array $data): string
    {
        return View::render('components.admin.sidebar', $data);
    }

    private function renderHeader(array $data): string
    {
        return View::render('components.admin.header', $data);
    }
}
PHP;

        file_put_contents($this->config['layout_dir'] . '/AdminLayout.php', $content);
    }

    /**
     * 生成认证布局
     */
    private function generateAuthLayout()
    {
        $content = <<<'PHP'
<?php

namespace AlingAi\Views\Layouts;

use AlingAi\Core\View;

/**
 * 认证页面布局模板
 */
class AuthLayout extends MasterLayout
{
    public function __construct(
        string $title = 'AlingAi Pro - 用户认证',
        array $meta = [],
        array $styles = ['auth.css'],
        array $scripts = ['auth.js'],
        ?string $bodyClass = 'auth-layout'
    ) {
        parent::__construct($title, $meta, $styles, $scripts, $bodyClass);
    }

    public function render(string $content, array $data = []): string
    {
        return View::render('layouts.auth', [
            'title' => $this->title,
            'meta' => $this->meta,
            'styles' => $this->styles,
            'scripts' => $this->scripts,
            'bodyClass' => $this->bodyClass,
            'content' => $content,
            ...$data
        ]);
    }
}
PHP;

        file_put_contents($this->config['layout_dir'] . '/AuthLayout.php', $content);
    }

    /**
     * 提取可复用组件
     */
    private function extractComponents($analysis)
    {
        $commonComponents = array_count_values($analysis['common_components']);
        
        foreach ($commonComponents as $component => $usage) {
            if ($usage >= 2) { // 被使用2次以上的组件
                $this->generateComponent($component);
            }
        }
        
        $this->logger->info('组件提取完成');
    }

    /**
     * 生成组件
     */
    private function generateComponent($componentName)
    {
        $className = ucfirst($componentName) . 'Component';
        
        $content = <<<PHP
<?php

namespace AlingAi\Views\Components;

use AlingAi\Core\Component;

/**
 * {$componentName} 组件
 * 自动生成的现代PHP8.0+组件
 */
class {$className} extends Component
{
    public function __construct(
        private readonly array \$props = [],
        private readonly array \$attributes = [],
        private readonly ?string \$slot = null
    ) {}

    public function render(): string
    {
        return \$this->view('{$componentName}', [
            'props' => \$this->props,
            'attributes' => \$this->attributes,
            'slot' => \$this->slot
        ]);
    }

    public function shouldRender(): bool
    {
        return true;
    }
}
PHP;

        $componentDir = $this->config['component_dir'];
        file_put_contents("{$componentDir}/{$className}.php", $content);
        
        // 生成对应的模板文件
        $this->generateComponentTemplate($componentName);
    }

    /**
     * 生成组件模板
     */
    private function generateComponentTemplate($componentName)
    {
        $templateContent = $this->getComponentTemplate($componentName);
        
        $templateDir = $this->config['target_dir'] . '/components';
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }
        
        file_put_contents("{$templateDir}/{$componentName}.php", $templateContent);
    }

    /**
     * 获取组件模板内容
     */
    private function getComponentTemplate($componentName): string
    {
        return match($componentName) {
            'header' => $this->getHeaderTemplate(),
            'navigation' => $this->getNavigationTemplate(),
            'sidebar' => $this->getSidebarTemplate(),
            'footer' => $this->getFooterTemplate(),
            'modal' => $this->getModalTemplate(),
            'form' => $this->getFormTemplate(),
            'table' => $this->getTableTemplate(),
            'card' => $this->getCardTemplate(),
            default => $this->getGenericTemplate($componentName)
        };
    }

    /**
     * 获取头部组件模板
     */
    private function getHeaderTemplate(): string
    {
        return <<<'HTML'
<header class="main-header <?= $attributes['class'] ?? '' ?>" <?= $this->renderAttributes($attributes) ?>>
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <img src="<?= $props['logo'] ?? '/assets/images/logo.png' ?>" alt="<?= $props['title'] ?? 'AlingAi Pro' ?>">
            </div>
            <nav class="main-nav">
                <?= $props['navigation'] ?? '' ?>
            </nav>
            <div class="header-actions">
                <?= $slot ?? '' ?>
            </div>
        </div>
    </div>
</header>
HTML;
    }

    /**
     * 获取导航组件模板
     */
    private function getNavigationTemplate(): string
    {
        return <<<'HTML'
<nav class="navigation <?= $attributes['class'] ?? '' ?>" <?= $this->renderAttributes($attributes) ?>>
    <ul class="nav-list">
        <?php foreach ($props['items'] ?? [] as $item): ?>
            <li class="nav-item <?= $item['active'] ? 'active' : '' ?>">
                <a href="<?= $item['url'] ?>" class="nav-link">
                    <?php if (!empty($item['icon'])): ?>
                        <i class="<?= $item['icon'] ?>"></i>
                    <?php endif; ?>
                    <?= $item['label'] ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
HTML;
    }

    /**
     * 获取侧边栏组件模板
     */
    private function getSidebarTemplate(): string
    {
        return <<<'HTML'
<aside class="sidebar <?= $attributes['class'] ?? '' ?>" <?= $this->renderAttributes($attributes) ?>>
    <div class="sidebar-header">
        <h3><?= $props['title'] ?? '菜单' ?></h3>
    </div>
    <div class="sidebar-content">
        <?= $slot ?? '' ?>
        <?php if (!empty($props['menu'])): ?>
            <ul class="sidebar-menu">
                <?php foreach ($props['menu'] as $item): ?>
                    <li class="menu-item <?= $item['active'] ? 'active' : '' ?>">
                        <a href="<?= $item['url'] ?>" class="menu-link">
                            <?php if (!empty($item['icon'])): ?>
                                <i class="<?= $item['icon'] ?>"></i>
                            <?php endif; ?>
                            <?= $item['label'] ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</aside>
HTML;
    }

    /**
     * 获取页脚组件模板
     */
    private function getFooterTemplate(): string
    {
        return <<<'HTML'
<footer class="main-footer <?= $attributes['class'] ?? '' ?>" <?= $this->renderAttributes($attributes) ?>>
    <div class="container">
        <div class="footer-content">
            <div class="footer-info">
                <p>&copy; <?= date('Y') ?> <?= $props['company'] ?? 'AlingAi Pro' ?>. <?= $props['rights'] ?? 'All rights reserved.' ?></p>
            </div>
            <div class="footer-links">
                <?= $slot ?? '' ?>
            </div>
        </div>
    </div>
</footer>
HTML;
    }

    /**
     * 获取模态框组件模板
     */
    private function getModalTemplate(): string
    {
        return <<<'HTML'
<div class="modal <?= $attributes['class'] ?? '' ?>" id="<?= $props['id'] ?? 'modal' ?>" <?= $this->renderAttributes($attributes) ?>>
    <div class="modal-backdrop"></div>
    <div class="modal-dialog">
        <div class="modal-content">
            <?php if (!empty($props['title'])): ?>
                <div class="modal-header">
                    <h4 class="modal-title"><?= $props['title'] ?></h4>
                    <button type="button" class="modal-close" data-dismiss="modal">&times;</button>
                </div>
            <?php endif; ?>
            <div class="modal-body">
                <?= $slot ?? '' ?>
            </div>
            <?php if (!empty($props['actions'])): ?>
                <div class="modal-footer">
                    <?php foreach ($props['actions'] as $action): ?>
                        <button type="button" class="btn <?= $action['class'] ?? 'btn-primary' ?>" onclick="<?= $action['onclick'] ?? '' ?>">
                            <?= $action['label'] ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
HTML;
    }

    /**
     * 获取表单组件模板
     */
    private function getFormTemplate(): string
    {
        return <<<'HTML'
<form class="form <?= $attributes['class'] ?? '' ?>" method="<?= $props['method'] ?? 'POST' ?>" action="<?= $props['action'] ?? '' ?>" <?= $this->renderAttributes($attributes) ?>>
    <?php if (!empty($props['csrf'])): ?>
        <input type="hidden" name="_token" value="<?= $props['csrf'] ?>">
    <?php endif; ?>
    
    <?php if (!empty($props['title'])): ?>
        <div class="form-header">
            <h3><?= $props['title'] ?></h3>
        </div>
    <?php endif; ?>
    
    <div class="form-body">
        <?= $slot ?? '' ?>
    </div>
    
    <?php if (!empty($props['submit'])): ?>
        <div class="form-footer">
            <button type="submit" class="btn btn-primary"><?= $props['submit'] ?></button>
        </div>
    <?php endif; ?>
</form>
HTML;
    }

    /**
     * 获取表格组件模板
     */
    private function getTableTemplate(): string
    {
        return <<<'HTML'
<div class="table-container <?= $attributes['class'] ?? '' ?>" <?= $this->renderAttributes($attributes) ?>>
    <?php if (!empty($props['title'])): ?>
        <div class="table-header">
            <h3><?= $props['title'] ?></h3>
        </div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table class="table">
            <?php if (!empty($props['headers'])): ?>
                <thead>
                    <tr>
                        <?php foreach ($props['headers'] as $header): ?>
                            <th><?= $header ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
            <?php endif; ?>
            <tbody>
                <?= $slot ?? '' ?>
            </tbody>
        </table>
    </div>
</div>
HTML;
    }

    /**
     * 获取卡片组件模板
     */
    private function getCardTemplate(): string
    {
        return <<<'HTML'
<div class="card <?= $attributes['class'] ?? '' ?>" <?= $this->renderAttributes($attributes) ?>>
    <?php if (!empty($props['title'])): ?>
        <div class="card-header">
            <h4 class="card-title"><?= $props['title'] ?></h4>
        </div>
    <?php endif; ?>
    
    <div class="card-body">
        <?= $slot ?? '' ?>
    </div>
    
    <?php if (!empty($props['footer'])): ?>
        <div class="card-footer">
            <?= $props['footer'] ?>
        </div>
    <?php endif; ?>
</div>
HTML;
    }

    /**
     * 获取通用组件模板
     */
    private function getGenericTemplate($componentName): string
    {
        return <<<HTML
<div class="{$componentName} <?= \$attributes['class'] ?? '' ?>" <?= \$this->renderAttributes(\$attributes) ?>>
    <?= \$slot ?? '' ?>
</div>
HTML;
    }

    /**
     * 转换页面文件
     */
    private function convertPages($files)
    {
        foreach ($files as $file) {
            $this->convertSinglePage($file);
        }
        
        $this->logger->info('页面转换完成');
    }

    /**
     * 转换单个页面
     */
    private function convertSinglePage($file)
    {
        $content = file_get_contents($file);
        $dom = new DOMDocument();
        @$dom->loadHTML($content);
        
        $filename = basename($file, '.html');
        $phpContent = $this->generatePHPPage($dom, $filename);
        
        $outputFile = $this->config['target_dir'] . '/pages/' . $filename . '.php';
        file_put_contents($outputFile, $phpContent);
        
        $this->logger->info("页面转换完成: {$filename}");
    }

    /**
     * 生成PHP页面
     */
    private function generatePHPPage($dom, $filename): string
    {
        $xpath = new DOMXPath($dom);
        
        // 提取页面元数据
        $title = $this->extractTitle($xpath);
        $meta = $this->extractMeta($xpath);
        $bodyClass = $this->extractBodyClass($xpath);
        
        // 提取页面内容
        $bodyContent = $this->extractBodyContent($dom);
        
        // 生成PHP代码
        return <<<PHP
<?php
/**
 * {$filename} 页面
 * 自动从HTML转换而来
 */

use AlingAi\Views\Layouts\MasterLayout;

\$layout = new MasterLayout(
    title: '{$title}',
    meta: {$this->arrayToPhp($meta)},
    bodyClass: '{$bodyClass}'
);

\$content = <<<'HTML'
{$bodyContent}
HTML;

echo \$layout->render(\$content, \$data ?? []);
PHP;
    }

    /**
     * 提取标题
     */
    private function extractTitle($xpath): string
    {
        $titleNode = $xpath->query('//title')->item(0);
        return $titleNode ? $titleNode->textContent : 'AlingAi Pro';
    }

    /**
     * 提取元数据
     */
    private function extractMeta($xpath): array
    {
        $meta = [];
        $metaNodes = $xpath->query('//meta');
        
        foreach ($metaNodes as $node) {
            $name = $node->getAttribute('name');
            $property = $node->getAttribute('property');
            $content = $node->getAttribute('content');
            
            if ($name && $content) {
                $meta[$name] = $content;
            } elseif ($property && $content) {
                $meta[$property] = $content;
            }
        }
        
        return $meta;
    }

    /**
     * 提取body类名
     */
    private function extractBodyClass($xpath): string
    {
        $bodyNode = $xpath->query('//body')->item(0);
        return $bodyNode ? $bodyNode->getAttribute('class') : '';
    }

    /**
     * 提取body内容
     */
    private function extractBodyContent($dom): string
    {
        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body) {
            return '';
        }
        
        $content = '';
        foreach ($body->childNodes as $node) {
            $content .= $dom->saveHTML($node);
        }
        
        return $this->cleanupHTML($content);
    }

    /**
     * 清理HTML内容
     */
    private function cleanupHTML($content): string
    {
        // 移除不必要的空白
        $content = preg_replace('/\s+/', ' ', $content);
        
        // 清理换行
        $content = str_replace(["\r\n", "\r", "\n"], '', $content);
        
        // 恢复必要的换行
        $content = str_replace(['><', '></'], [">\n<", ">\n</"], $content);
        
        return trim($content);
    }

    /**
     * 将数组转换为PHP代码
     */
    private function arrayToPhp($array): string
    {
        if (empty($array)) {
            return '[]';
        }
        
        $items = [];
        foreach ($array as $key => $value) {
            $items[] = "'{$key}' => '{$value}'";
        }
        
        return '[' . implode(', ', $items) . ']';
    }

    /**
     * 生成路由配置
     */
    private function generateRoutes($files)
    {
        $routes = [];
        
        foreach ($files as $file) {
            $filename = basename($file, '.html');
            $route = $this->generateRouteFromFilename($filename);
            $routes[] = $route;
        }
        
        $routeContent = $this->generateRouteFile($routes);
        file_put_contents('config/routes_migrated.php', $routeContent);
        
        $this->logger->info('路由配置生成完成');
    }

    /**
     * 从文件名生成路由
     */
    private function generateRouteFromFilename($filename): array
    {
        $path = '/' . str_replace(['_', 'index'], ['/', ''], $filename);
        $path = rtrim($path, '/') ?: '/';
        
        return [
            'path' => $path,
            'controller' => ucfirst($filename) . 'Controller',
            'action' => 'index',
            'name' => $filename
        ];
    }

    /**
     * 生成路由文件
     */
    private function generateRouteFile($routes): string
    {
        $routeLines = [];
        
        foreach ($routes as $route) {
            $routeLines[] = "    Route::get('{$route['path']}', [{$route['controller']}::class, '{$route['action']}'])->name('{$route['name']}');";
        }
        
        return <<<PHP
<?php
/**
 * 迁移生成的路由配置
 */

use Illuminate\Support\Facades\Route;

{implode("\n", $routeLines)}
PHP;
    }

    /**
     * 生成控制器
     */
    private function generateControllers($files)
    {
        foreach ($files as $file) {
            $filename = basename($file, '.html');
            $this->generateController($filename);
        }
        
        $this->logger->info('控制器生成完成');
    }

    /**
     * 生成单个控制器
     */
    private function generateController($filename)
    {
        $className = ucfirst($filename) . 'Controller';
        
        $content = <<<PHP
<?php

namespace AlingAi\Controllers;

use AlingAi\Core\Controller;
use AlingAi\Views\Layouts\MasterLayout;

/**
 * {$filename} 控制器
 * 自动生成的现代PHP8.0+控制器
 */
class {$className} extends Controller
{
    public function index(): string
    {
        \$data = \$this->getData();
        
        return \$this->render('{$filename}', \$data);
    }

    private function getData(): array
    {
        return [
            'title' => '{$filename}页面',
            'timestamp' => time(),
            'user' => \$this->getCurrentUser()
        ];
    }

    private function getCurrentUser(): ?array
    {
        // 实现用户获取逻辑
        return null;
    }
}
PHP;

        $controllerDir = 'src/Controllers/Generated';
        if (!is_dir($controllerDir)) {
            mkdir($controllerDir, 0755, true);
        }
        
        file_put_contents("{$controllerDir}/{$className}.php", $content);
    }

    /**
     * 优化资源文件
     */
    private function optimizeAssets()
    {
        $this->optimizeCSS();
        $this->optimizeJavaScript();
        $this->optimizeImages();
        
        $this->logger->info('资源优化完成');
    }

    /**
     * 优化CSS
     */
    private function optimizeCSS()
    {
        // 合并和压缩CSS文件
        $cssFiles = glob('public/assets/css/*.css');
        $combinedCSS = '';
        
        foreach ($cssFiles as $file) {
            $content = file_get_contents($file);
            $combinedCSS .= $this->minifyCSS($content);
        }
        
        file_put_contents('public/assets/css/app.min.css', $combinedCSS);
    }

    /**
     * 压缩CSS
     */
    private function minifyCSS($css): string
    {
        // 移除注释
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // 移除不必要的空白
        $css = str_replace(["\r\n", "\r", "\n", "\t", '  ', '   ', '    '], '', $css);
        
        return $css;
    }

    /**
     * 优化JavaScript
     */
    private function optimizeJavaScript()
    {
        // 合并和压缩JS文件
        $jsFiles = glob('public/assets/js/*.js');
        $combinedJS = '';
        
        foreach ($jsFiles as $file) {
            $content = file_get_contents($file);
            $combinedJS .= $this->minifyJS($content);
        }
        
        file_put_contents('public/assets/js/app.min.js', $combinedJS);
    }

    /**
     * 压缩JavaScript
     */
    private function minifyJS($js): string
    {
        // 基本的JS压缩
        $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js); // 移除多行注释
        $js = preg_replace('/\/\/.*$/', '', $js); // 移除单行注释
        $js = preg_replace('/\s+/', ' ', $js); // 压缩空白
        
        return $js;
    }

    /**
     * 优化图片
     */
    private function optimizeImages()
    {
        // 图片优化逻辑（需要图片处理库支持）
        $this->logger->info('图片优化跳过（需要额外的图片处理库）');
    }

    /**
     * 生成迁移报告
     */
    private function generateMigrationReport($files): array
    {
        $report = [
            'summary' => [
                'total_files' => count($files),
                'converted_files' => count($files),
                'generated_components' => count($this->componentMapping),
                'migration_time' => date('Y-m-d H:i:s')
            ],
            'files' => array_map('basename', $files),
            'components' => array_keys($this->componentMapping),
            'optimizations' => [
                'css_minified' => true,
                'js_minified' => true,
                'images_optimized' => false
            ]
        ];
        
        $reportFile = 'reports/frontend_migration_' . date('Y-m-d_H-i-s') . '.json';
        $reportDir = dirname($reportFile);
        
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0755, true);
        }
        
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
        
        return $report;
    }
}
