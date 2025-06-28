<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\Security\SecurityThreat;
use App\Services\Security\QuantumAiSecurityService;
use App\Services\Security\QuantumDefenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * 安全威胁管理控制器
 */
class SecurityThreatController extends Controller
{
    /**
     * 量子AI安全服务
     * 
     * @var QuantumAiSecurityService
     */
    protected $securityService;
    
    /**
     * 量子防御服务
     * 
     * @var QuantumDefenseService
     */
    protected $defenseService;
    
    /**
     * 构造函数
     * 
     * @param QuantumAiSecurityService $securityService
     * @param QuantumDefenseService $defenseService
     */
    public function __construct(QuantumAiSecurityService $securityService, QuantumDefenseService $defenseService)
    {
        $this->securityService = $securityService;
        $this->defenseService = $defenseService;
    }
    
    /**
     * 获取所有安全威胁
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = SecurityThreat::query();
            
            // 应用过滤条件
            if ($request->has('threat_type')) {
                $query->ofType($request->input('threat_type'));
            }
            
            if ($request->has('severity')) {
                $query->ofSeverity($request->input('severity'));
            }
            
            if ($request->has('status')) {
                $query->ofStatus($request->input('status'));
            } else {
                // 默认只显示活跃威胁
                $query->active();
            }
            
            // 应用排序
            $sortField = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $query->orderBy($sortField, $sortDirection);
            
            // 分页
            $perPage = $request->input('per_page', 15);
            $threats = $query->paginate($perPage);
            
            return response()->json([
                'status' => 'success',
                'data' => $threats,
                'message' => '获取安全威胁列表成功'
            ]);
        } catch (\Exception $e) {
            Log::error('获取安全威胁列表失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'status' => 'error',
                'message' => '获取安全威胁列表失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取安全威胁详情
     * 
     * @param int $id 威胁ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $threat = SecurityThreat::findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'data' => $threat,
                'message' => '获取安全威胁详情成功'
            ]);
        } catch (\Exception $e) {
            Log::error('获取安全威胁详情失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'status' => 'error',
                'message' => '获取安全威胁详情失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 创建安全威胁
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'threat_type' => 'required|string',
                'severity' => 'required|string|in:low,medium,high',
                'confidence' => 'nullable|numeric|min:0|max:1',
                'details' => 'nullable|string',
                'source_ip' => 'nullable|string',
                'target' => 'nullable|string',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => '参数验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $threat = SecurityThreat::create([
                'threat_type' => $request->input('threat_type'),
                'severity' => $request->input('severity'),
                'confidence' => $request->input('confidence', 0),
                'details' => $request->input('details'),
                'source_ip' => $request->input('source_ip'),
                'target' => $request->input('target'),
                'status' => SecurityThreat::STATUS_DETECTED,
            ]);
            
            return response()->json([
                'status' => 'success',
                'data' => $threat,
                'message' => '创建安全威胁成功'
            ], 201);
        } catch (\Exception $e) {
            Log::error('创建安全威胁失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'status' => 'error',
                'message' => '创建安全威胁失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 更新安全威胁
     * 
     * @param Request $request
     * @param int $id 威胁ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'threat_type' => 'nullable|string',
                'severity' => 'nullable|string|in:low,medium,high',
                'confidence' => 'nullable|numeric|min:0|max:1',
                'details' => 'nullable|string',
                'source_ip' => 'nullable|string',
                'target' => 'nullable|string',
                'status' => 'nullable|string|in:detected,analyzing,responding,contained,resolved,false_positive',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => '参数验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $threat = SecurityThreat::findOrFail($id);
            
            // 更新威胁信息
            $threat->fill($request->only([
                'threat_type',
                'severity',
                'confidence',
                'details',
                'source_ip',
                'target',
                'status',
            ]));
            
            // 如果状态变更为已解决或误报，设置解决时间
            if ($request->has('status') && in_array($request->input('status'), [SecurityThreat::STATUS_RESOLVED, SecurityThreat::STATUS_FALSE_POSITIVE])) {
                $threat->resolved_at = now();
            }
            
            $threat->save();
            
            return response()->json([
                'status' => 'success',
                'data' => $threat,
                'message' => '更新安全威胁成功'
            ]);
        } catch (\Exception $e) {
            Log::error('更新安全威胁失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'status' => 'error',
                'message' => '更新安全威胁失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 标记威胁为已解决
     * 
     * @param int $id 威胁ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function resolve($id)
    {
        try {
            $threat = SecurityThreat::findOrFail($id);
            
            if ($threat->status === SecurityThreat::STATUS_RESOLVED) {
                return response()->json([
                    'status' => 'success',
                    'message' => '威胁已经是已解决状态'
                ]);
            }
            
            $threat->markAsResolved();
            
            return response()->json([
                'status' => 'success',
                'data' => $threat,
                'message' => '标记威胁为已解决成功'
            ]);
        } catch (\Exception $e) {
            Log::error('标记威胁为已解决失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'status' => 'error',
                'message' => '标记威胁为已解决失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 标记威胁为误报
     * 
     * @param int $id 威胁ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsFalsePositive($id)
    {
        try {
            $threat = SecurityThreat::findOrFail($id);
            
            if ($threat->status === SecurityThreat::STATUS_FALSE_POSITIVE) {
                return response()->json([
                    'status' => 'success',
                    'message' => '威胁已经是误报状态'
                ]);
            }
            
            $threat->markAsFalsePositive();
            
            return response()->json([
                'status' => 'success',
                'data' => $threat,
                'message' => '标记威胁为误报成功'
            ]);
        } catch (\Exception $e) {
            Log::error('标记威胁为误报失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'status' => 'error',
                'message' => '标记威胁为误报失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 响应威胁
     * 
     * @param Request $request
     * @param int $id 威胁ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondToThreat(Request $request, $id)
    {
        try {
            $threat = SecurityThreat::findOrFail($id);
            
            // 如果威胁已解决或是误报，则不能再响应
            if (in_array($threat->status, [SecurityThreat::STATUS_RESOLVED, SecurityThreat::STATUS_FALSE_POSITIVE])) {
                return response()->json([
                    'status' => 'error',
                    'message' => '无法响应已解决或误报的威胁'
                ], 400);
            }
            
            // 更新威胁状态为响应中
            $threat->status = SecurityThreat::STATUS_RESPONDING;
            $threat->save();
            
            // 调用量子防御服务响应威胁
            $threatData = [
                'type' => $threat->threat_type,
                'severity' => $threat->severity,
                'confidence' => $threat->confidence,
                'source_ip' => $threat->source_ip,
                'target' => $threat->target,
                'details' => $threat->details,
            ];
            
            $responseResult = $this->defenseService->respondToThreat($threatData);
            
            // 记录响应操作
            $threat->addResponseAction($responseResult);
            
            // 如果响应成功，更新威胁状态为已控制
            if ($responseResult['status'] === 'success') {
                $threat->status = SecurityThreat::STATUS_CONTAINED;
                $threat->save();
            }
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'threat' => $threat,
                    'response_result' => $responseResult
                ],
                'message' => '响应威胁成功'
            ]);
        } catch (\Exception $e) {
            Log::error('响应威胁失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'status' => 'error',
                'message' => '响应威胁失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取威胁统计信息
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics()
    {
        try {
            // 获取总威胁数
            $totalThreats = SecurityThreat::count();
            
            // 获取活跃威胁数
            $activeThreats = SecurityThreat::active()->count();
            
            // 获取已解决威胁数
            $resolvedThreats = SecurityThreat::resolved()->count();
            
            // 按严重程度统计
            $severityStats = [
                'high' => SecurityThreat::ofSeverity(SecurityThreat::SEVERITY_HIGH)->count(),
                'medium' => SecurityThreat::ofSeverity(SecurityThreat::SEVERITY_MEDIUM)->count(),
                'low' => SecurityThreat::ofSeverity(SecurityThreat::SEVERITY_LOW)->count(),
            ];
            
            // 按威胁类型统计
            $typeStats = [];
            foreach (SecurityThreat::getThreatTypes() as $type => $name) {
                $typeStats[$type] = SecurityThreat::ofType($type)->count();
            }
            
            // 按状态统计
            $statusStats = [];
            foreach (SecurityThreat::getStatusList() as $status => $name) {
                $statusStats[$status] = SecurityThreat::ofStatus($status)->count();
            }
            
            // 最近7天的威胁趋势
            $trendData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $count = SecurityThreat::whereDate('created_at', $date)->count();
                $trendData[] = [
                    'date' => $date,
                    'count' => $count
                ];
            }
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_threats' => $totalThreats,
                    'active_threats' => $activeThreats,
                    'resolved_threats' => $resolvedThreats,
                    'severity_stats' => $severityStats,
                    'type_stats' => $typeStats,
                    'status_stats' => $statusStats,
                    'trend_data' => $trendData,
                    'timestamp' => now()->toDateTimeString()
                ],
                'message' => '获取威胁统计信息成功'
            ]);
        } catch (\Exception $e) {
            Log::error('获取威胁统计信息失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'status' => 'error',
                'message' => '获取威胁统计信息失败: ' . $e->getMessage()
            ], 500);
        }
    }
} 