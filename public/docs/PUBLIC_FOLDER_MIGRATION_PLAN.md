# AlingAi Pro 5.0 - Public 文件夹迁移计划

## 迁移目标
将所有需要通过 Web 直接访问的文件迁移到 `public` 文件夹中，以符合现代 Web 应用程序的最佳实践。

## 需要迁移的文件类别

### 1. API 服务器文件 (api/)
- [ ] api_server.php → public/api/server.php
- [ ] simple_api_server.php → public/api/simple-server.php  
- [ ] clean_api_server.php → public/api/clean-server.php

### 2. 测试工具文件 (test/)
- [ ] comprehensive_api_test.php → public/test/api-comprehensive.php
- [ ] simple_api_test.php → public/test/api-simple.php
- [ ] direct_api_test.php → public/test/api-direct.php
- [ ] http_api_test.php → public/test/api-http.php
- [ ] integration_test.php → public/test/integration.php
- [ ] performance_test.php → public/test/performance.php
- [ ] simple_connection_test.php → public/test/connection.php
- [ ] simple_route_test.php → public/test/route.php

### 3. 系统监控文件 (monitor/)
- [ ] quick_health_check.php → public/monitor/health.php
- [ ] ai_service_health_check.php → public/monitor/ai-health.php
- [ ] performance_monitoring_health.php → public/monitor/performance.php
- [ ] ai_service_integration_health.php → public/monitor/ai-integration.php

### 4. 系统工具文件 (tools/)
- [ ] router.php → public/router.php (如果不存在)
- [ ] debug.php → public/debug.php (如果不存在)

### 5. 安装和部署工具 (install/)
- [ ] install/test_server.php → public/install/test-server.php
- [ ] install/test_api_cli.php → public/install/test-api-cli.php

## 迁移步骤
1. 创建目录结构
2. 复制文件并更新路径引用
3. 更新文件中的相对路径
4. 测试迁移后的功能
5. 清理旧文件（备份后）

## 路径更新规则
- `__DIR__ . '/vendor/autoload.php'` → `__DIR__ . '/../../vendor/autoload.php'`
- `__DIR__ . '/src/'` → `__DIR__ . '/../../src/'`
- `__DIR__ . '/storage/'` → `__DIR__ . '/../../storage/'`
- `__DIR__ . '/config/'` → `__DIR__ . '/../../config/'`

## 预期收益
- 符合 Web 应用程序安全最佳实践
- 清晰的文件组织结构
- 更好的访问控制
- 简化的 URL 结构
