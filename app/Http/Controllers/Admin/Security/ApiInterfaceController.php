<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Security\ApiControl\ApiInterface;
use App\Models\Security\ApiControl\ApiAccessControl;
use App\Models\Security\ApiControl\ApiSecurityAudit;
use App\Models\Admin\Management\AdminOperationLog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ApiInterfaceController extends Controller
{
    /**
     * 显示API接口列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = ApiInterface::query();
        
        // 搜索条件
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('path', 'like', "%{$keyword}%");
            });
        }
        
        if ($request->filled('method')) {
            $query->where('method', $request->input('method'));
        }
        
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        $interfaces = $query->orderBy('category')
            ->orderBy('path')
            ->paginate(20);
        
        // 获取所有分类
        $categories = ApiInterface::distinct('category')->pluck('category');
        
        return view('admin.security.api.interfaces.index', compact('interfaces', 'categories'));
    }

    /**
     * 显示创建API接口表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // 获取所有分类
        $categories = ApiInterface::distinct('category')->pluck('category');
        return view('admin.security.api.interfaces.create', compact('categories'));
    }

    /**
     * 保存新API接口
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:100',
            'path' => 'required|string',
            'method' => 'required|string|max:10',
            'version' => 'required|string|max:10',
            'category' => 'required|string|max:50',
            'description' => 'nullable|string',
            'rate_limit' => 'required|integer|min:1',
            'rate_window' => 'required|integer|min:1',
            'requires_auth' => 'required|boolean',
            'required_role' => 'nullable|string',
            'status' => 'required|string|in:active,inactive,deprecated',
        ]);
        
        try {
            DB::beginTransaction();
            
            // 检查路径、方法和版本的组合是否唯一
            $exists = ApiInterface::where('path', $validatedData['path'])
                ->where('method', $validatedData['method'])
                ->where('version', $validatedData['version'])
                ->exists();
                
            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', '相同路径、方法和版本的接口已存在');
            }
            
            $interface = new ApiInterface();
            $interface->name = $validatedData['name'];
            $interface->path = $validatedData['path'];
            $interface->method = $validatedData['method'];
            $interface->version = $validatedData['version'];
            $interface->category = $validatedData['category'];
            $interface->description = $validatedData['description'] ?? null;
            $interface->rate_limit = $validatedData['rate_limit'];
            $interface->rate_window = $validatedData['rate_window'];
            $interface->requires_auth = $validatedData['requires_auth'];
            $interface->required_role = $validatedData['required_role'] ?? null;
            $interface->status = $validatedData['status'];
            $interface->save();
            
            // 创建默认的访问控制
            ApiAccessControl::create([
                'interface_id' => $interface->id,
                'control_type' => 'rate_limit',
                'control_config' => [
                    'limit' => $validatedData['rate_limit'],
                    'window' => $validatedData['rate_window'],
                ],
                'status' => 'active',
                'description' => '默认速率限制',
                'created_by' => auth('admin')->id(),
            ]);
            
            DB::commit();
            
            return redirect()->route('admin.api.interfaces.index')
                ->with('success', 'API接口创建成功');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API接口创建失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'API接口创建失败: ' . $e->getMessage());
        }
    }

    /**
     * 显示API接口详情
     *
     * @param ApiInterface $interface
     * @return \Illuminate\View\View
     */
    public function show(ApiInterface $interface)
    {
        // 加载访问控制
        $interface->load('accessControls');
        
        // 获取接口调用统计
        $stats = [
            'total_calls' => rand(1000, 10000), // 示例数据，实际应从监控系统获取
            'success_rate' => rand(90, 99) . '%',
            'avg_response_time' => rand(50, 500) . 'ms',
            'error_rate' => rand(1, 10) . '%',
        ];
        
        return view('admin.security.api.interfaces.show', compact('interface', 'stats'));
    }

    /**
     * 显示编辑API接口表单
     *
     * @param ApiInterface $interface
     * @return \Illuminate\View\View
     */
    public function edit(ApiInterface $interface)
    {
        // 获取所有分类
        $categories = ApiInterface::distinct('category')->pluck('category');
        return view('admin.security.api.interfaces.edit', compact('interface', 'categories'));
    }

    /**
     * 更新API接口
     *
     * @param Request $request
     * @param ApiInterface $interface
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, ApiInterface $interface)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:100',
            'path' => 'required|string',
            'method' => 'required|string|max:10',
            'version' => 'required|string|max:10',
            'category' => 'required|string|max:50',
            'description' => 'nullable|string',
            'rate_limit' => 'required|integer|min:1',
            'rate_window' => 'required|integer|min:1',
            'requires_auth' => 'required|boolean',
            'required_role' => 'nullable|string',
            'status' => 'required|string|in:active,inactive,deprecated',
        ]);
        
        try {
            DB::beginTransaction();
            
            // 检查路径、方法和版本的组合是否唯一（排除自身）
            $exists = ApiInterface::where('path', $validatedData['path'])
                ->where('method', $validatedData['method'])
                ->where('version', $validatedData['version'])
                ->where('id', '!=', $interface->id)
                ->exists();
                
            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', '相同路径、方法和版本的接口已存在');
            }
            
            $interface->name = $validatedData['name'];
            $interface->path = $validatedData['path'];
            $interface->method = $validatedData['method'];
            $interface->version = $validatedData['version'];
            $interface->category = $validatedData['category'];
            $interface->description = $validatedData['description'] ?? null;
            $interface->rate_limit = $validatedData['rate_limit'];
            $interface->rate_window = $validatedData['rate_window'];
            $interface->requires_auth = $validatedData['requires_auth'];
            $interface->required_role = $validatedData['required_role'] ?? null;
            $interface->status = $validatedData['status'];
            $interface->save();
            
            // 更新默认的访问控制
            $accessControl = ApiAccessControl::where('interface_id', $interface->id)
                ->where('control_type', 'rate_limit')
                ->first();
                
            if ($accessControl) {
                $accessControl->control_config = [
                    'limit' => $validatedData['rate_limit'],
                    'window' => $validatedData['rate_window'],
                ];
                $accessControl->save();
            }
            
            DB::commit();
            
            return redirect()->route('admin.api.interfaces.index')
                ->with('success', 'API接口更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API接口更新失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'API接口更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除API接口
     *
     * @param ApiInterface $interface
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ApiInterface $interface)
    {
        try {
            DB::beginTransaction();
            
            // 删除关联的访问控制
            $interface->accessControls()->delete();
            
            // 删除接口
            $interface->delete();
            
            DB::commit();
            
            return redirect()->route('admin.api.interfaces.index')
                ->with('success', 'API接口删除成功');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API接口删除失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'API接口删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 导入API接口
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:json,csv',
        ]);
        
        try {
            $file = $request->file('import_file');
            $extension = $file->getClientOriginalExtension();
            
            if ($extension === 'json') {
                $content = json_decode(file_get_contents($file->getPathname()), true);
                
                if (!is_array($content)) {
                    return redirect()->back()->with('error', 'JSON格式无效');
                }
                
                DB::beginTransaction();
                
                $importCount = 0;
                foreach ($content as $item) {
                    // 验证必要字段
                    if (!isset($item['name'], $item['path'], $item['method'], $item['version'])) {
                        continue;
                    }
                    
                    // 检查是否已存在
                    $exists = ApiInterface::where('path', $item['path'])
                        ->where('method', $item['method'])
                        ->where('version', $item['version'])
                        ->exists();
                        
                    if ($exists) {
                        continue;
                    }
                    
                    $interface = new ApiInterface();
                    $interface->name = $item['name'];
                    $interface->path = $item['path'];
                    $interface->method = $item['method'];
                    $interface->version = $item['version'] ?? 'v1';
                    $interface->category = $item['category'] ?? 'general';
                    $interface->description = $item['description'] ?? null;
                    $interface->rate_limit = $item['rate_limit'] ?? 100;
                    $interface->rate_window = $item['rate_window'] ?? 60;
                    $interface->requires_auth = $item['requires_auth'] ?? true;
                    $interface->required_role = $item['required_role'] ?? null;
                    $interface->status = $item['status'] ?? 'active';
                    $interface->save();
                    
                    // 创建默认的访问控制
                    ApiAccessControl::create([
                        'interface_id' => $interface->id,
                        'control_type' => 'rate_limit',
                        'control_config' => [
                            'limit' => $interface->rate_limit,
                            'window' => $interface->rate_window,
                        ],
                        'status' => 'active',
                        'description' => '默认速率限制',
                        'created_by' => auth('admin')->id(),
                    ]);
                    
                    $importCount++;
                }
                
                DB::commit();
                
                return redirect()->route('admin.api.interfaces.index')
                    ->with('success', "成功导入 {$importCount} 个API接口");
            } elseif ($extension === 'csv') {
                // CSV导入逻辑
                $handle = fopen($file->getPathname(), 'r');
                $headers = fgetcsv($handle);
                
                // 验证CSV头部
                $requiredHeaders = ['name', 'path', 'method', 'version'];
                $missingHeaders = array_diff($requiredHeaders, $headers);
                
                if (!empty($missingHeaders)) {
                    return redirect()->back()->with('error', '缺少必要的CSV列: ' . implode(', ', $missingHeaders));
                }
                
                DB::beginTransaction();
                
                $importCount = 0;
                while (($data = fgetcsv($handle)) !== false) {
                    $item = array_combine($headers, $data);
                    
                    // 检查是否已存在
                    $exists = ApiInterface::where('path', $item['path'])
                        ->where('method', $item['method'])
                        ->where('version', $item['version'])
                        ->exists();
                        
                    if ($exists) {
                        continue;
                    }
                    
                    $interface = new ApiInterface();
                    $interface->name = $item['name'];
                    $interface->path = $item['path'];
                    $interface->method = $item['method'];
                    $interface->version = $item['version'] ?? 'v1';
                    $interface->category = $item['category'] ?? 'general';
                    $interface->description = $item['description'] ?? null;
                    $interface->rate_limit = $item['rate_limit'] ?? 100;
                    $interface->rate_window = $item['rate_window'] ?? 60;
                    $interface->requires_auth = isset($item['requires_auth']) ? ($item['requires_auth'] === 'true' || $item['requires_auth'] === '1') : true;
                    $interface->required_role = $item['required_role'] ?? null;
                    $interface->status = $item['status'] ?? 'active';
                    $interface->save();
                    
                    // 创建默认的访问控制
                    ApiAccessControl::create([
                        'interface_id' => $interface->id,
                        'control_type' => 'rate_limit',
                        'control_config' => [
                            'limit' => $interface->rate_limit,
                            'window' => $interface->rate_window,
                        ],
                        'status' => 'active',
                        'description' => '默认速率限制',
                        'created_by' => auth('admin')->id(),
                    ]);
                    
                    $importCount++;
                }
                
                fclose($handle);
                DB::commit();
                
                return redirect()->route('admin.api.interfaces.index')
                    ->with('success', "成功导入 {$importCount} 个API接口");
            }
            
            return redirect()->back()->with('error', '不支持的文件格式');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API接口导入失败: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'API接口导入失败: ' . $e->getMessage());
        }
    }

    /**
     * 导出API接口
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $query = ApiInterface::query();
        
        // 搜索条件
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('path', 'like', "%{$keyword}%");
            });
        }
        
        if ($request->filled('method')) {
            $query->where('method', $request->input('method'));
        }
        
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        $interfaces = $query->orderBy('category')
            ->orderBy('path')
            ->get();
        
        // 导出为JSON
        $exportData = $interfaces->map(function ($interface) {
            return [
                'id' => $interface->id,
                'name' => $interface->name,
                'path' => $interface->path,
                'method' => $interface->method,
                'version' => $interface->version,
                'category' => $interface->category,
                'description' => $interface->description,
                'rate_limit' => $interface->rate_limit,
                'rate_window' => $interface->rate_window,
                'requires_auth' => $interface->requires_auth,
                'required_role' => $interface->required_role,
                'status' => $interface->status,
                'created_at' => $interface->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $interface->updated_at->format('Y-m-d H:i:s'),
            ];
        });
        
        $jsonContent = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'api_interfaces_' . date('YmdHis') . '.json';
        
        return response($jsonContent)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
