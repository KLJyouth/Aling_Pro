<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Security\QuantumAiSecurityService;
use App\Models\Security\SecurityThreat;
use Illuminate\Support\Facades\Log;

/**
 * 检查安全威胁命令
 */
class CheckSecurityThreats extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $signature = 'security:check-threats';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '检查系统安全威胁并生成威胁记录';

    /**
     * 量子AI安全服务
     *
     * @var QuantumAiSecurityService
     */
    protected $securityService;

    /**
     * 创建命令实例
     *
     * @param QuantumAiSecurityService $securityService
     * @return void
     */
    public function __construct(QuantumAiSecurityService $securityService)
    {
        parent::__construct();
        $this->securityService = $securityService;
    }

    /**
     * 执行命令
     *
     * @return int
     */
    public function handle()
    {
        $this->info('开始检查系统安全威胁...');
        
        try {
            // 检测系统安全威胁
            $detectionResults = $this->securityService->detectThreats();
            
            // 处理检测结果
            $this->processDetectionResults($detectionResults);
            
            $this->info('安全威胁检查完成');
            return 0;
        } catch (\Exception $e) {
            $this->error('安全威胁检查失败: ' . $e->getMessage());
            Log::error('安全威胁检查失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return 1;
        }
    }
    
    /**
     * 处理检测结果
     *
     * @param array $detectionResults 检测结果
     * @return void
     */
    protected function processDetectionResults(array $detectionResults)
    {
        // 检查是否有错误
        if (isset($detectionResults['error'])) {
            $this->error('检测过程中出现错误: ' . $detectionResults['message']);
            return;
        }
        
        // 获取检测到的威胁
        $threats = $detectionResults['threats_detected'] ?? [];
        $anomalies = $detectionResults['anomalies'] ?? [];
        
        $this->info('检测到 ' . count($threats) . ' 个威胁和 ' . count($anomalies) . ' 个异常');
        
        // 处理威胁
        foreach ($threats as $threat) {
            $this->processThreat($threat);
        }
        
        // 处理异常
        foreach ($anomalies as $anomaly) {
            $this->processAnomaly($anomaly);
        }
    }
    
    /**
     * 处理威胁
     *
     * @param array $threat 威胁信息
     * @return void
     */
    protected function processThreat(array $threat)
    {
        $this->info('处理威胁: ' . ($threat['type'] ?? 'unknown') . ' - ' . ($threat['severity'] ?? 'low'));
        
        // 检查是否已存在相同的威胁
        $existingThreat = SecurityThreat::where('threat_type', $threat['type'] ?? 'unknown')
            ->where('source_ip', $threat['source_ip'] ?? null)
            ->where('target', $threat['target'] ?? null)
            ->whereNotIn('status', [SecurityThreat::STATUS_RESOLVED, SecurityThreat::STATUS_FALSE_POSITIVE])
            ->first();
        
        if ($existingThreat) {
            $this->info('已存在相同的威胁记录，更新置信度和详情');
            
            // 更新现有威胁
            $existingThreat->confidence = max($existingThreat->confidence, $threat['confidence'] ?? 0);
            $existingThreat->details = $threat['details'] ?? $existingThreat->details;
            $existingThreat->save();
            
            return;
        }
        
        // 创建新的威胁记录
        SecurityThreat::create([
            'threat_type' => $threat['type'] ?? 'unknown',
            'severity' => $threat['severity'] ?? 'low',
            'confidence' => $threat['confidence'] ?? 0,
            'details' => $threat['details'] ?? null,
            'source_ip' => $threat['source_ip'] ?? null,
            'target' => $threat['target'] ?? null,
            'status' => SecurityThreat::STATUS_DETECTED,
        ]);
        
        $this->info('已创建新的威胁记录');
    }
    
    /**
     * 处理异常
     *
     * @param array $anomaly 异常信息
     * @return void
     */
    protected function processAnomaly(array $anomaly)
    {
        $this->info('处理异常: ' . ($anomaly['type'] ?? 'unknown') . ' - ' . ($anomaly['severity'] ?? 'low'));
        
        // 检查是否已存在相同的异常
        $existingAnomaly = SecurityThreat::where('threat_type', 'anomaly_' . ($anomaly['type'] ?? 'unknown'))
            ->where('source_ip', $anomaly['source_ip'] ?? null)
            ->where('target', $anomaly['target'] ?? null)
            ->whereNotIn('status', [SecurityThreat::STATUS_RESOLVED, SecurityThreat::STATUS_FALSE_POSITIVE])
            ->first();
        
        if ($existingAnomaly) {
            $this->info('已存在相同的异常记录，更新置信度和详情');
            
            // 更新现有异常
            $existingAnomaly->confidence = max($existingAnomaly->confidence, $anomaly['score'] ?? 0);
            $existingAnomaly->details = $anomaly['details'] ?? $existingAnomaly->details;
            $existingAnomaly->save();
            
            return;
        }
        
        // 创建新的异常记录
        SecurityThreat::create([
            'threat_type' => 'anomaly_' . ($anomaly['type'] ?? 'unknown'),
            'severity' => $anomaly['severity'] ?? 'low',
            'confidence' => $anomaly['score'] ?? 0,
            'details' => $anomaly['details'] ?? null,
            'source_ip' => $anomaly['source_ip'] ?? null,
            'target' => $anomaly['target'] ?? null,
            'status' => SecurityThreat::STATUS_DETECTED,
        ]);
        
        $this->info('已创建新的异常记录');
    }
} 