<?php

namespace AlingAi\Models;

use AlingAi\Models\BaseModel;
use AlingAi\Models\Conversation;
use AlingAi\Models\Document;
use AlingAi\Models\UserLog;
use DateTime;

class User extends BaseModel
{
    protected $table = 'users';
    
    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'avatar',
        'bio',
        'role',
        'status',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        'preferences',
        'settings'
    ];
    
    protected $hidden = [
        'password',
        'remember_token'
    ];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'preferences' => 'array',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
    
    protected $dates = [
        'email_verified_at',
        'last_login_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    // 默认属性值
    protected $attributes = [
        'role' => 'user',
        'status' => 'pending',
        'preferences' => '{}',
        'settings' => '{}'
    ];
    
    /**
     * 获取用户全名
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
    
    /**
     * 检查用户是否为管理员
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->role === 'super_admin';
    }
    
    /**
     * 检查用户是否为超级管理员
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }
    
    /**
     * 检查用户是否已验证邮箱
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }
    
    /**
     * 检查用户是否激活
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    
    /**
     * 检查用户是否被禁用
     */
    public function isBanned(): bool
    {
        return $this->status === 'banned';
    }
    
    /**
     * 获取用户角色权限
     */
    public function getPermissions(): array
    {
        $rolePermissions = [
            'super_admin' => ['*'],
            'admin' => [
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'conversations.view', 'conversations.edit', 'conversations.delete',
                'documents.view', 'documents.edit', 'documents.delete',
                'admin.dashboard', 'admin.settings', 'admin.logs'
            ],
            'moderator' => [
                'users.view', 'users.edit',
                'conversations.view', 'conversations.edit',
                'documents.view', 'documents.edit'
            ],
            'user' => [
                'conversations.create', 'conversations.view_own', 'conversations.edit_own',
                'documents.create', 'documents.view_own', 'documents.edit_own',
                'profile.view', 'profile.edit'
            ]
        ];
        
        return $rolePermissions[$this->role] ?? $rolePermissions['user'];
    }
    
    /**
     * 检查用户是否有特定权限
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->getPermissions();
        return in_array('*', $permissions) || in_array($permission, $permissions);
    }
    
    /**
     * 获取用户头像URL
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return $this->avatar;
        }
        
        // 使用Gravatar作为默认头像
        $email = strtolower(trim($this->email));
        $hash = md5($email);
        return "https://www.gravatar.com/avatar/{$hash}?d=identicon&s=200";
    }
    
    /**
     * 更新最后登录信息
     */
    public function updateLastLogin(string $ipAddress): void
    {
        $this->last_login_at = now();
        $this->last_login_ip = $ipAddress;
        $this->save();
    }
    
    /**
     * 关联：对话记录
     */
    public function conversations()
    {
        $conversation = new Conversation();
        return $conversation->where('user_id', $this->id);
    }
    
    /**
     * 关联：文档
     */
    public function documents()
    {
        $document = new Document();
        return $document->where('user_id', $this->id);
    }
    
    /**
     * 关联：用户日志
     */
    public function userLogs()
    {
        $userLog = new UserLog();
        return $userLog->where('user_id', $this->id);
    }
    
    /**
     * 密码加密器
     */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_ARGON2ID);
    }
    
    /**
     * 验证密码
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
    
    /**
     * 序列化为数组（隐藏敏感信息）
     */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->getFullNameAttribute(),
            'avatar' => $this->avatar,
            'avatar_url' => $this->getAvatarUrlAttribute(),
            'bio' => $this->bio,
            'role' => $this->role,
            'status' => $this->status,
            'email_verified' => $this->hasVerifiedEmail(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
    
    /**
     * 序列化为详细数组（包含更多信息）
     */
    public function toDetailedArray(): array
    {
        return array_merge($this->toPublicArray(), [
            'phone' => $this->phone,
            'email_verified_at' => $this->email_verified_at,
            'last_login_at' => $this->last_login_at,
            'last_login_ip' => $this->last_login_ip,
            'preferences' => $this->preferences,
            'settings' => $this->settings,
            'permissions' => $this->getPermissions()
        ]);
    }
}
