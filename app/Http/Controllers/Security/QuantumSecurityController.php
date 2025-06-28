<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Security\QuantumAiSecurityService;
use App\Services\Security\QuantumDefenseService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * 量子安全控制器
 * 提供量子AI安全系统的API接口
 */
class QuantumSecurityController extends Controller
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
     * 检测系统安全威胁
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function detectThreats(Request $request)
    {
        try {
            $parameters = $request->all();
            
            $results = $this->securityService->detectThreats($parameters);
            
            return response()->json([
                'status' => 'success',
                'data' => $results,
                'message' => '安全威胁检测完成'
            ]);
        } catch (\Exception $e) {
            Log::error('安全威胁检测API失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'status' => 'error',
                'message' => '安全威胁检测失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取当前防御级别
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDefenseLevel()
    {
        try {
            $level = $this->defenseService->getCurrentDefenseLevel();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'defense_level' => $level,
                    'description' => $this->getDefenseLevelDescription($level)
                ],
                'message' => '获取防御级别成功'
            ]);
        } catch (\Exception $e) {
            Log::error('获取防御级别API失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'status' => 'error',
                'message' => '获取防御级别失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 设置防御级别
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setDefenseLevel(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'level' => 'required|string|in:passive,active,aggressive'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => '参数验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $level = $request->input('level');
            $result = $this->defenseService->setDefenseLevel($level);
            
            if ($result) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'defense_level' => $level,
                        'description' => $this->getDefenseLevelDescription($level)
                    ],
                    'message' => '防御级别设置成功'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => '防御级别设置失败'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('设置防御级别API失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'status' => 'error',
                'message' => '设置防御级别失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 响应安全威胁
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondToThreat(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'threat' => 'required|array',
                'threat.type' => 'required|string',
                'threat.severity' => 'required|string|in:low,medium,high',
                'auto_adjust_defense_level' => 'boolean'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => '参数验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $threat = $request->input('threat');
            $autoAdjustDefenseLevel = $request->input('auto_adjust_defense_level', false);
            
            // 如果启用自动调整防御级别
            if ($autoAdjustDefenseLevel) {
                $this->adjustDefenseLevelBasedOnThreat($threat);
            }
            
            $results = $this->defenseService->respondToThreat($threat);
            
            return response()->json([
                'status' => 'success',
                'data' => $results,
                'message' => '安全威胁响应完成'
            ]);
        } catch (\Exception $e) {
            Log::error('安全威胁响应API失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'status' => 'error',
                'message' => '安全威胁响应失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取系统安全状态
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSecurityStatus()
    {
        try {
            // 检测系统安全威胁
            $threatDetectionResults = $this->securityService->detectThreats();
            
            // 获取当前防御级别
            $defenseLevel = $this->defenseService->getCurrentDefenseLevel();
            
            // 计算系统安全评分
            $securityScore = $this->calculateSecurityScore($threatDetectionResults);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'security_score' => $securityScore,
                    'threat_level' => $threatDetectionResults['threat_level'] ?? [],
                    'defense_level' => [
                        'level' => $defenseLevel,
                        'description' => $this->getDefenseLevelDescription($defenseLevel)
                    ],
                    'threats_detected' => $threatDetectionResults['threats_detected'] ?? [],
                    'anomalies' => $threatDetectionResults['anomalies'] ?? [],
                    'recommendations' => $threatDetectionResults['recommendations'] ?? [],
                    'timestamp' => now()->toDateTimeString()
                ],
                'message' => '获取系统安全状态成功'
            ]);
        } catch (\Exception $e) {
            Log::error('获取系统安全状态API失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'status' => 'error',
                'message' => '获取系统安全状态失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 根据威胁自动调整防御级别
     * 
     * @param array $threat 威胁信息
     * @return void
     */
    private function adjustDefenseLevelBasedOnThreat(array $threat)
    {
        $severity = $threat['severity'] ?? 'low';
        
        switch ($severity) {
            case 'high':
                $this->defenseService->setDefenseLevel(QuantumDefenseService::DEFENSE_LEVEL_AGGRESSIVE);
                break;
            
            case 'medium':
                $this->defenseService->setDefenseLevel(QuantumDefenseService::DEFENSE_LEVEL_ACTIVE);
                break;
            
            case 'low':
            default:
                $this->defenseService->setDefenseLevel(QuantumDefenseService::DEFENSE_LEVEL_PASSIVE);
                break;
        }
    }
    
    /**
     * 计算系统安全评分
     * 
     * @param array $threatDetectionResults 威胁检测结果
     * @return int 安全评分(0-100)
     */
    private function calculateSecurityScore(array $threatDetectionResults): int
    {
        // 基础分数
        $baseScore = 100;
        
        // 根据威胁级别扣分
        if (isset($threatDetectionResults['threat_level']['level'])) {
            switch ($threatDetectionResults['threat_level']['level']) {
                case 'high':
                    $baseScore -= 50;
                    break;
                
                case 'medium':
                    $baseScore -= 25;
                    break;
                
                case 'low':
                    $baseScore -= 10;
                    break;
            }
        }
        
        // 根据威胁数量扣分
        $threatsCount = count($threatDetectionResults['threats_detected'] ?? []);
        $baseScore -= min(30, $threatsCount * 5);
        
        // 根据异常数量扣分
        $anomaliesCount = count($threatDetectionResults['anomalies'] ?? []);
        $baseScore -= min(20, $anomaliesCount * 3);
        
        // 确保分数在0-100范围内
        return max(0, min(100, $baseScore));
    }
    
    /**
     * 获取防御级别描述
     * 
     * @param string $level 防御级别
     * @return string 防御级别描述
     */
    private function getDefenseLevelDescription(string $level): string
    {
        switch ($level) {
            case QuantumDefenseService::DEFENSE_LEVEL_PASSIVE:
                return '被动防御 - 监控威胁并采取基本防御措施';
            
            case QuantumDefenseService::DEFENSE_LEVEL_ACTIVE:
                return '主动防御 - 主动阻止威胁并采取预防措施';
            
            case QuantumDefenseService::DEFENSE_LEVEL_AGGRESSIVE:
                return '积极反击 - 主动防御并采取反制措施';
            
            default:
                return '未知防御级别';
        }
    }
} 