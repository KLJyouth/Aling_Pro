# AlingAi Pro 系统架构分析报告

生成时间: 2025-06-05 12:45:47

## 系统概览

| 组件 | 数量 | 说明 |
|------|------|------|
| 控制器 | 34 | API 端点和业务逻辑入口 |
| 服务类 | 29 | 业务逻辑处理层 |
| 模型类 | 12 | 数据模型和数据库交互 |
| 中间件 | 9 | 请求处理中间层 |
| 模块目录 | 23 | 功能模块组织 |

## 目录结构

### Cache
- PHP 文件数: 3
- 子目录数: 0

### Commands
- PHP 文件数: 0
- 子目录数: 0

### Config
- PHP 文件数: 4
- 子目录数: 0

### Controllers
- PHP 文件数: 35
- 子目录数: 1

### Core
- PHP 文件数: 6
- 子目录数: 0

### Database
- PHP 文件数: 4
- 子目录数: 1

### Events
- PHP 文件数: 0
- 子目录数: 0

### Exceptions
- PHP 文件数: 2
- 子目录数: 0

### Listeners
- PHP 文件数: 0
- 子目录数: 0

### Mail
- PHP 文件数: 0
- 子目录数: 0

### Middleware
- PHP 文件数: 9
- 子目录数: 0

### Models
- PHP 文件数: 12
- 子目录数: 0

### Monitoring
- PHP 文件数: 1
- 子目录数: 0

### Performance
- PHP 文件数: 1
- 子目录数: 0

### Providers
- PHP 文件数: 0
- 子目录数: 0

### Repositories
- PHP 文件数: 0
- 子目录数: 0

### Security
- PHP 文件数: 2
- 子目录数: 0

### Services
- PHP 文件数: 29
- 子目录数: 0

### Support
- PHP 文件数: 0
- 子目录数: 0

### Transformers
- PHP 文件数: 0
- 子目录数: 0

### Utils
- PHP 文件数: 8
- 子目录数: 0

### Validation
- PHP 文件数: 0
- 子目录数: 0

### WebSocket
- PHP 文件数: 1
- 子目录数: 0

## 控制器分析

### AdminController
- 文件: /src/Controllers/AdminController.php
- 方法数: 9
- 方法列表:
  - `public __construct(DatabaseServiceInterface $db,
        CacheService $cache,
        EmailService $emailService)`
  - `public dashboard(ServerRequestInterface $request)`
  - `public getConfig(ServerRequestInterface $request)`
  - `public updateConfig(ServerRequestInterface $request)`
  - `public getLogs(ServerRequestInterface $request)`
  - `public clearCache(ServerRequestInterface $request)`
  - `public maintenance(ServerRequestInterface $request)`
  - `public sendNotification(ServerRequestInterface $request)`
  - `private isAdmin(ServerRequestInterface $request)`

### ApiController
- 文件: /src/Controllers/ApiController.php
- 方法数: 38
- 方法列表:
  - `public __construct(DatabaseServiceInterface $db,
        CacheService $cache,
        AuthService $authService,
        ChatService $chatService,
        UserService $userService,
        ValidationService $validator,
        RateLimitService $rateLimiter,
        ApplicationCacheManager $cacheManager)`
  - `public status(ServerRequestInterface $request, ResponseInterface $response)`
  - `public authStatus(ServerRequestInterface $request, ResponseInterface $response)`
  - `public chatHealth(ServerRequestInterface $request, ResponseInterface $response)`
  - `public userProfile(ServerRequestInterface $request, ResponseInterface $response)`
  - `public updateSettings(ServerRequestInterface $request, ResponseInterface $response)`
  - `public sendMessage(ServerRequestInterface $request, ResponseInterface $response)`
  - `public getChatHistory(ServerRequestInterface $request, ResponseInterface $response)`
  - `public systemStats(ServerRequestInterface $request, ResponseInterface $response)`
  - `public clearCache(ServerRequestInterface $request, ResponseInterface $response)`
  - `public exportData(ServerRequestInterface $request, ResponseInterface $response)`
  - `public enhancedChat(ServerRequestInterface $request, ResponseInterface $response)`
  - `public aiStatus(ServerRequestInterface $request, ResponseInterface $response)`
  - `public aiUsageStats(ServerRequestInterface $request, ResponseInterface $response)`
  - `public systemMetrics(ServerRequestInterface $request, ResponseInterface $response)`
  - `public systemHealth(ServerRequestInterface $request, ResponseInterface $response)`
  - `public systemAlerts(ServerRequestInterface $request, ResponseInterface $response)`
  - `public sendTestEmail(ServerRequestInterface $request, ResponseInterface $response)`
  - `public emailStats(ServerRequestInterface $request, ResponseInterface $response)`
  - `public databaseStatus(ServerRequestInterface $request, ResponseInterface $response)`
  - `public cleanupSystem(ServerRequestInterface $request, ResponseInterface $response)`
  - `public getConfig(ServerRequestInterface $request, ResponseInterface $response)`
  - `public updateConfig(ServerRequestInterface $request, ResponseInterface $response)`
  - `private extractBearerToken(ServerRequestInterface $request)`
  - `private requireAuth(ServerRequestInterface $request)`
  - `private getSystemUptime()`
  - `public userInfo(ServerRequestInterface $request, ResponseInterface $response)`
  - `public getSettings(ServerRequestInterface $request, ResponseInterface $response)`
  - `public sendChatMessage(ServerRequestInterface $request, ResponseInterface $response)`
  - `public getAIModels(ServerRequestInterface $request, ResponseInterface $response)`
  - `public uploadFile(ServerRequestInterface $request, ResponseInterface $response)`
  - `private getUserChatCount(int $userId)`
  - `private getUserMessageCount(int $userId)`
  - `private getUserLastActivity(int $userId)`
  - `private generateMockAIResponse(string $message)`
  - `public cacheStats(ServerRequestInterface $request, ResponseInterface $response)`
  - `public warmupCache(ServerRequestInterface $request, ResponseInterface $response)`
  - `private calculateAverageResponseTime()`

### ApiController_fixed
- 文件: /src/Controllers/ApiController_fixed.php
- 方法数: 14
- 方法列表:
  - `public __construct(DatabaseService $db,
        CacheService $cache,
        AuthService $authService,
        ChatService $chatService,
        UserService $userService,
        ValidationService $validator,
        RateLimitService $rateLimiter)`
  - `public status(ServerRequestInterface $request, ResponseInterface $response)`
  - `public authStatus(ServerRequestInterface $request, ResponseInterface $response)`
  - `public chatHealth(ServerRequestInterface $request, ResponseInterface $response)`
  - `public userProfile(ServerRequestInterface $request, ResponseInterface $response)`
  - `public updateSettings(ServerRequestInterface $request, ResponseInterface $response)`
  - `public sendMessage(ServerRequestInterface $request, ResponseInterface $response)`
  - `public getChatHistory(ServerRequestInterface $request, ResponseInterface $response)`
  - `public systemStats(ServerRequestInterface $request, ResponseInterface $response)`
  - `public clearCache(ServerRequestInterface $request, ResponseInterface $response)`
  - `public exportData(ServerRequestInterface $request, ResponseInterface $response)`
  - `private extractBearerToken(ServerRequestInterface $request)`
  - `private requireAuth(ServerRequestInterface $request)`
  - `private getSystemUptime()`

### AuthController
- 文件: /src/Controllers/Api/AuthController.php
- 方法数: 13
- 方法列表:
  - `public __construct()`
  - `public login()`
  - `public register()`
  - `public requestPasswordReset()`
  - `public resetPassword()`
  - `public me()`
  - `public logout()`
  - `private getJsonInput()`
  - `private generateUuid()`
  - `private storePasswordResetToken($userId, $token, $expiresAt)`
  - `private getPasswordResetToken($token)`
  - `private deletePasswordResetToken($token)`
  - `private logUserActivity($userId, $action, $data = [])`

### AuthController_new
- 文件: /src/Controllers/AuthController_new.php
- 方法数: 0

### AuthController_old
- 文件: /src/Controllers/AuthController_old.php
- 方法数: 14
- 方法列表:
  - `public __construct(\AlingAi\Services\DatabaseService $db,
        \AlingAi\Services\CacheService $cache,
        AuthService $auth,
        EmailService $emailService)`
  - `public register(ServerRequestInterface $request, ResponseInterface $response)`
  - `public login(ServerRequestInterface $request, ResponseInterface $response)`
  - `public refresh(ServerRequestInterface $request, ResponseInterface $response)`
  - `public logout(ServerRequestInterface $request, ResponseInterface $response)`
  - `public me(ServerRequestInterface $request, ResponseInterface $response)`
  - `public updateProfile(ServerRequestInterface $request, ResponseInterface $response)`
  - `public changePassword(ServerRequestInterface $request, ResponseInterface $response)`
  - `public forgotPassword(ServerRequestInterface $request, ResponseInterface $response)`
  - `public resetPassword(ServerRequestInterface $request, ResponseInterface $response)`
  - `public verifyEmail(ServerRequestInterface $request, ResponseInterface $response)`
  - `protected getCurrentUser($request = null)`
  - `protected validateRequired(array $data, array $required)`
  - `private sendVerificationEmail(array $user)`

### AuthController_old_fixed
- 文件: /src/Controllers/AuthController_old_fixed.php
- 方法数: 14
- 方法列表:
  - `public __construct(\AlingAi\Services\DatabaseService $db,
        \AlingAi\Services\CacheService $cache,
        AuthService $auth,
        EmailService $emailService)`
  - `public register(ServerRequestInterface $request, ResponseInterface $response)`
  - `public login(ServerRequestInterface $request, ResponseInterface $response)`
  - `public refresh(ServerRequestInterface $request, ResponseInterface $response)`
  - `public logout(ServerRequestInterface $request, ResponseInterface $response)`
  - `public me(ServerRequestInterface $request, ResponseInterface $response)`
  - `public updateProfile(ServerRequestInterface $request, ResponseInterface $response)`
  - `public changePassword(ServerRequestInterface $request, ResponseInterface $response)`
  - `public forgotPassword(ServerRequestInterface $request, ResponseInterface $response)`
  - `public resetPassword(ServerRequestInterface $request, ResponseInterface $response)`
  - `public verifyEmail(ServerRequestInterface $request, ResponseInterface $response)`
  - `private getCurrentUser($request)`
  - `private validateRequired(array $data, array $required)`
  - `private sendVerificationEmail(array $user)`

### BaseController
- 文件: /src/Controllers/BaseController.php
- 方法数: 20
- 方法列表:
  - `public __construct(DatabaseServiceInterface $db,
        CacheService $cache)`
  - `protected jsonResponse(ResponseInterface $response,
        array $data,
        int $status = 200,
        array $headers = [])`
  - `protected getJsonData($request)`
  - `protected successResponse(\Psr\Http\Message\ResponseInterface $response,
        $data = null,
        string $message = 'Success',
        int $status = 200)`
  - `protected errorResponse(\Psr\Http\Message\ResponseInterface $response,
        string $message = 'Error',
        int $status = 400,
        $details = null)`
  - `protected validateRequest(array $data, array $rules)`
  - `protected getClientIp($request = null)`
  - `protected getUserAgent($request = null)`
  - `protected logAction(string $action, array $details = [])`
  - `protected handleException(\Exception $e, ResponseInterface $response)`
  - `protected getRequestData($request = null)`
  - `protected getQueryParams($request = null)`
  - `protected getCurrentUser($request = null)`
  - `protected hasPermission(string $permission, $request = null)`
  - `protected validateRequired(array $data, array $required)`
  - `protected getPaginationParams($request = null)`
  - `protected paginatedResponse(ResponseInterface $response,
        array $data,
        int $total,
        array $pagination)`
  - `protected renderPage(ResponseInterface $response,
        string $template,
        array $data = [])`
  - `protected renderErrorPage(ResponseInterface $response,
        int $code,
        string $message)`
  - `protected redirect(ResponseInterface $response, string $url, int $status = 302)`

### ChatController
- 文件: /src/Controllers/ChatController.php
- 方法数: 10
- 方法列表:
  - `public chatInterface(ServerRequestInterface $request, ResponseInterface $response)`
  - `public getConversations(ServerRequestInterface $request, ResponseInterface $response)`
  - `public createConversation(ServerRequestInterface $request, ResponseInterface $response)`
  - `public getConversation(ServerRequestInterface $request, ResponseInterface $response)`
  - `public updateConversation(ServerRequestInterface $request, ResponseInterface $response)`
  - `public deleteConversation(ServerRequestInterface $request, ResponseInterface $response)`
  - `public sendMessage(ServerRequestInterface $request, ResponseInterface $response)`
  - `public getChatHistory(ServerRequestInterface $request, ResponseInterface $response)`
  - `public getMessages(ServerRequestInterface $request, ResponseInterface $response)`
  - `private generateAIResponse(string $userMessage)`

### ConversationController
- 文件: /src/Controllers/ConversationController.php
- 方法数: 11
- 方法列表:
  - `public __construct(DatabaseService $db,
        CacheService $cache)`
  - `public index(ServerRequestInterface $request)`
  - `public show(ServerRequestInterface $request, array $args)`
  - `public store(ServerRequestInterface $request)`
  - `public update(ServerRequestInterface $request, array $args)`
  - `public delete(ServerRequestInterface $request, array $args)`
  - `public statistics(ServerRequestInterface $request)`
  - `public batchAction(ServerRequestInterface $request)`
  - `public search(ServerRequestInterface $request)`
  - `private getCurrentUserId(ServerRequestInterface $request)`
  - `private createResponse()`

### ConversationController_new
- 文件: /src/Controllers/ConversationController_new.php
- 方法数: 9
- 方法列表:
  - `public __construct(DatabaseService $db,
        CacheService $cache,
        ResponseFactoryInterface $responseFactory)`
  - `public index(ServerRequestInterface $request)`
  - `public show(ServerRequestInterface $request)`
  - `public store(ServerRequestInterface $request)`
  - `public update(ServerRequestInterface $request)`
  - `public delete(ServerRequestInterface $request)`
  - `public statistics(ServerRequestInterface $request)`
  - `public batchOperation(ServerRequestInterface $request)`
  - `public search(ServerRequestInterface $request)`

### DocumentController
- 文件: /src/Controllers/DocumentController.php
- 方法数: 11
- 方法列表:
  - `public __construct(CacheService $cacheService)`
  - `public index(ServerRequestInterface $request)`
  - `public show(ServerRequestInterface $request, array $args)`
  - `public store(ServerRequestInterface $request)`
  - `public update(ServerRequestInterface $request, array $args)`
  - `public delete(ServerRequestInterface $request, array $args)`
  - `public download(ServerRequestInterface $request, array $args)`
  - `public statistics(ServerRequestInterface $request)`
  - `public search(ServerRequestInterface $request)`
  - `private getCurrentUserId(ServerRequestInterface $request)`
  - `private createResponse()`

### HomeController
- 文件: /src/Controllers/HomeController.php
- 方法数: 18
- 方法列表:
  - `public index(ServerRequestInterface $request, ResponseInterface $response)`
  - `public status(ServerRequestInterface $request, ResponseInterface $response)`
  - `public apiInfo(ServerRequestInterface $request, ResponseInterface $response)`
  - `public health(ServerRequestInterface $request, ResponseInterface $response)`
  - `public info(ServerRequestInterface $request, ResponseInterface $response)`
  - `private getSystemUptime()`
  - `private checkDatabaseStatus()`
  - `private checkCacheStatus()`
  - `private checkAuthService()`
  - `private checkStorageService()`
  - `private checkQueueService()`
  - `private healthCheckDatabase()`
  - `private healthCheckCache()`
  - `private healthCheckStorage()`
  - `private healthCheckMemory()`
  - `private healthCheckDisk()`
  - `private parseMemoryLimit(string $limit)`
  - `private getBaseUrl(ServerRequestInterface $request)`

### SimpleApiController
- 文件: /src/Controllers/SimpleApiController.php
- 方法数: 6
- 方法列表:
  - `public userInfo(ServerRequestInterface $request, ResponseInterface $response)`
  - `public getSettings(ServerRequestInterface $request, ResponseInterface $response)`
  - `public sendChatMessage(ServerRequestInterface $request, ResponseInterface $response)`
  - `public getAIModels(ServerRequestInterface $request, ResponseInterface $response)`
  - `public uploadFile(ServerRequestInterface $request, ResponseInterface $response)`
  - `private generateMockAIResponse(string $message)`

### SystemController
- 文件: /src/Controllers/SystemController.php
- 方法数: 23
- 方法列表:
  - `public __construct(DatabaseServiceInterface $db,
        CacheService $cache,
        \AlingAi\Services\LoggingService $logger)`
  - `public health(ServerRequestInterface $request, ResponseInterface $response)`
  - `public status(ServerRequestInterface $request, ResponseInterface $response)`
  - `public version(ServerRequestInterface $request, ResponseInterface $response)`
  - `public performance(ServerRequestInterface $request, ResponseInterface $response)`
  - `private getUptime()`
  - `private formatUptime(float $seconds)`
  - `private parseBytes(string $size)`
  - `private formatBytes($bytes, int $precision = 2)`
  - `private getCpuUsage()`
  - `private getCpuCores()`
  - `private getMemoryInfo()`
  - `private getAvailableMemory()`
  - `private getDiskInfo()`
  - `private getDatabasePerformance()`
  - `private getDatabaseConnections()`
  - `private getCachePerformance()`
  - `private getCacheMemoryUsage()`
  - `private getNetworkInfo()`
  - `public databaseTest(ServerRequestInterface $request, ResponseInterface $response)`
  - `public aiTest(ServerRequestInterface $request, ResponseInterface $response)`
  - `private getDatabaseVersion()`
  - `private getDatabaseCharset()`

### UserController
- 文件: /src/Controllers/UserController.php
- 方法数: 21
- 方法列表:
  - `public __construct(CacheService $cacheService, EmailService $emailService)`
  - `public index(ServerRequestInterface $request)`
  - `public show(ServerRequestInterface $request)`
  - `public store(ServerRequestInterface $request)`
  - `public update(ServerRequestInterface $request)`
  - `public delete(ServerRequestInterface $request)`
  - `public statistics(ServerRequestInterface $request)`
  - `public batchOperation(ServerRequestInterface $request)`
  - `public dashboard(ServerRequestInterface $request, ResponseInterface $response)`
  - `public refreshDashboard(ServerRequestInterface $request, ResponseInterface $response)`
  - `public showProfile(ServerRequestInterface $request, ResponseInterface $response)`
  - `public showSettings(ServerRequestInterface $request, ResponseInterface $response)`
  - `private getDashboardData(int $userId)`
  - `private calculateMonthlyUsage(int $userId)`
  - `private calculateStorageUsed(int $userId)`
  - `private getActivityTitle(string $action)`
  - `private getActivityIcon(string $action)`
  - `private getFileIcon(string $type)`
  - `private formatFileSize(int $bytes)`
  - `private getSystemNotifications(int $userId)`
  - `private getChartData(int $userId)`

### WebController
- 文件: /src/Controllers/WebController.php
- 方法数: 17
- 方法列表:
  - `public __construct(\AlingAi\Services\DatabaseServiceInterface $db,
        \AlingAi\Services\CacheService $cache)`
  - `private getPublicConfig()`
  - `public index(ServerRequestInterface $request, ResponseInterface $response)`
  - `public login(ServerRequestInterface $request, ResponseInterface $response)`
  - `public register(ServerRequestInterface $request, ResponseInterface $response)`
  - `public dashboard(ServerRequestInterface $request, ResponseInterface $response)`
  - `public profile(ServerRequestInterface $request, ResponseInterface $response)`
  - `public admin(ServerRequestInterface $request, ResponseInterface $response)`
  - `protected getCurrentUser($request = null)`
  - `private extractToken(ServerRequestInterface $request)`
  - `private getBaseUrl()`
  - `private getSystemStatus()`
  - `private getAdminStats()`
  - `private getServerInfo()`
  - `private getUptime()`
  - `private getMemoryUsage()`
  - `private getDiskUsage()`

### AdminApiController
- 文件: /src/Controllers/Api/AdminApiController.php
- 方法数: 3
- 方法列表:
  - `public __construct()`
  - `public test()`
  - `public getSystemStatus()`

### AdminApiController_simple
- 文件: /src/Controllers/Api/AdminApiController_simple.php
- 方法数: 3
- 方法列表:
  - `public __construct()`
  - `public test()`
  - `public getSystemStatus()`

### AuthApiController
- 文件: /src/Controllers/Api/AuthApiController.php
- 方法数: 12
- 方法列表:
  - `public test()`
  - `public login()`
  - `public register()`
  - `public refresh()`
  - `public getUser()`
  - `public forgotPassword()`
  - `public resetPassword()`
  - `public logout()`
  - `private logFailedLogin(string $email)`
  - `private logSuccessfulLogin(int $userId)`
  - `private sendVerificationEmail(string $email, string $token)`
  - `private sendPasswordResetEmail(string $email, string $token)`

### BaseApiController
- 文件: /src/Controllers/Api/BaseApiController.php
- 方法数: 22
- 方法列表:
  - `public __construct()`
  - `protected loadConfig()`
  - `protected initializeSecurity()`
  - `protected logApiRequest()`
  - `protected requireAuth()`
  - `protected requireAdmin()`
  - `protected getBearerToken()`
  - `protected getRequestData()`
  - `protected validateRequestData(array $data, array $rules)`
  - `protected sendSuccessResponse($data = null, string $message = 'Success', int $statusCode = 200)`
  - `protected sendErrorResponse(string $message, int $statusCode = 400, $details = null)`
  - `protected sendJsonResponse(array $data, int $statusCode = 200)`
  - `protected handleOptionsRequest()`
  - `protected getPaginationParams()`
  - `protected buildPaginatedResponse(array $data, int $total, array $pagination)`
  - `protected getCurrentUser()`
  - `protected hasPermission(string $permission)`
  - `protected getDatabase()`
  - `protected success($data = null, string $message = 'Success', int $statusCode = 200)`
  - `protected error(string $message, $details = null, int $statusCode = 400)`
  - `private createTables(\PDO $pdo)`
  - `public __destruct()`

### ChatApiController
- 文件: /src/Controllers/Api/ChatApiController.php
- 方法数: 15
- 方法列表:
  - `public __construct()`
  - `public test()`
  - `public sendMessage()`
  - `public getConversations()`
  - `public getConversation()`
  - `public deleteConversation()`
  - `public regenerateResponse()`
  - `public getModels()`
  - `private createConversation(int $userId, string $title)`
  - `private getConversationById(int $conversationId, int $userId)`
  - `private saveMessage(int $conversationId, string $role, string $content, ?int $userId, array $metadata = [])`
  - `private updateConversation(int $conversationId)`
  - `private getConversationHistory(int $conversationId, int $limit = 10, ?int $upToMessageId = null)`
  - `private getConversationMessages(int $conversationId, array $pagination)`
  - `private callAiApi(array $history, string $model, bool $stream = false)`

### DatabaseController
- 文件: /src/Controllers/Api/DatabaseController.php
- 方法数: 18
- 方法列表:
  - `public __construct()`
  - `public testConnection()`
  - `public initializeDatabase()`
  - `public getStatus()`
  - `private connect()`
  - `private createDatabase()`
  - `private createTables()`
  - `private seedDatabase()`
  - `private tableExists($tableName)`
  - `private getTableRowCount($tableName)`
  - `private getUsersTableSQL()`
  - `private getConversationsTableSQL()`
  - `private getDocumentsTableSQL()`
  - `private getUserLogsTableSQL()`
  - `private getPasswordResetsTableSQL()`
  - `private getApiTokensTableSQL()`
  - `private jsonResponse($data, $status = 200)`
  - `public handleRequest()`

### FileApiController
- 文件: /src/Controllers/Api/FileApiController.php
- 方法数: 4
- 方法列表:
  - `public __construct()`
  - `public test()`
  - `public uploadFile()`
  - `public getUserFiles()`

### FileApiController_Simple
- 文件: /src/Controllers/Api/FileApiController_Simple.php
- 方法数: 0

### HistoryApiController
- 文件: /src/Controllers/Api/HistoryApiController.php
- 方法数: 9
- 方法列表:
  - `public test()`
  - `public getSessions()`
  - `public getMessages()`
  - `public saveHistory()`
  - `public getHistoryById($requestData = null, $params = null)`
  - `public deleteHistory($requestData = null, $params = null)`
  - `public clearHistory()`
  - `public searchHistory()`
  - `public exportHistory()`

### MonitorApiController
- 文件: /src/Controllers/Api/MonitorApiController.php
- 方法数: 5
- 方法列表:
  - `public __construct()`
  - `public test()`
  - `public getMetrics()`
  - `public getAnalytics()`
  - `public getErrors()`

### MonitorApiController_Simple
- 文件: /src/Controllers/Api/MonitorApiController_Simple.php
- 方法数: 0

### SimpleAuthApiController
- 文件: /src/Controllers/Api/SimpleAuthApiController.php
- 方法数: 3
- 方法列表:
  - `public test()`
  - `public login()`
  - `public verify()`

### SystemApiController
- 文件: /src/Controllers/Api/SystemApiController.php
- 方法数: 5
- 方法列表:
  - `public __construct()`
  - `public test()`
  - `public healthCheck()`
  - `public getStatus()`
  - `private getUptime()`

### SystemApiController_Simple
- 文件: /src/Controllers/Api/SystemApiController_Simple.php
- 方法数: 0

### UserApiController
- 文件: /src/Controllers/Api/UserApiController.php
- 方法数: 3
- 方法列表:
  - `public __construct()`
  - `public test()`
  - `public getProfile()`

### UserApiController_backup
- 文件: /src/Controllers/Api/UserApiController_backup.php
- 方法数: 3
- 方法列表:
  - `public __construct()`
  - `public test()`
  - `public getProfile()`

### UserApiController_simple
- 文件: /src/Controllers/Api/UserApiController_simple.php
- 方法数: 3
- 方法列表:
  - `public __construct()`
  - `public test()`
  - `public getProfile()`

## 依赖分析

### 外部依赖 (Composer)

#### 生产环境依赖
- php: >=7.4.0
- firebase/php-jwt: ^5.0
- guzzlehttp/guzzle: ^7.8
- monolog/monolog: ^2.9
- nyholm/psr7: ^1.8
- php-di/php-di: ^7.0
- phpmailer/phpmailer: ^6.8
- predis/predis: ^1.1
- psr/http-message: ^1.0
- psr/http-server-middleware: ^1.0
- ramsey/uuid: ^4.7
- ratchet/pawl: ^0.4.2
- ratchet/rfc6455: ^0.4.0
- react/http: ^1.10
- react/socket: ^1.15
- slim/psr7: ^1.6
- slim/slim: ^4.10
- vlucas/phpdotenv: ^5.5

#### 开发环境依赖
- phpunit/phpunit: ^9.6
- phpstan/phpstan: ^1.10

## 架构优化建议

### 🏗️ 通用架构建议
1. **依赖注入**: 实现容器化的依赖注入
2. **接口抽象**: 为主要服务定义接口
3. **错误处理**: 统一异常处理机制
4. **日志记录**: 结构化日志记录
5. **缓存策略**: 多层缓存设计
6. **API 文档**: OpenAPI 规范文档
7. **测试覆盖**: 单元测试和集成测试
8. **性能监控**: 应用性能监控 (APM)

