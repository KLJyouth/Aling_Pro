<?php
/**
 * AlingAi Pro ��װ�� - ϵͳ���ű�
 * ���ϵͳ�����Ƿ����㰲װҪ��
 */

header('Content-Type: application/json'];

// ������������
$result = [
    'success' => true,
    'required' => [],
    'recommended' => []
];

// ���PHP�汾
$phpVersion = phpversion(];
$requiredPhpVersion = '7.4.0';
$result['required'][] = [
    'name' => 'PHP�汾',
    'passed' => version_compare($phpVersion, $requiredPhpVersion, '>='],
    'message' => '��ǰ�汾: ' . $phpVersion . ' (��Ҫ ' . $requiredPhpVersion . ' �����]'
];

// ���PDO��չ
$pdoInstalled = extension_loaded('pdo'];
$result['required'][] = [
    'name' => 'PDO��չ',
    'passed' => $pdoInstalled,
    'message' => $pdoInstalled ? '�Ѱ�װ' : 'δ��װ'
];

// ���PDO MySQL��չ
$pdoMysqlInstalled = extension_loaded('pdo_mysql'];
$result['required'][] = [
    'name' => 'PDO MySQL��չ',
    'passed' => $pdoMysqlInstalled,
    'message' => $pdoMysqlInstalled ? '�Ѱ�װ' : 'δ��װ'
];

// ���JSON��չ
$jsonInstalled = extension_loaded('json'];
$result['required'][] = [
    'name' => 'JSON��չ',
    'passed' => $jsonInstalled,
    'message' => $jsonInstalled ? '�Ѱ�װ' : 'δ��װ'
];

// ���cURL��չ
$curlInstalled = extension_loaded('curl'];
$result['required'][] = [
    'name' => 'cURL��չ',
    'passed' => $curlInstalled,
    'message' => $curlInstalled ? '�Ѱ�װ' : 'δ��װ'
];

// ���Ŀ¼Ȩ��
$baseDir = dirname(dirname(__DIR__]];
$dirsToCheck = [
    $baseDir . '/config' => '����Ŀ¼',
    $baseDir . '/storage' => '�洢Ŀ¼',
    $baseDir . '/public/uploads' => '�ϴ�Ŀ¼'
];

foreach ($dirsToCheck as $dir => $name] {
    $exists = file_exists($dir];
    $writable = $exists && is_writable($dir];
    
    if (!$exists] {
        // ���Դ���Ŀ¼
        $created = @mkdir($dir, 0755, true];
        $writable = $created && is_writable($dir];
    }
    
    $result['required'][] = [
        'name' => $name . ' Ȩ��',
        'passed' => $writable,
        'message' => $writable ? '��д' : ($exists ? '���ڵ�����д' : '���������޷�����']
    ];
}

// ���OPcache��չ
$opcacheInstalled = extension_loaded('opcache'];
$result['recommended'][] = [
    'name' => 'OPcache��չ',
    'passed' => $opcacheInstalled,
    'message' => $opcacheInstalled ? '�Ѱ�װ' : 'δ��װ (���鰲װ���������]'
];

// ���Mbstring��չ
$mbstringInstalled = extension_loaded('mbstring'];
$result['recommended'][] = [
    'name' => 'Mbstring��չ',
    'passed' => $mbstringInstalled,
    'message' => $mbstringInstalled ? '�Ѱ�װ' : 'δ��װ (���鰲װ��֧�ֶ��ֽ��ַ�]'
];

// ���GD��չ
$gdInstalled = extension_loaded('gd'];
$result['recommended'][] = [
    'name' => 'GD��չ',
    'passed' => $gdInstalled,
    'message' => $gdInstalled ? '�Ѱ�װ' : 'δ��װ (���鰲װ��֧��ͼ����]'
];

// ����ڴ�����
$memoryLimit = ini_get('memory_limit'];
$memoryLimitBytes = return_bytes($memoryLimit];
$recommendedMemory = 128 * 1024 * 1024;// 128MB
$result['recommended'][] = [
    'name' => '�ڴ�����',
    'passed' => $memoryLimitBytes >= $recommendedMemory,
    'message' => '��ǰ����: ' . $memoryLimit . ' (�������� 128M]'
];

// ����Ƿ����б���������ͨ��
foreach ($result['required'] as $check] {
    if (!$check['passed']] {
        $result['success'] = false;
        break;
    }
}

// �������JSON��ʽ����
echo json_encode($result, JSON_PRETTY_PRINT];

/**
 * ���ڴ������ַ���ת��Ϊ�ֽ���
 */
function return_bytes($val] {
    $val = trim($val];
    $last = strtolower($val[strlen($val]-1]];
    $val = (int] $val;
    
    switch($last] {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    
    return $val;
}

