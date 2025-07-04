<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Management\AdminUser;
use App\Models\Admin\Management\AdminRole;
use App\Models\Admin\Management\AdminLoginLog;
use App\Models\Admin\Management\AdminOperationLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /**
     * 显示管理员列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = AdminUser::with('role');
        
        // 搜索条件
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function($q) use ($keyword) {
                $q->where('username', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%")
                  ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }
        
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->input('role_id'));
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->input('risk_level'));
        }
        
        $users = $query->orderBy('id', 'desc')->paginate(15);
        $roles = AdminRole::where('status', 'active')->get();
        
        return view('admin.management.users.index', compact('users', 'roles'));
    }

    /**
     * 显示创建管理员表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $roles = AdminRole::where('status', 'active')->get();
        return view('admin.management.users.create', compact('roles'));
    }

    /**
     * 保存新管理员
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|max:50|unique:admin_users',
            'email' => 'required|email|max:100|unique:admin_users',
            'phone' => 'nullable|string|max:20|unique:admin_users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:admin_roles,id',
            'avatar' => 'nullable|image|max:2048',
            'status' => 'required|string|in:active,inactive',
        ]);
        
        try {
            DB::beginTransaction();
            
            $user = new AdminUser();
            $user->username = $validatedData['username'];
            $user->email = $validatedData['email'];
            $user->phone = $validatedData['phone'] ?? null;
            $user->password = Hash::make($validatedData['password']);
            $user->role_id = $validatedData['role_id'];
            $user->status = $validatedData['status'];
            
            // 处理头像上传
            if ($request->hasFile('avatar')) {
                $path = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $path;
            }
            
            $user->save();
            
            // 记录操作日志
            $this->logAdminOperation('admin_user', 'create', [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role_id' => $user->role_id
            ]);
            
            DB::commit();
            
            return redirect()->route('admin.users.index')
                ->with('success', '管理员创建成功');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('管理员创建失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', '管理员创建失败: ' . $e->getMessage());
        }
    }

    /**
     * 显示管理员详情
     *
     * @param AdminUser $user
     * @return \Illuminate\View\View
     */
    public function show(AdminUser $user)
    {
        $user->load('role');
        
        // 获取最近的登录记录
        $recentLogins = AdminLoginLog::where('admin_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // 获取最近的操作记录
        $recentOperations = AdminOperationLog::where('admin_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('admin.management.users.show', compact('user', 'recentLogins', 'recentOperations'));
    }

    /**
     * 显示编辑管理员表单
     *
     * @param AdminUser $user
     * @return \Illuminate\View\View
     */
    public function edit(AdminUser $user)
    {
        $roles = AdminRole::where('status', 'active')->get();
        return view('admin.management.users.edit', compact('user', 'roles'));
    }

    /**
     * 更新管理员信息
     *
     * @param Request $request
     * @param AdminUser $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, AdminUser $user)
    {
        $validatedData = $request->validate([
            'username' => [
                'required', 
                'string', 
                'max:50', 
                Rule::unique('admin_users')->ignore($user->id)
            ],
            'email' => [
                'required', 
                'email', 
                'max:100', 
                Rule::unique('admin_users')->ignore($user->id)
            ],
            'phone' => [
                'nullable', 
                'string', 
                'max:20', 
                Rule::unique('admin_users')->ignore($user->id)
            ],
            'role_id' => 'required|exists:admin_roles,id',
            'avatar' => 'nullable|image|max:2048',
            'status' => 'required|string|in:active,inactive',
        ]);
        
        try {
            DB::beginTransaction();
            
            $oldData = [
                'username' => $user->username,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'status' => $user->status
            ];
            
            $user->username = $validatedData['username'];
            $user->email = $validatedData['email'];
            $user->phone = $validatedData['phone'] ?? null;
            $user->role_id = $validatedData['role_id'];
            $user->status = $validatedData['status'];
            
            // 处理头像上传
            if ($request->hasFile('avatar')) {
                $path = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $path;
            }
            
            $user->save();
            
            // 记录操作日志
            $this->logAdminOperation('admin_user', 'update', [
                'id' => $user->id,
                'old_data' => $oldData,
                'new_data' => [
                    'username' => $user->username,
                    'email' => $user->email,
                    'role_id' => $user->role_id,
                    'status' => $user->status
                ]
            ]);
            
            DB::commit();
            
            return redirect()->route('admin.users.index')
                ->with('success', '管理员信息更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('管理员信息更新失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', '管理员信息更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 显示修改密码表单
     *
     * @param AdminUser $user
     * @return \Illuminate\View\View
     */
    public function editPassword(AdminUser $user)
    {
        return view('admin.management.users.edit_password', compact('user'));
    }

    /**
     * 更新管理员密码
     *
     * @param Request $request
     * @param AdminUser $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request, AdminUser $user)
    {
        $validatedData = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        try {
            $user->password = Hash::make($validatedData['password']);
            $user->save();
            
            // 记录操作日志
            $this->logAdminOperation('admin_user', 'update_password', [
                'id' => $user->id,
                'username' => $user->username
            ]);
            
            return redirect()->route('admin.users.index')
                ->with('success', '密码修改成功');
        } catch (\Exception $e) {
            Log::error('密码修改失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', '密码修改失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除管理员
     *
     * @param AdminUser $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(AdminUser $user)
    {
        // 不能删除自己
        if ($user->id === auth('admin')->id()) {
            return redirect()->back()
                ->with('error', '不能删除自己的账号');
        }
        
        try {
            $userData = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email
            ];
            
            $user->delete();
            
            // 记录操作日志
            $this->logAdminOperation('admin_user', 'delete', $userData);
            
            return redirect()->route('admin.users.index')
                ->with('success', '管理员删除成功');
        } catch (\Exception $e) {
            Log::error('管理员删除失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', '管理员删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 查看管理员登录日志
     *
     * @param AdminUser $user
     * @return \Illuminate\View\View
     */
    public function loginLogs(AdminUser $user)
    {
        $logs = AdminLoginLog::where('admin_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('admin.management.users.login_logs', compact('user', 'logs'));
    }

    /**
     * 查看管理员操作日志
     *
     * @param AdminUser $user
     * @return \Illuminate\View\View
     */
    public function operationLogs(AdminUser $user)
    {
        $logs = AdminOperationLog::where('admin_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('admin.management.users.operation_logs', compact('user', 'logs'));
    }
    
    /**
     * 记录管理员操作日志
     *
     * @param string $module
     * @param string $action
     * @param array $data
     * @return void
     */
    protected function logAdminOperation($module, $action, $data = [])
    {
        $request = request();
        
        AdminOperationLog::create([
            'admin_id' => auth('admin')->id(),
            'module' => $module,
            'action' => $action,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'request_data' => $data,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
    }
}
