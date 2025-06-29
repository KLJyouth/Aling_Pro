<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDevice extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * ��������ֵ������
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'device_id',
        'device_name',
        'device_type',
        'device_model',
        'os_type',
        'os_version',
        'app_version',
        'device_fingerprint',
        'phone_number',
        'imei',
        'mac_address',
        'is_verified',
        'last_active_at',
    ];
    
    /**
     * Ӧ��ת��Ϊ���ڵ�����
     *
     * @var array
     */
    protected $dates = [
        'last_active_at',
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
        'is_verified' => 'boolean',
    ];
    
    /**
     * ��ȡӵ�д��豸���û�
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * ��ȡ�豸�ĵ�¼��ʷ
     */
    public function loginHistory()
    {
        return $this->hasMany(SecurityLog::class, 'device_id', 'device_id')
            ->where('event_type', 'login');
    }
    
    /**
     * ����豸�Ƿ�Ϊ���豸��30�����״�ʹ�ã�
     * 
     * @return bool
     */
    public function isNew()
    {
        return $this->created_at->diffInDays(now()) <= 30;
    }
    
    /**
     * ����豸�Ƿ�Ϊ��Ծ�豸��7�����л��
     * 
     * @return bool
     */
    public function isActive()
    {
        return $this->last_active_at && $this->last_active_at->diffInDays(now()) <= 7;
    }
    
    /**
     * ��ȡ�豸���Ѻ�����
     * 
     * @return string
     */
    public function getFriendlyName()
    {
        if ($this->device_name) {
            return $this->device_name;
        }
        
        $name = $this->device_model ?: $this->device_type;
        $os = $this->os_type . ($this->os_version ? " {$this->os_version}" : '');
        
        return "{$name} ({$os})";
    }
}
