<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\Membership\PrivilegeService;
use Illuminate\Http\Request;

class PrivilegeController extends Controller
{
    /**
     * 特权服务
     *
     * @var PrivilegeService
     */
    protected $privilegeService;

    /**
     * 创建控制器实例
     *
     * @param PrivilegeService $privilegeService
     * @return void
     */
    public function __construct(PrivilegeService $privilegeService)
    {
        $this->privilegeService = $privilegeService;
        $this->middleware("auth");
    }

    /**
     * 显示用户特权页面
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $privileges = $this->privilegeService->getUserPrivileges($user);
        
        return view("user.privileges.index", [
            "privileges" => $privileges,
        ]);
    }

    /**
     * 显示特权详情页面
     *
     * @param Request $request
     * @param string $code 特权代码
     * @return \Illuminate\View\View
     */
    public function show(Request $request, string $code)
    {
        $user = $request->user();
        
        // 检查用户是否拥有该特权
        if (!$this->privilegeService->hasPrivilege($user, $code)) {
            return redirect()->route("user.privileges.index")->with("error", "您没有权限访问该特权");
        }
        
        // 获取特权详情
        $privileges = $this->privilegeService->getUserPrivileges($user);
        $privilege = null;
        
        foreach ($privileges as $p) {
            if ($p->code === $code) {
                $privilege = $p;
                break;
            }
        }
        
        if (!$privilege) {
            return redirect()->route("user.privileges.index")->with("error", "特权不存在");
        }
        
        // 获取特权值
        $value = $this->privilegeService->getPrivilegeValue($user, $code);
        
        return view("user.privileges.show", [
            "privilege" => $privilege,
            "value" => $value,
        ]);
    }
}
