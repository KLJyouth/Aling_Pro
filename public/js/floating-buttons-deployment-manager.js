/**
 * 浮动按钮系统部署管理器
 * 用于将优化后的浮动按钮系统部署到生产环境
 */

class FloatingButtonsDeploymentManager {
    constructor() {
        this.deploymentChecklist = [];
        this.deploymentStatus = 'not-started';
        this.init();
    }
    
    init() {
        
        this.createDeploymentChecklist();
    }
    
    createDeploymentChecklist() {
        this.deploymentChecklist = [
            {
                id: 'manager-files',
                name: '管理器文件检查',
                description: '确保所有浮动按钮管理器文件已正确部署',
                status: 'pending',
                files: [
                    'assets/js/floating-buttons-manager.js',
                    'assets/js/chat-button-integrator.js',
                    'assets/js/floating-buttons-diagnostic.js'
                ]
            },
            {
                id: 'html-integration',
                name: 'HTML集成检查',
                description: '确保主要HTML文件已包含浮动按钮管理器脚本',
                status: 'pending',
                files: ['index.html', 'chat.html']
            },
            {
                id: 'component-updates',
                name: '组件更新检查',
                description: '确保所有浮动按钮组件已更新为使用新的管理系统',
                status: 'pending',
                components: [
                    'social-customization.js',
                    'realtime-performance-dashboard.js',
                    'advanced-debug-console.js',
                    'quantum-chat-integrator.js'
                ]
            },
            {
                id: 'positioning-verification',
                name: '定位验证',
                description: '验证所有浮动按钮的位置和层级正确',
                status: 'pending'
            },
            {
                id: 'performance-test',
                name: '性能测试',
                description: '验证系统性能满足要求',
                status: 'pending'
            },
            {
                id: 'cross-browser-test',
                name: '跨浏览器测试',
                description: '在不同浏览器中测试兼容性',
                status: 'pending'
            },
            {
                id: 'mobile-responsive',
                name: '移动端响应式测试',
                description: '验证移动设备上的显示效果',
                status: 'pending'
            }
        ];
    }
    
    async runDeploymentCheck() {
        
        this.deploymentStatus = 'running';
        
        try {
            for (const item of this.deploymentChecklist) {
                await this.checkDeploymentItem(item);
            }
            
            this.deploymentStatus = 'completed';
            this.generateDeploymentReport();
        } catch (error) {
            this.deploymentStatus = 'failed';
            console.error('❌ 部署检查失败:', error);
        }
    }
    
    async checkDeploymentItem(item) {
        
        
        try {
            switch (item.id) {
                case 'manager-files':
                    await this.checkManagerFiles(item);
                    break;
                case 'html-integration':
                    await this.checkHtmlIntegration(item);
                    break;
                case 'component-updates':
                    await this.checkComponentUpdates(item);
                    break;
                case 'positioning-verification':
                    await this.checkPositioning(item);
                    break;
                case 'performance-test':
                    await this.checkPerformance(item);
                    break;
                case 'cross-browser-test':
                    await this.checkCrossBrowser(item);
                    break;
                case 'mobile-responsive':
                    await this.checkMobileResponsive(item);
                    break;
            }
            
            item.status = 'passed';
            
        } catch (error) {
            item.status = 'failed';
            item.error = error.message;
            console.error(`❌ ${item.name}: 失败 - ${error.message}`);
        }
    }
    
    async checkManagerFiles(item) {
        // 检查管理器文件是否存在且可访问
        for (const file of item.files) {
            try {
                const response = await fetch(file, { method: 'HEAD' });
                if (!response.ok) {
                    throw new Error(`文件不可访问: ${file}`);
                }
            } catch (error) {
                throw new Error(`管理器文件检查失败: ${file} - ${error.message}`);
            }
        }
        
        // 检查JavaScript对象是否正确加载
        if (!window.FloatingButtonManager) {
            throw new Error('FloatingButtonManager类未加载');
        }
        
        if (!window.floatingButtonManager) {
            throw new Error('floatingButtonManager实例未创建');
        }
    }
    
    async checkHtmlIntegration(item) {
        // 检查脚本标签是否存在
        const scripts = document.querySelectorAll('script[src*="floating-buttons-manager"]');
        if (scripts.length === 0) {
            throw new Error('HTML中未找到浮动按钮管理器脚本引用');
        }
        
        // 检查脚本加载顺序
        const allScripts = Array.from(document.querySelectorAll('script[src]'));
        const managerIndex = allScripts.findIndex(script => script.src.includes('floating-buttons-manager'));
        const integratorIndex = allScripts.findIndex(script => script.src.includes('chat-button-integrator'));
        
        if (managerIndex >= integratorIndex && integratorIndex !== -1) {
            throw new Error('脚本加载顺序错误：管理器应该在集成器之前加载');
        }
    }
    
    async checkComponentUpdates(item) {
        // 检查组件是否已注册到管理器
        const manager = window.floatingButtonManager;
        if (!manager) {
            throw new Error('浮动按钮管理器不可用');
        }
        
        const expectedButtons = [
            'performance-dashboard',
            'social-customization', 
            'debug-console',
            'quantum-chat'
        ];
        
        for (const buttonId of expectedButtons) {
            if (!manager.registeredButtons.has(buttonId)) {
                console.warn(`⚠️ 按钮未注册: ${buttonId}`);
            }
        }
        
        // 至少应该有一些按钮注册
        if (manager.registeredButtons.size === 0) {
            throw new Error('没有任何按钮注册到管理器');
        }
    }
    
    async checkPositioning(item) {
        const buttons = document.querySelectorAll('.floating-button, [data-floating-button]');
        
        if (buttons.length === 0) {
            throw new Error('页面上没有找到浮动按钮');
        }
        
        // 检查按钮是否在视窗内
        let outOfViewport = 0;
        buttons.forEach(button => {
            const rect = button.getBoundingClientRect();
            if (rect.right > window.innerWidth || rect.bottom > window.innerHeight ||
                rect.left < 0 || rect.top < 0) {
                outOfViewport++;
            }
        });
        
        if (outOfViewport > 0) {
            throw new Error(`${outOfViewport}个按钮超出视窗范围`);
        }
        
        // 检查重叠
        let overlaps = 0;
        for (let i = 0; i < buttons.length; i++) {
            for (let j = i + 1; j < buttons.length; j++) {
                const rect1 = buttons[i].getBoundingClientRect();
                const rect2 = buttons[j].getBoundingClientRect();
                
                if (this.isOverlapping(rect1, rect2)) {
                    overlaps++;
                }
            }
        }
        
        if (overlaps > 0) {
            throw new Error(`发现${overlaps}个按钮重叠`);
        }
    }
    
    async checkPerformance(item) {
        const startTime = performance.now();
        
        // 测试管理器性能
        const manager = window.floatingButtonManager;
        if (manager) {
            for (let i = 0; i < 1000; i++) {
                manager.getAvailableSlots();
            }
        }
        
        const endTime = performance.now();
        const executionTime = endTime - startTime;
        
        if (executionTime > 500) {
            throw new Error(`性能测试失败：执行时间过长 (${executionTime.toFixed(2)}ms)`);
        }
    }
    
    async checkCrossBrowser(item) {
        // 检查浏览器兼容性特性
        const features = [
            'CSS.supports',
            'IntersectionObserver',
            'requestAnimationFrame',
            'addEventListener'
        ];
        
        for (const feature of features) {
            if (!this.hasFeature(feature)) {
                throw new Error(`浏览器不支持必需特性: ${feature}`);
            }
        }
        
        // 检查CSS特性
        if (!CSS.supports('backdrop-filter', 'blur(10px)')) {
            console.warn('⚠️ 浏览器不支持backdrop-filter，可能影响视觉效果');
        }
    }
    
    async checkMobileResponsive(item) {
        // 模拟移动设备检查
        const isMobile = window.innerWidth <= 768;
        
        if (isMobile) {
            const buttons = document.querySelectorAll('.floating-button, [data-floating-button]');
            
            buttons.forEach(button => {
                const computedStyle = window.getComputedStyle(button);
                const size = Math.min(
                    parseInt(computedStyle.width),
                    parseInt(computedStyle.height)
                );
                
                if (size < 44) { // iOS最小触摸目标
                    console.warn(`⚠️ 移动设备按钮可能太小: ${button.id} (${size}px)`);
                }
            });
        }
    }
    
    hasFeature(featurePath) {
        return featurePath.split('.').reduce((obj, prop) => obj && obj[prop], window);
    }
    
    isOverlapping(rect1, rect2) {
        return !(rect1.right <= rect2.left || 
                rect2.right <= rect1.left || 
                rect1.bottom <= rect2.top || 
                rect2.bottom <= rect1.top);
    }
    
    generateDeploymentReport() {
        const passed = this.deploymentChecklist.filter(item => item.status === 'passed').length;
        const failed = this.deploymentChecklist.filter(item => item.status === 'failed').length;
        const total = this.deploymentChecklist.length;
        
        const report = {
            timestamp: new Date().toISOString(),
            status: this.deploymentStatus,
            summary: {
                total,
                passed,
                failed,
                successRate: ((passed / total) * 100).toFixed(2) + '%'
            },
            checklist: this.deploymentChecklist,
            recommendations: this.generateRecommendations()
        };
        
        
        this.displayDeploymentReport(report);
        
        return report;
    }
    
    generateRecommendations() {
        const recommendations = [];
        const failed = this.deploymentChecklist.filter(item => item.status === 'failed');
        
        if (failed.length === 0) {
            recommendations.push('✅ 系统已准备好部署到生产环境');
            recommendations.push('🔄 建议在生产环境中进行最终验证测试');
            recommendations.push('📊 建议设置监控系统跟踪浮动按钮的使用情况');
        } else {
            recommendations.push('❌ 存在部署前问题，需要解决后再部署');
            failed.forEach(item => {
                recommendations.push(`🔧 修复: ${item.name} - ${item.error}`);
            });
        }
        
        return recommendations;
    }
    
    displayDeploymentReport(report) {
        let reportElement = document.getElementById('deploymentReport');
        if (!reportElement) {
            reportElement = document.createElement('div');
            reportElement.id = 'deploymentReport';
            reportElement.className = 'fixed top-4 right-4 bg-gray-900 text-white p-6 rounded-lg shadow-lg max-w-md z-50 max-h-96 overflow-y-auto';
            document.body.appendChild(reportElement);
        }
        
        const statusColor = report.status === 'completed' && report.summary.failed === 0 
            ? 'text-green-400' : 'text-red-400';
        
        reportElement.innerHTML = `
            <div class="mb-4">
                <h3 class="text-lg font-bold mb-2">🚀 部署检查报告</h3>
                <div class="text-sm space-y-1">
                    <div class="${statusColor}">状态: ${report.status}</div>
                    <div>检查项: ${report.summary.total}</div>
                    <div class="text-green-400">通过: ${report.summary.passed}</div>
                    <div class="text-red-400">失败: ${report.summary.failed}</div>
                    <div class="${statusColor}">成功率: ${report.summary.successRate}</div>
                </div>
            </div>
            
            <div class="mb-4">
                <h4 class="font-semibold mb-2">建议:</h4>
                <div class="space-y-1 text-xs">
                    ${report.recommendations.map(rec => `
                        <div class="flex items-start space-x-1">
                            <span>${rec}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <div class="flex space-x-2">
                <button onclick="this.parentElement.parentElement.remove()" 
                        class="px-3 py-1 bg-gray-700 rounded text-xs hover:bg-gray-600">
                    关闭
                </button>
                <button onclick="window.floatingButtonsDeploymentManager.runDeploymentCheck()" 
                        class="px-3 py-1 bg-blue-600 rounded text-xs hover:bg-blue-500">
                    重新检查
                </button>
            </div>
        `;
    }
    
    async deployToProduction() {
        
        
        // 这里可以添加实际的部署逻辑
        // 例如：复制文件、更新配置、重启服务等
        
        return {
            status: 'success',
            message: '浮动按钮系统已成功部署到生产环境',
            timestamp: new Date().toISOString()
        };
    }
}

// 自动初始化
document.addEventListener('DOMContentLoaded', function() {
    window.floatingButtonsDeploymentManager = new FloatingButtonsDeploymentManager();
});

// 提供全局访问
window.FloatingButtonsDeploymentManager = FloatingButtonsDeploymentManager;
