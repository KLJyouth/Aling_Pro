# 用户历史对话管理

## 功能概述

用户历史对话管理模块记录和管理用户与AI的对话历史，支持对话归档、置顶和导出等功能。该模块帮助用户回顾过去的交互，继续之前的对话，并分析AI使用模式。

## 数据结构

### 用户对话表 (user_conversations)

| 字段名 | 类型 | 说明 |
|-------|------|------|
| id | bigint | 主键 |
| user_id | bigint | 用户ID，外键关联users表 |
| title | string | 对话标题 |
| model | string | 使用的模型 |
| agent_id | bigint | 关联的智能体ID |
| system_prompt | json | 系统提示词 |
| metadata | json | 元数据 |
| last_message_at | timestamp | 最后消息时间 |
| message_count | integer | 消息数量 |
| is_pinned | boolean | 是否置顶 |
| is_archived | boolean | 是否归档 |
| created_at | timestamp | 创建时间 |
| updated_at | timestamp | 更新时间 |
| deleted_at | timestamp | 删除时间（软删除） |

### 对话消息表 (conversation_messages)

| 字段名 | 类型 | 说明 |
|-------|------|------|
| id | bigint | 主键 |
| conversation_id | bigint | 对话ID，外键关联user_conversations表 |
| role | string | 角色：user, assistant, system |
| content | text | 消息内容 |
| metadata | json | 元数据（如令牌计数、延迟等） |
| created_at | timestamp | 创建时间 |
| updated_at | timestamp | 更新时间 |

## 核心功能

### 对话管理

系统支持创建、查看、归档和删除对话，并可以为对话设置标题和系统提示词。

### 消息记录

记录对话中的所有消息，包括用户消息、助手回复和系统消息，并存储消息元数据如令牌计数和响应延迟。

### 对话组织

支持对话置顶和归档功能，帮助用户组织和管理多个对话。

### 对话导出

支持将对话导出为JSON格式，方便备份和分享。

### 对话统计

提供对话统计信息，如总对话数、活跃对话数、归档对话数和总消息数。

## 接口说明

### 前台接口

#### 对话列表

- 路由：`GET /user/conversations`
- 控制器：`ConversationController@index`
- 功能：显示用户对话列表，支持按智能体、模型、归档状态过滤和搜索

#### 创建对话

- 路由：`POST /user/conversations`
- 控制器：`ConversationController@store`
- 功能：创建新对话，设置标题、模型、智能体和系统提示词

#### 对话详情

- 路由：`GET /user/conversations/{id}`
- 控制器：`ConversationController@show`
- 功能：显示对话详情和消息历史

#### 编辑对话

- 路由：`PUT /user/conversations/{id}`
- 控制器：`ConversationController@update`
- 功能：更新对话信息，包括标题、模型、智能体和系统提示词

#### 删除对话

- 路由：`DELETE /user/conversations/{id}`
- 控制器：`ConversationController@destroy`
- 功能：删除对话（软删除）

#### 清空对话

- 路由：`POST /user/conversations/{id}/clear`
- 控制器：`ConversationController@clear`
- 功能：清空对话消息

#### 置顶/取消置顶对话

- 路由：`POST /user/conversations/{id}/pin`
- 控制器：`ConversationController@togglePin`
- 功能：切换对话置顶状态

#### 归档/取消归档对话

- 路由：`POST /user/conversations/{id}/archive`
- 控制器：`ConversationController@toggleArchive`
- 功能：切换对话归档状态

#### 发送消息

- 路由：`POST /user/conversations/{id}/message`
- 控制器：`ConversationController@sendMessage`
- 功能：向对话发送消息

#### 导出对话

- 路由：`GET /user/conversations/{id}/export`
- 控制器：`ConversationController@export`
- 功能：导出对话为JSON格式

### 后台接口

#### 用户对话列表

- 路由：`GET /admin/users/{userId}/conversations`
- 控制器：`AdminConversationController@index`
- 功能：显示指定用户的对话列表

#### 对话详情

- 路由：`GET /admin/users/{userId}/conversations/{id}`
- 控制器：`AdminConversationController@show`
- 功能：显示指定用户的对话详情

#### 编辑对话

- 路由：`PUT /admin/users/{userId}/conversations/{id}`
- 控制器：`AdminConversationController@update`
- 功能：更新指定用户的对话信息

#### 删除对话

- 路由：`DELETE /admin/users/{userId}/conversations/{id}`
- 控制器：`AdminConversationController@destroy`
- 功能：删除指定用户的对话

#### 清空对话

- 路由：`POST /admin/users/{userId}/conversations/{id}/clear`
- 控制器：`AdminConversationController@clear`
- 功能：清空指定用户的对话消息

#### 置顶/取消置顶对话

- 路由：`POST /admin/users/{userId}/conversations/{id}/pin`
- 控制器：`AdminConversationController@togglePin`
- 功能：切换指定用户的对话置顶状态

#### 归档/取消归档对话

- 路由：`POST /admin/users/{userId}/conversations/{id}/archive`
- 控制器：`AdminConversationController@toggleArchive`
- 功能：切换指定用户的对话归档状态

#### 导出对话

- 路由：`GET /admin/users/{userId}/conversations/{id}/export`
- 控制器：`AdminConversationController@export`
- 功能：导出指定用户的对话

## 服务层

`ConversationService`类封装了对话管理的核心业务逻辑，包括：

- `createConversation`：创建对话
- `addMessage`：添加消息到对话
- `getConversationHistory`：获取对话历史
- `updateConversation`：更新对话信息
- `deleteConversation`：删除对话
- `clearConversation`：清空对话消息
- `getUserConversations`：获取用户对话列表，支持过滤和排序
- `togglePin`：切换对话置顶状态
- `toggleArchive`：切换对话归档状态
- `getConversationStats`：获取对话统计信息

## 使用示例

### 创建对话

```php
$conversationService = new ConversationService();

// 创建新对话
$conversation = $conversationService->createConversation(
    Auth::id(),
    [
        "title" => "关于AI的讨论",
        "model" => "gpt-4",
        "agent_id" => 1,
        "system_prompt" => [
            "content" => "你是一个AI专家，专门回答关于人工智能的问题。"
        ]
    ]
);
```

### 添加消息

```php
$conversationService = new ConversationService();

// 添加用户消息
$userMessage = $conversationService->addMessage(
    $conversationId,
    "user",
    "什么是深度学习？"
);

// 添加助手消息
$assistantMessage = $conversationService->addMessage(
    $conversationId,
    "assistant",
    "深度学习是机器学习的一个分支，它使用多层神经网络来模拟人脑的学习过程...",
    [
        "token_count" => 150,
        "latency" => 2500,
    ]
);
```

### 获取对话历史

```php
$conversationService = new ConversationService();

// 获取完整对话历史
$history = $conversationService->getConversationHistory($conversationId);

// 获取最近10条消息
$recentHistory = $conversationService->getConversationHistory($conversationId, 10);
```

### 获取对话统计信息

```php
$conversationService = new ConversationService();

// 获取用户对话统计信息
$stats = $conversationService->getConversationStats(Auth::id());
```

## 与AI系统集成

对话历史管理模块与AI系统集成，提供上下文连续性：

1. 用户可以创建新对话或继续现有对话
2. 系统可以加载对话历史作为上下文
3. AI响应会自动记录到对话中
4. 用户可以随时查看、导出或管理对话历史

## 注意事项

1. 对话标题如果未提供，系统会使用用户的第一条消息作为标题
2. 对话支持软删除，可以通过后台恢复已删除的对话
3. 导出的对话包含完整的消息历史和元数据
4. 系统提示词存储为JSON格式，支持复杂的提示结构
5. 对话消息不支持软删除，清空对话会永久删除所有消息
