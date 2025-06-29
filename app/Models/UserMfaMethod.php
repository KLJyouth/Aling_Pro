<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserMfaMethod extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * ��������ֵ������
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'method',
        'is_primary',
        'metadata',
        'last_used_at',
    ];
    
    /**
     * Ӧ��ת��Ϊ���ڵ�����
     *
     * @var array
     */
    protected $dates = [
        'last_used_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    
    /**
     * Ӧ��ת��Ϊԭ�����͵�����
     *
     * @var array
     */
    protected $casts = [
        'is_primary' => 'boolean',
        'metadata' => 'array',
    ];
    
    /**
     * ��ȡӵ�д�MFA�������û�
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * ��ȡMFA�������Ѻ�����
     * 
     * @return string
     */
    public function getFriendlyName()
    {
        switch ($this->method) {
            case 'app':
                return '��֤Ӧ��';
            case 'sms':
                $phone = $this->metadata['phone_number'] ?? '';
                return '������֤ (' . substr($phone, 0, 3) . '****' . substr($phone, -4) . ')';
            case 'email':
                $email = $this->metadata['email'] ?? '';
                return '������֤ (' . substr($email, 0, 3) . '***' . strstr($email, '@') . ')';
            case 'fingerprint':
                return 'ָ����֤';
            default:
                return 'δ֪����';
        }
    }
    
    /**
     * ��ȡMFA������ͼ��
     * 
     * @return string
     */
    public function getIcon()
    {
        switch ($this->method) {
            case 'app':
                return 'fa-mobile-alt';
            case 'sms':
                return 'fa-sms';
            case 'email':
                return 'fa-envelope';
            case 'fingerprint':
                return 'fa-fingerprint';
            default:
                return 'fa-shield-alt';
        }
    }
    
    /**
     * ���MFA�����Ƿ����ʹ�ù���7���ڣ�
     * 
     * @return bool
     */
    public function isRecentlyUsed()
    {
        return $this->last_used_at && $this->last_used_at->diffInDays(now()) <= 7;
    }
}
