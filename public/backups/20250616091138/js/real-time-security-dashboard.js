/**
 * 实时网络安全监控与威胁可视化系统
 * 基于WebSocket的实时数据可视化
 */
class RealTimeSecurityDashboard {
    constructor() {
        this.isConnected = false;
        this.socket = null;
        this.threatData = [];
        this.statisticsData = {};
        this.geographicData = new Map();
        this.attackTimeline = [];
        
        // 3D可视化相关
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.earth = null;
        this.threatMarkers = [];
        this.attackLines = [];
        
        // 监控状态
        this.monitoringStartTime = Date.now();
        this.totalThreats = 0;
        this.blockedAttacks = 0;
        this.activeConnections = 0;
        
        this.init();
    }
    
    /**
     * 初始化系统
     */
    init() {
        console.log('🚀 初始化实时安全监控系统...');
        
        // 初始化WebSocket连接
        this.initWebSocket();
        
        // 初始化3D可视化
        this.init3DVisualization();
        
        // 初始化实时图表
        this.initRealTimeCharts();
        
        // 初始化控制面板
        this.initControlPanel();
        
        // 启动数据更新循环
        this.startDataUpdateLoop();
        
        console.log('✅ 实时安全监控系统初始化完成');
    }
    
    /**
     * 初始化WebSocket连接
     */
    initWebSocket() {
        try {
            // 连接到实时监控WebSocket服务器
            this.socket = new WebSocket('ws://localhost:8080/real-time-monitor');
            
            this.socket.onopen = () => {
                this.isConnected = true;
                console.log('🔗 WebSocket连接已建立');
                this.updateConnectionStatus('已连接', 'success');
            };
            
            this.socket.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    this.handleRealTimeData(data);
                } catch (error) {
                    console.error('解析WebSocket数据失败:', error);
                }
            };
            
            this.socket.onclose = () => {
                this.isConnected = false;
                console.log('❌ WebSocket连接已断开');
                this.updateConnectionStatus('已断开', 'error');
                
                // 尝试重连
                setTimeout(() => this.reconnectWebSocket(), 5000);
            };
            
            this.socket.onerror = (error) => {
                console.error('WebSocket连接错误:', error);
                this.updateConnectionStatus('连接错误', 'error');
            };
            
        } catch (error) {
            console.error('初始化WebSocket失败:', error);
            // 如果WebSocket不可用，使用HTTP轮询作为后备
            this.initHttpPolling();
        }
    }
    
    /**
     * 处理实时数据
     */
    handleRealTimeData(data) {
        switch (data.type) {
            case 'threat_detected':
                this.handleThreatDetection(data.data);
                break;
            case 'attack_blocked':
                this.handleAttackBlocked(data.data);
                break;
            case 'statistics_update':
                this.handleStatisticsUpdate(data.data);
                break;
            case 'system_status':
                this.handleSystemStatus(data.data);
                break;
            case 'geographic_update':
                this.handleGeographicUpdate(data.data);
                break;
            default:
                console.log('未知数据类型:', data.type);
        }
    }
    
    /**
     * 处理威胁检测
     */
    handleThreatDetection(threatData) {
        this.totalThreats++;
        this.threatData.push(threatData);
        
        // 更新3D可视化
        this.addThreatMarker(threatData);
        
        // 更新威胁列表
        this.updateThreatList(threatData);
        
        // 更新统计数据
        this.updateStatistics();
        
        // 播放警报音效
        this.playThreatSound(threatData.threat_level);
        
        // 显示实时通知
        this.showThreatNotification(threatData);
        
        console.log('🚨 检测到威胁:', threatData);
    }
    
    /**
     * 处理攻击阻止
     */
    handleAttackBlocked(attackData) {
        this.blockedAttacks++;
        
        // 更新被阻止攻击的可视化
        this.showBlockedAttack(attackData);
        
        // 更新统计
        this.updateStatistics();
        
        console.log('🛡️ 阻止攻击:', attackData);
    }
    
    /**
     * 初始化3D可视化
     */
    init3DVisualization() {
        const container = document.getElementById('threat-globe-container');
        if (!container) return;
        
        // 创建场景
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0x000011);
        
        // 创建相机
        this.camera = new THREE.PerspectiveCamera(
            75, 
            container.clientWidth / container.clientHeight, 
            0.1, 
            1000
        );
        this.camera.position.set(0, 0, 3);
        
        // 创建渲染器
        this.renderer = new THREE.WebGLRenderer({ antialias: true });
        this.renderer.setSize(container.clientWidth, container.clientHeight);
        this.renderer.shadowMap.enabled = true;
        this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        container.appendChild(this.renderer.domElement);
        
        // 创建地球
        this.createEarth();
        
        // 添加光照
        this.addLighting();
        
        // 添加控制器
        this.addControls();
        
        // 开始渲染循环
        this.startRenderLoop();
        
        // 响应式调整
        window.addEventListener('resize', () => this.onWindowResize());
    }
    
    /**
     * 创建地球
     */
    createEarth() {
        const geometry = new THREE.SphereGeometry(1, 64, 64);
        
        // 加载地球纹理
        const loader = new THREE.TextureLoader();
        const earthTexture = loader.load('/assets/textures/earth_daymap.jpg');
        const bumpTexture = loader.load('/assets/textures/earth_normal.jpg');
        
        const material = new THREE.MeshPhongMaterial({
            map: earthTexture,
            bumpMap: bumpTexture,
            bumpScale: 0.05,
            shininess: 100
        });
        
        this.earth = new THREE.Mesh(geometry, material);
        this.scene.add(this.earth);
        
        // 添加大气层效果
        this.addAtmosphere();
    }
    
    /**
     * 添加大气层效果
     */
    addAtmosphere() {
        const atmosphereGeometry = new THREE.SphereGeometry(1.05, 64, 64);
        const atmosphereMaterial = new THREE.MeshBasicMaterial({
            color: 0x00aaff,
            transparent: true,
            opacity: 0.1,
            side: THREE.BackSide
        });
        
        const atmosphere = new THREE.Mesh(atmosphereGeometry, atmosphereMaterial);
        this.scene.add(atmosphere);
    }
    
    /**
     * 添加威胁标记
     */
    addThreatMarker(threatData) {
        const { lat, lon } = this.getCoordinatesFromIP(threatData.ip);
        const position = this.latLonToVector3(lat, lon, 1.02);
        
        // 根据威胁级别选择颜色
        const color = this.getThreatColor(threatData.threat_level);
        
        // 创建威胁点
        const geometry = new THREE.SphereGeometry(0.02, 16, 16);
        const material = new THREE.MeshBasicMaterial({ 
            color: color,
            transparent: true,
            opacity: 0.9
        });
        
        const marker = new THREE.Mesh(geometry, material);
        marker.position.copy(position);
        marker.userData = threatData;
        
        this.scene.add(marker);
        this.threatMarkers.push(marker);
        
        // 添加脉冲效果
        this.addPulseEffect(marker, color);
        
        // 如果是攻击，添加攻击线
        if (threatData.target) {
            this.addAttackLine(threatData);
        }
        
        // 限制标记数量
        if (this.threatMarkers.length > 100) {
            const oldMarker = this.threatMarkers.shift();
            this.scene.remove(oldMarker);
        }
    }
    
    /**
     * 添加脉冲效果
     */
    addPulseEffect(marker, color) {
        const pulseGeometry = new THREE.RingGeometry(0.03, 0.08, 16);
        const pulseMaterial = new THREE.MeshBasicMaterial({
            color: color,
            transparent: true,
            opacity: 0.5,
            side: THREE.DoubleSide
        });
        
        const pulse = new THREE.Mesh(pulseGeometry, pulseMaterial);
        pulse.position.copy(marker.position);
        pulse.lookAt(new THREE.Vector3(0, 0, 0));
        
        this.scene.add(pulse);
        
        // 脉冲动画
        const animatePulse = () => {
            const scale = 1 + 0.5 * Math.sin(Date.now() * 0.01);
            pulse.scale.setScalar(scale);
            
            pulseMaterial.opacity = 0.5 * Math.abs(Math.sin(Date.now() * 0.005));
            
            if (this.threatMarkers.includes(marker)) {
                requestAnimationFrame(animatePulse);
            } else {
                this.scene.remove(pulse);
            }
        };
        animatePulse();
    }
    
    /**
     * 添加攻击线
     */
    addAttackLine(threatData) {
        const sourcePos = this.latLonToVector3(
            threatData.source_lat || 0, 
            threatData.source_lon || 0, 
            1.02
        );
        const targetPos = this.latLonToVector3(
            threatData.target_lat || 0, 
            threatData.target_lon || 0, 
            1.02
        );
        
        // 创建弧形路径
        const curve = new THREE.QuadraticBezierCurve3(
            sourcePos,
            sourcePos.clone().add(targetPos).multiplyScalar(0.7),
            targetPos
        );
        
        const points = curve.getPoints(50);
        const geometry = new THREE.BufferGeometry().setFromPoints(points);
        const material = new THREE.LineBasicMaterial({
            color: 0xff0000,
            transparent: true,
            opacity: 0.8
        });
        
        const line = new THREE.Line(geometry, material);
        this.scene.add(line);
        this.attackLines.push(line);
        
        // 动画效果
        this.animateAttackLine(line);
    }
    
    /**
     * 攻击线动画
     */
    animateAttackLine(line) {
        let opacity = 1;
        const fadeOut = () => {
            opacity -= 0.02;
            line.material.opacity = opacity;
            
            if (opacity > 0) {
                requestAnimationFrame(fadeOut);
            } else {
                this.scene.remove(line);
                const index = this.attackLines.indexOf(line);
                if (index > -1) {
                    this.attackLines.splice(index, 1);
                }
            }
        };
        
        setTimeout(fadeOut, 3000);
    }
    
    /**
     * 经纬度转3D坐标
     */
    latLonToVector3(lat, lon, radius) {
        const phi = (90 - lat) * (Math.PI / 180);
        const theta = (lon + 180) * (Math.PI / 180);
        
        const x = -(radius * Math.sin(phi) * Math.cos(theta));
        const z = (radius * Math.sin(phi) * Math.sin(theta));
        const y = (radius * Math.cos(phi));
        
        return new THREE.Vector3(x, y, z);
    }
    
    /**
     * 根据IP获取坐标
     */
    getCoordinatesFromIP(ip) {
        // 简化的IP地理位置映射
        const ipRanges = {
            '192.168': { lat: 39.9042, lon: 116.4074 }, // 北京
            '10.0': { lat: 31.2304, lon: 121.4737 },    // 上海
            '172.16': { lat: 22.3193, lon: 114.1694 },  // 香港
        };
        
        for (const [range, coords] of Object.entries(ipRanges)) {
            if (ip.startsWith(range)) {
                return coords;
            }
        }
        
        // 默认坐标（随机生成）
        return {
            lat: (Math.random() - 0.5) * 180,
            lon: (Math.random() - 0.5) * 360
        };
    }
    
    /**
     * 获取威胁颜色
     */
    getThreatColor(threatLevel) {
        if (threatLevel >= 90) return 0xff0000; // 红色 - 严重
        if (threatLevel >= 70) return 0xff8800; // 橙色 - 高
        if (threatLevel >= 50) return 0xffff00; // 黄色 - 中等
        return 0x00ff00; // 绿色 - 低
    }
    
    /**
     * 初始化实时图表
     */
    initRealTimeCharts() {
        // 威胁趋势图表
        this.initThreatTrendChart();
        
        // 攻击类型分布图
        this.initAttackTypeChart();
        
        // 地理分布图
        this.initGeographicChart();
        
        // 实时流量图
        this.initTrafficChart();
    }
    
    /**
     * 初始化威胁趋势图表
     */
    initThreatTrendChart() {
        const ctx = document.getElementById('threat-trend-chart');
        if (!ctx) return;
        
        this.threatTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: '威胁检测数量',
                    data: [],
                    borderColor: '#ff4444',
                    backgroundColor: 'rgba(255, 68, 68, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                animation: false,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'minute'
                        }
                    },
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    /**
     * 初始化攻击类型图表
     */
    initAttackTypeChart() {
        const ctx = document.getElementById('attack-type-chart');
        if (!ctx) return;
        
        this.attackTypeChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['SQL注入', 'XSS攻击', 'DDoS', '暴力破解', '恶意扫描', '其他'],
                datasets: [{
                    data: [0, 0, 0, 0, 0, 0],
                    backgroundColor: [
                        '#ff4444',
                        '#ff8844',
                        '#ffaa44',
                        '#44ff44',
                        '#4488ff',
                        '#8844ff'
                    ]
                }]
            },
            options: {
                responsive: true,
                animation: false
            }
        });
    }
    
    /**
     * 更新威胁列表
     */
    updateThreatList(threatData) {
        const threatList = document.getElementById('threat-list');
        if (!threatList) return;
        
        const threatItem = document.createElement('div');
        threatItem.className = `threat-item threat-level-${this.getThreatLevelName(threatData.threat_level)}`;
        
        threatItem.innerHTML = `
            <div class="threat-header">
                <span class="threat-ip">${threatData.ip}</span>
                <span class="threat-level">${threatData.threat_level}</span>
                <span class="threat-time">${new Date().toLocaleTimeString()}</span>
            </div>
            <div class="threat-details">
                <span class="threat-type">${threatData.threats.map(t => t.type).join(', ')}</span>
                <span class="threat-location">${threatData.location?.country || 'Unknown'}</span>
            </div>
            <div class="threat-actions">
                <button onclick="this.blockIP('${threatData.ip}')" class="btn-block">封禁</button>
                <button onclick="this.analyzeIP('${threatData.ip}')" class="btn-analyze">分析</button>
            </div>
        `;
        
        threatList.insertBefore(threatItem, threatList.firstChild);
        
        // 限制列表长度
        while (threatList.children.length > 50) {
            threatList.removeChild(threatList.lastChild);
        }
    }
    
    /**
     * 获取威胁级别名称
     */
    getThreatLevelName(level) {
        if (level >= 90) return 'critical';
        if (level >= 70) return 'high';
        if (level >= 50) return 'medium';
        return 'low';
    }
    
    /**
     * 更新统计数据
     */
    updateStatistics() {
        // 更新总威胁数
        document.getElementById('total-threats').textContent = this.totalThreats;
        
        // 更新被阻止攻击数
        document.getElementById('blocked-attacks').textContent = this.blockedAttacks;
        
        // 更新运行时间
        const uptime = Math.floor((Date.now() - this.monitoringStartTime) / 1000);
        document.getElementById('uptime').textContent = this.formatUptime(uptime);
        
        // 更新威胁率
        const threatRate = this.totalThreats / Math.max(uptime / 60, 1);
        document.getElementById('threat-rate').textContent = threatRate.toFixed(2) + '/分钟';
        
        // 更新图表
        this.updateCharts();
    }
    
    /**
     * 更新图表
     */
    updateCharts() {
        const now = new Date();
        
        // 更新威胁趋势图
        if (this.threatTrendChart) {
            this.threatTrendChart.data.labels.push(now);
            this.threatTrendChart.data.datasets[0].data.push(this.totalThreats);
            
            // 保持最近30个数据点
            if (this.threatTrendChart.data.labels.length > 30) {
                this.threatTrendChart.data.labels.shift();
                this.threatTrendChart.data.datasets[0].data.shift();
            }
            
            this.threatTrendChart.update('none');
        }
    }
    
    /**
     * 播放威胁警报音
     */
    playThreatSound(threatLevel) {
        if (!this.soundEnabled) return;
        
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        // 根据威胁级别设置频率
        let frequency = 440;
        if (threatLevel >= 90) frequency = 880; // 高音
        else if (threatLevel >= 70) frequency = 660;
        else if (threatLevel >= 50) frequency = 550;
        
        oscillator.frequency.setValueAtTime(frequency, audioContext.currentTime);
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0, audioContext.currentTime);
        gainNode.gain.linearRampToValueAtTime(0.3, audioContext.currentTime + 0.01);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.5);
    }
    
    /**
     * 显示威胁通知
     */
    showThreatNotification(threatData) {
        // 创建通知元素
        const notification = document.createElement('div');
        notification.className = `threat-notification threat-level-${this.getThreatLevelName(threatData.threat_level)}`;
        
        notification.innerHTML = `
            <div class="notification-icon">🚨</div>
            <div class="notification-content">
                <div class="notification-title">检测到威胁</div>
                <div class="notification-details">
                    IP: ${threatData.ip} | 
                    级别: ${threatData.threat_level} | 
                    类型: ${threatData.threats.map(t => t.type).join(', ')}
                </div>
            </div>
            <button class="notification-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        // 添加到通知容器
        const container = document.getElementById('notifications-container') || document.body;
        container.appendChild(notification);
        
        // 自动移除
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 10000);
    }
    
    /**
     * 格式化运行时间
     */
    formatUptime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
    
    /**
     * 启动数据更新循环
     */
    startDataUpdateLoop() {
        setInterval(() => {
            if (this.isConnected) {
                this.requestDataUpdate();
            } else {
                this.fetchDataViaHttp();
            }
        }, 5000); // 每5秒更新一次
    }
    
    /**
     * 请求数据更新
     */
    requestDataUpdate() {
        if (this.socket && this.socket.readyState === WebSocket.OPEN) {
            this.socket.send(JSON.stringify({
                type: 'request_update',
                timestamp: Date.now()
            }));
        }
    }
    
    /**
     * HTTP轮询获取数据
     */
    async fetchDataViaHttp() {
        try {
            const response = await fetch('/api/real-time-monitoring/status');
            const data = await response.json();
            
            if (data.success) {
                this.handleStatisticsUpdate(data.data);
            }
        } catch (error) {
            console.error('获取监控数据失败:', error);
        }
    }
    
    /**
     * 初始化控制面板
     */
    initControlPanel() {
        // 监控开关
        const monitorToggle = document.getElementById('monitor-toggle');
        if (monitorToggle) {
            monitorToggle.addEventListener('change', (e) => {
                this.toggleMonitoring(e.target.checked);
            });
        }
        
        // 声音开关
        const soundToggle = document.getElementById('sound-toggle');
        if (soundToggle) {
            this.soundEnabled = soundToggle.checked;
            soundToggle.addEventListener('change', (e) => {
                this.soundEnabled = e.target.checked;
            });
        }
        
        // 自动旋转开关
        const rotateToggle = document.getElementById('rotate-toggle');
        if (rotateToggle) {
            this.autoRotate = rotateToggle.checked;
            rotateToggle.addEventListener('change', (e) => {
                this.autoRotate = e.target.checked;
            });
        }
    }
    
    /**
     * 切换监控状态
     */
    async toggleMonitoring(enabled) {
        try {
            const response = await fetch('/api/real-time-monitoring/toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ enabled })
            });
            
            const result = await response.json();
            if (result.success) {
                console.log(`监控已${enabled ? '启用' : '停用'}`);
                this.updateConnectionStatus(
                    enabled ? '监控中' : '已停止',
                    enabled ? 'success' : 'warning'
                );
            }
        } catch (error) {
            console.error('切换监控状态失败:', error);
        }
    }
    
    /**
     * 更新连接状态
     */
    updateConnectionStatus(status, type) {
        const statusElement = document.getElementById('connection-status');
        if (statusElement) {
            statusElement.textContent = status;
            statusElement.className = `status ${type}`;
        }
    }
    
    /**
     * 开始渲染循环
     */
    startRenderLoop() {
        const animate = () => {
            requestAnimationFrame(animate);
            
            // 地球自转
            if (this.earth && this.autoRotate) {
                this.earth.rotation.y += 0.002;
            }
            
            // 渲染场景
            if (this.renderer && this.scene && this.camera) {
                this.renderer.render(this.scene, this.camera);
            }
        };
        
        animate();
    }
    
    /**
     * 窗口大小调整
     */
    onWindowResize() {
        if (!this.camera || !this.renderer) return;
        
        const container = document.getElementById('threat-globe-container');
        if (!container) return;
        
        this.camera.aspect = container.clientWidth / container.clientHeight;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(container.clientWidth, container.clientHeight);
    }
    
    /**
     * 重连WebSocket
     */
    reconnectWebSocket() {
        if (!this.isConnected) {
            console.log('🔄 尝试重新连接WebSocket...');
            this.initWebSocket();
        }
    }
    
    /**
     * 初始化HTTP轮询
     */
    initHttpPolling() {
        console.log('📡 使用HTTP轮询作为数据源');
        this.updateConnectionStatus('HTTP轮询', 'warning');
        this.startDataUpdateLoop();
    }
    
    /**
     * 添加光照
     */
    addLighting() {
        // 环境光
        const ambientLight = new THREE.AmbientLight(0x404040, 0.3);
        this.scene.add(ambientLight);
        
        // 方向光
        const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
        directionalLight.position.set(5, 5, 5);
        directionalLight.castShadow = true;
        this.scene.add(directionalLight);
    }
    
    /**
     * 添加控制器
     */
    addControls() {
        // 鼠标控制
        this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);
        this.controls.enableDamping = true;
        this.controls.dampingFactor = 0.05;
        this.controls.enableZoom = true;
        this.controls.enablePan = false;
    }
    
    /**
     * 处理统计更新
     */
    handleStatisticsUpdate(data) {
        this.statisticsData = data;
        this.updateStatistics();
    }
    
    /**
     * 处理系统状态
     */
    handleSystemStatus(data) {
        console.log('系统状态更新:', data);
        // 更新系统状态显示
    }
    
    /**
     * 处理地理更新
     */
    handleGeographicUpdate(data) {
        this.geographicData.set(data.country, data);
        // 更新地理分布图
    }
    
    /**
     * 显示被阻止的攻击
     */
    showBlockedAttack(attackData) {
        console.log('🛡️ 攻击已被阻止:', attackData);
        // 可以添加特殊的视觉效果
    }
    
    /**
     * 封禁IP
     */
    async blockIP(ip) {
        if (!confirm(`确定要封禁IP ${ip} 吗？`)) return;
        
        try {
            const response = await fetch('/api/security/block-ip', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ip, duration: 3600 })
            });
            
            const result = await response.json();
            if (result.success) {
                alert(`IP ${ip} 已被封禁`);
            } else {
                alert(`封禁失败: ${result.message}`);
            }
        } catch (error) {
            console.error('封禁IP失败:', error);
            alert('封禁操作失败');
        }
    }
    
    /**
     * 分析IP
     */
    async analyzeIP(ip) {
        try {
            const response = await fetch(`/api/security/analyze-ip?ip=${ip}`);
            const result = await response.json();
            
            if (result.success) {
                this.showIPAnalysisModal(ip, result.data);
            }
        } catch (error) {
            console.error('分析IP失败:', error);
        }
    }
    
    /**
     * 显示IP分析模态框
     */
    showIPAnalysisModal(ip, analysis) {
        const modal = document.createElement('div');
        modal.className = 'ip-analysis-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>IP分析报告: ${ip}</h3>
                    <button class="modal-close" onclick="this.closest('.ip-analysis-modal').remove()">×</button>
                </div>
                <div class="modal-body">
                    <div class="analysis-section">
                        <h4>基本信息</h4>
                        <p>国家: ${analysis.country || 'Unknown'}</p>
                        <p>城市: ${analysis.city || 'Unknown'}</p>
                        <p>ISP: ${analysis.isp || 'Unknown'}</p>
                    </div>
                    <div class="analysis-section">
                        <h4>威胁评估</h4>
                        <p>风险等级: ${analysis.risk_level || 'Unknown'}</p>
                        <p>威胁类型: ${analysis.threat_types?.join(', ') || 'None'}</p>
                        <p>检测次数: ${analysis.detection_count || 0}</p>
                    </div>
                    <div class="analysis-section">
                        <h4>历史记录</h4>
                        <div class="history-list">
                            ${analysis.history?.map(h => `
                                <div class="history-item">
                                    <span>${h.timestamp}</span>
                                    <span>${h.action}</span>
                                </div>
                            `).join('') || '<p>无历史记录</p>'}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }
}

// 全局实例
let securityDashboard;

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 启动实时网络安全监控系统...');
    securityDashboard = new RealTimeSecurityDashboard();
    
    // 导出到全局作用域
    window.securityDashboard = securityDashboard;
});

// 导出类
window.RealTimeSecurityDashboard = RealTimeSecurityDashboard;
