<?php
/**
 * 隐私政策页面
 * 包含与AlingAi相关的个人数据处理政策、数据收集、使用和保护措施等内容
 */

// 设置页面信息
$pageTitle = "隐私政策 - AlingAi Pro";
$pageDescription = "AlingAi Pro的隐私政策，了解我们如何收集、使用和保护您的个人数据";
$pageKeywords = "隐私政策, 数据保护, 数据安全, 个人信息, GDPR, 隐私声明";

// 包含页面模板
require_once __DIR__ . "/templates/page.php";

// 渲染页面头部
renderPageHeader();
?>


<!-- 隐私政策页面内容 -->
<section class="privacy-hero">
    <div class="container">
        <h1>隐私政策</h1>
        <p class="lead">上次更新：<?php echo date("Y年m月d日"); ?></p>
    </div>
</section>

<section class="privacy-content">
    <div class="container">
        <div class="content-wrapper">
            <div class="content-main">
                <div id="overview" class="privacy-section">
                    <h2>概述</h2>
                    <p>AlingAi Pro（以下简称"我们"或"AlingAi"）非常重视您的隐私。本隐私政策旨在说明我们在您使用AlingAi服务时如何收集、使用、披露、传输和存储您的个人信息。</p>
                    <p>请在使用我们的服务前仔细阅读本隐私政策。使用我们的服务即表示您同意本隐私政策中描述的数据处理实践。如果您不同意本隐私政策的任何部分，请勿使用我们的服务。</p>
                </div>

                <div id="information-we-collect" class="privacy-section">
                    <h2>我们收集的信息</h2>
                    <p>我们可能会收集以下类型的个人信息：</p>
                    
                    <h3>您直接提供给我们的信息</h3>
                    <ul>
                        <li><strong>账户信息</strong>：当您注册AlingAi账户时，我们会收集您的姓名、电子邮件地址、电话号码、密码和其他账户相关信息。</li>
                        <li><strong>个人资料信息</strong>：您可以选择提供额外的个人资料信息，如头像、职业、公司名称等。</li>
                        <li><strong>支付信息</strong>：如果您购买我们的付费服务，我们会收集必要的支付信息，包括账单地址和支付方式等。</li>
                        <li><strong>用户内容</strong>：您在使用我们的服务时创建、上传或共享的内容，包括与AI助手的对话、文件、图像等。</li>
                        <li><strong>通信信息</strong>：您与我们的客户支持团队交流时提供的信息。</li>
                    </ul>

                    <h3>我们自动收集的信息</h3>
                    <ul>
                        <li><strong>使用数据</strong>：关于您如何使用我们的服务的信息，如功能使用频率、点击流模式、会话时长等。</li>
                        <li><strong>设备信息</strong>：包括设备类型、操作系统、浏览器类型、IP地址、设备标识符等。</li>
                        <li><strong>位置信息</strong>：基于您的IP地址的大致位置信息。</li>
                        <li><strong>Cookies和类似技术</strong>：我们使用cookies和类似技术来收集和存储信息，详情请参见下方"Cookies和类似技术"部分。</li>
                    </ul>
                </div>


                <div id="how-we-use" class="privacy-section">
                    <h2>如何使用您的信息</h2>
                    <p>我们使用收集到的信息主要用于以下目的：</p>
                    <ul>
                        <li><strong>提供服务</strong>：提供、维护和改进我们的服务，包括处理AI助手对话、分析用户需求等。</li>
                        <li><strong>账户管理</strong>：创建和管理您的账户，验证身份，处理付款。</li>
                        <li><strong>个性化</strong>：根据您的偏好和行为提供个性化的服务和内容。</li>
                        <li><strong>通信</strong>：与您联系，提供客户支持，发送关于服务的更新、安全警报等信息。</li>
                        <li><strong>研究与开发</strong>：进行数据分析和研究，以改进我们的服务和开发新功能。</li>
                        <li><strong>安全与保护</strong>：检测和防止欺诈、垃圾邮件、滥用和其他安全问题。</li>
                        <li><strong>法律合规</strong>：遵守适用的法律法规和执行我们的服务条款。</li>
                    </ul>
                </div>

                <div id="information-sharing" class="privacy-section">
                    <h2>信息共享与披露</h2>
                    <p>我们不会出售或出租您的个人信息给第三方进行营销目的。在以下情况下，我们可能会共享您的信息：</p>
                    <ul>
                        <li><strong>服务提供商</strong>：我们与帮助我们提供和支持服务的第三方服务提供商合作，如云存储提供商、支付处理商等。</li>
                        <li><strong>合作伙伴</strong>：经您同意，我们可能与我们的业务合作伙伴共享信息，以提供您所请求的产品或服务。</li>
                        <li><strong>法律要求</strong>：当我们善意地认为有必要遵守法律、法规或法律程序时，或保护AlingAi、我们的用户或公众的权利、财产或安全时。</li>
                        <li><strong>业务转让</strong>：如果AlingAi涉及合并、收购、破产、资产出售或类似交易，您的信息可能会作为资产的一部分被转让。</li>
                        <li><strong>聚合或匿名信息</strong>：我们可能会共享不能用于识别您个人身份的聚合或匿名信息。</li>
                    </ul>
                </div>

                <div id="data-security" class="privacy-section">
                    <h2>数据安全</h2>
                    <p>我们采取多层次的安全措施来保护您的个人信息免受未经授权的访问、使用或披露：</p>
                    <ul>
                        <li><strong>数据加密</strong>：我们使用行业标准的加密技术（SSL/TLS）来保护传输中的数据，并对存储的敏感数据进行加密。</li>
                        <li><strong>访问控制</strong>：我们实施严格的访问控制措施，只有经授权的员工才能访问您的个人信息，且仅限于履行职责所需的范围。</li>
                        <li><strong>定期安全审计</strong>：我们定期进行安全审计和渗透测试，以识别和修复潜在的漏洞。</li>
                        <li><strong>持续监控</strong>：我们的系统受到24/7的监控，以检测和应对异常活动。</li>
                        <li><strong>员工培训</strong>：我们的员工接受关于数据保护和安全最佳实践的定期培训。</li>
                    </ul>
                    <p>虽然我们努力保护您的信息，但请注意，没有任何安全措施是完美的或不可突破的。如果您怀疑您的账户安全受到威胁，请立即联系我们的安全团队。</p>
                </div>


                <div id="cookies" class="privacy-section">
                    <h2>Cookies和类似技术</h2>
                    <p>AlingAi使用cookies和类似技术（如网络信标、像素）来收集和存储信息，以便为您提供更好的用户体验、分析网站性能和改进我们的服务。</p>
                    <p>我们使用的cookies类型包括：</p>
                    <ul>
                        <li><strong>必要cookies</strong>：这些cookies是网站功能所必需的，例如用于登录和验证身份。</li>
                        <li><strong>功能cookies</strong>：这些cookies使我们能够记住您的偏好和设置。</li>
                        <li><strong>分析cookies</strong>：这些cookies帮助我们了解用户如何使用我们的网站，从而改进服务。</li>
                    </ul>
                    <p>您可以通过浏览器设置来控制cookies。请注意，禁用某些cookies可能会影响我们网站的功能。</p>
                </div>

                <div id="data-retention" class="privacy-section">
                    <h2>数据保留与删除</h2>
                    <p>我们会在实现本隐私政策中描述的目的所需的时间内保留您的个人信息，除非法律要求或允许更长的保留期限。</p>
                    <p>当信息不再需要用于这些目的时，我们会安全删除或匿名化处理这些信息。如果技术原因导致无法完全删除某些信息，我们会采取适当措施防止该信息被进一步使用或处理。</p>
                    <p>您可以通过账户设置删除特定内容，或通过联系我们的客户支持团队请求删除您的账户和相关数据。</p>
                </div>

                <div id="your-rights" class="privacy-section">
                    <h2>您的权利和选择</h2>
                    <p>根据您所在地区的适用法律，您可能拥有以下权利：</p>
                    <ul>
                        <li><strong>访问权</strong>：获取我们收集的关于您的个人信息副本。</li>
                        <li><strong>纠正权</strong>：要求更正不准确或不完整的个人信息。</li>
                        <li><strong>删除权</strong>：在某些情况下，要求删除您的个人信息。</li>
                        <li><strong>限制处理权</strong>：在某些情况下，要求限制处理您的个人信息。</li>
                        <li><strong>数据可携权</strong>：获取结构化、常用格式的个人信息副本，可以将其传输给其他数据控制者。</li>
                        <li><strong>反对权</strong>：基于与您特定情况相关的原因，反对处理您的个人信息。</li>
                        <li><strong>撤回同意权</strong>：如果我们基于您的同意处理您的个人信息，您有权随时撤回同意。</li>
                    </ul>
                    <p>如需行使这些权利，请通过以下"联系我们"部分提供的联系方式与我们联系。我们将在适用法律规定的时限内回应您的请求。</p>
                </div>


                <div id="international-transfers" class="privacy-section">
                    <h2>国际数据传输</h2>
                    <p>AlingAi是一家全球性企业，我们的服务器可能位于世界各地。这意味着您的信息可能会被传输和存储在您所在国家/地区以外的地方，包括可能不具有同等数据保护水平的国家/地区。</p>
                    <p>当我们传输您的个人信息至其他国家/地区时，我们会采取适当的保障措施，如签订标准合同条款或确保接收方遵守适当的数据保护标准，以确保您的信息得到保护。</p>
                </div>

                <div id="children-privacy" class="privacy-section">
                    <h2>儿童隐私</h2>
                    <p>AlingAi的服务不面向13岁以下的儿童。我们不会故意收集13岁以下儿童的个人信息。如果您是父母或监护人，并且发现您的孩子向我们提供了个人信息，请联系我们，我们将采取措施删除这些信息。</p>
                </div>

                <div id="changes" class="privacy-section">
                    <h2>隐私政策的变更</h2>
                    <p>我们可能会不时更新本隐私政策，以反映法律、技术或业务发展的变化。当我们更新本政策时，我们会在网站上发布更新后的版本，并更新页面顶部的"上次更新"日期。</p>
                    <p>对于重大变更，我们会通过电子邮件、网站通知或其他适当方式通知您。我们建议您定期查看本隐私政策，以便了解我们如何保护您的信息。</p>
                </div>

                <div id="contact-us" class="privacy-section">
                    <h2>联系我们</h2>
                    <p>如果您对本隐私政策或我们的数据处理实践有任何疑问、意见或投诉，请通过以下方式联系我们：</p>
                    <ul>
                        <li>电子邮件：<a href="mailto:privacy@alingai.pro">privacy@alingai.pro</a></li>
                        <li>邮寄地址：[公司实际地址]</li>
                    </ul>
                    <p>我们会在30天内回应您的请求。如果您对我们的回应不满意，您可以联系您当地的数据保护监管机构。</p>
                </div>
            </div>

            <div class="content-sidebar">
                <div class="sidebar-card">
                    <h3>目录</h3>
                    <ul class="toc-list">
                        <li><a href="#overview">概述</a></li>
                        <li><a href="#information-we-collect">我们收集的信息</a></li>
                        <li><a href="#how-we-use">如何使用您的信息</a></li>
                        <li><a href="#information-sharing">信息共享与披露</a></li>
                        <li><a href="#data-security">数据安全</a></li>
                        <li><a href="#cookies">Cookies和类似技术</a></li>
                        <li><a href="#data-retention">数据保留与删除</a></li>
                        <li><a href="#your-rights">您的权利和选择</a></li>
                        <li><a href="#international-transfers">国际数据传输</a></li>
                        <li><a href="#children-privacy">儿童隐私</a></li>
                        <li><a href="#changes">隐私政策的变更</a></li>
                        <li><a href="#contact-us">联系我们</a></li>
                    </ul>
                </div>

                <div class="sidebar-card">
                    <h3>相关链接</h3>
                    <ul class="related-links">
                        <li><a href="/terms">服务条款</a></li>
                        <li><a href="/security">安全措施</a></li>
                        <li><a href="/contact">联系我们</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>


<style>
    /* 隐私政策页面特定样式 */
    .privacy-hero {
        background: var(--glass-background);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-bottom: 1px solid var(--glass-border);
        padding: var(--spacing-xxl) 0;
        margin-bottom: var(--spacing-xl);
        text-align: center;
    }
    
    .privacy-hero h1 {
        font-size: 2.5rem;
        margin-bottom: var(--spacing-md);
        background: linear-gradient(90deg, var(--accent-color), var(--tertiary-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .privacy-hero .lead {
        font-size: 1.2rem;
        color: var(--text-color-light);
    }
    
    .privacy-content {
        padding: var(--spacing-xl) 0;
    }
    
    .content-wrapper {
        display: grid;
        grid-template-columns: 3fr 1fr;
        gap: var(--spacing-xl);
    }
    
    .privacy-section {
        margin-bottom: var(--spacing-xl);
        scroll-margin-top: 100px;
    }
    
    .privacy-section h2 {
        margin: var(--spacing-lg) 0 var(--spacing-md);
        color: var(--secondary-color);
        font-size: 1.8rem;
    }
    
    .privacy-section h3 {
        margin: var(--spacing-lg) 0 var(--spacing-sm);
        color: var(--accent-color);
        font-size: 1.4rem;
    }
    
    .privacy-section p {
        margin-bottom: var(--spacing-md);
        line-height: 1.7;
    }
    
    .privacy-section ul {
        margin-bottom: var(--spacing-md);
        padding-left: var(--spacing-lg);
    }
    
    .privacy-section li {
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
    .related-links {
        list-style: none;
        padding: 0;
    }
    
    .toc-list li,
    .related-links li {
        margin-bottom: var(--spacing-sm);
    }
    
    .toc-list a,
    .related-links a {
        color: var(--accent-color);
        text-decoration: none;
        display: block;
        padding: var(--spacing-xs) 0;
        transition: all var(--transition-fast);
    }
    
    .toc-list a:hover,
    .related-links a:hover {
        color: var(--tertiary-color);
        transform: translateX(5px);
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
        .privacy-hero h1 {
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
