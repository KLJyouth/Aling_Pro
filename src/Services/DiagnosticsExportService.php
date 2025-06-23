<?php

declare(strict_types=1);

namespace AlingAi\Services;

/**
 * 诊断报告导出服务
 * 
 * 负责将诊断数据导出为多种格式
 * 
 * @package AlingAi\Services
 * @version 1.0.0
 */
class DiagnosticsExportService
{
    private string $tempDir;
    
    public function __construct()
    {
        $this->tempDir = sys_get_temp_dir();
    }

    /**
     * 导出诊断报告为JSON格式
     */
    public function exportToJson(array $diagnosticData): array
    {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "diagnostic_report_{$timestamp}.json";
            $filepath = $this->tempDir . DIRECTORY_SEPARATOR . $filename;
            
            $exportData = $this->prepareExportData($diagnosticData);
            $jsonContent = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
            file_put_contents($filepath, $jsonContent);
            
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => filesize($filepath),
                'format' => 'json'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 导出诊断报告为CSV格式
     */
    public function exportToCsv(array $diagnosticData): array
    {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "diagnostic_report_{$timestamp}.csv";
            $filepath = $this->tempDir . DIRECTORY_SEPARATOR . $filename;
            
            $handle = fopen($filepath, 'w');
            
            // 写入BOM以支持中文
            fwrite($handle, "\xEF\xBB\xBF");
            
            // 写入标题行
            fputcsv($handle, [
                '类别', '测试项目', '状态', '消息', '详细信息', '时间戳'
            ]);
            
            // 写入数据行
            foreach ($diagnosticData['categories'] as $categoryName => $category) {
                foreach ($category['tests'] as $test) {
                    fputcsv($handle, [
                        $category['name'],
                        $test['name'],
                        $this->translateStatus($test['status']),
                        $test['message'] ?? '',
                        $test['details'] ?? '',
                        $diagnosticData['timestamp']
                    ]);
                }
            }
            
            fclose($handle);
            
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => filesize($filepath),
                'format' => 'csv'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 导出诊断报告为HTML格式
     */
    public function exportToHtml(array $diagnosticData): array
    {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "diagnostic_report_{$timestamp}.html";
            $filepath = $this->tempDir . DIRECTORY_SEPARATOR . $filename;
            
            $htmlContent = $this->generateHtmlReport($diagnosticData);
            
            file_put_contents($filepath, $htmlContent);
            
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => filesize($filepath),
                'format' => 'html'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 导出诊断报告为文本格式
     */
    public function exportToText(array $diagnosticData): array
    {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "diagnostic_report_{$timestamp}.txt";
            $filepath = $this->tempDir . DIRECTORY_SEPARATOR . $filename;
            
            $textContent = $this->generateTextReport($diagnosticData);
            
            file_put_contents($filepath, $textContent);
            
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => filesize($filepath),
                'format' => 'txt'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 批量导出多种格式
     */
    public function exportMultipleFormats(array $diagnosticData, array $formats = ['json', 'csv', 'html', 'txt']): array
    {
        $results = [];
        
        foreach ($formats as $format) {
            switch ($format) {
                case 'json':
                    $results['json'] = $this->exportToJson($diagnosticData);
                    break;
                case 'csv':
                    $results['csv'] = $this->exportToCsv($diagnosticData);
                    break;
                case 'html':
                    $results['html'] = $this->exportToHtml($diagnosticData);
                    break;
                case 'txt':
                    $results['txt'] = $this->exportToText($diagnosticData);
                    break;
            }
        }
        
        return $results;
    }

    /**
     * 准备导出数据
     */
    private function prepareExportData(array $diagnosticData): array
    {
        return [
            'export_info' => [
                'generated_at' => date('Y-m-d H:i:s'),
                'generator' => 'AlingAi Pro Diagnostics System',
                'version' => '1.0.0'
            ],
            'system_info' => [
                'php_version' => PHP_VERSION,
                'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'timestamp' => $diagnosticData['timestamp']
            ],
            'diagnostic_data' => $diagnosticData
        ];
    }

    /**
     * 生成HTML报告
     */
    private function generateHtmlReport(array $diagnosticData): string
    {
        $timestamp = $diagnosticData['timestamp'];
        $overallStatus = $diagnosticData['overall_status'];
        
        $statusColor = [
            'healthy' => '#10b981',
            'warning' => '#f59e0b',
            'error' => '#ef4444'
        ][$overallStatus] ?? '#6b7280';
        
        $html = "<!DOCTYPE html>
<html lang='zh-CN'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>AlingAi Pro 系统诊断报告</title>
    <style>
        body { font-family: 'Microsoft YaHei', Arial, sans-serif; margin: 20px; background: #f8fafc; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .status-badge { display: inline-block; padding: 8px 16px; border-radius: 20px; color: white; font-weight: bold; }
        .category { margin-bottom: 25px; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; }
        .category-header { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #374151; }
        .test-item { margin: 10px 0; padding: 12px; border-radius: 6px; background: #f9fafb; }
        .test-header { font-weight: bold; margin-bottom: 5px; }
        .test-details { font-size: 14px; color: #6b7280; }
        .pass { border-left: 4px solid #10b981; }
        .fail { border-left: 4px solid #ef4444; }
        .warn { border-left: 4px solid #f59e0b; }
        .footer { text-align: center; margin-top: 30px; color: #6b7280; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>AlingAi Pro 系统诊断报告</h1>
            <p>生成时间: {$timestamp}</p>
            <span class='status-badge' style='background-color: {$statusColor}'>
                系统状态: " . $this->translateStatus($overallStatus) . "
            </span>
        </div>";

        foreach ($diagnosticData['categories'] as $categoryName => $category) {
            $html .= "<div class='category'>
                <div class='category-header'>{$category['name']} ({$category['passed']}/{$category['total']})</div>";
            
            foreach ($category['tests'] as $test) {
                $statusClass = $test['status'] === 'pass' ? 'pass' : 'fail';
                $html .= "<div class='test-item {$statusClass}'>
                    <div class='test-header'>{$test['name']}</div>
                    <div class='test-details'>
                        状态: " . $this->translateStatus($test['status']) . "<br>
                        " . ($test['message'] ?? '') . "<br>
                        " . ($test['details'] ?? '') . "
                    </div>
                </div>";
            }
            
            $html .= "</div>";
        }

        $html .= "<div class='footer'>
            <p>报告由 AlingAi Pro 诊断系统生成 | " . date('Y-m-d H:i:s') . "</p>
        </div>
    </div>
</body>
</html>";

        return $html;
    }

    /**
     * 生成文本报告
     */
    private function generateTextReport(array $diagnosticData): string
    {
        $text = "=====================================\n";
        $text .= "     AlingAi Pro 系统诊断报告\n";
        $text .= "=====================================\n\n";
        $text .= "生成时间: {$diagnosticData['timestamp']}\n";
        $text .= "系统状态: " . $this->translateStatus($diagnosticData['overall_status']) . "\n\n";

        foreach ($diagnosticData['categories'] as $categoryName => $category) {
            $text .= "【{$category['name']}】({$category['passed']}/{$category['total']})\n";
            $text .= str_repeat("-", 40) . "\n";
            
            foreach ($category['tests'] as $test) {
                $status = $this->translateStatus($test['status']);
                $text .= "✓ {$test['name']}: {$status}\n";
                if (!empty($test['message'])) {
                    $text .= "  消息: {$test['message']}\n";
                }
                if (!empty($test['details'])) {
                    $text .= "  详情: {$test['details']}\n";
                }
                $text .= "\n";
            }
            $text .= "\n";
        }

        $text .= "=====================================\n";
        $text .= "报告结束 | " . date('Y-m-d H:i:s') . "\n";
        $text .= "=====================================\n";

        return $text;
    }

    /**
     * 翻译状态
     */
    private function translateStatus(string $status): string
    {
        $translations = [
            'pass' => '通过',
            'fail' => '失败',
            'warn' => '警告',
            'healthy' => '健康',
            'warning' => '警告',
            'error' => '错误'
        ];
        
        return $translations[$status] ?? $status;
    }

    /**
     * 清理临时文件
     */
    public function cleanupTempFiles(int $olderThanHours = 24): int
    {
        $deletedCount = 0;
        $cutoffTime = time() - ($olderThanHours * 3600);
        
        $files = glob($this->tempDir . DIRECTORY_SEPARATOR . 'diagnostic_report_*.{json,csv,html,txt}', GLOB_BRACE);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                if (unlink($file)) {
                    $deletedCount++;
                }
            }
        }
        
        return $deletedCount;
    }
}
