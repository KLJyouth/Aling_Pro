@extends("layouts.app")

@section("title", "��ϵ����")

@section("content")
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 mb-4">��ϵ����</h1>
            <p class="lead text-muted">��������κ�������飬��ӭ��ʱ��ϵ���ǡ�</p>
        </div>
    </div>
    
    <div class="row">
        <!-- ��ϵ��ʽ -->
        <div class="col-lg-4 mb-4 mb-lg-0">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h4 mb-4">��ϵ��ʽ</h2>
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="h6 mb-1">��ַ</h3>
                            <p class="mb-0">�����к������йش��ϴ��5��</p>
                        </div>
                    </div>
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-phone-alt fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="h6 mb-1">�绰</h3>
                            <p class="mb-0">+86 10 8888 8888</p>
                        </div>
                    </div>
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-envelope fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="h6 mb-1">����</h3>
                            <p class="mb-0">contact@alingai.com</p>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="h6 mb-1">����ʱ��</h3>
                            <p class="mb-0">��һ������: 9:00 - 18:00</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ��ϵ�� -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h4 mb-4">����������</h2>
                    
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
                                <label for="name" class="form-label">����</label>
                                <input type="text" class="form-control @error("name") is-invalid @enderror" id="name" name="name" value="{{ old("name") }}" required>
                                @error("name")
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">����</label>
                                <input type="email" class="form-control @error("email") is-invalid @enderror" id="email" name="email" value="{{ old("email") }}" required>
                                @error("email")
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">����</label>
                            <input type="text" class="form-control @error("subject") is-invalid @enderror" id="subject" name="subject" value="{{ old("subject") }}" required>
                            @error("subject")
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">��Ϣ</label>
                            <textarea class="form-control @error("message") is-invalid @enderror" id="message" name="message" rows="5" required>{{ old("message") }}</textarea>
                            @error("message")
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input @error("privacy") is-invalid @enderror" type="checkbox" id="privacy" name="privacy" required>
                                <label class="form-check-label" for="privacy">
                                    ��ͬ�����<a href="{{ route("privacy") }}" target="_blank">��˽����</a>�����ҵĸ�������
                                </label>
                                @error("privacy")
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> ������Ϣ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ��ͼ -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3039.0588500880316!2d116.31860911744384!3d39.9878555!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x35f05b1af04245df%3A0x3fa6d48cae12b231!2z5Lit5YWD5p2R5Y2X5aSn6KGX!5e0!3m2!1szh-CN!2scn!4v1593500124732!5m2!1szh-CN!2scn" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>
    
    <!-- �������� -->
    <div class="row mt-5">
        <div class="col-lg-8 mx-auto">
            <h2 class="h3 mb-4 text-center">��������</h2>
            <div class="accordion" id="contactFaq">
                <div class="accordion-item border-0 mb-3 shadow-sm">
                    <h3 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            ��λ�ȡ����֧�֣�
                        </button>
                    </h3>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#contactFaq">
                        <div class="accordion-body">
                            ������ͨ�����·�ʽ��ȡ����֧�֣�<br>
                            1. �����ʼ��� support@alingai.com<br>
                            2. �ڿ���������ύ����<br>
                            3. ������֧������ +86 10 8888 8888 ת 2
                        </div>
                    </div>
                </div>
                <div class="accordion-item border-0 mb-3 shadow-sm">
                    <h3 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            ���������ҵ������
                        </button>
                    </h3>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#contactFaq">
                        <div class="accordion-body">
                            ������������ǽ�����ҵ�������뷢���ʼ��� partnership@alingai.com�����߲������ǵ������������ +86 10 8888 8888 ת 3�����ǵ������Ŷӻᾡ��������ϵ��
                        </div>
                    </div>
                </div>
                <div class="accordion-item border-0 shadow-sm">
                    <h3 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            ��μ��� AlingAi �Ŷӣ�
                        </button>
                    </h3>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#contactFaq">
                        <div class="accordion-body">
                            ����һֱ��Ѱ��������˲ż������ǵ��Ŷӡ������������ǵ�<a href="{{ route("careers") }}">��Ƹҳ��</a>�鿴��ǰ��ְλ��ȱ�����߷������ļ����� hr@alingai.com��
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
