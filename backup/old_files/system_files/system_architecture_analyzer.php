<?php

/**
 * AlingAi Pro 系统架构分析器
 * 
 * 此脚本分析当前系统架构，生成架构文档和依赖关系图
 * 
 * @author AlingAi Pro Team
 * @version 1.0.0
 */

class SystemArchitectureAnalyzer {
    
    private $projectRoot;
    private $srcPath;
    private $analysis = [];
    
    public function __construct($projectRoot) {
        $this->projectRoot = $projectRoot;
        $this->srcPath = $projectRoot . '/src';
    }
    
    /**
     * 执行完整的系统架构分析
     */
    public function analyze() {
        echo "=== AlingAi Pro 系统架构分析 ===\n\n";
        
        $this->analyzeDirectoryStructure();
        $this->analyzeControllers();
        $this->analyzeServices();
        $this->analyzeModels();
        $this->analyzeMiddleware();
        $this->analyzeDependencies();
        $this->generateArchitectureReport();
        
        return $this->analysis;
    }
    
    /**
     * 分析目录结构
     */
    private function analyzeDirectoryStructure() {
        echo "📁 分析目录结构...\n";
        
        $structure = $this->scanDirectory($this->srcPath);
        $this->analysis['directory_structure'] = $structure;
          echo "   ✓ 发现 " . count($structure) . " 个主要模块\n";
        foreach ($structure as $module => $info) {
            echo "     - {$module}: " . $info['files'] . " 个文件\n";
        }
        echo "\n";
    }
    
    /**
     * 分析控制器
     */
    private function analyzeControllers() {
        echo "🎮 分析控制器结构...\n";
        
        $controllersPath = $this->srcPath . '/Controllers';
        $controllers = [];
        
        if (is_dir($controllersPath)) {
            $this->scanControllers($controllersPath, $controllers);
        }
          $this->analysis['controllers'] = $controllers;
        echo "   ✓ 发现 " . count($controllers) . " 个控制器\n";
        
        foreach ($controllers as $controller => $info) {
            $methodCount = isset($info['methods']) && is_array($info['methods']) ? count($info['methods']) : 0;
            echo "     - {$controller}: " . $methodCount . " 个方法\n";
        }
        echo "\n";
    }
    
    /**
     * 分析服务层
     */
    private function analyzeServices() {
        echo "⚙️ 分析服务层结构...\n";
        
        $servicesPath = $this->srcPath . '/Services';
        $services = [];
        
        if (is_dir($servicesPath)) {
            $this->scanServices($servicesPath, $services);
        }
        
        $this->analysis['services'] = $services;
        echo "   ✓ 发现 " . count($services) . " 个服务\n";
        echo "\n";
    }
    
    /**
     * 分析模型层
     */
    private function analyzeModels() {
        echo "📊 分析模型层结构...\n";
        
        $modelsPath = $this->srcPath . '/Models';
        $models = [];
        
        if (is_dir($modelsPath)) {
            $this->scanModels($modelsPath, $models);
        }
        
        $this->analysis['models'] = $models;
        echo "   ✓ 发现 " . count($models) . " 个模型\n";
        echo "\n";
    }
    
    /**
     * 分析中间件
     */
    private function analyzeMiddleware() {
        echo "🔒 分析中间件结构...\n";
        
        $middlewarePath = $this->srcPath . '/Middleware';
        $middleware = [];
        
        if (is_dir($middlewarePath)) {
            $this->scanMiddleware($middlewarePath, $middleware);
        }
        
        $this->analysis['middleware'] = $middleware;
        echo "   ✓ 发现 " . count($middleware) . " 个中间件\n";
        echo "\n";
    }
    
    /**
     * 分析依赖关系
     */
    private function analyzeDependencies() {
        echo "🔗 分析依赖关系...\n";
        
        $dependencies = [
            'composer' => $this->analyzeComposerDependencies(),
            'internal' => $this->analyzeInternalDependencies()
        ];
        
        $this->analysis['dependencies'] = $dependencies;
        echo "   ✓ 分析完成\n\n";
    }
    
    /**
     * 扫描目录
     */
    private function scanDirectory($path, $level = 0) {
        $structure = [];
        
        if (!is_dir($path)) {
            return $structure;
        }
        
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $itemPath = $path . '/' . $item;
            if (is_dir($itemPath)) {
                $structure[$item] = [
                    'type' => 'directory',
                    'path' => $itemPath,
                    'files' => $this->countPhpFiles($itemPath),
                    'subdirs' => $this->countSubdirectories($itemPath)
                ];
            }
        }
        
        return $structure;
    }
    
    /**
     * 扫描控制器
     */
    private function scanControllers($path, &$controllers) {
        $files = glob($path . '/*.php');
        foreach ($files as $file) {
            $className = basename($file, '.php');
            $controllers[$className] = [
                'file' => $file,
                'methods' => $this->extractMethods($file),
                'routes' => $this->extractRoutes($file)
            ];
        }
        
        // 递归扫描子目录
        $dirs = glob($path . '/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $this->scanControllers($dir, $controllers);
        }
    }
    
    /**
     * 扫描服务
     */
    private function scanServices($path, &$services) {
        $files = glob($path . '/*.php');
        foreach ($files as $file) {
            $className = basename($file, '.php');
            $services[$className] = [
                'file' => $file,
                'methods' => $this->extractMethods($file),
                'dependencies' => $this->extractClassDependencies($file)
            ];
        }
        
        // 递归扫描子目录
        $dirs = glob($path . '/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $this->scanServices($dir, $services);
        }
    }
    
    /**
     * 扫描模型
     */
    private function scanModels($path, &$models) {
        $files = glob($path . '/*.php');
        foreach ($files as $file) {
            $className = basename($file, '.php');
            $models[$className] = [
                'file' => $file,
                'properties' => $this->extractProperties($file),
                'methods' => $this->extractMethods($file)
            ];
        }
        
        // 递归扫描子目录
        $dirs = glob($path . '/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $this->scanModels($dir, $models);
        }
    }
    
    /**
     * 扫描中间件
     */
    private function scanMiddleware($path, &$middleware) {
        $files = glob($path . '/*.php');
        foreach ($files as $file) {
            $className = basename($file, '.php');
            $middleware[$className] = [
                'file' => $file,
                'methods' => $this->extractMethods($file)
            ];
        }
        
        // 递归扫描子目录
        $dirs = glob($path . '/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $this->scanMiddleware($dir, $middleware);
        }
    }
    
    /**
     * 提取类方法
     */
    private function extractMethods($file) {
        $methods = [];
        
        if (!file_exists($file)) {
            return $methods;
        }
        
        $content = file_get_contents($file);
        
        // 简单的正则表达式匹配方法
        preg_match_all('/(?:public|private|protected)\s+function\s+(\w+)\s*\(([^)]*)\)/', $content, $matches);
        
        if (!empty($matches[1])) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                $methods[] = [
                    'name' => $matches[1][$i],
                    'parameters' => trim($matches[2][$i]),
                    'visibility' => $this->extractMethodVisibility($content, $matches[1][$i])
                ];
            }
        }
        
        return $methods;
    }
    
    /**
     * 提取类属性
     */
    private function extractProperties($file) {
        $properties = [];
        
        if (!file_exists($file)) {
            return $properties;
        }
        
        $content = file_get_contents($file);
        
        // 匹配属性
        preg_match_all('/(?:public|private|protected)\s+\$(\w+)/', $content, $matches);
        
        if (!empty($matches[1])) {
            $properties = $matches[1];
        }
        
        return $properties;
    }
    
    /**
     * 提取路由信息
     */
    private function extractRoutes($file) {
        $routes = [];
        
        if (!file_exists($file)) {
            return $routes;
        }
        
        $content = file_get_contents($file);
        
        // 简单匹配路由注释或路由定义
        preg_match_all('/@Route\(["\'](.*?)["\']\)/', $content, $matches);
        
        if (!empty($matches[1])) {
            $routes = $matches[1];
        }
        
        return $routes;
    }
    
    /**
     * 提取类依赖
     */
    private function extractClassDependencies($file) {
        $dependencies = [];
        
        if (!file_exists($file)) {
            return $dependencies;
        }
        
        $content = file_get_contents($file);
        
        // 匹配 use 语句
        preg_match_all('/use\s+([^;]+);/', $content, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $use) {
                $dependencies[] = trim($use);
            }
        }
        
        return $dependencies;
    }
    
    /**
     * 提取方法可见性
     */
    private function extractMethodVisibility($content, $methodName) {
        if (preg_match("/public\s+function\s+{$methodName}/", $content)) {
            return 'public';
        } elseif (preg_match("/private\s+function\s+{$methodName}/", $content)) {
            return 'private';
        } elseif (preg_match("/protected\s+function\s+{$methodName}/", $content)) {
            return 'protected';
        }
        return 'unknown';
    }
    
    /**
     * 统计 PHP 文件数量
     */
    private function countPhpFiles($path) {
        $count = 0;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * 统计子目录数量
     */
    private function countSubdirectories($path) {
        $dirs = glob($path . '/*', GLOB_ONLYDIR);
        return count($dirs);
    }
    
    /**
     * 分析 Composer 依赖
     */
    private function analyzeComposerDependencies() {
        $composerFile = $this->projectRoot . '/composer.json';
        $dependencies = [];
        
        if (file_exists($composerFile)) {
            $composerData = json_decode(file_get_contents($composerFile), true);
            
            if (isset($composerData['require'])) {
                foreach ($composerData['require'] as $package => $version) {
                    $dependencies['production'][$package] = $version;
                }
            }
            
            if (isset($composerData['require-dev'])) {
                foreach ($composerData['require-dev'] as $package => $version) {
                    $dependencies['development'][$package] = $version;
                }
            }
        }
        
        return $dependencies;
    }
      /**
     * 分析内部依赖
     */
    private function analyzeInternalDependencies() {
        $dependencies = [];
        
        // 分析控制器对服务的依赖
        foreach ($this->analysis['controllers'] ?? [] as $controller => $info) {
            if (is_array($info) && isset($info['methods']) && is_array($info['methods'])) {
                foreach ($info['methods'] as $method) {
                    // 这里可以添加更复杂的依赖分析逻辑
                }
            }
        }
        
        return $dependencies;
    }
    
    /**
     * 生成架构报告
     */
    private function generateArchitectureReport() {
        echo "📋 生成架构报告...\n";
        
        $report = $this->generateDetailedReport();
        $reportFile = $this->projectRoot . '/ARCHITECTURE_ANALYSIS.md';
        
        file_put_contents($reportFile, $report);
        
        echo "   ✓ 架构报告已生成: {$reportFile}\n\n";
        
        // 生成简化的架构图
        $this->generateArchitectureDiagram();
    }
    
    /**
     * 生成详细报告
     */
    private function generateDetailedReport() {
        $report = "# AlingAi Pro 系统架构分析报告\n\n";
        $report .= "生成时间: " . date('Y-m-d H:i:s') . "\n\n";
        
        // 系统概览
        $report .= "## 系统概览\n\n";
        $report .= "| 组件 | 数量 | 说明 |\n";
        $report .= "|------|------|------|\n";
        $report .= "| 控制器 | " . count($this->analysis['controllers'] ?? []) . " | API 端点和业务逻辑入口 |\n";
        $report .= "| 服务类 | " . count($this->analysis['services'] ?? []) . " | 业务逻辑处理层 |\n";
        $report .= "| 模型类 | " . count($this->analysis['models'] ?? []) . " | 数据模型和数据库交互 |\n";
        $report .= "| 中间件 | " . count($this->analysis['middleware'] ?? []) . " | 请求处理中间层 |\n";
        $report .= "| 模块目录 | " . count($this->analysis['directory_structure'] ?? []) . " | 功能模块组织 |\n\n";
        
        // 目录结构
        $report .= "## 目录结构\n\n";
        foreach ($this->analysis['directory_structure'] ?? [] as $module => $info) {
            $report .= "### {$module}\n";
            $report .= "- PHP 文件数: {$info['files']}\n";
            $report .= "- 子目录数: {$info['subdirs']}\n\n";
        }
          // 控制器详情
        $report .= "## 控制器分析\n\n";
        foreach ($this->analysis['controllers'] ?? [] as $controller => $info) {
            if (!is_array($info)) continue;
            
            $report .= "### {$controller}\n";
            $report .= "- 文件: " . str_replace($this->projectRoot, '', $info['file'] ?? 'N/A') . "\n";
            $methodCount = isset($info['methods']) && is_array($info['methods']) ? count($info['methods']) : 0;
            $report .= "- 方法数: " . $methodCount . "\n";
            
            if ($methodCount > 0 && !empty($info['methods'])) {
                $report .= "- 方法列表:\n";
                foreach ($info['methods'] as $method) {
                    if (is_array($method)) {
                        $visibility = $method['visibility'] ?? 'public';
                        $name = $method['name'] ?? 'unknown';
                        $params = $method['parameters'] ?? '';
                        $report .= "  - `{$visibility} {$name}({$params})`\n";
                    }
                }
            }
            $report .= "\n";
        }
          // 依赖分析
        $report .= "## 依赖分析\n\n";
        $report .= "### 外部依赖 (Composer)\n\n";
        
        $prodDeps = $this->analysis['dependencies']['composer']['production'] ?? [];
        if (is_array($prodDeps) && !empty($prodDeps)) {
            $report .= "#### 生产环境依赖\n";
            foreach ($prodDeps as $package => $version) {
                $report .= "- {$package}: {$version}\n";
            }
            $report .= "\n";
        }
        
        $devDeps = $this->analysis['dependencies']['composer']['development'] ?? [];
        if (is_array($devDeps) && !empty($devDeps)) {
            $report .= "#### 开发环境依赖\n";
            foreach ($devDeps as $package => $version) {
                $report .= "- {$package}: {$version}\n";
            }
            $report .= "\n";
        }
        
        // 架构建议
        $report .= "## 架构优化建议\n\n";
        $report .= $this->generateArchitectureRecommendations();
        
        return $report;
    }
    
    /**
     * 生成架构图
     */
    private function generateArchitectureDiagram() {
        echo "🗺️ 生成架构图...\n";
        
        $diagram = $this->generateMermaidDiagram();
        $diagramFile = $this->projectRoot . '/ARCHITECTURE_DIAGRAM.md';
        
        file_put_contents($diagramFile, $diagram);
        
        echo "   ✓ 架构图已生成: {$diagramFile}\n\n";
    }
    
    /**
     * 生成 Mermaid 架构图
     */
    private function generateMermaidDiagram() {
        $diagram = "# AlingAi Pro 系统架构图\n\n";
        $diagram .= "## 系统整体架构\n\n";
        $diagram .= "```mermaid\n";
        $diagram .= "graph TB\n";
        $diagram .= "    Client[客户端] --> Nginx[Nginx 反向代理]\n";
        $diagram .= "    Nginx --> App[PHP 应用]\n";
        $diagram .= "    \n";
        $diagram .= "    subgraph 应用层\n";
        $diagram .= "        App --> Router[路由器]\n";
        $diagram .= "        Router --> Middleware[中间件]\n";
        $diagram .= "        Middleware --> Controllers[控制器]\n";
        $diagram .= "        Controllers --> Services[服务层]\n";
        $diagram .= "        Services --> Models[模型层]\n";
        $diagram .= "    end\n";
        $diagram .= "    \n";
        $diagram .= "    subgraph 数据层\n";
        $diagram .= "        Models --> Database[(MySQL)]\n";
        $diagram .= "        Services --> Cache[(Redis)]\n";
        $diagram .= "        Services --> Files[文件存储]\n";
        $diagram .= "    end\n";
        $diagram .= "    \n";
        $diagram .= "    subgraph 外部服务\n";
        $diagram .= "        Services --> AI[AI 服务]\n";
        $diagram .= "        Services --> Email[邮件服务]\n";
        $diagram .= "    end\n";
        $diagram .= "```\n\n";
        
        // 添加模块关系图
        $diagram .= "## 模块依赖关系\n\n";
        $diagram .= "```mermaid\n";
        $diagram .= "graph LR\n";
        
        // 根据分析结果生成模块关系
        $modules = array_keys($this->analysis['directory_structure'] ?? []);
        foreach ($modules as $module) {
            $diagram .= "    {$module}[{$module}]\n";
        }
        
        // 添加一些典型的依赖关系
        $diagram .= "    Controllers --> Services\n";
        $diagram .= "    Controllers --> Middleware\n";
        $diagram .= "    Services --> Models\n";
        $diagram .= "    Services --> Cache\n";
        $diagram .= "    Models --> Database\n";
        $diagram .= "    Middleware --> Security\n";
        $diagram .= "```\n\n";
        
        return $diagram;
    }
    
    /**
     * 生成架构建议
     */
    private function generateArchitectureRecommendations() {
        $recommendations = "";
        
        $controllerCount = count($this->analysis['controllers'] ?? []);
        $serviceCount = count($this->analysis['services'] ?? []);
        $modelCount = count($this->analysis['models'] ?? []);
        
        // 基于分析结果提供建议
        if ($serviceCount < $controllerCount * 0.5) {
            $recommendations .= "### 🔧 服务层优化\n";
            $recommendations .= "- 建议增加服务层抽象，当前服务类数量相对控制器较少\n";
            $recommendations .= "- 将业务逻辑从控制器迁移到专门的服务类中\n\n";
        }
        
        if ($modelCount < $serviceCount * 0.3) {
            $recommendations .= "### 📊 数据层优化\n";
            $recommendations .= "- 考虑为主要数据实体创建专门的模型类\n";
            $recommendations .= "- 实现数据访问对象 (DAO) 模式\n\n";
        }
        
        $recommendations .= "### 🏗️ 通用架构建议\n";
        $recommendations .= "1. **依赖注入**: 实现容器化的依赖注入\n";
        $recommendations .= "2. **接口抽象**: 为主要服务定义接口\n";
        $recommendations .= "3. **错误处理**: 统一异常处理机制\n";
        $recommendations .= "4. **日志记录**: 结构化日志记录\n";
        $recommendations .= "5. **缓存策略**: 多层缓存设计\n";
        $recommendations .= "6. **API 文档**: OpenAPI 规范文档\n";
        $recommendations .= "7. **测试覆盖**: 单元测试和集成测试\n";
        $recommendations .= "8. **性能监控**: 应用性能监控 (APM)\n\n";
        
        return $recommendations;
    }
}

// 执行分析
try {
    $projectRoot = dirname(__FILE__);
    $analyzer = new SystemArchitectureAnalyzer($projectRoot);
    $analysis = $analyzer->analyze();
    
    echo "🎉 系统架构分析完成！\n";
    echo "📊 分析结果已保存到 ARCHITECTURE_ANALYSIS.md\n";
    echo "🗺️ 架构图已保存到 ARCHITECTURE_DIAGRAM.md\n\n";
    
    echo "📈 分析摘要:\n";
    echo "- 控制器: " . count($analysis['controllers'] ?? []) . " 个\n";
    echo "- 服务类: " . count($analysis['services'] ?? []) . " 个\n";
    echo "- 模型类: " . count($analysis['models'] ?? []) . " 个\n";
    echo "- 中间件: " . count($analysis['middleware'] ?? []) . " 个\n";
    echo "- 模块目录: " . count($analysis['directory_structure'] ?? []) . " 个\n";
    
} catch (Exception $e) {
    echo "❌ 分析过程中出现错误: " . $e->getMessage() . "\n";
    echo "📍 错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
