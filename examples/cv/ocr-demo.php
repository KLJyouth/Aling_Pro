<?php
/**
 * 文件名：ocr-demo.php
 * 功能描述：OCR文字识别模型使用示例
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 * 
 * @package AlingAi\Examples\CV
 * @author AlingAi Team
 * @license MIT
 */

// 自动加载
require_once __DIR__ . '/../../vendor/autoload.php';

use AlingAi\Engines\CV\OCRModel;
use AlingAi\Core\Logger\ConsoleLogger;

// 设置示例图像路径
$imagePath = __DIR__ . '/../data/sample_text.jpg';
$handwritingPath = __DIR__ . '/../data/sample_handwriting.jpg';
$documentPath = __DIR__ . '/../data/sample_document.jpg';
$tablePath = __DIR__ . '/../data/sample_table.jpg';
$formulaPath = __DIR__ . '/../data/sample_formula.jpg';

// 创建日志记录器
$logger = new ConsoleLogger();

echo "=================================================\n";
echo "AlingAi OCR模型演示\n";
echo "=================================================\n\n";

// -------------------------------------------------
// 示例1：基本OCR识别
// -------------------------------------------------
echo "示例1：基本OCR识别\n";
echo "-------------------------------------------------\n";

// 创建OCR模型实例
$ocr = new OCRModel([
    'engine' => 'general',
    'language' => 'auto',
    'confidence_threshold' => 0.6
], $logger);

// 执行OCR识别
try {
    $result = $ocr->recognize($imagePath);
    
    echo "识别文本：\n";
    echo $result['text'] . "\n\n";
    
    echo "检测到的语言：" . $result['detected_language'] . "\n";
    echo "文本块数量：" . $result['count'] . "\n";
    echo "处理时间：" . $result['processing_time'] . "毫秒\n\n";
    
    // 显示第一个文本块详情
    if (!empty($result['text_blocks'])) {
        $block = $result['text_blocks'][0];
        echo "第一个文本块：\n";
        echo "文本：" . $block['text'] . "\n";
        echo "置信度：" . $block['confidence'] . "\n";
        if (isset($block['bbox'])) {
            echo "位置：[" . $block['bbox']['x1'] . ", " . $block['bbox']['y1'] . ", " . 
                $block['bbox']['x2'] . ", " . $block['bbox']['y2'] . "]\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n\n";
}

// -------------------------------------------------
// 示例2：密集文本OCR识别
// -------------------------------------------------
echo "示例2：密集文本OCR识别\n";
echo "-------------------------------------------------\n";

// 更新配置为密集文本引擎
$ocr->updateConfig([
    'engine' => 'dense',
    'language' => 'zh-cn' // 指定中文
]);

try {
    $result = $ocr->recognize($imagePath);
    
    echo "识别文本：\n";
    echo $result['text'] . "\n\n";
    
    echo "文本块数量：" . $result['count'] . "\n";
    echo "处理时间：" . $result['processing_time'] . "毫秒\n\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n\n";
}

// -------------------------------------------------
// 示例3：手写文本识别
// -------------------------------------------------
echo "示例3：手写文本识别\n";
echo "-------------------------------------------------\n";

// 更新配置为手写识别引擎
$ocr->updateConfig([
    'engine' => 'handwriting',
    'language' => 'en' // 指定英语
]);

try {
    $result = $ocr->recognize($handwritingPath);
    
    echo "识别文本：\n";
    echo $result['text'] . "\n\n";
    
    echo "文本块数量：" . $result['count'] . "\n";
    echo "处理时间：" . $result['processing_time'] . "毫秒\n\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n\n";
}

// -------------------------------------------------
// 示例4：文档OCR识别（带布局分析）
// -------------------------------------------------
echo "示例4：文档OCR识别（带布局分析）\n";
echo "-------------------------------------------------\n";

// 更新配置为文档识别引擎
$ocr->updateConfig([
    'engine' => 'document',
    'language' => 'auto',
    'enable_layout_analysis' => true,
    'enable_correction' => true // 启用文本校正
]);

try {
    $result = $ocr->recognize($documentPath);
    
    echo "识别文本：\n";
    echo $result['text'] . "\n\n";
    
    // 显示布局分析结果
    if (isset($result['layout'])) {
        echo "布局分析结果：\n";
        echo "段落数量：" . count($result['layout']['paragraphs']) . "\n";
        echo "列数：" . count($result['layout']['columns']) . "\n";
        
        if (!empty($result['layout']['headers'])) {
            echo "标题：" . $result['layout']['headers'][0]['text'] . "\n";
        }
        
        if (!empty($result['layout']['footers'])) {
            echo "页脚：" . $result['layout']['footers'][0]['text'] . "\n";
        }
    }
    
    echo "\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n\n";
}

// -------------------------------------------------
// 示例5：表格识别
// -------------------------------------------------
echo "示例5：表格识别\n";
echo "-------------------------------------------------\n";

// 更新配置为表格识别引擎
$ocr->updateConfig([
    'engine' => 'table',
    'language' => 'auto',
    'enable_table_recognition' => true
]);

try {
    $result = $ocr->recognize($tablePath);
    
    // 显示表格识别结果
    if (isset($result['tables']) && !empty($result['tables'])) {
        $table = $result['tables'][0];
        
        echo "表格结构：" . $table['rows'] . " 行 x " . $table['cols'] . " 列\n\n";
        
        echo "表格内容：\n";
        for ($r = 0; $r < $table['rows']; $r++) {
            $rowContent = [];
            for ($c = 0; $c < $table['cols']; $c++) {
                $rowContent[] = $table['cells'][$r][$c]['text'];
            }
            echo implode("\t| ", $rowContent) . "\n";
        }
        
        echo "\nHTML表格：\n";
        echo $table['html'] . "\n\n";
    } else {
        echo "未检测到表格\n\n";
    }
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n\n";
}

// -------------------------------------------------
// 示例6：公式识别
// -------------------------------------------------
echo "示例6：公式识别\n";
echo "-------------------------------------------------\n";

// 更新配置为公式识别引擎
$ocr->updateConfig([
    'engine' => 'formula',
    'enable_formula_recognition' => true
]);

try {
    $result = $ocr->recognize($formulaPath);
    
    echo "识别结果：\n";
    
    // 显示公式识别结果
    if (!empty($result['text_blocks'])) {
        foreach ($result['text_blocks'] as $i => $block) {
            if ($block['type'] === 'formula') {
                echo "公式 " . ($i + 1) . ": " . $block['text'] . "\n";
                if (isset($block['latex'])) {
                    echo "LaTeX: " . $block['latex'] . "\n";
                }
                echo "\n";
            }
        }
    } else {
        echo "未检测到公式\n";
    }
    
    echo "\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n\n";
}

// -------------------------------------------------
// 示例7：批量处理
// -------------------------------------------------
echo "示例7：批量处理\n";
echo "-------------------------------------------------\n";

// 启用批量处理
$ocr->updateConfig([
    'engine' => 'general',
    'language' => 'auto',
    'batch_processing' => true,
    'batch_size' => 2
]);

$images = [
    $imagePath,
    $documentPath,
    $handwritingPath
];

try {
    $batchResults = $ocr->recognizeBatch($images);
    
    echo "批量处理结果：\n";
    echo "总图像数：" . $batchResults['total_images'] . "\n";
    echo "总处理时间：" . $batchResults['total_time'] . "毫秒\n";
    echo "每张图像平均时间：" . $batchResults['average_time_per_image'] . "毫秒\n";
    echo "批次大小：" . $batchResults['batch_size'] . "\n";
    echo "批次数量：" . $batchResults['num_batches'] . "\n\n";
    
    // 显示第一个结果
    if (!empty($batchResults['results'][0])) {
        $firstResult = $batchResults['results'][0];
        echo "第一个图像识别文本：\n";
        echo $firstResult['text'] . "\n\n";
    }
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n\n";
}

// -------------------------------------------------
// 示例8：获取支持的引擎和语言
// -------------------------------------------------
echo "示例8：获取支持的引擎和语言\n";
echo "-------------------------------------------------\n";

echo "支持的OCR引擎：\n";
foreach ($ocr->getSupportedEngines() as $engine) {
    echo "- " . $engine . "\n";
}
echo "\n";

echo "支持的语言：\n";
foreach ($ocr->getSupportedLanguages() as $code => $name) {
    echo "- " . $code . ": " . $name . "\n";
}
echo "\n";

// 清理资源
$ocr->cleanup();

echo "=================================================\n";
echo "OCR演示完成\n";
echo "=================================================\n"; 