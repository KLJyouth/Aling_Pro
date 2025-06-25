<?php
/**
 * IT运维中心入口文件
 * 
 * 提供IT运维中心的功能导航，包括安全管理、运维报告和日志管理
 */

// 设置页面标题
$pageTitle = "IT运维中心";

// 包含公共头部
include_once "../includes/header.php";

// 检查用户权�?
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php"];
    exit;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mt-4 mb-4">IT运维中心</h1>
            <div class="alert alert-info">
                <strong>欢迎使用IT运维中心�?/strong> 本模块提供全面的系统监控、安全管理、日志分析和报告功能�?
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 安全管理模块 -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">安全管理</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">提供全面的系统安全管理功能，包括安全概览、权限管理、备份管理、用户管理和角色管理�?/p>
                    <div class="list-group">
                        <a href="security/index.php" class="list-group-item list-group-item-action">安全概览</a>
                        <a href="security/permissions.php" class="list-group-item list-group-item-action">权限管理</a>
                        <a href="security/backups.php" class="list-group-item list-group-item-action">备份管理</a>
                        <a href="security/users.php" class="list-group-item list-group-item-action">用户管理</a>
                        <a href="security/roles.php" class="list-group-item list-group-item-action">角色管理</a>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="security/index.php" class="btn btn-primary">进入安全管理</a>
                </div>
            </div>
        </div>

        <!-- 运维报告模块 -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">运维报告</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">提供全面的系统运行状态报告功能，包括报告概览、系统性能报告、安全审计报告、错误统计报告和自定义报告�?/p>
                    <div class="list-group">
                        <a href="reports/index.php" class="list-group-item list-group-item-action">报告概览</a>
                        <a href="reports/performance.php" class="list-group-item list-group-item-action">系统性能报告</a>
                        <a href="reports/security.php" class="list-group-item list-group-item-action">安全审计报告</a>
                        <a href="reports/errors.php" class="list-group-item list-group-item-action">错误统计报告</a>
                        <a href="reports/custom.php" class="list-group-item list-group-item-action">自定义报�?/a>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="reports/index.php" class="btn btn-success">进入运维报告</a>
                </div>
            </div>
        </div>

        <!-- 日志管理模块 -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">日志管理</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">提供全面的系统日志收集、存储、分析和管理功能，包括日志概览、系统日志、错误日志、访问日志和安全日志管理�?/p>
                    <div class="list-group">
                        <a href="logs/index.php" class="list-group-item list-group-item-action">日志概览</a>
                        <a href="logs/system.php" class="list-group-item list-group-item-action">系统日志</a>
                        <a href="logs/errors.php" class="list-group-item list-group-item-action">错误日志</a>
                        <a href="logs/access.php" class="list-group-item list-group-item-action">访问日志</a>
                        <a href="logs/security.php" class="list-group-item list-group-item-action">安全日志</a>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="logs/index.php" class="btn btn-info">进入日志管理</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 维护工具 -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h5 class="card-title mb-0">维护工具</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">系统维护工具和报告，用于系统维护和问题排查�?/p>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="list-group">
                                <a href="maintenance/logs/PHP_ERROR_FIX_MASTER_PLAN.md" class="list-group-item list-group-item-action">PHP错误修复主计�?/a>
                                <a href="maintenance/logs/CURRENT_PHP_ISSUES.md" class="list-group-item list-group-item-action">当前PHP问题</a>
                                <a href="maintenance/logs/ACTION_PLAN_FOR_75_ERRORS.md" class="list-group-item list-group-item-action">75个错误的行动计划</a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="list-group">
                                <a href="maintenance/reports/PHP_FIX_COMPLETION_REPORT.md" class="list-group-item list-group-item-action">PHP修复完成报告</a>
                                <a href="maintenance/reports/php_error_fix_summary.md" class="list-group-item list-group-item-action">PHP错误修复摘要</a>
                                <a href="maintenance/reports/PHP_FIX_SUMMARY.md" class="list-group-item list-group-item-action">PHP修复摘要</a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="list-group">
                                <a href="maintenance/tools/fix_namespace_consistency.php" class="list-group-item list-group-item-action">修复命名空间一致�?/a>
                                <a href="maintenance/tools/check_interface_implementations.php" class="list-group-item list-group-item-action">检查接口实�?/a>
                                <a href="maintenance/tools/validate_fixed_files.php" class="list-group-item list-group-item-action">验证已修复文�?/a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="maintenance/" class="btn btn-warning">查看所有维护工�?/a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// 包含公共底部
include_once "../includes/footer.php";
?> 
