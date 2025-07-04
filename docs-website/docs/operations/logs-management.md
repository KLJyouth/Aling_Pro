---
id: logs-management
title: 日志管理
sidebar_label: 日志管理
---

# 日志管理

## 功能概述

日志管理模块提供了全面的系统日志收集、存储、分析和管理功能，包括日志概览、系统日志、错误日志、访问日志和安全日志管理。通过这些功能，管理员可以全面了解系统运行状态，及时发现并解决潜在的问题。

## 日志概览

日志概览提供了系统各类日志的汇总视图，包括：

- **日志统计**：各类日志数量统计和趋势图
- **重要事件**：最近发生的重要日志事件
- **日志健康度**：日志收集和存储的健康状态
- **异常检测**：基于AI的日志异常检测结果

![日志概览](../assets/logs-overview.png)

## 系统日志

系统日志功能提供对操作系统和应用系统日志的管理：

- **实时日志**：实时查看系统日志流
- **历史日志**：查询和分析历史系统日志
- **日志过滤**：按时间、级别、来源等条件过滤日志
- **日志导出**：将系统日志导出为CSV、JSON等格式
- **日志分析**：系统日志的统计分析和趋势图

## 错误日志

错误日志功能专注于收集和分析系统中的错误和异常：

- **错误分类**：按严重程度、模块、错误类型等分类错误日志
- **错误详情**：查看错误的详细信息，包括堆栈跟踪
- **错误趋势**：错误发生频率的历史趋势图
- **相关错误**：自动关联相似或相关的错误
- **解决方案**：常见错误的解决方案推荐

## 访问日志

访问日志功能记录和分析系统访问情况：

- **用户访问**：记录用户访问系统的详细信息
- **API调用**：记录API调用的请求和响应信息
- **资源访问**：记录对系统资源的访问情况
- **性能指标**：记录访问响应时间等性能指标
- **访问统计**：按用户、IP、时间等维度统计访问情况

## 安全日志

安全日志功能专注于收集和分析与安全相关的日志：

- **登录日志**：记录用户登录成功/失败的详细信息
- **权限变更**：记录用户权限变更的操作日志
- **敏感操作**：记录对敏感数据和功能的操作
- **安全告警**：记录系统检测到的安全告警
- **安全审计**：支持安全合规审计需求

## 使用指南

### 日志概览

1. 在左侧导航菜单中点击"日志管理"
2. 默认进入"日志概览"页面
3. 查看各类日志的汇总信息
4. 点击具体指标可查看详细信息

### 系统日志

1. 在左侧导航菜单中点击"日志管理" > "系统日志"
2. 使用顶部的时间选择器选择要查看的时间范围
3. 使用筛选器按级别、来源等条件过滤日志
4. 点击日志条目可查看详细信息
5. 使用右上角的导出按钮导出日志

### 错误日志

1. 在左侧导航菜单中点击"日志管理" > "错误日志"
2. 使用顶部的时间选择器和筛选器查找特定错误
3. 查看错误统计和趋势图
4. 点击错误条目可查看详细错误信息和堆栈跟踪
5. 查看系统推荐的解决方案

### 访问日志

1. 在左侧导航菜单中点击"日志管理" > "访问日志"
2. 使用筛选器按用户、IP、时间等条件过滤访问日志
3. 查看访问统计和趋势图
4. 点击访问条目可查看详细的请求和响应信息
5. 使用右上角的导出按钮导出访问日志

### 安全日志

1. 在左侧导航菜单中点击"日志管理" > "安全日志"
2. 使用筛选器按事件类型、严重程度等条件过滤安全日志
3. 查看安全事件统计和趋势图
4. 点击安全事件可查看详细信息
5. 使用右上角的导出按钮导出安全日志报告

## 高级功能

### 日志告警

配置日志告警规则，当日志满足特定条件时自动触发告警：

1. 在日志管理页面点击"告警配置"
2. 点击"添加告警规则"
3. 设置告警条件、通知方式和接收人
4. 保存告警规则

### 日志保留策略

配置日志保留策略，自动管理日志存储空间：

1. 在日志管理页面点击"存储配置"
2. 设置各类日志的保留时间
3. 配置日志归档策略
4. 保存配置 