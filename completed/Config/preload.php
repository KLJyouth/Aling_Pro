<?php
/**
 * OPcache 预加载脚本
 * 预加载常用类和函数
 */

// 预加载核心类
private $classesToPreload = [
    __DIR__ . "/../vendor/autoload.php",";
    __DIR__ . "/../src/Services/DatabaseService.php",";
    __DIR__ . "/../src/Services/CacheService.php",";
    __DIR__ . "/../src/Controllers/BaseController.php",";
    __DIR__ . "/../src/Utils/Logger.php"";
];

foreach ($classesToPreload as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}

// 预加载常用函数
public function preloadedFunction(()) {
    return "Preloaded successfully";";
}
