# AlingAi Pro 会员系统

## 功能概述

AlingAi Pro会员系统是一个完整的会员管理解决方案，提供以下核心功能：

1. **会员等级管理**：支持多级会员体系，每个等级具有不同的权益和价格
2. **会员订阅管理**：支持月度和年度订阅，自动续费，以及订阅升级/降级
3. **会员积分系统**：用户可以通过多种方式获取和消费积分
4. **会员特权系统**：每个会员等级拥有不同的特权和权益
5. **会员推荐系统**：用户可以通过推荐获得奖励
6. **会员等级自动升级**：基于积分、消费或时长自动升级会员等级
7. **会员使用分析和报表**：提供全面的会员数据分析

## 系统架构

### 数据模型

- `User`: 用户模型，包含基本用户信息和会员相关属性
- `MembershipLevel`: 会员等级模型，定义不同的会员级别
- `MembershipSubscription`: 会员订阅模型，记录用户的订阅信息
- `MemberPoint`: 会员积分模型，记录积分获取和消费
- `MemberPrivilege`: 会员特权模型，定义不同的会员特权
- `MemberReferral`: 会员推荐模型，记录用户推荐关系

### 服务类

- `SubscriptionService`: 处理会员订阅相关逻辑
- `PointService`: 处理积分获取、消费和统计
- `ReferralService`: 处理推荐奖励逻辑
- `LevelUpgradeService`: 处理会员等级自动升级
- `MembershipAnalyticsService`: 提供会员数据分析

## 前台功能

- 会员等级展示页面
- 会员注册和登录
- 会员中心
  - 个人资料管理
  - 会员等级信息
  - 积分明细
  - 推荐管理
  - 订阅管理
  - API密钥管理

## 后台功能

- 会员管理
  - 会员列表和详情
  - 会员等级设置
  - 订阅管理
- 积分管理
  - 积分规则设置
  - 积分记录查询
- 推荐管理
  - 推荐规则设置
  - 推荐记录查询
- 数据分析
  - 会员增长趋势
  - 会员等级分布
  - 收入分析
  - 留存率分析

## 安装和配置

### 安装步骤

1. 运行数据库迁移：`php artisan migrate`
2. 填充初始数据：`php artisan db:seed --class=MembershipSeeder`
3. 配置定时任务：`php artisan schedule:run`

### 配置选项

在`.env`文件中可以配置以下选项：

```
# 会员系统配置
MEMBERSHIP_REFERRAL_POINTS=100
MEMBERSHIP_AUTO_UPGRADE=true
MEMBERSHIP_POINTS_EXPIRATION_DAYS=365
```

## API接口

会员系统提供以下API接口：

- `GET /api/membership/levels`: 获取所有会员等级
- `GET /api/membership/current`: 获取当前用户的会员信息
- `POST /api/membership/subscribe`: 订阅会员
- `GET /api/membership/points`: 获取用户积分信息
- `POST /api/membership/referral`: 创建推荐

## 开发者文档

详细的开发者文档请参考 `docs/membership` 目录。
