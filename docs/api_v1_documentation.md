# AlingAi Pro API v1 文档

## 概述

AlingAi Pro API 提供了一组 RESTful 接口，使客户端应用能够与 AlingAi Pro 系统进行交互。
本文档介绍了 API v1 版本的使用方法、认证机制、数据模型以及可用的端点。

## 基本信息

- **基础URL**: `https://api.alingai.pro/api/v1`
- **API版本**: v1
- **内容类型**: JSON (application/json)

## 认证

AlingAi Pro API 使用基于令牌（Token）的认证机制。客户端需要通过以下方式获取访问令牌：

### 获取访问令牌

```
POST /auth/login
```

请求体：

```json
{
  "email": "user@example.com",
  "password": "your_password"
}
```

响应：

```json
{
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "token_type": "Bearer",
    "expires_in": 3600
  },
  "message": "登录成功"
}
```

### 使用访问令牌

获取令牌后，在每个 API 请求的 HTTP 头中包含以下字段：

```
Authorization: Bearer your_access_token
```

### API令牌

对于服务器端应用程序，可以生成长期有效的 API 令牌：

```
POST /users/tokens
```

请求体：

```json
{
  "name": "My Service",
  "expires_in": 30 // 可选，过期天数
}
```

## 错误处理

API 使用标准的 HTTP 状态码表示请求成功或失败：

- **2xx**: 请求成功
- **4xx**: 客户端错误
- **5xx**: 服务器错误

错误响应格式：

```json
{
  "error": {
    "code": 400,
    "message": "错误描述"
  }
}
```

常见错误代码：

| 状态码 | 说明 |
|--------|------|
| 400 | 请求参数错误 |
| 401 | 未经授权（未登录或令牌过期） |
| 403 | 权限不足 |
| 404 | 资源不存在 |
| 422 | 数据验证失败 |
| 429 | 请求过于频繁（限流） |
| 500 | 服务器内部错误 |

验证错误响应：

```json
{
  "error": {
    "code": 422,
    "message": "验证失败",
    "errors": {
      "email": ["邮箱格式不正确"],
      "password": ["密码长度至少为8个字符"]
    }
  }
}
```

## 分页

列表 API 支持分页，使用以下查询参数：

- `page`: 页码，默认为 1
- `per_page`: 每页记录数，默认为 15，最大为 100

分页响应格式：

```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 50,
    "last_page": 4
  },
  "links": {
    "first": "https://api.alingai.pro/api/v1/users?page=1&per_page=15",
    "last": "https://api.alingai.pro/api/v1/users?page=4&per_page=15",
    "prev": null,
    "next": "https://api.alingai.pro/api/v1/users?page=2&per_page=15"
  }
}
```

## 过滤

API 支持多种过滤方式：

- 精确匹配: `?email=user@example.com`
- 范围筛选: `?created_at_from=2023-01-01&created_at_to=2023-12-31`
- 比较筛选: `?age_gt=18&price_lt=100`
- 全文搜索: `?search=keyword`

## 排序

使用 `sort` 参数进行排序：

- 升序: `?sort=created_at`
- 降序: `?sort=-created_at`
- 多字段: `?sort=role,-created_at`

## 包含关联

使用 `include` 参数加载关联数据：

```
GET /users?include=conversations,documents
```

## 用户 API

### 获取用户列表

```
GET /users
```

查询参数：

- `role`: 按角色筛选
- `status`: 按状态筛选
- `search`: 搜索用户
- `sort`: 排序字段
- `include`: 包含关联

响应：

```json
{
  "data": [
    {
      "id": 1,
      "username": "admin",
      "email": "admin@example.com",
      "first_name": "Admin",
      "last_name": "User",
      "avatar": "https://...",
      "role": "admin",
      "status": "active",
      "created_at": "2023-01-01T00:00:00Z",
      "updated_at": "2023-01-01T00:00:00Z"
    },
    // ...
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 50,
    "last_page": 4
  },
  "links": {
    "first": "https://api.alingai.pro/api/v1/users?page=1",
    "last": "https://api.alingai.pro/api/v1/users?page=4",
    "prev": null,
    "next": "https://api.alingai.pro/api/v1/users?page=2"
  }
}
```

### 获取单个用户

```
GET /users/{id}
```

响应：

```json
{
  "data": {
    "id": 1,
    "username": "admin",
    "email": "admin@example.com",
    "first_name": "Admin",
    "last_name": "User",
    "avatar": "https://...",
    "role": "admin",
    "status": "active",
    "created_at": "2023-01-01T00:00:00Z",
    "updated_at": "2023-01-01T00:00:00Z"
  }
}
```

### 创建用户

```
POST /users
```

请求体：

```json
{
  "username": "newuser",
  "email": "newuser@example.com",
  "password": "password123",
  "first_name": "New",
  "last_name": "User",
  "role": "user",
  "status": "active"
}
```

响应：

```json
{
  "data": {
    "id": 51,
    "username": "newuser",
    "email": "newuser@example.com",
    "first_name": "New",
    "last_name": "User",
    "role": "user",
    "status": "active",
    "created_at": "2023-06-26T10:00:00Z",
    "updated_at": "2023-06-26T10:00:00Z"
  },
  "message": "创建成功"
}
```

### 更新用户

```
PUT /users/{id}
```

请求体：

```json
{
  "first_name": "Updated",
  "last_name": "Name",
  "phone": "1234567890",
  "bio": "User bio"
}
```

响应：

```json
{
  "data": {
    "id": 1,
    "username": "admin",
    "email": "admin@example.com",
    "first_name": "Updated",
    "last_name": "Name",
    "phone": "1234567890",
    "bio": "User bio",
    "role": "admin",
    "status": "active",
    "created_at": "2023-01-01T00:00:00Z",
    "updated_at": "2023-06-26T10:30:00Z"
  },
  "message": "更新成功"
}
```

### 删除用户

```
DELETE /users/{id}
```

响应：

```json
{
  "message": "删除成功"
}
```

### 获取当前用户信息

```
GET /users/me
```

响应：

```json
{
  "data": {
    "id": 1,
    "username": "admin",
    "email": "admin@example.com",
    "first_name": "Admin",
    "last_name": "User",
    "avatar": "https://...",
    "bio": "User bio",
    "role": "admin",
    "status": "active",
    "email_verified_at": "2023-01-01T00:00:00Z",
    "last_login_at": "2023-06-26T09:00:00Z",
    "last_login_ip": "192.168.1.1",
    "created_at": "2023-01-01T00:00:00Z",
    "updated_at": "2023-06-26T10:30:00Z",
    "permissions": ["*"]
  }
}
```

### 验证电子邮件

```
POST /users/verify-email
```

请求体：

```json
{
  "token": "verification_token"
}
```

响应：

```json
{
  "message": "邮箱验证成功"
}
```

### 更新密码

```
POST /users/update-password
```

请求体：

```json
{
  "current_password": "old_password",
  "new_password": "new_password",
  "confirm_password": "new_password"
}
```

响应：

```json
{
  "message": "密码更新成功"
}
```

### 生成 API 令牌

```
POST /users/tokens
```

请求体：

```json
{
  "name": "My App",
  "expires_in": 30
}
```

响应：

```json
{
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "name": "My App",
    "expires_in": 30
  },
  "message": "API令牌生成成功"
}
```

### 撤销 API 令牌

```
DELETE /users/tokens
```

请求体：

```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

响应：

```json
{
  "message": "API令牌已成功撤销"
}
```

### 获取 API 令牌列表

```
GET /users/tokens
```

响应：

```json
{
  "data": [
    {
      "id": 1,
      "name": "My App",
      "last_used_at": "2023-06-26T10:00:00Z",
      "created_at": "2023-06-25T10:00:00Z",
      "expires_at": "2023-07-25T10:00:00Z"
    },
    // ...
  ]
}
```

## 文档 API

### 获取文档列表

```
GET /documents
```

查询参数：

- `type`: 按类型筛选
- `status`: 按状态筛选
- `user_id`: 按用户筛选
- `search`: 搜索文档
- `sort`: 排序字段
- `include`: 包含关联

响应：

```json
{
  "data": [
    {
      "id": 1,
      "title": "文档标题",
      "content": "文档内容...",
      "type": "markdown",
      "status": "published",
      "user_id": 1,
      "created_at": "2023-06-01T00:00:00Z",
      "updated_at": "2023-06-01T00:00:00Z"
    },
    // ...
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 30,
    "last_page": 2
  },
  "links": {
    "first": "https://api.alingai.pro/api/v1/documents?page=1",
    "last": "https://api.alingai.pro/api/v1/documents?page=2",
    "prev": null,
    "next": "https://api.alingai.pro/api/v1/documents?page=2"
  }
}
```

### 获取单个文档

```
GET /documents/{id}
```

响应：

```json
{
  "data": {
    "id": 1,
    "title": "文档标题",
    "content": "文档内容...",
    "type": "markdown",
    "status": "published",
    "metadata": {
      "author": "原作者",
      "source": "来源网站",
      "tags": ["标签1", "标签2"]
    },
    "user_id": 1,
    "created_at": "2023-06-01T00:00:00Z",
    "updated_at": "2023-06-01T00:00:00Z"
  }
}
```

### 创建文档

```
POST /documents
```

请求体：

```json
{
  "title": "新文档",
  "content": "文档内容...",
  "type": "markdown",
  "status": "draft",
  "metadata": {
    "author": "原作者",
    "source": "来源网站",
    "tags": ["标签1", "标签2"]
  }
}
```

响应：

```json
{
  "data": {
    "id": 31,
    "title": "新文档",
    "content": "文档内容...",
    "type": "markdown",
    "status": "draft",
    "metadata": {
      "author": "原作者",
      "source": "来源网站",
      "tags": ["标签1", "标签2"]
    },
    "user_id": 1,
    "created_at": "2023-06-26T11:00:00Z",
    "updated_at": "2023-06-26T11:00:00Z"
  },
  "message": "文档创建成功"
}
```

### 更新文档

```
PUT /documents/{id}
```

请求体：

```json
{
  "title": "更新的标题",
  "content": "更新的内容...",
  "status": "published"
}
```

响应：

```json
{
  "data": {
    "id": 1,
    "title": "更新的标题",
    "content": "更新的内容...",
    "type": "markdown",
    "status": "published",
    "metadata": {
      "author": "原作者",
      "source": "来源网站",
      "tags": ["标签1", "标签2"]
    },
    "user_id": 1,
    "created_at": "2023-06-01T00:00:00Z",
    "updated_at": "2023-06-26T11:30:00Z"
  },
  "message": "更新成功"
}
```

### 删除文档

```
DELETE /documents/{id}
```

响应：

```json
{
  "message": "删除成功"
}
```

### 批量导入文档

```
POST /documents/bulk-import
```

请求体：

```json
{
  "documents": [
    {
      "title": "文档1",
      "content": "内容1",
      "type": "markdown",
      "status": "draft"
    },
    {
      "title": "文档2",
      "content": "内容2",
      "type": "html",
      "status": "published"
    }
  ]
}
```

响应：

```json
{
  "data": {
    "created": [
      {
        "id": 32,
        "title": "文档1",
        "content": "内容1",
        "type": "markdown",
        "status": "draft",
        "user_id": 1,
        "created_at": "2023-06-26T12:00:00Z",
        "updated_at": "2023-06-26T12:00:00Z"
      },
      {
        "id": 33,
        "title": "文档2",
        "content": "内容2",
        "type": "html",
        "status": "published",
        "user_id": 1,
        "created_at": "2023-06-26T12:00:00Z",
        "updated_at": "2023-06-26T12:00:00Z"
      }
    ],
    "failed": [],
    "total_created": 2,
    "total_failed": 0
  },
  "message": "文档批量导入完成"
}
```

### 导出文档

```
GET /documents/{id}/export?format=json
```

查询参数：

- `format`: 导出格式，支持 json、txt、html

响应：

根据请求的格式返回文档内容，并设置相应的 Content-Type 和 Content-Disposition 头。

## 限制和注意事项

1. API 请求限制为每分钟 60 次请求
2. 文件上传大小限制为 10MB
3. 批量操作限制为每次最多 100 条记录
4. 令牌有效期默认为 1 小时，API 令牌最长有效期为 365 天
5. 所有时间均采用 UTC 时间，ISO 8601 格式

## 开发工具

我们提供以下开发工具和资源：

1. API SDK（支持 PHP、JavaScript、Python）
2. Postman 集合
3. OpenAPI 规范文档

## 联系我们

如有问题或需要支持，请联系：

- 邮箱: api-support@alingai.pro
- 开发者论坛: https://forum.alingai.pro 