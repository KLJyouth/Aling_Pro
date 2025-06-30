@extends("layouts.app")

@section("title", "常见问题")

@section("content")
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 mb-4">常见问题</h1>
            <p class="lead text-muted">了解关于 AlingAi 平台的常见问题和解答。</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- 搜索框 -->
            <div class="card border-0 shadow-sm mb-5">
                <div class="card-body p-4">
                    <form action="#" method="GET" class="d-flex">
                        <input type="text" class="form-control form-control-lg" placeholder="搜索问题..." name="q">
                        <button type="submit" class="btn btn-primary ms-2">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- FAQ 分类 -->
            <ul class="nav nav-pills justify-content-center mb-5">
                <li class="nav-item">
                    <a class="nav-link active" href="#general" data-bs-toggle="tab">一般问题</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#account" data-bs-toggle="tab">账户相关</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#billing" data-bs-toggle="tab">计费与订阅</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#api" data-bs-toggle="tab">API 相关</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#technical" data-bs-toggle="tab">技术支持</a>
                </li>
            </ul>
            
            <!-- FAQ 内容 -->
            <div class="tab-content">
                <!-- 一般问题 -->
                <div class="tab-pane fade show active" id="general">
                    <div class="accordion" id="accordionGeneral">
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingG1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseG1" aria-expanded="true" aria-controls="collapseG1">
                                    什么是 AlingAi？
                                </button>
                            </h2>
                            <div id="collapseG1" class="accordion-collapse collapse show" aria-labelledby="headingG1" data-bs-parent="#accordionGeneral">
                                <div class="accordion-body">
                                    AlingAi 是一个人工智能平台，提供自然语言处理、计算机视觉和机器学习等 AI 功能。我们的目标是帮助企业和开发者轻松集成 AI 能力到他们的应用中，提高工作效率和创造价值。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingG2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseG2" aria-expanded="false" aria-controls="collapseG2">
                                    AlingAi 适合哪些用户？
                                </button>
                            </h2>
                            <div id="collapseG2" class="accordion-collapse collapse" aria-labelledby="headingG2" data-bs-parent="#accordionGeneral">
                                <div class="accordion-body">
                                    AlingAi 适合各种类型的用户，包括：
                                    <ul>
                                        <li>企业：需要 AI 解决方案来优化业务流程和提高效率</li>
                                        <li>开发者：希望在应用中集成 AI 功能</li>
                                        <li>研究人员：需要强大的 AI 工具进行研究和实验</li>
                                        <li>创业公司：寻求快速部署 AI 功能而无需大量投资</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingG3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseG3" aria-expanded="false" aria-controls="collapseG3">
                                    如何开始使用 AlingAi？
                                </button>
                            </h2>
                            <div id="collapseG3" class="accordion-collapse collapse" aria-labelledby="headingG3" data-bs-parent="#accordionGeneral">
                                <div class="accordion-body">
                                    开始使用 AlingAi 非常简单：
                                    <ol>
                                        <li>注册一个账户</li>
                                        <li>选择适合您需求的订阅计划</li>
                                        <li>获取 API 密钥</li>
                                        <li>按照我们的文档集成 API</li>
                                        <li>开始使用 AI 功能</li>
                                    </ol>
                                    我们还提供详细的文档和示例代码，帮助您快速上手。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm">
                            <h2 class="accordion-header" id="headingG4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseG4" aria-expanded="false" aria-controls="collapseG4">
                                    AlingAi 支持哪些语言？
                                </button>
                            </h2>
                            <div id="collapseG4" class="accordion-collapse collapse" aria-labelledby="headingG4" data-bs-parent="#accordionGeneral">
                                <div class="accordion-body">
                                    AlingAi 支持多种语言，包括中文、英文、日文、韩文、法文、德文、西班牙文等主要语言。我们的自然语言处理功能可以处理这些语言的文本，进行翻译、情感分析、文本分类等操作。
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 账户相关 -->
                <div class="tab-pane fade" id="account">
                    <div class="accordion" id="accordionAccount">
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingA1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseA1" aria-expanded="true" aria-controls="collapseA1">
                                    如何创建账户？
                                </button>
                            </h2>
                            <div id="collapseA1" class="accordion-collapse collapse show" aria-labelledby="headingA1" data-bs-parent="#accordionAccount">
                                <div class="accordion-body">
                                    创建账户非常简单，只需点击网站右上角的"注册"按钮，填写必要的信息，如电子邮件、密码等，然后按照指示完成注册流程即可。注册成功后，您将收到一封确认邮件，点击邮件中的链接激活您的账户。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingA2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseA2" aria-expanded="false" aria-controls="collapseA2">
                                    如何修改账户信息？
                                </button>
                            </h2>
                            <div id="collapseA2" class="accordion-collapse collapse" aria-labelledby="headingA2" data-bs-parent="#accordionAccount">
                                <div class="accordion-body">
                                    登录后，点击右上角的个人头像，选择"个人资料"，您可以在个人资料页面修改您的账户信息，如姓名、头像、联系方式等。修改完成后，点击"保存更改"按钮即可。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingA3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseA3" aria-expanded="false" aria-controls="collapseA3">
                                    如何修改密码？
                                </button>
                            </h2>
                            <div id="collapseA3" class="accordion-collapse collapse" aria-labelledby="headingA3" data-bs-parent="#accordionAccount">
                                <div class="accordion-body">
                                    登录后，点击右上角的个人头像，选择"安全设置"，在安全设置页面，您可以修改密码。您需要输入当前密码和新密码，然后点击"更新密码"按钮即可。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm">
                            <h2 class="accordion-header" id="headingA4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseA4" aria-expanded="false" aria-controls="collapseA4">
                                    如何删除账户？
                                </button>
                            </h2>
                            <div id="collapseA4" class="accordion-collapse collapse" aria-labelledby="headingA4" data-bs-parent="#accordionAccount">
                                <div class="accordion-body">
                                    如果您想删除账户，请登录后点击右上角的个人头像，选择"安全设置"，在页面底部找到"删除账户"选项。请注意，删除账户是不可逆的操作，您的所有数据将被永久删除。如果您有任何问题，请联系我们的客服团队。
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 计费与订阅 -->
                <div class="tab-pane fade" id="billing">
                    <div class="accordion" id="accordionBilling">
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingB1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseB1" aria-expanded="true" aria-controls="collapseB1">
                                    AlingAi 的价格如何？
                                </button>
                            </h2>
                            <div id="collapseB1" class="accordion-collapse collapse show" aria-labelledby="headingB1" data-bs-parent="#accordionBilling">
                                <div class="accordion-body">
                                    AlingAi 提供多种价格方案，从免费计划到企业级方案。具体价格取决于您的使用需求，如 API 调用次数、并发请求数、存储空间等。您可以在我们的<a href="{{ route("pricing") }}">价格页面</a>查看详细的价格信息。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingB2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseB2" aria-expanded="false" aria-controls="collapseB2">
                                    如何升级或降级我的订阅？
                                </button>
                            </h2>
                            <div id="collapseB2" class="accordion-collapse collapse" aria-labelledby="headingB2" data-bs-parent="#accordionBilling">
                                <div class="accordion-body">
                                    登录后，进入"会员中心"页面，您可以查看当前的订阅计划并进行升级或降级操作。升级后，新的计划将立即生效，并按比例计算费用。降级后，新的计划将在当前计费周期结束后生效。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingB3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseB3" aria-expanded="false" aria-controls="collapseB3">
                                    支持哪些付款方式？
                                </button>
                            </h2>
                            <div id="collapseB3" class="accordion-collapse collapse" aria-labelledby="headingB3" data-bs-parent="#accordionBilling">
                                <div class="accordion-body">
                                    我们支持多种付款方式，包括：
                                    <ul>
                                        <li>信用卡/借记卡（Visa、MasterCard、UnionPay等）</li>
                                        <li>支付宝</li>
                                        <li>微信支付</li>
                                        <li>银行转账（仅适用于企业客户）</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm">
                            <h2 class="accordion-header" id="headingB4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseB4" aria-expanded="false" aria-controls="collapseB4">
                                    是否提供发票？
                                </button>
                            </h2>
                            <div id="collapseB4" class="accordion-collapse collapse" aria-labelledby="headingB4" data-bs-parent="#accordionBilling">
                                <div class="accordion-body">
                                    是的，我们提供增值税发票。您可以在"会员中心"的"发票管理"页面申请发票。请提供准确的发票信息，如抬头、税号等。发票将在申请后 3-5 个工作日内开具并发送给您。
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- API 相关 -->
                <div class="tab-pane fade" id="api">
                    <div class="accordion" id="accordionApi">
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingAPI1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAPI1" aria-expanded="true" aria-controls="collapseAPI1">
                                    如何获取 API 密钥？
                                </button>
                            </h2>
                            <div id="collapseAPI1" class="accordion-collapse collapse show" aria-labelledby="headingAPI1" data-bs-parent="#accordionApi">
                                <div class="accordion-body">
                                    登录后，进入"API 管理"页面，点击"创建 API 密钥"按钮，填写必要的信息，如密钥名称、使用范围等，然后点击"创建"按钮即可获取 API 密钥。请妥善保管您的 API 密钥，不要泄露给他人。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingAPI2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAPI2" aria-expanded="false" aria-controls="collapseAPI2">
                                    如何使用 API？
                                </button>
                            </h2>
                            <div id="collapseAPI2" class="accordion-collapse collapse" aria-labelledby="headingAPI2" data-bs-parent="#accordionApi">
                                <div class="accordion-body">
                                    使用 API 前，您需要先获取 API 密钥。然后，您可以按照我们的<a href="{{ route("api-docs") }}">API 文档</a>中的说明，使用您选择的编程语言发送 HTTP 请求到我们的 API 端点。我们提供详细的文档和示例代码，帮助您快速集成我们的 API。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingAPI3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAPI3" aria-expanded="false" aria-controls="collapseAPI3">
                                    API 调用限制是什么？
                                </button>
                            </h2>
                            <div id="collapseAPI3" class="accordion-collapse collapse" aria-labelledby="headingAPI3" data-bs-parent="#accordionApi">
                                <div class="accordion-body">
                                    API 调用限制取决于您的订阅计划。不同的计划有不同的 API 调用次数和并发请求数限制。您可以在"会员中心"查看您的 API 使用情况和限制。如果您需要更高的限制，可以升级您的订阅计划或联系我们的销售团队获取定制方案。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm">
                            <h2 class="accordion-header" id="headingAPI4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAPI4" aria-expanded="false" aria-controls="collapseAPI4">
                                    API 支持哪些编程语言？
                                </button>
                            </h2>
                            <div id="collapseAPI4" class="accordion-collapse collapse" aria-labelledby="headingAPI4" data-bs-parent="#accordionApi">
                                <div class="accordion-body">
                                    我们的 API 是基于 RESTful 架构的，可以与任何支持 HTTP 请求的编程语言一起使用。我们提供多种编程语言的 SDK，包括 Python、Java、JavaScript、PHP、Go 等，方便您快速集成我们的 API。
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 技术支持 -->
                <div class="tab-pane fade" id="technical">
                    <div class="accordion" id="accordionTechnical">
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingT1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseT1" aria-expanded="true" aria-controls="collapseT1">
                                    如何获取技术支持？
                                </button>
                            </h2>
                            <div id="collapseT1" class="accordion-collapse collapse show" aria-labelledby="headingT1" data-bs-parent="#accordionTechnical">
                                <div class="accordion-body">
                                    您可以通过以下方式获取技术支持：
                                    <ul>
                                        <li>查阅我们的<a href="{{ route("api-docs") }}">文档</a>和<a href="{{ route("tutorials") }}">教程</a></li>
                                        <li>在控制面板中提交工单</li>
                                        <li>发送邮件至 support@alingai.com</li>
                                        <li>拨打技术支持热线 +86 10 8888 8888 转 2</li>
                                    </ul>
                                    高级会员和企业会员可以享受优先技术支持和专属技术支持。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingT2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseT2" aria-expanded="false" aria-controls="collapseT2">
                                    技术支持的响应时间是多久？
                                </button>
                            </h2>
                            <div id="collapseT2" class="accordion-collapse collapse" aria-labelledby="headingT2" data-bs-parent="#accordionTechnical">
                                <div class="accordion-body">
                                    技术支持的响应时间取决于您的订阅计划：
                                    <ul>
                                        <li>免费用户：24-48 小时</li>
                                        <li>基础会员：12-24 小时</li>
                                        <li>高级会员：6-12 小时</li>
                                        <li>企业会员：1-4 小时</li>
                                    </ul>
                                    紧急问题将获得优先处理。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingT3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseT3" aria-expanded="false" aria-controls="collapseT3">
                                    是否提供培训和咨询服务？
                                </button>
                            </h2>
                            <div id="collapseT3" class="accordion-collapse collapse" aria-labelledby="headingT3" data-bs-parent="#accordionTechnical">
                                <div class="accordion-body">
                                    是的，我们为企业客户提供培训和咨询服务，帮助您更好地使用我们的产品和服务。培训内容包括 API 使用、最佳实践、高级功能等。如果您需要培训和咨询服务，请联系我们的销售团队。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm">
                            <h2 class="accordion-header" id="headingT4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseT4" aria-expanded="false" aria-controls="collapseT4">
                                    如何报告 Bug 或提出功能建议？
                                </button>
                            </h2>
                            <div id="collapseT4" class="accordion-collapse collapse" aria-labelledby="headingT4" data-bs-parent="#accordionTechnical">
                                <div class="accordion-body">
                                    您可以通过以下方式报告 Bug 或提出功能建议：
                                    <ul>
                                        <li>在控制面板中提交工单</li>
                                        <li>发送邮件至 feedback@alingai.com</li>
                                        <li>在我们的社区论坛中发帖</li>
                                    </ul>
                                    我们非常重视用户的反馈，会认真考虑每一个建议。
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 未找到答案 -->
    <div class="row mt-5">
        <div class="col-lg-8 mx-auto text-center">
            <h2 class="h3 mb-4">没有找到您需要的答案？</h2>
            <p class="mb-4">我们的客服团队随时为您提供帮助。</p>
            <div class="d-flex justify-content-center">
                <a href="{{ route("contact") }}" class="btn btn-primary me-2">
                    <i class="fas fa-envelope me-1"></i> 联系我们
                </a>
                <a href="{{ route("support") }}" class="btn btn-outline-primary">
                    <i class="fas fa-headset me-1"></i> 获取支持
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
