<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\User\VerificationService;
use App\Models\User\UserVerification;
use App\Models\User\VerificationDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VerificationController extends Controller
{
    protected $verificationService;
    
    /**
     * 构造函数
     *
     * @param VerificationService $verificationService
     */
    public function __construct(VerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
        $this->middleware("auth:admin");
    }
    
    /**
     * 显示认证列表
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            "type",
            "status",
            "search",
            "sort_field",
            "sort_direction",
            "per_page",
        ]);
        
        $verifications = $this->verificationService->getAllVerifications($filters);
        $stats = $this->verificationService->getVerificationStats();
        
        return view("admin.users.verifications.index", [
            "verifications" => $verifications,
            "stats" => $stats,
            "filters" => $filters,
            "types" => UserVerification::$types,
            "statuses" => UserVerification::$statuses,
        ]);
    }
    
    /**
     * 显示待审核认证列表
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function pending(Request $request)
    {
        $filters = $request->only([
            "type",
            "search",
            "sort_field",
            "sort_direction",
            "per_page",
        ]);
        
        $verifications = $this->verificationService->getPendingVerifications($filters);
        
        return view("admin.users.verifications.pending", [
            "verifications" => $verifications,
            "filters" => $filters,
            "types" => UserVerification::$types,
        ]);
    }
    
    /**
     * 显示认证详情
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $verification = $this->verificationService->getVerificationDetails($id);
        
        return view("admin.users.verifications.show", [
            "verification" => $verification,
        ]);
    }
    
    /**
     * 审核认证申请
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function review(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "status" => "required|in:approved,rejected",
            "rejection_reason" => "required_if:status,rejected|nullable|string|max:1000",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $status = $request->input("status");
        $rejectionReason = $request->input("rejection_reason");
        
        try {
            $this->verificationService->reviewVerification(
                $id,
                $status,
                Auth::id(),
                $rejectionReason
            );
            
            $message = $status === "approved" ? "认证已通过" : "认证已拒绝";
            
            return redirect()->route("admin.users.verifications.index")
                ->with("success", $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with("error", $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * 下载认证文件
     *
     * @param int $documentId
     * @return \Illuminate\Http\Response
     */
    public function downloadDocument($documentId)
    {
        try {
            return $this->verificationService->downloadVerificationDocument($documentId);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with("error", "文件下载失败：" . $e->getMessage());
        }
    }
}
