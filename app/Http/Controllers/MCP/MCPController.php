<?php

namespace App\Http\Controllers\MCP;

use App\Http\Controllers\Controller;
use App\Services\MCP\MCPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MCPController extends Controller
{
    /**
     * MCP服务
     *
     * @var MCPService
     */
    protected $mcpService;

    /**
     * 构造函数
     *
     * @param MCPService $mcpService
     */
    public function __construct(MCPService $mcpService)
    {
        $this->mcpService = $mcpService;
        $this->middleware("auth:api");
        $this->middleware("admin");
    }

    /**
     * 获取系统状态
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSystemStatus()
    {
        try {
            $status = $this->mcpService->getSystemStatus();
            return response()->json([
                "success" => true,
                "data" => $status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取资源使用情况
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getResourceUsage()
    {
        try {
            $resources = $this->mcpService->getResourceUsage();
            return response()->json([
                "success" => true,
                "data" => $resources
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取用户统计数据
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserStats()
    {
        try {
            $stats = $this->mcpService->getUserStats();
            return response()->json([
                "success" => true,
                "data" => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取API使用统计数据
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getApiStats()
    {
        try {
            $stats = $this->mcpService->getApiStats();
            return response()->json([
                "success" => true,
                "data" => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 执行系统维护任务
     *
     * @param Request $request
     * @param string $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function runMaintenanceTask(Request $request, $task)
    {
        try {
            $result = $this->mcpService->runMaintenanceTask($task, $request->all());
            return response()->json([
                "success" => true,
                "data" => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取系统配置
     *
     * @param string|null $configGroup
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSystemConfig($configGroup = null)
    {
        try {
            $config = $this->mcpService->getSystemConfig($configGroup);
            return response()->json([
                "success" => true,
                "data" => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新系统配置
     *
     * @param Request $request
     * @param string $configGroup
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSystemConfig(Request $request, $configGroup)
    {
        try {
            $validator = Validator::make($request->all(), [
                "config_data" => "required|array"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "success" => false,
                    "message" => $validator->errors()->first()
                ], 422);
            }

            $result = $this->mcpService->updateSystemConfig($configGroup, $request->input("config_data"));
            return response()->json([
                "success" => true,
                "data" => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }
}
