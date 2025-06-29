<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityAlert extends Model
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
        'alert_type',
        'severity',
        'description',
        'metadata',
        'is_resolved',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
    ];
    
    /**
     * Ӧ��ת��Ϊ���ڵ�����
     *
     * @var array
     */
    protected $dates = [
        'resolved_at',
        'created_at',
        'updated_at',
    ];
    
    /**
     * Ӧ��ת��Ϊԭ�����͵�����
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
        'is_resolved' => 'boolean',
    ];
    
    /**
     * ��ȡ��ص��û�
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * ��ȡ����˾������û�
     */
    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
    
    /**
     * ��ȡ�������͵��Ѻ�����
     * 
     * @return string
     */
    public function getAlertTypeName()
    {
        $alertTypes = [
            'suspicious_login' => '���ɵ�¼',
            'failed_login_attempts' => '��ε�¼ʧ��',
            'password_brute_force' => '���뱩���ƽⳢ��',
            'unusual_location' => '�쳣����λ��',
            'vpn_proxy_detected' => '��⵽VPN�����',
            'high_risk_activity' => '�߷��ջ',
            'unusual_payment' => '�쳣֧��',
            'api_abuse' => 'API����',
            'unusual_traffic' => '�쳣����',
            'data_leak' => '����й¶',
        ];
        
        return $alertTypes[$this->alert_type] ?? $this->alert_type;
    }
    
    /**
     * ��ȡ���س̶ȵ��Ѻ�����
     * 
     * @return string
     */
    public function getSeverityName()
    {
        $severities = [
            'low' => '��',
            'medium' => '��',
            'high' => '��',
            'critical' => '����',
        ];
        
        return $severities[$this->severity] ?? $this->severity;
    }
    
    /**
     * ��ȡ���س̶ȵ���ɫ
     * 
     * @return string
     */
    public function getSeverityColor()
    {
        $colors = [
            'low' => 'info',
            'medium' => 'primary',
            'high' => 'warning',
            'critical' => 'danger',
        ];
        
        return $colors[$this->severity] ?? 'secondary';
    }
    
    /**
     * �������
     * 
     * @param int $userId ����������û�ID
     * @param string $notes ���˵��
     * @return bool
     */
    public function resolve(int $userId, string $notes = '')
    {
        $this->is_resolved = true;
        $this->resolved_by = $userId;
        $this->resolved_at = now();
        $this->resolution_notes = $notes;
        
        return $this->save();
    }
}
