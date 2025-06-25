<?php
/**
 * ϵͳ����֤���޸��ű�
 * ���׶���֤���޸���Ŀ�е����⣬��ֹ����������
 */

// ����PHP 8.1�������޸�������
require_once 'php81_compatibility_fixes.php';

// ����ִ��ʱ�䣬��ֹ��ʱ
set_time_limit(0);
ini_set('memory_limit', '1024M');

// ��־�ͱ����ļ�
$log_file = 'systematic_fix_log_' . date('Ymd_His') . '.log';
$report_file = 'SYSTEMATIC_FIX_REPORT.md';
$backup_dir = 'backups/systematic_fix_' . date('Ymd_His');

// ��ʼ����־
file_put_contents($log_file, "=== ϵͳ����֤���޸���־ - " . date('Y-m-d H:i:s') . " ===\n\n");
echo "��ʼϵͳ����֤���޸�...\n";

// ��������Ŀ¼
if (!file_exists($backup_dir)) {
    mkdir($backup_dir, 0777, true);
}

// ͳ������
$stats = [
    'total_files' => 0,
    'scanned_files' => 0,
    'encoding_issues' => 0,
    'syntax_errors' => 0,
    'php81_issues' => 0,
    'fixed_files' => 0,
    'backup_files' => 0,
    'error_files' => 0
];

// Ҫ�ų���Ŀ¼
$exclude_dirs = [
    '.git',
    'vendor',
    'node_modules',
    'backups',
    'backup',
    'tmp',
    'temp',
    'logs',
    'php_temp',
    'portable_php'
];

// Ҫ������ļ���չ��
$file_extensions = [
    'php' => true,
    'phtml' => true,
    'php5' => true,
    'php7' => true,
    'phps' => true
];

// ��¼��־
function log_message($message) {
    global $log_file;
    echo $message . "\n";
    file_put_contents($log_file, $message . "\n", FILE_APPEND);
}

// �����ļ�
function backup_file($file) {
    global $backup_dir, $stats;
    
    $relative_path = str_replace('\\', '/', $file);
    $backup_path = $backup_dir . '/' . $relative_path;
    $backup_dir_path = dirname($backup_path);
    
    if (!file_exists($backup_dir_path)) {
        mkdir($backup_dir_path, 0777, true);
    }
    
    if (copy($file, $backup_path)) {
        $stats['backup_files']++;
        return true;
    }
    
    return false;
}

// ����ļ�����
function is_target_file($file) {
    global $file_extensions;
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    return isset($file_extensions[$ext]);
}

// ����Ƿ���Ҫ�ų�Ŀ¼
function should_exclude_dir($dir) {
    global $exclude_dirs;
    $basename = basename($dir);
    return in_array($basename, $exclude_dirs);
}

// ��鲢�Ƴ�BOM���
function check_and_remove_bom($content) {
    $bom = "\xEF\xBB\xBF";
    if (substr($content, 0, 3) === $bom) {
        return substr($content, 3);
    }
    return $content;
}

// ��鲢�޸���������
function check_and_fix_chinese_encoding($content) {
    // ����Ƿ�����������(��)
    if (strpos($content, '��') !== false) {
        // ��ȡӳ���
        $replacements = get_chinese_encoding_fix_map();
        
        $fixed_content = $content;
        foreach ($replacements as $broken => $fixed) {
            $fixed_content = str_replace($broken, $fixed, $fixed_content);
        }
        
        if ($fixed_content !== $content) {
            return [
                'fixed' => true,
                'content' => $fixed_content
            ];
        }
    }
    
    return [
        'fixed' => false,
        'content' => $content
    ];
}

// ��鲢�޸��﷨����
function check_and_fix_syntax_errors($content) {
    // 1. �޸����Ų�ƥ������
    $patterns = [
        // �޸��ַ�����ȱ�ٽ������ŵ����
        '/"([^"]*),\s*$/' => '"$1",',
        '/\'([^\']*),\s*$/' => "'$1',",
        // �޸������м�ֵ��ȱ�ٷָ��������
        '/=>([^,\s\n\]]*?)(\s*[\]\)])/' => '=> $1,$2'
    ];
    
    $fixed = false;
    $fixed_content = $content;
    
    foreach ($patterns as $pattern => $replacement) {
        $new_content = preg_replace($pattern, $replacement, $fixed_content);
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

// ���°汾�ź�����
function update_constants($content) {
    $fixed = false;
    $fixed_content = $content;
    
    // ���������׺
    $email_pattern = '/"email"\s*=>\s*"([^@"]+)@[^"]+"/';
    $email_replacement = '"email" => "$1@gxggm.com"';
    
    $new_content = preg_replace($email_pattern, $email_replacement, $fixed_content);
    if ($new_content !== $fixed_content) {
        $fixed = true;
        $fixed_content = $new_content;
    }
    
    // ���°汾��
    $version_pattern = '/"version"\s*=>\s*"(\d+\.\d+\.\d+)"/';
    $version_replacement = '"version" => "6.0.0"';
    
    $new_content = preg_replace($version_pattern, $version_replacement, $fixed_content);
    if ($new_content !== $fixed_content) {
        $fixed = true;
        $fixed_content = $new_content;
    }
    
    return [
        'fixed' => $fixed,
        'content' => $fixed_content
    ];
}

// ɨ��Ŀ¼
function scan_directory($dir) {
    global $stats, $log_file;

    try {
        $items = scandir($dir);
    } catch (Exception $e) {
        log_message("�޷�ɨ��Ŀ¼ $dir: " . $e->getMessage());
        return;
    }
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        
        if (is_dir($path)) {
            if (!should_exclude_dir($path)) {
                scan_directory($path);
            }
        } elseif (is_file($path)) {
            $stats['total_files']++;
            
            if (is_target_file($path)) {
                process_file($path);
            }
        }
    }
}

// �����ļ�
function process_file($file) {
    global $stats;
    
    $stats['scanned_files']++;
    
    try {
        $content = file_get_contents($file);
        if ($content === false) {
            log_message("�޷���ȡ�ļ�: $file");
            $stats['error_files']++;
            return;
        }
        
        $original_content = $content;
        $changed = false;
        
        // ��1�׶�: �Ƴ�BOM���
        $content = check_and_remove_bom($content);
        if ($content !== $original_content) {
            $changed = true;
            log_message("�Ƴ�BOM���: $file");
        }
        
        // ��2�׶�: �޸���������
        $encoding_result = check_and_fix_chinese_encoding($content);
        if ($encoding_result['fixed']) {
            $content = $encoding_result['content'];
            $changed = true;
            $stats['encoding_issues']++;
            log_message("�޸���������: $file");
        }
        
        // ��3�׶�: �޸��﷨����
        $syntax_result = check_and_fix_syntax_errors($content);
        if ($syntax_result['fixed']) {
            $content = $syntax_result['content'];
            $changed = true;
            $stats['syntax_errors']++;
            log_message("�޸��﷨����: $file");
        }
        
        // ��4�׶�: ���³���ֵ
        $constants_result = update_constants($content);
        if ($constants_result['fixed']) {
            $content = $constants_result['content'];
            $changed = true;
            log_message("���³���ֵ: $file");
        }
        
        // ��5�׶�: PHP 8.1 �������޸�
        $compatibility_result = fix_php81_compatibility_issues($content);
        if ($compatibility_result['fixed']) {
            $content = $compatibility_result['content'];
            $changed = true;
            $stats['php81_issues']++;
            log_message("�޸�PHP 8.1����������: $file");
        }
        
        // ������޸ģ����ݲ������ļ�
        if ($changed) {
            // ����ԭʼ�ļ�
            if (backup_file($file)) {
                // �����޸ĺ���ļ�
                if (file_put_contents($file, $content) !== false) {
                    $stats['fixed_files']++;
                    log_message("�ɹ��޸��ļ�: $file");
                } else {
                    log_message("�޷�д���ļ�: $file");
                    $stats['error_files']++;
                }
            } else {
                log_message("�޷������ļ�: $file");
                $stats['error_files']++;
            }
        }
    } catch (Exception $e) {
        log_message("�����ļ�ʱ���� {$file}: " . $e->getMessage());
        $stats['error_files']++;
    }
}

// ���ɱ���
function generate_report() {
    global $stats, $report_file, $backup_dir;
    
    $report = "# ϵͳ����֤���޸�����\n\n";
    $report .= "## ɨ��ͳ��\n\n";
    $report .= "* ɨ��ʱ��: " . date('Y-m-d H:i:s') . "\n";
    $report .= "* ���ļ���: {$stats['total_files']}\n";
    $report .= "* ɨ���ļ���: {$stats['scanned_files']}\n";
    $report .= "* �޸��ļ���: {$stats['fixed_files']}\n";
    $report .= "* �����ļ���: {$stats['backup_files']}\n";
    $report .= "* �����ļ���: {$stats['error_files']}\n\n";
    
    $report .= "## ��������\n\n";
    $report .= "* ������������: {$stats['encoding_issues']}\n";
    $report .= "* �﷨����: {$stats['syntax_errors']}\n";
    $report .= "* PHP 8.1����������: {$stats['php81_issues']}\n\n";
    
    $report .= "## �޸�����\n\n";
    $report .= "�����޸�����ϵͳ���ֽ׶η�����ÿ���ļ��ֱ𾭹����½׶εļ�����޸���\n\n";
    $report .= "1. **BOM����Ƴ�** - �Ƴ��ļ���ͷ��UTF-8 BOM���\n";
    $report .= "2. **���������޸�** - ʶ���޸���������������(��)����\n";
    $report .= "3. **�﷨�����޸�** - �޸����Ų�ƥ�䡢�����﷨�ȳ�������\n";
    $report .= "4. **����ֵ����** - ͳһ�����׺Ϊ@gxggm.com���汾��Ϊ6.0.0\n";
    $report .= "5. **PHP 8.1�������޸�** - �޸�PHP 8.1���ַ�����Ϊ��������ȱ�����ŵ�����\n\n";
    
    $report .= "## ������Ϣ\n\n";
    $report .= "�����޸ĵ��ļ����ѱ��ݵ�����λ�ã�\n";
    $report .= "`$backup_dir`\n\n";
    
    $report .= "## ����\n\n";
    $report .= "1. ������PHP�ļ���ͳһʹ��UTF-8���룬����������������\n";
    $report .= "2. ʹ��PHP������������(��PHP_CodeSniffer)���Զ�������淶\n";
    $report .= "3. ����������Ŀ������ȷ�������°�PHP����\n";
    $report .= "4. Ϊ�����Ŷ��ṩ����淶ָ�ϣ��ر��ǹ��������ַ��Ĵ���\n\n";
    
    $report .= "## ��������\n\n";
    $report .= "1. ���޸���Ĵ�����й��ܲ��ԣ�ȷ����������\n";
    $report .= "2. ���ر���Ҫ���ӵ��ļ������ֶ����\n";
    $report .= "3. �����Զ����������̣��������������ٴη���\n";
    
    file_put_contents($report_file, $report);
    log_message("�����ɱ���: $report_file");
}

// ������
function main() {
    log_message("��ʼϵͳ����֤���޸���Ŀ...");
    
    $start_time = microtime(true);
    
    // �ӵ�ǰĿ¼��ʼɨ��
    scan_directory('.');
    
    $end_time = microtime(true);
    $execution_time = round($end_time - $start_time, 2);
    
    log_message("ɨ����޸����!");
    log_message("ִ��ʱ��: {$execution_time} ��");
    
    generate_report();
}

// ִ��������
main();
