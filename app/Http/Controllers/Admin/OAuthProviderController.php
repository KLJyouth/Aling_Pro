<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OAuth\Provider;
use App\Models\OAuth\UserAccount;
use App\Models\OAuth\OAuthLog;
use App\Services\AI\AuditService;

class OAuthProviderController extends Controller
{
    protected $auditService;
    
    /**
     * 构造函数
     *
     * @param AuditService $auditService
     */
    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }
    
    /**
     * 显示OAuth提供商列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $providers = Provider::withCount("userAccounts")->get();
        
        return view("admin.oauth.providers.index", [
            "providers" => $providers,
        ]);
    }
    
    /**
     * 显示创建OAuth提供商表单
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("admin.oauth.providers.create");
    }
    
    /**
     * 存储新的OAuth提供商
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "name" => "required|string|max:255",
            "identifier" => "required|string|max:255|unique:oauth_providers,identifier",
            "icon" => "required|string|max:255",
            "description" => "nullable|string",
            "is_active" => "boolean",
            "client_id" => "nullable|string|max:255",
            "client_secret" => "nullable|string",
            "redirect_url" => "nullable|string|max:255",
            "auth_url" => "nullable|string|max:255",
            "token_url" => "nullable|string|max:255",
            "user_info_url" => "nullable|string|max:255",
            "scopes" => "nullable|array",
            "config" => "nullable|array",
        ]);
        
        $provider = Provider::create($validated);
        
        // 记录审计日志
        $this->auditService->logCreate("oauth_provider", $provider->id, $validated);
        
        return redirect()->route("admin.oauth.providers.index")
            ->with("success", "OAuth提供商创建成功");
    }
    
    /**
     * 显示指定的OAuth提供商
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $provider = Provider::withCount("userAccounts")->findOrFail($id);
        
        // 获取最近的日志
        $logs = OAuthLog::where("provider_id", $id)
            ->with("user")
            ->latest()
            ->take(10)
            ->get();
        
        // 获取统计数据
        $stats = [
            "total_logins" => OAuthLog::where("provider_id", $id)
                ->where("action", "login")
                ->where("status", "success")
                ->count(),
            "total_registrations" => OAuthLog::where("provider_id", $id)
                ->where("action", "register")
                ->where("status", "success")
                ->count(),
            "total_links" => OAuthLog::where("provider_id", $id)
                ->where("action", "link")
                ->where("status", "success")
                ->count(),
            "total_unlinks" => OAuthLog::where("provider_id", $id)
                ->where("action", "unlink")
                ->where("status", "success")
                ->count(),
            "total_failures" => OAuthLog::where("provider_id", $id)
                ->where("status", "failed")
                ->count(),
        ];
        
        // 记录审计日志
        $this->auditService->logView("oauth_provider", $id);
        
        return view("admin.oauth.providers.show", [
            "provider" => $provider,
            "logs" => $logs,
            "stats" => $stats,
        ]);
    }
    
    /**
     * 显示编辑OAuth提供商表单
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $provider = Provider::findOrFail($id);
        
        return view("admin.oauth.providers.edit", [
            "provider" => $provider,
        ]);
    }
    
    /**
     * 更新指定的OAuth提供商
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $provider = Provider::findOrFail($id);
        
        $validated = $request->validate([
            "name" => "required|string|max:255",
            "identifier" => "required|string|max:255|unique:oauth_providers,identifier," . $id,
            "icon" => "required|string|max:255",
            "description" => "nullable|string",
            "is_active" => "boolean",
            "client_id" => "nullable|string|max:255",
            "client_secret" => "nullable|string",
            "redirect_url" => "nullable|string|max:255",
            "auth_url" => "nullable|string|max:255",
            "token_url" => "nullable|string|max:255",
            "user_info_url" => "nullable|string|max:255",
            "scopes" => "nullable|array",
            "config" => "nullable|array",
        ]);
        
        // 如果未提供客户端密钥，保留原有值
        if (empty($validated["client_secret"])) {
            unset($validated["client_secret"]);
        }
        
        $oldValues = $provider->toArray();
        $provider->update($validated);
        
        // 记录审计日志
        $this->auditService->logUpdate("oauth_provider", $id, $oldValues, $provider->toArray());
        
        return redirect()->route("admin.oauth.providers.index")
            ->with("success", "OAuth提供商更新成功");
    }
    
    /**
     * 删除指定的OAuth提供商
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $provider = Provider::findOrFail($id);
        
        // 检查是否有关联的用户账号
        $userAccountsCount = UserAccount::where("provider_id", $id)->count();
        
        if ($userAccountsCount > 0) {
            return redirect()->route("admin.oauth.providers.index")
                ->with("error", "无法删除该提供商，因为有{$userAccountsCount}个用户账号与其关联");
        }
        
        $oldValues = $provider->toArray();
        $provider->delete();
        
        // 记录审计日志
        $this->auditService->logDelete("oauth_provider", $id, $oldValues);
        
        return redirect()->route("admin.oauth.providers.index")
            ->with("success", "OAuth提供商删除成功");
    }
    
    /**
     * 显示OAuth日志列表
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function logs(Request $request)
    {
        $query = OAuthLog::with(["provider", "user"]);
        
        // 筛选条件
        if ($request->has("provider_id")) {
            $query->where("provider_id", $request->provider_id);
        }
        
        if ($request->has("user_id")) {
            $query->where("user_id", $request->user_id);
        }
        
        if ($request->has("action")) {
            $query->where("action", $request->action);
        }
        
        if ($request->has("status")) {
            $query->where("status", $request->status);
        }
        
        if ($request->has("start_date")) {
            $query->whereDate("created_at", ">=", $request->start_date);
        }
        
        if ($request->has("end_date")) {
            $query->whereDate("created_at", "<=", $request->end_date);
        }
        
        // 排序
        $sortField = $request->get("sort_field", "created_at");
        $sortDirection = $request->get("sort_direction", "desc");
        $query->orderBy($sortField, $sortDirection);
        
        $logs = $query->paginate(15);
        
        return view("admin.oauth.logs.index", [
            "logs" => $logs,
            "providers" => Provider::all(),
        ]);
    }
    
    /**
     * 显示OAuth日志详情
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function showLog($id)
    {
        $log = OAuthLog::with(["provider", "user"])->findOrFail($id);
        
        return view("admin.oauth.logs.show", [
            "log" => $log,
        ]);
    }
    
    /**
     * 显示用户的OAuth账号列表
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function userAccounts(Request $request)
    {
        $query = UserAccount::with(["provider", "user"]);
        
        // 筛选条件
        if ($request->has("provider_id")) {
            $query->where("provider_id", $request->provider_id);
        }
        
        if ($request->has("user_id")) {
            $query->where("user_id", $request->user_id);
        }
        
        if ($request->has("email")) {
            $query->where("email", "like", "%" . $request->email . "%");
        }
        
        // 排序
        $sortField = $request->get("sort_field", "created_at");
        $sortDirection = $request->get("sort_direction", "desc");
        $query->orderBy($sortField, $sortDirection);
        
        $userAccounts = $query->paginate(15);
        
        return view("admin.oauth.user_accounts.index", [
            "userAccounts" => $userAccounts,
            "providers" => Provider::all(),
        ]);
    }
    
    /**
     * 显示OAuth账号详情
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function showUserAccount($id)
    {
        $userAccount = UserAccount::with(["provider", "user"])->findOrFail($id);
        
        // 获取该用户的所有OAuth账号
        $otherAccounts = UserAccount::where("user_id", $userAccount->user_id)
            ->where("id", "!=", $id)
            ->with("provider")
            ->get();
        
        // 获取该用户的日志
        $logs = OAuthLog::where("user_id", $userAccount->user_id)
            ->with("provider")
            ->latest()
            ->take(10)
            ->get();
        
        return view("admin.oauth.user_accounts.show", [
            "userAccount" => $userAccount,
            "otherAccounts" => $otherAccounts,
            "logs" => $logs,
        ]);
    }
}
