/**
 * AlingAi 集成检测系统 - 智能预警系统
 * 基于AI驱动的预测、智能阈值、模式识别的高级预警功能
 * 
 * 功能特性:
 * - AI驱动的异常预测
 * - 动态智能阈值设置
 * - 历史数据模式识别
 * - 多级预警机制
 * - 预测性维护建议
 * - 自适应学习算法
 * - 风险评估和预测
 * - 智能通知策略
 */

class IntelligentAlertSystem {
    constructor() {
        this.isInitialized = false;
        this.predictionModels = new Map();
        this.alertRules = new Map();
        this.learningData = [];
        this.thresholds = new Map();
        this.patterns = new Map();
        this.alertHistory = [];
        this.riskAssessment = new Map();
        this.predictionAccuracy = new Map();
        
        // 智能阈值配置
        this.intelligentThresholds = {
            performance: { min: 100, max: 5000, adaptive: true },
            errorRate: { min: 0, max: 0.1, adaptive: true },
            responseTime: { min: 50, max: 3000, adaptive: true },
            memoryUsage: { min: 0, max: 0.8, adaptive: true },
            cpuUsage: { min: 0, max: 0.9, adaptive: true }
        };
        
        // 预警级别定义
        this.alertLevels = {
            INFO: { priority: 1, color: '#17a2b8', icon: 'info-circle' },
            WARNING: { priority: 2, color: '#ffc107', icon: 'exclamation-triangle' },
            ERROR: { priority: 3, color: '#fd7e14', icon: 'exclamation-circle' },
            CRITICAL: { priority: 4, color: '#dc3545', icon: 'x-octagon' },
            EMERGENCY: { priority: 5, color: '#6f42c1', icon: 'lightning' }
        };
        
        // 机器学习模型参数
        this.mlConfig = {
            windowSize: 50,
            predictionHorizon: 10,
            minDataPoints: 20,
            confidenceThreshold: 0.7,
            learningRate: 0.01
        };
        
        this.init();
    }

    async init() {
        try {
            
            
            await this.loadHistoricalData();
            await this.initializePredictionModels();
            await this.setupAlertRules();
            await this.createUI();
            
            this.isInitialized = true;
            
            
            // 启动实时监控
            this.startRealTimeMonitoring();
            
        } catch (error) {
            console.error('❌ 智能预警系统初始化失败:', error);
        }
    }

    async loadHistoricalData() {
        // 从localStorage或API加载历史数据
        const historicalData = localStorage.getItem('intelligentAlertHistory');
        if (historicalData) {
            this.learningData = JSON.parse(historicalData);
        }
        
        // 加载性能基准数据
        const performanceData = localStorage.getItem('detectionPerformanceBaseline');
        if (performanceData) {
            const baseline = JSON.parse(performanceData);
            this.updateLearningData(baseline);
        }
        
        
    }

    async initializePredictionModels() {
        // 初始化各种预测模型
        const modelTypes = ['performance', 'errorRate', 'systemHealth', 'userBehavior'];
        
        for (const type of modelTypes) {
            this.predictionModels.set(type, {
                type: type,
                weights: this.initializeWeights(5),
                bias: 0,
                accuracy: 0,
                lastTrained: new Date(),
                predictionCount: 0,
                successCount: 0
            });
        }
        
        // 如果有足够的历史数据，训练模型
        if (this.learningData.length >= this.mlConfig.minDataPoints) {
            await this.trainModels();
        }
    }

    initializeWeights(size) {
        return Array.from({ length: size }, () => Math.random() * 2 - 1);
    }

    async trainModels() {
        
        
        for (const [modelType, model] of this.predictionModels) {
            const trainingData = this.prepareTrainingData(modelType);
            if (trainingData.length >= this.mlConfig.minDataPoints) {
                await this.trainModel(model, trainingData);
                console.log(`✅ ${modelType} 模型训练完成，准确率: ${(model.accuracy * 100).toFixed(2)}%`);
            }
        }
    }

    prepareTrainingData(modelType) {
        return this.learningData
            .filter(data => data.type === modelType)
            .slice(-100) // 使用最近100条数据
            .map(data => ({
                features: this.extractFeatures(data),
                target: data.target || 0
            }));
    }

    extractFeatures(data) {
        // 特征工程：从原始数据中提取有用特征
        const features = [];
        
        if (data.timestamp) {
            const time = new Date(data.timestamp);
            features.push(
                time.getHours() / 24,      // 时间特征
                time.getDay() / 7,         // 星期特征
                Math.sin(2 * Math.PI * time.getHours() / 24), // 周期性特征
                Math.cos(2 * Math.PI * time.getHours() / 24)
            );
        }
        
        if (data.metrics) {
            features.push(
                data.metrics.responseTime || 0,
                data.metrics.errorRate || 0,
                data.metrics.memoryUsage || 0,
                data.metrics.cpuUsage || 0
            );
        }
        
        return features.slice(0, 5); // 限制特征数量
    }

    async trainModel(model, trainingData) {
        // 简化的线性回归训练
        const { learningRate } = this.mlConfig;
        const epochs = 100;
        
        for (let epoch = 0; epoch < epochs; epoch++) {
            let totalError = 0;
            
            for (const { features, target } of trainingData) {
                const prediction = this.predict(model, features);
                const error = target - prediction;
                totalError += error * error;
                
                // 梯度下降更新权重
                for (let i = 0; i < model.weights.length && i < features.length; i++) {
                    model.weights[i] += learningRate * error * features[i];
                }
                model.bias += learningRate * error;
            }
            
            // 计算准确率
            if (epoch === epochs - 1) {
                model.accuracy = Math.max(0, 1 - Math.sqrt(totalError / trainingData.length));
            }
        }
        
        model.lastTrained = new Date();
    }

    predict(model, features) {
        let prediction = model.bias;
        for (let i = 0; i < model.weights.length && i < features.length; i++) {
            prediction += model.weights[i] * features[i];
        }
        return prediction;
    }

    async setupAlertRules() {
        // 设置基础预警规则
        this.alertRules.set('highErrorRate', {
            name: '错误率异常',
            condition: (data) => data.errorRate > this.getAdaptiveThreshold('errorRate'),
            level: 'ERROR',
            prediction: true,
            enabled: true
        });
        
        this.alertRules.set('slowResponse', {
            name: '响应时间异常',
            condition: (data) => data.responseTime > this.getAdaptiveThreshold('responseTime'),
            level: 'WARNING',
            prediction: true,
            enabled: true
        });
        
        this.alertRules.set('memoryLeak', {
            name: '内存泄漏风险',
            condition: (data) => this.detectMemoryLeak(data),
            level: 'CRITICAL',
            prediction: true,
            enabled: true
        });
        
        this.alertRules.set('systemOverload', {
            name: '系统过载预警',
            condition: (data) => this.detectSystemOverload(data),
            level: 'EMERGENCY',
            prediction: true,
            enabled: true
        });
        
        this.alertRules.set('abnormalPattern', {
            name: '异常模式检测',
            condition: (data) => this.detectAbnormalPattern(data),
            level: 'WARNING',
            prediction: true,
            enabled: true
        });
    }

    getAdaptiveThreshold(metric) {
        const threshold = this.thresholds.get(metric);
        if (!threshold) {
            return this.intelligentThresholds[metric]?.max || 1000;
        }
        
        // 基于历史数据和当前趋势动态调整阈值
        const recentData = this.learningData
            .filter(d => d.type === metric)
            .slice(-20);
            
        if (recentData.length < 5) {
            return threshold.value;
        }
        
        const mean = recentData.reduce((sum, d) => sum + (d.value || 0), 0) / recentData.length;
        const std = Math.sqrt(
            recentData.reduce((sum, d) => sum + Math.pow((d.value || 0) - mean, 2), 0) / recentData.length
        );
        
        // 动态阈值 = 均值 + 2*标准差（约95%置信区间）
        const adaptiveThreshold = mean + 2 * std;
        
        this.thresholds.set(metric, {
            value: adaptiveThreshold,
            mean: mean,
            std: std,
            lastUpdated: new Date()
        });
        
        return adaptiveThreshold;
    }

    detectMemoryLeak(data) {
        const recentMemoryData = this.learningData
            .filter(d => d.type === 'memoryUsage')
            .slice(-10);
            
        if (recentMemoryData.length < 5) return false;
        
        // 检查内存使用是否持续增长
        let increasingCount = 0;
        for (let i = 1; i < recentMemoryData.length; i++) {
            if (recentMemoryData[i].value > recentMemoryData[i-1].value) {
                increasingCount++;
            }
        }
        
        return increasingCount >= recentMemoryData.length * 0.8;
    }

    detectSystemOverload(data) {
        const metrics = ['cpuUsage', 'memoryUsage', 'responseTime'];
        let overloadScore = 0;
        
        for (const metric of metrics) {
            const threshold = this.getAdaptiveThreshold(metric);
            const currentValue = data[metric] || 0;
            
            if (currentValue > threshold) {
                overloadScore += currentValue / threshold;
            }
        }
        
        return overloadScore > 2; // 多个指标同时超阈值
    }

    detectAbnormalPattern(data) {
        // 使用时间序列异常检测
        const recentData = this.learningData.slice(-this.mlConfig.windowSize);
        if (recentData.length < this.mlConfig.minDataPoints) return false;
        
        // 计算当前数据点与历史模式的偏差
        const features = this.extractFeatures(data);
        const model = this.predictionModels.get('performance');
        
        if (!model || features.length === 0) return false;
        
        const prediction = this.predict(model, features);
        const actual = data.responseTime || 0;
        const deviation = Math.abs(actual - prediction);
        
        // 如果偏差超过历史标准差的3倍，认为是异常
        const threshold = this.getAdaptiveThreshold('performance');
        return deviation > threshold * 0.5;
    }

    async processRealTimeData(data) {
        // 添加到学习数据
        this.updateLearningData(data);
        
        // 执行预测
        const predictions = await this.makePredictions(data);
        
        // 检查预警规则
        const alerts = this.checkAlertRules(data, predictions);
        
        // 处理触发的预警
        for (const alert of alerts) {
            await this.handleAlert(alert);
        }
        
        // 更新风险评估
        this.updateRiskAssessment(data, predictions);
        
        return {
            predictions,
            alerts,
            riskLevel: this.calculateOverallRisk()
        };
    }

    updateLearningData(data) {
        const enrichedData = {
            ...data,
            timestamp: data.timestamp || new Date().toISOString(),
            features: this.extractFeatures(data)
        };
        
        this.learningData.push(enrichedData);
        
        // 保持数据量在合理范围内
        if (this.learningData.length > 1000) {
            this.learningData = this.learningData.slice(-800);
        }
        
        // 定期保存到localStorage
        if (this.learningData.length % 10 === 0) {
            localStorage.setItem('intelligentAlertHistory', JSON.stringify(this.learningData));
        }
    }

    async makePredictions(currentData) {
        const predictions = {};
        
        for (const [modelType, model] of this.predictionModels) {
            if (model.accuracy < this.mlConfig.confidenceThreshold) {
                continue; // 跳过准确率低的模型
            }
            
            const features = this.extractFeatures(currentData);
            if (features.length === 0) continue;
            
            const prediction = this.predict(model, features);
            predictions[modelType] = {
                value: prediction,
                confidence: model.accuracy,
                horizon: this.mlConfig.predictionHorizon,
                timestamp: new Date().toISOString()
            };
        }
        
        return predictions;
    }

    checkAlertRules(data, predictions) {
        const alerts = [];
        
        for (const [ruleId, rule] of this.alertRules) {
            if (!rule.enabled) continue;
            
            let shouldAlert = false;
            let alertData = { ...data };
            
            if (rule.prediction && predictions) {
                // 基于预测的预警
                for (const [predType, pred] of Object.entries(predictions)) {
                    alertData[predType + '_predicted'] = pred.value;
                }
            }
            
            try {
                shouldAlert = rule.condition(alertData);
            } catch (error) {
                console.error(`预警规则 ${ruleId} 执行错误:`, error);
                continue;
            }
            
            if (shouldAlert) {
                const alert = {
                    id: `alert_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
                    ruleId: ruleId,
                    name: rule.name,
                    level: rule.level,
                    timestamp: new Date().toISOString(),
                    data: alertData,
                    predictions: predictions,
                    suggestion: this.generateSuggestion(ruleId, alertData)
                };
                
                alerts.push(alert);
            }
        }
        
        return alerts;
    }

    generateSuggestion(ruleId, data) {
        const suggestions = {
            'highErrorRate': '建议检查错误日志，可能需要重启相关服务或修复代码问题',
            'slowResponse': '建议检查网络连接和服务器负载，考虑优化数据库查询或增加缓存',
            'memoryLeak': '建议立即检查内存使用情况，可能需要重启应用或修复内存泄漏',
            'systemOverload': '建议立即减少系统负载，考虑扩容或优化资源分配',
            'abnormalPattern': '检测到异常模式，建议深入分析系统行为变化原因'
        };
        
        const baseSuggestion = suggestions[ruleId] || '建议进一步监控和分析';
        
        // 基于具体数据生成更详细的建议
        const detailedSuggestions = [];
        
        if (data.errorRate > 0.05) {
            detailedSuggestions.push('错误率较高，优先检查近期代码变更');
        }
        
        if (data.responseTime > 2000) {
            detailedSuggestions.push('响应时间过长，建议优化数据库查询和API调用');
        }
        
        if (data.memoryUsage > 0.8) {
            detailedSuggestions.push('内存使用率过高，建议检查内存泄漏并考虑增加内存');
        }
        
        return {
            primary: baseSuggestion,
            detailed: detailedSuggestions,
            priority: this.alertLevels[this.alertRules.get(ruleId)?.level || 'INFO'].priority
        };
    }

    async handleAlert(alert) {
        // 添加到预警历史
        this.alertHistory.unshift(alert);
        
        // 保持历史记录在合理范围内
        if (this.alertHistory.length > 100) {
            this.alertHistory = this.alertHistory.slice(0, 50);
        }
        
        // 发送通知
        if (window.NotificationSystem) {
            const level = alert.level.toLowerCase();
            const message = `${alert.name}: ${alert.suggestion.primary}`;
            
            if (level === 'emergency' || level === 'critical') {
                window.NotificationSystem.error(message, {
                    persistent: true,
                    sound: true
                });
            } else if (level === 'error') {
                window.NotificationSystem.error(message);
            } else if (level === 'warning') {
                window.NotificationSystem.warning(message);
            } else {
                window.NotificationSystem.info(message);
            }
        }
        
        // 更新UI显示
        this.updateAlertUI(alert);
        
        // 记录预警事件
        console.log(`🚨 智能预警触发: ${alert.name} (${alert.level})`);
    }

    updateRiskAssessment(data, predictions) {
        const riskFactors = {
            performance: this.calculatePerformanceRisk(data),
            reliability: this.calculateReliabilityRisk(data),
            security: this.calculateSecurityRisk(data),
            scalability: this.calculateScalabilityRisk(data),
            maintenance: this.calculateMaintenanceRisk(data)
        };
        
        const overallRisk = Object.values(riskFactors).reduce((sum, risk) => sum + risk, 0) / 5;
        
        this.riskAssessment.set('current', {
            overall: overallRisk,
            factors: riskFactors,
            timestamp: new Date().toISOString(),
            predictions: predictions
        });
        
        // 如果风险级别发生显著变化，发出预警
        const previousRisk = this.riskAssessment.get('previous');
        if (previousRisk && Math.abs(overallRisk - previousRisk.overall) > 0.2) {
            this.handleRiskLevelChange(overallRisk, previousRisk.overall);
        }
        
        this.riskAssessment.set('previous', this.riskAssessment.get('current'));
    }

    calculatePerformanceRisk(data) {
        let risk = 0;
        
        if (data.responseTime > 1000) risk += 0.3;
        if (data.errorRate > 0.02) risk += 0.4;
        if (data.cpuUsage > 0.8) risk += 0.3;
        
        return Math.min(risk, 1);
    }

    calculateReliabilityRisk(data) {
        const recentErrors = this.learningData
            .filter(d => d.type === 'error')
            .slice(-10);
            
        return Math.min(recentErrors.length / 10, 1);
    }

    calculateSecurityRisk(data) {
        // 简化的安全风险评估
        let risk = 0;
        
        if (data.failedLogins > 10) risk += 0.5;
        if (data.suspiciousActivity) risk += 0.3;
        if (data.vulnerabilityScore > 7) risk += 0.2;
        
        return Math.min(risk, 1);
    }

    calculateScalabilityRisk(data) {
        let risk = 0;
        
        if (data.concurrentUsers > 100) risk += 0.2;
        if (data.memoryUsage > 0.7) risk += 0.3;
        if (data.diskUsage > 0.8) risk += 0.3;
        if (data.networkUtilization > 0.8) risk += 0.2;
        
        return Math.min(risk, 1);
    }

    calculateMaintenanceRisk(data) {
        const daysSinceLastUpdate = data.daysSinceLastUpdate || 0;
        const codeComplexity = data.codeComplexity || 0;
        
        let risk = 0;
        if (daysSinceLastUpdate > 30) risk += 0.3;
        if (codeComplexity > 10) risk += 0.4;
        
        return Math.min(risk, 1);
    }

    calculateOverallRisk() {
        const current = this.riskAssessment.get('current');
        return current ? current.overall : 0;
    }

    handleRiskLevelChange(newRisk, oldRisk) {
        const change = newRisk - oldRisk;
        const changeText = change > 0 ? '上升' : '下降';
        const level = newRisk > 0.7 ? 'CRITICAL' : newRisk > 0.4 ? 'WARNING' : 'INFO';
        
        const alert = {
            id: `risk_change_${Date.now()}`,
            ruleId: 'riskLevelChange',
            name: `系统风险级别${changeText}`,
            level: level,
            timestamp: new Date().toISOString(),
            data: { newRisk, oldRisk, change },
            suggestion: {
                primary: `系统整体风险${changeText}至 ${(newRisk * 100).toFixed(1)}%，建议${newRisk > 0.5 ? '立即' : '密切'}关注`,
                detailed: [`风险变化: ${(change * 100).toFixed(1)}%`],
                priority: this.alertLevels[level].priority
            }
        };
        
        this.handleAlert(alert);
    }

    startRealTimeMonitoring() {
        // 定期收集和分析数据
        setInterval(() => {
            this.collectSystemMetrics();
        }, 30000); // 每30秒
        
        // 定期重新训练模型
        setInterval(() => {
            if (this.learningData.length >= this.mlConfig.minDataPoints) {
                this.trainModels();
            }
        }, 300000); // 每5分钟
        
        
    }

    async collectSystemMetrics() {
        try {
            const metrics = {
                timestamp: new Date().toISOString(),
                responseTime: this.measureResponseTime(),
                memoryUsage: this.getMemoryUsage(),
                cpuUsage: this.getCPUUsage(),
                errorRate: this.calculateErrorRate(),
                activeUsers: this.getActiveUsers(),
                type: 'systemMetrics'
            };
            
            await this.processRealTimeData(metrics);
            
        } catch (error) {
            console.error('系统指标收集失败:', error);
        }
    }

    measureResponseTime() {
        // 模拟测量响应时间
        return Math.random() * 1000 + 200;
    }

    getMemoryUsage() {
        // 模拟获取内存使用率
        if (performance.memory) {
            return performance.memory.usedJSHeapSize / performance.memory.totalJSHeapSize;
        }
        return Math.random() * 0.8;
    }

    getCPUUsage() {
        // 模拟CPU使用率（浏览器环境限制）
        return Math.random() * 0.6;
    }

    calculateErrorRate() {
        // 从最近的检测记录中计算错误率
        const recentTests = this.learningData
            .filter(d => d.type === 'testResult')
            .slice(-20);
            
        if (recentTests.length === 0) return 0;
        
        const errorCount = recentTests.filter(t => t.status === 'error').length;
        return errorCount / recentTests.length;
    }

    getActiveUsers() {
        // 模拟活跃用户数
        return Math.floor(Math.random() * 50) + 1;
    }

    async createUI() {
        // 创建智能预警系统的UI界面
        const modalHTML = `
            <div class="modal fade" id="intelligentAlertModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content bg-dark text-light">
                        <div class="modal-header border-secondary">
                            <h5 class="modal-title">
                                <i class="bi bi-brain"></i> 智能预警系统
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <!-- 导航标签 -->
                            <ul class="nav nav-tabs mb-3" id="alertTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" 
                                            data-bs-target="#overview" type="button" role="tab">
                                        <i class="bi bi-speedometer2"></i> 总览
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="predictions-tab" data-bs-toggle="tab" 
                                            data-bs-target="#predictions" type="button" role="tab">
                                        <i class="bi bi-graph-up-arrow"></i> 预测分析
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="alerts-tab" data-bs-toggle="tab" 
                                            data-bs-target="#alerts" type="button" role="tab">
                                        <i class="bi bi-exclamation-triangle"></i> 预警记录
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="rules-tab" data-bs-toggle="tab" 
                                            data-bs-target="#rules" type="button" role="tab">
                                        <i class="bi bi-gear"></i> 规则配置
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="models-tab" data-bs-toggle="tab" 
                                            data-bs-target="#models" type="button" role="tab">
                                        <i class="bi bi-cpu"></i> AI模型
                                    </button>
                                </li>
                            </ul>

                            <!-- 标签内容 -->
                            <div class="tab-content" id="alertTabContent">
                                <!-- 总览标签 -->
                                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <div class="card bg-secondary">
                                                <div class="card-body text-center">
                                                    <h5 class="card-title">整体风险</h5>
                                                    <div class="display-4" id="overallRiskValue">--</div>
                                                    <div class="risk-indicator" id="riskIndicator"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-secondary">
                                                <div class="card-body text-center">
                                                    <h5 class="card-title">活跃预警</h5>
                                                    <div class="display-4 text-warning" id="activeAlertsCount">0</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-secondary">
                                                <div class="card-body text-center">
                                                    <h5 class="card-title">预测准确率</h5>
                                                    <div class="display-4 text-info" id="predictionAccuracy">--</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-secondary">
                                                <div class="card-body text-center">
                                                    <h5 class="card-title">学习数据量</h5>
                                                    <div class="display-4 text-success" id="learningDataCount">0</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card bg-secondary">
                                                <div class="card-header">
                                                    <h6 class="mb-0">风险分解</h6>
                                                </div>
                                                <div class="card-body">
                                                    <canvas id="riskBreakdownChart" width="400" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card bg-secondary">
                                                <div class="card-header">
                                                    <h6 class="mb-0">预警趋势</h6>
                                                </div>
                                                <div class="card-body">
                                                    <canvas id="alertTrendChart" width="400" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 预测分析标签 -->
                                <div class="tab-pane fade" id="predictions" role="tabpanel">
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="card bg-secondary">
                                                <div class="card-header">
                                                    <h6 class="mb-0">实时预测</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div id="predictionsList"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card bg-secondary">
                                                <div class="card-header">
                                                    <h6 class="mb-0">预测vs实际</h6>
                                                </div>
                                                <div class="card-body">
                                                    <canvas id="predictionAccuracyChart" width="400" height="300"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 预警记录标签 -->
                                <div class="tab-pane fade" id="alerts" role="tabpanel">
                                    <div class="mb-3">
                                        <button class="btn btn-sm btn-outline-light" onclick="clearAlertHistory()">
                                            <i class="bi bi-trash"></i> 清空历史
                                        </button>
                                        <button class="btn btn-sm btn-outline-light" onclick="exportAlertHistory()">
                                            <i class="bi bi-download"></i> 导出记录
                                        </button>
                                    </div>
                                    <div id="alertHistoryList"></div>
                                </div>

                                <!-- 规则配置标签 -->
                                <div class="tab-pane fade" id="rules" role="tabpanel">
                                    <div class="mb-3">
                                        <button class="btn btn-sm btn-success" onclick="addAlertRule()">
                                            <i class="bi bi-plus"></i> 添加规则
                                        </button>
                                        <button class="btn btn-sm btn-outline-light" onclick="resetAlertRules()">
                                            <i class="bi bi-arrow-clockwise"></i> 重置规则
                                        </button>
                                    </div>
                                    <div id="alertRulesList"></div>
                                </div>

                                <!-- AI模型标签 -->
                                <div class="tab-pane fade" id="models" role="tabpanel">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <button class="btn btn-sm btn-primary" onclick="retrainModels()">
                                                <i class="bi bi-arrow-clockwise"></i> 重新训练模型
                                            </button>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <small class="text-muted">最后训练: <span id="lastTrainingTime">--</span></small>
                                        </div>
                                    </div>
                                    <div id="modelsList"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-secondary">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                            <button type="button" class="btn btn-primary" onclick="refreshIntelligentAlerts()">
                                <i class="bi bi-arrow-clockwise"></i> 刷新数据
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // 添加到页面
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        
    }

    show() {
        if (!this.isInitialized) {
            console.error('智能预警系统尚未初始化');
            return;
        }

        this.updateUI();
        const modal = new bootstrap.Modal(document.getElementById('intelligentAlertModal'));
        modal.show();
    }

    updateUI() {
        this.updateOverviewTab();
        this.updatePredictionsTab();
        this.updateAlertsTab();
        this.updateRulesTab();
        this.updateModelsTab();
    }

    updateOverviewTab() {
        // 更新整体风险
        const currentRisk = this.calculateOverallRisk();
        const riskElement = document.getElementById('overallRiskValue');
        const indicatorElement = document.getElementById('riskIndicator');
        
        if (riskElement) {
            riskElement.textContent = (currentRisk * 100).toFixed(1) + '%';
            riskElement.className = `display-4 ${this.getRiskColorClass(currentRisk)}`;
        }
        
        if (indicatorElement) {
            indicatorElement.innerHTML = this.createRiskIndicator(currentRisk);
        }
        
        // 更新统计数据
        this.updateElement('activeAlertsCount', this.alertHistory.filter(a => 
            new Date() - new Date(a.timestamp) < 3600000 // 1小时内的预警
        ).length);
        
        this.updateElement('learningDataCount', this.learningData.length);
        
        // 更新预测准确率
        const avgAccuracy = Array.from(this.predictionModels.values())
            .reduce((sum, model) => sum + model.accuracy, 0) / this.predictionModels.size;
        this.updateElement('predictionAccuracy', (avgAccuracy * 100).toFixed(1) + '%');
        
        // 更新图表
        this.updateRiskBreakdownChart();
        this.updateAlertTrendChart();
    }

    getRiskColorClass(risk) {
        if (risk > 0.8) return 'text-danger';
        if (risk > 0.6) return 'text-warning';
        if (risk > 0.3) return 'text-info';
        return 'text-success';
    }

    createRiskIndicator(risk) {
        const bars = Math.ceil(risk * 10);
        let html = '<div class="risk-bars d-flex">';
        
        for (let i = 1; i <= 10; i++) {
            const colorClass = i <= 3 ? 'bg-success' : i <= 6 ? 'bg-warning' : 'bg-danger';
            const active = i <= bars ? colorClass : 'bg-secondary';
            html += `<div class="risk-bar ${active}" style="width: 8px; height: 20px; margin: 1px;"></div>`;
        }
        
        html += '</div>';
        return html;
    }

    updateElement(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    }

    updateRiskBreakdownChart() {
        const canvas = document.getElementById('riskBreakdownChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        const currentRisk = this.riskAssessment.get('current');
        
        if (!currentRisk) return;
        
        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: ['性能', '可靠性', '安全', '可扩展性', '维护'],
                datasets: [{
                    label: '风险级别',
                    data: Object.values(currentRisk.factors),
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: { color: 'white' }
                    }
                },
                scales: {
                    r: {
                        angleLines: { color: 'rgba(255, 255, 255, 0.3)' },
                        grid: { color: 'rgba(255, 255, 255, 0.3)' },
                        pointLabels: { color: 'white' },
                        ticks: { 
                            color: 'white',
                            beginAtZero: true,
                            max: 1
                        }
                    }
                }
            }
        });
    }

    updateAlertTrendChart() {
        const canvas = document.getElementById('alertTrendChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        
        // 准备24小时内的预警趋势数据
        const hours = Array.from({ length: 24 }, (_, i) => {
            const hour = new Date();
            hour.setHours(hour.getHours() - (23 - i));
            return hour.getHours();
        });
        
        const alertCounts = hours.map(hour => {
            return this.alertHistory.filter(alert => {
                const alertTime = new Date(alert.timestamp);
                return alertTime.getHours() === hour && 
                       new Date() - alertTime < 24 * 3600000;
            }).length;
        });
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: hours.map(h => h + ':00'),
                datasets: [{
                    label: '预警数量',
                    data: alertCounts,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: { color: 'white' }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: 'white' },
                        grid: { color: 'rgba(255, 255, 255, 0.3)' }
                    },
                    y: {
                        ticks: { color: 'white' },
                        grid: { color: 'rgba(255, 255, 255, 0.3)' },
                        beginAtZero: true
                    }
                }
            }
        });
    }

    updatePredictionsTab() {
        const container = document.getElementById('predictionsList');
        if (!container) return;
        
        let html = '';
        
        for (const [modelType, model] of this.predictionModels) {
            if (model.accuracy < 0.1) continue;
            
            const features = this.extractFeatures({ 
                timestamp: new Date().toISOString(),
                type: 'current'
            });
            
            if (features.length === 0) continue;
            
            const prediction = this.predict(model, features);
            
            html += `
                <div class="prediction-item border rounded p-3 mb-2" style="background: rgba(255,255,255,0.05);">
                    <div class="row">
                        <div class="col-md-8">
                            <h6>${this.getModelDisplayName(modelType)}</h6>
                            <div class="prediction-value">
                                预测值: <span class="badge bg-info">${prediction.toFixed(2)}</span>
                                置信度: <span class="badge bg-success">${(model.accuracy * 100).toFixed(1)}%</span>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <small class="text-muted">
                                最后训练: ${model.lastTrained.toLocaleTimeString()}<br>
                                预测次数: ${model.predictionCount}
                            </small>
                        </div>
                    </div>
                </div>
            `;
        }
        
        container.innerHTML = html || '<p class="text-muted">暂无可用预测</p>';
    }

    getModelDisplayName(modelType) {
        const names = {
            'performance': '性能预测',
            'errorRate': '错误率预测',
            'systemHealth': '系统健康度',
            'userBehavior': '用户行为模式'
        };
        return names[modelType] || modelType;
    }

    updateAlertsTab() {
        const container = document.getElementById('alertHistoryList');
        if (!container) return;
        
        let html = '';
        
        for (const alert of this.alertHistory.slice(0, 20)) {
            const levelConfig = this.alertLevels[alert.level] || this.alertLevels.INFO;
            const timeAgo = this.getTimeAgo(alert.timestamp);
            
            html += `
                <div class="alert-item border rounded p-3 mb-2" style="border-left: 4px solid ${levelConfig.color} !important; background: rgba(255,255,255,0.05);">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-${levelConfig.icon} me-2" style="color: ${levelConfig.color};"></i>
                                <strong>${alert.name}</strong>
                                <span class="badge ms-2" style="background: ${levelConfig.color};">${alert.level}</span>
                            </div>
                            <p class="mb-1">${alert.suggestion.primary}</p>
                            ${alert.suggestion.detailed.length > 0 ? 
                                `<ul class="small text-muted mb-0">
                                    ${alert.suggestion.detailed.map(d => `<li>${d}</li>`).join('')}
                                </ul>` : ''
                            }
                        </div>
                        <div class="col-md-4 text-end">
                            <small class="text-muted">${timeAgo}</small>
                        </div>
                    </div>
                </div>
            `;
        }
        
        container.innerHTML = html || '<p class="text-muted">暂无预警记录</p>';
    }

    getTimeAgo(timestamp) {
        const now = new Date();
        const time = new Date(timestamp);
        const diffMs = now - time;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMins / 60);
        const diffDays = Math.floor(diffHours / 24);
        
        if (diffDays > 0) return `${diffDays}天前`;
        if (diffHours > 0) return `${diffHours}小时前`;
        if (diffMins > 0) return `${diffMins}分钟前`;
        return '刚刚';
    }

    updateRulesTab() {
        const container = document.getElementById('alertRulesList');
        if (!container) return;
        
        let html = '';
        
        for (const [ruleId, rule] of this.alertRules) {
            const levelConfig = this.alertLevels[rule.level] || this.alertLevels.INFO;
            
            html += `
                <div class="rule-item border rounded p-3 mb-2" style="background: rgba(255,255,255,0.05);">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-2">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="checkbox" 
                                           ${rule.enabled ? 'checked' : ''} 
                                           onchange="toggleAlertRule('${ruleId}')">
                                </div>
                                <strong>${rule.name}</strong>
                                <span class="badge ms-2" style="background: ${levelConfig.color};">${rule.level}</span>
                            </div>
                            <small class="text-muted">
                                规则ID: ${ruleId} | 
                                预测模式: ${rule.prediction ? '启用' : '禁用'}
                            </small>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-sm btn-outline-warning" onclick="editAlertRule('${ruleId}')">
                                <i class="bi bi-pencil"></i> 编辑
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteAlertRule('${ruleId}')">
                                <i class="bi bi-trash"></i> 删除
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }
        
        container.innerHTML = html;
    }

    updateModelsTab() {
        const container = document.getElementById('modelsList');
        if (!container) return;
        
        let html = '';
        
        for (const [modelType, model] of this.predictionModels) {
            const statusColor = model.accuracy > 0.7 ? 'success' : 
                               model.accuracy > 0.5 ? 'warning' : 'danger';
            
            html += `
                <div class="model-item border rounded p-3 mb-2" style="background: rgba(255,255,255,0.05);">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>${this.getModelDisplayName(modelType)}</h6>
                            <div class="model-stats">
                                <span class="badge bg-${statusColor}">准确率: ${(model.accuracy * 100).toFixed(1)}%</span>
                                <span class="badge bg-info ms-1">预测: ${model.predictionCount}</span>
                                <span class="badge bg-secondary ms-1">成功: ${model.successCount}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-end">
                                <small class="text-muted">
                                    最后训练: ${model.lastTrained.toLocaleString()}<br>
                                    权重数量: ${model.weights.length}
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-${statusColor}" 
                                     style="width: ${model.accuracy * 100}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        container.innerHTML = html;
        
        // 更新最后训练时间
        const lastTrainingElement = document.getElementById('lastTrainingTime');
        if (lastTrainingElement && this.predictionModels.size > 0) {
            const latestTraining = Math.max(
                ...Array.from(this.predictionModels.values()).map(m => m.lastTrained.getTime())
            );
            lastTrainingElement.textContent = new Date(latestTraining).toLocaleString();
        }
    }

    // 全局方法供UI调用
    toggleAlertRule(ruleId) {
        const rule = this.alertRules.get(ruleId);
        if (rule) {
            rule.enabled = !rule.enabled;
            
        }
    }

    async retrainModels() {
        if (this.learningData.length < this.mlConfig.minDataPoints) {
            alert(`需要至少 ${this.mlConfig.minDataPoints} 条学习数据才能训练模型，当前只有 ${this.learningData.length} 条`);
            return;
        }
        
        await this.trainModels();
        this.updateModelsTab();
        alert('模型重新训练完成！');
    }

    clearAlertHistory() {
        if (confirm('确定要清空所有预警历史记录吗？')) {
            this.alertHistory = [];
            this.updateAlertsTab();
        }
    }

    exportAlertHistory() {
        const data = {
            timestamp: new Date().toISOString(),
            totalAlerts: this.alertHistory.length,
            alerts: this.alertHistory,
            summary: this.generateAlertSummary()
        };
        
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `intelligent-alerts-${Date.now()}.json`;
        a.click();
        URL.revokeObjectURL(url);
    }

    generateAlertSummary() {
        const summary = {
            byLevel: {},
            byRule: {},
            timeRange: {
                earliest: null,
                latest: null
            }
        };
        
        for (const alert of this.alertHistory) {
            // 按级别统计
            summary.byLevel[alert.level] = (summary.byLevel[alert.level] || 0) + 1;
            
            // 按规则统计
            summary.byRule[alert.ruleId] = (summary.byRule[alert.ruleId] || 0) + 1;
            
            // 时间范围
            const alertTime = new Date(alert.timestamp);
            if (!summary.timeRange.earliest || alertTime < summary.timeRange.earliest) {
                summary.timeRange.earliest = alertTime;
            }
            if (!summary.timeRange.latest || alertTime > summary.timeRange.latest) {
                summary.timeRange.latest = alertTime;
            }
        }
        
        return summary;
    }
}

// 全局实例
window.IntelligentAlertSystem = null;

// 初始化函数
window.initializeIntelligentAlertSystem = async function() {
    if (!window.IntelligentAlertSystem) {
        window.IntelligentAlertSystem = new IntelligentAlertSystem();
        await window.IntelligentAlertSystem.init();
    }
    return window.IntelligentAlertSystem;
};

// 显示智能预警系统的全局函数
window.showIntelligentAlertSystem = function() {
    if (window.IntelligentAlertSystem && window.IntelligentAlertSystem.isInitialized) {
        window.IntelligentAlertSystem.show();
    } else {
        console.error('智能预警系统尚未初始化');
    }
};

// 全局方法供UI调用
window.toggleAlertRule = function(ruleId) {
    if (window.IntelligentAlertSystem) {
        window.IntelligentAlertSystem.toggleAlertRule(ruleId);
    }
};

window.retrainModels = function() {
    if (window.IntelligentAlertSystem) {
        window.IntelligentAlertSystem.retrainModels();
    }
};

window.clearAlertHistory = function() {
    if (window.IntelligentAlertSystem) {
        window.IntelligentAlertSystem.clearAlertHistory();
    }
};

window.exportAlertHistory = function() {
    if (window.IntelligentAlertSystem) {
        window.IntelligentAlertSystem.exportAlertHistory();
    }
};

window.refreshIntelligentAlerts = function() {
    if (window.IntelligentAlertSystem) {
        window.IntelligentAlertSystem.updateUI();
    }
};


