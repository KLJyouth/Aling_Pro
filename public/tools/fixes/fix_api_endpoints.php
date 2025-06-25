<?php
/**
 * API�˵��޸��ű�
 * 
 * �˽ű������޸�AlingAi Proϵͳ�е�API�˵�����
 */

// ����Ӧ�ó����Ŀ¼
define("APP_ROOT", __DIR__];

// �������
echo "=================================================\n";
echo "AlingAi Pro API�˵��޸�����\n";
echo "=================================================\n\n";

// 1. ��������API�˵�
echo "1. ��������API�˵�...\n";

// ��publicĿ¼�´�������API�˵�
$testApiDir = APP_ROOT . "/public/api";
if (!is_dir($testApiDir)) {
    mkdir($testApiDir, 0755, true];
    echo "   - ��������APIĿ¼: {$testApiDir}\n";
}

// ����v1/system/info.php
$systemInfoDir = $testApiDir . "/v1/system";
if (!is_dir($systemInfoDir)) {
    mkdir($systemInfoDir, 0755, true];
}

$systemInfoFile = $systemInfoDir . "/info.php";
$systemInfoContent = "<?php
header(\"Content-Type: application/json\"];

echo json_encode([
    \"version\" => \"1.0\",
    \"system\" => \"AlingAi Pro\",
    \"api_version\" => \"v1\",
    \"timestamp\" => date(\"Y-m-d H:i:s\"],
    \"features\" => [
        \"security_scanning\" => true,
        \"threat_visualization\" => true,
        \"database_management\" => true,
        \"cache_optimization\" => true
    ]
]];";

file_put_contents($systemInfoFile, $systemInfoContent];
echo "   - ��������API�˵�: /api/v1/system/info\n";

// ����info.php
$infoFile = $testApiDir . "/info.php";
$infoContent = "<?php
header(\"Content-Type: application/json\"];

echo json_encode([
    \"message\" => \"AlingAi Pro API\",
    \"current_version\" => \"v2\",
    \"available_versions\" => [\"v1\", \"v2\"], 
    \"endpoints\" => [
        \"/api/v1/system/info\" => \"System information (v1)\",
        \"/api/v2/enhanced/dashboard\" => \"Enhanced dashboard (v2)\",
        \"/api/v1/security/overview\" => \"Security overview\",
        \"/api/v2/ai/agents\" => \"AI agents management\"
    ]
]];";

file_put_contents($infoFile, $infoContent];
echo "   - ��������API�˵�: /api/info\n";

// 2. ����.htaccess�ļ��Դ���API����
echo "\n2. ����.htaccess�ļ��Դ���API����...\n";

$htaccessFile = APP_ROOT . "/public/.htaccess";
$htaccessContent = "<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # ����������ʵ���ļ���Ŀ¼����ֱ�ӷ���
    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]

    # ����API����
    RewriteRule ^api/v1/system/info/?$ api/v1/system/info.php [L]
    RewriteRule ^api/info/?$ api/info.php [L]

    # ��������ת����index.php
    RewriteRule ^ index.php [L]
</IfModule>";

file_put_contents($htaccessFile, $htaccessContent];
echo "   - ����.htaccess�ļ�\n";

echo "\n=================================================\n";
echo "�޸����!\n";
echo "������������������������������API�˵�:\n";
echo "php -c php.ini.local -S localhost:8000 -t public\n";
echo "=================================================\n";
