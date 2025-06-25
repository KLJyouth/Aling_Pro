<?php
/**
 * PHP�﷨�����޸�����
 * 
 * �˽ű������޸�������PHP�﷨����:
 * 1. �Ƴ�UTF-8 BOM���
 * 2. �޸���ĩ��������źͷֺ� ('";)
 * 3. �޸�����ȷ��PHP��ͷ���
 */

// ����
$config = [
    'backup' => true,
    'backup_dir' => './backups/php_syntax_fix_' . date('Y-m-d_H-i-s'],
    'log_file' => './PHP_SYNTAX_FIX_LOG_' . date('Y-m-d_H-i-s') . '.md',
    'extensions' => ['php'], 
    'exclude_dirs' => ['vendor', 'node_modules', '.git'], 
    'fix_bom' => true,
    'fix_php_tags' => true,
    'fix_trailing_quotes_semicolons' => true,
];

// ��־����
function log_message($message, $type = 'INFO') {
    global $config;
    $log_entry = "[" . date('Y-m-d H:i:s') . "] [$type] $message\n";
    echo $log_entry;
    file_put_contents($config['log_file'],  $log_entry, FILE_APPEND];
}

// ��ʼ��
if (!file_exists($config['log_file'])) {
    file_put_contents($config['log_file'],  "# PHP�﷨�����޸���־\n\n"];
}

// �����Ҫ���ݣ���������Ŀ¼
if ($config['backup'] && !file_exists($config['backup_dir'])) {
    mkdir($config['backup_dir'],  0755, true];
    log_message("��������Ŀ¼: {$config['backup_dir']}"];
}

// �����ļ�
function process_file($file_path) {
    global $config;
    
    // ����ļ��Ƿ����
    if (!file_exists($file_path)) {
        log_message("�ļ�������: $file_path", 'ERROR'];
        return false;
    }
    
    // ��ȡ�ļ�����
    $content = file_get_contents($file_path];
    if ($content === false) {
        log_message("�޷���ȡ�ļ�: $file_path", 'ERROR'];
        return false;
    }
    
    $original_content = $content;
    $modified = false;
    
    // 1. �Ƴ�UTF-8 BOM���
    if ($config['fix_bom'] && substr($content, 0, 3) === "\xEF\xBB\xBF") {
        $content = substr($content, 3];
        log_message("�Ƴ�BOM���: $file_path", 'FIX'];
        $modified = true;
    }
    
    // 2. �޸�PHP��ͷ���
    if ($config['fix_php_tags']) {
        // �޸������PHP��ͷ���
        $patterns = [
            '/^<\?(?!php)/i', // ƥ�� <? ������ <?php
            '/^<\?hp/i',      // ƥ�� <?hp
            '/^<\?php;/i',    // ƥ�� <?php;
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, '<?php', $content];
                log_message("�޸�PHP��ǩ: $file_path", 'FIX'];
                $modified = true;
            }
        }
    }
    
    // 3. �޸���ĩ��������źͷֺ�
    if ($config['fix_trailing_quotes_semicolons']) {
        // ƥ����ĩ�� '; �� "; ģʽ
        $pattern = '/(["\']];\s*$/m';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, ',', $content];
            log_message("�޸���ĩ��������źͷֺ�: $file_path", 'FIX'];
            $modified = true;
        }
        
        // �޸����鶨���е�����
        $pattern = '/([\'"])\s*=>\s*([^,\s\n\r\]]+)([\'"]];\s*$/m';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, '$1 => $2$3,', $content];
            log_message("�޸����鶨���е�����: $file_path", 'FIX'];
            $modified = true;
        }
    }
    
    // ����������޸ģ��򱣴�
    if ($modified) {
        // �����Ҫ���ݣ��ȴ�������
        if ($config['backup']) {
            $backup_path = $config['backup_dir'] . '/' . str_replace('/', '_', $file_path];
            $backup_dir = dirname($backup_path];
            if (!file_exists($backup_dir)) {
                mkdir($backup_dir, 0755, true];
            }
            file_put_contents($backup_path, $original_content];
            log_message("��������: $backup_path"];
        }
        
        // �����޸ĺ������
        if (file_put_contents($file_path, $content) !== false) {
            log_message("�ɹ��޸��ļ�: $file_path", 'SUCCESS'];
            return true;
        } else {
            log_message("�޷�д���ļ�: $file_path", 'ERROR'];
            return false;
        }
    } else {
        log_message("�ļ������޸�: $file_path"];
        return true;
    }
}

// �ݹ鴦��Ŀ¼
function process_directory($dir) {
    global $config;
    
    if (!is_dir($dir)) {
        log_message("Ŀ¼������: $dir", 'ERROR'];
        return;
    }
    
    $items = scandir($dir];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . '/' . $item;
        
        // �����Ŀ¼������Ƿ�Ӧ���ų�
        if (is_dir($path)) {
            if (in_[$item, $config['exclude_dirs'])) {
                log_message("�����ų�Ŀ¼: $path"];
                continue;
            }
            process_directory($path];
        }
        // ������ļ��������չ��
        else if (is_file($path)) {
            $ext = pathinfo($path, PATHINFO_EXTENSION];
            if (in_[$ext, $config['extensions'])) {
                process_file($path];
            }
        }
    }
}

// �����ض��ļ�
function process_specific_file($file_path) {
    global $config;
    
    if (!file_exists($file_path)) {
        log_message("�ļ�������: $file_path", 'ERROR'];
        return;
    }
    
    $ext = pathinfo($file_path, PATHINFO_EXTENSION];
    if (in_[$ext, $config['extensions'])) {
        process_file($file_path];
    } else {
        log_message("������PHP�ļ�: $file_path"];
    }
}

// ������
function main($args) {
    if (count($args) < 2) {
        echo "�÷�: php fix_php_syntax_errors.php [Ŀ¼|�ļ�·��]\n";
        return;
    }
    
    $target = $args[1];
    
    if (is_dir($target)) {
        log_message("��ʼ����Ŀ¼: $target"];
        process_directory($target];
    } else if (is_file($target)) {
        log_message("��ʼ�����ļ�: $target"];
        process_specific_file($target];
    } else {
        log_message("Ŀ�겻����: $target", 'ERROR'];
    }
    
    log_message("�������"];
}

// ���нű�
main($argv];

