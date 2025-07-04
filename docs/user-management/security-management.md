# 用户安全管理

## 功能概述

用户安全管理模块提供多因素认证、会话管理、安全日志记录等安全功能，保护用户账号安全，防止未授权访问。该模块为用户提供全面的安全控制选项，同时为管理员提供安全监控和干预能力。

## 数据结构

### 用户安全凭证表 (user_credentials)

| 字段名 | 类型 | 说明 |
|-------|------|------|
| id | bigint | 主键 |
| user_id | bigint | 用户ID，外键关联users表 |
| type | string | 凭证类型：totp, webauthn, recovery_code, trusted_device |
| identifier | string | 凭证标识符 |
| secret | text | 密钥（加密存储） |
| metadata | json | 元数据 |
| is_primary | boolean | 是否为主要凭证 |
| is_active | boolean | 是否激活 |
| last_used_at | timestamp | 最后使用时间 |
| created_at | timestamp | 创建时间 |
| updated_at | timestamp | 更新时间 |
| deleted_at | timestamp | 删除时间（软删除） |

### 用户登录会话表 (user_sessions)

| 字段名 | 类型 | 说明 |
|-------|------|------|
| id | bigint | 主键 |
| user_id | bigint | 用户ID，外键关联users表 |
| session_id | string | 会话ID |
| ip_address | string | IP地址 |
| user_agent | text | 用户代理 |
| device_type | string | 设备类型 |
| location | string | 位置 |
| is_current | boolean | 是否为当前会话 |
| last_activity | timestamp | 最后活动时间 |
| created_at | timestamp | 创建时间 |
| updated_at | timestamp | 更新时间 |

### 用户安全日志表 (user_security_logs)

| 字段名 | 类型 | 说明 |
|-------|------|------|
| id | bigint | 主键 |
| user_id | bigint | 用户ID，外键关联users表 |
| action | string | 操作：login, logout, 2fa_setup, password_change, etc. |
| status | string | 状态：success, failed |
| ip_address | string | IP地址 |
| user_agent | text | 用户代理 |
| device_type | string | 设备类型 |
| location | string | 位置 |
| details | text | 详情 |
| created_at | timestamp | 创建时间 |
| updated_at | timestamp | 更新时间 |

## 核心功能

### 多因素认证

系统支持多种双因素认证方式：

1. **TOTP认证**：基于时间的一次性密码，兼容Google Authenticator等应用
2. **恢复码**：用于在无法使用主要认证方式时恢复账号访问
3. **受信任设备**：标记特定设备为受信任，减少认证频率

### 会话管理

用户可以查看和管理自己的活跃会话：

1. **会话列表**：显示所有活跃会话，包括设备类型、位置和最后活动时间
2. **撤销会话**：撤销特定会话，强制登出该设备
3. **撤销所有其他会话**：保留当前会话，撤销所有其他设备的会话

### 安全日志

系统记录用户的安全相关操作：

1. **登录日志**：记录成功和失败的登录尝试
2. **安全设置变更**：记录密码修改、双因素认证设置等操作
3. **会话管理**：记录会话创建和撤销操作

### 账号锁定

系统支持账号锁定功能，防止暴力破解：

1. **自动锁定**：多次登录失败后自动锁定账号
2. **手动锁定**：管理员可以手动锁定账号
3. **解锁**：管理员可以解锁被锁定的账号

## 接口说明

### 前台接口

#### 安全设置

- 路由：`GET /user/security`
- 控制器：`SecurityController@index`
- 功能：显示用户安全设置页面

#### 双因素认证设置

- 路由：`GET /user/security/two-factor`
- 控制器：`SecurityController@twoFactorSetup`
- 功能：设置双因素认证

#### 激活双因素认证

- 路由：`POST /user/security/two-factor`
- 控制器：`SecurityController@twoFactorActivate`
- 功能：验证并激活双因素认证

#### 禁用双因素认证

- 路由：`GET /user/security/two-factor/disable`
- 控制器：`SecurityController@twoFactorDisableForm`
- 功能：显示禁用双因素认证表单

#### 确认禁用双因素认证

- 路由：`POST /user/security/two-factor/disable`
- 控制器：`SecurityController@twoFactorDisable`
- 功能：验证密码并禁用双因素认证

#### 安全日志

- 路由：`GET /user/security/logs`
- 控制器：`SecurityController@logs`
- 功能：显示用户安全日志

#### 会话管理

- 路由：`GET /user/security/sessions`
- 控制器：`SecurityController@sessions`
- 功能：显示用户会话列表

#### 撤销会话

- 路由：`DELETE /user/security/sessions/{sessionId}`
- 控制器：`SecurityController@revokeSession`
- 功能：撤销特定会话

#### 撤销其他会话

- 路由：`POST /user/security/sessions/revoke-others`
- 控制器：`SecurityController@revokeOtherSessions`
- 功能：撤销除当前会话外的所有会话

#### 修改密码表单

- 路由：`GET /user/security/change-password`
- 控制器：`SecurityController@changePasswordForm`
- 功能：显示修改密码表单

#### 修改密码

- 路由：`POST /user/security/change-password`
- 控制器：`SecurityController@changePassword`
- 功能：验证当前密码并设置新密码

### 后台接口

#### 用户安全信息

- 路由：`GET /admin/users/{userId}/security`
- 控制器：`AdminSecurityController@index`
- 功能：显示指定用户的安全信息

#### 用户安全日志

- 路由：`GET /admin/users/{userId}/security/logs`
- 控制器：`AdminSecurityController@logs`
- 功能：显示指定用户的安全日志

#### 用户会话管理

- 路由：`GET /admin/users/{userId}/security/sessions`
- 控制器：`AdminSecurityController@sessions`
- 功能：显示指定用户的会话列表

#### 撤销用户会话

- 路由：`DELETE /admin/users/{userId}/security/sessions/{sessionId}`
- 控制器：`AdminSecurityController@revokeSession`
- 功能：撤销指定用户的特定会话

#### 撤销用户所有会话

- 路由：`POST /admin/users/{userId}/security/sessions/revoke-all`
- 控制器：`AdminSecurityController@revokeAllSessions`
- 功能：撤销指定用户的所有会话

#### 用户凭证管理

- 路由：`GET /admin/users/{userId}/security/credentials`
- 控制器：`AdminSecurityController@credentials`
- 功能：显示指定用户的安全凭证列表

#### 禁用用户凭证

- 路由：`POST /admin/users/{userId}/security/credentials/{credentialId}/disable`
- 控制器：`AdminSecurityController@disableCredential`
- 功能：禁用指定用户的安全凭证

#### 启用用户凭证

- 路由：`POST /admin/users/{userId}/security/credentials/{credentialId}/enable`
- 控制器：`AdminSecurityController@enableCredential`
- 功能：启用指定用户的安全凭证

#### 删除用户凭证

- 路由：`DELETE /admin/users/{userId}/security/credentials/{credentialId}`
- 控制器：`AdminSecurityController@deleteCredential`
- 功能：删除指定用户的安全凭证

#### 重置双因素认证

- 路由：`POST /admin/users/{userId}/security/two-factor/reset`
- 控制器：`AdminSecurityController@resetTwoFactor`
- 功能：重置指定用户的双因素认证

#### 锁定用户账号

- 路由：`POST /admin/users/{userId}/security/lock`
- 控制器：`AdminSecurityController@lockAccount`
- 功能：锁定指定用户的账号

#### 解锁用户账号

- 路由：`POST /admin/users/{userId}/security/unlock`
- 控制器：`AdminSecurityController@unlockAccount`
- 功能：解锁指定用户的账号

## 服务层

`SecurityService`类封装了安全管理的核心业务逻辑，包括：

- `setupTwoFactor`：设置双因素认证
- `verifyTwoFactor`：验证双因素认证
- `activateTwoFactor`：激活双因素认证
- `disableTwoFactor`：禁用双因素认证
- `generateRecoveryCodes`：生成恢复码
- `useRecoveryCode`：使用恢复码
- `addTrustedDevice`：添加受信任设备
- `verifyTrustedDevice`：验证受信任设备
- `removeTrustedDevice`：删除受信任设备
- `recordSession`：记录用户会话
- `updateSessionActivity`：更新会话活动时间
- `revokeSession`：撤销会话
- `revokeOtherSessions`：撤销其他会话
- `getUserSessions`：获取用户会话列表
- `getUserSecurityLogs`：获取用户安全日志
- `getUserCredentials`：获取用户凭证列表
- `hasTwoFactorEnabled`：检查用户是否启用了双因素认证

## 使用示例

### 设置双因素认证

```php
$securityService = new SecurityService();

// 设置双因素认证
$setupData = $securityService->setupTwoFactor(Auth::id());

// 显示QR码和恢复码
$qrCodeUrl = $setupData["qr_code_url"];
$recoveryCodes = $setupData["recovery_codes"];
```

### 验证双因素认证

```php
$securityService = new SecurityService();

// 验证双因素认证
$isValid = $securityService->verifyTwoFactor(
    Auth::id(),
    $request->input("code")
);

if ($isValid) {
    // 认证成功，继续登录流程
} else {
    // 认证失败，显示错误消息
}
```

### 记录安全日志

```php
// 记录登录成功
UserSecurityLog::success(Auth::id(), "login", [
    "method" => "password",
]);

// 记录登录失败
UserSecurityLog::failure(Auth::id(), "login", [
    "reason" => "Invalid password",
]);
```

### 撤销会话

```php
$securityService = new SecurityService();

// 撤销特定会话
$securityService->revokeSession(Auth::id(), $sessionId);

// 撤销除当前会话外的所有会话
$count = $securityService->revokeOtherSessions(Auth::id(), session()->getId());
```

## 安全最佳实践

系统实施了以下安全最佳实践：

1. **密码安全**：强制使用强密码，密码使用bcrypt哈希存储
2. **多因素认证**：提供TOTP双因素认证，增加账号安全性
3. **会话管理**：记录和管理用户会话，允许撤销可疑会话
4. **安全日志**：记录所有安全相关操作，便于审计和分析
5. **加密存储**：敏感数据（如TOTP密钥）使用加密存储
6. **账号锁定**：多次登录失败后自动锁定账号，防止暴力破解
7. **安全通知**：重要安全操作（如密码修改）会通知用户

## 注意事项

1. 双因素认证设置需要用户验证才能激活
2. 恢复码应该安全保存，每个恢复码只能使用一次
3. 禁用双因素认证需要验证用户密码
4. 会话记录包含IP地址和设备信息，便于识别可疑活动
5. 管理员可以重置用户的双因素认证，但不能查看用户的TOTP密钥
