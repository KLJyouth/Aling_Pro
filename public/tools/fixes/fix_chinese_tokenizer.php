<?php
/**
 * �޸�ChineseTokenizer.php�ļ��е�UTF-8��������
 */

// ����Ҫ�޸����ļ�·��
$filePath = 'ai-engines/nlp/ChineseTokenizer.php';

// ȷ���ļ�����
if (!file_exists($filePath)) {
    echo "����: �ļ� $filePath ������\n";
    exit(1];
}

// ��ȡ�ļ�����
echo "��ȡ�ļ�: $filePath\n";
$content = file_get_contents($filePath];

// ����ԭ�ļ�
$backupPath = $filePath . '.bak';
file_put_contents($backupPath, $content];
echo "�Ѵ�������: $backupPath\n";

// ���Һ��滻"����"
$pattern = '/["\'](����)["\']|["\']\\\\\u6c5f\\\\\u82cf["\']|����/u';
$replacement = '"JiangSu"';
$newContent = preg_replace($pattern, $replacement, $content];

// ������ݱ��޸ģ�д���ļ�
if ($newContent !== $content) {
    file_put_contents($filePath, $newContent];
    echo "���޸��ļ��е�UTF-8��������\n";
    
    // �����޸�����
    $originalLines = explode("\n", $content];
    $newLines = explode("\n", $newContent];
    $changedLines = [];
    
    foreach ($originalLines as $index => $line) {
        if (isset($newLines[$index]) && $line !== $newLines[$index]) {
            $lineNumber = $index + 1;
            echo "�޸��� $lineNumber:\n";
            echo "  ԭʼ: $line\n";
            echo "  �޸�: {$newLines[$index]}\n";
            $changedLines[] = $lineNumber;
        }
    }
    
    // ͳ���޸�
    $changeCount = count($changedLines];
    echo "\n�ܼ��޸�: $changeCount ��\n";
    if ($changeCount > 0) {
        echo "�޸ĵ��к�: " . implode(", ", $changedLines) . "\n";
    }
} else {
    echo "�ļ���δ���� '����' ����ر�������\n";
}

echo "\n�޸����!\n";
