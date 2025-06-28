<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

/**
 * 通知规则模型
 * 
 * 用于自动化通知发送规则
 */
class NotificationRule extends Model
{
    use SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'notification_rules';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'name',               // 规则名称
        'description',        // 规则描述
        'event_type',         // 触发事件类型
        'conditions',         // 触发条件（JSON）
        'template_id',        // 使用的模板ID
        'recipients',         // 接收者配置（JSON）
        'settings',           // 规则设置（JSON）
        'is_active',          // 是否激活
        'creator_id',         // 创建者ID
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'conditions' => 'array',
        'recipients' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * 获取关联的通知模板
     */
    public function template()
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    /**
     * 获取关联的创建者
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * 获取活跃的规则
     *
     * @param string|null $eventType 事件类型（可选）
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveRules($eventType = null)
    {
        $query = self::where('is_active', true);
        
        if ($eventType) {
            $query->where('event_type', $eventType);
        }
        
        return $query->get();
    }

    /**
     * 检查规则条件是否满足
     *
     * @param array $data 事件数据
     * @return bool
     */
    public function checkConditions(array $data)
    {
        $conditions = $this->conditions ?? [];
        
        // 如果没有条件，则默认满足
        if (empty($conditions)) {
            return true;
        }
        
        // 条件组逻辑（AND/OR）
        $logic = $conditions['logic'] ?? 'AND';
        $items = $conditions['items'] ?? [];
        
        // 如果没有条件项，则默认满足
        if (empty($items)) {
            return true;
        }
        
        $results = [];
        
        // 检查每个条件
        foreach ($items as $condition) {
            $field = $condition['field'] ?? null;
            $operator = $condition['operator'] ?? '=';
            $value = $condition['value'] ?? null;
            
            if (!$field || !isset($data[$field])) {
                $results[] = false;
                continue;
            }
            
            $fieldValue = $data[$field];
            
            // 根据操作符检查条件
            switch ($operator) {
                case '=':
                    $results[] = $fieldValue == $value;
                    break;
                case '!=':
                    $results[] = $fieldValue != $value;
                    break;
                case '>':
                    $results[] = $fieldValue > $value;
                    break;
                case '>=':
                    $results[] = $fieldValue >= $value;
                    break;
                case '<':
                    $results[] = $fieldValue < $value;
                    break;
                case '<=':
                    $results[] = $fieldValue <= $value;
                    break;
                case 'in':
                    $results[] = in_array($fieldValue, (array)$value);
                    break;
                case 'not_in':
                    $results[] = !in_array($fieldValue, (array)$value);
                    break;
                case 'contains':
                    $results[] = is_string($fieldValue) && strpos($fieldValue, $value) !== false;
                    break;
                case 'not_contains':
                    $results[] = is_string($fieldValue) && strpos($fieldValue, $value) === false;
                    break;
                case 'starts_with':
                    $results[] = is_string($fieldValue) && strpos($fieldValue, $value) === 0;
                    break;
                case 'ends_with':
                    $results[] = is_string($fieldValue) && substr($fieldValue, -strlen($value)) === $value;
                    break;
                default:
                    $results[] = false;
            }
        }
        
        // 根据逻辑组合结果
        if ($logic === 'OR') {
            return in_array(true, $results);
        } else {
            return !in_array(false, $results);
        }
    }

    /**
     * 获取规则的接收者
     *
     * @param array $data 事件数据
     * @return array 接收者数组
     */
    public function getRecipients(array $data)
    {
        $recipientConfig = $this->recipients ?? [];
        $recipients = [];
        
        // 用户类型接收者
        if (!empty($recipientConfig['users'])) {
            foreach ($recipientConfig['users'] as $userId) {
                $recipients[] = [
                    'type' => 'user',
                    'user_id' => $userId
                ];
            }
        }
        
        // 用户组类型接收者
        if (!empty($recipientConfig['user_groups'])) {
            // 获取用户组中的所有用户
            foreach ($recipientConfig['user_groups'] as $groupId) {
                // 这里需要根据实际的用户组模型来获取用户
                // 示例代码，需要根据实际情况修改
                $users = \App\Models\UserGroup::find($groupId)->users ?? [];
                
                foreach ($users as $user) {
                    $recipients[] = [
                        'type' => 'user',
                        'user_id' => $user->id
                    ];
                }
            }
        }
        
        // 角色类型接收者
        if (!empty($recipientConfig['roles'])) {
            // 获取角色中的所有用户
            foreach ($recipientConfig['roles'] as $roleId) {
                // 这里需要根据实际的角色模型来获取用户
                // 示例代码，需要根据实际情况修改
                $users = \App\Models\Role::find($roleId)->users ?? [];
                
                foreach ($users as $user) {
                    $recipients[] = [
                        'type' => 'user',
                        'user_id' => $user->id
                    ];
                }
            }
        }
        
        // 邮箱类型接收者
        if (!empty($recipientConfig['emails'])) {
            foreach ($recipientConfig['emails'] as $email) {
                $recipients[] = [
                    'type' => 'email',
                    'email' => $email
                ];
            }
        }
        
        // 动态字段接收者
        if (!empty($recipientConfig['dynamic_fields'])) {
            foreach ($recipientConfig['dynamic_fields'] as $field) {
                if (isset($data[$field]) && !empty($data[$field])) {
                    // 判断字段值是邮箱还是用户ID
                    if (filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                        $recipients[] = [
                            'type' => 'email',
                            'email' => $data[$field]
                        ];
                    } elseif (is_numeric($data[$field])) {
                        $recipients[] = [
                            'type' => 'user',
                            'user_id' => $data[$field]
                        ];
                    }
                }
            }
        }
        
        // 去重
        $uniqueRecipients = [];
        $userIds = [];
        $emails = [];
        
        foreach ($recipients as $recipient) {
            if ($recipient['type'] === 'user' && !in_array($recipient['user_id'], $userIds)) {
                $userIds[] = $recipient['user_id'];
                $uniqueRecipients[] = $recipient;
            } elseif ($recipient['type'] === 'email' && !in_array($recipient['email'], $emails)) {
                $emails[] = $recipient['email'];
                $uniqueRecipients[] = $recipient;
            }
        }
        
        return $uniqueRecipients;
    }
} 