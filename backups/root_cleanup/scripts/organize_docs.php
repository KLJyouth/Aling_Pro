<?php

class DocOrganizer {
    private $categories = [
        'api' => [
            'keywords' => ['api', 'endpoint', 'route', '接口', '路由'],
            'files' => []
        ],
        'deployment' => [
            'keywords' => ['deploy', 'deployment', 'docker', 'kubernetes', '部署', '运维'],
            'files' => []
        ],
        'development' => [
            'keywords' => ['developer', 'development', 'guide', '开发', '指南'],
            'files' => []
        ],
        'reports' => [
            'keywords' => ['report', 'completion', 'fix', '报告', '完成'],
            'files' => []
        ],
        'security' => [
            'keywords' => ['security', 'encryption', 'crypto', '安全', '加密'],
            'files' => []
        ],
        'system' => [
            'keywords' => ['system', 'platform', '系统', '平台'],
            'files' => []
        ],
        'quantum' => [
            'keywords' => ['quantum', 'sm4', 'sm2', 'sm3', '量子'],
            'files' => []
        ]
    ];

    public function organize() {
        // 获取所有md文件
        $files = glob('*.md');
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $this->categorizeFile($file, $content);
        }
        
        // 移动文件到对应目录
        $this->moveFiles();
    }

    private function categorizeFile($file, $content) {
        $content = strtolower($content);
        
        foreach ($this->categories as $category => $config) {
            foreach ($config['keywords'] as $keyword) {
                if (strpos($content, strtolower($keyword)) !== false) {
                    $this->categories[$category]['files'][] = $file;
                    break;
                }
            }
        }
    }

    private function moveFiles() {
        foreach ($this->categories as $category => $config) {
            $targetDir = "docs/{$category}";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            foreach ($config['files'] as $file) {
                if (file_exists($file)) {
                    $targetFile = "{$targetDir}/" . basename($file);
                    rename($file, $targetFile);
                    echo "Moved {$file} to {$targetFile}\n";
                }
            }
        }
    }
}

// 执行整理
$organizer = new DocOrganizer();
$organizer->organize(); 