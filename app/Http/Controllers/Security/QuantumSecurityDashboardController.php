<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Security\QuantumAiSecurityService;
use App\Services\Security\QuantumDefenseService;
use App\Models\Security\SecurityThreat;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 量子安全仪表盘控制器
 * 提供安全仪表盘所需的数据
 */
class QuantumSecurityDashboardController extends Controller
{
    /**
     * 量子AI安全服务
     * 
     * @var QuantumAiSecurityService
     */
    protected $quantumAiSecurityService;
    
    /**
     * 量子防御服务
     * 
     * @var QuantumDefenseService
     */
    protected $quantumDefenseService;
    
    /**
     * 构造函数
     * 
     * @param QuantumAiSecurityService $quantumAiSecurityService
     * @param QuantumDefenseService $quantumDefenseService
     */
    public function __construct(
        QuantumAiSecurityService $quantumAiSecurityService,
        QuantumDefenseService $quantumDefenseService
    ) {
        $this->quantumAiSecurityService = $quantumAiSecurityService;
        $this->quantumDefenseService = $quantumDefenseService;
    }
    
    /**
     * 显示量子安全仪表盘视图
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // 获取仪表盘概览数据
        $overview = $this->getDashboardOverview();
        
        return view('security.quantum-security-dashboard', [
            'overview' => $overview
        ]);
    }
    
    /**
     * 获取仪表盘概览数据
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboardOverview()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        
        // 获取今日威胁统计
        $todayThreats = SecurityThreat::whereDate('created_at', $today)->count();
        $todayBlockedThreats = SecurityThreat::whereDate('created_at', $today)
            ->where('status', 'resolved')
            ->count();
        
        // 获取本周威胁统计
        $weeklyThreats = SecurityThreat::where('created_at', '>=', $startOfWeek)->count();
        
        // 获取高危威胁统计
        $highSeverityThreats = SecurityThreat::where('severity', 'high')
            ->where('created_at', '>=', $startOfWeek)
            ->count();
        
        // 获取安全评分
        $securityScore = $this->quantumAiSecurityService->calculateSecurityScore();
        
        return [
            'today_threats' => $todayThreats,
            'today_blocked_threats' => $todayBlockedThreats,
            'weekly_threats' => $weeklyThreats,
            'high_severity_threats' => $highSeverityThreats,
            'security_score' => $securityScore
        ];
    }
    
    /**
     * 获取威胁趋势数据
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getThreatTrends(Request $request)
    {
        $timeRange = $request->input('time_range', 'week');
        $now = Carbon::now();
        
        switch ($timeRange) {
            case 'day':
                $start = $now->copy()->startOfDay();
                $interval = '1 hour';
                $format = 'H:i';
                $groupBy = 'hour';
                break;
            case 'month':
                $start = $now->copy()->startOfMonth();
                $interval = '1 day';
                $format = 'm-d';
                $groupBy = 'date';
                break;
            case 'week':
            default:
                $start = $now->copy()->startOfWeek();
                $interval = '1 day';
                $format = 'D';
                $groupBy = 'date';
                break;
        }
        
        // 获取威胁数据
        $threats = SecurityThreat::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as interval_start"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $start)
            ->groupBy('interval_start')
            ->orderBy('interval_start')
            ->get()
            ->keyBy('interval_start');
        
        // 获取已处理威胁数据
        $resolvedThreats = SecurityThreat::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as interval_start"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $start)
            ->where('status', 'resolved')
            ->groupBy('interval_start')
            ->orderBy('interval_start')
            ->get()
            ->keyBy('interval_start');
        
        // 生成时间间隔
        $intervals = [];
        $labels = [];
        $current = $start->copy();
        
        while ($current <= $now) {
            $intervalKey = $current->format('Y-m-d H:00:00');
            $intervals[] = $intervalKey;
            $labels[] = $current->format($format);
            $current->modify("+{$interval}");
        }
        
        // 准备数据
        $threatCounts = [];
        $resolvedCounts = [];
        
        foreach ($intervals as $interval) {
            $threatCounts[] = $threats->has($interval) ? $threats[$interval]->count : 0;
            $resolvedCounts[] = $resolvedThreats->has($interval) ? $resolvedThreats[$interval]->count : 0;
        }
        
        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => '检测到的威胁',
                    'data' => $threatCounts,
                    'borderColor' => '#7b68ee',
                    'backgroundColor' => 'rgba(123, 104, 238, 0.2)'
                ],
                [
                    'label' => '已处理的威胁',
                    'data' => $resolvedCounts,
                    'borderColor' => '#2ed573',
                    'backgroundColor' => 'rgba(46, 213, 115, 0.2)'
                ]
            ]
        ]);
    }
    
    /**
     * 获取威胁分布数据
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getThreatDistribution(Request $request)
    {
        $distributionType = $request->input('distribution_type', 'byType');
        $startDate = Carbon::now()->subDays(30);
        
        switch ($distributionType) {
            case 'bySeverity':
                $data = SecurityThreat::select('severity', DB::raw('COUNT(*) as count'))
                    ->where('created_at', '>=', $startDate)
                    ->groupBy('severity')
                    ->get();
                
                $labels = [];
                $counts = [];
                $colors = [
                    'high' => '#ff4757',
                    'medium' => '#ff9f43',
                    'low' => '#2ed573'
                ];
                
                foreach ($data as $item) {
                    $labels[] = $this->getSeverityLabel($item->severity);
                    $counts[] = $item->count;
                }
                
                break;
                
            case 'byStatus':
                $data = SecurityThreat::select('status', DB::raw('COUNT(*) as count'))
                    ->where('created_at', '>=', $startDate)
                    ->groupBy('status')
                    ->get();
                
                $labels = [];
                $counts = [];
                $colors = [
                    'detected' => '#ff4757',
                    'analyzing' => '#ff9f43',
                    'responding' => '#3498db',
                    'contained' => '#7b68ee',
                    'resolved' => '#2ed573',
                    'false_positive' => '#a5b1c2'
                ];
                
                foreach ($data as $item) {
                    $labels[] = $this->getStatusLabel($item->status);
                    $counts[] = $item->count;
                }
                
                break;
                
            case 'byType':
            default:
                $data = SecurityThreat::select('type', DB::raw('COUNT(*) as count'))
                    ->where('created_at', '>=', $startDate)
                    ->groupBy('type')
                    ->get();
                
                $labels = [];
                $counts = [];
                $colors = [
                    'malware' => '#7b68ee',
                    'intrusion' => '#ff4757',
                    'data_breach' => '#ff9f43',
                    'dos' => '#2ed573',
                    'insider' => '#3498db',
                    'unknown' => '#a5b1c2'
                ];
                
                foreach ($data as $item) {
                    $labels[] = $this->getTypeLabel($item->type);
                    $counts[] = $item->count;
                }
                
                break;
        }
        
        // 提取颜色数组，确保与标签顺序一致
        $colorValues = [];
        foreach ($labels as $index => $label) {
            $key = array_search($label, array_map([$this, 'getTypeLabel'], array_keys($colors)));
            if ($key !== false) {
                $colorValues[] = array_values($colors)[$key];
            } else {
                $colorValues[] = '#a5b1c2'; // 默认颜色
            }
        }
        
        return response()->json([
            'labels' => $labels,
            'data' => $counts,
            'colors' => $colorValues
        ]);
    }
    
    /**
     * 获取防御效果数据
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDefenseEffectiveness()
    {
        // 从量子防御服务获取数据
        $defenseStats = $this->quantumDefenseService->getDefenseStatistics();
        
        return response()->json([
            'defense_efficiency' => $defenseStats['efficiency'],
            'detection_rate' => $defenseStats['detection_rate'],
            'high_severity_response_time' => $defenseStats['high_severity_response_time'],
            'medium_severity_response_time' => $defenseStats['medium_severity_response_time'],
            'actions' => [
                'blocked_attacks' => $defenseStats['blocked_attacks'],
                'malware_cleaned' => $defenseStats['malware_cleaned'],
                'suspicious_logins_blocked' => $defenseStats['suspicious_logins_blocked'],
                'identity_theft_prevented' => $defenseStats['identity_theft_prevented']
            ]
        ]);
    }
    
    /**
     * 获取量子安全评分详情
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSecurityScoreDetails()
    {
        // 从量子AI安全服务获取详细评分
        $scoreDetails = $this->quantumAiSecurityService->getSecurityScoreDetails();
        
        return response()->json($scoreDetails);
    }
    
    /**
     * 获取实时安全事件
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSecurityEvents(Request $request)
    {
        $severity = $request->input('severity');
        $status = $request->input('status');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 5);
        
        $query = SecurityThreat::orderBy('created_at', 'desc');
        
        // 应用过滤
        if ($severity && $severity !== 'all') {
            $query->where('severity', $severity);
        }
        
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }
        
        // 分页
        $events = $query->paginate($perPage);
        
        // 格式化数据
        $formattedEvents = [];
        foreach ($events as $event) {
            $formattedEvents[] = [
                'id' => $event->id,
                'time' => $event->created_at->format('Y-m-d H:i:s'),
                'type' => $this->getTypeLabel($event->type),
                'source' => $event->source,
                'severity' => $event->severity,
                'severity_label' => $this->getSeverityLabel($event->severity),
                'status' => $event->status,
                'status_label' => $this->getStatusLabel($event->status)
            ];
        }
        
        return response()->json([
            'events' => $formattedEvents,
            'pagination' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total()
            ]
        ]);
    }
    
    /**
     * 获取威胁类型标签
     * 
     * @param string $type
     * @return string
     */
    private function getTypeLabel($type)
    {
        $labels = [
            'malware' => '恶意软件',
            'intrusion' => '入侵',
            'data_breach' => '数据泄露',
            'dos' => '拒绝服务',
            'insider' => '内部威胁',
            'unknown' => '未知'
        ];
        
        return $labels[$type] ?? '未知';
    }
    
    /**
     * 获取严重程度标签
     * 
     * @param string $severity
     * @return string
     */
    private function getSeverityLabel($severity)
    {
        $labels = [
            'high' => '高危',
            'medium' => '中危',
            'low' => '低危'
        ];
        
        return $labels[$severity] ?? '未知';
    }
    
    /**
     * 获取状态标签
     * 
     * @param string $status
     * @return string
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'detected' => '已检测',
            'analyzing' => '分析中',
            'responding' => '响应中',
            'contained' => '已控制',
            'resolved' => '已解决',
            'false_positive' => '误报'
        ];
        
        return $labels[$status] ?? '未知';
    }
}
