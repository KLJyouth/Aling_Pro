<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\User\FileService;
use App\Models\User\UserFile;
use App\Models\User\UserFileCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    protected $fileService;
    
    /**
     * 构造函数
     *
     * @param FileService $fileService
     */
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
        $this->middleware("auth");
    }
    
    /**
     * 显示文件列表
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            "category",
            "type",
            "search",
            "sort_field",
            "sort_direction",
            "per_page",
        ]);
        
        $files = $this->fileService->getUserFiles(Auth::id(), $filters);
        $categories = $this->fileService->getUserFileCategories(Auth::id());
        
        return view("user.files.index", [
            "files" => $files,
            "categories" => $categories,
            "filters" => $filters,
        ]);
    }
    
    /**
     * 显示上传文件表单
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = $this->fileService->getUserFileCategories(Auth::id());
        
        return view("user.files.create", [
            "categories" => $categories,
        ]);
    }
    
    /**
     * 存储上传的文件
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "file" => "required|file|max:10240", // 最大10MB
            "name" => "nullable|string|max:255",
            "category" => "nullable|string|max:255",
            "description" => "nullable|string|max:1000",
            "is_public" => "boolean",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $file = $request->file("file");
        
        $data = [
            "name" => $request->input("name") ?: $file->getClientOriginalName(),
            "category" => $request->input("category"),
            "description" => $request->input("description"),
            "is_public" => $request->has("is_public"),
        ];
        
        $this->fileService->uploadFile(Auth::id(), $file, $data);
        
        return redirect()->route("user.files.index")
            ->with("success", "文件上传成功");
    }
    
    /**
     * 显示文件详情
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $file = UserFile::where("id", $id)
            ->where("user_id", Auth::id())
            ->firstOrFail();
            
        return view("user.files.show", [
            "file" => $file,
        ]);
    }
    
    /**
     * 显示编辑文件表单
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $file = UserFile::where("id", $id)
            ->where("user_id", Auth::id())
            ->firstOrFail();
            
        $categories = $this->fileService->getUserFileCategories(Auth::id());
        
        return view("user.files.edit", [
            "file" => $file,
            "categories" => $categories,
        ]);
    }
    
    /**
     * 更新文件信息
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "category" => "nullable|string|max:255",
            "description" => "nullable|string|max:1000",
            "is_public" => "boolean",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $file = UserFile::where("id", $id)
            ->where("user_id", Auth::id())
            ->firstOrFail();
            
        $data = $request->only([
            "name",
            "category",
            "description",
        ]);
        
        $data["is_public"] = $request->has("is_public");
        
        $this->fileService->updateFile($id, $data);
        
        return redirect()->route("user.files.show", $id)
            ->with("success", "文件信息更新成功");
    }
    
    /**
     * 删除文件
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $file = UserFile::where("id", $id)
            ->where("user_id", Auth::id())
            ->firstOrFail();
            
        $this->fileService->deleteFile($id);
        
        return redirect()->route("user.files.index")
            ->with("success", "文件删除成功");
    }
    
    /**
     * 下载文件
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function download($id)
    {
        $file = UserFile::where("id", $id)
            ->where(function ($query) {
                $query->where("user_id", Auth::id())
                    ->orWhere("is_public", true);
            })
            ->firstOrFail();
            
        // 增加下载次数
        $file->incrementDownloadCount();
        
        return Storage::disk("public")->download($file->path, $file->name);
    }
    
    /**
     * 显示分类管理页面
     *
     * @return \Illuminate\Http\Response
     */
    public function categories()
    {
        $categories = $this->fileService->getUserFileCategories(Auth::id());
        
        return view("user.files.categories", [
            "categories" => $categories,
        ]);
    }
    
    /**
     * 存储新分类
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function storeCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "icon" => "nullable|string|max:255",
            "description" => "nullable|string|max:1000",
            "sort_order" => "nullable|integer",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 检查分类名称是否已存在
        $exists = UserFileCategory::where("user_id", Auth::id())
            ->where("name", $request->input("name"))
            ->exists();
            
        if ($exists) {
            return redirect()->back()
                ->withErrors(["name" => "分类名称已存在"])
                ->withInput();
        }
        
        $data = $request->only([
            "name",
            "icon",
            "description",
            "sort_order",
        ]);
        
        $this->fileService->createFileCategory(Auth::id(), $data);
        
        return redirect()->route("user.files.categories")
            ->with("success", "分类创建成功");
    }
    
    /**
     * 更新分类
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function updateCategory(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "icon" => "nullable|string|max:255",
            "description" => "nullable|string|max:1000",
            "sort_order" => "nullable|integer",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $category = UserFileCategory::where("id", $id)
            ->where("user_id", Auth::id())
            ->firstOrFail();
            
        // 检查分类名称是否已存在（排除自身）
        $exists = UserFileCategory::where("user_id", Auth::id())
            ->where("name", $request->input("name"))
            ->where("id", "!=", $id)
            ->exists();
            
        if ($exists) {
            return redirect()->back()
                ->withErrors(["name" => "分类名称已存在"])
                ->withInput();
        }
        
        $data = $request->only([
            "name",
            "icon",
            "description",
            "sort_order",
        ]);
        
        $this->fileService->updateFileCategory($id, $data);
        
        return redirect()->route("user.files.categories")
            ->with("success", "分类更新成功");
    }
    
    /**
     * 删除分类
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroyCategory($id)
    {
        $category = UserFileCategory::where("id", $id)
            ->where("user_id", Auth::id())
            ->firstOrFail();
            
        $this->fileService->deleteFileCategory($id);
        
        return redirect()->route("user.files.categories")
            ->with("success", "分类删除成功");
    }
}
