# AlingAi API监控系统使用文档

## 目录

1. [系统概述](#系统概述)
2. [功能特点](#功能特点)
3. [系统架构](#系统架构)
4. [安装与配置](#安装与配置)
5. [使用指南](#使用指南)
6. [命令行工具](#命令行工具)
7. [API文档](#api文档)
8. [扩展与集成](#扩展与集成)
9. [故障排除](#故障排除)
10. [常见问题](#常见问题)

## 系统概述

AlingAi API监控系统是一个功能完备的API实时监控与运维管理平台，支持监控内部API、接入的第三方API以及被第三方接入的API。系统提供全面的性能指标收集、实时监控、异常预警和历史数据分析功能，帮助开发团队和运维人员确保API服务的高可用性和稳定性。

## 功能特点

### 全面监控能力

- **内部API监控**：监控系统内部API的性能、可用性和错误率
- **第三方API监控**：监控接入的外部第三方服务API
- **入站API监控**：监控被第三方接入的API服务质量

### 丰富的监控指标

- **性能指标**：响应时间、延迟、吞吐量
- **可用性指标**：正常运行时间、宕机时间、错误率
- **质量指标**：HTTP状态码分布、响应内容验证

### 告警与通知

- **多渠道告警**：支持邮件、短信、WebSocket、Webhook等告警通道
- **可定制阈值**：支持为不同API设置不同的告警阈值
- **告警降噪**：智能告警聚合和去重，避免告警风暴
- **分级告警**：根据严重程度不同配置不同告警策略

### 数据分析与可视化

- **实时仪表盘**：直观显示API健康状态和性能指标
- **趋势分析**：查看API性能和可用性的历史趋势
- **异常检测**：基于历史数据自动发现异常模式

### 其他特性

- **健康检查**：定期检查API可用性和响应时间
- **SLA监控**：自动追踪API的SLA遵从性
- **无侵入式设计**：代理模式无需修改现有API代码
- **可扩展架构**：易于扩展以支持更多API类型和监控特性

## 系统架构

AlingAi API监控系统采用模块化架构设计，主要包含以下核心组件：

### 核心组件

1. **API网关/代理层**：接收和转发API请求，收集调用指标
2. **监控收集器**：处理和存储监控数据
3. **告警系统**：检测异常并通过多渠道发送告警
4. **时序数据库**：存储历史监控数据
5. **分析引擎**：对数据进行处理和分析
6. **可视化仪表盘**：展示监控数据和分析结果
7. **健康检查服务**：定期检查API可用性
8. **调度器**：协调各组件的工作

### 数据流

```
API请求 → API网关/代理层 → 目标API
                ↓
            指标收集器 → 时序数据库 → 分析引擎 → 可视化仪表盘
                ↓
            告警系统 → 告警通知(邮件/短信/WebSocket等)
```

## 安装与配置

### 系统要求

- PHP 7.4或更高版本
- PostgreSQL 12+（推荐使用TimescaleDB扩展）
- Composer
- 建议：Redis（用于缓存和队列）

### 安装步骤

1. **克隆代码库**

```bash
git clone https://github.com/your-organization/alingai-monitoring.git
cd alingai-monitoring
```

2. **安装依赖**

```bash
composer install
```

3. **配置数据库**

创建PostgreSQL数据库并安装TimescaleDB扩展：

```bash
psql -U postgres
CREATE DATABASE alingai_monitoring;
\c alingai_monitoring
CREATE EXTENSION IF NOT EXISTS timescaledb CASCADE;
```

4. **配置环境变量**

复制`.env.example`文件为`.env`并编辑相关配置：

```bash
cp .env.example .env
```

编辑`.env`文件，配置数据库连接和其他设置：

```
# 数据库配置
MONITORING_DB_HOST=localhost
MONITORING_DB_PORT=5432
MONITORING_DB_DATABASE=alingai_monitoring
MONITORING_DB_USERNAME=postgres
MONITORING_DB_PASSWORD=your_password

# 告警配置
MONITORING_EMAIL_ALERTS_ENABLED=true
MONITORING_EMAIL_FROM=monitoring@alingai.com
MONITORING_EMAIL_RECIPIENTS=admin@example.com,ops@example.com
```

5. **运行迁移脚本**

```bash
php artisan monitoring:install
```

6. **启动监控系统**

```bash
php bin/monitor.php start --background
```

### 配置说明

主要配置文件位于`config/monitoring.php`，包括以下配置部分：

- **数据库**：数据库连接设置
- **告警**：各种告警通道配置
- **健康检查**：API健康检查配置
- **调度器**：后台任务调度设置
- **服务**：预配置的API服务
- **数据保留**：监控数据保留策略
- **界面**：UI相关配置

## 使用指南

### 添加API监控

1. **通过命令行工具添加**

```bash
php bin/monitor.php add-api --name=payment-gateway --url=https://api.payment.example.com \
  --auth-type=bearer --token=your_api_token \
  --health-endpoint=/health --health-interval=60
```

2. **通过Web界面添加**

访问`/admin/monitoring/config`页面，点击"添加API"按钮，填写API信息并保存。

### 查看监控仪表盘

访问`/admin/monitoring`查看主仪表盘，包括：

- 所有API的健康状态概览
- 按类型分组的API列表
- 重要性能指标和警报汇总

点击具体API可查看详细信息，包括：

- 响应时间趋势图
- 错误率图表
- 最近的错误记录
- 可用性百分比

### 配置告警

1. **在配置文件中设置全局告警策略**

编辑`config/monitoring.php`文件中的`alerts`部分。

2. **为特定API设置告警阈值**

通过Web界面或命令行工具为单个API设置特定阈值：

```bash
php bin/monitor.php set-threshold --api=payment-gateway --metric=response_time --value=2.0
```

### 查看和管理告警

访问`/admin/monitoring/alerts`页面查看所有告警记录，可以：

- 按严重程度、API或时间筛选告警
- 确认和解决告警
- 暂停特定API的告警通知

## 命令行工具

系统提供了功能强大的命令行工具，用于管理和操作监控系统：

```bash
php bin/monitor.php [命令] [选项]
```

### 常用命令

- **start**：启动监控系统
  ```bash
  php bin/monitor.php start --background
  ```

- **stop**：停止监控系统
  ```bash
  php bin/monitor.php stop
  ```

- **status**：检查监控系统状态
  ```bash
  php bin/monitor.php status
  ```

- **check**：执行一次健康检查
  ```bash
  php bin/monitor.php check
  ```

- **add-api**：添加API配置
  ```bash
  php bin/monitor.php add-api --name=example-api --url=https://api.example.com
  ```

- **list-apis**：列出所有API配置
  ```bash
  php bin/monitor.php list-apis
  ```

- **cleanup**：清理过期数据
  ```bash
  php bin/monitor.php cleanup --days=30
  ```

完整命令列表和选项，请运行：

```bash
php bin/monitor.php help
```

## API文档

监控系统提供了RESTful API，用于与其他系统集成：

### 监控数据API

- **GET /api/monitoring/metrics**：获取监控指标
- **GET /api/monitoring/alerts**：获取告警数据
- **GET /api/monitoring/health**：获取健康状态
- **POST /api/monitoring/config**：更新监控配置

详细API文档请参考[API参考手册](/docs/api-reference.md)。

## 扩展与集成

### 添加自定义告警通道

1. 创建实现`AlertChannelInterface`的新类
2. 在服务提供者中注册该通道
3. 在配置中启用该通道

```php
// 创建自定义通道类
class CustomChannel implements AlertChannelInterface
{
    public function send(array $alert): bool
    {
        // 实现发送逻辑
    }
    
    public function sendResolution(array $alert): bool
    {
        // 实现告警解决通知逻辑
    }
}

// 在服务提供者中注册
$alertManager->addChannel('custom', new CustomChannel($config, $logger));
```

### 集成到CI/CD流程

可以在CI/CD流程中集成API健康检查：

```bash
# 在部署后执行健康检查
php bin/monitor.php check --api=newly-deployed-service
```

## 故障排除

### 常见问题

1. **监控系统无法启动**

检查日志文件`logs/monitor.log`：

```bash
tail -f logs/monitor.log
```

确保数据库配置正确且TimescaleDB扩展已安装。

2. **告警没有发送**

检查告警配置和告警通道设置：

```bash
php bin/monitor.php check-alerts-config
```

3. **性能问题**

对于大规模部署，建议：
- 使用独立的TimescaleDB服务器
- 启用Redis缓存
- 调整数据保留策略
- 考虑使用异步处理告警

## 常见问题

**Q: 系统占用多少资源？**

A: 在中等规模部署(监控约50个API)下，系统需要约500MB内存和少量CPU资源。主要资源消耗在数据库I/O和告警处理。

**Q: 是否支持高可用部署？**

A: 是的，可以通过以下方式实现高可用：
- 使用主从复制的TimescaleDB集群
- 在多台服务器上运行监控系统
- 使用负载均衡器分发请求

**Q: 如何添加对新API协议的支持？**

A: 扩展`ApiGateway`类并添加新的请求处理方法，然后在服务提供者中注册。

**Q: 系统收集哪些数据？是否有隐私问题？**

A: 系统默认只收集API性能和可用性指标，不收集请求体或响应内容。可以配置额外的敏感数据屏蔽规则以增强隐私保护。

## Prometheus 集成

AlingAi API 监控系统现已完全集成 Prometheus，支持以下特性：

1. **指标暴露端点**：系统在 `/metrics` 路径上暴露了标准的 Prometheus 指标格式
2. **丰富的指标类型**：
   - 计数器 (Counter)：如 API 请求总数、错误总数
   - 仪表 (Gauge)：如当前活跃连接数、响应时间
   - 直方图 (Histogram)：如 API 响应时间分布
3. **自定义标签**：所有指标都带有详细的标签，如 `endpoint`、`method`、`status`
4. **标准指标集**：包含 API 性能、请求率、错误率等指标

### 可用指标列表

| 指标名称 | 类型 | 描述 | 标签 |
|---------|------|-----|------|
| api_requests_total | Counter | API请求总数 | endpoint, method, status |
| api_errors_total | Counter | API错误总数 | endpoint, method, status, error_type |
| api_response_time | Gauge | API响应时间 | endpoint, method |
| api_response_time_seconds | Histogram | API响应时间分布 | endpoint, method |
| active_sessions | Gauge | 当前活跃会话数 | - |
| database_query_time_seconds | Histogram | 数据库查询时间分布 | query_type, table |

## Grafana 仪表盘

系统预先配置了一组 Grafana 仪表盘，用于可视化监控指标：

1. **API 概览仪表盘**：显示所有 API 的总体性能和健康状况
2. **API 详情仪表盘**：针对特定 API 的详细性能指标
3. **告警仪表盘**：显示当前和历史告警信息
4. **SLA 仪表盘**：显示 API 的 SLA 符合情况

### 访问 Grafana

在部署系统后，可以通过以下地址访问 Grafana：

- URL: http://localhost:3000
- 默认用户名: admin
- 默认密码: admin

### 自定义仪表盘

用户可以基于系统提供的指标创建自定义仪表盘，步骤如下：

1. 登录 Grafana
2. 点击 "Create" -> "Dashboard"
3. 添加面板，选择 "Prometheus" 数据源
4. 使用 PromQL 查询语言查询所需指标
5. 保存仪表盘

## 负载测试

系统提供了内置的负载测试工具，用于测试监控系统的性能和稳定性：

```bash
npm run load-test -- --url http://localhost:8080 --connections 50 --duration 60
```

测试结果将保存在 `results` 目录中，包含详细的性能指标数据。 