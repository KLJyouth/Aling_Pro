# AlingAi Pro API 文档

## 概述

AlingAi Pro API 提供了一套RESTful接口，用于访问和管理系统的各种资源。本文档描述了这些API的使用方法和示例。

## 基础信息

- **基础URL**: `http://your-domain.com/api/v1`
- **内容类型**: 所有请求和响应均为JSON格式 (`application/json`)
- **认证**: 使用Bearer Token认证，将令牌放在请求头的`Authorization`字段中
- **分页**: 支持页码分页，使用`page`和`per_page`参数
- **过滤**: 使用`filter[field]`参数进行过滤
- **排序**: 使用`sort`参数指定排序字段，前缀`-`表示降序
- **包含关联**: 使用`include`参数指定要包含的关联资源

## 认证

### 登录

```
POST /api/auth/login
```

**请求参数**:

```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**响应**:

```json
{
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "expires_at": "2025-07-26T12:00:00+00:00",
    "user": {
      "id": 1,
      "username": "johndoe",
      "email": "user@example.com",
      "role": "user"
    }
  }
}
```

### 刷新令牌

```
POST /api/auth/refresh
```

**请求头**:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**响应**:

```json
{
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "expires_at": "2025-07-26T12:00:00+00:00"
  }
}
```

### 登出

```
POST /api/auth/logout
```

**请求头**:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**响应**:

```json
{
  "message": "成功登出"
}
```

## 用户API

### 获取用户列表

```
GET /api/v1/users
```

**请求头**:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**查询参数**:

- `page`: 页码，默认为1
- `per_page`: 每页记录数，默认为15，最大为100
- `filter[username]`: 按用户名过滤
- `filter[email]`: 按邮箱过滤
- `filter[status]`: 按状态过滤
- `filter[role]`: 按角色过滤
- `sort`: 排序字段，如`-created_at`表示按创建时间降序
- `include`: 包含关联资源，如`conversations,documents`

**响应**:

```json
{
  "data": [
    {
      "id": 1,
      "username": "admin",
      "email": "admin@example.com",
      "role": "admin",
      "status": "active",
      "created_at": "2025-01-01T00:00:00+00:00",
      "updated_at": "2025-01-01T00:00:00+00:00"
    },
    {
      "id": 2,
      "username": "user1",
      "email": "user1@example.com",
      "role": "user",
      "status": "active",
      "created_at": "2025-01-02T00:00:00+00:00",
      "updated_at": "2025-01-02T00:00:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 2,
    "last_page": 1
  },
  "links": {
    "first": "/api/v1/users?page=1&per_page=15",
    "last": "/api/v1/users?page=1&per_page=15",
    "prev": null,
    "next": null
  }
}
```

### 获取单个用户

```
GET /api/v1/users/{id}
```

**请求头**:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**响应**:

```json
{
  "data": {
    "id": 1,
    "username": "admin",
    "email": "admin@example.com",
    "first_name": "Admin",
    "last_name": "User",
    "phone": "+1234567890",
    "avatar": "https://example.com/avatars/admin.jpg",
    "bio": "System administrator",
    "role": "admin",
    "status": "active",
    "email_verified_at": "2025-01-01T00:00:00+00:00",
    "last_login_at": "2025-06-26T12:00:00+00:00",
    "last_login_ip": "192.168.1.1",
    "created_at": "2025-01-01T00:00:00+00:00",
    "updated_at": "2025-06-26T12:00:00+00:00"
  }
}
```

### 创建用户

```
POST /api/v1/users
```

**请求头**:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**请求参数**:

```json
{
  "username": "newuser",
  "email": "newuser@example.com",
  "password": "password123",
  "first_name": "New",
  "last_name": "User",
  "phone": "+1234567890",
  "bio": "Regular user",
  "role": "user",
  "status": "active"
}
```

**响应**:

```json
{
  "data": {
    "id": 3,
    "username": "newuser",
    "email": "newuser@example.com",
    "first_name": "New",
    "last_name": "User",
    "phone": "+1234567890",
    "avatar": null,
    "bio": "Regular user",
    "role": "user",
    "status": "active",
    "email_verified_at": null,
    "created_at": "2025-06-26T12:00:00+00:00",
    "updated_at": "2025-06-26T12:00:00+00:00"
  },
  "message": "创建成功"
}
```

### 更新用户

```
PUT /api/v1/users/{id}
```

**请求头**:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**请求参数**:

```json
{
  "first_name": "Updated",
  "last_name": "User",
  "bio": "Updated bio information"
}
```

**响应**:

```json
{
  "data": {
    "id": 3,
    "username": "newuser",
    "email": "newuser@example.com",
    "first_name": "Updated",
    "last_name": "User",
    "phone": "+1234567890",
    "avatar": null,
    "bio": "Updated bio information",
    "role": "user",
    "status": "active",
    "email_verified_at": null,
    "created_at": "2025-06-26T12:00:00+00:00",
    "updated_at": "2025-06-26T12:30:00+00:00"
  },
  "message": "更新成功"
}
```

### 删除用户

```
DELETE /api/v1/users/{id}
```

**请求头**:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**响应**:

```json
{
  "message": "删除成功"
}
```

### 获取当前用户信息

```
GET /api/v1/users/me
```

**请求头**:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**响应**:

```json
{
  "data": {
    "id": 1,
    "username": "admin",
    "email": "admin@example.com",
    "first_name": "Admin",
    "last_name": "User",
    "phone": "+1234567890",
    "avatar": "https://example.com/avatars/admin.jpg",
    "bio": "System administrator",
    "role": "admin",
    "status": "active",
    "email_verified_at": "2025-01-01T00:00:00+00:00",
    "last_login_at": "2025-06-26T12:00:00+00:00",
    "created_at": "2025-01-01T00:00:00+00:00",
    "updated_at": "2025-06-26T12:00:00+00:00"
  }
}
```

## 文档API

### 获取文档列表

```
GET /api/v1/documents
```

**请求头**:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**查询参数**:

- `page`: 页码，默认为1
- `per_page`: 每页记录数，默认为15，最大为100
- `filter[title]`: 按标题过滤
- `filter[status]`: 按状态过滤
- `filter[type]`: 按类型过滤
- `filter[user_id]`: 按用户ID过滤
- `sort`: 排序字段，如`-created_at`表示按创建时间降序
- `include`: 包含关联资源，如`user,tags`

**响应**:

```json
{
  "data": [
    {
      "id": 1,
      "title": "入门指南",
      "description": "系统使用入门指南",
      "status": "published",
      "type": "markdown",
      "user_id": 1,
      "version": 1,
      "created_at": "2025-06-01T00:00:00+00:00",
      "updated_at": "2025-06-01T00:00:00+00:00"
    },
    {
      "id": 2,
      "title": "API文档",
      "description": "系统API使用文档",
      "status": "published",
      "type": "markdown",
      "user_id": 1,
      "version": 2,
      "created_at": "2025-06-10T00:00:00+00:00",
      "updated_at": "2025-06-15T00:00:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 2,
    "last_page": 1
  },
  "links": {
    "first": "/api/v1/documents?page=1&per_page=15",
    "last": "/api/v1/documents?page=1&per_page=15",
    "prev": null,
    "next": null
  }
}
```

### 获取单个文档

```
GET /api/v1/documents/{id}
```

**请求头**:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**查询参数**:

- `include`: 包含关联资源，如`user,tags,versions`

**响应**:

```json
{
  "data": {
    "id": 1,
    "title": "入门指南",
    "description": "系统使用入门指南",
    "status": "published",
    "type": "markdown",
    "user_id": 1,
    "version": 1,
    "created_at": "2025-06-01T00:00:00+00:00",
    "updated_at": "2025-06-01T00:00:00+00:00"
  }
}
```

### 获取文档内容

```
GET /api/v1/documents/{id}/content
```

**请求头**:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**查询参数**:

- `version`: 文档版本，默认为最新版本

**响应**:

```json
{
  "data": {
    "id": 1,
    "title": "入门指南",
    "description": "系统使用入门指南",
    "type": "markdown",
    "version": 1,
    "content": "# 入门指南\n\n欢迎使用我们的系统！本指南将帮助您快速上手。\n\n## 第一步\n\n登录系统..."
  }
}
```

### 创建文档

```
POST /api/v1/documents
```

**请求头**:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**请求参数**:

```json
{
  "title": "新文档",
  "description": "新文档描述",
  "content": "# 新文档\n\n这是一个新创建的文档。",
  "type": "markdown",
  "status": "draft",
  "tags": ["指南", "教程"]
}
```

**响应**:

```json
{
  "data": {
    "id": 3,
    "title": "新文档",
    "description": "新文档描述",
    "status": "draft",
    "type": "markdown",
    "user_id": 1,
    "version": 1,
    "created_at": "2025-06-26T12:00:00+00:00",
    "updated_at": "2025-06-26T12:00:00+00:00"
  },
  "message": "文档创建成功"
}
```

### 上传文档

```
POST /api/v1/documents/upload
```

**请求头**:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
Content-Type: multipart/form-data
```

**表单参数**:

- `file`: 文档文件
- `title`: 文档标题（可选）
- `description`: 文档描述（可选）
- `status`: 文档状态（可选，默认为draft）

**响应**:

```json
{
  "data": {
    "id": 4,
    "title": "上传的文档",
    "description": "通过上传创建的文档",
    "status": "draft",
    "type": "pdf",
    "user_id": 1,
    "version": 1,
    "created_at": "2025-06-26T12:30:00+00:00",
    "updated_at": "2025-06-26T12:30:00+00:00"
  },
  "message": "文档上传成功"
}
```

### 批量导入文档

```
POST /api/v1/documents/batch-import
```

**请求头**:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**请求参数**:

```json
{
  "documents": [
    {
      "title": "文档1",
      "description": "文档1描述",
      "content": "# 文档1\n\n内容1",
      "type": "markdown",
      "status": "draft"
    },
    {
      "title": "文档2",
      "description": "文档2描述",
      "content": "# 文档2\n\n内容2",
      "type": "markdown",
      "status": "draft"
    }
  ]
}
```

**响应**:

```json
{
  "data": {
    "total": 2,
    "success": 2,
    "failed": 0,
    "errors": []
  },
  "message": "文档批量导入完成: 2个成功, 0个失败"
}
```

### 更新文档

```
PUT /api/v1/documents/{id}
```

**请求头**:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**请求参数**:

```json
{
  "title": "更新的文档标题",
  "description": "更新的文档描述",
  "content": "# 更新的文档\n\n这是更新后的内容。",
  "status": "published"
}
```

**响应**:

```json
{
  "data": {
    "id": 3,
    "title": "更新的文档标题",
    "description": "更新的文档描述",
    "status": "published",
    "type": "markdown",
    "user_id": 1,
    "version": 2,
    "created_at": "2025-06-26T12:00:00+00:00",
    "updated_at": "2025-06-26T13:00:00+00:00"
  },
  "message": "更新成功"
}
```

### 删除文档

```
DELETE /api/v1/documents/{id}
```

**请求头**:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**响应**:

```json
{
  "message": "删除成功"
}
```

### 获取文档版本历史

```
GET /api/v1/documents/{id}/versions
```

**请求头**:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**响应**:

```json
{
  "data": [
    {
      "id": 1,
      "document_id": 1,
      "version": 1,
      "user_id": 1,
      "created_at": "2025-06-01T00:00:00+00:00"
    },
    {
      "id": 2,
      "document_id": 1,
      "version": 2,
      "user_id": 1,
      "created_at": "2025-06-15T00:00:00+00:00"
    }
  ]
}
```

## 错误处理

所有API错误都会返回适当的HTTP状态码和JSON格式的错误信息：

### 认证错误 (401)

```json
{
  "error": "未登录或会话已过期"
}
```

### 权限错误 (403)

```json
{
  "error": "无权执行此操作"
}
```

### 资源不存在 (404)

```json
{
  "error": "记录不存在"
}
```

### 验证错误 (422)

```json
{
  "error": "验证失败",
  "errors": {
    "email": ["邮箱地址已被使用"],
    "password": ["密码长度必须至少为8个字符"]
  }
}
```

### 服务器错误 (500)

```json
{
  "error": "服务器内部错误"
}
```

## 开发者工具

### Postman 集合

我们提供了完整的Postman集合，您可以导入这个集合来测试所有API端点：

[下载Postman集合](https://example.com/alingai-pro-api-collection.json)

### API密钥生成

```
POST /api/v1/users/{id}/token
```

**请求头**:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**请求参数**:

```json
{
  "name": "我的API密钥",
  "expires_at": "2026-06-26T00:00:00+00:00"
}
```

**响应**:

```json
{
  "data": {
    "id": 1,
    "user_id": 1,
    "name": "我的API密钥",
    "token": "sk_live_1234567890abcdef1234567890abcdef",
    "last_used_at": null,
    "expires_at": "2026-06-26T00:00:00+00:00",
    "created_at": "2025-06-26T12:00:00+00:00"
  },
  "message": "API密钥创建成功"
}
``` 