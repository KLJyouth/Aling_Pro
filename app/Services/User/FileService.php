<?php

namespace App\Services\User;

use App\Models\User\UserFile;
use App\Models\User\UserFileCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    /**
     * 上传文件
     *
     * @param int $userId
     * @param UploadedFile $file
     * @param array $data
     * @return UserFile
     */
    public function uploadFile($userId, UploadedFile $file, array $data = [])
    {
        // 生成唯一文件名
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::uuid() . "." . $extension;
        
        // 确定存储路径
        $path = "users/{$userId}/files";
        
        // 存储文件
        $filePath = $file->storeAs($path, $fileName, "public");
        
        // 获取文件类型
        $fileType = $this->getFileType($file);
        
        // 创建文件记录
        return UserFile::create([
            "user_id" => $userId,
            "name" => $data["name"] ?? $file->getClientOriginalName(),
            "path" => $filePath,
            "type" => $fileType,
            "mime_type" => $file->getMimeType(),
            "size" => $file->getSize(),
            "category" => $data["category"] ?? null,
            "description" => $data["description"] ?? null,
            "is_public" => $data["is_public"] ?? false,
            "metadata" => $data["metadata"] ?? null,
        ]);
    }
    
    /**
     * 更新文件信息
     *
     * @param int $fileId
     * @param array $data
     * @return UserFile
     */
    public function updateFile($fileId, array $data)
    {
        $file = UserFile::findOrFail($fileId);
        
        $file->update($data);
        
        return $file;
    }
    
    /**
     * 删除文件
     *
     * @param int $fileId
     * @return bool
     */
    public function deleteFile($fileId)
    {
        $file = UserFile::findOrFail($fileId);
        
        // 删除存储的文件
        Storage::disk("public")->delete($file->path);
        
        // 删除数据库记录
        return $file->delete();
    }
    
    /**
     * 获取用户文件列表
     *
     * @param int $userId
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getUserFiles($userId, array $filters = [])
    {
        $query = UserFile::byUser($userId);
        
        // 应用过滤器
        if (isset($filters["category"])) {
            $query->byCategory($filters["category"]);
        }
        
        if (isset($filters["type"])) {
            $query->byType($filters["type"]);
        }
        
        if (isset($filters["search"])) {
            $search = $filters["search"];
            $query->where(function ($q) use ($search) {
                $q->where("name", "like", "%{$search}%")
                  ->orWhere("description", "like", "%{$search}%");
            });
        }
        
        // 排序
        $sortField = $filters["sort_field"] ?? "created_at";
        $sortDirection = $filters["sort_direction"] ?? "desc";
        
        $query->orderBy($sortField, $sortDirection);
        
        // 分页
        $perPage = $filters["per_page"] ?? 15;
        
        return $query->paginate($perPage);
    }
    
    /**
     * 获取用户文件分类列表
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserFileCategories($userId)
    {
        return UserFileCategory::byUser($userId)->ordered()->get();
    }
    
    /**
     * 创建文件分类
     *
     * @param int $userId
     * @param array $data
     * @return UserFileCategory
     */
    public function createFileCategory($userId, array $data)
    {
        return UserFileCategory::create([
            "user_id" => $userId,
            "name" => $data["name"],
            "icon" => $data["icon"] ?? null,
            "description" => $data["description"] ?? null,
            "sort_order" => $data["sort_order"] ?? 0,
        ]);
    }
    
    /**
     * 更新文件分类
     *
     * @param int $categoryId
     * @param array $data
     * @return UserFileCategory
     */
    public function updateFileCategory($categoryId, array $data)
    {
        $category = UserFileCategory::findOrFail($categoryId);
        
        $category->update($data);
        
        return $category;
    }
    
    /**
     * 删除文件分类
     *
     * @param int $categoryId
     * @return bool
     */
    public function deleteFileCategory($categoryId)
    {
        $category = UserFileCategory::findOrFail($categoryId);
        
        // 将该分类下的文件设为无分类
        UserFile::where("category", $category->name)
            ->where("user_id", $category->user_id)
            ->update(["category" => null]);
        
        // 删除分类
        return $category->delete();
    }
    
    /**
     * 获取文件类型
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function getFileType(UploadedFile $file)
    {
        $mime = $file->getMimeType();
        $extension = $file->getClientOriginalExtension();
        
        if (strpos($mime, "image/") === 0) {
            return "image";
        } elseif (strpos($mime, "video/") === 0) {
            return "video";
        } elseif (strpos($mime, "audio/") === 0) {
            return "audio";
        } elseif (strpos($mime, "text/") === 0) {
            return "text";
        } elseif (in_array($extension, ["pdf"])) {
            return "pdf";
        } elseif (in_array($extension, ["doc", "docx"])) {
            return "word";
        } elseif (in_array($extension, ["xls", "xlsx"])) {
            return "excel";
        } elseif (in_array($extension, ["ppt", "pptx"])) {
            return "powerpoint";
        } elseif (in_array($extension, ["zip", "rar", "7z", "tar", "gz"])) {
            return "archive";
        }
        
        return "other";
    }
}
