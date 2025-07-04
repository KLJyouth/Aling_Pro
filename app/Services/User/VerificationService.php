<?php

namespace App\Services\User;

use App\Models\User\UserVerification;
use App\Models\User\VerificationDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VerificationService
{
    /**
     * 提交认证申请
     *
     * @param int $userId
     * @param array $data
     * @param array $documents
     * @return UserVerification
     */
    public function submitVerification($userId, array $data, array $documents = [])
    {
        // 检查是否已有同类型的认证申请
        $existingVerification = UserVerification::byUser($userId)
            ->byType($data["type"])
            ->whereIn("status", ["pending", "approved"])
            ->first();
            
        if ($existingVerification) {
            if ($existingVerification->status === "approved") {
                throw new \Exception("您已通过{$existingVerification->type_name}认证");
            } else {
                throw new \Exception("您已提交{$existingVerification->type_name}认证申请，请等待审核");
            }
        }
        
        // 创建认证记录
        $verification = UserVerification::create([
            "user_id" => $userId,
            "type" => $data["type"],
            "name" => $data["name"] ?? null,
            "identifier" => $data["identifier"] ?? null,
            "contact_name" => $data["contact_name"] ?? null,
            "contact_phone" => $data["contact_phone"] ?? null,
            "contact_email" => $data["contact_email"] ?? null,
            "description" => $data["description"] ?? null,
            "status" => "pending",
        ]);
        
        // 处理上传的文件
        if (!empty($documents)) {
            foreach ($documents as $type => $file) {
                $this->uploadVerificationDocument($verification->id, $type, $file);
            }
        }
        
        return $verification;
    }
    
    /**
     * 上传认证文件
     *
     * @param int $verificationId
     * @param string $type
     * @param UploadedFile $file
     * @param string|null $notes
     * @return VerificationDocument
     */
    public function uploadVerificationDocument($verificationId, $type, UploadedFile $file, $notes = null)
    {
        $verification = UserVerification::findOrFail($verificationId);
        
        // 生成唯一文件名
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::uuid() . "." . $extension;
        
        // 确定存储路径
        $path = "users/{$verification->user_id}/verifications/{$verificationId}";
        
        // 存储文件
        $filePath = $file->storeAs($path, $fileName, "private");
        
        // 创建文件记录
        return VerificationDocument::create([
            "verification_id" => $verificationId,
            "name" => $file->getClientOriginalName(),
            "path" => $filePath,
            "type" => $type,
            "mime_type" => $file->getMimeType(),
            "size" => $file->getSize(),
            "notes" => $notes,
        ]);
    }
    
    /**
     * 审核认证申请
     *
     * @param int $verificationId
     * @param string $status
     * @param int $adminId
     * @param string|null $rejectionReason
     * @return UserVerification
     */
    public function reviewVerification($verificationId, $status, $adminId, $rejectionReason = null)
    {
        $verification = UserVerification::findOrFail($verificationId);
        
        if ($verification->status !== "pending") {
            throw new \Exception("该认证申请已经被处理");
        }
        
        if ($status === "approved") {
            $verification->approve($adminId);
        } elseif ($status === "rejected") {
            if (!$rejectionReason) {
                throw new \Exception("拒绝认证时必须提供拒绝原因");
            }
            
            $verification->reject($adminId, $rejectionReason);
        } else {
            throw new \Exception("无效的状态");
        }
        
        return $verification;
    }
    
    /**
     * 获取用户认证列表
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserVerifications($userId)
    {
        return UserVerification::byUser($userId)
            ->with(["verificationDocuments", "verifier"])
            ->get();
    }
    
    /**
     * 获取待审核认证列表
     *
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPendingVerifications(array $filters = [])
    {
        $query = UserVerification::pending();
        
        // 应用过滤器
        if (isset($filters["type"])) {
            $query->byType($filters["type"]);
        }
        
        if (isset($filters["search"])) {
            $search = $filters["search"];
            $query->where(function ($q) use ($search) {
                $q->where("name", "like", "%{$search}%")
                  ->orWhere("identifier", "like", "%{$search}%")
                  ->orWhere("contact_name", "like", "%{$search}%")
                  ->orWhere("contact_phone", "like", "%{$search}%")
                  ->orWhere("contact_email", "like", "%{$search}%");
            });
        }
        
        // 排序
        $sortField = $filters["sort_field"] ?? "created_at";
        $sortDirection = $filters["sort_direction"] ?? "asc";
        
        $query->orderBy($sortField, $sortDirection);
        
        // 分页
        $perPage = $filters["per_page"] ?? 15;
        
        return $query->with(["user", "verificationDocuments"])
            ->paginate($perPage);
    }
    
    /**
     * 获取所有认证列表
     *
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllVerifications(array $filters = [])
    {
        $query = UserVerification::query();
        
        // 应用过滤器
        if (isset($filters["type"])) {
            $query->byType($filters["type"]);
        }
        
        if (isset($filters["status"])) {
            $query->byStatus($filters["status"]);
        }
        
        if (isset($filters["search"])) {
            $search = $filters["search"];
            $query->where(function ($q) use ($search) {
                $q->where("name", "like", "%{$search}%")
                  ->orWhere("identifier", "like", "%{$search}%")
                  ->orWhere("contact_name", "like", "%{$search}%")
                  ->orWhere("contact_phone", "like", "%{$search}%")
                  ->orWhere("contact_email", "like", "%{$search}%");
            });
        }
        
        // 排序
        $sortField = $filters["sort_field"] ?? "created_at";
        $sortDirection = $filters["sort_direction"] ?? "desc";
        
        $query->orderBy($sortField, $sortDirection);
        
        // 分页
        $perPage = $filters["per_page"] ?? 15;
        
        return $query->with(["user", "verifier", "verificationDocuments"])
            ->paginate($perPage);
    }
    
    /**
     * 获取认证详情
     *
     * @param int $verificationId
     * @return UserVerification
     */
    public function getVerificationDetails($verificationId)
    {
        return UserVerification::with(["user", "verifier", "verificationDocuments"])
            ->findOrFail($verificationId);
    }
    
    /**
     * 获取认证文件
     *
     * @param int $documentId
     * @return VerificationDocument
     */
    public function getVerificationDocument($documentId)
    {
        return VerificationDocument::findOrFail($documentId);
    }
    
    /**
     * 下载认证文件
     *
     * @param int $documentId
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadVerificationDocument($documentId)
    {
        $document = VerificationDocument::findOrFail($documentId);
        
        return Storage::disk("private")->download(
            $document->path,
            $document->name
        );
    }
    
    /**
     * 获取认证统计信息
     *
     * @return array
     */
    public function getVerificationStats()
    {
        return [
            "total" => UserVerification::count(),
            "pending" => UserVerification::pending()->count(),
            "approved" => UserVerification::approved()->count(),
            "rejected" => UserVerification::rejected()->count(),
            "by_type" => [
                "personal" => UserVerification::byType("personal")->count(),
                "business" => UserVerification::byType("business")->count(),
                "team" => UserVerification::byType("team")->count(),
                "government" => UserVerification::byType("government")->count(),
                "education" => UserVerification::byType("education")->count(),
            ],
        ];
    }
}
