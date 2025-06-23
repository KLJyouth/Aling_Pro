<?php

declare(strict_types=1);

namespace AlingAi\Services;

use Psr\Log\LoggerInterface;

/**
 * 可视化服务类
 *
 * 提供数据可视化处理功能
 */
class VisualizationService
{
    private $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * 格式化威胁数据用于可视化
     *
     * @param array $threatData 原始威胁数据
     * @return array 格式化后的数据
     */
    public function formatThreatData(array $threatData): array
    {
        $this->logger->debug("格式化威胁数据用于可视化", ["data_size" => count($threatData)]);
        
        $visualData = [
            "nodes" => [],
            "links" => [],
            "categories" => []
        ];
        
        // 处理节点
        foreach ($threatData as $index => $threat) {
            $visualData["nodes"][] = [
                "id" => $index,
                "name" => $threat["name"] ?? "未知威胁",
                "value" => $threat["severity"] ?? 1,
                "category" => $threat["type"] ?? 0,
                "symbolSize" => $this->calculateSymbolSize($threat)
            ];
            
            // 处理关联
            if (isset($threat["relationships"]) && is_array($threat["relationships"])) {
                foreach ($threat["relationships"] as $rel) {
                    if (isset($rel["target_id"])) {
                        $visualData["links"][] = [
                            "source" => $index,
                            "target" => $rel["target_id"],
                            "value" => $rel["strength"] ?? 1
                        ];
                    }
                }
            }
        }
        
        // 处理分类
        $categories = [];
        foreach ($threatData as $threat) {
            $type = $threat["type"] ?? "other";
            if (!in_array($type, $categories)) {
                $categories[] = $type;
            }
        }
        
        foreach ($categories as $index => $category) {
            $visualData["categories"][] = [
                "name" => $this->getCategoryName($category)
            ];
        }
        
        return $visualData;
    }
    
    /**
     * 根据威胁计算节点大小
     *
     * @param array $threat 威胁数据
     * @return int 节点大小
     */
    private function calculateSymbolSize(array $threat): int
    {
        $base = 10;
        $severity = $threat["severity"] ?? 1;
        $impact = $threat["impact"] ?? 1;
        
        return $base + ($severity * $impact * 2);
    }
    
    /**
     * 获取分类名称
     *
     * @param string $category 分类代码
     * @return string 分类名称
     */
    private function getCategoryName(string $category): string
    {
        $categoryMap = [
            "sql_injection" => "SQL注入",
            "xss" => "跨站脚本",
            "csrf" => "跨站请求伪造",
            "file_upload" => "文件上传漏洞",
            "auth" => "认证漏洞",
            "config" => "配置问题",
            "network" => "网络漏洞",
            "other" => "其他威胁"
        ];
        
        return $categoryMap[$category] ?? "未知类型";
    }
}
