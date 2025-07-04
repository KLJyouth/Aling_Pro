<?php
/**
 * 安全政策页面
 * 介绍AlingAi Pro的安全措施和数据保护政策
 */

// 设置页面信息
$pageTitle = "安全政策 - AlingAi Pro";
$pageDescription = "AlingAi Pro的安全政策，了解我们如何保护您的数据和账户安全";
$pageKeywords = "安全政策, 数据安全, 加密, 安全措施, 漏洞披露, 安全更新";

// 包含页面模板
require_once __DIR__ . "/templates/page.php";

// 渲染页面头部
renderPageHeader();
?>


<!-- 安全政策页面内容 -->
<section class="security-hero">
    <div class="container">
        <h1>安全政策</h1>
        <p class="lead">我们致力于保护您的数据和账户安全</p>
    </div>
</section>

<section class="security-content">
    <div class="container">
        <div class="content-wrapper">
            <div class="content-main">
                <div id="overview" class="security-section">
                    <h2>安全承诺</h2>
                    <p>在AlingAi Pro，我们将安全视为首要任务。我们采用行业领先的安全技术和流程，保护您的数据和账户安全。我们的安全策略涵盖了从数据加密到定期安全审计的各个方面，确保您可以安心地使用我们的服务。</p>
                    <p>本安全政策详细说明了我们如何保护您的数据安全，以及您可以采取哪些措施来提高自己账户的安全性。</p>
                </div>

                <div id="data-security" class="security-section">
                    <h2>数据安全措施</h2>
                    <p>我们实施了多层次的安全措施来保护您的数据：</p>

                    <h3>数据加密</h3>
                    <p>我们使用行业标准的加密技术来保护您的数据：</p>
                    <ul>
                        <li><strong>传输中数据加密</strong>：所有通过我们网络传输的数据都使用TLS 1.3协议加密，确保数据在传输过程中不会被截取或篡改。</li>
                        <li><strong>存储数据加密</strong>：敏感数据在存储时使用AES-256加密算法进行加密，保护数据免受未经授权的访问。</li>
                        <li><strong>端到端加密</strong>：对于特定类型的敏感通信，我们提供端到端加密选项，确保只有发送方和接收方能够访问内容。</li>
                    </ul>

                    <h3>安全基础设施</h3>
                    <p>我们的基础设施设计遵循安全最佳实践：</p>
                    <ul>
                        <li><strong>安全数据中心</strong>：我们的服务器托管在符合ISO 27001、SOC 2和其他行业标准的数据中心。</li>
                        <li><strong>网络安全</strong>：使用防火墙、入侵检测系统和DDoS保护服务，防止网络攻击。</li>
                        <li><strong>冗余与备份</strong>：定期数据备份和灾难恢复计划，确保即使在系统故障的情况下也能保护您的数据。</li>
                    </ul>
                </div>

                <div id="access-control" class="security-section">
                    <h2>访问控制</h2>
                    <p>我们实施严格的访问控制措施，确保只有授权人员才能访问敏感数据：</p>
                    <ul>
                        <li><strong>最小权限原则</strong>：员工只能访问其工作所需的数据和系统，遵循"最小需知"原则。</li>
                        <li><strong>多因素认证</strong>：所有员工访问系统时都必须使用多因素认证，增加额外的安全层。</li>
                        <li><strong>访问日志与监控</strong>：所有系统访问都被记录和监控，以便及时检测异常活动。</li>
                        <li><strong>定期访问权限审查</strong>：定期审查和更新员工访问权限，确保符合当前的职责要求。</li>
                    </ul>
                </div>

                <div id="account-security" class="security-section">
                    <h2>账户安全</h2>
                    <p>我们提供多种工具和功能，帮助您保护自己的账户：</p>

                    <h3>身份验证</h3>
                    <ul>
                        <li><strong>强密码策略</strong>：要求使用包含大小写字母、数字和特殊字符的强密码。</li>
                        <li><strong>多因素认证</strong>：支持通过短信、移动应用或安全密钥进行双因素认证，大大提高账户安全性。</li>
                        <li><strong>登录异常检测</strong>：监控登录活动，检测可疑行为，如从新设备或不常见位置登录。</li>
                    </ul>

                    <h3>会话管理</h3>
                    <ul>
                        <li><strong>安全会话处理</strong>：会话令牌使用加密存储，并有适当的过期机制。</li>
                        <li><strong>活动会话管理</strong>：用户可以查看和管理当前活跃的会话，必要时远程终止会话。</li>
                        <li><strong>自动超时</strong>：闲置会话会自动超时，减少未经授权访问的风险。</li>
                    </ul>

                    <h3>账户安全建议</h3>
                    <p>为提高账户安全，我们建议您：</p>
                    <ul>
                        <li>启用多因素认证</li>
                        <li>使用独特、强密码，并定期更改</li>
                        <li>不要在多个网站使用相同的密码</li>
                        <li>定期检查账户活动和登录历史</li>
                        <li>保持设备和浏览器最新，及时安装安全更新</li>
                        <li>警惕钓鱼攻击，不要点击可疑链接或提供账户信息</li>
                    </ul>
                </div>

                <div id="vulnerability-management" class="security-section">
                    <h2>漏洞管理</h2>
                    <p>我们主动识别和修复安全漏洞：</p>

                    <h3>漏洞扫描与测试</h3>
                    <ul>
                        <li><strong>定期安全扫描</strong>：对我们的系统和基础设施进行定期自动和手动安全扫描。</li>
                        <li><strong>渗透测试</strong>：聘请第三方安全专家进行定期渗透测试，评估我们的安全防御措施。</li>
                        <li><strong>代码审查</strong>：严格的代码审查流程，包括自动化安全测试和人工审查，以识别潜在的安全问题。</li>
                    </ul>

                    <h3>安全更新</h3>
                    <ul>
                        <li><strong>及时补丁</strong>：对所有已知漏洞进行优先级排序并及时修补。</li>
                        <li><strong>依赖项管理</strong>：定期审计和更新第三方依赖项，减少供应链风险。</li>
                        <li><strong>无缝部署</strong>：使用安全的CI/CD流程，确保补丁能快速、安全地部署到生产环境。</li>
                    </ul>

                    <h3>漏洞报告计划</h3>
                    <p>我们鼓励安全研究人员和用户报告可能发现的安全问题：</p>
                    <ul>
                        <li>如发现安全漏洞，请发送详细信息至 <a href="mailto:security@alingai.pro">security@alingai.pro</a></li>
                        <li>我们的安全团队将迅速评估和响应所有报告</li>
                        <li>我们承诺不对善意报告安全问题的个人采取法律行动</li>
                        <li>我们对于重大安全发现提供适当的致谢或奖励</li>
                    </ul>
                </div>

                <div id="incident-response" class="security-section">
                    <h2>安全事件响应</h2>
                    <p>尽管我们尽一切努力预防安全事件，但我们也为可能发生的情况做好了准备：</p>

                    <h3>响应流程</h3>
                    <ul>
                        <li><strong>检测与报告</strong>：使用先进的监控工具和警报系统，快速检测潜在的安全事件。</li>
                        <li><strong>评估与分类</strong>：迅速评估事件的严重性和影响范围，确定适当的响应级别。</li>
                        <li><strong>遏制与消除</strong>：采取措施限制安全事件的影响，并消除威胁。</li>
                        <li><strong>恢复与学习</strong>：恢复正常运行，并从事件中吸取教训，改进安全控制。</li>
                    </ul>

                    <h3>通知政策</h3>
                    <p>如果发生影响用户数据的安全事件：</p>
                    <ul>
                        <li>我们将在发现后的合理时间内通知受影响的用户，符合适用法律要求</li>
                        <li>通知将包括事件的性质、可能的影响以及我们采取的补救措施</li>
                        <li>我们将提供明确的指导，说明用户可以采取哪些步骤来保护自己</li>
                    </ul>
                </div>

                    <ul>
                        <li><strong>及时补丁</strong>：对所有已知漏洞进行优先级排序并及时修补。</li>
                        <li><strong>依赖项管理</strong>：定期审计和更新第三方依赖项，减少供应链风险。</li>
                        <li><strong>无缝部署</strong>：使用安全的CI/CD流程，确保补丁能快速、安全地部署到生产环境。</li>
                    </ul>

                    <h3>漏洞报告计划</h3>
                    <p>我们鼓励安全研究人员和用户报告可能发现的安全问题：</p>
                    <ul>
                        <li>如发现安全漏洞，请发送详细信息至 <a href="mailto:security@alingai.pro">security@alingai.pro</a></li>
                        <li>我们的安全团队将迅速评估和响应所有报告</li>
                        <li>我们承诺不对善意报告安全问题的个人采取法律行动</li>
                        <li>我们对于重大安全发现提供适当的致谢或奖励</li>
                    </ul>
                </div>

                <div id="incident-response" class="security-section">
                    <h2>安全事件响应</h2>
                    <p>尽管我们尽一切努力预防安全事件，但我们也为可能发生的情况做好了准备：</p>

                    <h3>响应流程</h3>
                    <ul>
                        <li><strong>检测与报告</strong>：使用先进的监控工具和警报系统，快速检测潜在的安全事件。</li>
                        <li><strong>评估与分类</strong>：迅速评估事件的严重性和影响范围，确定适当的响应级别。</li>
                        <li><strong>遏制与消除</strong>：采取措施限制安全事件的影响，并消除威胁。</li>
                        <li><strong>恢复与学习</strong>：恢复正常运行，并从事件中吸取教训，改进安全控制。</li>
                    </ul>

                    <h3>通知政策</h3>
                    <p>如果发生影响用户数据的安全事件：</p>
                    <ul>
                        <li>我们将在发现后的合理时间内通知受影响的用户，符合适用法律要求</li>
                        <li>通知将包括事件的性质、可能的影响以及我们采取的补救措施</li>
                        <li>我们将提供明确的指导，说明用户可以采取哪些步骤来保护自己</li>
                    </ul>
                </div>

                <div id="compliance" class="security-section">
                    <h2>合规与认证</h2>
                    <p>我们严格遵守适用的数据保护法规和行业标准：</p>

                    <h3>法规遵从</h3>
                    <ul>
                        <li><strong>GDPR</strong>：我们遵守《通用数据保护条例》(GDPR)的要求，保护欧洲用户的隐私权。</li>
                        <li><strong>CCPA/CPRA</strong>：我们符合《加州消费者隐私法》(CCPA)和《加州隐私权法案》(CPRA)的要求。</li>
                        <li><strong>中国网络安全法</strong>：我们遵守中国网络安全法及相关数据保护法规。</li>
                        <li><strong>行业特定法规</strong>：根据不同行业需求，我们遵守HIPAA、GLBA等特定行业法规。</li>
                    </ul>

                    <h3>安全认证</h3>
                    <p>我们致力于获得并维持主要的安全认证和合规性认证，包括：</p>
                    <ul>
                        <li>ISO 27001（信息安全管理体系）</li>
                        <li>SOC 2 Type II（服务组织控制）</li>
                        <li>CSA STAR（云安全联盟安全、信任与保障注册）</li>
                    </ul>
                    <p>这些认证由独立的第三方审计师定期审查和验证，确保我们持续符合严格的安全标准。</p>
                </div>

                <div id="contact" class="security-section">
                    <h2>联系我们</h2>
                    <p>如果您对我们的安全措施有任何疑问或需要报告安全问题，请随时联系我们：</p>
                    <ul>
                        <li><strong>安全报告</strong>：<a href="mailto:security@alingai.pro">security@alingai.pro</a></li>
                        <li><strong>数据保护问询</strong>：<a href="mailto:privacy@alingai.pro">privacy@alingai.pro</a></li>
                        <li><strong>一般安全问题</strong>：<a href="mailto:info@alingai.pro">info@alingai.pro</a></li>
                    </ul>
                    <p>我们的安全团队将在1-2个工作日内回复您的询问。</p>
                </div>
            </div>

            <div class="content-sidebar">
                <div class="sidebar-card">
                    <h3>目录</h3>
                    <ul class="toc-list">
                        <li><a href="#overview">安全承诺</a></li>
                        <li><a href="#data-security">数据安全措施</a></li>
                        <li><a href="#access-control">访问控制</a></li>
                        <li><a href="#account-security">账户安全</a></li>
                        <li><a href="#vulnerability-management">漏洞管理</a></li>
                        <li><a href="#incident-response">安全事件响应</a></li>
                        <li><a href="#compliance">合规与认证</a></li>
                        <li><a href="#contact">联系我们</a></li>
                    </ul>
                </div>

                <div class="sidebar-card">
                    <h3>安全资源</h3>
                    <ul class="resource-links">
                        <li><a href="/privacy">隐私政策</a></li>
                        <li><a href="/terms">服务条款</a></li>
                        <li><a href="/contact">联系我们</a></li>
                        <li><a href="/docs/security">安全最佳实践</a></li>
                    </ul>
                </div>
                
                <div class="sidebar-card security-contact">
                    <h3>安全报告</h3>
                    <p>发现安全漏洞？立即联系我们的安全团队：</p>
                    <a href="mailto:security@alingai.pro" class="btn btn-accent">
                        <i class="fas fa-shield-alt"></i> 报告安全问题
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>


<style>
    /* 安全政策页面特定样式 */
    .security-hero {
        background: var(--glass-background);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-bottom: 1px solid var(--glass-border);
        padding: var(--spacing-xxl) 0;
        margin-bottom: var(--spacing-xl);
        text-align: center;
        background-image: radial-gradient(circle at 10% 20%, rgba(var(--accent-color-rgb), 0.05) 0%, rgba(var(--secondary-color-rgb), 0.07) 90%);
    }
    
    .security-hero h1 {
        font-size: 2.5rem;
        margin-bottom: var(--spacing-md);
        background: linear-gradient(90deg, var(--accent-color), var(--tertiary-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .security-hero .lead {
        font-size: 1.2rem;
        color: var(--text-color-light);
    }
    
    .security-content {
        padding: var(--spacing-xl) 0;
    }
    
    .content-wrapper {
        display: grid;
        grid-template-columns: 3fr 1fr;
        gap: var(--spacing-xl);
    }
    
    .security-section {
        margin-bottom: var(--spacing-xl);
        scroll-margin-top: 100px;
    }
    
    .security-section h2 {
        margin: var(--spacing-lg) 0 var(--spacing-md);
        color: var(--secondary-color);
        font-size: 1.8rem;
    }
    
    .security-section h3 {
        margin: var(--spacing-lg) 0 var(--spacing-sm);
        color: var(--accent-color);
        font-size: 1.4rem;
    }
    
    .security-section p {
        margin-bottom: var(--spacing-md);
        line-height: 1.7;
    }
    
    .security-section ul {
        margin-bottom: var(--spacing-md);
        padding-left: var(--spacing-lg);
    }
    
    .security-section li {
        margin-bottom: var(--spacing-xs);
        line-height: 1.6;
    }
    
    .sidebar-card {
        background: var(--glass-background);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-md);
        margin-bottom: var(--spacing-lg);
        position: sticky;
        top: 100px;
    }
    
    .sidebar-card h3 {
        margin-bottom: var(--spacing-md);
        color: var(--secondary-color);
    }
    
    .toc-list,
    .resource-links {
        list-style: none;
        padding: 0;
    }
    
    .toc-list li,
    .resource-links li {
        margin-bottom: var(--spacing-sm);
    }
    
    .toc-list a,
    .resource-links a {
        color: var(--accent-color);
        text-decoration: none;
        display: block;
        padding: var(--spacing-xs) 0;
        transition: all var(--transition-fast);
    }
    
    .toc-list a:hover,
    .resource-links a:hover {
        color: var(--tertiary-color);
        transform: translateX(5px);
    }
    
    .security-contact {
        text-align: center;
    }
    
    .security-contact p {
        margin-bottom: var(--spacing-md);
    }
    
    .security-contact .btn {
        display: inline-block;
        padding: var(--spacing-sm) var(--spacing-md);
        width: 100%;
    }
    
    .security-contact .fas {
        margin-right: var(--spacing-xs);
    }
    
    @media (max-width: 992px) {
        .content-wrapper {
            grid-template-columns: 1fr;
        }
        
        .content-sidebar {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: var(--spacing-md);
        }
        
        .sidebar-card {
            position: static;
        }
    }
    
    @media (max-width: 768px) {
        .security-hero h1 {
            font-size: 2rem;
        }
        
        .content-sidebar {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php
// 渲染页面页脚
renderPageFooter();
?>
