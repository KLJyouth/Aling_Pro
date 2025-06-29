<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityLog extends Model
{
    use HasFactory;
    
    /**
     * ��������ֵ������
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'event_type',
        'context',
        'risk_score',
        'metadata',
    ];
    
    /**
     * Ӧ��ת��Ϊԭ�����͵�����
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
        'risk_score' => 'integer',
    ];
    
    /**
     * ��ȡ��ص��û�
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * ��ȡ�¼����͵��Ѻ�����
     * 
     * @return string
     */
    public function getEventName()
    {
        $eventTypes = [
            'login' => '��¼',
            'logout' => '�ǳ�',
            'register' => 'ע��',
            'password_reset' => '��������',
            'password_change' => '�޸�����',
            'mfa_enabled' => '���ö�������֤',
            'mfa_disabled' => '���ö�������֤',
            'mfa_verification_success' => '��������֤�ɹ�',
            'mfa_verification_failed' => '��������֤ʧ��',
            'mfa_primary_changed' => '�޸���Ҫ��������֤����',
            'recovery_codes_generated' => '���ɻָ�����',
            'recovery_code_used' => 'ʹ�ûָ�����',
            'recovery_code_verification_failed' => '�ָ�������֤ʧ��',
            'device_binding' => '�豸��',
            'device_unbinding' => '�豸���',
            'payment_attempt' => '֧������',
            'payment_success' => '֧���ɹ�',
            'payment_failed' => '֧��ʧ��',
            'security_check' => '��ȫ���',
            'high_risk_activity' => '�߷��ջ',
            'api_key_rotation' => 'API��Կ�ֻ�',
        ];
        
        return $eventTypes[$this->event_type] ?? $this->event_type;
    }
    
    /**
     * ��ȡ�����ĵ��Ѻ�����
     * 
     * @return string
     */
    public function getContextName()
    {
        $contexts = [
            'auth' => '��֤',
            'mfa' => '��������֤',
            'device' => '�豸����',
            'payment' => '֧��',
            'api' => 'API',
            'admin' => '����',
        ];
        
        return $contexts[$this->context] ?? $this->context;
    }
    
    /**
     * �����־�Ƿ�Ϊ�߷����¼�
     * 
     * @return bool
     */
    public function isHighRisk()
    {
        return $this->risk_score > 70;
    }
    
    /**
     * ��ȡ���յȼ�����
     * 
     * @return string
     */
    public function getRiskLevel()
    {
        if ($this->risk_score > 90) {
            return 'Σ��';
        } elseif ($this->risk_score > 70) {
            return '�߷���';
        } elseif ($this->risk_score > 50) {
            return '�з���';
        } elseif ($this->risk_score > 30) {
            return '�ͷ���';
        } else {
            return '��ȫ';
        }
    }
    
    /**
     * ��ȡ���յȼ���ɫ
     * 
     * @return string
     */
    public function getRiskColor()
    {
        if ($this->risk_score > 90) {
            return 'danger';
        } elseif ($this->risk_score > 70) {
            return 'warning';
        } elseif ($this->risk_score > 50) {
            return 'primary';
        } elseif ($this->risk_score > 30) {
            return 'info';
        } else {
            return 'success';
        }
    }
}
