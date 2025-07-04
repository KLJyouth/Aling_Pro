<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification\Notification;
use App\Models\Notification\NotificationRecipient;
use App\Models\Notification\NotificationStatistic;
use App\Models\Notification\NotificationTracking;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class NotificationStatisticsController extends Controller
{
    /**
     * 显示通知统计分析页面
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        // 获取通知
        $notification = Notification::findOrFail($id);
        
        // 处理日期范围
        $dateRange = $request->get("date_range");
        $now = Carbon::now();
        
        if ($dateRange) {
            list($startDate, $endDate) = explode(" - ", $dateRange);
            $startDate = Carbon::createFromFormat("Y-m-d", $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat("Y-m-d", $endDate)->endOfDay();
        } else {
            // 默认显示最近7天
            $endDate = $now->copy()->endOfDay();
            $startDate = $now->copy()->subDays(6)->startOfDay();
            $defaultDateRange = $startDate->format("Y-m-d") . " - " . $endDate->format("Y-m-d");
        }
        
        // 获取统计数据
        $stats = $this->getNotificationStats($notification, $startDate, $endDate);
        
        // 获取图表数据
        $chartData = $this->getChartData($notification, $startDate, $endDate);
        
        // 获取链接点击统计
        $linkStats = $this->getLinkStats($notification->id);
        
        // 获取接收者详情
        $recipients = NotificationRecipient::where("notification_id", $notification->id)
            ->with(["user", "tracking"])
            ->paginate(20);
        
        return view("admin.notification.statistics", compact(
            "notification", 
            "stats", 
            "chartData", 
            "linkStats", 
            "recipients", 
            "defaultDateRange"
        ));
    }
    
    /**
     * 获取通知统计数据
     *
     * @param  \App\Models\Notification\Notification  $notification
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    private function getNotificationStats($notification, $startDate, $endDate)
    {
        // 获取基础统计
        $recipientsStats = NotificationRecipient::where("notification_id", $notification->id)
            ->select(
                DB::raw("COUNT(*) as total_recipients"),
                DB::raw("SUM(CASE WHEN status = \"sent\" OR status = \"read\" THEN 1 ELSE 0 END) as delivered_count"),
                DB::raw("SUM(CASE WHEN status = \"read\" THEN 1 ELSE 0 END) as read_count"),
                DB::raw("SUM(CASE WHEN status = \"failed\" THEN 1 ELSE 0 END) as failed_count")
            )
            ->first();
        
        // 获取通知统计
        $notificationStats = NotificationStatistic::where("notification_id", $notification->id)
            ->whereBetween("date", [$startDate->format("Y-m-d"), $endDate->format("Y-m-d")])
            ->get();
        
        // 合并设备、位置、浏览器、操作系统、时间段统计
        $deviceStats = [];
        $locationStats = [];
        $browserStats = [];
        $osStats = [];
        $timeStats = [];
        
        foreach ($notificationStats as $stat) {
            // 合并设备统计
            if ($stat->device_stats) {
                foreach ($stat->device_stats as $device => $count) {
                    if (isset($deviceStats[$device])) {
                        $deviceStats[$device] += $count;
                    } else {
                        $deviceStats[$device] = $count;
                    }
                }
            }
            
            // 合并位置统计
            if ($stat->location_stats) {
                foreach ($stat->location_stats as $location => $count) {
                    if (isset($locationStats[$location])) {
                        $locationStats[$location] += $count;
                    } else {
                        $locationStats[$location] = $count;
                    }
                }
            }
            
            // 合并浏览器统计
            if ($stat->browser_stats) {
                foreach ($stat->browser_stats as $browser => $count) {
                    if (isset($browserStats[$browser])) {
                        $browserStats[$browser] += $count;
                    } else {
                        $browserStats[$browser] = $count;
                    }
                }
            }
            
            // 合并操作系统统计
            if ($stat->os_stats) {
                foreach ($stat->os_stats as $os => $count) {
                    if (isset($osStats[$os])) {
                        $osStats[$os] += $count;
                    } else {
                        $osStats[$os] = $count;
                    }
                }
            }
            
            // 合并时间段统计
            if ($stat->time_stats) {
                foreach ($stat->time_stats as $time => $count) {
                    if (isset($timeStats[$time])) {
                        $timeStats[$time] += $count;
                    } else {
                        $timeStats[$time] = $count;
                    }
                }
            }
        }
        
        // 对各统计数据进行排序
        arsort($deviceStats);
        arsort($locationStats);
        arsort($browserStats);
        arsort($osStats);
        ksort($timeStats);
        
        // 计算送达率和阅读率
        $totalRecipients = $recipientsStats->total_recipients ?? 0;
        $deliveryRate = $totalRecipients > 0 ? round(($recipientsStats->delivered_count / $totalRecipients) * 100, 2) : 0;
        $readRate = $totalRecipients > 0 ? round(($recipientsStats->read_count / $totalRecipients) * 100, 2) : 0;
        
        return [
            "total_recipients" => $totalRecipients,
            "sent_count" => $totalRecipients,
            "delivered_count" => $recipientsStats->delivered_count ?? 0,
            "read_count" => $recipientsStats->read_count ?? 0,
            "failed_count" => $recipientsStats->failed_count ?? 0,
            "delivery_rate" => $deliveryRate,
            "read_rate" => $readRate,
            "device_stats" => $deviceStats,
            "location_stats" => $locationStats,
            "browser_stats" => $browserStats,
            "os_stats" => $osStats,
            "time_stats" => $timeStats,
        ];
    }
    
    /**
     * 获取图表数据
     *
     * @param  \App\Models\Notification\Notification  $notification
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    private function getChartData($notification, $startDate, $endDate)
    {
        // 生成日期范围
        $period = CarbonPeriod::create($startDate, $endDate);
        $dates = [];
        foreach ($period as $date) {
            $dates[] = $date->format("Y-m-d");
        }
        
        // 获取每日统计数据
        $stats = NotificationStatistic::where("notification_id", $notification->id)
            ->whereBetween("date", [$startDate->format("Y-m-d"), $endDate->format("Y-m-d")])
            ->get()
            ->keyBy("date");
        
        // 准备图表数据
        $sentData = [];
        $deliveredData = [];
        $readData = [];
        
        foreach ($dates as $date) {
            $stat = $stats->get($date);
            $sentData[] = $stat ? $stat->sent_count : 0;
            $deliveredData[] = $stat ? $stat->delivered_count : 0;
            $readData[] = $stat ? $stat->read_count : 0;
        }
        
        return [
            "dates" => $dates,
            "sent" => $sentData,
            "delivered" => $deliveredData,
            "read" => $readData,
        ];
    }
    
    /**
     * 获取链接点击统计
     *
     * @param  int  $notificationId
     * @return array
     */
    private function getLinkStats($notificationId)
    {
        // 获取链接点击统计
        $trackings = NotificationTracking::where("notification_id", $notificationId)
            ->where("tracking_type", "click")
            ->select("url", DB::raw("COUNT(*) as count"))
            ->groupBy("url")
            ->orderByDesc("count")
            ->limit(10)
            ->get();
        
        // 计算总点击数
        $totalClicks = $trackings->sum("count");
        
        // 计算点击率
        $linkStats = [];
        foreach ($trackings as $tracking) {
            $linkStats[] = [
                "url" => $tracking->url,
                "count" => $tracking->count,
                "rate" => $totalClicks > 0 ? round(($tracking->count / $totalClicks) * 100, 2) : 0,
            ];
        }
        
        return $linkStats;
    }
    
    /**
     * 显示接收者详情
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function recipientDetail(Request $request)
    {
        $recipientId = $request->get("recipient_id");
        $recipient = NotificationRecipient::with(["user", "notification", "tracking"])->findOrFail($recipientId);
        
        return view("admin.notification.recipient_detail", compact("recipient"));
    }
    
    /**
     * 导出通知统计数据
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export($id, Request $request)
    {
        $notification = Notification::findOrFail($id);
        $type = $request->get("type", "excel");
        
        // 获取接收者数据
        $recipients = NotificationRecipient::where("notification_id", $notification->id)
            ->with(["user"])
            ->get();
        
        // 根据导出类型处理
        switch ($type) {
            case "excel":
                return $this->exportExcel($notification, $recipients);
            case "csv":
                return $this->exportCsv($notification, $recipients);
            case "pdf":
                return $this->exportPdf($notification, $recipients);
            default:
                return redirect()->back()->with("error", "不支持的导出类型");
        }
    }
    
    /**
     * 导出为Excel
     *
     * @param  \App\Models\Notification\Notification  $notification
     * @param  \Illuminate\Database\Eloquent\Collection  $recipients
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    private function exportExcel($notification, $recipients)
    {
        // 临时返回
        return redirect()->back()->with("info", "Excel导出功能正在开发中");
    }
    
    /**
     * 导出为CSV
     *
     * @param  \App\Models\Notification\Notification  $notification
     * @param  \Illuminate\Database\Eloquent\Collection  $recipients
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportCsv($notification, $recipients)
    {
        // 临时返回
        return redirect()->back()->with("info", "CSV导出功能正在开发中");
    }
    
    /**
     * 导出为PDF
     *
     * @param  \App\Models\Notification\Notification  $notification
     * @param  \Illuminate\Database\Eloquent\Collection  $recipients
     * @return \Illuminate\Http\Response
     */
    private function exportPdf($notification, $recipients)
    {
        // 临时返回
        return redirect()->back()->with("info", "PDF导出功能正在开发中");
    }
}
