# AlingAi_pro 文件分类表

本文档详细列出了AlingAi_pro项目中所有文件的分类和目标位置，用于指导文件整理工作。

## 公开技术文档（前台）

### docs/standards/ - 编码标准文档

| 文件名 | 原始位置 | 目标位置 | 说明 |
|-------|---------|---------|------|
| PHP_NAMESPACE_STANDARDS.md | 根目录 | docs/standards/ | PHP命名空间规范 |
| PHP_BEST_PRACTICES_GUIDE.md | 根目录 | docs/standards/ | PHP最佳实践指南 |
| CHINESE_ENCODING_STANDARDS.md | 根目录 | docs/standards/ | 中文编码标准 |

### docs/guides/ - 使用指南

| 文件名 | 原始位置 | 目标位置 | 说明 |
|-------|---------|---------|------|
| PHP_DEVELOPMENT_GUIDELINES.md | 根目录 | docs/guides/ | PHP开发指南 |
| PHP_CODE_QUALITY_AUTOMATION.md | 根目录 | docs/guides/ | 代码质量自动化检查指南 |
| PHP_MAINTENANCE_PLAN.md | 根目录 | docs/guides/ | PHP维护计划 |
| PHP_ERROR_FIX_MANUAL_GUIDE.md | 根目录 | docs/guides/ | PHP错误修复手册指南 |

### docs/references/ - 参考资料

| 文件名 | 原始位置 | 目标位置 | 说明 |
|-------|---------|---------|------|
| PHP81_SYNTAX_ERROR_COMMON_FIXES.md | 根目录 | docs/references/ | PHP 8.1语法错误常见修复 |
| UNICODE_ENCODING_RECOMMENDATION.md | 根目录 | docs/references/ | Unicode编码建议 |
| README.md | 根目录 | docs/ | 项目主文档（副本保留在根目录） |

## 管理员后台（仅管理员可见）

### admin/maintenance/tools/ - 修复工具

| 文件名 | 原始位置 | 目标位置 | 说明 |
|-------|---------|---------|------|
| fix_namespace_consistency.php | 根目录 | admin/maintenance/tools/ | 命名空间一致性修复工具 |
| check_interface_implementations.php | 根目录 | admin/maintenance/tools/ | 接口实现检查工具 |
| fix_constructor_brackets.php | 根目录 | admin/maintenance/tools/ | 构造函数括号修复工具 |
| fix_private_variables.php | 根目录 | admin/maintenance/tools/ | 私有变量修复工具 |
| fix_duplicate_methods.php | 根目录 | admin/maintenance/tools/ | 重复方法修复工具 |
| fix_namespace_issues.php | 根目录 | admin/maintenance/tools/ | 命名空间问题修复工具 |
| validate_fixed_files.php | 根目录 | admin/maintenance/tools/ | 修复文件验证工具 |
| fix_screenshot_errors.php | 根目录 | admin/maintenance/tools/ | 截图错误修复工具 |
| fix_screenshot_specific.php | 根目录 | admin/maintenance/tools/ | 特定截图修复工具 |
| fix_all_php_errors.php | 根目录 | admin/maintenance/tools/ | PHP错误全面修复工具 |
| check_all_php_errors.php | 根目录 | admin/maintenance/tools/ | PHP错误检查工具 |
| fix_api_doc.php | 根目录 | admin/maintenance/tools/ | API文档修复工具 |
| check_api_doc.php | 根目录 | admin/maintenance/tools/ | API文档检查工具 |
| fix_syntax.php | 根目录 | admin/maintenance/tools/ | 语法修复工具 |
| check_syntax.php | 根目录 | admin/maintenance/tools/ | 语法检查工具 |
| fix_php_admin_api_errors.php | 根目录 | admin/maintenance/tools/ | PHP管理API错误修复工具 |
| fix_php_syntax_errors.php | 根目录 | admin/maintenance/tools/ | PHP语法错误修复工具 |
| fix_bom_markers.php | 根目录 | admin/maintenance/tools/ | BOM标记修复工具 |
| fix_php81_remaining_errors.php | 根目录 | admin/maintenance/tools/ | PHP 8.1剩余错误修复工具 |
| fix_chinese_tokenizer_unicode.php | 根目录 | admin/maintenance/tools/ | 中文分词器Unicode修复工具 |
| fix_chinese_tokenizer_utf8.php | 根目录 | admin/maintenance/tools/ | 中文分词器UTF-8修复工具 |
| scan_for_garbled_text.php | 根目录 | admin/maintenance/tools/ | 乱码文本扫描工具 |
| quick_fix.php | 根目录 | admin/maintenance/tools/ | 快速修复工具 |
| validate_only.php | 根目录 | admin/maintenance/tools/ | 仅验证工具 |
| systematic_fix.php | 根目录 | admin/maintenance/tools/ | 系统性修复工具 |
| php81_compatibility_fixes.php | 根目录 | admin/maintenance/tools/ | PHP 8.1兼容性修复工具 |
| fix_tokenizer.php | 根目录 | admin/maintenance/tools/ | 分词器修复工具 |
| fix_php_simple.php | 根目录 | admin/maintenance/tools/ | PHP简单修复工具 |

### admin/maintenance/reports/ - 内部报告

| 文件名 | 原始位置 | 目标位置 | 说明 |
|-------|---------|---------|------|
| PHP_FIX_COMPLETION_REPORT.md | 根目录 | admin/maintenance/reports/ | PHP修复完成报告 |
| PHP_FIX_SUMMARY.md | 根目录 | admin/maintenance/reports/ | PHP修复摘要 |
| php_error_fix_summary.md | 根目录 | admin/maintenance/reports/ | PHP错误修复摘要 |
| COMPREHENSIVE_PHP_ERROR_SOLUTION.md | 根目录 | admin/maintenance/reports/ | 综合PHP错误解决方案 |
| FINAL_PHP_ERRORS_FIX_REPORT.md | 根目录 | admin/maintenance/reports/ | 最终PHP错误修复报告 |
| PROJECT_SUMMARY_REPORT.md | 根目录 | admin/maintenance/reports/ | 项目摘要报告 |
| PHP_SCREENSHOT_FIXES_SUMMARY.md | 根目录 | admin/maintenance/reports/ | PHP截图修复摘要 |
| COMPREHENSIVE_FIX_SUMMARY.md | 根目录 | admin/maintenance/reports/ | 综合修复摘要 |
| PHP_FIX_README.md | 根目录 | admin/maintenance/reports/ | PHP修复自述文件 |
| PHP_FILES_FIX_SUMMARY.md | 根目录 | admin/maintenance/reports/ | PHP文件修复摘要 |
| API_DOC_FIX_REPORT.md | 根目录 | admin/maintenance/reports/ | API文档修复报告 |
| ADMIN_API_FIX_REPORT.md | 根目录 | admin/maintenance/reports/ | 管理API修复报告 |
| ADMIN_API_FIX_REPORT_UTF8.md | 根目录 | admin/maintenance/reports/ | 管理API修复报告(UTF-8) |
| ADMIN_API_FIX_SUMMARY.txt | 根目录 | admin/maintenance/reports/ | 管理API修复摘要 |
| PHP_SYNTAX_ERRORS_FIX_REPORT.md | 根目录 | admin/maintenance/reports/ | PHP语法错误修复报告 |
| PHP_BOM_FIX_REPORT.md | 根目录 | admin/maintenance/reports/ | PHP BOM修复报告 |
| PHP81_SYNTAX_FIX_SUMMARY.md | 根目录 | admin/maintenance/reports/ | PHP 8.1语法修复摘要 |
| PHP81_REMAINING_ERRORS_FIX_REPORT.md | 根目录 | admin/maintenance/reports/ | PHP 8.1剩余错误修复报告 |
| FINAL_PHP81_SYNTAX_FIX_REPORT.md | 根目录 | admin/maintenance/reports/ | 最终PHP 8.1语法修复报告 |
| FINAL_PHP81_SYNTAX_FIX_REPORT_NEW.md | 根目录 | admin/maintenance/reports/ | 最终PHP 8.1语法修复报告(新) |
| CHINESE_TOKENIZER_FIX_REPORT.md | 根目录 | admin/maintenance/reports/ | 中文分词器修复报告 |
| PHP_FIX_TOOLS_SUMMARY.md | 根目录 | admin/maintenance/reports/ | PHP修复工具摘要 |

### admin/maintenance/logs/ - 日志文件

| 文件名 | 原始位置 | 目标位置 | 说明 |
|-------|---------|---------|------|
| CURRENT_PHP_ISSUES.md | 根目录 | admin/maintenance/logs/ | 当前PHP问题 |
| CURRENT_FIX_STATUS.md | 根目录 | admin/maintenance/logs/ | 当前修复状态 |
| ACTION_PLAN_FOR_75_ERRORS.md | 根目录 | admin/maintenance/logs/ | 75个错误的行动计划 |
| NEXT_STEPS_PHP_ERROR_FIX.md | 根目录 | admin/maintenance/logs/ | PHP错误修复下一步 |
| REMAINING_PHP_ERRORS_ANALYSIS.md | 根目录 | admin/maintenance/logs/ | 剩余PHP错误分析 |
| PHP_ERROR_FIX_MASTER_PLAN.md | 根目录 | admin/maintenance/logs/ | PHP错误修复总体计划 |
| COMPREHENSIVE_PHP_ERROR_FIX_PLAN.md | 根目录 | admin/maintenance/logs/ | 综合PHP错误修复计划 |
| FINAL_SUMMARY.md | 根目录 | admin/maintenance/logs/ | 最终摘要 |
| FIX_SPEECH_PROCESSOR.md | 根目录 | admin/maintenance/logs/ | 语音处理器修复 |
| SCREENSHOT_ERROR_MANUAL_FIX.md | 根目录 | admin/maintenance/logs/ | 截图错误手动修复 |
| PHP_SCREENSHOT_ERRORS_FIX_GUIDE.md | 根目录 | admin/maintenance/logs/ | PHP截图错误修复指南 |
| FIX_SCREENSHOT_INSTRUCTIONS.md | 根目录 | admin/maintenance/logs/ | 截图修复说明 |
| PHP_ERROR_FIX_GUIDE.md | 根目录 | admin/maintenance/logs/ | PHP错误修复指南 |

## 通用脚本

### scripts/build/ - 构建脚本

| 文件名 | 原始位置 | 目标位置 | 说明 |
|-------|---------|---------|------|
| extract_php.bat | 根目录 | scripts/build/ | 提取PHP批处理 |
| extract_php.ps1 | 根目录 | scripts/build/ | 提取PHP PowerShell脚本 |
| download_php.ps1 | 根目录 | scripts/build/ | 下载PHP PowerShell脚本 |
| download_and_extract_php.ps1 | 根目录 | scripts/build/ | 下载并提取PHP PowerShell脚本 |
| simple_download_php.ps1 | 根目录 | scripts/build/ | 简单下载PHP PowerShell脚本 |
| fix_bom_markers.ps1 | 根目录 | scripts/build/ | BOM标记修复PowerShell脚本 |
| simple_validator.ps1 | 根目录 | scripts/build/ | 简单验证器PowerShell脚本 |

### scripts/quality/ - 质量检查脚本

| 文件名 | 原始位置 | 目标位置 | 说明 |
|-------|---------|---------|------|
| run_fix_all.bat | 根目录 | scripts/quality/ | 运行所有修复批处理 |
| fix_all_manual.bat | 根目录 | scripts/quality/ | 手动修复所有批处理 |
| run_validation.bat | 根目录 | scripts/quality/ | 运行验证批处理 |
| run_fix_screenshots.bat | 根目录 | scripts/quality/ | 运行截图修复批处理 |
| fix_screenshot_errors.bat | 根目录 | scripts/quality/ | 修复截图错误批处理 |
| run_php_fix_all.bat | 根目录 | scripts/quality/ | 运行PHP修复所有批处理 |
| fix_api_doc_quick.bat | 根目录 | scripts/quality/ | 快速修复API文档批处理 |
| run_php_fix.bat | 根目录 | scripts/quality/ | 运行PHP修复批处理 |
| run_fix_all_php_errors.bat | 根目录 | scripts/quality/ | 运行修复所有PHP错误批处理 |
| fix_php_errors.bat | 根目录 | scripts/quality/ | 修复PHP错误批处理 |
| fix_admin_api_errors.bat | 根目录 | scripts/quality/ | 修复管理API错误批处理 |
| fix_php_simple.bat | 根目录 | scripts/quality/ | PHP简单修复批处理 |
| fix_php_file.bat | 根目录 | scripts/quality/ | PHP文件修复批处理 |
| run_fix_bom.bat | 根目录 | scripts/quality/ | 运行修复BOM批处理 |
| run_systematic_fix.bat | 根目录 | scripts/quality/ | 运行系统性修复批处理 |

## 特殊处理文件

| 文件名 | 原始位置 | 目标位置 | 说明 |
|-------|---------|---------|------|
| README.md | 根目录 | 根目录 | 项目主文档（保留在根目录） |
| ALING_AI_PRO_DOCUMENTATION_PLAN.md | 根目录 | 根目录 | 文档整理方案（新创建） |
| FILE_CLASSIFICATION_TABLE.md | 根目录 | 根目录 | 文件分类表（新创建） |
| php.zip | 根目录 | admin/maintenance/tools/resources/ | PHP安装包 |
| $phpIniPath | 根目录 | admin/maintenance/tools/resources/ | PHP配置路径 |
| temp.php | 根目录 | admin/maintenance/tools/resources/ | 临时PHP文件 |
| portable_php/ | 根目录 | admin/maintenance/tools/resources/portable_php/ | 便携式PHP |
| php_temp/ | 根目录 | admin/maintenance/tools/resources/php_temp/ | PHP临时文件 |
| .git/ | 根目录 | 根目录 | Git版本控制（保留在根目录） |
| config/ | 根目录 | 根目录 | 配置文件夹（保留在根目录） |
| tests/ | 根目录 | 根目录 | 测试文件夹（保留在根目录） | 