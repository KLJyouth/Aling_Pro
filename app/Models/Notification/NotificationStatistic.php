<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

/**
 * 通知统计模型
 * 
 * 用于记录和分析通知的统计数据
 */
class NotificationStatistic extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'notification_statistics';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'notification_id',        // 通知ID
        'date',                   // 统计日期
        'sent_count',             // 发送数量
        'delivered_count',        // 送达数量
        'read_count',             // 阅读数量
        'failed_count',           // 失败数量
        'click_count',            // 点击数量
        'device_stats',           // 设备统计（JSON）
        'location_stats',         // 位置统计（JSON）
        'browser_stats',          // 浏览器统计（JSON）
        'os_stats',               // 操作系统统计（JSON）
        'time_stats',             // 时间段统计（JSON）
        'metadata',               // 其他元数据（JSON）
    ];

    /**
     * 应该被转换为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'date',
        'created_at',
        'updated_at',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'device_stats' => 'array',
        'location_stats' => 'array',
        'browser_stats' => 'array',
        'os_stats' => 'array',
        'time_stats' => 'array',
        'metadata' => 'array',
    ];

    /**
     * 获取关联的通知
     */
    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_id');
    }

    /**
     * 获取特定通知的统计数据
     *
     * @param int $notificationId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getNotificationStats($notificationId)
    {
        return self::where('notification_id', $notificationId)
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * 获取特定日期范围的通知统计数据
     *
     * @param int $notificationId
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getNotificationStatsByDateRange($notificationId, $startDate, $endDate)
    {
        return self::where('notification_id', $notificationId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * 获取特定日期的通知统计数据
     *
     * @param string $date 日期，格式：Y-m-d
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getStatsByDate($date)
    {
        return self::where('date', $date)->get();
    }

    /**
     * 获取特定日期范围内的所有通知统计数据
     *
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getStatsByDateRange($startDate, $endDate)
    {
        return self::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * 获取通知的总体统计数据
     *
     * @param int $notificationId
     * @return array
     */
    public static function getOverallStats($notificationId)
    {
        $stats = self::where('notification_id', $notificationId)->get();
        
        $result = [
            'sent_count' => $stats->sum('sent_count'),
            'delivered_count' => $stats->sum('delivered_count'),
            'read_count' => $stats->sum('read_count'),
            'failed_count' => $stats->sum('failed_count'),
            'click_count' => $stats->sum('click_count'),
            'delivery_rate' => 0,
            'read_rate' => 0,
            'failure_rate' => 0,
        ];
        
        // 计算比率
        if ($result['sent_count'] > 0) {
            $result['delivery_rate'] = round(($result['delivered_count'] / $result['sent_count']) * 100, 2);
            $result['read_rate'] = round(($result['read_count'] / $result['sent_count']) * 100, 2);
            $result['failure_rate'] = round(($result['failed_count'] / $result['sent_count']) * 100, 2);
        }
        
        // 合并设备统计
        $result['device_stats'] = self::mergeJsonStats($stats, 'device_stats');
        $result['location_stats'] = self::mergeJsonStats($stats, 'location_stats');
        $result['browser_stats'] = self::mergeJsonStats($stats, 'browser_stats');
        $result['os_stats'] = self::mergeJsonStats($stats, 'os_stats');
        $result['time_stats'] = self::mergeJsonStats($stats, 'time_stats');
        
        return $result;
    }

    /**
     * 合并JSON统计数据
     *
     * @param \Illuminate\Database\Eloquent\Collection $stats
     * @param string $field
     * @return array
     */
    protected static function mergeJsonStats($stats, $field)
    {
        $result = [];
        
        foreach ($stats as $stat) {
            if (!empty($stat->$field)) {
                foreach ($stat->$field as $key => $value) {
                    if (isset($result[$key])) {
                        $result[$key] += $value;
                    } else {
                        $result[$key] = $value;
                    }
                }
            }
        }
        
        return $result;
    }

    /**
     * 记录通知统计数据
     *
     * @param int $notificationId
     * @param array $data
     * @return NotificationStatistic
     */
    public static function recordStats($notificationId, array $data)
    {
        $date = $data['date'] ?? date('Y-m-d');
        
        // 查找或创建统计记录
        $stat = self::firstOrNew([
            'notification_id' => $notificationId,
            'date' => $date,
        ]);
        
        // 更新计数
        if (isset($data['sent_count'])) {
            $stat->sent_count = ($stat->sent_count ?? 0) + $data['sent_count'];
        }
        
        if (isset($data['delivered_count'])) {
            $stat->delivered_count = ($stat->delivered_count ?? 0) + $data['delivered_count'];
        }
        
        if (isset($data['read_count'])) {
            $stat->read_count = ($stat->read_count ?? 0) + $data['read_count'];
        }
        
        if (isset($data['failed_count'])) {
            $stat->failed_count = ($stat->failed_count ?? 0) + $data['failed_count'];
        }
        
        if (isset($data['click_count'])) {
            $stat->click_count = ($stat->click_count ?? 0) + $data['click_count'];
        }
        
        // 更新JSON统计
        if (isset($data['device'])) {
            $deviceStats = $stat->device_stats ?? [];
            $device = $data['device'];
            $deviceStats[$device] = ($deviceStats[$device] ?? 0) + 1;
            $stat->device_stats = $deviceStats;
        }
        
        if (isset($data['location'])) {
            $locationStats = $stat->location_stats ?? [];
            $location = $data['location'];
            $locationStats[$location] = ($locationStats[$location] ?? 0) + 1;
            $stat->location_stats = $locationStats;
        }
        
        if (isset($data['browser'])) {
            $browserStats = $stat->browser_stats ?? [];
            $browser = $data['browser'];
            $browserStats[$browser] = ($browserStats[$browser] ?? 0) + 1;
            $stat->browser_stats = $browserStats;
        }
        
        if (isset($data['os'])) {
            $osStats = $stat->os_stats ?? [];
            $os = $data['os'];
            $osStats[$os] = ($osStats[$os] ?? 0) + 1;
            $stat->os_stats = $osStats;
        }
        
        if (isset($data['time'])) {
            $timeStats = $stat->time_stats ?? [];
            $time = $data['time'];
            $timeStats[$time] = ($timeStats[$time] ?? 0) + 1;
            $stat->time_stats = $timeStats;
        }
        
        // 保存统计记录
        $stat->save();
        
        return $stat;
    }
}
