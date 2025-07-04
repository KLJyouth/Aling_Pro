<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket\TicketDepartment;
use Illuminate\Support\Facades\Validator;

/**
 * 工单部门控制器
 * 
 * 处理工单部门相关的请求
 */
class TicketDepartmentController extends Controller
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->middleware(["auth", "role:admin"]);
    }
    
    /**
     * 显示部门列表
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $departments = TicketDepartment::with("children")->topLevel()->sorted()->get();
        
        return view("ticket.departments.index", compact("departments"));
    }
    
    /**
     * 显示创建部门表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $departments = TicketDepartment::topLevel()->sorted()->get();
        
        return view("ticket.departments.create", compact("departments"));
    }
    
    /**
     * 存储新部门
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 验证请求
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "description" => "nullable|string",
            "parent_id" => "nullable|exists:ticket_departments,id",
            "is_active" => "boolean",
            "sort_order" => "nullable|integer",
            "auto_assign_to" => "nullable|exists:users,id",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 创建部门
        TicketDepartment::create([
            "name" => $request->input("name"),
            "description" => $request->input("description"),
            "parent_id" => $request->input("parent_id"),
            "is_active" => $request->input("is_active", true),
            "sort_order" => $request->input("sort_order", 0),
            "auto_assign_to" => $request->input("auto_assign_to"),
        ]);
        
        return redirect()->route("ticket.departments.index")
            ->with("success", "部门创建成功");
    }
    
    /**
     * 显示编辑部门表单
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $department = TicketDepartment::findOrFail($id);
        $departments = TicketDepartment::where("id", "!=", $id)
            ->whereNull("parent_id")
            ->orWhere("parent_id", "!=", $id)
            ->sorted()
            ->get();
        
        return view("ticket.departments.edit", compact("department", "departments"));
    }
    
    /**
     * 更新部门
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // 验证请求
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "description" => "nullable|string",
            "parent_id" => "nullable|exists:ticket_departments,id",
            "is_active" => "boolean",
            "sort_order" => "nullable|integer",
            "auto_assign_to" => "nullable|exists:users,id",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $department = TicketDepartment::findOrFail($id);
        
        // 检查是否将部门设为自己的子部门
        if ($request->input("parent_id") == $id) {
            return redirect()->back()
                ->with("error", "部门不能设为自己的子部门")
                ->withInput();
        }
        
        // 更新部门
        $department->update([
            "name" => $request->input("name"),
            "description" => $request->input("description"),
            "parent_id" => $request->input("parent_id"),
            "is_active" => $request->input("is_active", true),
            "sort_order" => $request->input("sort_order", 0),
            "auto_assign_to" => $request->input("auto_assign_to"),
        ]);
        
        return redirect()->route("ticket.departments.index")
            ->with("success", "部门更新成功");
    }
    
    /**
     * 删除部门
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $department = TicketDepartment::findOrFail($id);
        
        // 检查是否有子部门
        if ($department->children()->count() > 0) {
            return redirect()->back()
                ->with("error", "请先删除所有子部门");
        }
        
        // 检查是否有关联的工单
        if ($department->tickets()->count() > 0) {
            return redirect()->back()
                ->with("error", "此部门下有工单，无法删除");
        }
        
        // 删除部门
        $department->delete();
        
        return redirect()->route("ticket.departments.index")
            ->with("success", "部门删除成功");
    }
}
