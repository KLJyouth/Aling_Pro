<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberReferral extends Model
{
    use HasFactory;
    
    /**
     * ��������ֵ������
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "referrer_id",
        "referred_id",
        "code",
        "status",
        "points_awarded",
        "reward_type",
        "reward_amount",
        "reward_description",
    ];
    
    /**
     * Ӧ�ñ�ת��������
     *
     * @var array<string, string>
     */
    protected $casts = [
        "points_awarded" => "integer",
        "reward_amount" => "float",
    ];
    
    /**
     * ��ȡ�Ƽ���
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, "referrer_id");
    }
    
    /**
     * ��ȡ���Ƽ���
     */
    public function referred()
    {
        return $this->belongsTo(User::class, "referred_id");
    }
    
    /**
     * ����Ƽ��Ƿ������
     *
     * @return bool
     */
    public function isCompleted()
    {
        return $this->status === "completed";
    }
    
    /**
     * ����Ƽ��Ƿ������
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->status === "pending";
    }
}
