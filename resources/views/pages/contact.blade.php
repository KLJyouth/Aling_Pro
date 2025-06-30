@extends("layouts.app")

@section("title", "联系我们")

@section("content")
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 mb-4">联系我们</h1>
            <p class="lead text-muted">如果您有任何问题或建议，欢迎随时联系我们。</p>
        </div>
    </div>
    
    <div class="row">
        <!-- 联系方式 -->
        <div class="col-lg-4 mb-4 mb-lg-0">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h4 mb-4">联系方式</h2>
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="h6 mb-1">地址</h3>
                            <p class="mb-0">北京市海淀区中关村南大街5号</p>
                        </div>
                    </div>
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-phone-alt fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="h6 mb-1">电话</h3>
                            <p class="mb-0">+86 10 8888 8888</p>
                        </div>
                    </div>
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-envelope fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="h6 mb-1">邮箱</h3>
                            <p class="mb-0">contact@alingai.com</p>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="h6 mb-1">工作时间</h3>
                            <p class="mb-0">周一至周五: 9:00 - 18:00</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 联系表单 -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h4 mb-4">给我们留言</h2>
                    
                    @if(session("success"))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session("success") }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                    
                    <form action="{{ route("submit-contact") }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">姓名</label>
                                <input type="text" class="form-control @error("name") is-invalid @enderror" id="name" name="name" value="{{ old("name") }}" required>
                                @error("name")
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">邮箱</label>
                                <input type="email" class="form-control @error("email") is-invalid @enderror" id="email" name="email" value="{{ old("email") }}" required>
                                @error("email")
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">主题</label>
                            <input type="text" class="form-control @error("subject") is-invalid @enderror" id="subject" name="subject" value="{{ old("subject") }}" required>
                            @error("subject")
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">消息</label>
                            <textarea class="form-control @error("message") is-invalid @enderror" id="message" name="message" rows="5" required>{{ old("message") }}</textarea>
                            @error("message")
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input @error("privacy") is-invalid @enderror" type="checkbox" id="privacy" name="privacy" required>
                                <label class="form-check-label" for="privacy">
                                    我同意根据<a href="{{ route("privacy") }}" target="_blank">隐私政策</a>处理我的个人数据
                                </label>
                                @error("privacy")
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> 发送消息
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 地图 -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3039.0588500880316!2d116.31860911744384!3d39.9878555!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x35f05b1af04245df%3A0x3fa6d48cae12b231!2z5Lit5YWD5p2R5Y2X5aSn6KGX!5e0!3m2!1szh-CN!2scn!4v1593500124732!5m2!1szh-CN!2scn" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 常见问题 -->
    <div class="row mt-5">
        <div class="col-lg-8 mx-auto">
            <h2 class="h3 mb-4 text-center">常见问题</h2>
            <div class="accordion" id="contactFaq">
                <div class="accordion-item border-0 mb-3 shadow-sm">
                    <h3 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            如何获取技术支持？
                        </button>
                    </h3>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#contactFaq">
                        <div class="accordion-body">
                            您可以通过以下方式获取技术支持：<br>
                            1. 发送邮件至 support@alingai.com<br>
                            2. 在控制面板中提交工单<br>
                            3. 拨打技术支持热线 +86 10 8888 8888 转 2
                        </div>
                    </div>
                </div>
                <div class="accordion-item border-0 mb-3 shadow-sm">
                    <h3 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            如何申请企业合作？
                        </button>
                    </h3>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#contactFaq">
                        <div class="accordion-body">
                            如果您想与我们进行企业合作，请发送邮件至 partnership@alingai.com，或者拨打我们的商务合作热线 +86 10 8888 8888 转 3。我们的商务团队会尽快与您联系。
                        </div>
                    </div>
                </div>
                <div class="accordion-item border-0 shadow-sm">
                    <h3 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            如何加入 AlingAi 团队？
                        </button>
                    </h3>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#contactFaq">
                        <div class="accordion-body">
                            我们一直在寻找优秀的人才加入我们的团队。您可以在我们的<a href="{{ route("careers") }}">招聘页面</a>查看当前的职位空缺，或者发送您的简历至 hr@alingai.com。
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
