<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\Membership\PrivilegeService;
use Illuminate\Http\Request;

class PrivilegeController extends Controller
{
    /**
     * ��Ȩ����
     *
     * @var PrivilegeService
     */
    protected $privilegeService;

    /**
     * ����������ʵ��
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
     * ��ʾ�û���Ȩҳ��
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
     * ��ʾ��Ȩ����ҳ��
     *
     * @param Request $request
     * @param string $code ��Ȩ����
     * @return \Illuminate\View\View
     */
    public function show(Request $request, string $code)
    {
        $user = $request->user();
        
        // ����û��Ƿ�ӵ�и���Ȩ
        if (!$this->privilegeService->hasPrivilege($user, $code)) {
            return redirect()->route("user.privileges.index")->with("error", "��û��Ȩ�޷��ʸ���Ȩ");
        }
        
        // ��ȡ��Ȩ����
        $privileges = $this->privilegeService->getUserPrivileges($user);
        $privilege = null;
        
        foreach ($privileges as $p) {
            if ($p->code === $code) {
                $privilege = $p;
                break;
            }
        }
        
        if (!$privilege) {
            return redirect()->route("user.privileges.index")->with("error", "��Ȩ������");
        }
        
        // ��ȡ��Ȩֵ
        $value = $this->privilegeService->getPrivilegeValue($user, $code);
        
        return view("user.privileges.show", [
            "privilege" => $privilege,
            "value" => $value,
        ]);
    }
}
