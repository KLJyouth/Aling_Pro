/**
 * AlingAi Pro 安装向导
 * JavaScript 控制文件
 */

document.addEventListener('DOMContentLoaded', function() {
    // 步骤控制
    const steps = document.querySelectorAll('.step');
    const stepContents = document.querySelectorAll('.step-content');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    let currentStep = 1;
    
    // 数据库类型切换
    const dbType = document.getElementById('dbType');
    const mysqlFields = document.getElementById('mysqlFields');
    const sqliteFields = document.getElementById('sqliteFields');
    
    if (dbType) {
        dbType.addEventListener('change', function() {
            if (this.value === 'sqlite') {
                mysqlFields.style.display = 'none';
                sqliteFields.style.display = 'block';
            } else {
                mysqlFields.style.display = 'block';
                sqliteFields.style.display = 'none';
            }
        });
    }
    
    // 重试安装按钮
    const retryInstall = document.getElementById('retryInstall');
    if (retryInstall) {
        retryInstall.addEventListener('click', function() {
            goToStep(1);
            document.querySelector('.install-error').classList.add('d-none');
            document.querySelector('.install-success').classList.remove('d-none');
        });
    }
    
    // 步骤导航
    prevBtn.addEventListener('click', function() {
        if (currentStep > 1) {
            goToStep(currentStep - 1);
        }
    });
    
    nextBtn.addEventListener('click', function() {
        if (currentStep < steps.length) {
            // 验证当前步骤
            if (validateStep(currentStep)) {
                if (currentStep === 4) {
                    // 开始安装
                    startInstallation();
                } else {
                    goToStep(currentStep + 1);
                }
            }
        }
    });
    
    // 初始化系统检查
    if (currentStep === 1) {
        runSystemChecks();
    }
    
    /**
     * 导航到指定步骤
     */
    function goToStep(step) {
        // 更新当前步骤
        currentStep = step;
        
        // 更新步骤指示器
        steps.forEach(function(stepEl) {
            const stepNum = parseInt(stepEl.dataset.step);
            stepEl.classList.remove('active', 'completed');
            
            if (stepNum === currentStep) {
                stepEl.classList.add('active');
            } else if (stepNum < currentStep) {
                stepEl.classList.add('completed');
            }
        });
        
        // 更新步骤内容
        stepContents.forEach(function(content, index) {
        content.classList.remove('active');
            if (index === currentStep - 1) {
            content.classList.add('active');
        }
    });
    
        // 更新按钮状态
        prevBtn.disabled = currentStep === 1;
        
        if (currentStep === steps.length) {
        nextBtn.style.display = 'none';
        } else if (currentStep === 4) {
            nextBtn.textContent = '开始安装';
    } else {
        nextBtn.style.display = 'block';
            nextBtn.textContent = '下一步';
    }
}

/**
 * 验证当前步骤
 */
    function validateStep(step) {
        switch (step) {
        case 1:
                // 系统检查验证
                return !document.getElementById('checkError').classList.contains('d-block');
            
        case 2:
                // 数据库配置验证
                const dbForm = document.getElementById('databaseForm');
                
                if (dbType.value === 'mysql') {
                    if (!document.getElementById('dbHost').value ||
                        !document.getElementById('dbPort').value ||
                        !document.getElementById('dbName').value ||
                        !document.getElementById('dbUser').value) {
                        showError('dbError', '请填写所有必填字段');
                        return false;
                    }
                }
                
                // 测试数据库连接
                if (document.getElementById('testConnection').checked) {
                    testDatabaseConnection();
                    return false; // 阻止进入下一步，直到连接测试完成
                }
                
                return true;
            
        case 3:
                // 管理员设置验证
                const adminUsername = document.getElementById('adminUsername').value;
                const adminEmail = document.getElementById('adminEmail').value;
                const adminPassword = document.getElementById('adminPassword').value;
                const adminConfirmPassword = document.getElementById('adminConfirmPassword').value;
                
                if (!adminUsername || !adminEmail || !adminPassword || !adminConfirmPassword) {
                    showError('passwordError', '请填写所有必填字段');
                    return false;
                }
                
                if (adminPassword !== adminConfirmPassword) {
                    showError('passwordError', '两次输入的密码不一致');
                    return false;
                }
                
                if (adminPassword.length < 8) {
                    showError('passwordError', '密码长度至少为8位');
                    return false;
                }
                
                // 验证邮箱格式
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(adminEmail)) {
                    showError('passwordError', '请输入有效的电子邮箱地址');
                    return false;
                }
                
                hideError('passwordError');
                return true;
            
        case 4:
                // 安装步骤不需要验证
                return true;
            
        default:
            return true;
    }
}

/**
     * 运行系统检查
     */
    function runSystemChecks() {
        // 发送AJAX请求检查系统要求
        fetch('check.php')
            .then(response => response.json())
            .then(data => {
                updateSystemChecks(data);
            })
            .catch(error => {
                console.error('系统检查失败:', error);
                document.getElementById('requiredChecks').innerHTML = `
                    <li class="p-3">
                        <i class="bi bi-x-circle-fill requirement-fail"></i>
                        无法连接到检查脚本，请确保服务器正常运行
                    </li>
                `;
                showError('checkError', '系统检查失败，请确保服务器正常运行');
            });
    }
    
    /**
     * 更新系统检查结果
     */
    function updateSystemChecks(data) {
        const requiredChecks = document.getElementById('requiredChecks');
        const recommendedChecks = document.getElementById('recommendedChecks');
        
        // 清空检查列表
        requiredChecks.innerHTML = '';
        recommendedChecks.innerHTML = '';
        
        // 更新必要条件
        let allRequiredPassed = true;
        
        data.required.forEach(check => {
            const icon = check.passed 
                ? '<i class="bi bi-check-circle-fill requirement-pass"></i>' 
                : '<i class="bi bi-x-circle-fill requirement-fail"></i>';
            
            requiredChecks.innerHTML += `
                <li class="p-3">
                    ${icon}
                    ${check.name}: ${check.message}
                </li>
            `;
            
            if (!check.passed) {
                allRequiredPassed = false;
            }
        });
        
        // 更新推荐条件
        data.recommended.forEach(check => {
            const icon = check.passed 
                ? '<i class="bi bi-check-circle-fill requirement-pass"></i>' 
                : '<i class="bi bi-exclamation-triangle-fill requirement-warning"></i>';
            
            recommendedChecks.innerHTML += `
                <li class="p-3">
                    ${icon}
                    ${check.name}: ${check.message}
                </li>
            `;
        });
        
        // 显示或隐藏错误消息
        if (!allRequiredPassed) {
            showError('checkError', '系统不满足最低安装要求，请解决上述问题后重试');
        } else {
            hideError('checkError');
    }
}

/**
 * 测试数据库连接
 */
    function testDatabaseConnection() {
        const dbConfig = getDatabaseConfig();
        
        // 显示加载状态
        nextBtn.disabled = true;
        nextBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>测试连接中...';
        
        // 发送AJAX请求测试数据库连接
        fetch('test-db.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dbConfig)
        })
        .then(response => response.json())
        .then(data => {
            nextBtn.disabled = false;
            nextBtn.innerHTML = '下一步';
            
            if (data.success) {
                hideError('dbError');
                document.getElementById('dbSuccess').classList.remove('d-none');
                
                // 延迟1秒后自动进入下一步
                setTimeout(() => {
                    goToStep(currentStep + 1);
                }, 1000);
        } else {
                showError('dbError', data.message || '数据库连接失败');
                document.getElementById('dbSuccess').classList.add('d-none');
            }
        })
        .catch(error => {
            console.error('数据库连接测试失败:', error);
            nextBtn.disabled = false;
            nextBtn.innerHTML = '下一步';
            showError('dbError', '无法连接到测试脚本，请检查服务器配置');
            document.getElementById('dbSuccess').classList.add('d-none');
        });
    }
    
    /**
     * 获取数据库配置
     */
    function getDatabaseConfig() {
        const config = {
            type: dbType.value
        };
        
        if (config.type === 'mysql') {
            config.host = document.getElementById('dbHost').value;
            config.port = document.getElementById('dbPort').value;
            config.database = document.getElementById('dbName').value;
            config.username = document.getElementById('dbUser').value;
            config.password = document.getElementById('dbPassword').value;
        }
        
        return config;
    }
    
    /**
     * 获取管理员配置
     */
    function getAdminConfig() {
        return {
            username: document.getElementById('adminUsername').value,
            email: document.getElementById('adminEmail').value,
            password: document.getElementById('adminPassword').value,
            site_name: document.getElementById('siteName').value,
            site_url: document.getElementById('siteUrl').value || window.location.origin
        };
}

/**
 * 开始安装
 */
    function startInstallation() {
        // 进入安装步骤
        goToStep(4);
        
        // 禁用导航按钮
        prevBtn.disabled = true;
        nextBtn.disabled = true;
        
        // 准备安装数据
        const installData = {
            database: getDatabaseConfig(),
            admin: getAdminConfig()
        };
        
        // 初始化安装日志
        const installLog = document.getElementById('installLog');
        installLog.innerHTML = '<div class="text-muted">准备安装...</div>';
        
        // 更新进度条
        updateProgress(0, '准备安装...');
        
        // 添加安装日志
        addInstallLog('开始安装 AlingAi Pro 5.1');
        addInstallLog('正在验证安装数据...');
        
        // 延迟以显示初始日志
        setTimeout(() => {
        // 发送安装请求
            fetch('install.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
                body: JSON.stringify(installData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 安装成功
                    handleInstallationSuccess(data);
                } else {
                    // 安装失败
                    handleInstallationError(data);
                }
            })
            .catch(error => {
                console.error('安装失败:', error);
                handleInstallationError({
                    message: '安装过程中发生错误，请检查服务器日志',
                    error: error.toString()
                });
            });
        }, 500);
    }
    
    /**
     * 处理安装成功
     */
    function handleInstallationSuccess(data) {
        // 更新进度
        updateProgress(100, '安装完成');
        
        // 添加成功日志
        addInstallLog('✅ 安装成功完成!', 'text-success fw-bold');
        
        // 显示各步骤结果
        if (data.progress) {
            Object.entries(data.progress).forEach(([step, result]) => {
                addInstallLog(`${result.message}${result.details ? ': ' + result.details : ''}`, 
                    result.success ? 'text-success' : 'text-danger');
            });
        }
        
        // 延迟后进入完成步骤
        setTimeout(() => {
            goToStep(5);
        }, 1000);
    }
    
    /**
     * 处理安装错误
     */
    function handleInstallationError(data) {
        // 更新进度
        updateProgress(100, '安装失败');
        
        // 添加错误日志
        addInstallLog('❌ 安装失败: ' + data.message, 'text-danger fw-bold');
        
        if (data.error) {
            addInstallLog('错误详情: ' + data.error, 'text-danger');
        }
        
        // 显示各步骤结果
        if (data.progress) {
            Object.entries(data.progress).forEach(([step, result]) => {
                addInstallLog(`${step}: ${result.message}`, 
                    result.success ? 'text-success' : 'text-danger');
            });
        }
        
        // 显示错误信息
        document.getElementById('finalErrorMessage').textContent = data.message;
        
        // 延迟后进入完成步骤，但显示错误
        setTimeout(() => {
            goToStep(5);
            document.querySelector('.install-success').classList.add('d-none');
            document.querySelector('.install-error').classList.remove('d-none');
        }, 1000);
}

/**
 * 更新安装进度
 */
    function updateProgress(percentage, message) {
        const progressBar = document.querySelector('.progress-bar');
        const progressPercentage = document.getElementById('progressPercentage');
        const currentStepEl = document.getElementById('currentStep');
        
        progressBar.style.width = percentage + '%';
        progressBar.setAttribute('aria-valuenow', percentage);
        progressPercentage.textContent = percentage + '%';
        
        if (message) {
            currentStepEl.textContent = message;
        }
    }
    
    /**
     * 添加安装日志
     */
    function addInstallLog(message, className = '') {
        const installLog = document.getElementById('installLog');
        const logEntry = document.createElement('div');
        logEntry.className = className;
        logEntry.textContent = message;
        installLog.appendChild(logEntry);
        installLog.scrollTop = installLog.scrollHeight;
        
        // 根据日志内容更新进度
        updateProgressFromLog(message);
    }
    
    /**
     * 根据日志内容更新进度
     */
    function updateProgressFromLog(message) {
        if (message.includes('验证安装数据')) {
            updateProgress(10, '验证安装数据...');
        } else if (message.includes('创建配置文件')) {
            updateProgress(25, '创建配置文件...');
        } else if (message.includes('设置数据库')) {
            updateProgress(40, '设置数据库...');
        } else if (message.includes('创建数据表')) {
            updateProgress(60, '创建数据表...');
        } else if (message.includes('创建管理员')) {
            updateProgress(80, '创建管理员账户...');
        } else if (message.includes('完成安装')) {
            updateProgress(95, '完成安装...');
        }
    }
    
    /**
     * 显示错误消息
     */
    function showError(elementId, message) {
        const errorElement = document.getElementById(elementId);
        if (errorElement) {
            errorElement.querySelector('span').textContent = message;
            errorElement.classList.remove('d-none');
            errorElement.classList.add('d-block');
        }
    }
    
    /**
     * 隐藏错误消息
     */
    function hideError(elementId) {
        const errorElement = document.getElementById(elementId);
        if (errorElement) {
            errorElement.classList.add('d-none');
            errorElement.classList.remove('d-block');
        }
    }
});
