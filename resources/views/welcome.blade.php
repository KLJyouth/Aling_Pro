@extends("layouts.app")

@section("title", "AlingAi Pro - �˹�����ƽ̨")

@section("content")
<!-- Ӣ������ -->
<section class="hero bg-primary text-white py-5">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <h1 class="display-4 fw-bold mb-4">�ͷ�AI������Ǳ��</h1>
                <p class="lead mb-4">AlingAi Pro�ṩǿ���AI���ߺ�API�����������ߺ���ҵ����ʵ�����ܻ�Ӧ�ã�����Ч�ʣ������ֵ��</p>
                <div class="d-flex gap-3">
                    <a href="{{ route("register") }}" class="btn btn-light btn-lg">���ע��</a>
                    <a href="{{ route("features") }}" class="btn btn-outline-light btn-lg">�˽����</a>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="{{ asset("images/hero-image.svg") }}" alt="AlingAi Pro" class="img-fluid rounded-3 shadow-lg">
            </div>
        </div>
    </div>
</section>

<!-- �ص����� -->
<section class="features py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">ǿ���ܣ�������</h2>
            <p class="lead text-muted">�����ṩȫ���AI����������������ĸ�������</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-primary bg-gradient text-white rounded-3 p-3 mb-4">
                            <i class="fas fa-brain fa-2x"></i>
                        </div>
                        <h3 class="h4 mb-3">�Ƚ���AIģ��</h3>
                        <p class="text-muted mb-0">�������Ƚ���AIģ�ͣ�������Ȼ���Դ���ͼ��ʶ���Ԥ������ȡ�</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-success bg-gradient text-white rounded-3 p-3 mb-4">
                            <i class="fas fa-code fa-2x"></i>
                        </div>
                        <h3 class="h4 mb-3">�����õ�API</h3>
                        <p class="text-muted mb-0">ͨ�������õ�API�����ٽ�AI���ܼ��ɵ�����Ӧ�ó����С�</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-info bg-gradient text-white rounded-3 p-3 mb-4">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                        <h3 class="h4 mb-3">ʵʱ���ݷ���</h3>
                        <p class="text-muted mb-0">ʵʱ�����������ݣ���ȡ�м�ֵ�ļ��⣬�������������ǵľ��ߡ�</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ��Ա�ƻ� -->
<section class="pricing bg-light py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">ѡ���ʺ����Ļ�Ա�ƻ�</h2>
            <p class="lead text-muted">���Ļ�Ա�ƻ������㲻ͬ��ģ��������û�</p>
        </div>
        
        <div class="row g-4 justify-content-center">
            @foreach($membershipLevels as $level)
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm {{ $level->is_featured ? "border border-primary" : "" }}">
                    @if($level->is_featured)
                    <div class="card-header bg-primary text-white text-center py-3">
                        <span class="badge bg-white text-primary">�Ƽ�</span>
                    </div>
                    @endif
                    <div class="card-body p-4">
                        <h3 class="h4 mb-3">{{ $level->name }}</h3>
                        <div class="d-flex align-items-baseline mb-4">
                            <span class="h2 fw-bold">��{{ $level->formatted_monthly_price }}</span>
                            <span class="text-muted ms-1">/��</span>
                        </div>
                        <ul class="list-unstyled mb-4">
                            @foreach(json_decode($level->benefits) as $benefit)
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i> {{ $benefit }}
                            </li>
                            @endforeach
                        </ul>
                        <div class="d-grid">
                            <a href="{{ route("register") }}" class="btn {{ $level->is_featured ? "btn-primary" : "btn-outline-primary" }}">ѡ��˼ƻ�</a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- �ͻ����� -->
<section class="testimonials py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">�ͻ�������</h2>
            <p class="lead text-muted">�������ǵĿͻ����ʹ��AlingAi Pro</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-4">"AlingAi Pro�������ǽ��ͻ�֧���Զ���������������Ӧ�ٶȺͿͻ�����ȡ�"</p>
                        <div class="d-flex align-items-center">
                            <img src="{{ asset("images/testimonial-1.jpg") }}" alt="�û�ͷ��" class="rounded-circle me-3" width="48">
                            <div>
                                <h5 class="mb-0">����</h5>
                                <p class="text-muted mb-0">�Ƽ���˾CEO</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-4">"ͨ��AlingAi Pro��API�������ܹ����ٿ�������Ӧ�ã���������˲�Ʒ����ʱ�䡣"</p>
                        <div class="d-flex align-items-center">
                            <img src="{{ asset("images/testimonial-2.jpg") }}" alt="�û�ͷ��" class="rounded-circle me-3" width="48">
                            <div>
                                <h5 class="mb-0">�</h5>
                                <p class="text-muted mb-0">�����ܼ�</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-4">"AlingAi Pro�����ݷ������ܰ������Ƿ�����ҵ���е����ػ��ᣬ�������������۶"</p>
                        <div class="d-flex align-items-center">
                            <img src="{{ asset("images/testimonial-3.jpg") }}" alt="�û�ͷ��" class="rounded-circle me-3" width="48">
                            <div>
                                <h5 class="mb-0">����</h5>
                                <p class="text-muted mb-0">Ӫ���ܼ�</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- �ж����� -->
<section class="cta bg-primary text-white py-5">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <h2 class="display-5 fw-bold mb-3">׼���ÿ�ʼ����AI֮������</h2>
                <p class="lead mb-0">����ע�ᣬ�������AlingAi Pro��ǿ���ܡ�</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route("register") }}" class="btn btn-light btn-lg">���ע��</a>
                <a href="{{ route("contact") }}" class="btn btn-outline-light btn-lg ms-2">��ϵ����</a>
            </div>
        </div>
    </div>
</section>
@endsection
