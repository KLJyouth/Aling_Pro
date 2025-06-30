<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberPrivilege extends Model
{
    use HasFactory;
    
    /**
     * ��������ֵ������
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "name",
        "code",
        "description",
        "icon",
        "status",
        "is_featured",
        "sort_order",
    ];
    
    /**
     * Ӧ�ñ�ת��������
     *
     * @var array<string, string>
     */
    protected $casts = [
        "is_featured" => "boolean",
        "sort_order" => "integer",
    ];
    
    /**
     * ��ȡӵ�д���Ȩ�Ļ�Ա�ȼ�
     */
    public function membershipLevels()
    {
        return $this->belongsToMany(MembershipLevel::class, "member_privilege_level", "privilege_id", "level_id")
            ->withPivot("value")
            ->withTimestamps();
    }
}
