<?php

namespace App\Models\Monitoring;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alert extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'monitoring_alerts';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'level',
        'source',
        'message',
        'details',
        'status',
        'occurrence_count',
        'first_occurred_at',
        'last_occurred_at',
        'acknowledged_by',
        'acknowledged_at',
        'resolved_by',
        'resolved_at',
        'comment',
        'resolution'
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'details' => 'array',
        'first_occurred_at' => 'datetime',
        'last_occurred_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime'
    ];

    /**
     * 获取确认该告警的用户
     */
    public function acknowledgedBy()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    /**
     * 获取解决该告警的用户
     */
    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * 获取告警级别的标签颜色
     *
     * @return string
     */
    public function getLevelColorAttribute(): string
    {
        switch ($this->level) {
            case 'critical':
                return 'danger';
            case 'warning':
                return 'warning';
            case 'info':
                return 'info';
            default:
                return 'secondary';
        }
    }

    /**
     * 获取告警状态的标签颜色
     *
     * @return string
     */
    public function getStatusColorAttribute(): string
    {
        switch ($this->status) {
            case 'active':
                return 'danger';
            case 'acknowledged':
                return 'warning';
            case 'resolved':
                return 'success';
            default:
                return 'secondary';
        }
    }

    /**
     * 获取告警级别的中文名称
     *
     * @return string
     */
    public function getLevelNameAttribute(): string
    {
        switch ($this->level) {
            case 'critical':
                return '严重';
            case 'warning':
                return '警告';
            case 'info':
                return '信息';
            default:
                return $this->level;
        }
    }

    /**
     * 获取告警类型的中文名称
     *
     * @return string
     */
    public function getTypeNameAttribute(): string
    {
        switch ($this->type) {
            case 'performance':
                return '性能';
            case 'health':
                return '健康状态';
            case 'application':
                return '应用';
            case 'security':
                return '安全';
            default:
                return $this->type;
        }
    }

    /**
     * 获取告警状态的中文名称
     *
     * @return string
     */
    public function getStatusNameAttribute(): string
    {
        switch ($this->status) {
            case 'active':
                return '活跃';
            case 'acknowledged':
                return '已确认';
            case 'resolved':
                return '已解决';
            default:
                return $this->status;
        }
    }
} 