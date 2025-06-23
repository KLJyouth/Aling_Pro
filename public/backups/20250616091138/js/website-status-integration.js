// 网站状态监控和集成测试模块
// 从首页移植并整合到综合检测系统
// 创建时间: 2025-05-30

console.log('🌐 加载网站状态监控和集成测试模块...');

// 网站状态监控类
class WebsiteStatusMonitor {
    constructor() {
        this.metrics = {
            loadTime: 0,
            jsErrors: [],
            resourceErrors: [],
            performanceScore: 0,
            memoryUsage: null,
            connectionType: null,
            isOnline: navigator.onLine
        };
        this.init();
        console.log('📊 网站状态监控器初始化完成');
    }

    init() {
        this.measureLoadTime();
        this.setupErrorHandling();
        this.checkResources();
        this.monitorPerformance();
        this.checkNetworkStatus();
        this.monitorMemoryUsage();
    }

    measureLoadTime() {
        if (performance && performance.timing) {
            const navigation = performance.timing;
            this.metrics.loadTime = navigation.loadEventEnd - navigation.navigationStart;
            console.log(`⏱️ 页面加载时间: ${this.metrics.loadTime}ms`);
        }
    }

    setupErrorHandling() {
        window.addEventListener('error', (event) => {
            const error = {
                type: 'javascript',
                message: event.message,
                filename: event.filename,
                line: event.lineno,
                column: event.colno,
                timestamp: new Date().toISOString()
            };
            this.metrics.jsErrors.push(error);
            console.warn('🚨 JavaScript错误:', error);
        });

        window.addEventListener('unhandledrejection', (event) => {
            const error = {
                type: 'promise',
                message: 'Promise rejection: ' + event.reason,
                filename: 'Promise',
                line: 0,
                column: 0,
                timestamp: new Date().toISOString()
            };
            this.metrics.jsErrors.push(error);
            console.warn('🚨 Promise拒绝:', error);
        });
    }

    checkResources() {
        const resources = performance.getEntriesByType('resource');
        resources.forEach(resource => {
            if (resource.transferSize === 0 && resource.name.includes('.')) {
                this.metrics.resourceErrors.push({
                    name: resource.name,
                    type: 'failed_to_load',
                    timestamp: new Date().toISOString()
                });
            }
        });
    }

    monitorPerformance() {
        if (performance.memory) {
            this.metrics.memoryUsage = {
                used: Math.round(performance.memory.usedJSHeapSize / 1024 / 1024),
                total: Math.round(performance.memory.totalJSHeapSize / 1024 / 1024),
                limit: Math.round(performance.memory.jsHeapSizeLimit / 1024 / 1024)
            };
        }

        // 计算性能评分
        this.calculatePerformanceScore();
    }

    checkNetworkStatus() {
        if ('connection' in navigator) {
            const connection = navigator.connection;
            this.metrics.connectionType = {
                effectiveType: connection.effectiveType,
                downlink: connection.downlink,
                rtt: connection.rtt
            };
        }

        window.addEventListener('online', () => {
            this.metrics.isOnline = true;
            console.log('🌐 网络连接已恢复');
        });

        window.addEventListener('offline', () => {
            this.metrics.isOnline = false;
            console.log('📵 网络连接已断开');
        });
    }

    monitorMemoryUsage() {
        if (performance.memory) {
            setInterval(() => {
                const current = performance.memory.usedJSHeapSize / 1024 / 1024;
                if (current > 100) { // 超过100MB
                    console.warn('⚠️ 内存使用量较高:', Math.round(current) + 'MB');
                }
            }, 30000); // 每30秒检查一次
        }
    }

    calculatePerformanceScore() {
        let score = 100;
        
        // 根据加载时间扣分
        if (this.metrics.loadTime > 3000) score -= 30;
        else if (this.metrics.loadTime > 2000) score -= 20;
        else if (this.metrics.loadTime > 1000) score -= 10;
        
        // 根据错误数量扣分
        score -= this.metrics.jsErrors.length * 5;
        score -= this.metrics.resourceErrors.length * 3;
        
        this.metrics.performanceScore = Math.max(0, score);
    }

    getStatusReport() {
        return {
            timestamp: new Date().toISOString(),
            metrics: this.metrics,
            status: this.getOverallStatus(),
            recommendations: this.getRecommendations()
        };
    }

    getOverallStatus() {
        if (this.metrics.performanceScore >= 90) return 'excellent';
        if (this.metrics.performanceScore >= 70) return 'good';
        if (this.metrics.performanceScore >= 50) return 'fair';
        return 'poor';
    }

    getRecommendations() {
        const recommendations = [];
        
        if (this.metrics.loadTime > 2000) {
            recommendations.push('考虑优化资源加载速度');
        }
        
        if (this.metrics.jsErrors.length > 0) {
            recommendations.push('修复JavaScript错误以提升稳定性');
        }
        
        if (this.metrics.resourceErrors.length > 0) {
            recommendations.push('检查并修复失败的资源加载');
        }
        
        if (this.metrics.memoryUsage && this.metrics.memoryUsage.used > 50) {
            recommendations.push('优化内存使用以提升性能');
        }
        
        return recommendations;
    }
}

// 综合集成测试类
class ComprehensiveIntegrationTester {
    constructor() {
        this.testResults = [];
        this.categories = {
            frontend: { passed: 0, failed: 0, total: 0 },
            animation: { passed: 0, failed: 0, total: 0 },
            chat: { passed: 0, failed: 0, total: 0 },
            accessibility: { passed: 0, failed: 0, total: 0 },
            detection: { passed: 0, failed: 0, total: 0 }
        };
        console.log('🧪 综合集成测试器初始化完成');
    }

    async runFullIntegrationTest() {
        console.log('🚀 开始完整集成测试...');
        
        const testSuites = [
            { name: 'frontend', tests: this.frontendTests() },
            { name: 'animation', tests: this.animationTests() },
            { name: 'chat', tests: this.chatTests() },
            { name: 'accessibility', tests: this.accessibilityTests() },
            { name: 'detection', tests: this.detectionTests() }
        ];

        for (const suite of testSuites) {
            await this.runTestSuite(suite.name, suite.tests);
        }

        this.generateTestReport();
        return this.getTestSummary();
    }

    async runTestSuite(category, tests) {
        console.log(`📋 运行${category}测试套件...`);
        
        for (const test of tests) {
            try {
                const result = await test.run();
                this.recordTestResult(category, test.name, result, null);
            } catch (error) {
                this.recordTestResult(category, test.name, false, error.message);
            }
        }
    }

    frontendTests() {
        return [
            {
                name: '前端资源加载检查',
                run: () => {
                    const scripts = document.scripts.length;
                    const stylesheets = document.styleSheets.length;
                    return scripts > 5 && stylesheets > 0;
                }
            },
            {
                name: 'DOM元素完整性',
                run: () => {
                    const elements = document.querySelectorAll('*').length;
                    return elements > 50;
                }
            },
            {
                name: '响应式设计检查',
                run: () => {
                    const viewport = document.querySelector('meta[name="viewport"]');
                    return viewport !== null;
                }
            }
        ];
    }

    animationTests() {
        return [
            {
                name: '量子粒子系统',
                run: () => {
                    const particles = document.getElementById('quantumParticles');
                    return particles !== null;
                }
            },
            {
                name: '轨道容器',
                run: () => {
                    const orbital = document.getElementById('orbitalContainer');
                    return orbital !== null;
                }
            },
            {
                name: '矩阵效果',
                run: () => {
                    const matrix = document.getElementById('matrixEffect');
                    return matrix !== null;
                }
            },
            {
                name: '量子加载器',
                run: () => {
                    const loader = document.getElementById('quantumLoader');
                    return loader !== null;
                }
            }
        ];
    }

    chatTests() {
        return [
            {
                name: '聊天组件',
                run: () => {
                    const widget = document.getElementById('chatWidget');
                    return widget !== null;
                }
            },
            {
                name: '聊天消息区域',
                run: () => {
                    const messages = document.getElementById('chatMessages');
                    return messages !== null;
                }
            },
            {
                name: '聊天输入框',
                run: () => {
                    const input = document.getElementById('chatInput');
                    return input !== null;
                }
            },
            {
                name: '聊天功能实例',
                run: () => {
                    return typeof window.chatSystem !== 'undefined';
                }
            },
            {
                name: '全局聊天对象',
                run: () => {
                    return typeof window.chatInstance !== 'undefined';
                }
            }
        ];
    }

    accessibilityTests() {
        return [
            {
                name: '无障碍工具栏',
                run: () => {
                    const toolbar = document.getElementById('accessibilityToolbar');
                    return toolbar !== null;
                }
            },
            {
                name: '字体大小控制',
                run: () => {
                    const fontUp = document.getElementById('fontSizeUp');
                    const fontDown = document.getElementById('fontSizeDown');
                    return fontUp !== null && fontDown !== null;
                }
            },
            {
                name: '对比度切换',
                run: () => {
                    const contrast = document.getElementById('toggleContrast');
                    return contrast !== null;
                }
            },
            {
                name: '屏幕阅读器支持',
                run: () => {
                    const reader = document.getElementById('toggleScreenReader');
                    return reader !== null;
                }
            },
            {
                name: '键盘导航支持',
                run: () => {
                    // 检查是否有焦点样式
                    const focusableElements = document.querySelectorAll('button, input, select, textarea, a[href]');
                    return focusableElements.length > 0;
                }
            }
        ];
    }

    detectionTests() {
        return [
            {
                name: '检测系统核心',
                run: () => {
                    return typeof initializeDetectionSystem !== 'undefined';
                }
            },
            {
                name: '错误处理器',
                run: () => {
                    return typeof window.systemErrorHandler !== 'undefined';
                }
            },
            {
                name: '性能监控器',
                run: () => {
                    return typeof window.performanceMonitor !== 'undefined';
                }
            },
            {
                name: '系统验证器',
                run: () => {
                    return typeof window.systemValidator !== 'undefined';
                }
            }
        ];
    }

    recordTestResult(category, testName, passed, errorMessage) {
        const result = {
            category,
            name: testName,
            passed,
            error: errorMessage,
            timestamp: new Date().toISOString()
        };
        
        this.testResults.push(result);
        this.categories[category].total++;
        
        if (passed) {
            this.categories[category].passed++;
            console.log(`✅ ${testName}: 通过`);
        } else {
            this.categories[category].failed++;
            console.error(`❌ ${testName}: 失败${errorMessage ? ` - ${errorMessage}` : ''}`);
        }
    }

    generateTestReport() {
        console.log('\n🎯 ===== 综合集成测试报告 =====');
        
        Object.entries(this.categories).forEach(([category, stats]) => {
            const passRate = stats.total > 0 ? ((stats.passed / stats.total) * 100).toFixed(1) : '0';
            console.log(`📊 ${category}: ${stats.passed}/${stats.total} 通过 (${passRate}%)`);
        });
        
        const totalTests = this.testResults.length;
        const totalPassed = this.testResults.filter(r => r.passed).length;
        const overallPassRate = totalTests > 0 ? ((totalPassed / totalTests) * 100).toFixed(1) : '0';
        
        console.log(`\n🎯 总体通过率: ${totalPassed}/${totalTests} (${overallPassRate}%)`);
        
        if (overallPassRate >= 90) {
            console.log('🎉 优秀！系统状态非常良好');
        } else if (overallPassRate >= 70) {
            console.log('👍 良好！大部分功能正常');
        } else if (overallPassRate >= 50) {
            console.log('⚠️ 一般，需要关注一些问题');
        } else {
            console.log('❌ 需要修复多个问题');
        }
        
        console.log('=========================\n');
    }

    getTestSummary() {
        const totalTests = this.testResults.length;
        const totalPassed = this.testResults.filter(r => r.passed).length;
        const overallPassRate = totalTests > 0 ? ((totalPassed / totalTests) * 100).toFixed(1) : '0';
        
        return {
            timestamp: new Date().toISOString(),
            totalTests,
            totalPassed,
            totalFailed: totalTests - totalPassed,
            overallPassRate: parseFloat(overallPassRate),
            categories: this.categories,
            results: this.testResults
        };
    }
}

// 全局函数定义
function runWebsiteStatusCheck() {
    if (window.websiteStatusMonitor) {
        const report = window.websiteStatusMonitor.getStatusReport();
        console.log('🌐 网站状态报告:', report);
        return report;
    } else {
        console.warn('⚠️ 网站状态监控器未初始化');
        return null;
    }
}

function runIntegrationTest() {
    if (window.integrationTester) {
        return window.integrationTester.runFullIntegrationTest();
    } else {
        console.warn('⚠️ 集成测试器未初始化');
        return Promise.resolve(null);
    }
}

// 页面加载后初始化
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        try {
            window.websiteStatusMonitor = new WebsiteStatusMonitor();
            window.integrationTester = new ComprehensiveIntegrationTester();
            
            console.log('✅ 网站状态监控和集成测试模块初始化完成');
            
            // 5秒后自动运行一次集成测试
            setTimeout(() => {
                runIntegrationTest().then(summary => {
                    if (summary) {
                        console.log('📋 集成测试完成，通过率:', summary.overallPassRate + '%');
                    }
                });
            }, 5000);
            
        } catch (error) {
            console.error('❌ 网站状态监控和集成测试模块初始化失败:', error);
        }
    }, 2000);
});

console.log('✅ 网站状态监控和集成测试模块加载完成');
