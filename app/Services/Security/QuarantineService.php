<?php

namespace App\Services\Security;

use App\Models\Security\SecurityQuarantine;
use App\Models\Security\SecurityIpBan;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

/**
 * 隔离区服务
 * 
 * 用于处理异常请求和文件的检测、分析和隔离
 */
class QuarantineService
{
    protected $aiSecurityService;
    
    /**
     * 构造函数
     *
     * @param QuantumAiSecurityService $aiSecurityService
     */
    public function __construct(QuantumAiSecurityService $aiSecurityService)
    {
        $this->aiSecurityService = $aiSecurityService;
    }
    
    /**
     * 检测并处理可疑请求
     *
     * @param Request $request
     * @return bool 是否为异常请求
     */
    public function detectSuspiciousRequest(Request $request)
    {
        // 获取请求信息
        $ip = $request->ip();
        $method = $request->method();
        $url = $request->fullUrl();
        $userAgent = $request->header('User-Agent');
        $payload = $request->all();
        
        // 检查IP是否已被封禁
        if ($this->isIpBanned($ip)) {
            Log::warning("Blocked request from banned IP: {$ip}");
            return true;
        }
        
        // 构建请求详情
        $details = [
            'method' => $method,
            'url' => $url,
            'user_agent' => $userAgent,
            'headers' => $request->headers->all(),
            'payload' => $payload,
            'cookies' => $request->cookies->all(),
        ];
        
        // 使用AI服务分析请求
        $analysis = $this->aiSecurityService->analyzeRequest($details);
        
        // 如果AI判定为异常请求
        if ($analysis['is_suspicious']) {
            // 创建隔离记录
            $quarantine = SecurityQuarantine::create([
                'type' => 'request',
                'source' => $ip,
                'content_hash' => md5(json_encode($details)),
                'risk_level' => $analysis['risk_level'],
                'category' => $analysis['category'],
                'details' => $details,
                'status' => 'analyzing',
                'ai_analysis' => $analysis,
            ]);
            
            // 如果是高风险请求，自动封禁IP
            if ($analysis['risk_level'] === 'high') {
                $this->banIp($ip, "高风险异常请求: {$analysis['category']}", $quarantine->id);
                Log::alert("High risk request detected and IP banned: {$ip}");
            }
            
            Log::warning("Suspicious request quarantined: {$ip}, Risk: {$analysis['risk_level']}, Category: {$analysis['category']}");
            return true;
        }
        
        return false;
    }
    
    /**
     * 检测并处理可疑文件
     *
     * @param string $filePath 文件路径
     * @param string $originalName 原始文件名
     * @return array 分析结果
     */
    public function detectSuspiciousFile($filePath, $originalName)
    {
        // 文件不存在
        if (!file_exists($filePath)) {
            return [
                'success' => false,
                'message' => '文件不存在'
            ];
        }
        
        // 获取文件内容和哈希
        $content = file_get_contents($filePath);
        $contentHash = md5($content);
        
        // 获取文件信息
        $fileInfo = [
            'original_name' => $originalName,
            'path' => $filePath,
            'size' => filesize($filePath),
            'mime_type' => mime_content_type($filePath),
            'extension' => pathinfo($filePath, PATHINFO_EXTENSION),
        ];
        
        // 使用AI服务分析文件
        $analysis = $this->aiSecurityService->analyzeFile($filePath, $fileInfo);
        
        // 如果AI判定为可疑文件
        if ($analysis['is_suspicious']) {
            // 创建隔离记录
            $quarantine = SecurityQuarantine::create([
                'type' => 'file',
                'source' => $filePath,
                'content_hash' => $contentHash,
                'risk_level' => $analysis['risk_level'],
                'category' => $analysis['category'],
                'details' => $fileInfo,
                'status' => 'quarantined',
                'ai_analysis' => $analysis,
            ]);
            
            // 移动文件到隔离区
            $quarantinePath = $this->moveToQuarantine($filePath, $contentHash);
            
            Log::warning("Suspicious file quarantined: {$originalName}, Risk: {$analysis['risk_level']}, Category: {$analysis['category']}");
            
            return [
                'success' => true,
                'is_suspicious' => true,
                'quarantine_id' => $quarantine->id,
                'risk_level' => $analysis['risk_level'],
                'category' => $analysis['category'],
                'message' => "文件已被隔离: {$analysis['category']} ({$analysis['risk_level']})"
            ];
        }
        
        return [
            'success' => true,
            'is_suspicious' => false,
            'message' => '文件安全'
        ];
    }
    
    /**
     * 移动文件到隔离区
     *
     * @param string $filePath 原文件路径
     * @param string $contentHash 内容哈希
     * @return string 隔离区中的路径
     */
    protected function moveToQuarantine($filePath, $contentHash)
    {
        // 创建隔离区目录
        $quarantineDir = storage_path('quarantine');
        if (!file_exists($quarantineDir)) {
            mkdir($quarantineDir, 0755, true);
        }
        
        // 生成隔离区中的文件路径
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $quarantinePath = $quarantineDir . '/' . $contentHash . '.' . $extension;
        
        // 复制文件到隔离区
        copy($filePath, $quarantinePath);
        
        return $quarantinePath;
    }
    
    /**
     * 检查IP是否已被封禁
     *
     * @param string $ip
     * @return bool
     */
    public function isIpBanned($ip)
    {
        $ban = SecurityIpBan::where('ip_address', $ip)
            ->where('status', 'active')
            ->first();
            
        if (!$ban) {
            return false;
        }
        
        return $ban->isActive();
    }
    
    /**
     * 封禁IP
     *
     * @param string $ip IP地址
     * @param string $reason 封禁原因
     * @param int|null $quarantineId 关联的隔离记录ID
     * @param \DateTime|null $bannedUntil 封禁截止时间 (null表示永久封禁)
     * @param int|null $bannedBy 操作人ID
     * @return SecurityIpBan
     */
    public function banIp($ip, $reason, $quarantineId = null, $bannedUntil = null, $bannedBy = null)
    {
        return SecurityIpBan::create([
            'ip_address' => $ip,
            'reason' => $reason,
            'quarantine_id' => $quarantineId,
            'banned_until' => $bannedUntil,
            'banned_by' => $bannedBy,
            'status' => 'active',
            'details' => [
                'auto_banned' => $bannedBy === null,
                'banned_at' => now()->toDateTimeString(),
            ],
        ]);
    }
    
    /**
     * 获取隔离记录
     *
     * @param array $filters 过滤条件
     * @param int $perPage 每页记录数
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getQuarantineItems($filters = [], $perPage = 15)
    {
        $query = SecurityQuarantine::query();
        
        // 应用过滤条件
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        if (isset($filters['risk_level'])) {
            $query->where('risk_level', $filters['risk_level']);
        }
        
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['source'])) {
            $query->where('source', 'like', "%{$filters['source']}%");
        }
        
        // 排序
        $query->orderBy('created_at', 'desc');
        
        return $query->paginate($perPage);
    }
    
    /**
     * 获取IP封禁记录
     *
     * @param array $filters 过滤条件
     * @param int $perPage 每页记录数
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getIpBans($filters = [], $perPage = 15)
    {
        $query = SecurityIpBan::query();
        
        // 应用过滤条件
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['ip_address'])) {
            $query->where('ip_address', 'like', "%{$filters['ip_address']}%");
        }
        
        // 排序
        $query->orderBy('created_at', 'desc');
        
        return $query->paginate($perPage);
    }
} 