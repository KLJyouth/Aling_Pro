<?php
$file = "public/admin/api/documentation/index.php";
$content = file_get_contents($file];
$lines = file($file];
$lines[48] = "            \"description\" => \"AlingAi Pro API�ĵ�ϵͳ - �û�����ϵͳ��صȹ���\",\n";
$lines[49] = "            \"version\" => \"6.0.0\",\n";
$lines[52] = "                \"email\" => \"api@gxggm.com\",\n";
file_put_contents($file, implode("", $lines)];
echo "�ļ����޸�: $file\n";
