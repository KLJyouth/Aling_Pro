# AlingAi Pro API 文档

## 概述

AlingAi Pro 是一个企业级智能对话系统，提供完整的 RESTful API 接口。本文档详细描述了所有可用的 API 端点、请求格式、响应格式和使用示例。

## 基础信息

- **Base URL**: `http://localhost` (或您的服务器地址)
- **API Version**: `v1`
- **认证方式**: JWT Bearer Token
- **Content-Type**: `application/json`

## 认证

### JWT Token 格式

```
Authorization: Bearer <your_jwt_token>
```

### Token 有效期

- **Access Token**: 1小时
- **Refresh Token**: 30天

## API 端点

### 1. 认证相关 (Authentication)

#### 1.1 用户登录

**POST** `/api/auth/login`

登录获取访问令牌。

**请求体**:
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**响应**:
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "refresh_token": "def50200...",
    "user": {
      "id": 1,
      "email": "user@example.com",
      "username": "user123",
      "role": "user",
      "avatar": "/uploads/avatars/default.png",
      "created_at": "2024-01-01 00:00:00"
    },
    "expires_in": 3600
  }
}
```

#### 1.2 用户注册

**POST** `/api/auth/register`

注册新用户账户。

**请求体**:
```json
{
  "username": "newuser",
  "email": "newuser@example.com",
  "password": "SecurePass123!",
  "confirm_password": "SecurePass123!"
}
```

**响应**:
```json
{
  "success": true,
  "data": {
    "user_id": 123,
    "message": "Registration successful. Please check your email for verification.",
    "verification_required": true
  }
}
```

#### 1.3 刷新令牌

**POST** `/api/auth/refresh`

使用 refresh token 获取新的访问令牌。

**请求体**:
```json
{
  "refresh_token": "def50200..."
}
```

#### 1.4 忘记密码

**POST** `/api/auth/forgot-password`

请求密码重置邮件。

**请求体**:
```json
{
  "email": "user@example.com"
}
```

#### 1.5 重置密码

**POST** `/api/auth/reset-password`

使用重置令牌更新密码。

**请求体**:
```json
{
  "token": "reset_token_here",
  "password": "NewSecurePass123!",
  "confirm_password": "NewSecurePass123!"
}
```

#### 1.6 退出登录

**POST** `/api/auth/logout`

退出当前用户会话。

**Headers**: `Authorization: Bearer <token>`

---

### 2. 聊天相关 (Chat)

#### 2.1 发送消息

**POST** `/api/chat/send`

向AI发送消息并获取回复。

**Headers**: `Authorization: Bearer <token>`

**请求体**:
```json
{
  "message": "Hello, how are you?",
  "conversation_id": "conv_123",
  "model": "gpt-3.5-turbo",
  "temperature": 0.7,
  "max_tokens": 2000
}
```

**响应**:
```json
{
  "success": true,
  "data": {
    "message_id": "msg_456",
    "conversation_id": "conv_123",
    "response": "Hello! I'm doing well, thank you for asking. How can I help you today?",
    "model": "gpt-3.5-turbo",
    "tokens_used": 45,
    "response_time": 1.2,
    "created_at": "2024-01-01 12:00:00"
  }
}
```

#### 2.2 获取对话列表

**GET** `/api/chat/conversations`

获取用户的对话列表。

**Headers**: `Authorization: Bearer <token>`

**查询参数**:
- `page`: 页码 (默认: 1)
- `limit`: 每页数量 (默认: 10, 最大: 100)
- `search`: 搜索关键词

**响应**:
```json
{
  "success": true,
  "data": {
    "conversations": [
      {
        "id": "conv_123",
        "title": "关于AI的讨论",
        "model": "gpt-3.5-turbo",
        "message_count": 15,
        "last_message_at": "2024-01-01 12:00:00",
        "created_at": "2024-01-01 10:00:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 5,
      "total_count": 50,
      "per_page": 10
    }
  }
}
```

#### 2.3 获取消息记录

**GET** `/api/chat/messages/{conversation_id}`

获取指定对话的消息记录。

**Headers**: `Authorization: Bearer <token>`

**路径参数**:
- `conversation_id`: 对话ID

**查询参数**:
- `page`: 页码
- `limit`: 每页数量

#### 2.4 创建新对话

**POST** `/api/chat/conversations`

创建新的对话会话。

**Headers**: `Authorization: Bearer <token>`

**请求体**:
```json
{
  "title": "新对话",
  "model": "gpt-3.5-turbo"
}
```

#### 2.5 删除对话

**DELETE** `/api/chat/conversations/{conversation_id}`

删除指定的对话。

**Headers**: `Authorization: Bearer <token>`

---

### 3. 用户资料 (User Profile)

#### 3.1 获取用户资料

**GET** `/api/user/profile`

获取当前用户的详细资料。

**Headers**: `Authorization: Bearer <token>`

**响应**:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "username": "john_doe",
      "email": "john@example.com",
      "full_name": "John Doe",
      "bio": "AI enthusiast and developer",
      "location": "San Francisco, CA",
      "website": "https://johndoe.com",
      "avatar": "/uploads/avatars/user_1.jpg",
      "role": "user",
      "status": "active",
      "created_at": "2024-01-01 00:00:00",
      "updated_at": "2024-01-15 10:30:00",
      "preferences": {
        "language": "zh-CN",
        "theme": "dark",
        "timezone": "Asia/Shanghai",
        "email_notifications": true,
        "push_notifications": false
      }
    }
  }
}
```

#### 3.2 更新用户资料

**PUT** `/api/user/profile`

更新用户资料信息。

**Headers**: `Authorization: Bearer <token>`

**请求体**:
```json
{
  "username": "updated_username",
  "full_name": "John Doe",
  "bio": "AI enthusiast and developer",
  "location": "San Francisco, CA",
  "website": "https://johndoe.com",
  "phone": "+1-555-0123",
  "birthday": "1990-01-01"
}
```

#### 3.3 更改密码

**POST** `/api/user/change-password`

更改用户密码。

**Headers**: `Authorization: Bearer <token>`

**请求体**:
```json
{
  "current_password": "old_password",
  "new_password": "NewSecurePass123!",
  "confirm_password": "NewSecurePass123!"
}
```

#### 3.4 上传头像

**POST** `/api/user/avatar`

上传用户头像图片。

**Headers**: 
- `Authorization: Bearer <token>`
- `Content-Type: multipart/form-data`

**请求体**: 
- `avatar`: 图片文件 (最大2MB, 支持 JPEG, PNG, GIF, WebP)

#### 3.5 更新偏好设置

**PUT** `/api/user/preferences`

更新用户偏好设置。

**Headers**: `Authorization: Bearer <token>`

**请求体**:
```json
{
  "language": "zh-CN",
  "theme": "dark",
  "timezone": "Asia/Shanghai",
  "email_notifications": true,
  "push_notifications": false,
  "marketing_emails": false
}
```

---

### 4. 系统相关 (System)

#### 4.1 健康检查

**GET** `/api/health`

检查系统健康状态。

**响应**:
```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "version": "1.0.0",
    "timestamp": "2024-01-01 12:00:00",
    "services": {
      "database": "online",
      "cache": "online",
      "email": "online",
      "ai_service": "online"
    }
  }
}
```

#### 4.2 系统信息

**GET** `/api/system/info`

获取系统详细信息（需要管理员权限）。

**Headers**: `Authorization: Bearer <token>`

#### 4.3 API文档

**GET** `/api/docs`

获取API文档。

---

### 5. AI模型 (AI Models)

#### 5.1 获取模型列表

**GET** `/api/models`

获取可用的AI模型列表。

**Headers**: `Authorization: Bearer <token>`

**响应**:
```json
{
  "success": true,
  "data": {
    "models": [
      {
        "id": "gpt-3.5-turbo",
        "name": "GPT-3.5 Turbo",
        "description": "Most capable GPT-3.5 model",
        "max_tokens": 4096,
        "cost_per_1k_tokens": 0.002,
        "available": true
      },
      {
        "id": "gpt-4",
        "name": "GPT-4",
        "description": "More capable than any GPT-3.5 model",
        "max_tokens": 8192,
        "cost_per_1k_tokens": 0.03,
        "available": true
      }
    ]
  }
}
```

#### 5.2 获取模型详情

**GET** `/api/models/{model_id}`

获取特定模型的详细信息。

**Headers**: `Authorization: Bearer <token>`

---

## 错误响应

所有API错误都遵循统一的格式：

```json
{
  "success": false,
  "error": {
    "code": "INVALID_CREDENTIALS",
    "message": "The provided credentials are invalid",
    "details": {
      "field": "email",
      "reason": "Email format is invalid"
    }
  }
}
```

### 常见错误代码

| 状态码 | 错误代码 | 描述 |
|--------|----------|------|
| 400 | VALIDATION_ERROR | 请求数据验证失败 |
| 401 | UNAUTHORIZED | 未认证或认证失败 |
| 403 | FORBIDDEN | 权限不足 |
| 404 | NOT_FOUND | 资源不存在 |
| 429 | RATE_LIMITED | 请求频率限制 |
| 500 | INTERNAL_ERROR | 服务器内部错误 |

## 速率限制

- **登录/注册**: 5次/分钟
- **聊天API**: 60次/分钟
- **其他API**: 100次/分钟

## SDK 和示例

### JavaScript/Node.js 示例

```javascript
const axios = require('axios');

class AlingAiClient {
  constructor(baseURL, token) {
    this.client = axios.create({
      baseURL,
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });
  }

  async sendMessage(message, conversationId) {
    const response = await this.client.post('/api/chat/send', {
      message,
      conversation_id: conversationId,
      model: 'gpt-3.5-turbo'
    });
    return response.data;
  }

  async getProfile() {
    const response = await this.client.get('/api/user/profile');
    return response.data;
  }
}

// 使用示例
const client = new AlingAiClient('http://localhost', 'your_token_here');
client.sendMessage('Hello AI!', 'conv_123')
  .then(response => console.log(response));
```

### Python 示例

```python
import requests
import json

class AlingAiClient:
    def __init__(self, base_url, token):
        self.base_url = base_url
        self.headers = {
            'Authorization': f'Bearer {token}',
            'Content-Type': 'application/json'
        }
    
    def send_message(self, message, conversation_id):
        url = f'{self.base_url}/api/chat/send'
        data = {
            'message': message,
            'conversation_id': conversation_id,
            'model': 'gpt-3.5-turbo'
        }
        response = requests.post(url, headers=self.headers, json=data)
        return response.json()
    
    def get_profile(self):
        url = f'{self.base_url}/api/user/profile'
        response = requests.get(url, headers=self.headers)
        return response.json()

# 使用示例
client = AlingAiClient('http://localhost', 'your_token_here')
result = client.send_message('Hello AI!', 'conv_123')
print(result)
```

## 支持

如有API使用问题，请联系技术支持：
- 邮箱: support@alingai.com
- 文档: https://docs.alingai.com
- GitHub: https://github.com/alingai/alingai-pro

---

**版本**: 1.0.0  
**最后更新**: 2024-12-19
