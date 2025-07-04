<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;
use App\Models\Membership\MembershipLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MembershipLevelController extends Controller
{
    /**
     * 显示会员等级列表页面
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view("admin.membership.levels");
    }

    /**
     * 获取会员等级数据（用于DataTables）
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        $draw = $request->input("draw");
        $start = $request->input("start");
        $length = $request->input("length");
        $search = $request->input("search.value");
        $orderColumn = $request->input("order.0.column");
        $orderDir = $request->input("order.0.dir");
        
        $columns = [
            "id", "icon", "name", "code", "price", "duration_days", 
            "benefits", "status"
        ];
        
        $query = MembershipLevel::query();
        
        // 搜索
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where("name", "like", "%{$search}%")
                  ->orWhere("code", "like", "%{$search}%")
                  ->orWhere("description", "like", "%{$search}%");
            });
        }
        
        // 排序
        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        } else {
            $query->orderBy("sort_order", "asc")->orderBy("id", "asc");
        }
        
        // 总记录数
        $total = $query->count();
        
        // 分页
        $levels = $query->skip($start)->take($length)->get();
        
        $data = [];
        foreach ($levels as $level) {
            $data[] = [
                "id" => $level->id,
                "icon" => $level->icon,
                "name" => $level->name,
                "code" => $level->code,
                "price" => $level->price,
                "duration_days" => $level->duration_days,
                "benefits" => json_decode($level->benefits, true),
                "status" => $level->status
            ];
        }
        
        return response()->json([
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $data
        ]);
    }

    /**
     * 显示会员等级编辑页面
     *
     * @param int|null $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id = null)
    {
        if ($id) {
            $level = MembershipLevel::findOrFail($id);
        } else {
            $level = null;
        }
        
        return view("admin.membership.level_edit", compact("level"));
    }
    
    /**
     * 保存会员等级
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        // 验证输入
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:100",
            "code" => "required|string|max:50|regex:/^[a-zA-Z0-9_]+$/",
            "price" => "required|numeric|min:0",
            "duration_days" => "required|integer|min:1",
            "icon" => "nullable|image|max:1024", // 最大1MB
            "color" => "nullable|string|max:20",
            "benefits" => "nullable|array",
            "description" => "nullable|string|max:5000",
            "api_quota" => "nullable|integer|min:0",
            "ai_quota" => "nullable|integer|min:0",
            "storage_quota" => "nullable|integer|min:0",
            "bandwidth_quota" => "nullable|integer|min:0",
            "discount_percent" => "nullable|integer|min:0|max:100",
            "priority_support" => "nullable|boolean",
            "is_featured" => "nullable|boolean",
            "sort_order" => "nullable|integer|min:0",
            "status" => "required|in:0,1",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 获取或创建会员等级
        if ($request->has("id")) {
            $level = MembershipLevel::findOrFail($request->input("id"));
        } else {
            $level = new MembershipLevel();
        }
        
        // 设置基本属性
        $level->name = $request->input("name");
        $level->code = $request->input("code");
        $level->price = $request->input("price");
        $level->duration_days = $request->input("duration_days");
        $level->color = $request->input("color");
        $level->benefits = json_encode($request->input("benefits", []));
        $level->description = $request->input("description");
        $level->api_quota = $request->input("api_quota", 0);
        $level->ai_quota = $request->input("ai_quota", 0);
        $level->storage_quota = $request->input("storage_quota", 0);
        $level->bandwidth_quota = $request->input("bandwidth_quota", 0);
        $level->discount_percent = $request->input("discount_percent", 0);
        $level->priority_support = $request->has("priority_support");
        $level->is_featured = $request->has("is_featured");
        $level->sort_order = $request->input("sort_order", 0);
        $level->status = $request->input("status");

        
        // 处理图标上传
        if ($request->hasFile("icon") && $request->file("icon")->isValid()) {
            // 删除旧图标
            if ($level->icon && Storage::exists("public/" . $level->icon)) {
                Storage::delete("public/" . $level->icon);
            }
            
            // 保存新图标
            $path = $request->file("icon")->store("membership/icons", "public");
            $level->icon = $path;
        }
        
        // 处理删除图标
        if ($request->has("remove_icon") && $level->icon) {
            if (Storage::exists("public/" . $level->icon)) {
                Storage::delete("public/" . $level->icon);
            }
            $level->icon = null;
        }
        
        // 保存会员等级
        $level->save();
        
        // 设置成功消息
        session()->flash("admin_message", "会员等级已保存");
        session()->flash("admin_message_type", "success");
        
        return redirect()->route("admin.membership.levels");
    }
    
    /**
     * 删除会员等级
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        // 验证CSRF令牌
        if (!$request->has("csrf_token") || $request->input("csrf_token") !== session("csrf_token")) {
            return response()->json([
                "success" => false,
                "message" => "无效的安全令牌"
            ]);
        }
        
        // 获取会员等级
        $id = $request->input("id");
        $level = MembershipLevel::find($id);
        
        if (!$level) {
            return response()->json([
                "success" => false,
                "message" => "会员等级不存在"
            ]);
        }
        
        // 检查是否有用户正在使用该等级
        $usersCount = $level->subscriptions()->count();
        if ($usersCount > 0) {
            return response()->json([
                "success" => false,
                "message" => "该会员等级下有 {$usersCount} 个用户，无法删除"
            ]);
        }
        
        // 删除图标
        if ($level->icon && Storage::exists("public/" . $level->icon)) {
            Storage::delete("public/" . $level->icon);
        }
        
        // 删除会员等级
        $level->delete();
        
        return response()->json([
            "success" => true,
            "message" => "会员等级已删除"
        ]);
    }
}
