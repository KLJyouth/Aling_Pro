/**
 * AlingAi Pro - 量子粒子动画
 * 为登录和首页提供量子安全视觉效果
 * 
 * @version 1.0.0
 * @author AlingAi Team
 */

// 量子粒子动画初始化函数
function initQuantumParticles(elementId) {
    const container = document.getElementById(elementId);
    if (!container) return;
    
    // 配置参数
    const config = {
        particleCount: 50,
        particleSize: [2, 5],
        particleColor: ['#3498db', '#9b59b6', '#2ecc71', '#f1c40f', '#e74c3c'],
        speed: [0.5, 2],
        connectionDistance: 150,
        connectionOpacity: 0.6,
        interactive: true
    };
    
    let particles = [];
    let width = container.offsetWidth;
    let height = container.offsetHeight;
    let canvas, ctx, animationFrame;
    
    // 创建画布
    function createCanvas() {
        canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        ctx = canvas.getContext('2d');
        container.appendChild(canvas);
    }
    
    // 粒子类
    class Particle {
        constructor() {
            this.x = Math.random() * width;
            this.y = Math.random() * height;
            this.size = Math.random() * (config.particleSize[1] - config.particleSize[0]) + config.particleSize[0];
            this.color = config.particleColor[Math.floor(Math.random() * config.particleColor.length)];
            this.speedX = (Math.random() - 0.5) * (config.speed[1] - config.speed[0]) + config.speed[0];
            this.speedY = (Math.random() - 0.5) * (config.speed[1] - config.speed[0]) + config.speed[0];
            this.opacity = Math.random() * 0.5 + 0.5;
        }
        
        update() {
            // 更新位置
            this.x += this.speedX;
            this.y += this.speedY;
            
            // 边界检查
            if (this.x < 0 || this.x > width) this.speedX *= -1;
            if (this.y < 0 || this.y > height) this.speedY *= -1;
            
            // 保持在边界内
            this.x = Math.max(0, Math.min(width, this.x));
            this.y = Math.max(0, Math.min(height, this.y));
        }
        
        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fillStyle = this.color;
            ctx.globalAlpha = this.opacity;
            ctx.fill();
            ctx.globalAlpha = 1;
        }
    }
    
    // 初始化粒子
    function initParticles() {
        particles = [];
        for (let i = 0; i < config.particleCount; i++) {
            particles.push(new Particle());
        }
    }
    
    // 绘制粒子连线
    function drawConnections() {
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                
                if (distance < config.connectionDistance) {
                    const opacity = (1 - distance / config.connectionDistance) * config.connectionOpacity;
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.strokeStyle = 'rgba(255, 255, 255, ' + opacity + ')';
                    ctx.lineWidth = 0.5;
                    ctx.stroke();
                }
            }
        }
    }
    
    // 动画循环
    function animate() {
        ctx.clearRect(0, 0, width, height);
        
        drawConnections();
        
        particles.forEach(particle => {
            particle.update();
            particle.draw();
        });
        
        animationFrame = requestAnimationFrame(animate);
    }
    
    // 处理交互
    function handleInteraction() {
        if (!config.interactive) return;
        
        let mouseX, mouseY;
        let isActive = false;
        
        canvas.addEventListener('mousemove', e => {
            isActive = true;
            const rect = canvas.getBoundingClientRect();
            mouseX = e.clientX - rect.left;
            mouseY = e.clientY - rect.top;
            
            // 粒子跟随鼠标
            particles.forEach(particle => {
                const dx = mouseX - particle.x;
                const dy = mouseY - particle.y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                
                if (distance < 100) {
                    const force = 0.2 * (1 - distance / 100);
                    particle.speedX += dx * force / 20;
                    particle.speedY += dy * force / 20;
                }
            });
        });
        
        canvas.addEventListener('mouseleave', () => {
            isActive = false;
        });
    }
    
    // 处理窗口大小变化
    function handleResize() {
        window.addEventListener('resize', () => {
            width = container.offsetWidth;
            height = container.offsetHeight;
            if (canvas) {
                canvas.width = width;
                canvas.height = height;
            }
        });
    }
    
    // 初始化
    function init() {
        createCanvas();
        initParticles();
        handleInteraction();
        handleResize();
        animate();
    }
    
    // 清理函数
    function cleanup() {
        if (animationFrame) {
            cancelAnimationFrame(animationFrame);
        }
        if (canvas && canvas.parentNode) {
            canvas.parentNode.removeChild(canvas);
        }
    }
    
    init();
    
    // 返回清理函数，便于后续需要时清理资源
    return cleanup;
}

// 量子动画初始化函数（用于首页特效）
function initQuantumAnimation(elementId) {
    const container = document.getElementById(elementId);
    if (!container) return;
    
    // 配置
    const config = {
        sphereCount: 3,
        particleCount: 80,
        color: ['#3498db', '#9b59b6', '#2ecc71'],
        rotationSpeed: 0.01
    };
    
    let width = container.offsetWidth;
    let height = container.offsetHeight;
    let canvas, ctx, animationFrame;
    let spheres = [];
    let particles = [];
    let angle = 0;
    
    // 创建画布
    function createCanvas() {
        canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        ctx = canvas.getContext('2d');
        container.appendChild(canvas);
    }
    
    // 量子球体类
    class QuantumSphere {
        constructor(index) {
            this.index = index;
            this.color = config.color[index % config.color.length];
            this.radius = 30 + index * 10;
            this.x = width / 2;
            this.y = height / 2;
            this.orbitRadius = 50 + index * 40;
            this.orbitSpeed = 0.02 - index * 0.005;
            this.angle = index * (Math.PI * 2 / config.sphereCount);
        }
        
        update() {
            this.angle += this.orbitSpeed;
            this.x = width / 2 + Math.cos(this.angle) * this.orbitRadius;
            this.y = height / 2 + Math.sin(this.angle) * this.orbitRadius;
        }
        
        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            const gradient = ctx.createRadialGradient(
                this.x, this.y, 0,
                this.x, this.y, this.radius
            );
            gradient.addColorStop(0, this.color);
            gradient.addColorStop(1, 'rgba(0, 0, 0, 0)');
            ctx.fillStyle = gradient;
            ctx.globalAlpha = 0.7;
            ctx.fill();
            ctx.globalAlpha = 1;
        }
    }
    
    // 量子粒子类
    class QuantumParticle {
        constructor() {
            this.reset();
        }
        
        reset() {
            this.sphere = Math.floor(Math.random() * config.sphereCount);
            this.size = Math.random() * 3 + 1;
            this.color = config.color[this.sphere % config.color.length];
            this.speed = Math.random() * 2 + 0.5;
            this.angle = Math.random() * Math.PI * 2;
            this.distance = Math.random() * 100 + 50;
            this.opacity = Math.random() * 0.5 + 0.3;
            this.life = 0;
            this.maxLife = Math.random() * 100 + 50;
        }
        
        update() {
            // 更新位置
            this.distance += this.speed;
            this.life++;
            
            if (this.life >= this.maxLife || this.distance > 300) {
                this.reset();
            }
            
            // 设置透明度
            this.opacity = (1 - this.life / this.maxLife) * 0.8;
        }
        
        draw() {
            if (this.sphere >= spheres.length) return;
            
            const sphere = spheres[this.sphere];
            const x = sphere.x + Math.cos(this.angle) * this.distance;
            const y = sphere.y + Math.sin(this.angle) * this.distance;
            
            ctx.beginPath();
            ctx.arc(x, y, this.size, 0, Math.PI * 2);
            ctx.fillStyle = this.color;
            ctx.globalAlpha = this.opacity;
            ctx.fill();
            ctx.globalAlpha = 1;
        }
    }
    
    // 初始化
    function init() {
        createCanvas();
        
        // 创建量子球体
        for (let i = 0; i < config.sphereCount; i++) {
            spheres.push(new QuantumSphere(i));
        }
        
        // 创建粒子
        for (let i = 0; i < config.particleCount; i++) {
            particles.push(new QuantumParticle());
        }
        
        // 处理窗口大小变化
        window.addEventListener('resize', () => {
            width = container.offsetWidth;
            height = container.offsetHeight;
            if (canvas) {
                canvas.width = width;
                canvas.height = height;
            }
        });
        
        // 开始动画
        animate();
    }
    
    // 动画循环
    function animate() {
        ctx.clearRect(0, 0, width, height);
        
        // 更新和绘制球体
        spheres.forEach(sphere => {
            sphere.update();
            sphere.draw();
        });
        
        // 更新和绘制粒子
        particles.forEach(particle => {
            particle.update();
            particle.draw();
        });
        
        // 绘制中心光点
        ctx.beginPath();
        ctx.arc(width / 2, height / 2, 15, 0, Math.PI * 2);
        const gradient = ctx.createRadialGradient(
            width / 2, height / 2, 0,
            width / 2, height / 2, 15
        );
        gradient.addColorStop(0, 'rgba(255, 255, 255, 0.8)');
        gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');
        ctx.fillStyle = gradient;
        ctx.fill();
        
        angle += config.rotationSpeed;
        animationFrame = requestAnimationFrame(animate);
    }
    
    // 清理函数
    function cleanup() {
        if (animationFrame) {
            cancelAnimationFrame(animationFrame);
        }
        if (canvas && canvas.parentNode) {
            canvas.parentNode.removeChild(canvas);
        }
    }
    
    init();
    
    // 返回清理函数
    return cleanup;
}
