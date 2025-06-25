# AlingAi_pro 文件重组手动步骤指南

由于自动化脚本执行遇到问题，请按照以下步骤手动完成文件重组工作。

## 第一步：创建备份

1. 打开文件资源管理器
2. 导航到 `E:\Code` 目录
3. 右键点击 -> 新建 -> 文件夹
4. 将文件夹命名为 `AlingAi_pro_backup_当前日期`（例如：`AlingAi_pro_backup_20230515`）
5. 打开 `E:\Code\AlingAi_pro` 目录
6. 全选所有文件和文件夹（Ctrl+A）
7. 复制所有文件（Ctrl+C）
8. 导航到刚创建的备份文件夹
9. 粘贴所有文件（Ctrl+V）

## 第二步：创建目录结构

在 `E:\Code\AlingAi_pro` 目录下，手动创建以下文件夹结构：

1. 右键点击 -> 新建 -> 文件夹，创建 `docs` 文件夹
   - 在 `docs` 内创建 `standards` 文件夹
   - 在 `docs` 内创建 `guides` 文件夹
   - 在 `docs` 内创建 `references` 文件夹

2. 右键点击 -> 新建 -> 文件夹，创建 `admin` 文件夹
   - 在 `admin` 内创建 `maintenance` 文件夹
     - 在 `maintenance` 内创建 `tools` 文件夹
       - 在 `tools` 内创建 `resources` 文件夹
         - 在 `resources` 内创建 `portable_php` 文件夹
         - 在 `resources` 内创建 `php_temp` 文件夹
     - 在 `maintenance` 内创建 `reports` 文件夹
     - 在 `maintenance` 内创建 `logs` 文件夹
   - 在 `admin` 内创建 `security` 文件夹

3. 右键点击 -> 新建 -> 文件夹，创建 `scripts` 文件夹
   - 在 `scripts` 内创建 `build` 文件夹
   - 在 `scripts` 内创建 `quality` 文件夹

## 第三步：移动文件

### 1. 公开技术文档

#### 移动到 docs/standards/
1. 找到以下文件：
   - PHP_NAMESPACE_STANDARDS.md
   - PHP_BEST_PRACTICES_GUIDE.md
   - CHINESE_ENCODING_STANDARDS.md
2. 选中这些文件
3. 复制这些文件（Ctrl+C）
4. 导航到 `docs\standards\` 目录
5. 粘贴文件（Ctrl+V）

#### 移动到 docs/guides/
1. 找到以下文件：
   - PHP_DEVELOPMENT_GUIDELINES.md
   - PHP_CODE_QUALITY_AUTOMATION.md
   - PHP_MAINTENANCE_PLAN.md
   - PHP_ERROR_FIX_MANUAL_GUIDE.md
2. 选中这些文件
3. 复制这些文件（Ctrl+C）
4. 导航到 `docs\guides\` 目录
5. 粘贴文件（Ctrl+V）

#### 移动到 docs/references/
1. 找到以下文件：
   - PHP81_SYNTAX_ERROR_COMMON_FIXES.md
   - README.md
2. 选中这些文件
3. 复制这些文件（Ctrl+C）
4. 导航到 `docs\references\` 目录
5. 粘贴文件（Ctrl+V）

### 2. 修复工具

#### 移动到 admin/maintenance/tools/
1. 找到以下文件：
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
   - scan_for_garbled_text.php
   - quick_fix.php
   - validate_only.php
   - systematic_fix.php
   - php81_compatibility_fixes.php
2. 选中这些文件
3. 复制这些文件（Ctrl+C）
4. 导航到 `admin\maintenance\tools\` 目录
5. 粘贴文件（Ctrl+V）

#### 移动到 admin/maintenance/tools/resources/
1. 找到以下文件：
   - php.zip
   - $phpIniPath
2. 选中这些文件
3. 复制这些文件（Ctrl+C）
4. 导航到 `admin\maintenance\tools\resources\` 目录
5. 粘贴文件（Ctrl+V）

#### 移动文件夹
1. 如果存在 `portable_php` 文件夹：
   - 复制该文件夹
   - 导航到 `admin\maintenance\tools\resources\` 目录
   - 粘贴文件夹
2. 如果存在 `php_temp` 文件夹：
   - 复制该文件夹
   - 导航到 `admin\maintenance\tools\resources\` 目录
   - 粘贴文件夹

### 3. 报告和日志

#### 移动到 admin/maintenance/reports/
1. 找到以下文件：
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
   - PHP_FIX_TOOLS_SUMMARY.md
2. 选中这些文件
3. 复制这些文件（Ctrl+C）
4. 导航到 `admin\maintenance\reports\` 目录
5. 粘贴文件（Ctrl+V）

#### 移动到 admin/maintenance/logs/
1. 找到以下文件：
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
2. 选中这些文件
3. 复制这些文件（Ctrl+C）
4. 导航到 `admin\maintenance\logs\` 目录
5. 粘贴文件（Ctrl+V）

### 4. 脚本文件

#### 移动到 scripts/build/
1. 找到以下文件：
   - extract_php.bat
   - extract_php.ps1
   - download_php.ps1
   - download_and_extract_php.ps1
   - simple_download_php.ps1
   - simple_validator.ps1
2. 选中这些文件
3. 复制这些文件（Ctrl+C）
4. 导航到 `scripts\build\` 目录
5. 粘贴文件（Ctrl+V）

#### 移动到 scripts/quality/
1. 找到以下文件：
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
   - run_systematic_fix.bat
2. 选中这些文件
3. 复制这些文件（Ctrl+C）
4. 导航到 `scripts\quality\` 目录
5. 粘贴文件（Ctrl+V）

## 第四步：保留根目录文件

以下文件应保留在根目录（不要移动）：
- README.md
- ALING_AI_PRO_DOCUMENTATION_PLAN.md
- FILE_CLASSIFICATION_TABLE.md
- IMPLEMENTATION_STEPS.md
- SUMMARY.md
- NEXT_STEPS_AFTER_REORGANIZATION.md
- DOCUMENTATION_REORGANIZATION_SUMMARY.md
- MANUAL_REORGANIZATION_INSTRUCTIONS.md
- MANUAL_REORGANIZATION_STEPS.md
- STEP_BY_STEP_MANUAL_REORGANIZATION.md（本文档）

## 第五步：验证整理结果

完成文件整理后，请检查以下几点：

1. 所有文件是否已正确复制到目标位置
2. 根目录下是否只保留了必要的文件
3. 目录结构是否符合设计要求

## 第六步：后续工作

完成文件整理后，请参考 `NEXT_STEPS_AFTER_REORGANIZATION.md` 文档，开始进行后续工作：

1. 开发前台文档中心
2. 开发后台IT运维中心
3. 进行测试和优化
4. 上线和培训

## 注意事项

1. 这是复制而非移动操作，所以原始文件仍然保留在根目录
2. 如果您希望移动而非复制，请在复制完成后删除原始文件
3. 如果某些文件不存在，可以跳过
4. 如果遇到权限问题，请以管理员身份运行文件资源管理器