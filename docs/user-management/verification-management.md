# 用户认证管理

## 功能概述

用户认证管理模块提供多种认证类型（个人、企业、团队、政府机构、教育机构），包括完整的认证申请、审核流程和文档管理。该模块帮助平台建立信任机制，验证用户身份，并为认证用户提供特殊标识和权限。

## 数据结构

### 用户认证表 (user_verifications)

| 字段名 | 类型 | 说明 |
|-------|------|------|
| id | bigint | 主键 |
| user_id | bigint | 用户ID，外键关联users表 |
| type | string | 认证类型：personal, business, team, government, education |
| status | string | 状态：pending, approved, rejected |
| name | string | 认证名称（企业名称、团队名称等） |
| identifier | string | 认证标识（如统一社会信用代码） |
| contact_name | string | 联系人姓名 |
| contact_phone | string | 联系人电话 |
| contact_email | string | 联系人邮箱 |
| description | text | 描述 |
| documents | json | 认证文件 |
| rejection_reason | text | 拒绝原因 |
| verified_by | bigint | 审核人ID |
| verified_at | timestamp | 审核时间 |
| created_at | timestamp | 创建时间 |
| updated_at | timestamp | 更新时间 |
| deleted_at | timestamp | 删除时间（软删除） |

### 认证文件表 (verification_documents)

| 字段名 | 类型 | 说明 |
|-------|------|------|
| id | bigint | 主键 |
| verification_id | bigint | 认证ID，外键关联user_verifications表 |
| name | string | 文件名称 |
| path | string | 文件路径 |
| type | string | 文件类型：id_card, business_license, etc. |
| mime_type | string | MIME类型 |
| size | bigint | 文件大小（字节） |
| notes | text | 备注 |
| created_at | timestamp | 创建时间 |
| updated_at | timestamp | 更新时间 |

## 核心功能

### 认证类型

系统支持多种认证类型，每种类型有不同的认证要求和文件需求：

1. **个人认证**：验证个人身份，需要身份证正反面
2. **企业认证**：验证企业资质，需要营业执照等
3. **团队认证**：验证团队身份，需要团队介绍和授权书
4. **政府机构认证**：验证政府机构身份，需要资质证书和授权书
5. **教育机构认证**：验证教育机构身份，需要资质证书和授权书

### 认证流程

1. 用户提交认证申请，上传必要文件
2. 管理员审核认证申请
3. 审核通过或拒绝，并提供拒绝原因
4. 用户可以查看认证状态和结果

### 认证文件管理

系统安全存储认证文件，只有管理员可以查看和下载。认证文件存储在私有目录，不对外公开访问。

### 认证标识

通过认证的用户会获得相应的认证标识，显示在用户资料和内容旁边，增加可信度。

## 接口说明

### 前台接口

#### 认证列表

- 路由：`GET /user/verifications`
- 控制器：`VerificationController@index`
- 功能：显示用户的认证申请列表

#### 创建认证申请

- 路由：`GET /user/verifications/create`
- 控制器：`VerificationController@create`
- 功能：显示认证申请表单

#### 提交认证申请

- 路由：`POST /user/verifications`
- 控制器：`VerificationController@store`
- 功能：提交认证申请，上传认证文件

#### 认证详情

- 路由：`GET /user/verifications/{id}`
- 控制器：`VerificationController@show`
- 功能：显示认证申请详情

#### 取消认证申请

- 路由：`DELETE /user/verifications/{id}`
- 控制器：`VerificationController@cancel`
- 功能：取消待审核的认证申请

#### 重新提交认证

- 路由：`POST /user/verifications/{id}/resubmit`
- 控制器：`VerificationController@resubmit`
- 功能：重新提交被拒绝的认证申请

### 后台接口

#### 认证列表

- 路由：`GET /admin/users/verifications`
- 控制器：`AdminVerificationController@index`
- 功能：显示所有认证申请列表

#### 待审核认证

- 路由：`GET /admin/users/verifications/pending`
- 控制器：`AdminVerificationController@pending`
- 功能：显示待审核的认证申请

#### 认证详情

- 路由：`GET /admin/users/verifications/{id}`
- 控制器：`AdminVerificationController@show`
- 功能：显示认证申请详情

#### 审核认证

- 路由：`POST /admin/users/verifications/{id}/review`
- 控制器：`AdminVerificationController@review`
- 功能：审核认证申请（通过或拒绝）

#### 下载认证文件

- 路由：`GET /admin/users/verifications/documents/{documentId}/download`
- 控制器：`AdminVerificationController@downloadDocument`
- 功能：下载认证文件

## 服务层

`VerificationService`类封装了认证管理的核心业务逻辑，包括：

- `submitVerification`：提交认证申请
- `uploadVerificationDocument`：上传认证文件
- `reviewVerification`：审核认证申请
- `getUserVerifications`：获取用户认证列表
- `getPendingVerifications`：获取待审核认证列表
- `getAllVerifications`：获取所有认证列表
- `getVerificationDetails`：获取认证详情
- `getVerificationDocument`：获取认证文件
- `downloadVerificationDocument`：下载认证文件
- `getVerificationStats`：获取认证统计信息

## 使用示例

### 提交认证申请

```php
$verificationService = new VerificationService();

// 准备认证数据
$data = [
    "type" => "business",
    "name" => "示例企业有限公司",
    "identifier" => "91310000XXXXXXXX",
    "contact_name" => "张三",
    "contact_phone" => "13800138000",
    "contact_email" => "zhangsan@example.com",
    "description" => "我们是一家专注于AI技术研发的企业",
];

// 准备认证文件
$documents = [
    "business_license" => $request->file("business_license"),
    "organization_code" => $request->file("organization_code"),
];

// 提交认证申请
$verification = $verificationService->submitVerification(
    Auth::id(),
    $data,
    $documents
);
```

### 审核认证申请

```php
$verificationService = new VerificationService();

// 通过认证
$verificationService->reviewVerification(
    $verificationId,
    "approved",
    Auth::id()
);

// 拒绝认证
$verificationService->reviewVerification(
    $verificationId,
    "rejected",
    Auth::id(),
    "提供的营业执照已过期，请提供有效的营业执照"
);
```

### 获取认证统计信息

```php
$verificationService = new VerificationService();

// 获取认证统计信息
$stats = $verificationService->getVerificationStats();
```

## 认证标识和权益

通过认证的用户可以获得以下标识和权益：

1. **认证标识**：在用户名旁显示认证图标和类型
2. **增加可信度**：提高用户内容的可信度和曝光率
3. **特殊权限**：根据认证类型获得特定功能权限
4. **优先支持**：获得优先客户支持服务

## 安全考虑

1. 认证文件存储在私有目录，使用严格的访问控制
2. 敏感信息（如身份证号码）在显示时部分隐藏
3. 认证审核操作记录完整日志，便于追溯
4. 认证文件下载需要管理员权限，并记录下载操作

## 注意事项

1. 每种认证类型只能申请一次，重复申请会被拒绝
2. 认证申请被拒绝后，用户可以重新提交
3. 认证文件要求清晰可辨，否则可能被拒绝
4. 认证状态变更会通过站内消息和邮件通知用户
5. 管理员可以撤销已通过的认证，如发现虚假信息
