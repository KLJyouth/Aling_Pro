<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Billing\Package;
use App\Models\Billing\UserPackage;
use App\Models\Membership\MembershipLevel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserApiController extends Controller
{
    /**
     * 获取用户账户信息
     *
     * @param Request \
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAccountInfo(Request \)
    {
        \ = Auth::user();
        
        \ = [
            'user' => [
                'id' => \->id,
                'name' => \->name,
                'email' => \->email,
                'created_at' => \->created_at->format('Y-m-d H:i:s'),
                'is_premium' => \->is_premium,
            ],
        ];
        
        // 获取会员信息
        if (\->membership_level_id) {
            \ = MembershipLevel::find(\->membership_level_id);
            \['membership'] = [
                'level' => [
                    'id' => \->id,
                    'name' => \->name,
                    'code' => \->code,
                ],
                'expires_at' => \->membership_expires_at ? \->membership_expires_at->format('Y-m-d H:i:s') : null,
                'days_remaining' => \->membership_expires_at ? now()->diffInDays(\->membership_expires_at, false) : 0,
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => \,
        ]);
    }
}
