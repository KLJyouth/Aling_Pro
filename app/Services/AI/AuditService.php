<?php

namespace App\Services\AI;

use App\Models\AI\AuditLog;
use App\Models\AI\AdvancedSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * 记录审计日志
     *
     * @param string $action 操作类型：create, update, delete, view
     * @param string $resourceType 资源类型：provider, model, agent, api_key, setting
     * @param int|null $resourceId 资源ID
     * @param array|null $oldValues 旧值
     * @param array|null $newValues 新值
     * @return AuditLog|null
     */
    public function log($action, $resourceType, $resourceId = null, $oldValues = null, $newValues = null)
    {
        // 检查是否启用审计日志
        $auditEnabled = AdvancedSetting::getValue("enable_audit_logging", true);
        
        if (!$auditEnabled) {
            return null;
        }
        
        return AuditLog::create([
            "user_id" => Auth::id(),
            "action" => $action,
            "resource_type" => $resourceType,
            "resource_id" => $resourceId,
            "old_values" => $oldValues ? json_encode($oldValues) : null,
            "new_values" => $newValues ? json_encode($newValues) : null,
            "ip_address" => Request::ip(),
            "user_agent" => Request::userAgent(),
        ]);
    }
    
    /**
     * 记录创建操作
     *
     * @param string $resourceType
     * @param int $resourceId
     * @param array $values
     * @return AuditLog|null
     */
    public function logCreate($resourceType, $resourceId, array $values)
    {
        return $this->log("create", $resourceType, $resourceId, null, $values);
    }
    
    /**
     * 记录更新操作
     *
     * @param string $resourceType
     * @param int $resourceId
     * @param array $oldValues
     * @param array $newValues
     * @return AuditLog|null
     */
    public function logUpdate($resourceType, $resourceId, array $oldValues, array $newValues)
    {
        return $this->log("update", $resourceType, $resourceId, $oldValues, $newValues);
    }
    
    /**
     * 记录删除操作
     *
     * @param string $resourceType
     * @param int $resourceId
     * @param array $values
     * @return AuditLog|null
     */
    public function logDelete($resourceType, $resourceId, array $values)
    {
        return $this->log("delete", $resourceType, $resourceId, $values, null);
    }
    
    /**
     * 记录查看操作
     *
     * @param string $resourceType
     * @param int $resourceId
     * @return AuditLog|null
     */
    public function logView($resourceType, $resourceId)
    {
        return $this->log("view", $resourceType, $resourceId);
    }
}
