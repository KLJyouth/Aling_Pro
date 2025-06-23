# AlingAi Pro 代码规范

## PHP 代码规范 (PSR-12 扩展)

### 1. 基础代码风格

#### 1.1 文件格式
```php
<?php

declare(strict_types=1);

namespace AlingAi\Pro\Controllers;

use AlingAi\Pro\Services\UserService;
use AlingAi\Pro\Models\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * 用户控制器
 * 
 * @package AlingAi\Pro\Controllers
 * @author AlingAi Pro Team
 * @version 1.0.0
 */
class UserController extends BaseController
{
    // 类内容
}
```

#### 1.2 命名规范
- **类名**: PascalCase (如: `UserController`, `AuthService`)
- **方法名**: camelCase (如: `getUserById`, `validateToken`)
- **变量名**: camelCase (如: `$userId`, `$requestData`)
- **常量**: SCREAMING_SNAKE_CASE (如: `MAX_RETRY_COUNT`)
- **数据库表**: snake_case (如: `user_profiles`, `chat_messages`)
- **配置键**: snake_case (如: `database.host`, `app.debug`)

#### 1.3 目录和文件命名
```
src/
├── Controllers/        # 控制器 (PascalCase)
│   ├── Api/           # API 控制器子目录
│   └── Web/           # Web 控制器子目录
├── Services/          # 服务层 (PascalCase)
├── Models/            # 模型层 (PascalCase)
├── Middleware/        # 中间件 (PascalCase)
├── Repositories/      # 仓储层 (PascalCase)
├── Exceptions/        # 异常类 (PascalCase)
├── Utils/             # 工具类 (PascalCase)
└── Config/            # 配置文件 (snake_case.php)
```

### 2. 类和方法规范

#### 2.1 类结构顺序
```php
class ExampleClass
{
    // 1. 常量
    public const DEFAULT_TIMEOUT = 30;
    
    // 2. 属性 (按可见性排序: public, protected, private)
    public string $publicProperty;
    protected array $protectedProperty;
    private object $privateProperty;
    
    // 3. 构造函数
    public function __construct(
        private UserService $userService,
        private LoggerInterface $logger
    ) {
    }
    
    // 4. 公共方法
    public function publicMethod(): ResponseInterface
    {
        // 方法实现
    }
    
    // 5. 受保护方法
    protected function protectedMethod(): array
    {
        // 方法实现
    }
    
    // 6. 私有方法
    private function privateMethod(): void
    {
        // 方法实现
    }
}
```

#### 2.2 方法规范
```php
/**
 * 获取用户信息
 * 
 * @param int $userId 用户ID
 * @param array $options 选项参数
 * @return UserModel|null 用户模型或null
 * @throws UserNotFoundException 用户不存在时抛出
 */
public function getUserById(int $userId, array $options = []): ?UserModel
{
    // 1. 参数验证
    if ($userId <= 0) {
        throw new InvalidArgumentException('用户ID必须大于0');
    }
    
    // 2. 业务逻辑
    try {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new UserNotFoundException("用户不存在: {$userId}");
        }
        
        // 3. 返回结果
        return $user;
    } catch (DatabaseException $e) {
        $this->logger->error('数据库查询失败', [
            'user_id' => $userId,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}
```

### 3. 错误处理规范

#### 3.1 异常处理
```php
// 自定义异常类
namespace AlingAi\Pro\Exceptions;

class ValidationException extends \Exception
{
    private array $errors;
    
    public function __construct(array $errors, string $message = '数据验证失败')
    {
        $this->errors = $errors;
        parent::__construct($message);
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
}

// 使用示例
try {
    $this->validateRequest($request);
} catch (ValidationException $e) {
    return $this->jsonError([
        'message' => $e->getMessage(),
        'errors' => $e->getErrors()
    ], 422);
}
```

#### 3.2 日志记录
```php
// 使用结构化日志
$this->logger->info('用户登录成功', [
    'user_id' => $user->getId(),
    'ip_address' => $request->getClientIp(),
    'user_agent' => $request->getHeader('User-Agent')[0] ?? 'unknown'
]);

$this->logger->error('API调用失败', [
    'endpoint' => $request->getUri()->getPath(),
    'method' => $request->getMethod(),
    'error' => $exception->getMessage(),
    'trace' => $exception->getTraceAsString()
]);
```

### 4. 数据库操作规范

#### 4.1 模型类
```php
namespace AlingAi\Pro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * 用户模型
 * 
 * @property int $id
 * @property string $username
 * @property string $email
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class User extends Model
{
    protected $table = 'users';
    
    protected $fillable = [
        'username',
        'email',
        'password_hash'
    ];
    
    protected $hidden = [
        'password_hash',
        'remember_token'
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_active' => 'boolean'
    ];
    
    // 关联关系
    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }
}
```

#### 4.2 查询构建器
```php
// 使用查询构建器
$users = User::query()
    ->select(['id', 'username', 'email', 'created_at'])
    ->where('is_active', true)
    ->where('created_at', '>=', Carbon::now()->subDays(30))
    ->orderBy('created_at', 'desc')
    ->limit(100)
    ->get();

// 复杂查询
$result = User::query()
    ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
    ->where('users.is_active', true)
    ->where(function ($query) {
        $query->where('user_profiles.subscription_type', 'premium')
              ->orWhere('user_profiles.trial_ends_at', '>', Carbon::now());
    })
    ->paginate(20);
```

### 5. API 响应规范

#### 5.1 统一响应格式
```php
// 成功响应
{
    "success": true,
    "code": 200,
    "message": "操作成功",
    "data": {
        "user": {
            "id": 1,
            "username": "user001",
            "email": "user@example.com"
        }
    },
    "meta": {
        "timestamp": "2025-06-05T12:45:47Z",
        "request_id": "req_123456789"
    }
}

// 错误响应
{
    "success": false,
    "code": 422,
    "message": "数据验证失败",
    "errors": {
        "email": ["邮箱格式不正确"],
        "password": ["密码长度至少8位"]
    },
    "meta": {
        "timestamp": "2025-06-05T12:45:47Z",
        "request_id": "req_123456789"
    }
}
```

#### 5.2 控制器响应方法
```php
abstract class BaseController
{
    protected function jsonSuccess($data = null, string $message = '操作成功', int $code = 200): ResponseInterface
    {
        return $this->jsonResponse([
            'success' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'meta' => $this->getResponseMeta()
        ], $code);
    }
    
    protected function jsonError($errors = null, int $code = 400, string $message = '操作失败'): ResponseInterface
    {
        return $this->jsonResponse([
            'success' => false,
            'code' => $code,
            'message' => $message,
            'errors' => $errors,
            'meta' => $this->getResponseMeta()
        ], $code);
    }
    
    private function getResponseMeta(): array
    {
        return [
            'timestamp' => (new \DateTime())->format('c'),
            'request_id' => $this->generateRequestId()
        ];
    }
}
```

### 6. 安全编码规范

#### 6.1 输入验证
```php
use Respect\Validation\Validator as v;

class UserValidation
{
    public function validateRegistration(array $data): array
    {
        $errors = [];
        
        // 用户名验证
        if (!v::stringType()->length(3, 50)->validate($data['username'] ?? '')) {
            $errors['username'] = ['用户名长度必须在3-50字符之间'];
        }
        
        // 邮箱验证
        if (!v::email()->validate($data['email'] ?? '')) {
            $errors['email'] = ['请输入有效的邮箱地址'];
        }
        
        // 密码验证
        if (!v::stringType()->length(8, 128)->validate($data['password'] ?? '')) {
            $errors['password'] = ['密码长度必须在8-128字符之间'];
        }
        
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
        
        return $data;
    }
}
```

#### 6.2 SQL 注入防护
```php
// ✅ 正确 - 使用参数绑定
$users = DB::select('SELECT * FROM users WHERE email = ? AND status = ?', [$email, $status]);

// ✅ 正确 - 使用查询构建器
$users = User::where('email', $email)->where('status', $status)->get();

// ❌ 错误 - 直接拼接SQL
$users = DB::select("SELECT * FROM users WHERE email = '{$email}'");
```

#### 6.3 XSS 防护
```php
// 输出转义
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');

// 使用Twig模板引擎自动转义
{{ user.username|e }}
```

### 7. 性能优化规范

#### 7.1 数据库查询优化
```php
// ✅ 使用预加载避免N+1查询
$users = User::with(['profile', 'chatMessages'])->get();

// ✅ 只选择需要的字段
$users = User::select(['id', 'username', 'email'])->get();

// ✅ 使用索引
User::where('email', $email)->first(); // 确保email字段有索引

// ✅ 批量操作
User::insert($batchData);
```

#### 7.2 缓存策略
```php
// 缓存查询结果
$cacheKey = "user:{$userId}";
$user = Cache::remember($cacheKey, 3600, function () use ($userId) {
    return User::find($userId);
});

// 清除相关缓存
Cache::forget("user:{$userId}");
Cache::tags(['users', "user:{$userId}"])->flush();
```

## 前端代码规范

### 1. JavaScript/TypeScript 规范

#### 1.1 命名规范
```typescript
// 变量和函数: camelCase
const userName = 'john_doe';
const getUserInfo = async (userId: number): Promise<User> => {};

// 类名: PascalCase
class UserService {
    private apiClient: ApiClient;
}

// 常量: SCREAMING_SNAKE_CASE
const API_BASE_URL = 'https://api.example.com';
const MAX_RETRY_ATTEMPTS = 3;

// 接口: PascalCase，以 I 开头（可选）
interface IUserProfile {
    id: number;
    username: string;
    email: string;
}
```

#### 1.2 函数规范
```typescript
/**
 * 获取用户信息
 * @param userId 用户ID
 * @param options 请求选项
 * @returns Promise<User> 用户信息
 */
async function fetchUserData(
    userId: number,
    options: RequestOptions = {}
): Promise<User> {
    try {
        const response = await apiClient.get(`/users/${userId}`, options);
        return response.data;
    } catch (error) {
        logger.error('获取用户信息失败', { userId, error });
        throw new Error('用户信息获取失败');
    }
}
```

### 2. CSS/SCSS 规范

#### 2.1 BEM 命名方法
```scss
// Block
.chat-message {
    padding: 16px;
    border-radius: 8px;
    
    // Element
    &__avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
    }
    
    &__content {
        flex: 1;
        margin-left: 12px;
    }
    
    &__text {
        line-height: 1.5;
        color: var(--text-primary);
    }
    
    &__timestamp {
        font-size: 12px;
        color: var(--text-secondary);
    }
    
    // Modifier
    &--own {
        background-color: var(--primary-color);
        margin-left: auto;
        
        .chat-message__text {
            color: white;
        }
    }
    
    &--system {
        background-color: var(--bg-secondary);
        text-align: center;
    }
}
```

#### 2.2 CSS 变量使用
```scss
:root {
    // 颜色系统
    --primary-color: #007bff;
    --secondary-color: #6c757d;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --warning-color: #ffc107;
    
    // 文本颜色
    --text-primary: #212529;
    --text-secondary: #6c757d;
    --text-muted: #8e8e93;
    
    // 背景颜色
    --bg-primary: #ffffff;
    --bg-secondary: #f8f9fa;
    --bg-tertiary: #e9ecef;
    
    // 间距系统
    --spacing-xs: 4px;
    --spacing-sm: 8px;
    --spacing-md: 16px;
    --spacing-lg: 24px;
    --spacing-xl: 32px;
    
    // 字体大小
    --font-size-xs: 12px;
    --font-size-sm: 14px;
    --font-size-base: 16px;
    --font-size-lg: 18px;
    --font-size-xl: 20px;
}
```

## 项目结构规范

```
AlingAi_pro/
├── src/                          # 源代码目录
│   ├── Controllers/              # 控制器层
│   │   ├── Api/                 # API控制器
│   │   └── Web/                 # Web控制器
│   ├── Services/                 # 服务层
│   ├── Models/                   # 数据模型层
│   ├── Repositories/             # 数据访问层
│   ├── Middleware/               # 中间件
│   ├── Exceptions/               # 自定义异常
│   ├── Utils/                    # 工具类
│   ├── Config/                   # 配置文件
│   └── Core/                     # 核心框架代码
├── public/                       # 公共资源目录
│   ├── assets/                   # 静态资源
│   │   ├── css/                 # 样式文件
│   │   ├── js/                  # JavaScript文件
│   │   └── images/              # 图片资源
│   └── index.php                # 入口文件
├── templates/                    # 模板文件
├── tests/                        # 测试文件
│   ├── Unit/                    # 单元测试
│   ├── Integration/             # 集成测试
│   └── Feature/                 # 功能测试
├── storage/                      # 存储目录
│   ├── logs/                    # 日志文件
│   ├── cache/                   # 缓存文件
│   └── uploads/                 # 上传文件
├── docs/                         # 文档目录
├── scripts/                      # 脚本文件
└── deployment/                   # 部署相关文件
```

## 代码审查清单

### 1. 功能性检查
- [ ] 代码实现符合需求规范
- [ ] 所有边界条件都已处理
- [ ] 错误处理完整且合理
- [ ] 单元测试覆盖率达标

### 2. 代码质量检查
- [ ] 遵循项目编码规范
- [ ] 变量和函数命名清晰
- [ ] 代码逻辑清晰易懂
- [ ] 避免代码重复

### 3. 安全性检查
- [ ] 输入验证和输出转义
- [ ] SQL注入防护
- [ ] XSS攻击防护
- [ ] 认证和授权检查

### 4. 性能检查
- [ ] 数据库查询优化
- [ ] 缓存策略合理
- [ ] 避免N+1查询问题
- [ ] 资源使用合理

### 5. 可维护性检查
- [ ] 代码注释充分
- [ ] API文档完整
- [ ] 日志记录合理
- [ ] 配置管理规范

---

**版本**: 1.0.0  
**创建时间**: 2025-06-05  
**最后更新**: 2025-06-05  
**维护团队**: AlingAi Pro Team
