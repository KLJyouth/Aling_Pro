/**
 * AlingAi 集成检测系统 - 增强功能演示脚本
 * 展示所有新完成的功能，包括历史管理、性能基线、自动检测、错误诊断等高级功能
 * 创建时间: 2025年5月30日
 */

class EnhancedDetectionDemo {
    constructor() {
        this.demos = [
            {
                name: '历史记录管理演示',
                description: '展示测试历史记录跟踪和管理功能',
                action: () => this.demoHistoryManagement()
            },
            {
                name: '性能基线系统演示',
                description: '展示性能基线建立和趋势分析',
                action: () => this.demoPerformanceBaseline()
            },
            {
                name: '自动检测调度演示',
                description: '展示自动定期检测功能',
                action: () => this.demoAutoDetection()
            },
            {
                name: '错误诊断系统演示',
                description: '展示智能错误诊断和建议功能',
                action: () => this.demoDiagnostics()
            },
            {
                name: '高级报告系统演示',
                description: '展示综合分析报告生成',
                action: () => this.demoAdvancedReporting()
            },
            {
                name: 'CSV/PDF导出演示',
                description: '展示多种格式报告导出功能',
                action: () => this.demoExportFormats()
            },
            {
                name: '系统信息显示演示',
                description: '展示详细的系统环境信息',
                action: () => this.demoSystemInfo()
            },
            {
                name: '完整工作流演示',
                description: '运行完整检测并展示所有功能',
                action: () => this.demoCompleteWorkflow()
            }
        ];
        this.currentDemo = 0;
    }

    async start() {
        console.log('🎬 开始增强功能演示...');
        
        // 等待检测系统初始化
        if (!window.detectionSystem) {
            console.log('⏳ 等待检测系统初始化...');
            await this.waitForSystem();
        }
        
        this.showDemoMenu();
    }

    async waitForSystem() {
        return new Promise((resolve) => {
            const checkSystem = () => {
                if (window.detectionSystem) {
                    resolve();
                } else {
                    setTimeout(checkSystem, 100);
                }
            };
            checkSystem();
        });
    }

    showDemoMenu() {
        const menuHTML = `
            <div class="alert alert-info border-0" style="background: rgba(23, 162, 184, 0.1); border-left: 4px solid #17a2b8 !important;">
                <h6><i class="bi bi-play-circle-fill me-2"></i>增强功能演示中心</h6>
                <p class="mb-3">体验AlingAi集成检测系统的所有高级功能:</p>
                
                <div class="row g-2">
                    <div class="col-md-6">
                        <button class="btn btn-sm btn-outline-info w-100" onclick="enhancedDemo.demoHistoryManagement()">
                            <i class="bi bi-clock-history me-1"></i>历史记录管理
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-sm btn-outline-info w-100" onclick="enhancedDemo.demoPerformanceBaseline()">
                            <i class="bi bi-speedometer2 me-1"></i>性能基线系统
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-sm btn-outline-info w-100" onclick="enhancedDemo.demoAutoDetection()">
                            <i class="bi bi-arrow-repeat me-1"></i>自动检测调度
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-sm btn-outline-info w-100" onclick="enhancedDemo.demoDiagnostics()">
                            <i class="bi bi-search me-1"></i>错误诊断系统
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-sm btn-outline-info w-100" onclick="enhancedDemo.demoAdvancedReporting()">
                            <i class="bi bi-graph-up me-1"></i>高级报告系统
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-sm btn-outline-info w-100" onclick="enhancedDemo.demoExportFormats()">
                            <i class="bi bi-download me-1"></i>多格式导出
                        </button>
                    </div>
                </div>
                
                <hr class="my-3">
                
                <div class="row g-2">
                    <div class="col-md-4">
                        <button class="btn btn-sm btn-success w-100" onclick="enhancedDemo.demoCompleteWorkflow()">
                            <i class="bi bi-play-fill me-1"></i>完整工作流
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-sm btn-warning w-100" onclick="enhancedDemo.generateAllDemoData()">
                            <i class="bi bi-database-fill-add me-1"></i>生成演示数据
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-sm btn-danger w-100" onclick="enhancedDemo.resetAllDemoData()">
                            <i class="bi bi-trash me-1"></i>清除演示数据
                        </button>
                    </div>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        提示: 使用键盘快捷键 Ctrl+H(历史) Ctrl+P(性能) 快速访问功能
                    </small>
                </div>
            </div>
        `;

        // 在控制面板后插入演示菜单
        const controlPanel = document.querySelector('.control-panel');
        if (controlPanel) {
            const existingMenu = document.getElementById('enhancedDemoMenu');
            if (existingMenu) {
                existingMenu.remove();
            }

            const menuDiv = document.createElement('div');
            menuDiv.id = 'enhancedDemoMenu';
            menuDiv.innerHTML = menuHTML;
            controlPanel.parentNode.insertBefore(menuDiv, controlPanel.nextSibling);
        }
    }

    async runDemo(index) {
        if (index < 1 || index > this.demos.length) {
            console.log('❌ 无效的演示编号');
            return;
        }

        const demo = this.demos[index - 1];
        console.log(`\n🎬 开始演示: ${demo.name}`);
        console.log(`📝 描述: ${demo.description}`);
        
        try {
            await demo.action();
            console.log(`✅ 演示完成: ${demo.name}`);
        } catch (error) {
            console.log(`❌ 演示失败: ${error.message}`);
        }
    }

    async runAll() {
        console.log('🎬 开始运行所有演示...');
        
        for (let i = 0; i < this.demos.length; i++) {
            await this.runDemo(i + 1);
            if (i < this.demos.length - 1) {
                await this.delay(2000); // 演示间隔2秒
            }
        }
        
        console.log('🎉 所有演示完成！');
    }

    async next() {
        this.currentDemo = (this.currentDemo % this.demos.length) + 1;
        await this.runDemo(this.currentDemo);
    }

    // ==================== 具体演示实现 ====================

    async demoCSVExport() {
        logInfo('📊 演示CSV导出功能...');
        
        // 先运行一些测试以生成数据
        await this.generateSampleData();
        
        // 演示CSV导出
        logInfo('🔄 准备导出CSV报告...');
        await this.delay(1000);
        
        if (window.exportCSV) {
            exportCSV();
            logSuccess('✅ CSV导出功能演示完成！');
        } else {
            logError('❌ CSV导出功能不可用');
        }
    }

    async demoPDFExport() {
        logInfo('📄 演示PDF导出功能...');
        
        // 先运行一些测试以生成数据
        await this.generateSampleData();
        
        // 演示PDF导出
        logInfo('🔄 准备导出PDF报告...');
        await this.delay(1000);
        
        if (window.exportPDF) {
            exportPDF();
            logSuccess('✅ PDF导出功能演示完成！');
        } else {
            logError('❌ PDF导出功能不可用');
        }
    }

    async demoSystemInfo() {
        logInfo('💻 演示系统信息显示功能...');
        
        await this.delay(1000);
        
        if (window.showSystemInfo) {
            showSystemInfo();
            logSuccess('✅ 系统信息功能演示完成！请查看弹出的模态框');
        } else {
            logError('❌ 系统信息功能不可用');
        }
    }

    async demoCustomDetection() {
        logInfo('⚙️ 演示自定义检测功能...');
        
        await this.delay(1000);
        
        if (window.runCustomDetection) {
            runCustomDetection();
            logSuccess('✅ 自定义检测功能演示完成！请查看弹出的选择界面');
        } else {
            logError('❌ 自定义检测功能不可用');
        }
    }

    async demoLogManagement() {
        logInfo('📜 演示日志管理功能...');
        
        // 生成一些日志
        for (let i = 1; i <= 5; i++) {
            logInfo(`📝 示例日志消息 ${i}`);
            await this.delay(200);
        }
        
        // 演示自动滚动切换
        logInfo('🔄 演示自动滚动切换...');
        await this.delay(1000);
        
        if (window.toggleAutoScroll) {
            toggleAutoScroll();
            await this.delay(1000);
            toggleAutoScroll(); // 再次切换回来
        }
        
        // 演示日志导出
        logInfo('📤 演示日志导出功能...');
        await this.delay(1000);
        
        if (window.exportLog) {
            exportLog();
            logSuccess('✅ 日志管理功能演示完成！');
        } else {
            logError('❌ 日志导出功能不可用');
        }
    }

    async demoKeyboardShortcuts() {
        logInfo('⌨️ 演示键盘快捷键功能...');
        
        const shortcuts = [
            'Ctrl+R - 快速检测',
            'Ctrl+F - 完整检测',
            'Ctrl+C - 清空结果',
            'Ctrl+S - 导出结果'
        ];
        
        logInfo('📋 可用的键盘快捷键:');
        shortcuts.forEach(shortcut => {
            logInfo(`  • ${shortcut}`);
        });
        
        logInfo('💡 提示: 请尝试使用这些快捷键来操作系统');
        logSuccess('✅ 键盘快捷键功能演示完成！');
    }

    async demoCompleteWorkflow() {
        if (!window.detectionSystem) return;

        console.log('🎬 开始完整工作流演示...');
        detectionSystem.logInfo('🎭 演示: 完整检测工作流程');

        // 第一步：生成基础数据
        detectionSystem.logInfo('📊 步骤1: 准备演示数据...');
        this.generateAllDemoData();

        // 第二步：运行快速检测
        setTimeout(async () => {
            detectionSystem.logInfo('⚡ 步骤2: 运行快速检测...');
            await runQuickDetection();
        }, 2000);

        // 第三步：展示历史记录
        setTimeout(() => {
            detectionSystem.logInfo('📋 步骤3: 查看历史记录...');
            showHistory();
        }, 5000);

        // 第四步：展示性能报告
        setTimeout(() => {
            detectionSystem.logInfo('📈 步骤4: 分析性能数据...');
            showPerformanceReport();
        }, 8000);

        // 第五步：启用自动检测
        setTimeout(() => {
            detectionSystem.logInfo('🔄 步骤5: 启用自动检测...');
            detectionSystem.enableAutoDetection(30);
        }, 11000);

        // 第六步：导出高级报告
        setTimeout(() => {
            detectionSystem.logInfo('📊 步骤6: 导出综合报告...');
            exportAdvancedReport();
        }, 14000);

        // 完成提示
        setTimeout(() => {
            detectionSystem.logSuccess('🎉 完整工作流演示完成！');
            detectionSystem.logInfo('💡 您已体验了系统的所有核心功能');
            detectionSystem.logInfo('🚀 现在可以在真实环境中使用这些功能');
        }, 17000);
    }

    // ==================== 新增高级功能演示方法 ====================

    async demoHistoryManagement() {
        if (!window.detectionSystem) {
            console.error('检测系统未初始化');
            return;
        }

        console.log('📊 开始历史记录管理演示...');
        detectionSystem.logInfo('🎭 演示: 历史记录管理功能');

        // 生成一些示例历史数据
        this.generateSampleHistory();
        
        setTimeout(() => {
            detectionSystem.logInfo('📋 显示历史记录模态框...');
            showHistory();
        }, 1000);

        setTimeout(() => {
            detectionSystem.logInfo('💡 您可以在历史记录中查看所有测试执行记录');
            detectionSystem.logInfo('🔧 支持功能: 查看详情、导出历史、清空记录');
        }, 2000);
    }

    async demoPerformanceBaseline() {
        if (!window.detectionSystem) return;

        console.log('⚡ 开始性能基线演示...');
        detectionSystem.logInfo('🎭 演示: 性能基线系统');

        // 生成性能基线数据
        this.generatePerformanceData();

        setTimeout(() => {
            detectionSystem.logInfo('📈 显示性能报告...');
            showPerformanceReport();
        }, 1000);

        setTimeout(() => {
            detectionSystem.logInfo('💡 性能基线功能帮助您:');
            detectionSystem.logInfo('   • 跟踪测试执行时间趋势');
            detectionSystem.logInfo('   • 识别性能回归问题');
            detectionSystem.logInfo('   • 建立性能优化目标');
        }, 2000);
    }

    async demoAutoDetection() {
        if (!window.detectionSystem) return;

        console.log('🔄 开始自动检测演示...');
        detectionSystem.logInfo('🎭 演示: 自动检测调度功能');
        
        detectionSystem.logInfo('⏰ 启用自动检测 (演示用1分钟间隔)...');
        detectionSystem.enableAutoDetection(1);

        setTimeout(() => {
            detectionSystem.logInfo('💡 自动检测功能特点:');
            detectionSystem.logInfo('   • 可配置检测间隔');
            detectionSystem.logInfo('   • 持久化配置保存');
            detectionSystem.logInfo('   • 智能冲突避免');
            detectionSystem.logInfo('   • 状态实时显示');
        }, 1000);

        // 3分钟后停止演示
        setTimeout(() => {
            detectionSystem.disableAutoDetection();
            detectionSystem.logInfo('🛑 自动检测演示结束');
        }, 3 * 60 * 1000);
    }

    async demoDiagnostics() {
        if (!window.detectionSystem) return;

        console.log('🔍 开始错误诊断演示...');
        detectionSystem.logInfo('🎭 演示: 智能错误诊断系统');

        // 模拟不同类型的错误进行诊断
        const errorCases = [
            { error: 'Network request failed: Connection timeout', context: { testType: '网络连接测试' }},
            { error: 'Unauthorized access - 401 error', context: { testType: '身份验证测试' }},
            { error: 'Resource not found - 404', context: { testType: '资源访问测试' }},
            { error: 'Syntax error: Unexpected token', context: { testType: '代码解析测试' }},
            { error: 'Performance degradation detected', context: { testType: '性能监控测试' }}
        ];

        errorCases.forEach((testCase, index) => {
            setTimeout(() => {
                const diagnosis = detectionSystem.diagnoseError(new Error(testCase.error), testCase.context);
                
                detectionSystem.logError(`❌ 模拟错误: ${testCase.error}`);
                detectionSystem.logInfo(`🔍 诊断结果: ${diagnosis.matches.join(', ')}`);
                detectionSystem.logInfo(`⚠️ 严重级别: ${diagnosis.severity}`);
                detectionSystem.logInfo(`💡 建议解决方案: ${diagnosis.suggestions[0]}`);
                
                if (index === errorCases.length - 1) {
                    setTimeout(() => {
                        detectionSystem.logSuccess('✅ 错误诊断演示完成');
                        detectionSystem.logInfo('💡 诊断系统可帮助快速定位和解决问题');
                    }, 1000);
                }
            }, index * 2000);
        });
    }

    async demoAdvancedReporting() {
        if (!window.detectionSystem) return;

        console.log('📈 开始高级报告演示...');
        detectionSystem.logInfo('🎭 演示: 高级分析报告系统');

        // 确保有足够的数据
        this.generateSampleHistory();
        this.generatePerformanceData();

        setTimeout(() => {
            detectionSystem.logInfo('📊 生成高级报告...');
            const report = detectionSystem.generateAdvancedReport();
            
            detectionSystem.logInfo('📋 报告内容包含:');
            detectionSystem.logInfo(`   • 总体摘要: ${report.summary.completedTests}次测试，成功率${report.summary.successRate}%`);
            detectionSystem.logInfo(`   • 性能指标: 平均耗时${report.performance.averagePerformance}ms`);
            detectionSystem.logInfo(`   • 历史趋势: ${report.history.totalRuns}条记录分析`);
            detectionSystem.logInfo(`   • 智能建议: ${report.recommendations.length}条优化建议`);
            
            // 导出高级报告
            setTimeout(() => {
                exportAdvancedReport();
            }, 2000);
        }, 1000);
    }

    async demoExportFormats() {
        if (!window.detectionSystem) return;

        console.log('📄 开始导出格式演示...');
        detectionSystem.logInfo('🎭 演示: 多格式报告导出');

        // 确保有测试数据
        if (detectionSystem.testResults.size === 0) {
            await this.runSampleTests();
        }

        setTimeout(() => {
            detectionSystem.logInfo('📄 导出JSON格式报告...');
            exportJSON();
        }, 1000);

        setTimeout(() => {
            detectionSystem.logInfo('📊 导出CSV格式报告...');
            exportCSV();
        }, 2000);

        setTimeout(() => {
            detectionSystem.logInfo('📋 导出PDF格式报告...');
            exportPDF();
        }, 3000);

        setTimeout(() => {
            detectionSystem.logSuccess('✅ 多格式导出演示完成');
            detectionSystem.logInfo('💡 支持JSON、CSV、PDF三种导出格式');
        }, 4000);
    }

    // ==================== 辅助方法 ====================

    generateSampleHistory() {
        if (!window.detectionSystem) return;

        const categories = ['backend', 'frontend', 'websocket', 'chat', 'performance'];
        const testNames = [
            '服务器健康检查', '数据库连接', 'API端点验证', '页面加载测试',
            'WebSocket连接', '聊天功能', '性能监控', '内存使用检查'
        ];

        // 生成近期的模拟历史数据
        for (let i = 0; i < 30; i++) {
            const daysAgo = Math.floor(Math.random() * 30);
            const timestamp = Date.now() - (daysAgo * 24 * 60 * 60 * 1000) - (Math.random() * 24 * 60 * 60 * 1000);
            const category = categories[Math.floor(Math.random() * categories.length)];
            const testName = testNames[Math.floor(Math.random() * testNames.length)];
            const duration = Math.random() * 2000 + 100;
            const statusRand = Math.random();
            const status = statusRand > 0.8 ? 'success' : statusRand > 0.6 ? 'warning' : 'error';

            const historyEntry = {
                id: `demo_${timestamp}_${Math.random().toString(36).substr(2, 6)}`,
                sessionId: `demo_session_${Math.floor(timestamp / (24 * 60 * 60 * 1000))}`,
                timestamp: timestamp,
                datetime: new Date(timestamp).toISOString(),
                category: category,
                testId: `demo_${category}_${i}`,
                testName: testName,
                status: status,
                duration: duration,
                details: `演示测试数据 - ${status}`,
                error: status === 'error' ? `模拟错误: ${testName}失败` : null,
                environment: {
                    userAgent: navigator.userAgent.substring(0, 100),
                    platform: navigator.platform,
                    url: window.location.href
                }
            };

            detectionSystem.testHistory.push(historyEntry);

            // 更新性能基线
            if (status === 'success') {
                detectionSystem.updatePerformanceBaseline(category, `demo_${category}_${i}`, duration);
            }
        }

        // 保持历史记录在限制范围内
        if (detectionSystem.testHistory.length > detectionSystem.maxHistoryRecords) {
            detectionSystem.testHistory = detectionSystem.testHistory.slice(-detectionSystem.maxHistoryRecords);
        }

        detectionSystem.saveToStorage();
        detectionSystem.logInfo('📊 已生成30条演示历史记录');
    }

    generatePerformanceData() {
        if (!window.detectionSystem) return;

        const testCategories = ['backend', 'frontend', 'websocket', 'chat', 'performance'];
        const testIds = ['health', 'load', 'connect', 'message', 'monitor'];

        testCategories.forEach((category, categoryIndex) => {
            const testId = testIds[categoryIndex];
            
            // 为每个测试生成多次运行记录以建立基线
            for (let run = 0; run < 15; run++) {
                const baseDuration = 200 + (categoryIndex * 150);
                const variance = (Math.random() - 0.5) * 200;
                const trendFactor = run * 5; // 轻微的性能改善趋势
                const duration = baseDuration + variance - trendFactor;

                detectionSystem.updatePerformanceBaseline(category, testId, Math.max(50, duration));
            }
        });

        detectionSystem.logInfo('⚡ 已生成性能基线数据');
    }

    async runSampleTests() {
        if (!window.detectionSystem) return;

        detectionSystem.logInfo('🏃 运行示例测试以生成数据...');
        
        // 模拟几个快速测试
        const sampleTests = [
            { category: 'backend', id: 'serverHealth', result: { status: 'success', duration: 150, message: '服务器响应正常' }},
            { category: 'frontend', id: 'pageAccess', result: { status: 'success', duration: 320, message: '页面加载成功' }},
            { category: 'websocket', id: 'wsConnection', result: { status: 'warning', duration: 280, message: '连接建立但响应较慢' }}
        ];

        for (const test of sampleTests) {
            const result = { ...test.result, timestamp: Date.now() };
            detectionSystem.testResults.set(`${test.category}.${test.id}`, result);
            detectionSystem.addTestToHistory(test.category, test.id, result);
            
            await new Promise(resolve => setTimeout(resolve, 500));
        }
    }    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // ==================== 快速访问方法 ====================

    showFeatures() {
        console.log('\n🎯 AlingAi集成检测系统高级功能清单:');
        console.log('━'.repeat(50));
        console.log('✅ 历史记录管理系统（持久化存储、智能限制）');
        console.log('✅ 性能基线建立与趋势分析');
        console.log('✅ 自动检测调度（可配置间隔、状态持久化）');
        console.log('✅ 智能错误诊断系统（模式匹配、解决建议）');
        console.log('✅ 高级分析报告生成（趋势、建议、指标）');
        console.log('✅ 多格式导出（JSON、CSV、PDF）');
        console.log('✅ 会话管理和环境检测');
        console.log('✅ 实时状态监控和显示');
        console.log('✅ 键盘快捷键支持（Ctrl+H/P/R/F）');
        console.log('✅ 响应式UI和模态框系统');
        console.log('✅ 数据持久化和自动恢复');
        console.log('✅ 性能优化和内存管理');
        console.log('━'.repeat(50));
    }    showHelp() {
        console.log('\n📖 高级功能演示帮助:');
        console.log('━'.repeat(40));
        console.log('demo.demoHistoryManagement()    - 历史记录管理演示');
        console.log('demo.demoPerformanceBaseline()  - 性能基线演示');
        console.log('demo.demoAutoDetection()        - 自动检测演示');
        console.log('demo.demoDiagnostics()          - 错误诊断演示');
        console.log('demo.demoAdvancedReporting()    - 高级报告演示');
        console.log('demo.demoCompleteWorkflow()     - 完整工作流演示');
        console.log('demo.generateAllDemoData()      - 生成演示数据');
        console.log('demo.resetAllDemoData()         - 清除演示数据');
        console.log('demo.showFeatures()             - 显示功能清单');
        console.log('demo.showHelp()                 - 显示此帮助');
        console.log('━'.repeat(40));
        console.log('💡 也可以使用页面上的演示菜单按钮');
    }

    /**
     * 生成所有演示数据
     */
    async generateAllDemoData() {
        if (!window.detectionSystem) {
            console.error('❌ 检测系统未初始化');
            return;
        }

        console.log('🎲 正在生成演示数据...');
        detectionSystem.logInfo('🎲 开始生成演示数据');

        try {
            // 生成历史记录数据
            await this.generateHistoryData();
            
            // 生成性能基线数据
            await this.generatePerformanceData();
            
            // 运行示例测试
            await this.runSampleTests();
            
            console.log('✅ 所有演示数据生成完成');
            detectionSystem.logSuccess('✅ 演示数据生成完成');
        } catch (error) {
            console.error('❌ 生成演示数据时出错:', error);
            detectionSystem.logError('❌ 演示数据生成失败: ' + error.message);
        }
    }

    /**
     * 重置所有演示数据
     */
    resetAllDemoData() {
        if (!window.detectionSystem) {
            console.error('❌ 检测系统未初始化');
            return;
        }

        console.log('🗑️ 清除演示数据...');
        detectionSystem.logWarning('🗑️ 清除所有演示数据');

        try {
            // 清除历史记录
            detectionSystem.testHistory = [];
            localStorage.removeItem('detectionHistory');
            
            // 清除性能基线
            detectionSystem.performanceBaseline.clear();
            localStorage.removeItem('performanceBaseline');
            
            // 清除测试结果
            detectionSystem.testResults.clear();
            detectionSystem.clearResults();
            
            console.log('✅ 演示数据已清除');
            detectionSystem.logSuccess('✅ 演示数据清除完成');
        } catch (error) {
            console.error('❌ 清除演示数据时出错:', error);
            detectionSystem.logError('❌ 演示数据清除失败: ' + error.message);
        }
    }
}

// 创建全局演示实例
window.enhancedDemo = new EnhancedDetectionDemo();
window.demo = window.enhancedDemo; // 简短别名

// 在页面加载完成后初始化演示系统 - 已禁用自动显示菜单
// 使用 enhancedDemo.showDemoMenu() 手动显示演示菜单
if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            console.log('🎬 AlingAi集成检测系统高级功能演示已就绪！');
            console.log('💡 使用 enhancedDemo.showDemoMenu() 显示演示菜单');
            console.log('💡 使用 demo.showHelp() 查看命令');
            
            if (window.detectionSystem) {
                detectionSystem.logInfo('🎭 高级功能演示系统已加载 (自动演示已禁用)');
                detectionSystem.logInfo('💡 使用 enhancedDemo.showDemoMenu() 手动启动演示');
            }
        }, 1000);
    });
}

/*
// 原自动显示代码已禁用:
if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            // 显示演示菜单
            if (window.enhancedDemo) {
                enhancedDemo.showDemoMenu();
            }
            
            console.log('🎬 AlingAi集成检测系统高级功能演示已就绪！');
            console.log('💡 使用页面上的演示菜单或输入 demo.showHelp() 查看命令');
            
            if (window.detectionSystem) {
                detectionSystem.logInfo('🎭 高级功能演示系统已加载');
                detectionSystem.logInfo('💡 使用演示菜单体验所有新功能');
                demo.showFeatures();
            }
        }, 3000);
    });
}
*/
