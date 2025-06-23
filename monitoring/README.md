# AlingAi API监控系统

AlingAi API实时监控系统是一个功能完备的API监控解决方案，用于监控各类API的性能、可用性和SLA遵循情况。

## 主要功能

- 实时监控第三方API、被第三方接入的API以及内部API
- 高性能指标收集和处理
- 基于规则的智能告警系统
- 多种告警通道（邮件、WebSocket、Webhook等）
- 支持TimescaleDB高效存储时序数据
- 提供完整的Prometheus集成
- 内置Grafana仪表盘
- 自动健康检查
- 容器化部署支持

## 系统架构

```
+---------------------+      +---------------------+
|    API网关层        |      |   监控服务层        |
|  (请求代理和监控)   |      | (指标收集和处理)    |
+----------+----------+      +---------+-----------+
           |                           |
           v                           v
+---------------------+      +---------------------+
|    存储层           |      |   告警层           |
| (时序数据库和缓存)  |      | (规则引擎和通知)   |
+---------------------+      +---------------------+
           |                           |
           v                           v
+---------------------+      +---------------------+
|   可视化层          |      |   健康检查层       |
| (Grafana仪表盘)     |      | (SLA监控)          |
+---------------------+      +---------------------+
```

## 安装和部署

### 前置条件

- Node.js 14.x 或更高版本
- Docker 和 Docker Compose（用于容器化部署）
- TimescaleDB 或 PostgreSQL（可选，也支持SQLite）

### 本地开发环境设置

1. 克隆代码库：

```bash
git clone https://github.com/your-org/alingai-api-monitoring.git
cd alingai-api-monitoring
```

2. 安装依赖：

```bash
npm install
```

3. 配置环境变量：

创建 `.env` 文件并配置必要的环境变量。

4. 启动开发服务器：

```bash
npm run dev
```

### 使用Docker Compose部署

1. 确保已安装Docker和Docker Compose

2. 启动所有服务：

```bash
docker-compose up -d
```

3. 访问服务：
   - 监控系统：http://localhost:8080
   - API网关：http://localhost:9000
   - Grafana：http://localhost:3000 (用户名/密码: admin/admin)
   - Prometheus：http://localhost:9090

## 配置

系统配置文件位于 `config/config.json`，包含以下主要配置项：

- **system**: 系统基本配置
- **api_gateway**: API网关配置
- **metrics_collector**: 指标收集器配置
- **storage**: 存储配置
- **alerting**: 告警系统配置
- **health_check**: 健康检查配置
- **scheduler**: 任务调度器配置
- **api_endpoints**: 被监控的API端点配置

## 性能测试

运行负载测试：

```bash
npm run load-test
```

自定义负载测试参数：

```bash
npm run load-test -- --url http://localhost:8080 --connections 50 --duration 60
```

## 贡献指南

欢迎贡献代码或提出改进建议。请遵循以下步骤：

1. Fork代码库
2. 创建功能分支
3. 提交更改
4. 创建Pull Request

## 许可证

MIT 