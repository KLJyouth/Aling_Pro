<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket\TicketCategory;
use App\Models\Ticket\TicketDepartment;
use Illuminate\Support\Facades\Validator;

/**
 * 工单分类控制器
 * 
 * 处理工单分类相关的请求
 */
class TicketCategoryController extends Controller
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->middleware(["auth", "role:admin"]);
    }
    
    /**
     * 显示分类列表
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $categories = TicketCategory::with(["department", "children"])->topLevel()->sorted()->get();
        
        return view("ticket.categories.index", compact("categories"));
    }
    
    /**
     * 显示创建分类表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $departments = TicketDepartment::active()->sorted()->get();
        $categories = TicketCategory::topLevel()->sorted()->get();
        
        return view("ticket.categories.create", compact("departments", "categories"));
    }
    
    /**
     * 存储新分类
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
            "department_id" => "required|exists:ticket_departments,id",
            "parent_id" => "nullable|exists:ticket_categories,id",
            "is_active" => "boolean",
            "sort_order" => "nullable|integer",
            "auto_assign_to" => "nullable|exists:users,id",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 创建分类
        TicketCategory::create([
            "name" => $request->input("name"),
            "description" => $request->input("description"),
            "department_id" => $request->input("department_id"),
            "parent_id" => $request->input("parent_id"),
            "is_active" => $request->input("is_active", true),
            "sort_order" => $request->input("sort_order", 0),
            "auto_assign_to" => $request->input("auto_assign_to"),
        ]);
        
        return redirect()->route("ticket.categories.index")
            ->with("success", "分类创建成功");
    }
    
    /**
     * 显示编辑分类表单
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $category = TicketCategory::findOrFail($id);
        $departments = TicketDepartment::active()->sorted()->get();
        $categories = TicketCategory::where("id", "!=", $id)
            ->whereNull("parent_id")
            ->orWhere("parent_id", "!=", $id)
            ->sorted()
            ->get();
        
        return view("ticket.categories.edit", compact("category", "departments", "categories"));
    }
    
    /**
     * 更新分类
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
            "department_id" => "required|exists:ticket_departments,id",
            "parent_id" => "nullable|exists:ticket_categories,id",
            "is_active" => "boolean",
            "sort_order" => "nullable|integer",
            "auto_assign_to" => "nullable|exists:users,id",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $category = TicketCategory::findOrFail($id);
        
        // 检查是否将分类设为自己的子分类
        if ($request->input("parent_id") == $id) {
            return redirect()->back()
                ->with("error", "分类不能设为自己的子分类")
                ->withInput();
        }
        
        // 更新分类
        $category->update([
            "name" => $request->input("name"),
            "description" => $request->input("description"),
            "department_id" => $request->input("department_id"),
            "parent_id" => $request->input("parent_id"),
            "is_active" => $request->input("is_active", true),
            "sort_order" => $request->input("sort_order", 0),
            "auto_assign_to" => $request->input("auto_assign_to"),
        ]);
        
        return redirect()->route("ticket.categories.index")
            ->with("success", "分类更新成功");
    }
    
    /**
     * 删除分类
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $category = TicketCategory::findOrFail($id);
        
        // 检查是否有子分类
        if ($category->children()->count() > 0) {
            return redirect()->back()
                ->with("error", "请先删除所有子分类");
        }
        
        // 检查是否有关联的工单
        if ($category->tickets()->count() > 0) {
            return redirect()->back()
                ->with("error", "此分类下有工单，无法删除");
        }
        
        // 删除分类
        $category->delete();
        
        return redirect()->route("ticket.categories.index")
            ->with("success", "分类删除成功");
    }
    
    /**
     * 根据部门获取分类列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByDepartment(Request $request)
    {
        $departmentId = $request->input("department_id");
        
        if (!$departmentId) {
            return response()->json([]);
        }
        
        $categories = TicketCategory::where("department_id", $departmentId)
            ->active()
            ->sorted()
            ->get()
            ->map(function($category) {
                return [
                    "id" => $category->id,
                    "name" => $category->name,
                    "parent_id" => $category->parent_id,
                ];
            });
        
        return response()->json($categories);
    }
}
