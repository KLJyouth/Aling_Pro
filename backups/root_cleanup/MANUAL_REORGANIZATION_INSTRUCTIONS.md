# AlingAi_pro 文件整理手动执行步骤

由于批处理脚本执行遇到问题，以下是手动执行文件整理的详细步骤。

## 一、准备工作

### 1.1 备份文件

1. 在 `E:\Code` 目录下创建一个名为 `AlingAi_pro_backup_年月日` 的文件夹，例如 `AlingAi_pro_backup_20230515`
2. 将 `E:\Code\AlingAi_pro` 目录下的所有文件复制到备份文件夹中

### 1.2 创建新的目录结构

在 `E:\Code\AlingAi_pro` 目录下创建以下文件夹：

```
docs\standards
docs\guides
docs\references

admin\maintenance\tools
admin\maintenance\reports
admin\maintenance\logs
admin\security
admin\maintenance\tools\resources
admin\maintenance\tools\resources\portable_php
admin\maintenance\tools\resources\php_temp

scripts\build
scripts\quality
```

## 二、文件整理

### 2.1 移动公开技术文档

#### docs/standards/ - 编码标准文档

将以下文件复制到 `docs\standards\` 目录：
- PHP_NAMESPACE_STANDARDS.md
- PHP_BEST_PRACTICES_GUIDE.md
- CHINESE_ENCODING_STANDARDS.md

#### docs/guides/ - 使用指南

将以下文件复制到 `docs\guides\` 目录：
- PHP_DEVELOPMENT_GUIDELINES.md
- PHP_CODE_QUALITY_AUTOMATION.md
- PHP_MAINTENANCE_PLAN.md
- PHP_ERROR_FIX_MANUAL_GUIDE.md

#### docs/references/ - 参考资料

将以下文件复制到 `docs\references\` 目录：
- PHP81_SYNTAX_ERROR_COMMON_FIXES.md
- UNICODE_ENCODING_RECOMMENDATION.md
- README.md (复制一份，原文件保留在根目录)

### 2.2 移动修复工具

#### admin/maintenance/tools/ - 修复工具

将以下文件复制到 `admin\maintenance\tools\` 目录：
- fix_namespace_consistency.php
- check_interface_implementations.php
- fix_constructor_brackets.php
- fix_private_variables.php
- fix_duplicate_methods.php
- fix_namespace_issues.php
- validate_fixed_files.php
- fix_screenshot_errors.php
- fix_screenshot_specific.php
- fix_all_php_errors.php
- check_all_php_errors.php
- fix_api_doc.php
- check_api_doc.php
- fix_syntax.php
- check_syntax.php
- fix_php_admin_api_errors.php
- fix_php_syntax_errors.php
- fix_bom_markers.php
- fix_php81_remaining_errors.php
- scan_for_garbled_text.php
- quick_fix.php
- validate_only.php
- systematic_fix.php
- php81_compatibility_fixes.php
- fix_php_simple.php

#### admin/maintenance/tools/resources/ - 资源文件

将以下文件复制到 `admin\maintenance\tools\resources\` 目录：
- php.zip
- $phpIniPath
- temp.php

将 `portable_php` 文件夹复制到 `admin\maintenance\tools\resources\portable_php\`
将 `php_temp` 文件夹复制到 `admin\maintenance\tools\resources\php_temp\`

### 2.3 移动报告和日志

#### admin/maintenance/reports/ - 内部报告

将以下文件复制到 `admin\maintenance\reports\` 目录：
- PHP_FIX_COMPLETION_REPORT.md
- PHP_FIX_SUMMARY.md
- php_error_fix_summary.md
- COMPREHENSIVE_PHP_ERROR_SOLUTION.md
- FINAL_PHP_ERRORS_FIX_REPORT.md
- PROJECT_SUMMARY_REPORT.md
- PHP_SCREENSHOT_FIXES_SUMMARY.md
- COMPREHENSIVE_FIX_SUMMARY.md
- PHP_FIX_README.md
- PHP_FILES_FIX_SUMMARY.md
- API_DOC_FIX_REPORT.md
- ADMIN_API_FIX_REPORT.md
- ADMIN_API_FIX_REPORT_UTF8.md
- ADMIN_API_FIX_SUMMARY.txt
- PHP_SYNTAX_ERRORS_FIX_REPORT.md
- PHP_BOM_FIX_REPORT.md
- PHP81_SYNTAX_FIX_SUMMARY.md
- PHP81_REMAINING_ERRORS_FIX_REPORT.md
- FINAL_PHP81_SYNTAX_FIX_REPORT_NEW.md
- PHP_FIX_TOOLS_SUMMARY.md

#### admin/maintenance/logs/ - 日志文件

将以下文件复制到 `admin\maintenance\logs\` 目录：
- CURRENT_PHP_ISSUES.md
- CURRENT_FIX_STATUS.md
- ACTION_PLAN_FOR_75_ERRORS.md
- NEXT_STEPS_PHP_ERROR_FIX.md
- REMAINING_PHP_ERRORS_ANALYSIS.md
- PHP_ERROR_FIX_MASTER_PLAN.md
- COMPREHENSIVE_PHP_ERROR_FIX_PLAN.md
- FINAL_SUMMARY.md
- FIX_SPEECH_PROCESSOR.md
- SCREENSHOT_ERROR_MANUAL_FIX.md
- PHP_SCREENSHOT_ERRORS_FIX_GUIDE.md
- FIX_SCREENSHOT_INSTRUCTIONS.md
- PHP_ERROR_FIX_GUIDE.md

### 2.4 移动脚本文件

#### scripts/build/ - 构建脚本

将以下文件复制到 `scripts\build\` 目录：
- extract_php.bat
- extract_php.ps1
- download_php.ps1
- download_and_extract_php.ps1
- simple_download_php.ps1
- fix_bom_markers.ps1
- simple_validator.ps1

#### scripts/quality/ - 质量检查脚本

将以下文件复制到 `scripts\quality\` 目录：
- run_fix_all.bat
- fix_all_manual.bat
- run_validation.bat
- run_fix_screenshots.bat
- fix_screenshot_errors.bat
- run_php_fix_all.bat
- fix_api_doc_quick.bat
- run_php_fix.bat
- run_fix_all_php_errors.bat
- fix_php_errors.bat
- fix_admin_api_errors.bat
- fix_php_simple.bat
- fix_php_file.bat
- run_fix_bom.bat
- run_systematic_fix.bat

### 2.5 特殊处理文件

以下文件保留在根目录：
- README.md
- ALING_AI_PRO_DOCUMENTATION_PLAN.md
- FILE_CLASSIFICATION_TABLE.md
- IMPLEMENTATION_STEPS.md
- SUMMARY.md
- NEXT_STEPS_AFTER_REORGANIZATION.md
- DOCUMENTATION_REORGANIZATION_SUMMARY.md
- MANUAL_REORGANIZATION_INSTRUCTIONS.md

## 三、验证整理结果

完成文件整理后，请检查以下几点：

1. 所有文件是否已正确复制到目标位置
2. 根目录下是否只保留了必要的文件
3. 目录结构是否符合设计要求

## 四、后续工作

完成文件整理后，请参考 `NEXT_STEPS_AFTER_REORGANIZATION.md` 文档，开始进行后续工作：

1. 开发前台文档中心
2. 开发后台IT运维中心
3. 进行测试和优化
4. 上线和培训 