<?php

namespace App\Services\Membership;

use App\Models\Membership\MemberPrivilege;
use App\Models\Membership\MembershipLevel;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PrivilegeService
{
    /**
     * 缓存过期时间（秒）
     *
     * @var int
     */
    protected $cacheExpiration = 3600; // 1小时

    /**
     * 检查用户是否拥有指定特权
     *
     * @param User $user 用户
     * @param string $privilegeCode 特权代码
     * @return bool
     */
    public function hasPrivilege(User $user, string $privilegeCode): bool
    {
        // 获取用户的活跃订阅
        $subscription = $user->activeSubscription();
        if (!$subscription) {
            return false;
        }

        // 获取该订阅的会员等级
        $level = $subscription->level;
        if (!$level) {
            return false;
        }

        // 检查会员等级是否拥有该特权
        return $this->levelHasPrivilege($level, $privilegeCode);
    }

    /**
     * 获取特权的值
     *
     * @param User $user 用户
     * @param string $privilegeCode 特权代码
     * @param mixed $default 默认值
     * @return mixed
     */
    public function getPrivilegeValue(User $user, string $privilegeCode, $default = null)
    {
        // 获取用户的活跃订阅
        $subscription = $user->activeSubscription();
        if (!$subscription) {
            return $default;
        }

        // 获取该订阅的会员等级
        $level = $subscription->level;
        if (!$level) {
            return $default;
        }

        // 从缓存获取特权值
        $cacheKey = "privilege_value:" . $level->id . ":" . $privilegeCode;
        return Cache::remember($cacheKey, $this->cacheExpiration, function () use ($level, $privilegeCode, $default) {
            $privilege = $level->privileges()->where("member_privileges.code", $privilegeCode)->first();
            return $privilege ? $privilege->pivot->value : $default;
        });
    }

    /**
     * 检查会员等级是否拥有指定特权
     *
     * @param MembershipLevel $level 会员等级
     * @param string $privilegeCode 特权代码
     * @return bool
     */
    public function levelHasPrivilege(MembershipLevel $level, string $privilegeCode): bool
    {
        // 从缓存获取结果
        $cacheKey = "level_has_privilege:" . $level->id . ":" . $privilegeCode;
        return Cache::remember($cacheKey, $this->cacheExpiration, function () use ($level, $privilegeCode) {
            return $level->privileges()->where("member_privileges.code", $privilegeCode)->exists();
        });
    }

    /**
     * 获取所有可用特权
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPrivileges()
    {
        return Cache::remember("all_privileges", $this->cacheExpiration, function () {
            return MemberPrivilege::where("status", "active")
                ->orderBy("sort_order")
                ->get();
        });
    }

    /**
     * 获取会员等级的所有特权
     *
     * @param MembershipLevel $level 会员等级
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLevelPrivileges(MembershipLevel $level)
    {
        $cacheKey = "level_privileges:" . $level->id;
        return Cache::remember($cacheKey, $this->cacheExpiration, function () use ($level) {
            return $level->privileges()->where("member_privileges.status", "active")->get();
        });
    }

    /**
     * 获取用户的所有特权
     *
     * @param User $user 用户
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function getUserPrivileges(User $user)
    {
        // 获取用户的活跃订阅
        $subscription = $user->activeSubscription();
        if (!$subscription) {
            return null;
        }

        // 获取该订阅的会员等级
        $level = $subscription->level;
        if (!$level) {
            return null;
        }

        return $this->getLevelPrivileges($level);
    }
}
