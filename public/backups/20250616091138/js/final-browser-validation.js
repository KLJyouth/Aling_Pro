// 🔬 AlingAi量子动画系统最终浏览器验证脚本
// 创建时间: 2025-05-31
// 目的: 在实际浏览器环境中验证量子动画系统是否正常工作

console.log('🚀 开始AlingAi量子动画系统最终浏览器验证...');

class FinalBrowserValidation {
    constructor() {
        this.results = [];
        this.startTime = Date.now();
    }

    log(message, type = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        const logMessage = `[${timestamp}] ${message}`;
        
        switch(type) {
            case 'success':
                console.log('✅', logMessage);
                break;
            case 'error':
                console.error('❌', logMessage);
                break;
            case 'warning':
                console.warn('⚠️', logMessage);
                break;
            default:
                console.log('ℹ️', logMessage);
        }
    }

    addResult(testName, passed, details) {
        this.results.push({
            test: testName,
            passed: passed,
            details: details,
            timestamp: new Date().toISOString()
        });
        
        this.log(`${testName}: ${passed ? '通过' : '失败'} - ${details}`, 
                 passed ? 'success' : 'error');
    }

    // 验证DOM环境
    validateDOMEnvironment() {
        this.log('🏗️ 验证DOM环境...');
        
        // 检查必需的DOM元素
        const requiredElements = [
            'quantumLoader',
            'quantumBalls', 
            'validationText'
        ];
        
        let allElementsExist = true;
        requiredElements.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                this.addResult(`DOM元素-${id}`, true, '元素存在');
            } else {
                this.addResult(`DOM元素-${id}`, false, '元素不存在');
                allElementsExist = false;
            }
        });

        // 检查量子球容器
        const quantumBalls = document.querySelectorAll('.quantum-ball');
        this.addResult('量子球元素', quantumBalls.length > 0, 
                      `找到 ${quantumBalls.length} 个量子球元素`);

        return allElementsExist && quantumBalls.length > 0;
    }

    // 验证JavaScript环境
    validateJSEnvironment() {
        this.log('📜 验证JavaScript环境...');
        
        // 检查全局类
        const requiredClasses = [
            'QuantumAnimationSystem',
            'QuantumChatIntegrator',
            'QuantumParticleSystem'
        ];
        
        let allClassesExist = true;
        requiredClasses.forEach(className => {
            const classExists = typeof window[className] !== 'undefined';
            this.addResult(`类定义-${className}`, classExists, 
                          classExists ? '类已定义' : '类未定义');
            if (!classExists) allClassesExist = false;
        });

        // 检查全局变量
        const quantumAnimationExists = typeof window.quantumAnimation !== 'undefined';
        this.addResult('全局变量-quantumAnimation', quantumAnimationExists, 
                      quantumAnimationExists ? '已定义' : '未定义');

        const quantumChatIntegratorExists = typeof window.quantumChatIntegrator !== 'undefined';
        this.addResult('全局变量-quantumChatIntegrator', quantumChatIntegratorExists, 
                      quantumChatIntegratorExists ? '已定义' : '未定义');

        return allClassesExist;
    }

    // 验证量子动画系统初始化
    validateQuantumAnimationInitialization() {
        this.log('⚛️ 验证量子动画系统初始化...');
        
        try {
            // 检查是否已经初始化
            if (window.quantumAnimation) {
                this.addResult('量子动画系统', true, '已经初始化');
                return true;
            }
            
            // 尝试初始化
            if (typeof QuantumAnimationSystem !== 'undefined') {
                window.quantumAnimation = new QuantumAnimationSystem();
                
                if (window.quantumAnimation) {
                    this.addResult('量子动画实例创建', true, '实例创建成功');
                    
                    // 检查初始化方法
                    if (typeof window.quantumAnimation.initialize === 'function') {
                        window.quantumAnimation.initialize();
                        this.addResult('量子动画初始化调用', true, 'initialize方法调用成功');
                        return true;
                    } else {
                        this.addResult('量子动画初始化调用', false, 'initialize方法不存在');
                        return false;
                    }
                } else {
                    this.addResult('量子动画实例创建', false, '实例创建失败');
                    return false;
                }
            } else {
                this.addResult('量子动画系统', false, 'QuantumAnimationSystem类未定义');
                return false;
            }
        } catch (error) {
            this.addResult('量子动画系统', false, `初始化错误: ${error.message}`);
            return false;
        }
    }

    // 验证聊天集成器
    validateChatIntegrator() {
        this.log('💬 验证聊天集成器...');
        
        try {
            // 检查是否已经初始化
            if (window.quantumChatIntegrator) {
                this.addResult('聊天集成器', true, '已经初始化');
                return true;
            }
            
            // 尝试初始化
            if (typeof QuantumChatIntegrator !== 'undefined') {
                window.quantumChatIntegrator = new QuantumChatIntegrator();
                
                if (window.quantumChatIntegrator) {
                    this.addResult('聊天集成器实例创建', true, '实例创建成功');
                    
                    // 检查initialize方法
                    if (typeof window.quantumChatIntegrator.initialize === 'function') {
                        window.quantumChatIntegrator.initialize();
                        this.addResult('聊天集成器初始化', true, 'initialize方法调用成功');
                        return true;
                    } else {
                        this.addResult('聊天集成器初始化', false, 'initialize方法不存在');
                        return false;
                    }
                } else {
                    this.addResult('聊天集成器实例创建', false, '实例创建失败');
                    return false;
                }
            } else {
                this.addResult('聊天集成器', false, 'QuantumChatIntegrator类未定义');
                return false;
            }
        } catch (error) {
            this.addResult('聊天集成器', false, `初始化错误: ${error.message}`);
            return false;
        }
    }

    // 验证量子动画功能
    validateQuantumAnimationFunctionality() {
        this.log('🎬 验证量子动画功能...');
        
        if (!window.quantumAnimation) {
            this.addResult('量子动画功能测试', false, '量子动画系统未初始化');
            return false;
        }

        try {
            // 测试核心方法是否存在
            const requiredMethods = [
                'initialize',
                'simulateValidation', 
                'createQuantumBalls',
                'animateQuantumBalls'
            ];
            
            let allMethodsExist = true;
            requiredMethods.forEach(method => {
                const methodExists = typeof window.quantumAnimation[method] === 'function';
                this.addResult(`量子动画方法-${method}`, methodExists, 
                              methodExists ? '方法存在' : '方法不存在');
                if (!methodExists) allMethodsExist = false;
            });

            // 测试动画执行
            if (typeof window.quantumAnimation.simulateValidation === 'function') {
                window.quantumAnimation.simulateValidation();
                this.addResult('量子验证动画', true, '验证动画执行成功');
            }

            return allMethodsExist;
        } catch (error) {
            this.addResult('量子动画功能测试', false, `功能测试错误: ${error.message}`);
            return false;
        }
    }

    // 验证WebSocket连接
    async validateWebSocketConnection() {
        this.log('🔌 验证WebSocket连接...');
        
        return new Promise((resolve) => {
            try {
                const wsUrl = `ws://${window.location.host}`;
                const ws = new WebSocket(wsUrl);
                
                const timeout = setTimeout(() => {
                    this.addResult('WebSocket连接', false, '连接超时');
                    ws.close();
                    resolve(false);
                }, 5000);
                
                ws.onopen = () => {
                    this.addResult('WebSocket连接', true, '连接成功');
                    clearTimeout(timeout);
                    ws.close();
                    resolve(true);
                };
                
                ws.onerror = (error) => {
                    this.addResult('WebSocket连接', false, `连接错误: ${error}`);
                    clearTimeout(timeout);
                    resolve(false);
                };
            } catch (error) {
                this.addResult('WebSocket连接', false, `WebSocket错误: ${error.message}`);
                resolve(false);
            }
        });
    }

    // 运行完整验证
    async runCompleteValidation() {
        this.log('🎯 开始完整验证流程...');
        
        // 1. DOM环境验证
        const domValid = this.validateDOMEnvironment();
        
        // 2. JavaScript环境验证  
        const jsValid = this.validateJSEnvironment();
        
        // 3. 量子动画系统初始化验证
        const quantumValid = this.validateQuantumAnimationInitialization();
        
        // 4. 聊天集成器验证
        const chatValid = this.validateChatIntegrator();
        
        // 5. 量子动画功能验证
        const functionalityValid = this.validateQuantumAnimationFunctionality();
        
        // 6. WebSocket连接验证
        const wsValid = await this.validateWebSocketConnection();
        
        // 生成最终报告
        this.generateFinalReport();
        
        return {
            dom: domValid,
            javascript: jsValid,
            quantum: quantumValid,
            chat: chatValid,
            functionality: functionalityValid,
            websocket: wsValid,
            overall: domValid && jsValid && quantumValid && chatValid && functionalityValid
        };
    }

    // 生成最终报告
    generateFinalReport() {
        const endTime = Date.now();
        const duration = endTime - this.startTime;
        
        const totalTests = this.results.length;
        const passedTests = this.results.filter(r => r.passed).length;
        const failedTests = totalTests - passedTests;
        const successRate = ((passedTests / totalTests) * 100).toFixed(1);
        
        this.log('📊 最终验证报告', 'info');
        this.log('=====================================', 'info');
        this.log(`总测试数: ${totalTests}`, 'info');
        this.log(`通过测试: ${passedTests}`, 'success');
        this.log(`失败测试: ${failedTests}`, failedTests > 0 ? 'error' : 'info');
        this.log(`成功率: ${successRate}%`, successRate >= 90 ? 'success' : 'warning');
        this.log(`耗时: ${duration}ms`, 'info');
        
        if (failedTests === 0) {
            this.log('🎉 所有测试通过！量子动画系统运行正常！', 'success');
        } else {
            this.log('⚠️ 存在失败的测试，请检查具体错误', 'warning');
            
            // 显示失败的测试
            const failedResults = this.results.filter(r => !r.passed);
            failedResults.forEach(result => {
                this.log(`❌ ${result.test}: ${result.details}`, 'error');
            });
        }
        
        // 在页面上显示结果（如果有合适的容器）
        this.displayResultsOnPage();
    }

    // 在页面上显示结果
    displayResultsOnPage() {
        const container = document.getElementById('validation-results') || 
                         document.getElementById('testResults') ||
                         document.body;
        
        if (container) {
            const totalTests = this.results.length;
            const passedTests = this.results.filter(r => r.passed).length;
            const successRate = ((passedTests / totalTests) * 100).toFixed(1);
            
            const resultHtml = `
                <div style="background: rgba(0,0,0,0.8); color: white; padding: 20px; margin: 20px 0; border-radius: 10px; font-family: monospace;">
                    <h3>🔬 量子动画系统验证结果</h3>
                    <p><strong>总测试数:</strong> ${totalTests}</p>
                    <p><strong>通过测试:</strong> <span style="color: #4CAF50">${passedTests}</span></p>
                    <p><strong>失败测试:</strong> <span style="color: #f44336">${totalTests - passedTests}</span></p>
                    <p><strong>成功率:</strong> <span style="color: ${successRate >= 90 ? '#4CAF50' : '#ff9800'}">${successRate}%</span></p>
                    <p><strong>状态:</strong> <span style="color: ${passedTests === totalTests ? '#4CAF50' : '#f44336'}">${passedTests === totalTests ? '✅ 系统正常' : '❌ 需要修复'}</span></p>
                </div>
            `;
            
            if (container.id === 'testResults' && container.innerHTML.includes('<h2>测试结果：</h2>')) {
                container.innerHTML += resultHtml;
            } else {
                const resultDiv = document.createElement('div');
                resultDiv.innerHTML = resultHtml;
                container.appendChild(resultDiv);
            }
        }
    }
}

// 创建验证实例并在页面加载完成后自动运行
const finalValidation = new FinalBrowserValidation();

// 如果页面已经加载完成，立即运行验证
if (document.readyState === 'complete') {
    setTimeout(() => finalValidation.runCompleteValidation(), 1000);
} else {
    // 否则等待页面加载完成
    window.addEventListener('load', () => {
        setTimeout(() => finalValidation.runCompleteValidation(), 1000);
    });
}

// 导出到全局作用域，以便手动调用
window.finalValidation = finalValidation;

console.log('✨ 量子动画系统验证脚本已加载，验证将自动开始...');
