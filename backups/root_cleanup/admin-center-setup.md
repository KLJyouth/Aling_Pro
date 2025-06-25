# AlingAi_pro 后台IT技术运维中心搭建指南

## 后台IT技术运维中心 - PHP+Laravel 实施方案

### 1. 环境准备

确保您的系统已安装以下软件：

- PHP 8.0 或更高版本
- Composer
- MySQL 或 MariaDB
- Node.js 和 npm (用于前端资源编译)

### 2. 创建 Laravel 项目

在项目根目录下执行以下命令：

```bash
# 创建一个新的 Laravel 项目
composer create-project laravel/laravel admin-center

# 进入项目目录
cd admin-center
```

### 3. 数据库配置

1. 创建数据库：

```sql
CREATE DATABASE alingai_admin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. 配置 `.env` 文件：

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alingai_admin
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. 认证系统

Laravel 提供了内置的认证系统：

```bash
# 安装 Laravel UI 包
composer require laravel/ui

# 生成基本的认证脚手架
php artisan ui bootstrap --auth

# 安装前端依赖
npm install && npm run dev
```

### 5. 创建数据模型

为运维中心创建必要的数据模型：

```bash
# 创建工具模型和迁移
php artisan make:model Tool -m

# 创建报告模型和迁移
php artisan make:model Report -m

# 创建日志模型和迁移
php artisan make:model MaintenanceLog -m

# 创建任务模型和迁移
php artisan make:model Task -m
```

### 6. 设计数据库结构

编辑迁移文件，定义表结构：

1. `database/migrations/xxxx_xx_xx_create_tools_table.php`:

```php
Schema::create('tools', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->string('path');
    $table->string('type');
    $table->json('parameters')->nullable();
    $table->boolean('active')->default(true);
    $table->timestamps();
});
```

2. `database/migrations/xxxx_xx_xx_create_reports_table.php`:

```php
Schema::create('reports', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('content');
    $table->string('type');
    $table->json('metadata')->nullable();
    $table->timestamps();
});
```

3. `database/migrations/xxxx_xx_xx_create_maintenance_logs_table.php`:

```php
Schema::create('maintenance_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->string('action');
    $table->text('description');
    $table->json('details')->nullable();
    $table->string('status');
    $table->timestamps();
});
```

4. `database/migrations/xxxx_xx_xx_create_tasks_table.php`:

```php
Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->foreignId('user_id')->constrained();
    $table->string('status');
    $table->timestamp('due_date')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
});
```

运行迁移：

```bash
php artisan migrate
```

### 7. 创建控制器

```bash
# 创建资源控制器
php artisan make:controller ToolController --resource
php artisan make:controller ReportController --resource
php artisan make:controller LogController --resource
php artisan make:controller TaskController --resource
php artisan make:controller DashboardController
```

### 8. 定义路由

编辑 `routes/web.php` 文件：

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect('/login');
});

// 认证路由
Auth::routes();

// 需要认证的路由
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // 工具管理
    Route::resource('tools', ToolController::class);
    Route::post('/tools/{tool}/execute', [ToolController::class, 'execute'])->name('tools.execute');
    
    // 报告管理
    Route::resource('reports', ReportController::class);
    Route::get('/reports/{report}/download', [ReportController::class, 'download'])->name('reports.download');
    
    // 日志管理
    Route::resource('logs', LogController::class)->only(['index', 'show']);
    
    // 任务管理
    Route::resource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
});
```

### 9. 创建视图

使用 Laravel Blade 模板创建视图。以下是一些关键视图：

1. 仪表板视图 (`resources/views/dashboard.blade.php`)
2. 工具列表和执行视图 (`resources/views/tools/index.blade.php`, `resources/views/tools/show.blade.php`)
3. 报告视图 (`resources/views/reports/index.blade.php`, `resources/views/reports/show.blade.php`)
4. 日志视图 (`resources/views/logs/index.blade.php`, `resources/views/logs/show.blade.php`)
5. 任务管理视图 (`resources/views/tasks/index.blade.php`, `resources/views/tasks/create.blade.php`)

### 10. 实现工具执行功能

在 `ToolController` 中添加执行工具的方法：

```php
public function execute(Request $request, Tool $tool)
{
    // 验证请求参数
    $validated = $request->validate([
        'parameters' => 'sometimes|array',
    ]);
    
    // 记录日志
    $log = new MaintenanceLog([
        'user_id' => auth()->id(),
        'action' => 'tool_execution',
        'description' => "执行工具: {$tool->name}",
        'details' => [
            'tool_id' => $tool->id,
            'parameters' => $validated['parameters'] ?? [],
        ],
        'status' => 'started',
    ]);
    $log->save();
    
    try {
        // 执行工具逻辑
        $result = $this->executeToolScript($tool, $validated['parameters'] ?? []);
        
        // 更新日志
        $log->update([
            'status' => 'completed',
            'details' => array_merge($log->details, ['result' => $result]),
        ]);
        
        return back()->with('success', '工具执行成功');
    } catch (\Exception $e) {
        // 更新日志
        $log->update([
            'status' => 'failed',
            'details' => array_merge($log->details, ['error' => $e->getMessage()]),
        ]);
        
        return back()->with('error', '工具执行失败: ' . $e->getMessage());
    }
}

private function executeToolScript(Tool $tool, array $parameters)
{
    // 根据工具类型执行不同的脚本
    switch ($tool->type) {
        case 'php':
            return $this->executePHPScript($tool->path, $parameters);
        case 'batch':
            return $this->executeBatchScript($tool->path, $parameters);
        case 'powershell':
            return $this->executePowerShellScript($tool->path, $parameters);
        default:
            throw new \Exception("不支持的工具类型: {$tool->type}");
    }
}

// 实现各种脚本执行方法...
```

### 11. 实现报告生成功能

在 `ReportController` 中添加报告生成和下载方法：

```php
public function generate(Request $request)
{
    $validated = $request->validate([
        'type' => 'required|string',
        'parameters' => 'sometimes|array',
    ]);
    
    // 根据类型生成报告
    switch ($validated['type']) {
        case 'code_quality':
            $report = $this->generateCodeQualityReport($validated['parameters'] ?? []);
            break;
        case 'error_summary':
            $report = $this->generateErrorSummaryReport($validated['parameters'] ?? []);
            break;
        default:
            return back()->with('error', '不支持的报告类型');
    }
    
    return redirect()->route('reports.show', $report);
}

public function download(Report $report)
{
    // 根据报告类型生成不同格式
    switch ($report->type) {
        case 'code_quality':
        case 'error_summary':
            $pdf = $this->generatePDF($report);
            return response()->streamDownload(
                fn () => print($pdf->output()),
                "{$report->slug}.pdf",
                ['Content-Type' => 'application/pdf']
            );
        default:
            return back()->with('error', '不支持的下载格式');
    }
}

// 实现各种报告生成方法...
```

### 12. 实现监控面板

创建监控面板视图和控制器方法：

```php
// DashboardController.php
public function index()
{
    $recentLogs = MaintenanceLog::latest()->take(10)->get();
    $pendingTasks = Task::where('status', 'pending')->count();
    $toolsCount = Tool::count();
    $reportsCount = Report::count();
    
    // 获取系统状态信息
    $systemInfo = [
        'php_version' => phpversion(),
        'memory_usage' => memory_get_usage(true),
        'disk_free_space' => disk_free_space('/'),
        'disk_total_space' => disk_total_space('/'),
    ];
    
    return view('dashboard', compact(
        'recentLogs', 
        'pendingTasks', 
        'toolsCount', 
        'reportsCount',
        'systemInfo'
    ));
}
```

### 13. 集成工具和脚本

将 `admin/maintenance/tools` 目录下的工具集成到系统中：

```php
// 创建数据库填充器
php artisan make:seeder ToolsSeeder

// 在 ToolsSeeder 中添加代码
public function run()
{
    $toolsDir = base_path('admin/maintenance/tools');
    $files = File::files($toolsDir);
    
    foreach ($files as $file) {
        if ($file->getExtension() === 'php') {
            Tool::create([
                'name' => $file->getFilenameWithoutExtension(),
                'slug' => Str::slug($file->getFilenameWithoutExtension()),
                'description' => '从文件系统导入的PHP工具',
                'path' => $file->getPathname(),
                'type' => 'php',
                'parameters' => [],
                'active' => true,
            ]);
        } elseif (in_array($file->getExtension(), ['bat', 'cmd'])) {
            Tool::create([
                'name' => $file->getFilenameWithoutExtension(),
                'slug' => Str::slug($file->getFilenameWithoutExtension()),
                'description' => '从文件系统导入的批处理工具',
                'path' => $file->getPathname(),
                'type' => 'batch',
                'parameters' => [],
                'active' => true,
            ]);
        } elseif ($file->getExtension() === 'ps1') {
            Tool::create([
                'name' => $file->getFilenameWithoutExtension(),
                'slug' => Str::slug($file->getFilenameWithoutExtension()),
                'description' => '从文件系统导入的PowerShell工具',
                'path' => $file->getPathname(),
                'type' => 'powershell',
                'parameters' => [],
                'active' => true,
            ]);
        }
    }
}

// 运行填充器
php artisan db:seed --class=ToolsSeeder
```

### 14. 添加权限系统

使用 Spatie 的权限包：

```bash
composer require spatie/laravel-permission
```

发布配置：

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

创建角色和权限：

```php
// 创建数据库填充器
php artisan make:seeder RolesAndPermissionsSeeder

// 在 RolesAndPermissionsSeeder 中添加代码
public function run()
{
    // 重置缓存
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // 创建权限
    Permission::create(['name' => 'view tools']);
    Permission::create(['name' => 'execute tools']);
    Permission::create(['name' => 'manage tools']);
    
    Permission::create(['name' => 'view reports']);
    Permission::create(['name' => 'generate reports']);
    Permission::create(['name' => 'manage reports']);
    
    Permission::create(['name' => 'view logs']);
    
    Permission::create(['name' => 'view tasks']);
    Permission::create(['name' => 'create tasks']);
    Permission::create(['name' => 'manage tasks']);
    
    // 创建角色并分配权限
    $role = Role::create(['name' => 'admin']);
    $role->givePermissionTo(Permission::all());
    
    $role = Role::create(['name' => 'developer']);
    $role->givePermissionTo([
        'view tools', 'execute tools',
        'view reports', 'generate reports',
        'view logs',
        'view tasks', 'create tasks',
    ]);
    
    $role = Role::create(['name' => 'viewer']);
    $role->givePermissionTo([
        'view tools',
        'view reports',
        'view logs',
        'view tasks',
    ]);
}

// 运行填充器
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### 15. 本地运行和测试

```bash
php artisan serve
```

这将启动一个本地开发服务器，通常在 http://localhost:8000 访问。

### 16. 部署

1. **准备生产环境**：
   - 设置生产服务器（Nginx/Apache, PHP, MySQL）
   - 配置域名和SSL证书
   - 设置适当的文件权限

2. **部署代码**：
   ```bash
   git clone [repository] /var/www/admin-center
   cd /var/www/admin-center
   composer install --no-dev --optimize-autoloader
   npm install && npm run build
   cp .env.example .env
   php artisan key:generate
   ```

3. **配置环境**：
   - 编辑 `.env` 文件，设置生产环境的数据库和其他配置
   - 配置缓存：
     ```bash
     php artisan config:cache
     php artisan route:cache
     php artisan view:cache
     ```

4. **数据库迁移**：
   ```bash
   php artisan migrate --force
   php artisan db:seed --class=RolesAndPermissionsSeeder
   php artisan db:seed --class=ToolsSeeder
   ```

5. **配置Web服务器**：
   - 设置网站根目录为 `/var/www/admin-center/public`
   - 配置适当的重写规则

### 17. 监控和维护

1. **设置监控**：
   - 使用 Laravel Telescope 进行应用监控
   - 配置服务器监控工具（如 New Relic, Datadog）
   - 设置日志监控和告警

2. **定期维护**：
   - 设置定期备份
   - 定期更新依赖
   - 监控安全漏洞

### 下一步：测试和优化

完成后台IT技术运维中心搭建后，需要进行全面测试和优化，请参考 `NEXT_STEPS_AFTER_REORGANIZATION.md` 文档中的第三部分内容。 