<?php // Logs Controller

namespace App\Controllers;

use App\Core\Controller;

/**
 * ��־���������
 * ��������־�����������
 */
class LogsController extends Controller
{
    /**
     * ��־Ŀ¼·��
     * @var string
     */
    private $logsPath;
    
    /**
     * ���캯��
     */
    public function __construct()
    {
        parent::__construct(];
        $this->logsPath = BASE_PATH . '/storage/logs';
    }
    
    /**
     * ��ʾ��־������ҳ
     * @return void
     */
    public function index()
    {
        // ��ȡ��־��������
        $logsOverview = $this->getLogsOverview(];
        
        // ��ȡ�������־�ļ�
        $recentLogs = $this->getRecentLogFiles(];
        
        // ��Ⱦ��ͼ
        $this->view('logs.index', [
            'logsOverview' => $logsOverview,
            'recentLogs' => $recentLogs,
            'pageTitle' => 'IT��ά���� - ��־����'
        ]];
    }
    
    /**
     * ��ʾϵͳ��־ҳ��
     * @return void
     */
    public function system()
    {
        // ��ȡϵͳ��־
        $logFile = $this->input('file', 'system.log'];
        $page = $this->input('page', 1];
        $perPage = $this->input('per_page', 100];
        
        // ��ȡ��־����
        $logContent = $this->readLogFile($logFile, $page, $perPage];
        
        // ��Ⱦ��ͼ
        $this->view('logs.system', [
            'logFile' => $logFile,
            'logContent' => $logContent,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $logContent['totalPages'], 
            'pageTitle' => 'IT��ά���� - ϵͳ��־'
        ]];
    }
    
    /**
     * ��ʾ������־ҳ��
     * @return void
     */
    public function errors()
    {
        // ��ȡ������־
        $logFile = $this->input('file', 'errors.log'];
        $page = $this->input('page', 1];
        $perPage = $this->input('per_page', 100];
        
        // ��ȡ��־����
        $logContent = $this->readLogFile($logFile, $page, $perPage];
        
        // ��Ⱦ��ͼ
        $this->view('logs.errors', [
            'logFile' => $logFile,
            'logContent' => $logContent,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $logContent['totalPages'], 
            'pageTitle' => 'IT��ά���� - ������־'
        ]];
    }
    
    /**
     * ��ʾ������־ҳ��
     * @return void
     */
    public function access()
    {
        // ��ȡ������־
        $logFile = $this->input('file', 'access.log'];
        $page = $this->input('page', 1];
        $perPage = $this->input('per_page', 100];
        
        // ��ȡ��־����
        $logContent = $this->readLogFile($logFile, $page, $perPage];
        
        // ��Ⱦ��ͼ
        $this->view('logs.access', [
            'logFile' => $logFile,
            'logContent' => $logContent,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $logContent['totalPages'], 
            'pageTitle' => 'IT��ά���� - ������־'
        ]];
    }
    
    /**
     * ��ʾ��ȫ��־ҳ��
     * @return void
     */
    public function security()
    {
        // ��ȡ��ȫ��־
        $logFile = $this->input('file', 'security_events.log'];
        $page = $this->input('page', 1];
        $perPage = $this->input('per_page', 100];
        
        // ��ȡ��־����
        $logContent = $this->readLogFile($logFile, $page, $perPage];
        
        // ��Ⱦ��ͼ
        $this->view('logs.security', [
            'logFile' => $logFile,
            'logContent' => $logContent,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $logContent['totalPages'], 
            'pageTitle' => 'IT��ά���� - ��ȫ��־'
        ]];
    }
    
    /**
     * ������־
     * @return void
     */
    public function search()
    {
        // ��ȡ��������
        $keyword = $this->input('keyword'];
        $logType = $this->input('log_type', 'all'];
        $startDate = $this->input('start_date'];
        $endDate = $this->input('end_date'];
        
        // ִ������
        $searchResults = $this->searchLogs($keyword, $logType, $startDate, $endDate];
        
        // ����JSON���
        $this->json($searchResults];
    }
    
    /**
     * ������־�ļ�
     * @return void
     */
    public function download()
    {
        // ��ȡ�ļ���
        $fileName = $this->input('file'];
        
        // ��֤����
        if (empty($fileName)) {
            $this->json([
                'success' => false,
                'message' => '�ļ�������Ϊ��'
            ],  400];
            return;
        }
        
        // ����ļ��Ƿ����
        $filePath = $this->logsPath . '/' . $fileName;
        if (!file_exists($filePath)) {
            $this->json([
                'success' => false,
                'message' => '�ļ�������'
            ],  404];
            return;
        }
        
        // ��������ͷ
        header('Content-Description: File Transfer'];
        header('Content-Type: application/octet-stream'];
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"'];
        header('Expires: 0'];
        header('Cache-Control: must-revalidate'];
        header('Pragma: public'];
        header('Content-Length: ' . filesize($filePath)];
        
        // ����������
        ob_clean(];
        flush(];
        
        // ����ļ�����
        readfile($filePath];
        exit;
    }
    
    /**
     * �����־�ļ�
     * @return void
     */
    public function clear()
    {
        // ��ȡ�ļ���
        $fileName = $this->input('file'];
        
        // ��֤����
        if (empty($fileName)) {
            $this->json([
                'success' => false,
                'message' => '�ļ�������Ϊ��'
            ],  400];
            return;
        }
        
        // ����ļ��Ƿ����
        $filePath = $this->logsPath . '/' . $fileName;
        if (!file_exists($filePath)) {
            $this->json([
                'success' => false,
                'message' => '�ļ�������'
            ],  404];
            return;
        }
        
        // ����ļ�����
        if (file_put_contents($filePath, '') !== false) {
            $this->json([
                'success' => true,
                'message' => '��־�ļ������'
            ]];
        } else {
            $this->json([
                'success' => false,
                'message' => '�����־�ļ�ʧ��'
            ],  500];
        }
    }
    
    /**
     * ��ȡ��־��������
     * @return array ��־��������
     */
    private function getLogsOverview()
    {
        // �����־Ŀ¼�Ƿ����
        if (!is_dir($this->logsPath)) {
            mkdir($this->logsPath, 0755, true];
        }
        
        // ��ȡ��־�ļ��б�
        $logFiles = glob($this->logsPath . '/*.log'];
        
        // ͳ����־�ļ�����
        $totalLogFiles = count($logFiles];
        
        // ͳ����־�ļ��ܴ�С
        $totalSize = 0;
        foreach ($logFiles as $file) {
            $totalSize += filesize($file];
        }
        
        // ͳ�ƽ�����־����
        $todayLogs = 0;
        $today = date('Y-m-d'];
        foreach ($logFiles as $file) {
            if (date('Y-m-d', filemtime($file)) === $today) {
                $todayLogs++;
            }
        }
        
        // ͳ�ƴ�����־����
        $errorLogs = count(glob($this->logsPath . '/*error*.log')];
        
        return [
            'totalLogFiles' => $totalLogFiles,
            'totalSize' => $this->formatBytes($totalSize],
            'todayLogs' => $todayLogs,
            'errorLogs' => $errorLogs
        ];
    }
    
    /**
     * ��ȡ�������־�ļ�
     * @param int $limit �ļ���������
     * @return array ��־�ļ��б�
     */
    private function getRecentLogFiles($limit = 10)
    {
        // �����־Ŀ¼�Ƿ����
        if (!is_dir($this->logsPath)) {
            mkdir($this->logsPath, 0755, true];
        }
        
        // ��ȡ��־�ļ��б�
        $logFiles = glob($this->logsPath . '/*.log'];
        
        // ���޸�ʱ������
        usort($logFiles, function($a, $b) {
            return filemtime($b) - filemtime($a];
        }];
        
        // ��������
        $logFiles = array_slice($logFiles, 0, $limit];
        
        // ��ʽ�����
        $result = [];
        foreach ($logFiles as $file) {
            $fileName = basename($file];
            $fileSize = filesize($file];
            $modifiedTime = filemtime($file];
            $lineCount = $this->countFileLines($file];
            
            // ȷ����־����
            $type = 'other';
            if (strpos($fileName, 'system') !== false) {
                $type = 'system';
            } elseif (strpos($fileName, 'error') !== false) {
                $type = 'error';
            } elseif (strpos($fileName, 'access') !== false) {
                $type = 'access';
            } elseif (strpos($fileName, 'security') !== false) {
                $type = 'security';
            }
            
            $result[] = [
                'name' => $fileName,
                'path' => $file,
                'size' => $this->formatBytes($fileSize],
                'modifiedTime' => date('Y-m-d H:i:s', $modifiedTime],
                'lineCount' => $lineCount,
                'type' => $type
            ];
        }
        
        return $result;
    }
    
    /**
     * ��ȡ��־�ļ�����
     * @param string $fileName �ļ���
     * @param int $page ҳ��
     * @param int $perPage ÿҳ����
     * @return array ��־����
     */
    private function readLogFile($fileName, $page = 1, $perPage = 100)
    {
        // �����ļ�·��
        $filePath = $this->logsPath . '/' . $fileName;
        
        // ����ļ��Ƿ����
        if (!file_exists($filePath)) {
            return [
                'lines' => [], 
                'totalLines' => 0,
                'totalPages' => 0,
                'error' => '�ļ�������'
            ];
        }
        
        // ͳ���ļ�������
        $totalLines = $this->countFileLines($filePath];
        
        // ������ҳ��
        $totalPages = ceil($totalLines / $perPage];
        
        // ������ʼ�кͽ�����
        $startLine = ($page - 1) * $perPage;
        $endLine = $startLine + $perPage - 1;
        
        // ��ȡָ����Χ����
        $lines = [];
        $lineNumber = 0;
        $file = fopen($filePath, 'r'];
        
        if ($file) {
            while (($line = fgets($file)) !== false) {
                if ($lineNumber >= $startLine && $lineNumber <= $endLine) {
                    $lines[] = [
                        'number' => $lineNumber + 1,
                        'content' => trim($line],
                        'timestamp' => $this->extractTimestamp($line)
                    ];
                }
                
                $lineNumber++;
                
                // ����Ѿ���ȡ���˽����У��Ϳ���ֹͣ��
                if ($lineNumber > $endLine) {
                    break;
                }
            }
            
            fclose($file];
        }
        
        return [
            'lines' => $lines,
            'totalLines' => $totalLines,
            'totalPages' => $totalPages,
            'error' => null
        ];
    }
    
    /**
     * ������־
     * @param string $keyword �ؼ���
     * @param string $logType ��־����
     * @param string $startDate ��ʼ����
     * @param string $endDate ��������
     * @return array �������
     */
    private function searchLogs($keyword, $logType = 'all', $startDate = null, $endDate = null)
    {
        // �����־Ŀ¼�Ƿ����
        if (!is_dir($this->logsPath)) {
            mkdir($this->logsPath, 0755, true];
        }
        
        // ȷ��Ҫ�������ļ�
        $filesToSearch = [];
        if ($logType === 'all') {
            $filesToSearch = glob($this->logsPath . '/*.log'];
        } else {
            switch ($logType) {
                case 'system':
                    $filesToSearch = glob($this->logsPath . '/*system*.log'];
                    break;
                case 'error':
                    $filesToSearch = glob($this->logsPath . '/*error*.log'];
                    break;
                case 'access':
                    $filesToSearch = glob($this->logsPath . '/*access*.log'];
                    break;
                case 'security':
                    $filesToSearch = glob($this->logsPath . '/*security*.log'];
                    break;
            }
        }
        
        // �������
        $results = [];
        
        // �����ļ�
        foreach ($filesToSearch as $file) {
            $fileName = basename($file];
            $fileHandle = fopen($file, 'r'];
            
            if ($fileHandle) {
                $lineNumber = 0;
                
                while (($line = fgets($fileHandle)) !== false) {
                    $lineNumber++;
                    
                    // ����Ƿ�����ؼ���
                    if (!empty($keyword) && stripos($line, $keyword) === false) {
                        continue;
                    }
                    
                    // ��ȡʱ���
                    $timestamp = $this->extractTimestamp($line];
                    
                    // ������ڷ�Χ
                    if (!empty($timestamp)) {
                        $date = date('Y-m-d', strtotime($timestamp)];
                        
                        if (!empty($startDate) && $date < $startDate) {
                            continue;
                        }
                        
                        if (!empty($endDate) && $date > $endDate) {
                            continue;
                        }
                    }
                    
                    // ��ӵ������
                    $results[] = [
                        'file' => $fileName,
                        'line' => $lineNumber,
                        'content' => trim($line],
                        'timestamp' => $timestamp
                    ];
                    
                    // ���ƽ������
                    if (count($results) >= 1000) {
                        break 2; // ��������ѭ��
                    }
                }
                
                fclose($fileHandle];
            }
        }
        
        // ��ʱ�������
        usort($results, function($a, $b) {
            if (empty($a['timestamp']) && empty($b['timestamp'])) {
                return 0;
            }
            
            if (empty($a['timestamp'])) {
                return 1;
            }
            
            if (empty($b['timestamp'])) {
                return -1;
            }
            
            return strtotime($b['timestamp']) - strtotime($a['timestamp']];
        }];
        
        return [
            'success' => true,
            'count' => count($results],
            'results' => $results
        ];
    }
    
    /**
     * ͳ���ļ�����
     * @param string $file �ļ�·��
     * @return int ����
     */
    private function countFileLines($file)
    {
        $lineCount = 0;
        $handle = fopen($file, 'r'];
        
        if ($handle) {
            while (fgets($handle) !== false) {
                $lineCount++;
            }
            
            fclose($handle];
        }
        
        return $lineCount;
    }
    
    /**
     * ��ʽ���ֽ���Ϊ�ɶ���ʽ
     * @param int $bytes �ֽ���
     * @param int $precision ����
     * @return string ��ʽ������ַ���
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0];
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)];
        $pow = min($pow, count($units) - 1];
        
        $bytes /= pow(1024, $pow];
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * ����־������ȡʱ���
     * @param string $line ��־��
     * @return string|null ʱ���
     */
    private function extractTimestamp($line)
    {
        // ����ƥ�䳣����ʱ�����ʽ
        $patterns = [
            // [2023-06-25 10:30:45]
            '/\[(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\]/',
            // 2023-06-25T10:30:45+00:00
            '/(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[\+\-]\d{2}:\d{2})/',
            // 2023-06-25 10:30:45
            '/(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})/',
            // 25/Jun/2023:10:30:45 +0000
            '/(\d{2}\/[A-Za-z) {3}\/\d{4}:\d{2}:\d{2}:\d{2}\s[\+\-]\d{4})/',
            // Jun 25 10:30:45
            '/([A-Za-z) {3}\s+\d{1,2}\s\d{2}:\d{2}:\d{2})/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $line, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
}

