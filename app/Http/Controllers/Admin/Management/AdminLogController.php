<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use App\Models\Admin\Management\AdminLoginLog;
use App\Models\Admin\Management\AdminOperationLog;
use App\Models\Admin\Management\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AdminLogController extends Controller
{
    /**
     * 显示管理员登录日志
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function loginLogs(Request $request)
    {
        $query = AdminLoginLog::with('adminUser');
        
        // 搜索条件
        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->input('admin_id'));
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->filled('ip')) {
            $query->where('ip_address', 'like', '%' . $request->input('ip') . '%');
        }
        
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->input('start_date') . ' 00:00:00',
                $request->input('end_date') . ' 23:59:59'
            ]);
        }
        
        $logs = $query->orderBy('created_at', 'desc')->paginate(20);
        $admins = AdminUser::all();
        
        return view('admin.management.logs.login', compact('logs', 'admins'));
    }

    /**
     * 显示管理员操作日志
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function operationLogs(Request $request)
    {
        $query = AdminOperationLog::with('adminUser');
        
        // 搜索条件
        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->input('admin_id'));
        }
        
        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }
        
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }
        
        if ($request->filled('ip')) {
            $query->where('ip_address', 'like', '%' . $request->input('ip') . '%');
        }
        
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->input('start_date') . ' 00:00:00',
                $request->input('end_date') . ' 23:59:59'
            ]);
        }
        
        $logs = $query->orderBy('created_at', 'desc')->paginate(20);
        $admins = AdminUser::all();
        
        // 获取所有模块和操作
        $modules = AdminOperationLog::distinct('module')->pluck('module');
        $actions = AdminOperationLog::distinct('action')->pluck('action');
        
        return view('admin.management.logs.operation', compact('logs', 'admins', 'modules', 'actions'));
    }

    /**
     * 显示登录日志详情
     *
     * @param int $log
     * @return \Illuminate\View\View
     */
    public function showLoginLog($log)
    {
        $log = AdminLoginLog::with('adminUser')->findOrFail($log);
        return view('admin.management.logs.login_detail', compact('log'));
    }

    /**
     * 显示操作日志详情
     *
     * @param int $log
     * @return \Illuminate\View\View
     */
    public function showOperationLog($log)
    {
        $log = AdminOperationLog::with('adminUser')->findOrFail($log);
        return view('admin.management.logs.operation_detail', compact('log'));
    }

    /**
     * 导出登录日志
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportLoginLogs(Request $request)
    {
        $query = AdminLoginLog::with('adminUser');
        
        // 搜索条件
        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->input('admin_id'));
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->filled('ip')) {
            $query->where('ip_address', 'like', '%' . $request->input('ip') . '%');
        }
        
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->input('start_date') . ' 00:00:00',
                $request->input('end_date') . ' 23:59:59'
            ]);
        }
        
        $logs = $query->orderBy('created_at', 'desc')->get();
        
        // 准备CSV数据
        $csvData = [
            ['ID', '管理员', '状态', 'IP地址', '设备类型', '位置', '登录时间', '失败原因']
        ];
        
        foreach ($logs as $log) {
            $csvData[] = [
                $log->id,
                $log->adminUser ? $log->adminUser->username : '未知',
                $log->status,
                $log->ip_address,
                $log->device_type ?? '未知',
                $log->location ?? '未知',
                $log->created_at->format('Y-m-d H:i:s'),
                $log->failure_reason ?? ''
            ];
        }
        
        // 创建CSV文件
        $filename = 'admin_login_logs_' . date('YmdHis') . '.csv';
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
     * 导出操作日志
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportOperationLogs(Request $request)
    {
        $query = AdminOperationLog::with('adminUser');
        
        // 搜索条件
        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->input('admin_id'));
        }
        
        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }
        
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }
        
        if ($request->filled('ip')) {
            $query->where('ip_address', 'like', '%' . $request->input('ip') . '%');
        }
        
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->input('start_date') . ' 00:00:00',
                $request->input('end_date') . ' 23:59:59'
            ]);
        }
        
        $logs = $query->orderBy('created_at', 'desc')->get();
        
        // 准备CSV数据
        $csvData = [
            ['ID', '管理员', '模块', '操作', '请求方法', 'IP地址', '操作时间']
        ];
        
        foreach ($logs as $log) {
            $csvData[] = [
                $log->id,
                $log->adminUser ? $log->adminUser->username : '未知',
                $log->module,
                $log->action,
                $log->method,
                $log->ip_address,
                $log->created_at->format('Y-m-d H:i:s')
            ];
        }
        
        // 创建CSV文件
        $filename = 'admin_operation_logs_' . date('YmdHis') . '.csv';
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
}
