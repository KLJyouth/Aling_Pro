/**
 * AlingAi Pro 5.0 安装向导 JavaScript
 * 处理安装流程的交互逻辑
 */

let currentStep = 1;
let totalSteps = 5;
let systemChecks = {};
let installConfig = {};

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    updateStepUI();
    
    // 绑定数据库类型变化事件
    document.querySelector('select[name="db_type"]').addEventListener('change', function() {
        toggleDatabaseFields(this.value);
    });
    
    // 初始化数据库字段显示
    toggleDatabaseFields('sqlite');
});

/**
 * 下一步
 */
function nextStep() {
    if (validateCurrentStep()) {
        if (currentStep < totalSteps) {
            currentStep++;
            updateStepUI();
            
            // 特殊处理步骤2的系统检查
            if (currentStep === 2) {
                startSystemCheck();
            }
        }
    }
}

/**
 * 上一步
 */
function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        updateStepUI();
    }
}

/**
 * 更新步骤UI
 */
function updateStepUI() {
    // 更新步骤指示器
    document.querySelectorAll('.step').forEach((step, index) => {
        const stepNum = index + 1;
        step.className = 'step';
        
        if (stepNum < currentStep) {
            step.classList.add('completed');
        } else if (stepNum === currentStep) {
            step.classList.add('active');
        } else {
            step.classList.add('pending');
        }
    });
    
    // 更新进度条
    const progress = (currentStep / totalSteps) * 100;
    document.querySelector('.progress-fill').style.width = progress + '%';
    
    // 显示/隐藏步骤内容
    document.querySelectorAll('.step-content').forEach((content, index) => {
        content.classList.remove('active');
        if (index + 1 === currentStep) {
            content.classList.add('active');
        }
    });
    
    // 更新按钮
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const installBtn = document.getElementById('install-btn');
    
    prevBtn.style.display = currentStep > 1 ? 'block' : 'none';
    
    if (currentStep === totalSteps) {
        nextBtn.style.display = 'none';
        installBtn.style.display = 'block';
        generateInstallSummary();
    } else {
        nextBtn.style.display = 'block';
        installBtn.style.display = 'none';
    }
    
    // 特殊处理某些步骤的按钮状态
    if (currentStep === 2) {
        nextBtn.disabled = true;
        nextBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> 检查中...';
    } else {
        nextBtn.disabled = false;
        nextBtn.innerHTML = '下一步 <i class="bi bi-arrow-right"></i>';
    }
}

/**
 * 验证当前步骤
 */
function validateCurrentStep() {
    switch (currentStep) {
        case 1:
            return true; // 欢迎页面无需验证
            
        case 2:
            // 检查系统检查是否完成
            return Object.keys(systemChecks).length > 0 && 
                   Object.values(systemChecks).every(check => check.passed);
            
        case 3:
            return validateDatabaseForm();
            
        case 4:
            return validateAdminForm();
            
        case 5:
            return true; // 确认页面无需验证
            
        default:
            return true;
    }
}

/**
 * 开始系统检查
 */
async function startSystemCheck() {
    const checks = [
        { key: 'php', name: 'PHP版本检查', url: 'check.php?type=php' },
        { key: 'extensions', name: 'PHP扩展检查', url: 'check.php?type=extensions' },
        { key: 'permissions', name: '文件权限检查', url: 'check.php?type=permissions' },
        { key: 'memory', name: '内存限制检查', url: 'check.php?type=memory' },
        { key: 'database', name: '数据库支持检查', url: 'check.php?type=database' }
    ];
    
    for (const check of checks) {
        await performSystemCheck(check);
        await sleep(500); // 添加延迟效果
    }
    
    // 检查完成后更新UI
    setTimeout(() => {
        const allPassed = Object.values(systemChecks).every(check => check.passed);
        if (allPassed) {
            document.getElementById('check-complete').style.display = 'block';
            document.getElementById('next-btn').disabled = false;
            document.getElementById('next-btn').innerHTML = '下一步 <i class="bi bi-arrow-right"></i>';
        }
    }, 1000);
}

/**
 * 执行单个系统检查
 */
async function performSystemCheck(check) {
    const checkElement = document.querySelector(`[data-check="${check.key}"]`);
    
    try {
        const response = await fetch(check.url);
        const result = await response.json();
        
        systemChecks[check.key] = result;
        
        // 更新UI
        checkElement.className = 'requirement-check ' + (result.passed ? 'success' : 'error');
        checkElement.innerHTML = `
            <i class="bi bi-${result.passed ? 'check-circle' : 'x-circle'} me-3"></i>
            <span>${result.message}</span>
        `;
        
    } catch (error) {
        systemChecks[check.key] = { passed: false, message: check.name + ' - 检查失败' };
        checkElement.className = 'requirement-check error';
        checkElement.innerHTML = `
            <i class="bi bi-x-circle me-3"></i>
            <span>${check.name} - 检查失败</span>
        `;
    }
}

/**
 * 切换数据库字段显示
 */
function toggleDatabaseFields(dbType) {
    const hostField = document.querySelector('input[name="db_host"]').closest('.form-group');
    const portField = document.querySelector('input[name="db_port"]').closest('.form-group');
    const usernameField = document.querySelector('input[name="db_username"]').closest('.form-group');
    const passwordField = document.querySelector('input[name="db_password"]').closest('.form-group');
    
    if (dbType === 'sqlite') {
        hostField.style.display = 'none';
        portField.style.display = 'none';
        usernameField.style.display = 'none';
        passwordField.style.display = 'none';
        
        // 设置SQLite的默认值
        document.querySelector('input[name="db_name"]').value = 'storage/database.sqlite';
    } else {
        hostField.style.display = 'block';
        portField.style.display = 'block';
        usernameField.style.display = 'block';
        passwordField.style.display = 'block';
        
        // 设置其他数据库的默认值
        document.querySelector('input[name="db_name"]').value = 'alingai_pro';
        if (dbType === 'mysql') {
            document.querySelector('input[name="db_port"]').value = '3306';
        } else if (dbType === 'pgsql') {
            document.querySelector('input[name="db_port"]').value = '5432';
        }
    }
}

/**
 * 测试数据库连接
 */
async function testDatabase() {
    const form = document.getElementById('database-form');
    const formData = new FormData(form);
    const resultElement = document.getElementById('db-test-result');
    
    resultElement.innerHTML = '<i class="bi bi-hourglass-split"></i> 测试中...';
    
    try {
        const response = await fetch('test-db.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            resultElement.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> 连接成功</span>';
        } else {
            resultElement.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> ' + result.message + '</span>';
        }
    } catch (error) {
        resultElement.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> 连接测试失败</span>';
    }
}

/**
 * 验证数据库表单
 */
function validateDatabaseForm() {
    const form = document.getElementById('database-form');
    const dbType = form.querySelector('select[name="db_type"]').value;
    const dbName = form.querySelector('input[name="db_name"]').value;
    
    if (!dbName) {
        alert('请输入数据库名称');
        return false;
    }
    
    if (dbType !== 'sqlite') {
        const dbHost = form.querySelector('input[name="db_host"]').value;
        const dbUsername = form.querySelector('input[name="db_username"]').value;
        
        if (!dbHost) {
            alert('请输入数据库主机');
            return false;
        }
        
        if (!dbUsername) {
            alert('请输入数据库用户名');
            return false;
        }
    }
    
    // 保存数据库配置
    installConfig.database = {
        type: dbType,
        host: form.querySelector('input[name="db_host"]').value,
        port: form.querySelector('input[name="db_port"]').value,
        database: dbName, // 使用正确的键名
        username: form.querySelector('input[name="db_username"]').value,
        password: form.querySelector('input[name="db_password"]').value
    };
    
    return true;
}

/**
 * 验证管理员表单
 */
function validateAdminForm() {
    const form = document.getElementById('admin-form');
    const username = form.querySelector('input[name="admin_username"]').value;
    const email = form.querySelector('input[name="admin_email"]').value;
    const password = form.querySelector('input[name="admin_password"]').value;
    const passwordConfirm = form.querySelector('input[name="admin_password_confirm"]').value;
    
    if (!username || !email || !password || !passwordConfirm) {
        alert('请填写所有必需字段');
        return false;
    }
    
    if (password.length < 8) {
        alert('密码长度至少8位');
        return false;
    }
    
    if (password !== passwordConfirm) {
        alert('两次输入的密码不一致');
        return false;
    }
    
    // 保存管理员配置
    installConfig.admin = {
        username: username,
        email: email,
        password: password,
        site_name: form.querySelector('input[name="site_name"]').value,
        site_url: form.querySelector('input[name="site_url"]').value
    };
    
    return true;
}

/**
 * 生成安装摘要
 */
function generateInstallSummary() {
    const summaryElement = document.getElementById('install-summary');
    
    const dbConfig = installConfig.database;
    const adminConfig = installConfig.admin;
    
    summaryElement.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="bi bi-database"></i> 数据库配置</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>类型:</strong> ${dbConfig.type.toUpperCase()}</p>
                        ${dbConfig.type !== 'sqlite' ? `
                            <p><strong>主机:</strong> ${dbConfig.host}</p>
                            <p><strong>端口:</strong> ${dbConfig.port}</p>
                            <p><strong>用户名:</strong> ${dbConfig.username}</p>
                        ` : ''}
                        <p><strong>数据库:</strong> ${dbConfig.name}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="bi bi-person-gear"></i> 管理员配置</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>用户名:</strong> ${adminConfig.username}</p>
                        <p><strong>邮箱:</strong> ${adminConfig.email}</p>
                        <p><strong>站点名称:</strong> ${adminConfig.site_name}</p>
                        <p><strong>站点URL:</strong> ${adminConfig.site_url}</p>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * 开始安装
 */
async function startInstall() {
    const progressElement = document.getElementById('install-progress');
    const completeElement = document.getElementById('install-complete');
    const installBtn = document.getElementById('install-btn');
    
    // 显示进度
    progressElement.style.display = 'block';
    installBtn.disabled = true;
    
    try {
        // 收集配置数据
        const configData = {
            database: installConfig.database,
            admin: installConfig.admin
        };
        
        // 发送安装请求
        const response = await fetch('install.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(configData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // 显示安装进度
            if (result.progress) {
                let stepCount = 0;
                const totalSteps = Object.keys(result.progress).length;
                
                for (const [step, details] of Object.entries(result.progress)) {
                    stepCount++;
                    const progressPercent = (stepCount / totalSteps) * 100;
                    updateInstallProgress(progressPercent, details.message);
                    await sleep(800);
                }
            }
            
            // 安装完成
            progressElement.style.display = 'none';
            completeElement.style.display = 'block';
            
            // 如果有重定向URL，显示访问链接
            if (result.redirect) {
                const completeContent = completeElement.querySelector('.text-center');
                completeContent.innerHTML += `
                    <div class="mt-4">
                        <a href="${result.redirect}" class="btn btn-primary btn-lg">
                            <i class="bi bi-house-door"></i> 访问管理后台
                        </a>
                    </div>
                `;
            }
            
        } else {
            throw new Error(result.message || '安装失败');
        }
        
    } catch (error) {
        console.error('安装错误:', error);
        alert('安装过程中出现错误: ' + error.message);
        progressElement.style.display = 'none';
        installBtn.disabled = false;
    }
}

/**
 * 更新安装进度
 */
function updateInstallProgress(progress, message) {
    const progressBar = document.getElementById('install-progress-bar');
    const statusElement = document.getElementById('install-status');
    
    statusElement.textContent = message || '安装中...';
    progressBar.style.width = progress + '%';
    progressBar.textContent = Math.round(progress) + '%';
}

/**
 * 工具函数：延迟
 */
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}
