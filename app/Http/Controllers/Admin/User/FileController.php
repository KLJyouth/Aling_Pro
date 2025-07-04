<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\User\FileService;
use App\Models\User\UserFile;
use App\Models\User\UserFileCategory;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
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
        $this->middleware("auth:admin");
    }
    
    /**
     * 显示用户文件列表
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        $filters = $request->only([
            "category",
            "type",
            "search",
            "sort_field",
            "sort_direction",
            "per_page",
        ]);
        
        $files = $this->fileService->getUserFiles($userId, $filters);
        $categories = $this->fileService->getUserFileCategories($userId);
        
        return view("admin.users.files.index", [
            "user" => $user,
            "files" => $files,
            "categories" => $categories,
            "filters" => $filters,
        ]);
    }
    
    /**
     * 显示文件详情
     *
     * @param int $userId
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($userId, $id)
    {
        $user = User::findOrFail($userId);
        $file = UserFile::where("id", $id)
            ->where("user_id", $userId)
            ->firstOrFail();
            
        return view("admin.users.files.show", [
            "user" => $user,
            "file" => $file,
        ]);
    }
    
    /**
     * 显示编辑文件表单
     *
     * @param int $userId
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($userId, $id)
    {
        $user = User::findOrFail($userId);
        $file = UserFile::where("id", $id)
            ->where("user_id", $userId)
            ->firstOrFail();
            
        $categories = $this->fileService->getUserFileCategories($userId);
        
        return view("admin.users.files.edit", [
            "user" => $user,
            "file" => $file,
            "categories" => $categories,
        ]);
    }
    
    /**
     * 更新文件信息
     *
     * @param Request $request
     * @param int $userId
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $userId, $id)
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
            ->where("user_id", $userId)
            ->firstOrFail();
            
        $data = $request->only([
            "name",
            "category",
            "description",
        ]);
        
        $data["is_public"] = $request->has("is_public");
        
        $this->fileService->updateFile($id, $data);
        
        return redirect()->route("admin.users.files.show", [$userId, $id])
            ->with("success", "文件信息更新成功");
    }
    
    /**
     * 删除文件
     *
     * @param int $userId
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($userId, $id)
    {
        $file = UserFile::where("id", $id)
            ->where("user_id", $userId)
            ->firstOrFail();
            
        $this->fileService->deleteFile($id);
        
        return redirect()->route("admin.users.files.index", $userId)
            ->with("success", "文件删除成功");
    }
    
    /**
     * 下载文件
     *
     * @param int $userId
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function download($userId, $id)
    {
        $file = UserFile::where("id", $id)
            ->where("user_id", $userId)
            ->firstOrFail();
            
        // 增加下载次数
        $file->incrementDownloadCount();
        
        return Storage::disk("public")->download($file->path, $file->name);
    }
    
    /**
     * 显示分类管理页面
     *
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function categories($userId)
    {
        $user = User::findOrFail($userId);
        $categories = $this->fileService->getUserFileCategories($userId);
        
        return view("admin.users.files.categories", [
            "user" => $user,
            "categories" => $categories,
        ]);
    }
    
    /**
     * 存储新分类
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function storeCategory(Request $request, $userId)
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
        $exists = UserFileCategory::where("user_id", $userId)
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
        
        $this->fileService->createFileCategory($userId, $data);
        
        return redirect()->route("admin.users.files.categories", $userId)
            ->with("success", "分类创建成功");
    }
    
    /**
     * 更新分类
     *
     * @param Request $request
     * @param int $userId
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function updateCategory(Request $request, $userId, $id)
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
            ->where("user_id", $userId)
            ->firstOrFail();
            
        // 检查分类名称是否已存在（排除自身）
        $exists = UserFileCategory::where("user_id", $userId)
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
        
        return redirect()->route("admin.users.files.categories", $userId)
            ->with("success", "分类更新成功");
    }
    
    /**
     * 删除分类
     *
     * @param int $userId
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroyCategory($userId, $id)
    {
        $category = UserFileCategory::where("id", $id)
            ->where("user_id", $userId)
            ->firstOrFail();
            
        $this->fileService->deleteFileCategory($id);
        
        return redirect()->route("admin.users.files.categories", $userId)
            ->with("success", "分类删除成功");
    }
}
