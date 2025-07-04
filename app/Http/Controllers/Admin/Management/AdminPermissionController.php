<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Management\AdminPermission;
use App\Models\Admin\Management\AdminPermissionGroup;
use App\Models\Admin\Management\AdminOperationLog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminPermissionController extends Controller
{
    /**
     * 显示权限列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = AdminPermission::query();
        
        // 搜索条件
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('display_name', 'like', "%{$keyword}%")
                  ->orWhere('module', 'like', "%{$keyword}%");
            });
        }
        
        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }
        
        $permissions = $query->orderBy('module')
            ->orderBy('sort_order')
            ->paginate(20);
        
        // 获取所有模块
        $modules = AdminPermission::distinct('module')->pluck('module');
        
        return view('admin.management.permissions.index', compact('permissions', 'modules'));
    }

    /**
     * 显示创建权限表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // 获取所有模块
        $modules = AdminPermission::distinct('module')->pluck('module');
        return view('admin.management.permissions.create', compact('modules'));
    }

    /**
     * 保存新权限
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:50|unique:admin_permissions',
            'display_name' => 'required|string|max:100',
            'module' => 'required|string|max:50',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);
        
        try {
            DB::beginTransaction();
            
            $permission = new AdminPermission();
            $permission->name = $validatedData['name'];
            $permission->display_name = $validatedData['display_name'];
            $permission->module = $validatedData['module'];
            $permission->description = $validatedData['description'] ?? null;
            $permission->sort_order = $validatedData['sort_order'] ?? 0;
            $permission->save();
            
            DB::commit();
            
            return redirect()->route('admin.permissions.index')
                ->with('success', '权限创建成功');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('权限创建失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', '权限创建失败: ' . $e->getMessage());
        }
    }

    /**
     * 显示编辑权限表单
     *
     * @param AdminPermission $permission
     * @return \Illuminate\View\View
     */
    public function edit(AdminPermission $permission)
    {
        // 获取所有模块
        $modules = AdminPermission::distinct('module')->pluck('module');
        return view('admin.management.permissions.edit', compact('permission', 'modules'));
    }

    /**
     * 更新权限
     *
     * @param Request $request
     * @param AdminPermission $permission
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, AdminPermission $permission)
    {
        $validatedData = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:50', 
                Rule::unique('admin_permissions')->ignore($permission->id)
            ],
            'display_name' => 'required|string|max:100',
            'module' => 'required|string|max:50',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);
        
        try {
            DB::beginTransaction();
            
            $permission->name = $validatedData['name'];
            $permission->display_name = $validatedData['display_name'];
            $permission->module = $validatedData['module'];
            $permission->description = $validatedData['description'] ?? null;
            $permission->sort_order = $validatedData['sort_order'] ?? 0;
            $permission->save();
            
            DB::commit();
            
            return redirect()->route('admin.permissions.index')
                ->with('success', '权限更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('权限更新失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', '权限更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除权限
     *
     * @param AdminPermission $permission
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(AdminPermission $permission)
    {
        try {
            // 检查权限是否被权限组使用
            $usedByGroups = $permission->permissionGroups()->count();
            if ($usedByGroups > 0) {
                return redirect()->back()
                    ->with('error', '该权限已被权限组使用，无法删除');
            }
            
            // 检查权限是否被角色使用
            $usedByRoles = DB::table('admin_roles')
                ->whereJsonContains('permissions', $permission->id)
                ->count();
            
            if ($usedByRoles > 0) {
                return redirect()->back()
                    ->with('error', '该权限已被角色使用，无法删除');
            }
            
            $permission->delete();
            
            return redirect()->route('admin.permissions.index')
                ->with('success', '权限删除成功');
        } catch (\Exception $e) {
            Log::error('权限删除失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', '权限删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 显示权限组列表
     *
     * @return \Illuminate\View\View
     */
    public function groupIndex()
    {
        $groups = AdminPermissionGroup::orderBy('sort_order')->paginate(15);
        return view('admin.management.permissions.groups.index', compact('groups'));
    }

    /**
     * 显示创建权限组表单
     *
     * @return \Illuminate\View\View
     */
    public function groupCreate()
    {
        $permissions = AdminPermission::orderBy('module')->orderBy('sort_order')->get();
        $groupedPermissions = $permissions->groupBy('module');
        
        return view('admin.management.permissions.groups.create', compact('groupedPermissions'));
    }

    /**
     * 保存新权限组
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function groupStore(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:50|unique:admin_permission_groups',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:admin_permissions,id',
        ]);
        
        try {
            DB::beginTransaction();
            
            $group = new AdminPermissionGroup();
            $group->name = $validatedData['name'];
            $group->display_name = $validatedData['display_name'];
            $group->description = $validatedData['description'] ?? null;
            $group->sort_order = $validatedData['sort_order'] ?? 0;
            $group->save();
            
            // 关联权限
            $group->permissions()->attach($validatedData['permissions']);
            
            DB::commit();
            
            return redirect()->route('admin.permission.groups')
                ->with('success', '权限组创建成功');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('权限组创建失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', '权限组创建失败: ' . $e->getMessage());
        }
    }

    /**
     * 显示编辑权限组表单
     *
     * @param AdminPermissionGroup $group
     * @return \Illuminate\View\View
     */
    public function groupEdit($group)
    {
        $group = AdminPermissionGroup::findOrFail($group);
        $permissions = AdminPermission::orderBy('module')->orderBy('sort_order')->get();
        $groupedPermissions = $permissions->groupBy('module');
        
        // 获取当前权限组的权限ID
        $selectedPermissions = $group->permissions->pluck('id')->toArray();
        
        return view('admin.management.permissions.groups.edit', compact('group', 'groupedPermissions', 'selectedPermissions'));
    }

    /**
     * 更新权限组
     *
     * @param Request $request
     * @param int $group
     * @return \Illuminate\Http\RedirectResponse
     */
    public function groupUpdate(Request $request, $group)
    {
        $group = AdminPermissionGroup::findOrFail($group);
        
        $validatedData = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:50', 
                Rule::unique('admin_permission_groups')->ignore($group->id)
            ],
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:admin_permissions,id',
        ]);
        
        try {
            DB::beginTransaction();
            
            $group->name = $validatedData['name'];
            $group->display_name = $validatedData['display_name'];
            $group->description = $validatedData['description'] ?? null;
            $group->sort_order = $validatedData['sort_order'] ?? 0;
            $group->save();
            
            // 更新权限关联
            $group->permissions()->sync($validatedData['permissions']);
            
            DB::commit();
            
            return redirect()->route('admin.permission.groups')
                ->with('success', '权限组更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('权限组更新失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', '权限组更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除权限组
     *
     * @param int $group
     * @return \Illuminate\Http\RedirectResponse
     */
    public function groupDestroy($group)
    {
        $group = AdminPermissionGroup::findOrFail($group);
        
        try {
            DB::beginTransaction();
            
            // 删除权限关联
            $group->permissions()->detach();
            
            // 删除权限组
            $group->delete();
            
            DB::commit();
            
            return redirect()->route('admin.permission.groups')
                ->with('success', '权限组删除成功');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('权限组删除失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', '权限组删除失败: ' . $e->getMessage());
        }
    }
}
