# 用户长期记忆管理

## 功能概述

用户长期记忆管理模块允许系统存储和管理用户的长期记忆数据，支持多种记忆类型、重要性分级和记忆访问统计。该模块为AI提供个性化上下文，使AI能够记住用户的偏好、历史交互和重要信息。

## 数据结构

### 用户记忆表 (user_memories)

| 字段名 | 类型 | 说明 |
|-------|------|------|
| id | bigint | 主键 |
| user_id | bigint | 用户ID，外键关联users表 |
| key | string | 记忆键 |
| content | text | 记忆内容 |
| type | string | 记忆类型：text, json, embedding |
| category | string | 分类 |
| importance | integer | 重要性（1-10） |
| last_accessed_at | timestamp | 最后访问时间 |
| access_count | integer | 访问次数 |
| metadata | json | 元数据 |
| created_at | timestamp | 创建时间 |
| updated_at | timestamp | 更新时间 |
| deleted_at | timestamp | 删除时间（软删除） |

## 核心功能

### 记忆存储

系统支持多种类型的记忆存储：
- **文本记忆**：存储纯文本信息
- **JSON记忆**：存储结构化数据
- **嵌入记忆**：存储向量嵌入数据（用于语义搜索）

### 记忆重要性

记忆可以设置1-10的重要性级别，重要性高的记忆在检索时会被优先考虑。

### 记忆访问统计

系统记录每条记忆的访问次数和最后访问时间，帮助识别常用和重要的记忆。

### 记忆分类

记忆可以按类别组织，方便管理和检索。

### 记忆搜索

支持基于关键词的记忆搜索，未来计划集成向量数据库实现语义搜索。

## 接口说明

### 前台接口

#### 记忆列表

- 路由：`GET /user/memories`
- 控制器：`MemoryController@index`
- 功能：显示用户记忆列表，支持按分类、类型、重要性过滤和搜索

#### 创建记忆

- 路由：`POST /user/memories`
- 控制器：`MemoryController@store`
- 功能：创建新记忆，设置记忆键、内容、类型、分类和重要性

#### 记忆详情

- 路由：`GET /user/memories/{id}`
- 控制器：`MemoryController@show`
- 功能：显示记忆详细信息

#### 编辑记忆

- 路由：`PUT /user/memories/{id}`
- 控制器：`MemoryController@update`
- 功能：更新记忆信息，包括键、内容、类型、分类和重要性

#### 删除记忆

- 路由：`DELETE /user/memories/{id}`
- 控制器：`MemoryController@destroy`
- 功能：删除记忆（软删除）

#### 搜索记忆

- 路由：`POST /user/memories/search`
- 控制器：`MemoryController@search`
- 功能：搜索相似记忆

### 后台接口

#### 用户记忆列表

- 路由：`GET /admin/users/{userId}/memories`
- 控制器：`AdminMemoryController@index`
- 功能：显示指定用户的记忆列表

#### 创建记忆

- 路由：`POST /admin/users/{userId}/memories`
- 控制器：`AdminMemoryController@store`
- 功能：为指定用户创建记忆

#### 记忆详情

- 路由：`GET /admin/users/{userId}/memories/{id}`
- 控制器：`AdminMemoryController@show`
- 功能：显示指定用户的记忆详情

#### 编辑记忆

- 路由：`PUT /admin/users/{userId}/memories/{id}`
- 控制器：`AdminMemoryController@update`
- 功能：更新指定用户的记忆信息

#### 删除记忆

- 路由：`DELETE /admin/users/{userId}/memories/{id}`
- 控制器：`AdminMemoryController@destroy`
- 功能：删除指定用户的记忆

## 服务层

`MemoryService`类封装了记忆管理的核心业务逻辑，包括：

- `remember`：创建或更新记忆
- `recall`：检索记忆
- `forget`：删除记忆
- `getUserMemories`：获取用户记忆列表，支持过滤和排序
- `getUserMemoryCategories`：获取用户记忆分类列表
- `getSimilarMemories`：获取语义相似的记忆

## 使用示例

### 存储记忆

```php
$memoryService = new MemoryService();

// 存储文本记忆
$memoryService->remember(
    Auth::id(),
    "user_preference_theme",
    "dark",
    [
        "type" => "text",
        "category" => "preferences",
        "importance" => 8
    ]
);

// 存储JSON记忆
$memoryService->remember(
    Auth::id(),
    "user_interests",
    [
        "topics" => ["AI", "Programming", "Science"],
        "favorite_books" => ["1984", "Dune"]
    ],
    [
        "type" => "json",
        "category" => "preferences",
        "importance" => 7
    ]
);
```

### 检索记忆

```php
$memoryService = new MemoryService();

// 检索记忆
$theme = $memoryService->recall(Auth::id(), "user_preference_theme");
$interests = $memoryService->recall(Auth::id(), "user_interests");
```

### 搜索相似记忆

```php
$memoryService = new MemoryService();

// 搜索相似记忆
$memories = $memoryService->getSimilarMemories(
    Auth::id(),
    "用户喜欢的编程语言",
    5
);
```

## 与AI集成

长期记忆模块设计用于与AI系统集成，提供个性化上下文：

1. 在AI对话开始前，系统可以检索相关记忆
2. 根据记忆重要性和相关性，选择最重要的记忆添加到上下文
3. AI可以根据对话内容创建或更新记忆
4. 记忆访问统计可以帮助识别哪些记忆对用户最重要

## 注意事项

1. 记忆键应该具有描述性，便于后续检索
2. JSON记忆在存储前会自动序列化，检索时会自动反序列化
3. 嵌入记忆需要向量化处理，目前使用简化实现，未来计划集成向量数据库
4. 记忆重要性应该根据实际需求设置，避免所有记忆都设为最高重要性
5. 系统目前使用关键词匹配进行记忆搜索，搜索精度有限
