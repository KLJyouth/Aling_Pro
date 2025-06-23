<?php
/**
 * 验证服务类
 * 提供数据验证和规则检查功能
 * 
 * @package AlingAi\Services
 * @version 2.0.0
 * @author AlingAi Team
 * @created 2024-12-19
 */

declare(strict_types=1);

namespace AlingAi\Services;

use Monolog\Logger;
use InvalidArgumentException;

class ValidationService
{
    private Logger $logger;
    private array $errors = [];
    private array $rules = [];
    private array $customMessages = [];
    
    // 预定义验证规则
    private const VALIDATION_RULES = [
        'required',
        'email',
        'url',
        'numeric',
        'integer',
        'alpha',
        'alpha_numeric',
        'min',
        'max',
        'between',
        'in',
        'not_in',
        'regex',
        'date',
        'json',
        'array',
        'string',
        'boolean',
        'phone',
        'ip',
        'uuid',
        'password_strength'
    ];
    
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $this->initializeCustomMessages();
    }
    
    /**
     * 初始化自定义错误消息
     */
    private function initializeCustomMessages(): void
    {
        $this->customMessages = [
            'required' => '字段 :field 是必需的',
            'email' => '字段 :field 必须是有效的电子邮件地址',
            'url' => '字段 :field 必须是有效的URL',
            'numeric' => '字段 :field 必须是数字',
            'integer' => '字段 :field 必须是整数',
            'alpha' => '字段 :field 只能包含字母',
            'alpha_numeric' => '字段 :field 只能包含字母和数字',
            'min' => '字段 :field 最小长度为 :value',
            'max' => '字段 :field 最大长度为 :value',
            'between' => '字段 :field 长度必须在 :min 和 :max 之间',
            'in' => '字段 :field 必须是以下值之一: :values',
            'not_in' => '字段 :field 不能是以下值之一: :values',
            'regex' => '字段 :field 格式不正确',
            'date' => '字段 :field 必须是有效的日期',
            'json' => '字段 :field 必须是有效的JSON',
            'array' => '字段 :field 必须是数组',
            'string' => '字段 :field 必须是字符串',
            'boolean' => '字段 :field 必须是布尔值',
            'phone' => '字段 :field 必须是有效的电话号码',
            'ip' => '字段 :field 必须是有效的IP地址',
            'uuid' => '字段 :field 必须是有效的UUID',
            'password_strength' => '字段 :field 密码强度不够，至少包含8个字符，包括大小写字母、数字和特殊字符'
        ];
    }
      /**
     * 验证数据
     * 
     * @param array $data 要验证的数据
     * @param array $rules 验证规则
     * @param array $customMessages 自定义错误消息
     * @return array 返回格式: ['valid' => bool, 'errors' => array]
     */
    public function validate(array $data, array $rules, array $customMessages = []): array
    {
        $this->errors = [];
        $this->rules = $rules;
        $this->customMessages = array_merge($this->customMessages, $customMessages);
        
        foreach ($rules as $field => $fieldRules) {
            $this->validateField($field, $data[$field] ?? null, $fieldRules);
        }
        
        $isValid = empty($this->errors);
        
        if (!$isValid) {
            $this->logger->warning('数据验证失败', [
                'errors' => $this->errors,
                'data_keys' => array_keys($data)
            ]);
        }
        
        return [
            'valid' => $isValid,
            'errors' => $this->errors
        ];
    }
    
    /**
     * 简单验证方法（只返回布尔值）
     * 
     * @param array $data 要验证的数据
     * @param array $rules 验证规则
     * @param array $customMessages 自定义错误消息
     * @return bool
     */
    public function validateSimple(array $data, array $rules, array $customMessages = []): bool
    {
        $result = $this->validate($data, $rules, $customMessages);
        return $result['valid'];
    }
    
    /**
     * 验证单个字段
     * 
     * @param string $field 字段名
     * @param mixed $value 字段值
     * @param string|array $rules 验证规则
     */
    private function validateField(string $field, $value, $rules): void
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }
        
        foreach ($rules as $rule) {
            if (strpos($rule, ':') !== false) {
                [$ruleName, $ruleValue] = explode(':', $rule, 2);
            } else {
                $ruleName = $rule;
                $ruleValue = null;
            }
            
            if (!$this->executeRule($field, $value, $ruleName, $ruleValue)) {
                break; // 如果某个规则失败，停止验证该字段的其他规则
            }
        }
    }
    
    /**
     * 执行验证规则
     * 
     * @param string $field 字段名
     * @param mixed $value 字段值
     * @param string $rule 规则名
     * @param string|null $ruleValue 规则值
     * @return bool
     */
    private function executeRule(string $field, $value, string $rule, ?string $ruleValue): bool
    {
        switch ($rule) {
            case 'required':
                return $this->validateRequired($field, $value);
                
            case 'email':
                return $this->validateEmail($field, $value);
                
            case 'url':
                return $this->validateUrl($field, $value);
                
            case 'numeric':
                return $this->validateNumeric($field, $value);
                
            case 'integer':
                return $this->validateInteger($field, $value);
                
            case 'alpha':
                return $this->validateAlpha($field, $value);
                
            case 'alpha_numeric':
                return $this->validateAlphaNumeric($field, $value);
                
            case 'min':
                return $this->validateMin($field, $value, (int)$ruleValue);
                
            case 'max':
                return $this->validateMax($field, $value, (int)$ruleValue);
                
            case 'between':
                [$min, $max] = explode(',', $ruleValue);
                return $this->validateBetween($field, $value, (int)$min, (int)$max);
                
            case 'in':
                $values = explode(',', $ruleValue);
                return $this->validateIn($field, $value, $values);
                
            case 'not_in':
                $values = explode(',', $ruleValue);
                return $this->validateNotIn($field, $value, $values);
                
            case 'regex':
                return $this->validateRegex($field, $value, $ruleValue);
                
            case 'date':
                return $this->validateDate($field, $value);
                
            case 'json':
                return $this->validateJson($field, $value);
                
            case 'array':
                return $this->validateArray($field, $value);
                
            case 'string':
                return $this->validateString($field, $value);
                
            case 'boolean':
                return $this->validateBoolean($field, $value);
                
            case 'phone':
                return $this->validatePhone($field, $value);
                
            case 'ip':
                return $this->validateIp($field, $value);
                
            case 'uuid':
                return $this->validateUuid($field, $value);
                
            case 'password_strength':
                return $this->validatePasswordStrength($field, $value);
                
            default:
                $this->logger->warning("未知的验证规则: {$rule}");
                return true;
        }
    }
    
    /**
     * 验证必填字段
     */
    private function validateRequired(string $field, $value): bool
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, 'required');
            return false;
        }
        return true;
    }
    
    /**
     * 验证邮箱
     */
    private function validateEmail(string $field, $value): bool
    {
        if ($value === null || $value === '') {
            return true; // 空值通过，使用required规则验证必填
        }
        
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'email');
            return false;
        }
        return true;
    }
    
    /**
     * 验证URL
     */
    private function validateUrl(string $field, $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, 'url');
            return false;
        }
        return true;
    }
    
    /**
     * 验证数字
     */
    private function validateNumeric(string $field, $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        
        if (!is_numeric($value)) {
            $this->addError($field, 'numeric');
            return false;
        }
        return true;
    }
    
    /**
     * 验证整数
     */
    private function validateInteger(string $field, $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, 'integer');
            return false;
        }
        return true;
    }
    
    /**
     * 验证字母
     */
    private function validateAlpha(string $field, $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        
        if (!preg_match('/^[a-zA-Z]+$/', $value)) {
            $this->addError($field, 'alpha');
            return false;
        }
        return true;
    }
    
    /**
     * 验证字母数字
     */
    private function validateAlphaNumeric(string $field, $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        
        if (!preg_match('/^[a-zA-Z0-9]+$/', $value)) {
            $this->addError($field, 'alpha_numeric');
            return false;
        }
        return true;
    }
    
    /**
     * 验证最小长度
     */
    private function validateMin(string $field, $value, int $min): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        
        $length = is_string($value) ? strlen($value) : count($value);
        if ($length < $min) {
            $this->addError($field, 'min', ['value' => $min]);
            return false;
        }
        return true;
    }
    
    /**
     * 验证最大长度
     */
    private function validateMax(string $field, $value, int $max): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        
        $length = is_string($value) ? strlen($value) : count($value);
        if ($length > $max) {
            $this->addError($field, 'max', ['value' => $max]);
            return false;
        }
        return true;
    }
    
    /**
     * 验证长度范围
     */
    private function validateBetween(string $field, $value, int $min, int $max): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        
        $length = is_string($value) ? strlen($value) : count($value);
        if ($length < $min || $length > $max) {
            $this->addError($field, 'between', ['min' => $min, 'max' => $max]);
            return false;
        }
        return true;
    }
    
    /**
     * 验证值在指定列表中
     */
    private function validateIn(string $field, $value, array $values): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        
        if (!in_array($value, $values, true)) {
            $this->addError($field, 'in', ['values' => implode(', ', $values)]);
            return false;
        }
        return true;
    }
    
    /**
     * 验证值不在指定列表中
     */
    private function validateNotIn(string $field, $value, array $values): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        
        if (in_array($value, $values, true)) {
            $this->addError($field, 'not_in', ['values' => implode(', ', $values)]);
            return false;
        }
        return true;
    }
    
    /**
     * 验证正则表达式
     */
    private function validateRegex(string $field, $value, string $pattern): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        
        if (!preg_match($pattern, $value)) {
            $this->addError($field, 'regex');
            return false;
        }
        return true;
    }
    
    /**
     * 验证日期
     */
    private function validateDate(string $field, $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        
        if (strtotime($value) === false) {
            $this->addError($field, 'date');
            return false;
        }
        return true;
    }
    
    /**
     * 验证JSON
     */
    private function validateJson(string $field, $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        
        json_decode($value);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->addError($field, 'json');
            return false;
        }
        return true;
    }
    
    /**
     * 验证数组
     */
    private function validateArray(string $field, $value): bool
    {
        if ($value === null) {
            return true;
        }
        
        if (!is_array($value)) {
            $this->addError($field, 'array');
            return false;
        }
        return true;
    }
    
    /**
     * 验证字符串
     */
    private function validateString(string $field, $value): bool
    {
        if ($value === null) {
            return true;
        }
        
        if (!is_string($value)) {
            $this->addError($field, 'string');
            return false;
        }
        return true;
    }
    
    /**
     * 验证布尔值
     */
    private function validateBoolean(string $field, $value): bool
    {
        if ($value === null) {
            return true;
        }
        
        if (!is_bool($value) && !in_array($value, [0, 1, '0', '1', 'true', 'false'], true)) {
            $this->addError($field, 'boolean');
            return false;
        }
        return true;
    }
    
    /**
     * 验证电话号码
     */
    private function validatePhone(string $field, $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        
        // 支持中国手机号和固定电话
        $pattern = '/^((\+86)?1[3-9]\d{9}|0\d{2,3}-?\d{7,8})$/';
        if (!preg_match($pattern, $value)) {
            $this->addError($field, 'phone');
            return false;
        }
        return true;
    }
    
    /**
     * 验证IP地址
     */
    private function validateIp(string $field, $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        
        if (!filter_var($value, FILTER_VALIDATE_IP)) {
            $this->addError($field, 'ip');
            return false;
        }
        return true;
    }
    
    /**
     * 验证UUID
     */
    private function validateUuid(string $field, $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        if (!preg_match($pattern, $value)) {
            $this->addError($field, 'uuid');
            return false;
        }
        return true;
    }
    
    /**
     * 验证密码强度
     */
    private function validatePasswordStrength(string $field, $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        
        // 至少8个字符，包含大小写字母、数字和特殊字符
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        if (!preg_match($pattern, $value)) {
            $this->addError($field, 'password_strength');
            return false;
        }
        return true;
    }
    
    /**
     * 添加错误信息
     */
    private function addError(string $field, string $rule, array $params = []): void
    {
        $message = $this->customMessages[$rule] ?? "字段 {$field} 验证失败";
        
        // 替换占位符
        $message = str_replace(':field', $field, $message);
        foreach ($params as $key => $value) {
            $message = str_replace(":{$key}", (string)$value, $message);
        }
        
        $this->errors[$field][] = $message;
    }
    
    /**
     * 获取所有错误
     * 
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * 获取第一个错误
     * 
     * @return string|null
     */
    public function getFirstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? null;
        }
        return null;
    }
    
    /**
     * 获取字段的错误
     * 
     * @param string $field
     * @return array
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }
    
    /**
     * 检查是否有错误
     * 
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
    
    /**
     * 清除所有错误
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }
    
    /**
     * 验证用户注册数据
     * 
     * @param array $data
     * @return bool
     */
    public function validateUserRegistration(array $data): bool
    {
        $rules = [
            'username' => 'required|alpha_numeric|min:3|max:20',
            'email' => 'required|email',
            'password' => 'required|password_strength',
            'confirm_password' => 'required',
            'phone' => 'phone'
        ];
        
        $customMessages = [
            'username.required' => '用户名是必需的',
            'username.alpha_numeric' => '用户名只能包含字母和数字',
            'username.min' => '用户名至少需要3个字符',
            'username.max' => '用户名最多20个字符',
            'email.required' => '邮箱是必需的',
            'email.email' => '请输入有效的邮箱地址',
            'password.required' => '密码是必需的',
            'confirm_password.required' => '确认密码是必需的'
        ];
        
        $isValid = $this->validate($data, $rules, $customMessages);
        
        // 检查密码确认
        if (isset($data['password']) && isset($data['confirm_password'])) {
            if ($data['password'] !== $data['confirm_password']) {
                $this->addError('confirm_password', 'password_match', []);
                $isValid = false;
            }
        }
        
        return $isValid;
    }
      /**
     * 验证用户登录数据
     * 
     * @param array $data
     * @return bool
     */
    public function validateUserLogin(array $data): bool
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required'
        ];
        
        $customMessages = [
            'email.required' => '邮箱是必需的',
            'email.email' => '请输入有效的邮箱地址',
            'password.required' => '密码是必需的'
        ];
        
        return $this->validateSimple($data, $rules, $customMessages);
    }
      /**
     * 验证聊天消息数据
     * 
     * @param array $data
     * @return bool
     */
    public function validateChatMessage(array $data): bool
    {
        $rules = [
            'message' => 'required|string|min:1|max:4000',
            'conversation_id' => 'string',
            'model' => 'string|in:gpt-3.5-turbo,gpt-4,claude-3',
            'temperature' => 'numeric|between:0,2'
        ];
        
        return $this->validateSimple($data, $rules);
    }
    
    /**
     * 验证文件上传
     * 
     * @param array $file $_FILES数组中的文件信息
     * @param array $options 选项 [maxSize, allowedTypes, maxFiles]
     * @return bool
     */
    public function validateFileUpload(array $file, array $options = []): bool
    {
        $maxSize = $options['maxSize'] ?? 10 * 1024 * 1024; // 10MB
        $allowedTypes = $options['allowedTypes'] ?? ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
        
        // 检查上传错误
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->addError('file', 'upload_error');
            return false;
        }
        
        // 检查文件大小
        if ($file['size'] > $maxSize) {
            $this->addError('file', 'file_too_large', ['size' => round($maxSize / 1024 / 1024, 2) . 'MB']);
            return false;
        }
        
        // 检查文件类型
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            $this->addError('file', 'invalid_file_type', ['types' => implode(', ', $allowedTypes)]);
            return false;
        }
        
        return true;
    }
}
