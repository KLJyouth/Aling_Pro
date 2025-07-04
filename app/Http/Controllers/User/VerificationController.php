<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\User\VerificationService;
use App\Models\User\UserVerification;
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
        $this->middleware("auth");
    }
    
    /**
     * 显示认证列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $verifications = $this->verificationService->getUserVerifications(Auth::id());
        
        return view("user.verifications.index", [
            "verifications" => $verifications,
        ]);
    }
    
    /**
     * 显示创建认证表单
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $type = $request->input("type", "personal");
        
        // 检查是否已有同类型的认证申请
        $existingVerification = UserVerification::byUser(Auth::id())
            ->byType($type)
            ->whereIn("status", ["pending", "approved"])
            ->first();
            
        if ($existingVerification) {
            if ($existingVerification->status === "approved") {
                return redirect()->route("user.verifications.index")
                    ->with("error", "您已通过{$existingVerification->type_name}认证");
            } else {
                return redirect()->route("user.verifications.show", $existingVerification->id)
                    ->with("warning", "您已提交{$existingVerification->type_name}认证申请，请等待审核");
            }
        }
        
        return view("user.verifications.create", [
            "type" => $type,
            "types" => UserVerification::$types,
        ]);
    }
    
    /**
     * 存储新认证申请
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $type = $request->input("type");
        
        // 根据认证类型设置不同的验证规则
        $rules = [
            "type" => "required|in:" . implode(",", array_keys(UserVerification::$types)),
            "description" => "nullable|string|max:1000",
        ];
        
        if ($type === "personal") {
            $rules = array_merge($rules, [
                "name" => "required|string|max:255",
                "identifier" => "required|string|max:255", // 身份证号
                "contact_phone" => "required|string|max:20",
                "id_card_front" => "required|file|image|max:5120", // 身份证正面
                "id_card_back" => "required|file|image|max:5120", // 身份证背面
            ]);
        } elseif ($type === "business") {
            $rules = array_merge($rules, [
                "name" => "required|string|max:255", // 企业名称
                "identifier" => "required|string|max:255", // 统一社会信用代码
                "contact_name" => "required|string|max:255",
                "contact_phone" => "required|string|max:20",
                "contact_email" => "nullable|email|max:255",
                "business_license" => "required|file|max:10240", // 营业执照
                "organization_code" => "nullable|file|max:10240", // 组织机构代码证
            ]);
        } elseif ($type === "team") {
            $rules = array_merge($rules, [
                "name" => "required|string|max:255", // 团队名称
                "contact_name" => "required|string|max:255",
                "contact_phone" => "required|string|max:20",
                "contact_email" => "nullable|email|max:255",
                "team_intro" => "required|file|max:10240", // 团队介绍
                "authorization_letter" => "required|file|max:10240", // 授权书
            ]);
        } elseif ($type === "government" || $type === "education") {
            $rules = array_merge($rules, [
                "name" => "required|string|max:255", // 机构名称
                "identifier" => "required|string|max:255", // 机构代码
                "contact_name" => "required|string|max:255",
                "contact_phone" => "required|string|max:20",
                "contact_email" => "nullable|email|max:255",
                "qualification_certificate" => "required|file|max:10240", // 资质证书
                "authorization_letter" => "required|file|max:10240", // 授权书
            ]);
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 准备基础数据
        $data = $request->only([
            "type",
            "name",
            "identifier",
            "contact_name",
            "contact_phone",
            "contact_email",
            "description",
        ]);
        
        // 准备文件
        $documents = [];
        
        if ($type === "personal") {
            $documents = [
                "id_card_front" => $request->file("id_card_front"),
                "id_card_back" => $request->file("id_card_back"),
            ];
        } elseif ($type === "business") {
            $documents = [
                "business_license" => $request->file("business_license"),
            ];
            
            if ($request->hasFile("organization_code")) {
                $documents["organization_code"] = $request->file("organization_code");
            }
        } elseif ($type === "team") {
            $documents = [
                "team_intro" => $request->file("team_intro"),
                "authorization_letter" => $request->file("authorization_letter"),
            ];
        } elseif ($type === "government" || $type === "education") {
            $documents = [
                "qualification_certificate" => $request->file("qualification_certificate"),
                "authorization_letter" => $request->file("authorization_letter"),
            ];
        }
        
        try {
            $verification = $this->verificationService->submitVerification(Auth::id(), $data, $documents);
            
            return redirect()->route("user.verifications.show", $verification->id)
                ->with("success", "认证申请提交成功，请等待审核");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with("error", $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * 显示认证详情
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $verification = UserVerification::where("id", $id)
            ->where("user_id", Auth::id())
            ->with("verificationDocuments")
            ->firstOrFail();
            
        return view("user.verifications.show", [
            "verification" => $verification,
        ]);
    }
    
    /**
     * 取消认证申请
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function cancel($id)
    {
        $verification = UserVerification::where("id", $id)
            ->where("user_id", Auth::id())
            ->where("status", "pending")
            ->firstOrFail();
            
        $verification->delete();
        
        return redirect()->route("user.verifications.index")
            ->with("success", "认证申请已取消");
    }
    
    /**
     * 重新提交被拒绝的认证申请
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function resubmit($id)
    {
        $verification = UserVerification::where("id", $id)
            ->where("user_id", Auth::id())
            ->where("status", "rejected")
            ->firstOrFail();
            
        $type = $verification->type;
        
        // 删除旧的认证申请
        $verification->delete();
        
        return redirect()->route("user.verifications.create", ["type" => $type])
            ->with("info", "请重新填写认证信息");
    }
}
