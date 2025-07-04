<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\Security\ApiControl\ApiInterface;
use App\Models\Security\ApiControl\ApiRiskEvent;
use App\Models\Security\ApiControl\ApiRiskRule;
use App\Models\Security\ApiControl\ApiBlacklist;
use App\Models\Security\ApiControl\ApiAnomalyEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApiMonitoringDashboardController extends Controller
{
    /**
     * 显示API监控仪表板
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // 获取API接口统计
        $apiStats = [
            'total' => ApiInterface::count(),
            'active' => ApiInterface::where('status', 'active')->count(),
            'inactive' => ApiInterface::where('status', 'inactive')->count(),
            'deprecated' => ApiInterface::where('status', 'deprecated')->count(),
        ];
        
        // 获取风险事件统计
        $riskStats = [
            'total' => ApiRiskEvent::count(),
            'pending' => ApiRiskEvent::where('status', 'pending')->count(),
            'processed' => ApiRiskEvent::where('status', 'processed')->count(),
            'ignored' => ApiRiskEvent::where('status', 'ignored')->count(),
            'false_positive' => ApiRiskEvent::where('status', 'false_positive')->count(),
        ];
        
        // 获取风险等级分布
        $riskLevelDistribution = ApiRiskEvent::selectRaw('risk_level, COUNT(*) as count')
            ->groupBy('risk_level')
            ->orderBy('risk_level')
            ->get()
            ->pluck('count', 'risk_level')
            ->toArray();
        
        // 获取黑名单统计
        $blacklistStats = [
            'total' => ApiBlacklist::count(),
            'active' => ApiBlacklist::where('status', 'active')->count(),
            'expired' => ApiBlacklist::where('status', 'expired')->count(),
            'removed' => ApiBlacklist::where('status', 'removed')->count(),
        ];
        
        // 获取黑名单类型分布
        $blacklistTypeDistribution = ApiBlacklist::selectRaw('list_type, COUNT(*) as count')
            ->groupBy('list_type')
            ->orderBy('list_type')
            ->get()
            ->pluck('count', 'list_type')
            ->toArray();
        
        // 获取最近的风险事件
        $recentRiskEvents = ApiRiskEvent::with(['rule', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // 获取最近的异常事件
        $recentAnomalyEvents = ApiAnomalyEvent::with(['config', 'interface'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // 获取过去30天的风险事件趋势
        $riskEventTrend = $this->getRiskEventTrend();
        
        return view('admin.security.api.dashboard.index', compact(
            'apiStats',
            'riskStats',
            'riskLevelDistribution',
            'blacklistStats',
            'blacklistTypeDistribution',
            'recentRiskEvents',
            'recentAnomalyEvents',
            'riskEventTrend'
        ));
    }
    
    /**
     * 显示实时监控页面
     *
     * @return \Illuminate\View\View
     */
    public function realtime()
    {
        // 获取活跃的API接口
        $activeInterfaces = ApiInterface::where('status', 'active')
            ->orderBy('category')
            ->orderBy('name')
            ->get();
        
        // 获取活跃的风控规则
        $activeRules = ApiRiskRule::where('status', 'active')
            ->orderBy('priority')
            ->get();
        
        return view('admin.security.api.dashboard.realtime', compact('activeInterfaces', 'activeRules'));
    }
    
    /**
     * 获取实时监控数据
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function realtimeData(Request $request)
    {
        // 获取最近的风险事件
        $recentRiskEvents = ApiRiskEvent::with(['rule', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'event_type' => $event->event_type,
                    'risk_level' => $event->risk_level,
                    'rule' => $event->rule ? $event->rule->name : '未知',
                    'user' => $event->user ? $event->user->name : '未知',
                    'description' => $event->description,
                    'status' => $event->status,
                    'created_at' => $event->created_at->format('Y-m-d H:i:s'),
                ];
            });
        
        // 获取最近的异常事件
        $recentAnomalyEvents = ApiAnomalyEvent::with(['config', 'interface'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'event_type' => $event->event_type,
                    'severity' => $event->severity,
                    'interface' => $event->interface ? $event->interface->name : '未知',
                    'observed_value' => $event->observed_value,
                    'expected_value' => $event->expected_value,
                    'deviation' => $event->deviation,
                    'status' => $event->status,
                    'created_at' => $event->created_at->format('Y-m-d H:i:s'),
                ];
            });
        
        // 获取API接口调用统计（模拟数据）
        $apiCallStats = $this->getApiCallStats();
        
        // 获取实时系统状态（模拟数据）
        $systemStatus = $this->getSystemStatus();
        
        return response()->json([
            'risk_events' => $recentRiskEvents,
            'anomaly_events' => $recentAnomalyEvents,
            'api_call_stats' => $apiCallStats,
            'system_status' => $systemStatus,
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ]);
    }
    
    /**
     * 获取过去30天的风险事件趋势
     *
     * @return array
     */
    protected function getRiskEventTrend()
    {
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        $trend = ApiRiskEvent::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
        
        // 填充没有数据的日期
        $result = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dateString = $currentDate->format('Y-m-d');
            $result[$dateString] = $trend[$dateString] ?? 0;
            $currentDate->addDay();
        }
        
        return $result;
    }
    
    /**
     * 获取API接口调用统计（模拟数据）
     *
     * @return array
     */
    protected function getApiCallStats()
    {
        // 这里是模拟数据，实际应用中应从监控系统获取
        $interfaces = ApiInterface::where('status', 'active')
            ->orderBy('category')
            ->orderBy('name')
            ->limit(5)
            ->get();
        
        $stats = [];
        foreach ($interfaces as $interface) {
            $stats[] = [
                'interface_id' => $interface->id,
                'name' => $interface->name,
                'path' => $interface->path,
                'method' => $interface->method,
                'calls_per_minute' => rand(1, 100),
                'avg_response_time' => rand(10, 500),
                'error_rate' => rand(0, 10) / 100,
                'status' => rand(0, 10) > 8 ? 'warning' : 'normal',
            ];
        }
        
        return $stats;
    }
    
    /**
     * 获取实时系统状态（模拟数据）
     *
     * @return array
     */
    protected function getSystemStatus()
    {
        // 这里是模拟数据，实际应用中应从监控系统获取
        return [
            'cpu_usage' => rand(10, 90),
            'memory_usage' => rand(20, 80),
            'disk_usage' => rand(30, 70),
            'network_in' => rand(100, 1000) . ' KB/s',
            'network_out' => rand(100, 1000) . ' KB/s',
            'active_connections' => rand(10, 1000),
            'requests_per_second' => rand(10, 500),
            'status' => rand(0, 10) > 8 ? 'warning' : 'normal',
        ];
    }
}
