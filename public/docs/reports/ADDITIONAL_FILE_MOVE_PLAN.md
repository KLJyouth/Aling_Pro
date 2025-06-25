# 额外文件移动计划

## 1. 测试和服务器相关文件 -> public/tools/server/

| 原始位置 | 新位置 |
|---------|-------|
| `run_tests.bat` | `public/tools/server/run_tests.bat` |
| `phpunit.xml.dist` | `public/tools/server/phpunit.xml.dist` |
| `run_server.bat` | `public/tools/server/run_server.bat` |
| `server.bat` | `public/tools/server/server.bat` |
| `start_server.bat` | `public/tools/server/start_server.bat` |
| `start_server_new.bat` | `public/tools/server/start_server_new.bat` |

## 2. 错误修复和安全相关文件 -> public/tools/fixes/

| 原始位置 | 新位置 |
|---------|-------|
| `fix_interface_implementation.php` | `public/tools/fixes/fix_interface_implementation.php` |
| `fix_php_errors.php` | `public/tools/fixes/fix_php_errors.php` |
| `fix_advanced_attack_surface_management.php` | `public/tools/fixes/fix_advanced_attack_surface_management.php` |
| `fix_stub_methods.php` | `public/tools/fixes/fix_stub_methods.php` |
| `comprehensive_fix.php` | `public/tools/fixes/comprehensive_fix.php` |
| `debug_autoloader.php` | `public/tools/fixes/debug_autoloader.php` |
| `final_example_replacer.php` | `public/tools/fixes/final_example_replacer.php` |
| `comprehensive_example_replacer.php` | `public/tools/fixes/comprehensive_example_replacer.php` |
| `replace_examples.php` | `public/tools/fixes/replace_examples.php` |
| `fix_api_endpoints.php` | `public/tools/fixes/fix_api_endpoints.php` |

## 3. 测试工具 -> public/tools/tests/

| 原始位置 | 新位置 |
|---------|-------|
| `simple_chat_test.php` | `public/tools/tests/simple_chat_test.php` |
| `test_chat_system.php` | `public/tools/tests/test_chat_system.php` |
| `test_api_endpoints.php` | `public/tools/tests/test_api_endpoints.php` |
| `test_sqlite.php` | `public/tools/tests/test_sqlite.php` |
| `test_php.php` | `public/tools/tests/test_php.php` |
| `test.php` | `public/tools/tests/test.php` |

## 4. 报告和文档文件 -> public/docs/reports/

| 原始位置 | 新位置 |
|---------|-------|
| `TESTING_IMPLEMENTATION_SUMMARY.md` | `public/docs/reports/testing_implementation_summary.md` |
| `ENHANCED_CHAT_API_FIX_REPORT.txt` | `public/docs/reports/enhanced_chat_api_fix_report.txt` |
| `AUTH_API_FIX_REPORT.txt` | `public/docs/reports/auth_api_fix_report.txt` |
| `FINAL_PHP_ERROR_FIX_REPORT.txt` | `public/docs/reports/final_php_error_fix_report.txt` |
| `FINAL_PHP_ERROR_FIX_REPORT.md` | `public/docs/reports/final_php_error_fix_report.md` |
| `PHP_ERRORS_FIX_REPORT.md` | `public/docs/reports/php_errors_fix_report.md` |
| `AdvancedAttackSurfaceManagement_fix_report.md` | `public/docs/reports/advanced_attack_surface_management_fix_report.md` |
| `AASM_Fix_Instructions.md` | `public/docs/reports/aasm_fix_instructions.md` |
| `fix_AdvancedAttackSurfaceManagement.md` | `public/docs/reports/fix_advanced_attack_surface_management.md` |
| `FINAL_COMPREHENSIVE_FIX_REPORT.md` | `public/docs/reports/final_comprehensive_fix_report.md` |
| `CHAT_SYSTEM_IMPLEMENTATION_SUMMARY.md` | `public/docs/reports/chat_system_implementation_summary.md` |
| `EXAMPLE_IMPLEMENTATION_UPGRADE_REPORT.md` | `public/docs/reports/example_implementation_upgrade_report.md` |
| `SECURITY_IMPLEMENTATION_SUMMARY.md` | `public/docs/reports/security_implementation_summary.md` |
| `api_endpoints_fix_summary.md` | `public/docs/reports/api_endpoints_fix_summary.md` |
| `IMPLEMENTATION_TRACKING.md` | `public/docs/reports/implementation_tracking.md` |
| `PROJECT_SUMMARY.md` | `public/docs/project_summary.md` |
| `PUBLIC_FOLDER_UPGRADE_PLAN.md` | `public/docs/plans/public_folder_upgrade_plan.md` |
| `OPTIMIZATION_SUMMARY.md` | `public/docs/reports/optimization_summary.md` |
| `ALINGAI_UPGRADE_ENHANCEMENT_PLAN.md` | `public/docs/plans/alingai_upgrade_enhancement_plan.md` |
| `FILE_CLASSIFICATION_TABLE.md` | `public/docs/file_classification_table.md` |

## 5. 配置文件 -> public/config/

| 原始位置 | 新位置 |
|---------|-------|
| `php.ini.local` | `public/config/php.ini.local` |
| `php_extensions.ini` | `public/config/php_extensions.ini` |
| `phpunit.xml.dist` (复制) | `public/config/phpunit.xml.dist` |

## 6. 开发工具 -> public/tools/dev/

| 原始位置 | 新位置 |
|---------|-------|
| `server_router.php` | `public/tools/dev/server_router.php` |
| `router.php` | `public/tools/dev/router.php` |
| `stubs.php` | `public/tools/dev/stubs.php` |
| `output.json` | `public/tools/dev/output.json` |
| `project_progress_temp.txt` | `public/tools/dev/project_progress_temp.txt` |

## 7. admin-center和admin目录合并到public/admin/

| 原始位置 | 新位置 |
|---------|-------|
| `admin-center/README.md` | `public/admin/docs/admin_center_readme.md` |
| `admin-center/public/*` | `public/admin/` |
| `admin-center/app/*` | `public/admin/app/` |
| `admin-center/routes/*` | `public/admin/routes/` |
| `admin-center/config/*` | `public/admin/config/` |
| `admin-center/index.php` | `public/admin/admin_center_index.php` |
| `admin-center/composer.json` | `public/admin/composer.json` |
| `admin-center/docs/*` | `public/admin/docs/` |
| `admin-center/tools/*` | `public/admin/tools/` |
| `admin-center/tests/*` | `public/admin/tests/` |
| `admin-center/database/*` | `public/admin/database/` |
| `admin-center/resources/*` | `public/admin/resources/` |

## 8. 不需要移动的文件

以下文件由于特殊原因不需要移动：

- `Composer-Setup.exe` - 安装工具，保留在根目录
- `setup_admin_center.bat`, `setup_docs_center.bat`, `setup_docusaurus.bat` - 设置脚本，保留在根目录
- `ADMIN_MERGE_SUMMARY.md` - 合并总结，保留在根目录
- `PROJECT_STATUS.md` - 项目状态信息，保留在根目录
- `ROOT_CLEANUP_SUMMARY.md` - 清理总结，保留在根目录
- `FILE_ORGANIZATION_PLAN.md` - 文件整理计划，保留在根目录
- `move_files.ps1` - 文件移动脚本，保留在根目录
- `cleanup_files.ps1` - 文件清理脚本，保留在根目录
- `docs-website/` - 文档网站源代码，保留在根目录

## 9. 需要创建的目录

在执行移动操作前，需要创建以下目录：

- `public/tools/server/`
- `public/tools/tests/`
- `public/tools/dev/`
- `public/docs/plans/`
- `public/admin/app/`
- `public/admin/routes/`
- `public/admin/config/`
- `public/admin/tools/`
- `public/admin/tests/`
- `public/admin/database/`
- `public/admin/resources/` 