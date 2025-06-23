<?php
/**
 * AlingAI Platform SDK下载统计API
 * 功能：记录、统计、分析SDK下载数据
 * 作者：AlingAI Development Team
 * 日期：2024-06-15
 */

class SDKStatsAPI {
    private $logPath;
    private $statsFile;
    
    public function __construct() {
        $this->logPath = __DIR__ . '/../logs/';
        $this->statsFile = $this->logPath . 'sdk_stats.json';
        
        // 确保目录存在
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }
    
    /**
     * 获取统计数据
     * @return array 统计信息
     */
    public function getStats() {
        if (!file_exists($this->statsFile)) {
            return $this->getDefaultStats();
        }
        
        $stats = json_decode(file_get_contents($this->statsFile), true);
        if (!$stats) {
            return $this->getDefaultStats();
        }
        
        // 添加实时计算的数据
        $stats['current_time'] = date('Y-m-d H:i:s');
        $stats['active_downloads'] = $this->getActiveDownloadsCount();
        
        return $stats;
    }
    
    /**
     * 记录下载
     * @param string $language 编程语言
     * @param string $version 版本
     * @param string $userAgent 用户代理
     * @param string $ip IP地址
     * @return array 操作结果
     */
    public function recordDownload($language, $version, $userAgent = '', $ip = '') {
        $stats = $this->getStats();
        
        // 更新总下载量
        $stats['total_downloads']++;
        
        // 更新语言统计
        if (!isset($stats['downloads_by_language'][$language])) {
            $stats['downloads_by_language'][$language] = 0;
        }
        $stats['downloads_by_language'][$language]++;
        
        // 更新版本统计
        if (!isset($stats['downloads_by_version'][$version])) {
            $stats['downloads_by_version'][$version] = 0;
        }
        $stats['downloads_by_version'][$version]++;
        
        // 更新每日统计
        $today = date('Y-m-d');
        if (!isset($stats['daily_downloads'][$today])) {
            $stats['daily_downloads'][$today] = 0;
        }
        $stats['daily_downloads'][$today]++;
        
        // 记录最近下载
        $downloadRecord = [
            'timestamp' => time(),
            'date' => date('Y-m-d H:i:s'),
            'language' => $language,
            'version' => $version,
            'user_agent' => $userAgent,
            'ip' => $this->anonymizeIP($ip)
        ];
        
        if (!isset($stats['recent_downloads'])) {
            $stats['recent_downloads'] = [];
        }
        
        array_unshift($stats['recent_downloads'], $downloadRecord);
        
        // 只保留最近100条记录
        $stats['recent_downloads'] = array_slice($stats['recent_downloads'], 0, 100);
        
        // 更新最后更新时间
        $stats['last_updated'] = date('Y-m-d H:i:s');
        
        // 保存统计数据
        $this->saveStats($stats);
        
        // 记录详细日志
        $this->logDownload($downloadRecord);
        
        return [
            'success' => true,
            'message' => '下载记录已更新',
            'total_downloads' => $stats['total_downloads']
        ];
    }
    
    /**
     * 重置统计数据
     * @return array 操作结果
     */
    public function resetStats() {
        $stats = $this->getDefaultStats();
        $this->saveStats($stats);
        
        return [
            'success' => true,
            'message' => '统计数据已重置'
        ];
    }
    
    /**
     * 导出统计数据
     * @param string $format 导出格式 (json, csv, xml)
     * @return string 导出内容
     */
    public function exportStats($format = 'json') {
        $stats = $this->getStats();
        
        switch ($format) {
            case 'csv':
                return $this->exportToCSV($stats);
            case 'xml':
                return $this->exportToXML($stats);
            default:
                return json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * 获取默认统计数据结构
     * @return array 默认统计数据
     */
    private function getDefaultStats() {
        return [
            'total_downloads' => 0,
            'downloads_by_language' => [],
            'downloads_by_version' => [],
            'daily_downloads' => [],
            'recent_downloads' => [],
            'created_at' => date('Y-m-d H:i:s'),
            'last_updated' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * 保存统计数据
     * @param array $stats 统计数据
     */
    private function saveStats($stats) {
        file_put_contents($this->statsFile, json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }
    
    /**
     * 记录下载日志
     * @param array $record 下载记录
     */
    private function logDownload($record) {
        $logFile = $this->logPath . 'downloads_' . date('Y-m') . '.log';
        $logEntry = date('Y-m-d H:i:s') . ' | ' . 
                   $record['language'] . ' | ' . 
                   $record['version'] . ' | ' . 
                   $record['ip'] . ' | ' . 
                   substr($record['user_agent'], 0, 100) . PHP_EOL;
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * 匿名化IP地址
     * @param string $ip IP地址
     * @return string 匿名化后的IP
     */
    private function anonymizeIP($ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            return $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.***';
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            return implode(':', array_slice($parts, 0, 4)) . '::***';
        }
        return 'unknown';
    }
    
    /**
     * 获取当前活跃下载数
     * @return int 活跃下载数
     */
    private function getActiveDownloadsCount() {
        $downloadsDir = __DIR__ . '/../public/downloads/';
        if (!is_dir($downloadsDir)) {
            return 0;
        }
        
        $files = glob($downloadsDir . 'alingai_sdk_*.zip');
        return count($files);
    }
    
    /**
     * 导出为CSV格式
     * @param array $stats 统计数据
     * @return string CSV内容
     */
    private function exportToCSV($stats) {
        $csv = "语言,下载次数\n";
        foreach ($stats['downloads_by_language'] as $lang => $count) {
            $csv .= "{$lang},{$count}\n";
        }
        return $csv;
    }
    
    /**
     * 导出为XML格式
     * @param array $stats 统计数据
     * @return string XML内容
     */
    private function exportToXML($stats) {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<sdk_stats>\n";
        $xml .= "  <total_downloads>{$stats['total_downloads']}</total_downloads>\n";
        $xml .= "  <downloads>\n";
        
        foreach ($stats['downloads_by_language'] as $lang => $count) {
            $xml .= "    <language name='{$lang}'>{$count}</language>\n";
        }
        
        $xml .= "  </downloads>\n</sdk_stats>";
        return $xml;
    }
}

// 处理API请求
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $api = new SDKStatsAPI();
    
    $action = $_GET['action'] ?? 'stats';
    
    switch ($action) {
        case 'stats':
            $result = $api->getStats();
            break;
        case 'export':
            $format = $_GET['format'] ?? 'json';
            $content = $api->exportStats($format);
            
            // 设置适当的Content-Type
            switch ($format) {
                case 'csv':
                    header('Content-Type: text/csv; charset=utf-8');
                    header('Content-Disposition: attachment; filename="sdk_stats.csv"');
                    break;
                case 'xml':
                    header('Content-Type: application/xml; charset=utf-8');
                    header('Content-Disposition: attachment; filename="sdk_stats.xml"');
                    break;
                default:
                    header('Content-Type: application/json; charset=utf-8');
            }
            
            echo $content;
            exit;
        default:
            $result = ['error' => '未知的操作'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($result);
    
} elseif (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $api = new SDKStatsAPI();
    
    $action = $_POST['action'] ?? 'record';
    
    switch ($action) {
        case 'record':
            $language = $_POST['language'] ?? '';
            $version = $_POST['version'] ?? '';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            
            $result = $api->recordDownload($language, $version, $userAgent, $ip);
            break;
        case 'reset':
            $result = $api->resetStats();
            break;
        default:
            $result = ['error' => '未知的操作'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($result);
    
} else {
    echo "SDK统计API已就绪";
}
?>
