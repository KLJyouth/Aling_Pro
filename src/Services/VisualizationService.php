<?php

declare(strict_types=1);

namespace AlingAi\Services;

use Psr\Log\LoggerInterface;

/**
 * ���ӻ�������
 *
 * �ṩ���ݿ��ӻ�������
 */
class VisualizationService
{
    private $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * ��ʽ����в�������ڿ��ӻ�
     *
     * @param array $threatData ԭʼ��в����
     * @return array ��ʽ���������
     */
    public function formatThreatData(array $threatData): array
    {
        $this->logger->debug("��ʽ����в�������ڿ��ӻ�", ["data_size" => count($threatData)]);
        
        $visualData = [
            "nodes" => [],
            "links" => [],
            "categories" => []
        ];
        
        // ����ڵ�
        foreach ($threatData as $index => $threat) {
            $visualData["nodes"][] = [
                "id" => $index,
                "name" => $threat["name"] ?? "δ֪��в",
                "value" => $threat["severity"] ?? 1,
                "category" => $threat["type"] ?? 0,
                "symbolSize" => $this->calculateSymbolSize($threat)
            ];
            
            // �������
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
        
        // �������
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
     * ������в����ڵ��С
     *
     * @param array $threat ��в����
     * @return int �ڵ��С
     */
    private function calculateSymbolSize(array $threat): int
    {
        $base = 10;
        $severity = $threat["severity"] ?? 1;
        $impact = $threat["impact"] ?? 1;
        
        return $base + ($severity * $impact * 2);
    }
    
    /**
     * ��ȡ��������
     *
     * @param string $category �������
     * @return string ��������
     */
    private function getCategoryName(string $category): string
    {
        $categoryMap = [
            "sql_injection" => "SQLע��",
            "xss" => "��վ�ű�",
            "csrf" => "��վ����α��",
            "file_upload" => "�ļ��ϴ�©��",
            "auth" => "��֤©��",
            "config" => "��������",
            "network" => "����©��",
            "other" => "������в"
        ];
        
        return $categoryMap[$category] ?? "δ֪����";
    }
}
