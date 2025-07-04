<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlingAi Pro - 安装向导</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/install.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mt-5">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">AlingAi Pro 安装向导</h3>
                    </div>
                    <div class="card-body">
                        <div class="step-indicator">
                            <ul class="steps">
                                <li class="step active" data-step="1">
                                    <span class="step-icon">1</span>
                                    <span class="step-text">环境检查</span>
                                </li>
                                <li class="step" data-step="2">
                                    <span class="step-icon">2</span>
                                    <span class="step-text">数据库配置</span>
                                </li>
                                <li class="step" data-step="3">
                                    <span class="step-icon">3</span>
                                    <span class="step-text">系统设置</span>
                                </li>
                                <li class="step" data-step="4">
                                    <span class="step-icon">4</span>
                                    <span class="step-text">安装</span>
                                </li>
                                <li class="step" data-step="5">
                                    <span class="step-icon">5</span>
                                    <span class="step-text">完成</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="step-content mt-4">
                            <div class="step-pane active" id="step1">
                                <h4>环境检查</h4>
                                <p>正在检查您的系统是否满足安装要求...</p>
                                
                                <div class="requirements mt-4">
                                    <div class="requirement" id="php-version">
                                        <span class="requirement-name">PHP版本 >= 8.1</span>
                                        <span class="requirement-status">检查中...</span>
                                    </div>
                                    <div class="requirement" id="php-pdo">
                                        <span class="requirement-name">PDO扩展</span>
                                        <span class="requirement-status">检查中...</span>
                                    </div>
                                    <div class="requirement" id="php-mbstring">
                                        <span class="requirement-name">Mbstring扩展</span>
                                        <span class="requirement-status">检查中...</span>
                                    </div>
                                    <div class="requirement" id="php-json">
                                        <span class="requirement-name">JSON扩展</span>
                                        <span class="requirement-status">检查中...</span>
                                    </div>
                                    <div class="requirement" id="php-openssl">
                                        <span class="requirement-name">OpenSSL扩展</span>
                                        <span class="requirement-status">检查中...</span>
                                    </div>
                                    <div class="requirement" id="storage-writable">
                                        <span class="requirement-name">存储目录可写</span>
                                        <span class="requirement-status">检查中...</span>
                                    </div>
                                    <div class="requirement" id="config-writable">
                                        <span class="requirement-name">配置目录可写</span>
                                        <span class="requirement-status">检查中...</span>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info mt-4" id="requirements-checking">
                                    正在检查系统要求，请稍候...
                                </div>
                                <div class="alert alert-success mt-4 d-none" id="requirements-success">
                                    恭喜！您的系统满足所有安装要求。
                                </div>
                                <div class="alert alert-danger mt-4 d-none" id="requirements-error">
                                    您的系统不满足一些安装要求，请解决上述问题后再继续。
                                </div>
                                
                                <div class="text-end mt-4">
                                    <button type="button" class="btn btn-primary" id="step1-next" disabled>下一步</button>
                                </div>
                            </div>
                            
                            <div class="step-pane" id="step2">
                                <h4>数据库配置</h4>
                                <p>请选择数据库类型并提供连接信息。</p>
                                
                                <form id="database-form" class="mt-4">
                                    <div class="mb-3">
                                        <label class="form-label">数据库类型</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="db_type" id="db-mysql" value="mysql" checked>
                                            <label class="form-check-label" for="db-mysql">MySQL</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="db_type" id="db-sqlite" value="sqlite">
                                            <label class="form-check-label" for="db-sqlite">SQLite</label>
                                        </div>
                                    </div>
                                    
                                    <div id="mysql-config">
                                        <div class="mb-3">
                                            <label for="db-host" class="form-label">数据库主机</label>
                                            <input type="text" class="form-control" id="db-host" name="db_host" value="localhost">
                                        </div>
                                        <div class="mb-3">
                                            <label for="db-port" class="form-label">数据库端口</label>
                                            <input type="text" class="form-control" id="db-port" name="db_port" value="3306">
                                        </div>
                                        <div class="mb-3">
                                            <label for="db-name" class="form-label">数据库名称</label>
                                            <input type="text" class="form-control" id="db-name" name="db_name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="db-user" class="form-label">数据库用户名</label>
                                            <input type="text" class="form-control" id="db-user" name="db_user" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="db-password" class="form-label">数据库密码</label>
                                            <input type="password" class="form-control" id="db-password" name="db_password">
                                        </div>
                                    </div>
                                    
                                    <div id="sqlite-config" style="display: none;">
                                        <div class="alert alert-info">
                                            将使用SQLite数据库，数据库文件将保存在存储目录中。
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-info" id="test-connection">测试连接</button>
                                    </div>
                                    
                                    <div class="alert alert-success mt-3 d-none" id="connection-success">
                                        数据库连接成功！
                                    </div>
                                    <div class="alert alert-danger mt-3 d-none" id="connection-error">
                                        数据库连接失败，请检查您的配置。
                                    </div>
                                </form>
                                
                                <div class="text-end mt-4">
                                    <button type="button" class="btn btn-secondary" id="step2-prev">上一步</button>
                                    <button type="button" class="btn btn-primary" id="step2-next" disabled>下一步</button>
                                </div>
                            </div>
                            
                            <div class="step-pane" id="step3">
                                <h4>系统设置</h4>
                                <p>请配置系统的基本设置。</p>
                                
                                <form id="system-form" class="mt-4">
                                    <div class="mb-3">
                                        <label for="app-name" class="form-label">应用名称</label>
                                        <input type="text" class="form-control" id="app-name" name="app_name" value="AlingAi Pro" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="app-url" class="form-label">应用URL</label>
                                        <input type="url" class="form-control" id="app-url" name="app_url" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="admin-email" class="form-label">管理员邮箱</label>
                                        <input type="email" class="form-control" id="admin-email" name="admin_email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="admin-password" class="form-label">管理员密码</label>
                                        <input type="password" class="form-control" id="admin-password" name="admin_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="admin-password-confirm" class="form-label">确认密码</label>
                                        <input type="password" class="form-control" id="admin-password-confirm" name="admin_password_confirm" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">时区</label>
                                        <select class="form-select" id="timezone" name="timezone">
                                            <option value="Asia/Shanghai">亚洲/上海 (GMT+8)</option>
                                            <option value="Asia/Hong_Kong">亚洲/香港 (GMT+8)</option>
                                            <option value="Asia/Tokyo">亚洲/东京 (GMT+9)</option>
                                            <option value="America/New_York">美国/纽约 (GMT-5)</option>
                                            <option value="Europe/London">欧洲/伦敦 (GMT+0)</option>
                                            <option value="Europe/Paris">欧洲/巴黎 (GMT+1)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">语言</label>
                                        <select class="form-select" id="locale" name="locale">
                                            <option value="zh_CN">简体中文</option>
                                            <option value="en">English</option>
                                        </select>
                                    </div>
                                </form>
                                
                                <div class="text-end mt-4">
                                    <button type="button" class="btn btn-secondary" id="step3-prev">上一步</button>
                                    <button type="button" class="btn btn-primary" id="step3-next">下一步</button>
                                </div>
                            </div>
                            
                            <div class="step-pane" id="step4">
                                <h4>安装</h4>
                                <p>系统将开始安装，请耐心等待...</p>
                                
                                <div class="progress mt-4">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" id="install-progress" style="width: 0%"></div>
                                </div>
                                
                                <div class="mt-4">
                                    <div class="install-log" id="install-log">
                                        <div class="log-entry">准备安装...</div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-success mt-4 d-none" id="install-success">
                                    安装成功！
                                </div>
                                <div class="alert alert-danger mt-4 d-none" id="install-error">
                                    安装过程中发生错误，请查看日志了解详情。
                                </div>
                                
                                <div class="text-end mt-4">
                                    <button type="button" class="btn btn-secondary" id="step4-prev">上一步</button>
                                    <button type="button" class="btn btn-primary" id="step4-next" disabled>下一步</button>
                                </div>
                            </div>
                            
                            <div class="step-pane" id="step5">
                                <h4>安装完成</h4>
                                <div class="alert alert-success">
                                    <h5>恭喜！AlingAi Pro 已成功安装。</h5>
                                    <p>您现在可以开始使用系统了。</p>
                                </div>
                                
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h5>访问信息</h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>前台地址：</strong> <a href="/" target="_blank" id="frontend-url">/</a></p>
                                        <p><strong>管理后台：</strong> <a href="/admin" target="_blank" id="admin-url">/admin</a></p>
                                        <p><strong>管理员邮箱：</strong> <span id="admin-email-display"></span></p>
                                    </div>
                                </div>
                                
                                <div class="alert alert-warning mt-4">
                                    <h5>安全提示</h5>
                                    <p>为了安全起见，请确保删除安装目录。</p>
                                    <button type="button" class="btn btn-sm btn-warning" id="remove-install-dir">删除安装目录</button>
                                </div>
                                
                                <div class="text-end mt-4">
                                    <a href="/" class="btn btn-success">进入网站</a>
                                    <a href="/admin" class="btn btn-primary">进入管理后台</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // 自动填充应用URL
            $("#app-url").val(window.location.origin);
            
            // 步骤导航
            $(".step").click(function() {
                const step = $(this).data("step");
                if (!$(this).hasClass("disabled")) {
                    goToStep(step);
                }
            });
            
            // 步骤1：环境检查
            checkRequirements();
            
            $("#step1-next").click(function() {
                goToStep(2);
            });
            
            // 步骤2：数据库配置
            $("input[name=db_type]").change(function() {
                const type = $(this).val();
                if (type === "mysql") {
                    $("#mysql-config").show();
                    $("#sqlite-config").hide();
                } else {
                    $("#mysql-config").hide();
                    $("#sqlite-config").show();
                }
            });
            
            $("#test-connection").click(function() {
                testDatabaseConnection();
            });
            
            $("#step2-prev").click(function() {
                goToStep(1);
            });
            
            $("#step2-next").click(function() {
                goToStep(3);
            });
            
            // 步骤3：系统设置
            $("#step3-prev").click(function() {
                goToStep(2);
            });
            
            $("#step3-next").click(function() {
                if (validateSystemForm()) {
                    goToStep(4);
                    startInstallation();
                }
            });
            
            // 步骤4：安装
            $("#step4-prev").click(function() {
                goToStep(3);
            });
            
            $("#step4-next").click(function() {
                goToStep(5);
                $("#admin-email-display").text($("#admin-email").val());
            });
            
            // 步骤5：完成
            $("#remove-install-dir").click(function() {
                removeInstallDirectory();
            });
        });
        
        // 切换到指定步骤
        function goToStep(step) {
            $(".step").removeClass("active");
            $(".step[data-step=" + step + "]").addClass("active");
            
            $(".step-pane").removeClass("active");
            $("#step" + step).addClass("active");
        }
        
        // 检查系统要求
        function checkRequirements() {
            $.ajax({
                url: "/install/check-requirements.php",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    $("#requirements-checking").addClass("d-none");
                    
                    let allPassed = true;
                    
                    // 更新PHP版本
                    updateRequirement("php-version", response.php_version.status, response.php_version.message);
                    if (!response.php_version.status) allPassed = false;
                    
                    // 更新PDO扩展
                    updateRequirement("php-pdo", response.pdo.status, response.pdo.message);
                    if (!response.pdo.status) allPassed = false;
                    
                    // 更新Mbstring扩展
                    updateRequirement("php-mbstring", response.mbstring.status, response.mbstring.message);
                    if (!response.mbstring.status) allPassed = false;
                    
                    // 更新JSON扩展
                    updateRequirement("php-json", response.json.status, response.json.message);
                    if (!response.json.status) allPassed = false;
                    
                    // 更新OpenSSL扩展
                    updateRequirement("php-openssl", response.openssl.status, response.openssl.message);
                    if (!response.openssl.status) allPassed = false;
                    
                    // 更新存储目录可写
                    updateRequirement("storage-writable", response.storage_writable.status, response.storage_writable.message);
                    if (!response.storage_writable.status) allPassed = false;
                    
                    // 更新配置目录可写
                    updateRequirement("config-writable", response.config_writable.status, response.config_writable.message);
                    if (!response.config_writable.status) allPassed = false;
                    
                    // 显示结果
                    if (allPassed) {
                        $("#requirements-success").removeClass("d-none");
                        $("#step1-next").prop("disabled", false);
                    } else {
                        $("#requirements-error").removeClass("d-none");
                    }
                },
                error: function() {
                    $("#requirements-checking").addClass("d-none");
                    $("#requirements-error").removeClass("d-none").text("检查系统要求时发生错误，请刷新页面重试。");
                }
            });
        }
        
        // 更新要求状态
        function updateRequirement(id, status, message) {
            const element = $("#" + id);
            const statusElement = element.find(".requirement-status");
            
            if (status) {
                statusElement.html("<span class=\"text-success\"> 通过</span>");
                element.addClass("requirement-passed");
            } else {
                statusElement.html("<span class=\"text-danger\"> 失败</span>");
                element.addClass("requirement-failed");
                
                if (message) {
                    element.append("<div class=\"requirement-message text-danger\">" + message + "</div>");
                }
            }
        }
        
        // 测试数据库连接
        function testDatabaseConnection() {
            const formData = $("#database-form").serialize();
            
            $.ajax({
                url: "/install/test-connection.php",
                type: "POST",
                data: formData,
                dataType: "json",
                beforeSend: function() {
                    $("#test-connection").prop("disabled", true).text("测试中...");
                    $("#connection-success, #connection-error").addClass("d-none");
                },
                success: function(response) {
                    $("#test-connection").prop("disabled", false).text("测试连接");
                    
                    if (response.success) {
                        $("#connection-success").removeClass("d-none");
                        $("#connection-error").addClass("d-none");
                        $("#step2-next").prop("disabled", false);
                    } else {
                        $("#connection-success").addClass("d-none");
                        $("#connection-error").removeClass("d-none").text("数据库连接失败：" + response.message);
                    }
                },
                error: function() {
                    $("#test-connection").prop("disabled", false).text("测试连接");
                    $("#connection-success").addClass("d-none");
                    $("#connection-error").removeClass("d-none").text("测试连接时发生错误，请重试。");
                }
            });
        }
        
        // 验证系统设置表单
        function validateSystemForm() {
            const appName = $("#app-name").val();
            const appUrl = $("#app-url").val();
            const adminEmail = $("#admin-email").val();
            const adminPassword = $("#admin-password").val();
            const adminPasswordConfirm = $("#admin-password-confirm").val();
            
            if (!appName) {
                alert("请输入应用名称");
                return false;
            }
            
            if (!appUrl) {
                alert("请输入应用URL");
                return false;
            }
            
            if (!adminEmail) {
                alert("请输入管理员邮箱");
                return false;
            }
            
            if (!adminPassword) {
                alert("请输入管理员密码");
                return false;
            }
            
            if (adminPassword !== adminPasswordConfirm) {
                alert("两次输入的密码不一致");
                return false;
            }
            
            return true;
        }
        
        // 开始安装
        function startInstallation() {
            const dbFormData = $("#database-form").serialize();
            const systemFormData = $("#system-form").serialize();
            const formData = dbFormData + "&" + systemFormData;
            
            // 清空安装日志
            $("#install-log").html("<div class=\"log-entry\">开始安装...</div>");
            
            // 重置进度条
            $("#install-progress").css("width", "0%");
            
            // 隐藏结果提示
            $("#install-success, #install-error").addClass("d-none");
            
            // 禁用上一步按钮
            $("#step4-prev").prop("disabled", true);
            
            // 开始安装
            $.ajax({
                url: "/install/install.php",
                type: "POST",
                data: formData,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // 更新进度条
                        $("#install-progress").css("width", "100%");
                        
                        // 添加日志
                        addInstallLog("安装成功！");
                        
                        // 显示成功提示
                        $("#install-success").removeClass("d-none");
                        
                        // 启用下一步按钮
                        $("#step4-next").prop("disabled", false);
                    } else {
                        // 更新进度条
                        $("#install-progress").css("width", "100%").removeClass("bg-primary").addClass("bg-danger");
                        
                        // 添加日志
                        addInstallLog("安装失败：" + response.message);
                        
                        // 显示错误提示
                        $("#install-error").removeClass("d-none").text("安装失败：" + response.message);
                        
                        // 启用上一步按钮
                        $("#step4-prev").prop("disabled", false);
                    }
                },
                error: function() {
                    // 更新进度条
                    $("#install-progress").css("width", "100%").removeClass("bg-primary").addClass("bg-danger");
                    
                    // 添加日志
                    addInstallLog("安装过程中发生错误，请查看服务器日志了解详情。");
                    
                    // 显示错误提示
                    $("#install-error").removeClass("d-none").text("安装过程中发生错误，请查看服务器日志了解详情。");
                    
                    // 启用上一步按钮
                    $("#step4-prev").prop("disabled", false);
                }
            });
            
            // 模拟安装进度
            simulateInstallProgress();
        }
        
        // 模拟安装进度
        function simulateInstallProgress() {
            let progress = 0;
            const steps = [
                { progress: 10, message: "正在创建数据库..." },
                { progress: 20, message: "正在创建数据表..." },
                { progress: 30, message: "正在设置安全系统..." },
                { progress: 40, message: "正在设置API系统..." },
                { progress: 50, message: "正在设置MCP管理控制平台..." },
                { progress: 60, message: "正在设置管理系统..." },
                { progress: 70, message: "正在配置系统设置..." },
                { progress: 80, message: "正在创建管理员账户..." },
                { progress: 90, message: "正在完成安装..." }
            ];
            
            let currentStep = 0;
            
            const interval = setInterval(function() {
                if (currentStep < steps.length) {
                    progress = steps[currentStep].progress;
                    addInstallLog(steps[currentStep].message);
                    $("#install-progress").css("width", progress + "%");
                    currentStep++;
                } else {
                    clearInterval(interval);
                }
            }, 1500);
        }
        
        // 添加安装日志
        function addInstallLog(message) {
            const time = new Date().toLocaleTimeString();
            $("#install-log").append("<div class=\"log-entry\">[" + time + "] " + message + "</div>");
            
            // 滚动到底部
            const logContainer = document.getElementById("install-log");
            logContainer.scrollTop = logContainer.scrollHeight;
        }
        
        // 删除安装目录
        function removeInstallDirectory() {
            $.ajax({
                url: "/install/remove-install-dir.php",
                type: "POST",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        alert("安装目录已成功删除！");
                        $("#remove-install-dir").prop("disabled", true).text("已删除");
                    } else {
                        alert("删除安装目录失败：" + response.message);
                    }
                },
                error: function() {
                    alert("删除安装目录时发生错误，请手动删除。");
                }
            });
        }
    </script>
</body>
</html>
