<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\Monitoring\Alert;
use App\Services\Monitoring\AlertService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AlertController extends Controller
{
    /**
     * 告警服务
     * 
     * @var AlertService
     */
    protected $alertService;

    /**
     * 构造函数
     * 
     * @param AlertService $alertService 告警服务
     */
    public function __construct(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    /**
     * 获取活跃告警列表
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getActiveAlerts(Request $request): JsonResponse
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);
            
            $filters = [];
            if ($request->has('level')) {
                $filters['level'] = $request->input('level');
            }
            
            if ($request->has('type')) {
                $filters['type'] = $request->input('type');
            }
            
            if ($request->has('source')) {
                $filters['source'] = $request->input('source');
            }
            
            $alerts = $this->alertService->getActiveAlerts($page, $perPage, $filters);
            
            return response()->json([
                'success' => true,
                'data' => $alerts
            ]);
        } catch (\Exception $e) {
            Log::error('获取活跃告警列表失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '获取活跃告警列表失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取告警详情
     * 
     * @param Request $request
     * @param int $id 告警ID
     * @return JsonResponse
     */
    public function getAlertDetail(Request $request, int $id): JsonResponse
    {
        try {
            $alert = Alert::with(['acknowledgedBy', 'resolvedBy'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $alert
            ]);
        } catch (\Exception $e) {
            Log::error('获取告警详情失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '获取告警详情失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 确认告警
     * 
     * @param Request $request
     * @param int $id 告警ID
     * @return JsonResponse
     */
    public function acknowledgeAlert(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'comment' => 'nullable|string|max:1000'
            ]);
            
            $userId = Auth::id();
            $comment = $request->input('comment', '');
            
            $result = $this->alertService->acknowledgeAlert($id, $userId, $comment);
            
            if (!$result['success']) {
                return response()->json($result, 400);
            }
            
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('确认告警失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '确认告警失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 解决告警
     * 
     * @param Request $request
     * @param int $id 告警ID
     * @return JsonResponse
     */
    public function resolveAlert(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'resolution' => 'nullable|string|max:1000'
            ]);
            
            $userId = Auth::id();
            $resolution = $request->input('resolution', '');
            
            $result = $this->alertService->resolveAlert($id, $userId, $resolution);
            
            if (!$result['success']) {
                return response()->json($result, 400);
            }
            
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('解决告警失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '解决告警失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 手动触发系统检查并生成告警
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function triggerSystemCheck(Request $request): JsonResponse
    {
        try {
            $alerts = $this->alertService->checkAndGenerateAlerts();
            
            return response()->json([
                'success' => true,
                'message' => '系统检查已触发',
                'data' => [
                    'alerts_count' => count($alerts)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('触发系统检查失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '触发系统检查失败: ' . $e->getMessage()
            ], 500);
        }
    }
} 