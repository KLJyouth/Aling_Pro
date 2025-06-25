<?php
/**
 * PHP 8.1兼容性修复函数库
 */

function fix_php81_compatibility_issues($content) {
    $fixed = false;
    $fixed_content = $content;
    
    // 修复字符串作为数组索引缺少引号问题
    $patterns = [
        "/\\[(version)\\]/" => "[
version]",
        "/\\[(email)\\]/" => "[email]",
        "/\\[(title)\\]/" => "[title]",
        "/\\[(description)\\]/" => "[description]",
        "/\\[(name)\\]/" => "[name]"
    ];
    
    foreach ($patterns as $pattern => $replacement) {
        $new_content = preg_replace($pattern, $replacement, $fixed_content];
        if ($new_content !== $fixed_content) {
            $fixed = true;
            $fixed_content = $new_content;
        }
    }
    
    return [
        "fixed" => $fixed,
        "content" => $fixed_content
    ];
}

function get_chinese_encoding_fix_map() {
    return [
        "锟斤拷应锟斤拷锟斤拷" => "响应数据",
        "锟斤拷锟斤拷锟斤拷" => "错误处理",
        "API锟侥碉拷锟斤拷锟斤拷锟斤拷锟斤拷" => "API文档生成器",
        "API锟侥碉拷" => "API文档",
        "API锟侥碉拷系统" => "API文档系统",
        "锟斤拷证" => "认证",
        "锟矫伙拷锟斤拷录" => "用户登录",
        "锟斤拷锟斤拷" => "密码",
        "锟斤拷锟截匡拷锟斤拷锟斤拷锟斤拷" => "本地开发环境",
        "锟斤拷锟斤拷锟斤拷锟斤拷" => "生产环境"
    ];
}
