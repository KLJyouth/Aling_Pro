<?php

namespace App\Models\Membership;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberReferral extends Model
{
    use HasFactory;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'member_referrals';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'referrer_id',
        'referred_id',
        'code',
        'status',
        'points_awarded',
        'reward_type',
        'reward_amount',
        'reward_description',
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected \ = [
        'points_awarded' => 'integer',
        'reward_amount' => 'decimal:2',
    ];

    /**
     * 获取关联的推荐人
     */
    public function referrer()
    {
        return \->belongsTo(User::class, 'referrer_id');
    }

    /**
     * 获取关联的被推荐人
     */
    public function referred()
    {
        return \->belongsTo(User::class, 'referred_id');
    }
}
