<?php
/**
 * AlingAi Pro - PHP���÷�����·�ɽű�
 * 
 * ���ļ�����PHP���÷�������·�ɴ���
 * ��̬�ļ�ֱ���ṩ���񣬶�̬����ת����index.php
 */

// ��ȡ����URI
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'],  PHP_URL_PATH)];

// ���������ļ��Ƿ����
$requested_file = __DIR__ . '/public' . $uri;

// �����������ļ��Ҹ��ļ����ڣ�ֱ���ṩ����
if ($uri !== '/' && file_exists($requested_file) && !is_dir($requested_file)) {
    // ��ȡ�ļ���չ��
    $extension = pathinfo($requested_file, PATHINFO_EXTENSION];
    
    // �����ʵ���Content-Type
    switch ($extension) {
        case 'css':
            header('Content-Type: text/css'];
            break;
        case 'js':
            header('Content-Type: application/javascript'];
            break;
        case 'json':
            header('Content-Type: application/json'];
            break;
        case 'png':
            header('Content-Type: image/png'];
            break;
        case 'jpg':
        case 'jpeg':
            header('Content-Type: image/jpeg'];
            break;
        case 'svg':
            header('Content-Type: image/svg+xml'];
            break;
    }
    
    // ����ļ�����
    readfile($requested_file];
    return true;
}

// ���򣬽�����ת����index.php
require_once __DIR__ . '/public/index.php';
