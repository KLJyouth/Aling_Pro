<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberPoint extends Model
{
    use HasFactory;
    
    /**
     * ��������ֵ������
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "user_id",
        "points",
        "action",
        "description",
        "reference_id",
        "reference_type",
        "expires_at",
    ];
    
    /**
     * Ӧ�ñ�ת��������
     *
     * @var array<string, string>
     */
    protected $casts = [
        "points" => "integer",
        "expires_at" => "datetime",
    ];
    
    /**
     * ��ȡ���������û�
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * �������Ƿ��ѹ���
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
