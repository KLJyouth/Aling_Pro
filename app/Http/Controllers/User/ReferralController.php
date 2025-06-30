<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\Membership\ReferralService;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    /**
     * �Ƽ�����
     *
     * @var ReferralService
     */
    protected $referralService;

    /**
     * ����������ʵ��
     *
     * @param ReferralService $referralService
     * @return void
     */
    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
        $this->middleware("auth");
    }

    /**
     * ��ʾ�û��Ƽ�ҳ��
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // ȷ���û����Ƽ���
        $referralCode = $this->referralService->generateReferralCode($user);
        
        // ��ȡ�û����Ƽ��б�
        $referrals = $this->referralService->getUserReferrals($user);
        
        // �����Ƽ�����
        $referralLink = url("/register") . "?ref=" . $referralCode;
        
        return view("user.referrals.index", [
            "referralCode" => $referralCode,
            "referralLink" => $referralLink,
            "referrals" => $referrals,
        ]);
    }

    /**
     * �����Ƽ�ע��
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processRegisterReferral(Request $request)
    {
        // ע�⣺�������ͨ������ע��������е��ã�������ֱ�ӱ�¶Ϊ·��
        // �������Ϊʾ��
        
        $referralCode = $request->input("ref");
        $user = $request->user(); // ��������û��Ѿ�ע�Ტ��¼
        
        if (!$referralCode) {
            return back();
        }
        
        // �����Ƽ�
        $result = $this->referralService->processReferral($user, $referralCode);
        
        if ($result) {
            return back()->with("status", "�Ƽ��Ѽ�¼����л��ʹ���Ƽ��룡");
        }
        
        return back();
    }

    /**
     * ��ȡ�����Ƽ��б�
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function moreReferrals(Request $request)
    {
        $request->validate([
            "offset" => "required|integer|min:0",
            "limit" => "integer|min:1|max:50",
        ]);
        
        $user = $request->user();
        $limit = $request->input("limit", 10);
        $offset = $request->input("offset", 0);
        
        $referrals = $this->referralService->getUserReferrals($user, $limit, $offset);
        
        return response()->json([
            "code" => 0,
            "message" => "��ȡ�ɹ�",
            "data" => $referrals,
        ]);
    }
}
