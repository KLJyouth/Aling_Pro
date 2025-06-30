<?php

namespace App\Services\Membership;

use App\Models\Membership\MemberPrivilege;
use App\Models\Membership\MembershipLevel;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PrivilegeService
{
    /**
     * �������ʱ�䣨�룩
     *
     * @var int
     */
    protected $cacheExpiration = 3600; // 1Сʱ

    /**
     * ����û��Ƿ�ӵ��ָ����Ȩ
     *
     * @param User $user �û�
     * @param string $privilegeCode ��Ȩ����
     * @return bool
     */
    public function hasPrivilege(User $user, string $privilegeCode): bool
    {
        // ��ȡ�û��Ļ�Ծ����
        $subscription = $user->activeSubscription();
        if (!$subscription) {
            return false;
        }

        // ��ȡ�ö��ĵĻ�Ա�ȼ�
        $level = $subscription->level;
        if (!$level) {
            return false;
        }

        // ����Ա�ȼ��Ƿ�ӵ�и���Ȩ
        return $this->levelHasPrivilege($level, $privilegeCode);
    }

    /**
     * ��ȡ��Ȩ��ֵ
     *
     * @param User $user �û�
     * @param string $privilegeCode ��Ȩ����
     * @param mixed $default Ĭ��ֵ
     * @return mixed
     */
    public function getPrivilegeValue(User $user, string $privilegeCode, $default = null)
    {
        // ��ȡ�û��Ļ�Ծ����
        $subscription = $user->activeSubscription();
        if (!$subscription) {
            return $default;
        }

        // ��ȡ�ö��ĵĻ�Ա�ȼ�
        $level = $subscription->level;
        if (!$level) {
            return $default;
        }

        // �ӻ����ȡ��Ȩֵ
        $cacheKey = "privilege_value:" . $level->id . ":" . $privilegeCode;
        return Cache::remember($cacheKey, $this->cacheExpiration, function () use ($level, $privilegeCode, $default) {
            $privilege = $level->privileges()->where("member_privileges.code", $privilegeCode)->first();
            return $privilege ? $privilege->pivot->value : $default;
        });
    }

    /**
     * ����Ա�ȼ��Ƿ�ӵ��ָ����Ȩ
     *
     * @param MembershipLevel $level ��Ա�ȼ�
     * @param string $privilegeCode ��Ȩ����
     * @return bool
     */
    public function levelHasPrivilege(MembershipLevel $level, string $privilegeCode): bool
    {
        // �ӻ����ȡ���
        $cacheKey = "level_has_privilege:" . $level->id . ":" . $privilegeCode;
        return Cache::remember($cacheKey, $this->cacheExpiration, function () use ($level, $privilegeCode) {
            return $level->privileges()->where("member_privileges.code", $privilegeCode)->exists();
        });
    }

    /**
     * ��ȡ���п�����Ȩ
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
     * ��ȡ��Ա�ȼ���������Ȩ
     *
     * @param MembershipLevel $level ��Ա�ȼ�
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
     * ��ȡ�û���������Ȩ
     *
     * @param User $user �û�
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function getUserPrivileges(User $user)
    {
        // ��ȡ�û��Ļ�Ծ����
        $subscription = $user->activeSubscription();
        if (!$subscription) {
            return null;
        }

        // ��ȡ�ö��ĵĻ�Ա�ȼ�
        $level = $subscription->level;
        if (!$level) {
            return null;
        }

        return $this->getLevelPrivileges($level);
    }
}
