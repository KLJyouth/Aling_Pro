<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\Security\ApiControl\ApiInterface;
use App\Models\Security\ApiControl\ApiSdk;
use App\Models\Security\ApiControl\ApiSdkVersion;
use App\Services\Security\ApiSdkGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApiSdkController extends Controller
{
    /**
     * SDK生成服务
     *
     * @var ApiSdkGeneratorService
     */
    protected $sdkGenerator;

    /**
     * 构造函数
     *
     * @param ApiSdkGeneratorService $sdkGenerator
     */
    public function __construct(ApiSdkGeneratorService $sdkGenerator)
    {
        $this->sdkGenerator = $sdkGenerator;
    }

    /**
     * 显示SDK列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = ApiSdk::query();

        // 筛选
        if ($request->has('name') && !empty($request->name)) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('language') && !empty($request->language)) {
            $query->where('language', $request->language);
        }

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $sdks = $query->with(['currentVersion', 'interfaces', 'versions'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('admin.security.api.sdks.index', compact('sdks'));
    }

    /**
     * 显示创建SDK表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $interfaces = ApiInterface::where('status', 'active')->get();
        return view('admin.security.api.sdks.create', compact('interfaces'));
    }

    /**
     * 保存新创建的SDK
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:api_sdks,slug',
            'language' => 'required|string|in:php,python,javascript,java,csharp,go',
            'description' => 'nullable|string',
            'interfaces' => 'required|array',
            'interfaces.*' => 'exists:api_interfaces,id',
            'status' => 'required|string|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $sdk = new ApiSdk();
        $sdk->name = $request->name;
        $sdk->slug = $request->slug;
        $sdk->language = $request->language;
        $sdk->description = $request->description;
        $sdk->options = $request->options;
        $sdk->status = $request->status;
        $sdk->save();

        // 关联接口
        $sdk->interfaces()->sync($request->interfaces);

        return redirect()->route('admin.security.api.sdks.show', $sdk->id)
            ->with('success', 'SDK创建成功');
    }

    /**
     * 显示指定的SDK
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $sdk = ApiSdk::with(['interfaces', 'versions', 'currentVersion'])->findOrFail($id);
        $options = json_decode($sdk->options, true) ?? [];
        
        return view('admin.security.api.sdks.show', compact('sdk', 'options'));
    }

    /**
     * 显示编辑SDK表单
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $sdk = ApiSdk::findOrFail($id);
        $interfaces = ApiInterface::where('status', 'active')->get();
        $sdkInterfaces = $sdk->interfaces->pluck('id')->toArray();
        $options = json_decode($sdk->options, true) ?? [];
        
        return view('admin.security.api.sdks.edit', compact('sdk', 'interfaces', 'sdkInterfaces', 'options'));
    }

    /**
     * 更新指定的SDK
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $sdk = ApiSdk::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:api_sdks,slug,' . $id,
            'description' => 'nullable|string',
            'interfaces' => 'required|array',
            'interfaces.*' => 'exists:api_interfaces,id',
            'status' => 'required|string|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $sdk->name = $request->name;
        $sdk->slug = $request->slug;
        $sdk->description = $request->description;
        $sdk->options = $request->options;
        $sdk->status = $request->status;
        $sdk->save();

        // 关联接口
        $sdk->interfaces()->sync($request->interfaces);

        return redirect()->route('admin.security.api.sdks.show', $sdk->id)
            ->with('success', 'SDK更新成功');
    }

    /**
     * 删除指定的SDK
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $sdk = ApiSdk::findOrFail($id);
        
        // 删除所有版本的文件
        foreach ($sdk->versions as $version) {
            if (Storage::exists($version->file_path)) {
                Storage::delete($version->file_path);
            }
        }
        
        $sdk->delete();

        return redirect()->route('admin.security.api.sdks.index')
            ->with('success', 'SDK删除成功');
    }

    /**
     * 生成SDK
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generate(Request $request, $id)
    {
        $sdk = ApiSdk::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'version' => 'required|string|max:50',
            'changelog' => 'nullable|string',
            'interfaces' => 'required|array',
            'interfaces.*' => 'exists:api_interfaces,id',
            'is_current' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // 生成SDK
            $options = json_decode($sdk->options, true) ?? [];
            $zipFilePath = $this->sdkGenerator->generateSdk($sdk->id, $sdk->language, $request->interfaces, $options);
            
            // 创建版本记录
            $version = new ApiSdkVersion();
            $version->api_sdk_id = $sdk->id;
            $version->version = $request->version;
            $version->file_path = $zipFilePath;
            $version->changelog = $request->changelog;
            $version->is_current = $request->has('is_current');
            $version->save();
            
            // 如果设置为当前版本，更新其他版本
            if ($request->has('is_current')) {
                ApiSdkVersion::where('api_sdk_id', $sdk->id)
                    ->where('id', '!=', $version->id)
                    ->update(['is_current' => false]);
            }
            
            return redirect()->route('admin.security.api.sdks.show', $sdk->id)
                ->with('success', 'SDK生成成功')
                ->with('sdk_generated', true)
                ->with('sdk_version', $request->version);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', '生成SDK失败：' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * 下载SDK
     *
     * @param int $id
     * @param int $versionId
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($id, $versionId)
    {
        $sdk = ApiSdk::findOrFail($id);
        $version = ApiSdkVersion::where('api_sdk_id', $id)
            ->where('id', $versionId)
            ->firstOrFail();
        
        // 增加下载计数
        $version->incrementDownloadCount();
        
        $filePath = storage_path('app/' . $version->file_path);
        $fileName = $sdk->slug . '-' . $version->version . '.zip';
        
        return response()->download($filePath, $fileName);
    }

    /**
     * 设置当前版本
     *
     * @param int $id
     * @param int $versionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setCurrent($id, $versionId)
    {
        $sdk = ApiSdk::findOrFail($id);
        $version = ApiSdkVersion::where('api_sdk_id', $id)
            ->where('id', $versionId)
            ->firstOrFail();
        
        // 更新所有版本
        ApiSdkVersion::where('api_sdk_id', $id)->update(['is_current' => false]);
        
        // 设置当前版本
        $version->is_current = true;
        $version->save();
        
        return redirect()->route('admin.security.api.sdks.show', $id)
            ->with('success', '当前版本已更新');
    }

    /**
     * 删除版本
     *
     * @param int $id
     * @param int $versionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteVersion($id, $versionId)
    {
        $sdk = ApiSdk::findOrFail($id);
        $version = ApiSdkVersion::where('api_sdk_id', $id)
            ->where('id', $versionId)
            ->firstOrFail();
        
        // 如果是当前版本，不允许删除
        if ($version->is_current) {
            return redirect()->route('admin.security.api.sdks.show', $id)
                ->with('error', '不能删除当前版本');
        }
        
        // 删除文件
        if (Storage::exists($version->file_path)) {
            Storage::delete($version->file_path);
        }
        
        $version->delete();
        
        return redirect()->route('admin.security.api.sdks.show', $id)
            ->with('success', '版本已删除');
    }

    /**
     * 获取SDK的接口列表
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInterfaces($id)
    {
        $sdk = ApiSdk::findOrFail($id);
        $sdkInterfaceIds = $sdk->interfaces->pluck('id')->toArray();
        
        $interfaces = ApiInterface::where('status', 'active')
            ->get()
            ->map(function ($interface) use ($sdkInterfaceIds) {
                return [
                    'id' => $interface->id,
                    'name' => $interface->name,
                    'path' => $interface->path,
                    'selected' => in_array($interface->id, $sdkInterfaceIds)
                ];
            });
        
        return response()->json(['interfaces' => $interfaces]);
    }

    /**
     * 显示SDK文档
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function documentation($id)
    {
        $sdk = ApiSdk::with(['interfaces', 'currentVersion'])->findOrFail($id);
        $options = json_decode($sdk->options, true) ?? [];
        
        return view('admin.security.api.sdks.documentation', compact('sdk', 'options'));
    }
} 