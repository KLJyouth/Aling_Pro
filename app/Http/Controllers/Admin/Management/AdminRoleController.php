<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use App\Models\Admin\Management\AdminRole;
use App\Models\Admin\Management\AdminPermission;
use App\Models\Admin\Management\AdminPermissionGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminRoleController extends Controller
{
    /**
     * 显示角色列表
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $roles = AdminRole::orderBy('sort_order')->paginate(15);
        return view('admin.management.roles.index', compact('roles'));
    }

    /**
     * 显示创建角色表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $permissions = AdminPermission::orderBy('module')->orderBy('sort_order')->get();
        $permissionGroups = AdminPermissionGroup::with('permissions')->orderBy('sort_order')->get();
        
        // 按模块分组权限
        $groupedPermissions = $permissions->groupBy('module');
        
        return view('admin.management.roles.create', compact('groupedPermissions', 'permissionGroups'));
    }

    /**
     * 保存新角色
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:50|unique:admin_roles',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
            'sort_order' => 'nullable|integer',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:admin_permissions,id',
        ]);
        
        try {
            DB::beginTransaction();
            
            $role = new AdminRole();
            $role->name = $validatedData['name'];
            $role->display_name = $validatedData['display_name'];
            $role->description = $validatedData['description'] ?? null;
            $role->status = $validatedData['status'];
            $role->sort_order = $validatedData['sort_order'] ?? 0;
            
            // 保存权限
            if (isset($validatedData['permissions'])) {
                $role->permissions = $validatedData['permissions'];
            }
            
            $role->save();
            
            DB::commit();
            
            return redirect()->route('admin.roles.index')
                ->with('success', '角色创建成功');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('角色创建失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', '角色创建失败: ' . $e->getMessage());
        }
    }

    /**
     * 显示角色详情
     *
     * @param  \App\Models\Admin\Management\AdminRole  $role
     * @return \Illuminate\View\View
     */
    public function show(AdminRole $role)
    {
        $role->load('adminUsers');
        
        // 获取角色的权限
        $rolePermissions = [];
        if ($role->permissions) {
            $rolePermissions = AdminPermission::whereIn('id', $role->permissions)->get();
        }
        
        return view('admin.management.roles.show', compact('role', 'rolePermissions'));
    }

    /**
     * 显示编辑角色表单
     *
     * @param  \App\Models\Admin\Management\AdminRole  $role
     * @return \Illuminate\View\View
     */
    public function edit(AdminRole $role)
    {
        $permissions = AdminPermission::orderBy('module')->orderBy('sort_order')->get();
        $permissionGroups = AdminPermissionGroup::with('permissions')->orderBy('sort_order')->get();
        
        // 按模块分组权限
        $groupedPermissions = $permissions->groupBy('module');
        
        return view('admin.management.roles.edit', compact('role', 'groupedPermissions', 'permissionGroups'));
    }

    /**
     * 更新角色
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\Management\AdminRole  $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, AdminRole $role)
    {
        $validatedData = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:50', 
                Rule::unique('admin_roles')->ignore($role->id)
            ],
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
            'sort_order' => 'nullable|integer',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:admin_permissions,id',
        ]);
        
        try {
            DB::beginTransaction();
            
            $role->name = $validatedData['name'];
            $role->display_name = $validatedData['display_name'];
            $role->description = $validatedData['description'] ?? null;
            $role->status = $validatedData['status'];
            $role->sort_order = $validatedData['sort_order'] ?? 0;
            
            // 更新权限
            if (isset($validatedData['permissions'])) {
                $role->permissions = $validatedData['permissions'];
            } else {
                $role->permissions = null;
            }
            
            $role->save();
            
            DB::commit();
            
            return redirect()->route('admin.roles.index')
                ->with('success', '角色更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('角色更新失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', '角色更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除角色
     *
     * @param  \App\Models\Admin\Management\AdminRole  $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(AdminRole $role)
    {
        // 检查是否有管理员使用此角色
        $usersCount = $role->adminUsers()->count();
        if ($usersCount > 0) {
            return redirect()->back()
                ->with('error', "无法删除角色，有 {$usersCount} 个管理员正在使用此角色");
        }
        
        try {
            $role->delete();
            return redirect()->route('admin.roles.index')
                ->with('success', '角色删除成功');
        } catch (\Exception $e) {
            Log::error('角色删除失败: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', '角色删除失败: ' . $e->getMessage());
        }
    }
}
