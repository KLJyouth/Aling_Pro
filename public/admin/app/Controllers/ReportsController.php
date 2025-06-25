<?php // Reports Controller

namespace App\Controllers;

use App\Core\Controller;

/**
 * ��ά���������
 * ��������ά�����������
 */
class ReportsController extends Controller
{
    /**
     * ��ʾ��ά������ҳ
     * @return void
     */
    public function index()
    {
        // ��ȡ�����������
        $reportsOverview = $this->getReportsOverview(];
        
        // ��ȡ����ı���
        $recentReports = $this->getRecentReports(];
        
        // ��Ⱦ��ͼ
        $this->view('reports.index', [
            'reportsOverview' => $reportsOverview,
            'recentReports' => $recentReports,
            'pageTitle' => 'IT��ά���� - ��ά����'
        ]];
    }
    
    /**
     * ��ʾϵͳ���ܱ���ҳ��
     * @return void
     */
    public function performance()
    {
        // ��ȡ��������
        $performanceData = $this->getPerformanceData(];
        
        // ��Ⱦ��ͼ
        $this->view('reports.performance', [
            'performanceData' => $performanceData,
            'pageTitle' => 'IT��ά���� - ���ܱ���'
        ]];
    }
    
    /**
     * ��ʾ��ȫ��Ʊ���ҳ��
     * @return void
     */
    public function security()
    {
        // ��ȡ��ȫ�������
        $securityData = $this->getSecurityAuditData(];
        
        // ��Ⱦ��ͼ
        $this->view('reports.security', [
            'securityData' => $securityData,
            'pageTitle' => 'IT��ά���� - ��ȫ��Ʊ���'
        ]];
    }
    
    /**
     * ��ʾ����ͳ�Ʊ���ҳ��
     * @return void
     */
    public function errors()
    {
        // ��ȡ����ͳ������
        $errorData = $this->getErrorStatistics(];
        
        // ��Ⱦ��ͼ
        $this->view('reports.errors', [
            'errorData' => $errorData,
            'pageTitle' => 'IT��ά���� - ����ͳ�Ʊ���'
        ]];
    }
    
    /**
     * �����Զ��屨��
     * @return void
     */
    public function generate()
    {
        // ��ȡ��������
        $reportType = $this->input('report_type'];
        
        // ��ȡʱ�䷶Χ
        $startDate = $this->input('start_date'];
        $endDate = $this->input('end_date'];
        
        // ��ȡ������������
        $filters = $this->input('filters', []];
        
        // ���ɱ���
        $reportData = $this->generateCustomReport($reportType, $startDate, $endDate, $filters];
        
        // ����JSON���
        $this->json($reportData];
    }
    
    /**
     * ��������
     * @return void
     */
    public function export()
    {
        // ��ȡ����ID
        $reportId = $this->input('report_id'];
        
        // ��ȡ������ʽ
        $format = $this->input('format', 'pdf'];
        
        // ��������
        $result = $this->exportReport($reportId, $format];
        
        // ����JSON���
        $this->json($result];
    }
    
    /**
     * ��ȡ�����������
     * @return array �����������
     */
    private function getReportsOverview()
    {
        // ģ�ⱨ���������
        return [
            'totalReports' => rand(50, 200],
            'generatedToday' => rand(1, 10],
            'scheduledReports' => rand(5, 15],
            'customReports' => rand(10, 30)
        ];
    }
    
    /**
     * ��ȡ����ı���
     * @param int $limit ������������
     * @return array �����б�
     */
    private function getRecentReports($limit = 10)
    {
        // ģ�ⱨ������
        $reports = [];
        $types = ['performance', 'security', 'errors', 'usage', 'custom'];
        $statuses = ['completed', 'scheduled', 'failed'];
        
        for ($i = 0; $i < $limit; $i++) {
            $date = date('Y-m-d H:i:s', time() - rand(0, 86400 * 30)];
            $type = $types[array_rand($types)];
            $status = $statuses[array_rand($statuses)];
            
            $reports[] = [
                'id' => 'report_' . ($i + 1],
                'name' => ucfirst($type) . ' Report - ' . date('Y-m-d', strtotime($date)],
                'type' => $type,
                'date' => $date,
                'status' => $status,
                'size' => rand(100, 5000) . ' KB',
                'creator' => 'admin'
            ];
        }
        
        // ����������
        usort($reports, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']];
        }];
        
        return $reports;
    }
    
    /**
     * ��ȡ��������
     * @return array ��������
     */
    private function getPerformanceData()
    {
        // ģ����������
        $dates = [];
        $cpuUsage = [];
        $memoryUsage = [];
        $diskUsage = [];
        $responseTime = [];
        
        // ���ɹ�ȥ30�������
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days")];
            $dates[] = $date;
            $cpuUsage[] = rand(10, 90];
            $memoryUsage[] = rand(20, 80];
            $diskUsage[] = rand(30, 95];
            $responseTime[] = rand(50, 500];
        }
        
        return [
            'dates' => $dates,
            'cpuUsage' => $cpuUsage,
            'memoryUsage' => $memoryUsage,
            'diskUsage' => $diskUsage,
            'responseTime' => $responseTime,
            'averageCpu' => array_sum($cpuUsage) / count($cpuUsage],
            'averageMemory' => array_sum($memoryUsage) / count($memoryUsage],
            'averageDisk' => array_sum($diskUsage) / count($diskUsage],
            'averageResponse' => array_sum($responseTime) / count($responseTime)
        ];
    }
    
    /**
     * ��ȡ��ȫ�������
     * @return array ��ȫ�������
     */
    private function getSecurityAuditData()
    {
        // ģ�ⰲȫ�������
        $dates = [];
        $loginAttempts = [];
        $failedLogins = [];
        $securityEvents = [];
        
        // ���ɹ�ȥ30�������
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days")];
            $dates[] = $date;
            $loginAttempts[] = rand(50, 200];
            $failedLogins[] = rand(0, 20];
            $securityEvents[] = rand(0, 10];
        }
        
        return [
            'dates' => $dates,
            'loginAttempts' => $loginAttempts,
            'failedLogins' => $failedLogins,
            'securityEvents' => $securityEvents,
            'totalLoginAttempts' => array_sum($loginAttempts],
            'totalFailedLogins' => array_sum($failedLogins],
            'totalSecurityEvents' => array_sum($securityEvents],
            'securityIncidents' => $this->getSecurityIncidents()
        ];
    }
    
    /**
     * ��ȡ��ȫ�¼�����
     * @return array ��ȫ�¼�����
     */
    private function getSecurityIncidents()
    {
        // ģ�ⰲȫ�¼�����
        return [
            [
                'id' => 'incident_1',
                'type' => 'δ��Ȩ���ʳ���',
                'severity' => '��',
                'date' => date('Y-m-d H:i:s', time() - rand(0, 86400 * 30)],
                'source' => '192.168.' . rand(1, 254) . '.' . rand(1, 254],
                'target' => '/admin/settings',
                'description' => '��γ��Է��ʹ�������ҳ��'
            ], 
            [
                'id' => 'incident_2',
                'type' => '�����ƽ��¼',
                'severity' => '��',
                'date' => date('Y-m-d H:i:s', time() - rand(0, 86400 * 30)],
                'source' => '192.168.' . rand(1, 254) . '.' . rand(1, 254],
                'target' => '��¼ҳ��',
                'description' => '��ʱ���ڶ�ε�¼ʧ��'
            ], 
            [
                'id' => 'incident_3',
                'type' => '�ļ�Ȩ�ޱ��',
                'severity' => '��',
                'date' => date('Y-m-d H:i:s', time() - rand(0, 86400 * 30)],
                'source' => 'admin',
                'target' => '/config/app.php',
                'description' => '�����ļ�Ȩ�ޱ��޸�'
            ], 
            [
                'id' => 'incident_4',
                'type' => '�쳣API����',
                'severity' => '��',
                'date' => date('Y-m-d H:i:s', time() - rand(0, 86400 * 30)],
                'source' => '192.168.' . rand(1, 254) . '.' . rand(1, 254],
                'target' => '/api/users',
                'description' => '�쳣��API����ģʽ'
            ], 
            [
                'id' => 'incident_5',
                'type' => '���ø���',
                'severity' => '��',
                'date' => date('Y-m-d H:i:s', time() - rand(0, 86400 * 30)],
                'source' => 'system',
                'target' => 'ϵͳ����',
                'description' => 'ϵͳ���ñ�����'
            ]
        ];
    }
    
    /**
     * ��ȡ����ͳ������
     * @return array ����ͳ������
     */
    private function getErrorStatistics()
    {
        // ģ�����ͳ������
        $dates = [];
        $errors = [];
        $warnings = [];
        $notices = [];
        
        // ���ɹ�ȥ30�������
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days")];
            $dates[] = $date;
            $errors[] = rand(0, 20];
            $warnings[] = rand(5, 50];
            $notices[] = rand(10, 100];
        }
        
        return [
            'dates' => $dates,
            'errors' => $errors,
            'warnings' => $warnings,
            'notices' => $notices,
            'totalErrors' => array_sum($errors],
            'totalWarnings' => array_sum($warnings],
            'totalNotices' => array_sum($notices],
            'errorDetails' => $this->getErrorDetails()
        ];
    }
    
    /**
     * ��ȡ��������
     * @return array ��������
     */
    private function getErrorDetails()
    {
        // ģ���������
        return [
            [
                'id' => 'error_1',
                'type' => 'PHP Fatal Error',
                'count' => rand(5, 20],
                'lastOccurrence' => date('Y-m-d H:i:s', time() - rand(0, 86400 * 7)],
                'file' => 'app/Controllers/DashboardController.php',
                'line' => rand(50, 200],
                'message' => 'Call to undefined method App\Models\User::getStatistics()'
            ], 
            [
                'id' => 'error_2',
                'type' => 'PHP Warning',
                'count' => rand(10, 50],
                'lastOccurrence' => date('Y-m-d H:i:s', time() - rand(0, 86400 * 7)],
                'file' => 'app/Models/Report.php',
                'line' => rand(50, 200],
                'message' => 'Division by zero'
            ], 
            [
                'id' => 'error_3',
                'type' => 'PHP Notice',
                'count' => rand(20, 100],
                'lastOccurrence' => date('Y-m-d H:i:s', time() - rand(0, 86400 * 7)],
                'file' => 'app/Views/dashboard/index.php',
                'line' => rand(50, 200],
                'message' => 'Undefined variable: user'
            ], 
            [
                'id' => 'error_4',
                'type' => 'JavaScript Error',
                'count' => rand(10, 30],
                'lastOccurrence' => date('Y-m-d H:i:s', time() - rand(0, 86400 * 7)],
                'file' => 'public/js/app.js',
                'line' => rand(50, 200],
                'message' => 'Uncaught TypeError: Cannot read property \'length\' of undefined'
            ], 
            [
                'id' => 'error_5',
                'type' => '404 Not Found',
                'count' => rand(5, 30],
                'lastOccurrence' => date('Y-m-d H:i:s', time() - rand(0, 86400 * 7)],
                'file' => 'N/A',
                'line' => 'N/A',
                'message' => 'Requested URL not found'
            ]
        ];
    }
    
    /**
     * �����Զ��屨��
     * @param string $reportType ��������
     * @param string $startDate ��ʼ����
     * @param string $endDate ��������
     * @param array $filters ��������
     * @return array ��������
     */
    private function generateCustomReport($reportType, $startDate, $endDate, $filters)
    {
        // ģ�ⱨ�����ɹ���
        sleep(1];
        
        // ���ݱ������ͷ��ز�ͬ������
        switch ($reportType) {
            case 'performance':
                $data = $this->getPerformanceData(];
                break;
            case 'security':
                $data = $this->getSecurityAuditData(];
                break;
            case 'errors':
                $data = $this->getErrorStatistics(];
                break;
            default:
                $data = [
                    'message' => 'δ֪�ı�������'
                ];
        }
        
        return [
            'success' => true,
            'reportId' => 'report_' . uniqid(),
            'reportType' => $reportType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => date('Y-m-d H:i:s'],
            'data' => $data
        ];
    }
    
    /**
     * ��������
     * @param string $reportId ����ID
     * @param string $format ������ʽ
     * @return array ִ�н��
     */
    private function exportReport($reportId, $format)
    {
        // ģ�⵼������
        sleep(1];
        
        return [
            'success' => true,
            'message' => '���浼���ɹ�',
            'details' => [
                'reportId' => $reportId,
                'format' => $format,
                'exportedAt' => date('Y-m-d H:i:s'],
                'downloadUrl' => BASE_URL . '/reports/download/' . $reportId . '.' . $format
            ]
        ];
    }
}

