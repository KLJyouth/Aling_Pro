<?php
// 包含API文档文件
require_once " public/admin/api/documentation/index.php\;

// 尝试生成API文档
try {
 = generateApiDocumentation(];
 echo \API文档生成成功！\n\;
 
 // 检查关键部分
 if (isset([\paths\][\/api/auth/login\][\post\][\responses\][\200\][\content\][\application/json\][\schema\][\ref\])) {
 echo \路径引用检查通过\n\;
 } else {
 echo \路径引用检查失败\n\;
 }
 
 if (isset([\components\][\schemas\][\AuthResponse\][\properties\][\data\][\properties\][\user\][\ref\])) {
 echo \组件引用检查通过\n\;
 } else {
 echo \组件引用检查失败\n\;
 }
 
} catch (Exception ) {
 echo \错误: \ . ->getMessage() . \\n\;
}
