<?php
// 检查API文档文件
require_once "public/admin/api/documentation/index.php";

// 生成并检查API文档
try {
    $docs = generateApiDocumentation();
    echo "API文档生成成功\n";
    
    // 检查关键路径
    if (isset($docs["paths"]["/api/auth/login"]["post"]["responses"]["200"]["content"]["application/json"]["schema"]["$ref"])) {
        echo "路径引用检查通过\n";
    } else {
        echo "路径引用检查失败\n";
    }
    
    if (isset($docs["components"]["schemas"]["AuthResponse"]["properties"]["data"]["properties"]["user"]["$ref"])) {
        echo "组件引用检查通过\n";
    } else {
        echo "组件引用检查失败\n";
    }
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
