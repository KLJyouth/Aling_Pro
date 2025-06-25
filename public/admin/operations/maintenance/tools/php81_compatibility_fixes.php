<?php
/**
 * PHP 8.1�������޸�������
 */

function fix_php81_compatibility_issues($content) {
    $fixed = false;
    $fixed_content = $content;
    
    // �޸��ַ�����Ϊ��������ȱ����������
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
        "��Ӧ����" => "��Ӧ����",
        "������" => "������",
        "API�ĵ���������" => "API�ĵ�������",
        "API�ĵ�" => "API�ĵ�",
        "API�ĵ�ϵͳ" => "API�ĵ�ϵͳ",
        "��֤" => "��֤",
        "�û���¼" => "�û���¼",
        "����" => "����",
        "���ؿ�������" => "���ؿ�������",
        "��������" => "��������"
    ];
}
