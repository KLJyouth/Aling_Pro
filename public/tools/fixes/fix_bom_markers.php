<?php
/**
 * �Ƴ�PHP�ļ��е�UTF-8 BOM���
 * 
 * UTF-8 BOM�����һ���ֽ����У�0xEF, 0xBB, 0xBF
 * ��Щ��ǿ��ܵ���PHP���������ر������ļ���ͷ��<?php���֮ǰ
 */

// Ҫ�����Ŀ¼
$directories = [
    "apps/ai-platform/Services/NLP",
    "ai-engines/cv",
    "src/Utils",
    "src/Performance"
];

$totalFixed = 0;
$totalFiles = 0;

foreach ($directories as $directory) {
    if (!is_dir($directory)) {
        echo "Ŀ¼������: $directory\n";
        continue;
    }

    echo "����Ŀ¼: $directory\n";
    
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory],
        RecursiveIteratorIterator::LEAVES_ONLY
    ];

    foreach ($files as $file) {
        if (!$file->isFile() || $file->getExtension() !== "php") {
            continue;
        }

        $totalFiles++;
        $filePath = $file->getRealPath(];
        $content = file_get_contents($filePath];
        
        // ����Ƿ���BOM���
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            // �Ƴ�BOM���
            $content = substr($content, 3];
            
            // ���PHP��ʼ����Ƿ���ȷ
            if (substr($content, 0, 5) !== "<?php") {
                // ���������<?php��ͷ�������ȷ��PHP��ʼ���
                if (substr($content, 0, 2) === "<?") {
                    // �滻�̱��
                    $content = "<?php" . substr($content, 2];
                } else {
                    // ����������
                    $content = "<?php\n\n" . $content;
                }
            }
            
            // �����޸�����ļ�
            file_put_contents($filePath, $content];
            
            echo "���޸�: $filePath\n";
            $totalFixed++;
        }
    }
}

echo "\n�ܽ�:\n";
echo "�����ļ�����: $totalFiles\n";
echo "�޸����ļ�����: $totalFixed\n";
