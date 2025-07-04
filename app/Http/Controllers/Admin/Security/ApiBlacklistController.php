<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\Security\ApiControl\ApiBlacklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class ApiBlacklistController extends Controller
{
    /**
     * 显示黑名单列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = ApiBlacklist::with('creator');
        
        // 搜索条件
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function($q) use ($keyword) {
                $q->where('value', 'like', "%{$keyword}%")
                  ->orWhere('reason', 'like', "%{$keyword}%");
            });
        }
        
        if ($request->filled('list_type')) {
            $query->where('list_type', $request->input('list_type'));
        }
        
        if ($request->filled('source')) {
            $query->where('source', $request->input('source'));
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->filled('expired') && $request->boolean('expired')) {
            $query->where(function($q) {
                $q->where('expires_at', '<', now())
                  ->orWhere('status', 'expired');
            });
        } else {
            $query->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })->where('status', '!=', 'expired');
        }
        
        $blacklists = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.security.api.blacklists.index', compact('blacklists'));
    }

    /**
     * 显示创建黑名单表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $listTypes = [
            'ip' => 'IP地址',
            'email' => '电子邮件',
            'phone' => '手机号码',
            'device' => '设备ID',
            'keyword' => '关键词'
        ];
        
        return view('admin.security.api.blacklists.create', compact('listTypes'));
    }

    /**
     * 保存新黑名单
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'list_type' => 'required|string|in:ip,email,phone,device,keyword',
            'value' => 'required|string',
            'reason' => 'nullable|string',
            'expires_at' => 'nullable|date',
            'status' => 'required|string|in:active,expired,removed',
        ]);
        
        try {
            DB::beginTransaction();
            
            // 检查是否已存在
            $exists = ApiBlacklist::where('list_type', $validatedData['list_type'])
                ->where('value', $validatedData['value'])
                ->exists();
                
            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', '该黑名单项已存在');
            }
            
            $blacklist = new ApiBlacklist();
            $blacklist->list_type = $validatedData['list_type'];
            $blacklist->value = $validatedData['value'];
            $blacklist->reason = $validatedData['reason'] ?? null;
            $blacklist->source = 'manual';
            $blacklist->expires_at = $validatedData['expires_at'] ?? null;
            $blacklist->status = $validatedData['status'];
            $blacklist->created_by = Auth::guard('admin')->id();
            $blacklist->save();
            
            DB::commit();
            
            return redirect()->route('admin.api.blacklists.index')
                ->with('success', '黑名单添加成功');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('黑名单添加失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', '黑名单添加失败: ' . $e->getMessage());
        }
    }

    /**
     * 显示编辑黑名单表单
     *
     * @param ApiBlacklist $blacklist
     * @return \Illuminate\View\View
     */
    public function edit(ApiBlacklist $blacklist)
    {
        $listTypes = [
            'ip' => 'IP地址',
            'email' => '电子邮件',
            'phone' => '手机号码',
            'device' => '设备ID',
            'keyword' => '关键词'
        ];
        
        return view('admin.security.api.blacklists.edit', compact('blacklist', 'listTypes'));
    }

    /**
     * 更新黑名单
     *
     * @param Request $request
     * @param ApiBlacklist $blacklist
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, ApiBlacklist $blacklist)
    {
        $validatedData = $request->validate([
            'list_type' => 'required|string|in:ip,email,phone,device,keyword',
            'value' => 'required|string',
            'reason' => 'nullable|string',
            'expires_at' => 'nullable|date',
            'status' => 'required|string|in:active,expired,removed',
        ]);
        
        try {
            DB::beginTransaction();
            
            // 检查是否已存在（排除自身）
            $exists = ApiBlacklist::where('list_type', $validatedData['list_type'])
                ->where('value', $validatedData['value'])
                ->where('id', '!=', $blacklist->id)
                ->exists();
                
            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', '该黑名单项已存在');
            }
            
            $blacklist->list_type = $validatedData['list_type'];
            $blacklist->value = $validatedData['value'];
            $blacklist->reason = $validatedData['reason'] ?? null;
            $blacklist->expires_at = $validatedData['expires_at'] ?? null;
            $blacklist->status = $validatedData['status'];
            $blacklist->save();
            
            DB::commit();
            
            return redirect()->route('admin.api.blacklists.index')
                ->with('success', '黑名单更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('黑名单更新失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', '黑名单更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除黑名单
     *
     * @param ApiBlacklist $blacklist
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ApiBlacklist $blacklist)
    {
        try {
            $blacklist->delete();
            
            return redirect()->route('admin.api.blacklists.index')
                ->with('success', '黑名单删除成功');
        } catch (\Exception $e) {
            Log::error('黑名单删除失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', '黑名单删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 导入黑名单
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt',
            'list_type' => 'required|string|in:ip,email,phone,device,keyword',
            'reason' => 'nullable|string',
            'expires_at' => 'nullable|date',
        ]);
        
        try {
            $file = $request->file('import_file');
            $listType = $request->input('list_type');
            $reason = $request->input('reason') ?? '批量导入';
            $expiresAt = $request->input('expires_at');
            
            // 读取CSV文件
            $handle = fopen($file->getPathname(), 'r');
            
            DB::beginTransaction();
            
            $importCount = 0;
            $skipCount = 0;
            
            while (($data = fgetcsv($handle)) !== false) {
                // 跳过空行
                if (empty($data[0])) {
                    continue;
                }
                
                $value = trim($data[0]);
                
                // 检查是否已存在
                $exists = ApiBlacklist::where('list_type', $listType)
                    ->where('value', $value)
                    ->exists();
                    
                if ($exists) {
                    $skipCount++;
                    continue;
                }
                
                ApiBlacklist::create([
                    'list_type' => $listType,
                    'value' => $value,
                    'reason' => $reason,
                    'source' => 'import',
                    'expires_at' => $expiresAt,
                    'status' => 'active',
                    'created_by' => Auth::guard('admin')->id(),
                ]);
                
                $importCount++;
            }
            
            fclose($handle);
            DB::commit();
            
            return redirect()->route('admin.api.blacklists.index')
                ->with('success', "成功导入 {$importCount} 条黑名单记录，跳过 {$skipCount} 条重复记录");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('黑名单导入失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', '黑名单导入失败: ' . $e->getMessage());
        }
    }

    /**
     * 导出黑名单
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $query = ApiBlacklist::with('creator');
        
        // 搜索条件
        if ($request->filled('list_type')) {
            $query->where('list_type', $request->input('list_type'));
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->filled('source')) {
            $query->where('source', $request->input('source'));
        }
        
        $blacklists = $query->orderBy('list_type')
            ->orderBy('value')
            ->get();
        
        // 准备CSV数据
        $csvData = [
            ['类型', '值', '原因', '来源', '过期时间', '状态', '创建人', '创建时间']
        ];
        
        $listTypeMap = [
            'ip' => 'IP地址',
            'email' => '电子邮件',
            'phone' => '手机号码',
            'device' => '设备ID',
            'keyword' => '关键词'
        ];
        
        $sourceMap = [
            'manual' => '手动添加',
            'auto' => '自动添加',
            'import' => '批量导入'
        ];
        
        $statusMap = [
            'active' => '生效',
            'expired' => '已过期',
            'removed' => '已移除'
        ];
        
        foreach ($blacklists as $blacklist) {
            $csvData[] = [
                $listTypeMap[$blacklist->list_type] ?? $blacklist->list_type,
                $blacklist->value,
                $blacklist->reason,
                $sourceMap[$blacklist->source] ?? $blacklist->source,
                $blacklist->expires_at ? $blacklist->expires_at->format('Y-m-d H:i:s') : '永不过期',
                $statusMap[$blacklist->status] ?? $blacklist->status,
                $blacklist->creator ? $blacklist->creator->username : '未知',
                $blacklist->created_at->format('Y-m-d H:i:s')
            ];
        }
        
        // 创建CSV文件
        $filename = 'api_blacklists_' . date('YmdHis') . '.csv';
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
