<?php

namespace App\Http\Controllers;

use App\Services\Membership\PointService;
use App\Services\Membership\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * �����µĿ�����ʵ��
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware("auth");
    }

    /**
     * ��ʾ�û���������
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $user = Auth::user();
        
        // ��ȡ������Ϣ
        $pointService = app(PointService::class);
        $pointsStats = $pointService->getPointsStats($user);
        $pointsHistory = $pointService->getPointsHistory($user, 10);
        
        // ��ȡ�Ƽ���Ϣ
        $referralService = app(ReferralService::class);
        $referralStats = $referralService->getReferralStats($user);
        $referralLink = $referralService->getReferralLink($user);
        
        // ��ȡ�û����Ƽ���¼
        $referrals = $user->referrals()->with("referred")->latest()->take(10)->get();
        
        return view("profile.show", compact(
            "user",
            "pointsStats",
            "pointsHistory",
            "referralStats",
            "referralLink",
            "referrals"
        ));
    }

    /**
     * �����û���������
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "email" => "required|string|email|max:255|unique:users,email," . $user->id,
            "phone" => "nullable|string|max:20",
            "company" => "nullable|string|max:255",
            "address" => "nullable|string|max:255",
            "city" => "nullable|string|max:100",
            "state" => "nullable|string|max:100",
            "zip_code" => "nullable|string|max:20",
            "country" => "nullable|string|max:100",
            "avatar" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // �����û���Ϣ
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->company = $request->company;
        $user->address = $request->address;
        $user->city = $request->city;
        $user->state = $request->state;
        $user->zip_code = $request->zip_code;
        $user->country = $request->country;
        
        // ����ͷ���ϴ�
        if ($request->hasFile("avatar")) {
            $avatar = $request->file("avatar");
            $filename = time() . "." . $avatar->getClientOriginalExtension();
            $avatar->move(public_path("uploads/avatars"), $filename);
            $user->avatar = "uploads/avatars/" . $filename;
        }
        
        $user->save();
        
        return redirect()->route("profile")->with("success", "���������ѳɹ����£�");
    }

    /**
     * �����û�����
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "current_password" => "required|string",
            "password" => "required|string|min:8|confirmed",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        
        // ��֤��ǰ����
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(["current_password" => "��ǰ���벻��ȷ"])
                ->withInput();
        }
        
        // ��������
        $user->password = Hash::make($request->password);
        $user->save();
        
        return redirect()->route("profile")->with("success", "�����ѳɹ����£�");
    }
}
