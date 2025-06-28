<?php

namespace App\Services\Monitoring;

use App\Models\Monitoring\Alert;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SystemAlertNotification;

/**
 * 告警服务
 * 提供系统监控告警功能
 */
class AlertService
{
    /**
     * 系统监控服务
     * 
     * @var SystemMonitorService
     */
    protected $systemMonitorService;
    
    /**
     * 构造函数
     * 
     * @param SystemMonitorService $systemMonitorService 系统监控服务
     */
    public function __construct(SystemMonitorService $systemMonitorService)
    {
        $this->systemMonitorService = $systemMonitorService;
    }
    
    /**
     * 检查系统并生成告警
     * 
     * @return array 生成的告警列表
     */
    public function checkAndGenerateAlerts(): array
    {
        try {
            $alerts = [];
            
            // 检查系统性能
            $performanceAlerts = $this->checkPerformanceMetrics();
            if (!empty($performanceAlerts)) {
                $alerts = array_merge($alerts, $performanceAlerts);
            }
            
            // 检查系统健康状态
            $healthAlerts = $this->checkHealthStatus();
            if (!empty($healthAlerts)) {
                $alerts = array_merge($alerts, $healthAlerts);
            }
            
            // 检查应用性能
            $applicationAlerts = $this->checkApplicationMetrics();
            if (!empty($applicationAlerts)) {
                $alerts = array_merge($alerts, $applicationAlerts);
            }
            
            // 保存告警到数据库
            if (!empty($alerts)) {
                $this->saveAlerts($alerts);
                
                // 发送告警通知
                $this->sendAlertNotifications($alerts);
            }
            
            return $alerts;
        } catch (\Exception $e) {
            Log::error('检查系统并生成告警失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'error' => '检查系统并生成告警失败',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 检查系统性能指标并生成告警
     * 
     * @return array 生成的告警列表
     */
    private function checkPerformanceMetrics(): array
    {
        $alerts = [];
        
        try {
            $metrics = $this->systemMonitorService->getPerformanceMetrics();
            
            // 检查CPU使用率
            if (isset($metrics['cpu']['usage_percent']) && $metrics['cpu']['usage_percent'] > 90) {
                $alerts[] = [
                    'type' => 'performance',
                    'level' => 'critical',
                    'source' => 'cpu',
                    'message' => 'CPU使用率过高: ' . $metrics['cpu']['usage_percent'] . '%',
                    'details' => [
                        'usage_percent' => $metrics['cpu']['usage_percent'],
                        'cores' => $metrics['cpu']['cores'] ?? null,
                        'threshold' => 90
                    ],
                    'created_at' => now()
                ];
            } elseif (isset($metrics['cpu']['usage_percent']) && $metrics['cpu']['usage_percent'] > 80) {
                $alerts[] = [
                    'type' => 'performance',
                    'level' => 'warning',
                    'source' => 'cpu',
                    'message' => 'CPU使用率较高: ' . $metrics['cpu']['usage_percent'] . '%',
                    'details' => [
                        'usage_percent' => $metrics['cpu']['usage_percent'],
                        'cores' => $metrics['cpu']['cores'] ?? null,
                        'threshold' => 80
                    ],
                    'created_at' => now()
                ];
            }
            
            // 检查内存使用率
            if (isset($metrics['memory']['usage_percent']) && $metrics['memory']['usage_percent'] > 90) {
                $alerts[] = [
                    'type' => 'performance',
                    'level' => 'critical',
                    'source' => 'memory',
                    'message' => '内存使用率过高: ' . $metrics['memory']['usage_percent'] . '%',
                    'details' => [
                        'usage_percent' => $metrics['memory']['usage_percent'],
                        'total' => $metrics['memory']['total'] ?? null,
                        'used' => $metrics['memory']['used'] ?? null,
                        'threshold' => 90
                    ],
                    'created_at' => now()
                ];
            } elseif (isset($metrics['memory']['usage_percent']) && $metrics['memory']['usage_percent'] > 80) {
                $alerts[] = [
                    'type' => 'performance',
                    'level' => 'warning',
                    'source' => 'memory',
                    'message' => '内存使用率较高: ' . $metrics['memory']['usage_percent'] . '%',
                    'details' => [
                        'usage_percent' => $metrics['memory']['usage_percent'],
                        'total' => $metrics['memory']['total'] ?? null,
                        'used' => $metrics['memory']['used'] ?? null,
                        'threshold' => 80
                    ],
                    'created_at' => now()
                ];
            }
            
            // 检查磁盘使用率
            if (isset($metrics['disk']['root']['usage_percent']) && $metrics['disk']['root']['usage_percent'] > 90) {
                $alerts[] = [
                    'type' => 'performance',
                    'level' => 'critical',
                    'source' => 'disk',
                    'message' => '磁盘使用率过高: ' . $metrics['disk']['root']['usage_percent'] . '%',
                    'details' => [
                        'path' => $metrics['disk']['root']['path'] ?? 'root',
                        'usage_percent' => $metrics['disk']['root']['usage_percent'],
                        'total' => $metrics['disk']['root']['total'] ?? null,
                        'used' => $metrics['disk']['root']['used'] ?? null,
                        'threshold' => 90
                    ],
                    'created_at' => now()
                ];
            } elseif (isset($metrics['disk']['root']['usage_percent']) && $metrics['disk']['root']['usage_percent'] > 80) {
                $alerts[] = [
                    'type' => 'performance',
                    'level' => 'warning',
                    'source' => 'disk',
                    'message' => '磁盘使用率较高: ' . $metrics['disk']['root']['usage_percent'] . '%',
                    'details' => [
                        'path' => $metrics['disk']['root']['path'] ?? 'root',
                        'usage_percent' => $metrics['disk']['root']['usage_percent'],
                        'total' => $metrics['disk']['root']['total'] ?? null,
                        'used' => $metrics['disk']['root']['used'] ?? null,
                        'threshold' => 80
                    ],
                    'created_at' => now()
                ];
            }
        } catch (\Exception $e) {
            Log::error('检查系统性能指标失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
        
        return $alerts;
    }
    
    /**
     * 检查系统健康状态并生成告警
     * 
     * @return array 生成的告警列表
     */
    private function checkHealthStatus(): array
    {
        $alerts = [];
        
        try {
            $status = $this->systemMonitorService->getHealthStatus();
            
            // 检查整体状态
            if (isset($status['overall']['status']) && $status['overall']['status'] === 'critical') {
                $alerts[] = [
                    'type' => 'health',
                    'level' => 'critical',
                    'source' => 'system',
                    'message' => '系统健康状态严重异常',
                    'details' => [
                        'status' => $status['overall']['status'],
                        'healthy_components' => $status['overall']['healthy_components'] ?? 0,
                        'total_components' => $status['overall']['total_components'] ?? 0
                    ],
                    'created_at' => now()
                ];
            } elseif (isset($status['overall']['status']) && $status['overall']['status'] === 'degraded') {
                $alerts[] = [
                    'type' => 'health',
                    'level' => 'warning',
                    'source' => 'system',
                    'message' => '系统健康状态异常',
                    'details' => [
                        'status' => $status['overall']['status'],
                        'healthy_components' => $status['overall']['healthy_components'] ?? 0,
                        'total_components' => $status['overall']['total_components'] ?? 0
                    ],
                    'created_at' => now()
                ];
            }
            
            // 检查数据库状态
            if (isset($status['components']['database']['status']) && $status['components']['database']['status'] === 'critical') {
                $alerts[] = [
                    'type' => 'health',
                    'level' => 'critical',
                    'source' => 'database',
                    'message' => '数据库服务严重异常: ' . ($status['components']['database']['message'] ?? ''),
                    'details' => [
                        'status' => $status['components']['database']['status'],
                        'response_time' => $status['components']['database']['response_time'] ?? null,
                        'connection' => $status['components']['database']['connection'] ?? null
                    ],
                    'created_at' => now()
                ];
            } elseif (isset($status['components']['database']['status']) && $status['components']['database']['status'] === 'warning') {
                $alerts[] = [
                    'type' => 'health',
                    'level' => 'warning',
                    'source' => 'database',
                    'message' => '数据库服务异常: ' . ($status['components']['database']['message'] ?? ''),
                    'details' => [
                        'status' => $status['components']['database']['status'],
                        'response_time' => $status['components']['database']['response_time'] ?? null,
                        'connection' => $status['components']['database']['connection'] ?? null
                    ],
                    'created_at' => now()
                ];
            }
            
            // 检查缓存状态
            if (isset($status['components']['cache']['status']) && $status['components']['cache']['status'] === 'critical') {
                $alerts[] = [
                    'type' => 'health',
                    'level' => 'critical',
                    'source' => 'cache',
                    'message' => '缓存服务严重异常: ' . ($status['components']['cache']['message'] ?? ''),
                    'details' => [
                        'status' => $status['components']['cache']['status'],
                        'response_time' => $status['components']['cache']['response_time'] ?? null,
                        'driver' => $status['components']['cache']['driver'] ?? null
                    ],
                    'created_at' => now()
                ];
            } elseif (isset($status['components']['cache']['status']) && $status['components']['cache']['status'] === 'warning') {
                $alerts[] = [
                    'type' => 'health',
                    'level' => 'warning',
                    'source' => 'cache',
                    'message' => '缓存服务异常: ' . ($status['components']['cache']['message'] ?? ''),
                    'details' => [
                        'status' => $status['components']['cache']['status'],
                        'response_time' => $status['components']['cache']['response_time'] ?? null,
                        'driver' => $status['components']['cache']['driver'] ?? null
                    ],
                    'created_at' => now()
                ];
            }
            
            // 检查存储状态
            if (isset($status['components']['storage']['status']) && $status['components']['storage']['status'] === 'critical') {
                $alerts[] = [
                    'type' => 'health',
                    'level' => 'critical',
                    'source' => 'storage',
                    'message' => '存储服务严重异常: ' . ($status['components']['storage']['message'] ?? ''),
                    'details' => [
                        'status' => $status['components']['storage']['status'],
                        'response_time' => $status['components']['storage']['response_time'] ?? null,
                        'driver' => $status['components']['storage']['driver'] ?? null
                    ],
                    'created_at' => now()
                ];
            } elseif (isset($status['components']['storage']['status']) && $status['components']['storage']['status'] === 'warning') {
                $alerts[] = [
                    'type' => 'health',
                    'level' => 'warning',
                    'source' => 'storage',
                    'message' => '存储服务异常: ' . ($status['components']['storage']['message'] ?? ''),
                    'details' => [
                        'status' => $status['components']['storage']['status'],
                        'response_time' => $status['components']['storage']['response_time'] ?? null,
                        'driver' => $status['components']['storage']['driver'] ?? null
                    ],
                    'created_at' => now()
                ];
            }
        } catch (\Exception $e) {
            Log::error('检查系统健康状态失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
        
        return $alerts;
    }
    
    /**
     * 检查应用性能指标并生成告警
     * 
     * @return array 生成的告警列表
     */
    private function checkApplicationMetrics(): array
    {
        $alerts = [];
        
        try {
            $metrics = $this->systemMonitorService->getApplicationMetrics();
            
            // 检查响应时间
            if (isset($metrics['response_times']['last_minute']) && $metrics['response_times']['last_minute'] > 1000) {
                $alerts[] = [
                    'type' => 'application',
                    'level' => 'critical',
                    'source' => 'response_time',
                    'message' => '平均响应时间过长: ' . $metrics['response_times']['last_minute'] . 'ms',
                    'details' => [
                        'response_time' => $metrics['response_times']['last_minute'],
                        'threshold' => 1000
                    ],
                    'created_at' => now()
                ];
            } elseif (isset($metrics['response_times']['last_minute']) && $metrics['response_times']['last_minute'] > 500) {
                $alerts[] = [
                    'type' => 'application',
                    'level' => 'warning',
                    'source' => 'response_time',
                    'message' => '平均响应时间较长: ' . $metrics['response_times']['last_minute'] . 'ms',
                    'details' => [
                        'response_time' => $metrics['response_times']['last_minute'],
                        'threshold' => 500
                    ],
                    'created_at' => now()
                ];
            }
            
            // 检查错误率
            if (isset($metrics['error_rates']['last_minute']['error_rate']) && $metrics['error_rates']['last_minute']['error_rate'] > 5) {
                $alerts[] = [
                    'type' => 'application',
                    'level' => 'critical',
                    'source' => 'error_rate',
                    'message' => '错误率过高: ' . $metrics['error_rates']['last_minute']['error_rate'] . '%',
                    'details' => [
                        'error_rate' => $metrics['error_rates']['last_minute']['error_rate'],
                        'total_requests' => $metrics['error_rates']['last_minute']['total_requests'] ?? 0,
                        'error_count' => $metrics['error_rates']['last_minute']['error_count'] ?? 0,
                        'threshold' => 5
                    ],
                    'created_at' => now()
                ];
            } elseif (isset($metrics['error_rates']['last_minute']['error_rate']) && $metrics['error_rates']['last_minute']['error_rate'] > 2) {
                $alerts[] = [
                    'type' => 'application',
                    'level' => 'warning',
                    'source' => 'error_rate',
                    'message' => '错误率较高: ' . $metrics['error_rates']['last_minute']['error_rate'] . '%',
                    'details' => [
                        'error_rate' => $metrics['error_rates']['last_minute']['error_rate'],
                        'total_requests' => $metrics['error_rates']['last_minute']['total_requests'] ?? 0,
                        'error_count' => $metrics['error_rates']['last_minute']['error_count'] ?? 0,
                        'threshold' => 2
                    ],
                    'created_at' => now()
                ];
            }
        } catch (\Exception $e) {
            Log::error('检查应用性能指标失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
        
        return $alerts;
    }
    
    /**
     * 保存告警到数据库
     * 
     * @param array $alerts 告警列表
     * @return void
     */
    private function saveAlerts(array $alerts): void
    {
        try {
            foreach ($alerts as $alertData) {
                // 检查是否已存在相同告警
                $existingAlert = Alert::where('type', $alertData['type'])
                    ->where('source', $alertData['source'])
                    ->where('level', $alertData['level'])
                    ->where('status', 'active')
                    ->first();
                
                if ($existingAlert) {
                    // 更新已存在的告警
                    $existingAlert->occurrence_count += 1;
                    $existingAlert->last_occurred_at = now();
                    $existingAlert->save();
                } else {
                    // 创建新告警
                    $alert = new Alert();
                    $alert->type = $alertData['type'];
                    $alert->level = $alertData['level'];
                    $alert->source = $alertData['source'];
                    $alert->message = $alertData['message'];
                    $alert->details = $alertData['details'] ?? [];
                    $alert->status = 'active';
                    $alert->occurrence_count = 1;
                    $alert->first_occurred_at = now();
                    $alert->last_occurred_at = now();
                    $alert->save();
                }
            }
        } catch (\Exception $e) {
            Log::error('保存告警失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }
    
    /**
     * 发送告警通知
     * 
     * @param array $alerts 告警列表
     * @return void
     */
    private function sendAlertNotifications(array $alerts): void
    {
        try {
            // 筛选出需要立即通知的告警（严重级别）
            $criticalAlerts = array_filter($alerts, function ($alert) {
                return $alert['level'] === 'critical';
            });
            
            if (empty($criticalAlerts)) {
                return;
            }
            
            // 获取需要接收通知的管理员用户
            $admins = $this->getAlertRecipients();
            
            if (empty($admins)) {
                Log::warning('没有找到可接收告警通知的管理员用户');
                return;
            }
            
            // 发送通知
            foreach ($admins as $admin) {
                try {
                    Notification::send($admin, new SystemAlertNotification($criticalAlerts));
                } catch (\Exception $e) {
                    Log::error('发送告警通知给用户 ' . $admin->id . ' 失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                }
            }
        } catch (\Exception $e) {
            Log::error('发送告警通知失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }
    
    /**
     * 获取告警接收者
     * 
     * @return \Illuminate\Database\Eloquent\Collection 告警接收者集合
     */
    private function getAlertRecipients()
    {
        try {
            // 获取所有管理员用户
            return User::where('role', 'admin')
                ->where('status', 'active')
                ->where('alert_notifications_enabled', true)
                ->get();
        } catch (\Exception $e) {
            Log::error('获取告警接收者失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return collect();
        }
    }
    
    /**
     * 获取所有活跃告警
     * 
     * @param int $page 页码
     * @param int $perPage 每页数量
     * @param array $filters 过滤条件
     * @return array 告警列表
     */
    public function getActiveAlerts(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $query = Alert::where('status', 'active');
            
            // 应用过滤条件
            if (isset($filters['level'])) {
                $query->where('level', $filters['level']);
            }
            
            if (isset($filters['type'])) {
                $query->where('type', $filters['type']);
            }
            
            if (isset($filters['source'])) {
                $query->where('source', $filters['source']);
            }
            
            // 排序
            $query->orderBy('level', 'desc')
                ->orderBy('last_occurred_at', 'desc');
            
            // 分页
            $alerts = $query->paginate($perPage, ['*'], 'page', $page);
            
            return [
                'data' => $alerts->items(),
                'total' => $alerts->total(),
                'per_page' => $alerts->perPage(),
                'current_page' => $alerts->currentPage(),
                'last_page' => $alerts->lastPage()
            ];
        } catch (\Exception $e) {
            Log::error('获取活跃告警失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'error' => '获取活跃告警失败',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 确认告警
     * 
     * @param int $alertId 告警ID
     * @param int $userId 用户ID
     * @param string $comment 备注
     * @return array 操作结果
     */
    public function acknowledgeAlert(int $alertId, int $userId, string $comment = ''): array
    {
        try {
            $alert = Alert::findOrFail($alertId);
            
            if ($alert->status !== 'active') {
                return [
                    'success' => false,
                    'message' => '只能确认活跃状态的告警'
                ];
            }
            
            $alert->status = 'acknowledged';
            $alert->acknowledged_by = $userId;
            $alert->acknowledged_at = now();
            $alert->comment = $comment;
            $alert->save();
            
            return [
                'success' => true,
                'message' => '告警已确认',
                'alert' => $alert
            ];
        } catch (\Exception $e) {
            Log::error('确认告警失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'success' => false,
                'message' => '确认告警失败: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 解决告警
     * 
     * @param int $alertId 告警ID
     * @param int $userId 用户ID
     * @param string $resolution 解决方案
     * @return array 操作结果
     */
    public function resolveAlert(int $alertId, int $userId, string $resolution = ''): array
    {
        try {
            $alert = Alert::findOrFail($alertId);
            
            if ($alert->status === 'resolved') {
                return [
                    'success' => false,
                    'message' => '该告警已经被解决'
                ];
            }
            
            $alert->status = 'resolved';
            $alert->resolved_by = $userId;
            $alert->resolved_at = now();
            $alert->resolution = $resolution;
            $alert->save();
            
            return [
                'success' => true,
                'message' => '告警已解决',
                'alert' => $alert
            ];
        } catch (\Exception $e) {
            Log::error('解决告警失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'success' => false,
                'message' => '解决告警失败: ' . $e->getMessage()
            ];
        }
    }
} 