<?php
/**
 * �޸�API�ĵ��е���������
 * ��
 */

// ����Ҫ������ļ�
$file = " public/admin/api/documentation/index.php\;

// ��ȡ�ļ�����
$content = file_get_contents($file);
if ($content === false) {
 die(\�޷���ȡ�ļ�: $file\n\);
}

// ��������
$backup = $file . \.bak\;
if (!file_exists($backup)) {
 file_put_contents($backup, $content);
 echo \�Ѵ�������: $backup\n\;
}

// �޸�
$content = str_replace(\[\\\\\\]\, \[\\\ref\\\]\, $content);

// �����޸�����ļ�
if (file_put_contents($file, $content)) {
 echo \���޸��ļ�: $file\n\;
} else {
 echo \�޷�д���ļ�: $file\n\;
}

echo \�޸����\n\;
