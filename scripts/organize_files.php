<?php

class FileOrganizer {
    private $rootDir;
    private $categories = [
        'config' => [
            'dir' => 'config\\files',
            'patterns' => [
                'composer*.json',
                '*.ini',
                'redis.conf',
                'MERGED_ENV_FILES*.txt'
            ]
        ],
        'tests' => [
            'dir' => 'tests\\scripts',
            'patterns' => [
                'test_*.php',
                'diagnose_*.php',
                '*_analysis.php'
            ]
        ],
        'fixers' => [
            'dir' => 'scripts\\fixers',
            'patterns' => [
                '*_fixer.php',
                '*_analysis.php'
            ]
        ],
        'reports' => [
            'dir' => 'docs\\reports',
            'patterns' => [
                '*_REPORT_*.json',
                '*.txt'
            ]
        ],
        'deployment' => [
            'dir' => 'deployment\\files',
            'patterns' => [
                'docker-compose*.yml',
                '*.bat',
                'router.php'
            ]
        ]
    ];

    public function __construct($rootDir) {
        $this->rootDir = rtrim($rootDir, '\\/');
    }

    public function organize() {
        // 创建目录
        foreach ($this->categories as $category) {
            if (!is_dir($category['dir'])) {
                mkdir($category['dir'], 0755, true);
            }
        }

        // 移动文件
        $files = scandir($this->rootDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || is_dir($file)) {
                continue;
            }

            foreach ($this->categories as $category) {
                foreach ($category['patterns'] as $pattern) {
                    if (fnmatch($pattern, $file)) {
                        $source = $this->rootDir . '\\' . $file;
                        $target = $category['dir'] . '\\' . $file;
                        
                        // 如果目标文件已存在，添加时间戳
                        if (file_exists($target)) {
                            $info = pathinfo($file);
                            $target = $category['dir'] . '\\' . $info['filename'] . '_' . date('YmdHis') . '.' . $info['extension'];
                        }
                        
                        rename($source, $target);
                        echo "Moved {$file} to {$target}\n";
                        break 2;
                    }
                }
            }
        }
    }
}

// 执行整理
$organizer = new FileOrganizer(__DIR__ . '\\..');
$organizer->organize(); 