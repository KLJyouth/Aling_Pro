# 用户文件管理

## 功能概述

用户文件管理模块允许用户上传、组织和管理各种类型的文件。系统按用户隔离存储文件，并提供文件分类、权限控制和下载统计等功能。

## 数据结构

### 用户文件表 (user_files)

| 字段名 | 类型 | 说明 |
|-------|------|------|
| id | bigint | 主键 |
| user_id | bigint | 用户ID，外键关联users表 |
| name | string | 文件名称 |
| path | string | 文件路径 |
| type | string | 文件类型 |
| mime_type | string | MIME类型 |
| size | bigint | 文件大小（字节） |
| category | string | 分类名称 |
| description | text | 文件描述 |
| metadata | json | 元数据 |
| is_public | boolean | 是否公开 |
| download_count | integer | 下载次数 |
| created_at | timestamp | 创建时间 |
| updated_at | timestamp | 更新时间 |
| deleted_at | timestamp | 删除时间（软删除） |

### 用户文件分类表 (user_file_categories)

| 字段名 | 类型 | 说明 |
|-------|------|------|
| id | bigint | 主键 |
| user_id | bigint | 用户ID，外键关联users表 |
| name | string | 分类名称 |
| icon | string | 图标 |
| description | text | 分类描述 |
| sort_order | integer | 排序顺序 |
| created_at | timestamp | 创建时间 |
| updated_at | timestamp | 更新时间 |
| deleted_at | timestamp | 删除时间（软删除） |

## 核心功能

### 文件上传

系统支持多种文件类型的上传，并自动识别文件类型和MIME类型。上传的文件存储在`storage/app/public/users/{user_id}/files`目录下，文件名使用UUID生成，确保唯一性。

### 文件分类

用户可以创建自定义文件分类，并将文件归类到不同的分类中。分类支持图标设置和排序，方便用户组织和查找文件。

### 文件权限控制

文件可以设置为公开或私有：
- 公开文件：任何人都可以通过URL直接访问
- 私有文件：只有文件所有者可以访问，需要通过专用下载路由

### 文件统计

系统记录文件的下载次数，用户可以查看文件的使用情况。

## 接口说明

### 前台接口

#### 文件列表

- 路由：`GET /user/files`
- 控制器：`FileController@index`
- 功能：显示用户文件列表，支持按分类、类型、名称搜索和排序

#### 上传文件

- 路由：`POST /user/files`
- 控制器：`FileController@store`
- 功能：上传新文件，设置文件名称、分类、描述和权限

#### 文件详情

- 路由：`GET /user/files/{id}`
- 控制器：`FileController@show`
- 功能：显示文件详细信息

#### 编辑文件

- 路由：`PUT /user/files/{id}`
- 控制器：`FileController@update`
- 功能：更新文件信息，包括名称、分类、描述和权限

#### 删除文件

- 路由：`DELETE /user/files/{id}`
- 控制器：`FileController@destroy`
- 功能：删除文件（软删除）

#### 下载文件

- 路由：`GET /user/files/{id}/download`
- 控制器：`FileController@download`
- 功能：下载文件，并增加下载计数

#### 分类管理

- 路由：`GET /user/files/categories`
- 控制器：`FileController@categories`
- 功能：管理文件分类

### 后台接口

#### 用户文件列表

- 路由：`GET /admin/users/{userId}/files`
- 控制器：`AdminFileController@index`
- 功能：显示指定用户的文件列表

#### 文件详情

- 路由：`GET /admin/users/{userId}/files/{id}`
- 控制器：`AdminFileController@show`
- 功能：显示指定用户的文件详情

#### 编辑文件

- 路由：`PUT /admin/users/{userId}/files/{id}`
- 控制器：`AdminFileController@update`
- 功能：更新指定用户的文件信息

#### 删除文件

- 路由：`DELETE /admin/users/{userId}/files/{id}`
- 控制器：`AdminFileController@destroy`
- 功能：删除指定用户的文件

#### 下载文件

- 路由：`GET /admin/users/{userId}/files/{id}/download`
- 控制器：`AdminFileController@download`
- 功能：下载指定用户的文件

## 服务层

`FileService`类封装了文件管理的核心业务逻辑，包括：

- `uploadFile`：上传文件并创建记录
- `updateFile`：更新文件信息
- `deleteFile`：删除文件
- `getUserFiles`：获取用户文件列表，支持过滤和排序
- `getUserFileCategories`：获取用户文件分类列表
- `createFileCategory`：创建文件分类
- `updateFileCategory`：更新文件分类
- `deleteFileCategory`：删除文件分类

## 使用示例

### 上传文件

```php
$fileService = new FileService();
$file = $request->file("file");
$data = [
    "name" => "项目文档",
    "category" => "文档",
    "description" => "项目相关文档",
    "is_public" => false,
];
$fileRecord = $fileService->uploadFile(Auth::id(), $file, $data);
```

### 获取用户文件列表

```php
$fileService = new FileService();
$filters = [
    "category" => "文档",
    "type" => "pdf",
    "search" => "项目",
    "sort_field" => "created_at",
    "sort_direction" => "desc",
];
$files = $fileService->getUserFiles(Auth::id(), $filters);
```

### 创建文件分类

```php
$fileService = new FileService();
$data = [
    "name" => "项目文档",
    "icon" => "fas fa-folder-open",
    "description" => "项目相关文档",
    "sort_order" => 1,
];
$category = $fileService->createFileCategory(Auth::id(), $data);
```

## 注意事项

1. 文件上传大小限制默认为10MB，可在配置中调整
2. 文件存储使用Laravel的`public`磁盘，需要运行`php artisan storage:link`创建符号链接
3. 文件名使用UUID生成，原始文件名存储在数据库中
4. 删除分类时，该分类下的文件会被设置为无分类，而不会被删除
5. 文件支持软删除，可以通过后台恢复已删除的文件
