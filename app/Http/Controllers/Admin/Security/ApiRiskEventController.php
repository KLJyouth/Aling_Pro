<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\Security\ApiControl\ApiRiskEvent;
use App\Models\Security\ApiControl\ApiRiskRule;
use App\Models\Security\ApiControl\ApiBlacklist;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class ApiRiskEventController extends Controller
{
    /**
     * 显示风险事件列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = ApiRiskEvent::with(['rule', 'user', 'processor']);
        
        // 搜索条件
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where('description', 'like', "%{$keyword}%");
        }
        
        if ($request->filled('rule_id')) {
            $query->where('rule_id', $request->input('rule_id'));
        }
        
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }
        
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->input('event_type'));
        }
        
        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->input('risk_level'));
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->input('start_date') . ' 00:00:00',
                $request->input('end_date') . ' 23:59:59'
            ]);
        }
        
        $events = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // 获取所有规则
        $rules = ApiRiskRule::where('status', 'active')->get();
        
        // 获取事件类型和风险等级
        $eventTypes = ApiRiskEvent::distinct('event_type')->pluck('event_type');
        $riskLevels = ['low', 'medium', 'high', 'critical'];
        
        return view('admin.security.api.risk_events.index', compact('events', 'rules', 'eventTypes', 'riskLevels'));
    }

    /**
     * 显示风险事件详情
     *
     * @param ApiRiskEvent $event
     * @return \Illuminate\View\View
     */
    public function show(ApiRiskEvent $event)
    {
        $event->load(['rule', 'user', 'processor']);
        
        // 获取相关的风险事件（同一用户或同一规则）
        $relatedEvents = ApiRiskEvent::where(function($query) use ($event) {
                if ($event->user_id) {
                    $query->where('user_id', $event->user_id);
                } else {
                    $query->where('rule_id', $event->rule_id);
                }
            })
            ->where('id', '!=', $event->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        return view('admin.security.api.risk_events.show', compact('event', 'relatedEvents'));
    }

    /**
     * 处理风险事件
     *
     * @param Request $request
     * @param ApiRiskEvent $event
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process(Request $request, ApiRiskEvent $event)
    {
        $validatedData = $request->validate([
            'status' => 'required|string|in:processed,ignored,false_positive',
            'notes' => 'nullable|string',
            'add_to_blacklist' => 'nullable|boolean',
            'blacklist_type' => 'required_if:add_to_blacklist,1|string|in:ip,email,phone,device,keyword',
            'blacklist_value' => 'required_if:add_to_blacklist,1|string',
            'blacklist_reason' => 'nullable|string',
            'blacklist_expires_at' => 'nullable|date',
        ]);
        
        try {
            DB::beginTransaction();
            
            // 更新事件状态
            $event->status = $validatedData['status'];
            $event->notes = $validatedData['notes'] ?? null;
            $event->processed_by = Auth::guard('admin')->id();
            $event->processed_at = now();
            $event->save();
            
            // 如果需要添加到黑名单
            if ($request->filled('add_to_blacklist') && $request->boolean('add_to_blacklist')) {
                // 检查黑名单是否已存在
                $exists = ApiBlacklist::where('list_type', $validatedData['blacklist_type'])
                    ->where('value', $validatedData['blacklist_value'])
                    ->exists();
                    
                if (!$exists) {
                    ApiBlacklist::create([
                        'list_type' => $validatedData['blacklist_type'],
                        'value' => $validatedData['blacklist_value'],
                        'reason' => $validatedData['blacklist_reason'] ?? '由风险事件 #' . $event->id . ' 自动添加',
                        'source' => 'auto',
                        'expires_at' => $validatedData['blacklist_expires_at'] ?? null,
                        'status' => 'active',
                        'created_by' => Auth::guard('admin')->id(),
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('admin.api.risk.events.index')
                ->with('success', '风险事件处理成功');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('风险事件处理失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', '风险事件处理失败: ' . $e->getMessage());
        }
    }

    /**
     * 批量处理风险事件
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function batchProcess(Request $request)
    {
        $validatedData = $request->validate([
            'event_ids' => 'required|array',
            'event_ids.*' => 'exists:api_risk_events,id',
            'status' => 'required|string|in:processed,ignored,false_positive',
            'notes' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            $count = ApiRiskEvent::whereIn('id', $validatedData['event_ids'])
                ->update([
                    'status' => $validatedData['status'],
                    'notes' => $validatedData['notes'] ?? null,
                    'processed_by' => Auth::guard('admin')->id(),
                    'processed_at' => now(),
                ]);
            
            DB::commit();
            
            return redirect()->route('admin.api.risk.events.index')
                ->with('success', "成功处理 {$count} 个风险事件");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('批量处理风险事件失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', '批量处理风险事件失败: ' . $e->getMessage());
        }
    }

    /**
     * 导出风险事件
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $query = ApiRiskEvent::with(['rule', 'user', 'processor']);
        
        // 搜索条件
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where('description', 'like', "%{$keyword}%");
        }
        
        if ($request->filled('rule_id')) {
            $query->where('rule_id', $request->input('rule_id'));
        }
        
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }
        
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->input('event_type'));
        }
        
        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->input('risk_level'));
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->input('start_date') . ' 00:00:00',
                $request->input('end_date') . ' 23:59:59'
            ]);
        }
        
        $events = $query->orderBy('created_at', 'desc')->get();
        
        // 准备CSV数据
        $csvData = [
            ['ID', '规则', '用户', '事件类型', '风险等级', '风险分数', '描述', '状态', '处理人', '处理时间', '创建时间']
        ];
        
        foreach ($events as $event) {
            $csvData[] = [
                $event->id,
                $event->rule ? $event->rule->name : '未知',
                $event->user ? $event->user->name : '未知',
                $event->event_type,
                $event->risk_level,
                $event->risk_score,
                $event->description,
                $event->status,
                $event->processor ? $event->processor->username : '未处理',
                $event->processed_at ? $event->processed_at->format('Y-m-d H:i:s') : '未处理',
                $event->created_at->format('Y-m-d H:i:s')
            ];
        }
        
        // 创建CSV文件
        $filename = 'api_risk_events_' . date('YmdHis') . '.csv';
        $tempFile = fopen('php://temp', 'r+');
        
        foreach ($csvData as $row) {
            fputcsv($tempFile, $row);
        }
        
        rewind($tempFile);
        $csv = stream_get_contents($tempFile);
        fclose($tempFile);
        
        // 添加BOM头，解决中文乱码问题
        $csv = chr(0xEF) . chr(0xBB) . chr(0xBF) . $csv;
        
        // 返回CSV文件
        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * 显示风险事件统计
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function statistics(Request $request)
    {
        // 时间范围
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        
        // 按日期统计风险事件数量
        $dailyStats = ApiRiskEvent::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
        
        // 按风险等级统计
        $riskLevelStats = ApiRiskEvent::selectRaw('risk_level, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('risk_level')
            ->orderBy('risk_level')
            ->get()
            ->pluck('count', 'risk_level')
            ->toArray();
        
        // 按事件类型统计
        $eventTypeStats = ApiRiskEvent::selectRaw('event_type, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('event_type')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->pluck('count', 'event_type')
            ->toArray();
        
        // 按规则统计
        $ruleStats = ApiRiskEvent::selectRaw('api_risk_rules.name as rule_name, COUNT(*) as count')
            ->join('api_risk_rules', 'api_risk_events.rule_id', '=', 'api_risk_rules.id')
            ->whereBetween('api_risk_events.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('api_risk_rules.name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->pluck('count', 'rule_name')
            ->toArray();
        
        // 按状态统计
        $statusStats = ApiRiskEvent::selectRaw('status, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
        
        // 总体统计
        $totalStats = [
            'total' => ApiRiskEvent::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->count(),
            'pending' => ApiRiskEvent::where('status', 'pending')->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->count(),
            'processed' => ApiRiskEvent::where('status', 'processed')->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->count(),
            'ignored' => ApiRiskEvent::where('status', 'ignored')->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->count(),
            'false_positive' => ApiRiskEvent::where('status', 'false_positive')->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->count(),
            'high_risk' => ApiRiskEvent::whereIn('risk_level', ['high', 'critical'])->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->count(),
        ];
        
        return view('admin.security.api.risk_events.statistics', compact(
            'dailyStats', 
            'riskLevelStats', 
            'eventTypeStats', 
            'ruleStats', 
            'statusStats', 
            'totalStats',
            'startDate',
            'endDate'
        ));
    }
}
