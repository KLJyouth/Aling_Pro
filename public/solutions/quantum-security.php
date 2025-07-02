<?php
/**
 * 量子安全服务解决方案页面
 * 
 * @version 1.0.0
 * @author AlingAi Team
 */

// 页面标题和描述
$pageTitle = "量子安全服务 - AlingAi Pro";
$pageDescription = "基于量子密码学的下一代加密技术，提供无法破解的安全防护解决方案";

// 额外CSS和JS
$additionalCSS = [
    "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css",
    "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css",
    "/css/solutions.css"
];
$additionalJS = [
    "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js",
    "/js/solutions.js"
];

// 页面内容
ob_start();
?>

<!-- 自定义样式 -->
<style>
    :root {
        --primary-color: #0066cc;
        --secondary-color: #00cc99;
        --accent-color: #ff6b35;
        --dark-color: #1a1a1a;
        --gradient-primary: linear-gradient(135deg, #0066cc 0%, #00cc99 100%);
    }

    .hero-section {
        background: var(--gradient-primary);
        color: white;
        padding: 120px 0 80px;
        margin-top: 20px;
    }

    .feature-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        height: 100%;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    }

    .feature-icon {
        width: 70px;
        height: 70px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.8rem;
        margin-bottom: 20px;
    }

    .tech-spec {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 20px;
    }

    .btn-quantum {
        background: var(--gradient-primary);
        border: none;
        color: white;
        padding: 12px 30px;
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-quantum:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0,102,204,0.3);
        color: white;
    }
</style>

<!-- 英雄区域 -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">量子安全服务</h1>
                <p class="lead mb-4">
                    基于量子密码学的下一代加密技术，提供理论上无法破解的安全防护。
                    利用量子物理学原理确保数据传输和存储的绝对安全。
                </p>
                <div class="d-flex gap-3">
                    <a href="#demo" class="btn btn-quantum">申请演示</a>
                    <a href="#features" class="btn btn-outline-light">了解特性</a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <i class="fas fa-atom" style="font-size: 12rem; opacity: 0.2;"></i>
            </div>
        </div>
    </div>
</section>

<!-- 核心特性 -->
<section class="py-5" id="features">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="display-5 fw-bold mb-4">量子加密核心特性</h2>
                <p class="lead text-muted">革命性的安全技术，重新定义数据保护标准</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h4>理论上无法破解</h4>
                    <p>基于量子物理学的海森堡不确定性原理，任何窃听行为都会被立即发现并阻止。</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <h4>量子密钥分发</h4>
                    <p>QKD技术确保密钥分发过程的绝对安全，即使面对量子计算机攻击也无懈可击。</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h4>实时威胁检测</h4>
                    <p>量子态监控系统实时检测任何异常活动，确保通信安全不被破坏。</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-random"></i>
                    </div>
                    <h4>真随机数生成</h4>
                    <p>利用量子随机性生成真正的随机数，为加密算法提供最强的随机性保障。</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-network-wired"></i>
                    </div>
                    <h4>量子网络架构</h4>
                    <p>构建基于量子原理的安全网络架构，实现端到端的量子安全通信。</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>后量子密码学</h4>
                    <p>结合传统密码学和量子技术，提供全方位的安全防护体系。</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 技术规格 -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="display-5 fw-bold mb-4">技术规格</h2>
                <p class="lead text-muted">了解我们量子安全解决方案的技术细节</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6">
                <div class="tech-spec">
                    <h4>量子密钥分发 (QKD)</h4>
                    <ul>
                        <li>基于BB84协议的量子密钥分发系统</li>
                        <li>密钥生成速率: 高达10Mbps</li>
                        <li>量子比特错误率 (QBER): < 1%</li>
                        <li>支持距离: 最大100公里</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="tech-spec">
                    <h4>量子随机数生成器 (QRNG)</h4>
                    <ul>
                        <li>基于量子光学原理的真随机数生成</li>
                        <li>随机数生成速率: 高达1Gbps</li>
                        <li>通过NIST SP 800-22随机性测试</li>
                        <li>支持AES-256, RSA-4096等加密算法</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="tech-spec">
                    <h4>量子安全网络</h4>
                    <ul>
                        <li>量子加密VPN解决方案</li>
                        <li>量子安全中继器</li>
                        <li>支持混合量子-经典网络架构</li>
                        <li>兼容现有网络基础设施</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="tech-spec">
                    <h4>后量子密码学</h4>
                    <ul>
                        <li>支持格基加密算法</li>
                        <li>支持多变量多项式加密</li>
                        <li>支持哈希基签名</li>
                        <li>符合NIST PQC标准</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 申请演示 -->
<section class="py-5" id="demo">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mx-auto text-center">
                <h2 class="display-5 fw-bold mb-4">申请演示</h2>
                <p class="lead text-muted mb-5">体验量子安全技术的强大保护能力</p>
                
                <form class="text-start">
                    <div class="mb-3">
                        <label for="name" class="form-label">姓名</label>
                        <input type="text" class="form-control" id="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="company" class="form-label">公司</label>
                        <input type="text" class="form-control" id="company" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">电子邮件</label>
                        <input type="email" class="form-control" id="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">电话</label>
                        <input type="tel" class="form-control" id="phone">
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">需求描述</label>
                        <textarea class="form-control" id="message" rows="4"></textarea>
                    </div>
                    <button type="submit" class="btn btn-quantum w-100">提交申请</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
$pageContent = ob_get_clean();

// 加载布局模板
require_once __DIR__ . '/../templates/layout.php';
?> 