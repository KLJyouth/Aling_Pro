<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Services\Security\QuarantineService;
use App\Models\Security\SecurityQuarantine;
use App\Models\Security\SecurityIpBan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * 隔离区控制器
 * 
 * 管理异常请求和文件的隔离区
 */
class QuarantineController extends Controller
{
    protected $quarantineService;

    /**
     * 构造函数
     *
     * @param QuarantineService $quarantineService
     */
    public function __construct(QuarantineService $quarantineService)
    {
        $this->quarantineService = $quarantineService;
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * 显示隔离区主页
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取过滤条件
        $filters = [
            'type' => $request->input('type'),
            'risk_level' => $request->input('risk_level'),
            'category' => $request->input('category'),
            'status' => $request->input('status'),
            'source' => $request->input('source'),
        ];
        
        // 过滤空值
        $filters = array_filter($filters);
        
        // 获取隔离记录
        $quarantineItems = $this->quarantineService->getQuarantineItems($filters);
        
        // 获取统计数据
        $stats = $this->getQuarantineStats();
        
        return view('security.quarantine.index', [
            'quarantineItems' => $quarantineItems,
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }

    /**
     * 显示IP封禁列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function ipBans(Request $request)
    {
        // 获取过滤条件
        $filters = [
            'status' => $request->input('status'),
            'ip_address' => $request->input('ip_address'),
        ];
        
        // 过滤空值
        $filters = array_filter($filters);
        
        // 获取IP封禁记录
        $ipBans = $this->quarantineService->getIpBans($filters);
        
        return view('security.quarantine.ip-bans', [
            'ipBans' => $ipBans,
            'filters' => $filters,
        ]);
    }

    /**
     * 显示隔离项详情
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $quarantineItem = SecurityQuarantine::findOrFail($id);
        
        // 获取关联的IP封禁记录
        $ipBans = $quarantineItem->ipBans;
        
        return view('security.quarantine.show', [
            'item' => $quarantineItem,
            'ipBans' => $ipBans,
        ]);
    }

    /**
     * 更新隔离项状态
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $quarantineItem = SecurityQuarantine::findOrFail($id);
        
        // 验证请求
        $validated = $request->validate([
            'status' => 'required|in:pending,analyzing,quarantined,resolved,false_positive',
            'admin_notes' => 'nullable|string|max:1000',
        ]);
        
        // 更新状态
        $quarantineItem->status = $validated['status'];
        $quarantineItem->admin_notes = $validated['admin_notes'];
        $quarantineItem->reviewed_by = Auth::id();
        
        // 如果状态为已解决或误报，设置解决时间
        if (in_array($validated['status'], ['resolved', 'false_positive'])) {
            $quarantineItem->resolved_at = now();
        }
        
        $quarantineItem->save();
        
        Log::info("Quarantine item {$id} status updated to {$validated['status']} by admin " . Auth::id());
        
        return redirect()->route('security.quarantine.show', $id)
            ->with('success', '隔离项状态已更新');
    }

    /**
     * 封禁IP
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function banIp(Request $request)
    {
        // 验证请求
        $validated = $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'required|string|max:255',
            'quarantine_id' => 'nullable|exists:security_quarantines,id',
            'banned_until' => 'nullable|date',
        ]);
        
        // 检查IP是否已被封禁
        if ($this->quarantineService->isIpBanned($validated['ip_address'])) {
            return back()->with('error', 'IP地址已被封禁');
        }
        
        // 封禁IP
        $ipBan = $this->quarantineService->banIp(
            $validated['ip_address'],
            $validated['reason'],
            $validated['quarantine_id'],
            $validated['banned_until'],
            Auth::id()
        );
        
        Log::warning("IP {$validated['ip_address']} banned by admin " . Auth::id() . " for reason: {$validated['reason']}");
        
        if ($validated['quarantine_id']) {
            return redirect()->route('security.quarantine.show', $validated['quarantine_id'])
                ->with('success', "IP {$validated['ip_address']} 已被封禁");
        }
        
        return redirect()->route('security.quarantine.ip-bans')
            ->with('success', "IP {$validated['ip_address']} 已被封禁");
    }

    /**
     * 撤销IP封禁
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function revokeIpBan(Request $request, $id)
    {
        $ipBan = SecurityIpBan::findOrFail($id);
        
        // 验证请求
        $validated = $request->validate([
            'revoke_reason' => 'required|string|max:255',
        ]);
        
        // 撤销封禁
        $ipBan->revoke(Auth::id(), $validated['revoke_reason']);
        
        Log::info("IP ban {$id} for {$ipBan->ip_address} revoked by admin " . Auth::id() . " for reason: {$validated['revoke_reason']}");
        
        return redirect()->route('security.quarantine.ip-bans')
            ->with('success', "IP {$ipBan->ip_address} 的封禁已撤销");
    }

    /**
     * 获取隔离区统计数据
     *
     * @return array
     */
    private function getQuarantineStats()
    {
        return [
            'total' => SecurityQuarantine::count(),
            'pending' => SecurityQuarantine::where('status', 'pending')->count(),
            'analyzing' => SecurityQuarantine::where('status', 'analyzing')->count(),
            'quarantined' => SecurityQuarantine::where('status', 'quarantined')->count(),
            'resolved' => SecurityQuarantine::where('status', 'resolved')->count(),
            'false_positive' => SecurityQuarantine::where('status', 'false_positive')->count(),
            'high_risk' => SecurityQuarantine::where('risk_level', 'high')->count(),
            'medium_risk' => SecurityQuarantine::where('risk_level', 'medium')->count(),
            'low_risk' => SecurityQuarantine::where('risk_level', 'low')->count(),
            'requests' => SecurityQuarantine::where('type', 'request')->count(),
            'files' => SecurityQuarantine::where('type', 'file')->count(),
            'active_ip_bans' => SecurityIpBan::where('status', 'active')->count(),
        ];
    }
    
    /**
     * API: 获取隔离项列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuarantineItems(Request $request)
    {
        // 获取过滤条件
        $filters = [
            'type' => $request->input('type'),
            'risk_level' => $request->input('risk_level'),
            'category' => $request->input('category'),
            'status' => $request->input('status'),
            'source' => $request->input('source'),
        ];
        
        // 过滤空值
        $filters = array_filter($filters);
        
        // 获取隔离记录
        $perPage = $request->input('per_page', 15);
        $quarantineItems = $this->quarantineService->getQuarantineItems($filters, $perPage);
        
        return response()->json([
            'success' => true,
            'data' => $quarantineItems,
        ]);
    }
    
    /**
     * API: 获取单个隔离项详情
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuarantineItem($id)
    {
        try {
            $quarantineItem = SecurityQuarantine::with('reviewer', 'ipBans')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $quarantineItem,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '隔离项不存在',
            ], 404);
        }
    }
    
    /**
     * API: 更新隔离项状态
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiUpdateStatus(Request $request, $id)
    {
        try {
            $quarantineItem = SecurityQuarantine::findOrFail($id);
            
            // 验证请求
            $validated = $request->validate([
                'status' => 'required|in:pending,analyzing,quarantined,resolved,false_positive',
                'admin_notes' => 'nullable|string|max:1000',
            ]);
            
            // 更新状态
            $quarantineItem->status = $validated['status'];
            $quarantineItem->admin_notes = $validated['admin_notes'];
            $quarantineItem->reviewed_by = Auth::id();
            
            // 如果状态为已解决或误报，设置解决时间
            if (in_array($validated['status'], ['resolved', 'false_positive'])) {
                $quarantineItem->resolved_at = now();
            }
            
            $quarantineItem->save();
            
            Log::info("Quarantine item {$id} status updated to {$validated['status']} by admin " . Auth::id() . " via API");
            
            return response()->json([
                'success' => true,
                'message' => '隔离项状态已更新',
                'data' => $quarantineItem,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '更新失败: ' . $e->getMessage(),
            ], 400);
        }
    }
    
    /**
     * API: 获取IP封禁列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getIpBans(Request $request)
    {
        // 获取过滤条件
        $filters = [
            'status' => $request->input('status'),
            'ip_address' => $request->input('ip_address'),
        ];
        
        // 过滤空值
        $filters = array_filter($filters);
        
        // 获取IP封禁记录
        $perPage = $request->input('per_page', 15);
        $ipBans = $this->quarantineService->getIpBans($filters, $perPage);
        
        return response()->json([
            'success' => true,
            'data' => $ipBans,
        ]);
    }
    
    /**
     * API: 封禁IP
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiBanIp(Request $request)
    {
        try {
            // 验证请求
            $validated = $request->validate([
                'ip_address' => 'required|ip',
                'reason' => 'required|string|max:255',
                'quarantine_id' => 'nullable|exists:security_quarantines,id',
                'banned_until' => 'nullable|date',
            ]);
            
            // 检查IP是否已被封禁
            if ($this->quarantineService->isIpBanned($validated['ip_address'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'IP地址已被封禁',
                ], 400);
            }
            
            // 封禁IP
            $ipBan = $this->quarantineService->banIp(
                $validated['ip_address'],
                $validated['reason'],
                $validated['quarantine_id'],
                $validated['banned_until'],
                Auth::id()
            );
            
            Log::warning("IP {$validated['ip_address']} banned by admin " . Auth::id() . " for reason: {$validated['reason']} via API");
            
            return response()->json([
                'success' => true,
                'message' => "IP {$validated['ip_address']} 已被封禁",
                'data' => $ipBan,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '封禁失败: ' . $e->getMessage(),
            ], 400);
        }
    }
    
    /**
     * API: 撤销IP封禁
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiRevokeIpBan(Request $request, $id)
    {
        try {
            $ipBan = SecurityIpBan::findOrFail($id);
            
            // 验证请求
            $validated = $request->validate([
                'revoke_reason' => 'required|string|max:255',
            ]);
            
            // 撤销封禁
            $ipBan->revoke(Auth::id(), $validated['revoke_reason']);
            
            Log::info("IP ban {$id} for {$ipBan->ip_address} revoked by admin " . Auth::id() . " for reason: {$validated['revoke_reason']} via API");
            
            return response()->json([
                'success' => true,
                'message' => "IP {$ipBan->ip_address} 的封禁已撤销",
                'data' => $ipBan,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '撤销失败: ' . $e->getMessage(),
            ], 400);
        }
    }
    
    /**
     * API: 获取隔离区统计数据
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuarantineStatistics()
    {
        $stats = $this->getQuarantineStats();
        
        // 添加时间趋势统计
        $stats['trends'] = [
            'last_7_days' => $this->getTrendData(7),
            'last_30_days' => $this->getTrendData(30),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
    
    /**
     * 获取趋势数据
     *
     * @param int $days 天数
     * @return array
     */
    private function getTrendData($days)
    {
        $startDate = now()->subDays($days)->startOfDay();
        $endDate = now()->endOfDay();
        
        // 获取每天的隔离项数量
        $dailyItems = SecurityQuarantine::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
        
        // 获取每天的高风险隔离项数量
        $dailyHighRiskItems = SecurityQuarantine::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('risk_level', 'high')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
        
        // 填充日期范围内的所有日期
        $result = [];
        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($days - $i - 1)->format('Y-m-d');
            $result[] = [
                'date' => $date,
                'total' => $dailyItems[$date] ?? 0,
                'high_risk' => $dailyHighRiskItems[$date] ?? 0,
            ];
        }
        
        return $result;
    }
} 