<?php

/**
 * ��Ⲣ�޸�PHP�ļ��е��﷨�����������������
 * ������
 * 1. �������루��������
 * 2. ���Ų�ƥ�䵼�µ��﷨����
 * 3. �����׺ͳһΪ @gxggm.com
 * 4. �汾��ͳһΪ 6.0.0
 */

// ����ִ��ʱ�䣬��ֹ��ʱ
set_time_limit(0];
ini_set("memory_limit", "1024M"];

// ��־�ļ�
$log_file = "php_errors_fix_log_" . date("Ymd_His") . ".txt";
$report_file = "PHP_ERRORS_FIX_REPORT.md";

// ͳ������
$stats = [
    "total_files" => 0,
    "scanned_files" => 0,
    "error_files" => 0,
    "fixed_files" => 0,
    "encoding_issues" => 0,
    "syntax_errors" => 0,
    "email_updates" => 0,
    "version_updates" => 0,
];


// Ҫ�ų���Ŀ¼
$exclude_dirs = [
    ".git",
    "vendor",
    "node_modules",
    "backups",
    "backup",
    "tmp",
    "temp",
    "logs",
];

// Ҫ�����ļ���չ��
$extensions = [
    "php",
    "phtml",
    "php5",
    "php7",
    "phps",
];

// ��ʼ����־
function init_log() {
    global $log_file;
    file_put_contents($log_file, "=== PHP���������޸���־ - " . date("Y-m-d H:i:s") . " ===\n\n"];
}

// д����־
function log_message($message) {
    global $log_file;
    file_put_contents($log_file, $message . "\n", FILE_APPEND];
    echo $message . "\n";
}

// ����ļ��Ƿ���BOM���
function has_bom($content) {
    return strpos($content, "\xEF\xBB\xBF") === 0;
}

// �Ƴ�BOM���
function remove_bom($content) {
    if (has_bom($content)) {
        return substr($content, 3];
    }
    return $content;
}

// ��鲢�޸���������
function fix_chinese_encoding($content) {
    $has_encoding_issues = preg_match('/��/', $content];
    
    if ($has_encoding_issues) {
        // ��������滻һЩ���������룬ʵ�����������Ҫ�����ӵĴ���
        $fixes = [
            // �������Ķ�Ӧ�����滻
            '��Ӧ����' => '��Ӧ����',
            '������' => '������',
            'API�ĵ���������' => 'API�ĵ�������',
            '��֤' => '��֤',
            '�û���¼' => '�û���¼',
            '����' => '����',
            '���ؿ�������' => '���ؿ�������',
            '��������' => '��������',
            '��Ȩ��֤' => '��Ȩ��֤',
            'δ�ṩ��֤��Ϣ' => 'δ�ṩ��֤��Ϣ',
            '��Ч������' => '��Ч������',
            '��Ч��API��Կ' => '��Ч��API��Կ',
            '��֤ʧ��' => '��֤ʧ��',
            '��������' => '������',
            '�����OPTIONS����CORSԤ����Ӧ����ǰ�洦��' => '����OPTIONS�����CORSԤ����Ӧ��ǰ�ô���',
            '����·���ͷ����ַ�����' => '����·���ͷ����ַ�����',
            '��ȡAPI�ĵ��ṹ' => '��ȡAPI�ĵ��ṹ',
            '�ɹ���ȡAPI�ĵ�' => '�ɹ���ȡAPI�ĵ�',
            'Ĭ�Ϸ���������API�ĵ�' => 'Ĭ�Ϸ���������API�ĵ�',
            'ִ��������' => 'ִ��������',
            '��������ʱ��������' => '��������ʱ��������',
            // ��Ӹ��ೣ�������滻
            '����API�ĵ� - �����û����������졢ϵͳ��ص����й���?' => 'AlingAi Pro API�ĵ�ϵͳ - �û�����ϵͳ��صȹ���'
        ];
        
        $fixed_content = $content;
        foreach ($fixes as $broken => $fixed) {
            $fixed_content = str_replace($broken, $fixed, $fixed_content];
        }
        
        return [
            'fixed' => $fixed_content !== $content,
            'content' => $fixed_content
        ];
    }
    
    return [
        'fixed' => false,
        'content' => $content
    ];
}

// ��鲢�޸��﷨����
function fix_syntax_errors($content) {
    $fixed = false;
    $fixed_content = $content;
    
    // 1. �޸����Ų�ƥ������
    $patterns = [
        // �޸��ַ�����ȱ�ٽ������ŵ����
        '/(["\'].*],\s*$/m' => '$1",',
        // ���������﷨����ģʽ...
    ];
    
    foreach ($patterns as $pattern => $replacement) {
        $new_content = preg_replace($pattern, $replacement, $fixed_content];
        if ($new_content !== $fixed_content) {
            $fixed = true;
            $fixed_content = $new_content;
        }
    }
    
    return [
        'fixed' => $fixed,
        'content' => $fixed_content
    ];
}

// ��������Ͱ汾��
function update_constants($content) {
    $fixed = false;
    $fixed_content = $content;
    
    // ���������׺
    $email_pattern = '/"email"\s*=>\s*"([^@"]+)@[^"]+"/';
    $email_replacement = '"email" => "$1@gxggm.com"';
    
    $new_content = preg_replace($email_pattern, $email_replacement, $fixed_content];
    if ($new_content !== $fixed_content) {
        $fixed = true;
        $fixed_content = $new_content;
        global $stats;
        $stats['email_updates']++;
    }
    
    // ���°汾��
    $version_pattern = '/"version"\s*=>\s*"(\d+\.\d+\.\d+)"/';
    $version_replacement = '"version" => "6.0.0"';
    
    $new_content = preg_replace($version_pattern, $version_replacement, $fixed_content];
    if ($new_content !== $fixed_content) {
        $fixed = true;
        $fixed_content = $new_content;
        global $stats;
        $stats['version_updates']++;
    }
    
    return [
        'fixed' => $fixed,
        'content' => $fixed_content
    ];
}

// ����Ƿ�Ϊ��Ч��PHP�ļ�
function is_valid_php_file($file) {
    global $extensions;
    $ext = pathinfo($file, PATHINFO_EXTENSION];
    return in_[strtolower($ext], $extensions];
}

// ����Ƿ���Ҫ�ų���Ŀ¼
function should_exclude_dir($dir) {
    global $exclude_dirs;
    $basename = basename($dir];
    return in_[$basename, $exclude_dirs];
}

// �ݹ�ɨ��Ŀ¼
function scan_directory($dir) {
    global $stats;
    
    if (should_exclude_dir($dir)) {
        return;
    }
    
    $items = scandir($dir];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        
        if (is_dir($path)) {
            scan_directory($path];
        } elseif (is_file($path) && is_valid_php_file($path)) {
            $stats['total_files']++;
            process_file($path];
        }
    }
}

// �������ļ�
function process_file($file) {
    global $stats;
    
    $stats['scanned_files']++;
    
    try {
        $content = file_get_contents($file];
        if ($content === false) {
            log_message("�޷���ȡ�ļ�: $file"];
            return;
        }
        
        $original_content = $content;
        $changed = false;
        
        // 1. �Ƴ�BOM���
        $content = remove_bom($content];
        $bom_removed = $content !== $original_content;
        if ($bom_removed) {
            $changed = true;
            log_message("���Ƴ�BOM���: $file"];
        }
        
        // 2. �޸���������
        $encoding_result = fix_chinese_encoding($content];
        if ($encoding_result['fixed']) {
            $content = $encoding_result['content'];
            $changed = true;
            $stats['encoding_issues']++;
            log_message("���޸���������: $file"];
        }
        
        // 3. �޸��﷨����
        $syntax_result = fix_syntax_errors($content];
        if ($syntax_result['fixed']) {
            $content = $syntax_result['content'];
            $changed = true;
            $stats['syntax_errors']++;
            log_message("���޸��﷨����: $file"];
        }
        
        // 4. ��������Ͱ汾��
        $constants_result = update_constants($content];
        if ($constants_result['fixed']) {
            $content = $constants_result['content'];
            $changed = true;
            log_message("�Ѹ��������汾��: $file"];
        }
        
        // ����б���������ļ�
        if ($changed) {
            // �ȴ�������
            $backup_file = $file . '.bak.' . date('YmdHis'];
            file_put_contents($backup_file, $original_content];
            
            // �����޸ĺ���ļ�
            if (file_put_contents($file, $content) !== false) {
                $stats['fixed_files']++;
                log_message("�ѳɹ��޸�������: $file"];
            } else {
                log_message("�޷�д���ļ�: $file"];
            }
        }
        
    } catch (Exception $e) {
        $stats['error_files']++;
        log_message("�����ļ�ʱ���� {$file}: " . $e->getMessage()];
    }
}

// ���ɱ���
function generate_report() {
    global $stats, $report_file;
    
    $report = "# PHP�����޸�����\n\n";
    $report .= "## ɨ��ժҪ\n\n";
    $report .= "* ɨ��ʱ��: " . date('Y-m-d H:i:s') . "\n";
    $report .= "* ���ļ���: {$stats['total_files']}\n";
    $report .= "* ɨ���ļ���: {$stats['scanned_files']}\n";
    $report .= "* �����ļ���: {$stats['error_files']}\n";
    $report .= "* �޸��ļ���: {$stats['fixed_files']}\n\n";
    
    $report .= "## �޸�����ͳ��\n\n";
    $report .= "* ������������: {$stats['encoding_issues']}\n";
    $report .= "* �﷨����: {$stats['syntax_errors']}\n";
    $report .= "* �������: {$stats['email_updates']}\n";
    $report .= "* �汾�Ÿ���: {$stats['version_updates']}\n\n";
    
    $report .= "## �޸�˵��\n\n";
    $report .= "����ɨ���޸����������͵����⣺\n\n";
    $report .= "1. **������������** - �޸������ȳ�����������\n";
    $report .= "2. **�﷨����** - �޸������Ų�ƥ����﷨����\n";
    $report .= "3. **�����׼��** - �����������׺ͳһΪ @gxggm.com\n";
    $report .= "4. **�汾��ͳһ** - �����а汾��ͳһΪ 6.0.0\n\n";
    
    $report .= "## ����\n\n";
    $report .= "1. �ڴ���������ļ�ʱʹ��UTF-8���룬�����������\n";
    $report .= "2. �ڱ༭PHP�ļ�ʱʹ��֧���﷨�����ı༭�������Լ�ʱ�����﷨����\n";
    $report .= "3. ��������Զ������ԣ��ڲ���ǰ���PHP�﷨����\n";
    $report .= "4. ʹ��ͳһ�����ù���ϵͳ����汾�ź���ϵ����ȳ���\n\n";
    
    $report .= "## ����\n\n";
    $report .= "ϵͳ�޸���ɣ��ѽ�����м�⵽�����⡣���ڸ����ӵ��﷨���󣬿�����Ҫ�ֶ�����޸���\n";
    
    file_put_contents($report_file, $report];
    log_message("�����ɱ���: $report_file"];
}

// ������
function main() {
    init_log(];
    log_message("��ʼɨ����޸�PHP�ļ�..."];
    
    $start_time = microtime(true];
    $root_dir = __DIR__;
    
    scan_directory($root_dir];
    
    $end_time = microtime(true];
    $execution_time = round($end_time - $start_time, 2];
    
    log_message("ɨ����޸���ɣ���ʱ: {$execution_time} ��"];
    
    generate_report(];
}

// ִ��������
main(];


