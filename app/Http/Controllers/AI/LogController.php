<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AI\ApiLog;
use App\Models\AI\AuditLog;
use App\Models\AI\ModelProvider;
use App\Models\AI\AIModel;
use App\Models\AI\Agent;
use App\Models\AI\ApiKey;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    /**
     * API调用日志列表
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function apiLogs(Request $request)
    {
        $query = ApiLog::with(["provider", "model", "agent", "apiKey", "user"]);
        
        // 筛选条件
        if ($request->has("provider_id")) {
            $query->where("provider_id", $request->provider_id);
        }
        
        if ($request->has("model_id")) {
            $query->where("model_id", $request->model_id);
        }
        
        if ($request->has("agent_id")) {
            $query->where("agent_id", $request->agent_id);
        }
        
        if ($request->has("api_key_id")) {
            $query->where("api_key_id", $request->api_key_id);
        }
        
        if ($request->has("user_id")) {
            $query->where("user_id", $request->user_id);
        }
        
        if ($request->has("status")) {
            $query->where("status", $request->status);
        }
        
        if ($request->has("start_date")) {
            $query->whereDate("created_at", ">=", $request->start_date);
        }
        
        if ($request->has("end_date")) {
            $query->whereDate("created_at", "<=", $request->end_date);
        }
        
        // 排序
        $sortField = $request->get("sort_field", "created_at");
        $sortDirection = $request->get("sort_direction", "desc");
        $query->orderBy($sortField, $sortDirection);
        
        $logs = $query->paginate(15);
        
        // 统计数据
        $stats = [
            "total_requests" => ApiLog::count(),
            "success_rate" => ApiLog::where("status", "success")->count() / max(1, ApiLog::count()) * 100,
            "total_tokens" => ApiLog::sum(DB::raw("input_tokens + output_tokens")),
            "avg_response_time" => ApiLog::avg("response_time"),
            "total_cost" => ApiLog::sum("cost"),
        ];
        
        return view("admin.ai.logs.api_logs", [
            "logs" => $logs,
            "stats" => $stats,
            "providers" => ModelProvider::all(),
            "models" => AIModel::all(),
            "agents" => Agent::all(),
            "apiKeys" => ApiKey::all(),
        ]);
    }
    
    /**
     * API调用日志详情
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function showApiLog($id)
    {
        $log = ApiLog::with(["provider", "model", "agent", "apiKey", "user"])->findOrFail($id);
        
        return view("admin.ai.logs.api_log_detail", [
            "log" => $log,
        ]);
    }
    
    /**
     * 审计日志列表
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with("user");
        
        // 筛选条件
        if ($request->has("user_id")) {
            $query->where("user_id", $request->user_id);
        }
        
        if ($request->has("action")) {
            $query->where("action", $request->action);
        }
        
        if ($request->has("resource_type")) {
            $query->where("resource_type", $request->resource_type);
        }
        
        if ($request->has("start_date")) {
            $query->whereDate("created_at", ">=", $request->start_date);
        }
        
        if ($request->has("end_date")) {
            $query->whereDate("created_at", "<=", $request->end_date);
        }
        
        // 排序
        $sortField = $request->get("sort_field", "created_at");
        $sortDirection = $request->get("sort_direction", "desc");
        $query->orderBy($sortField, $sortDirection);
        
        $logs = $query->paginate(15);
        
        return view("admin.ai.logs.audit_logs", [
            "logs" => $logs,
        ]);
    }
    
    /**
     * 审计日志详情
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function showAuditLog($id)
    {
        $log = AuditLog::with("user")->findOrFail($id);
        
        return view("admin.ai.logs.audit_log_detail", [
            "log" => $log,
        ]);
    }
    
    /**
     * 导出API调用日志
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportApiLogs(Request $request)
    {
        $query = ApiLog::with(["provider", "model", "agent", "apiKey", "user"]);
        
        // 应用筛选条件
        if ($request->has("provider_id")) {
            $query->where("provider_id", $request->provider_id);
        }
        
        if ($request->has("model_id")) {
            $query->where("model_id", $request->model_id);
        }
        
        if ($request->has("agent_id")) {
            $query->where("agent_id", $request->agent_id);
        }
        
        if ($request->has("api_key_id")) {
            $query->where("api_key_id", $request->api_key_id);
        }
        
        if ($request->has("user_id")) {
            $query->where("user_id", $request->user_id);
        }
        
        if ($request->has("status")) {
            $query->where("status", $request->status);
        }
        
        if ($request->has("start_date")) {
            $query->whereDate("created_at", ">=", $request->start_date);
        }
        
        if ($request->has("end_date")) {
            $query->whereDate("created_at", "<=", $request->end_date);
        }
        
        $logs = $query->get();
        
        // 创建CSV文件
        $filename = "api_logs_" . date("Y-m-d_H-i-s") . ".csv";
        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
        ];
        
        $handle = fopen("php://temp", "r+");
        
        // 添加CSV头
        fputcsv($handle, [
            "ID", "提供商", "模型", "智能体", "用户", "请求时间", "响应时间(ms)",
            "输入标记数", "输出标记数", "总标记数", "状态", "成本", "IP地址"
        ]);
        
        // 添加数据行
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->id,
                $log->provider ? $log->provider->name : "未知",
                $log->model ? $log->model->name : "未知",
                $log->agent ? $log->agent->name : "未知",
                $log->user ? $log->user->name : "系统",
                $log->created_at,
                $log->response_time,
                $log->input_tokens,
                $log->output_tokens,
                $log->input_tokens + $log->output_tokens,
                $log->status,
                $log->cost,
                $log->ip_address,
            ]);
        }
        
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        
        return response($content, 200, $headers);
    }
    
    /**
     * 导出审计日志
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportAuditLogs(Request $request)
    {
        $query = AuditLog::with("user");
        
        // 应用筛选条件
        if ($request->has("user_id")) {
            $query->where("user_id", $request->user_id);
        }
        
        if ($request->has("action")) {
            $query->where("action", $request->action);
        }
        
        if ($request->has("resource_type")) {
            $query->where("resource_type", $request->resource_type);
        }
        
        if ($request->has("start_date")) {
            $query->whereDate("created_at", ">=", $request->start_date);
        }
        
        if ($request->has("end_date")) {
            $query->whereDate("created_at", "<=", $request->end_date);
        }
        
        $logs = $query->get();
        
        // 创建CSV文件
        $filename = "audit_logs_" . date("Y-m-d_H-i-s") . ".csv";
        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
        ];
        
        $handle = fopen("php://temp", "r+");
        
        // 添加CSV头
        fputcsv($handle, [
            "ID", "用户", "操作", "资源类型", "资源ID", "操作时间", "IP地址"
        ]);
        
        // 添加数据行
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->id,
                $log->user ? $log->user->name : "系统",
                $log->action,
                $log->resource_type,
                $log->resource_id,
                $log->created_at,
                $log->ip_address,
            ]);
        }
        
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        
        return response($content, 200, $headers);
    }
}
