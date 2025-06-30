@extends("layouts.app")

@section("title", "�۸�")

@section("content")
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 mb-4">���ļ۸񷽰�</h1>
            <p class="lead text-muted">ѡ�����ʺ�������ķ�������ʼʹ�� AlingAi ǿ��� AI ���ܡ�</p>
        </div>
    </div>
    
    <!-- �۸񷽰� -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <div class="row row-cols-1 row-cols-md-3 g-4">
                @foreach($membershipLevels as $level)
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm {{ $level->is_popular ? "border border-primary" : "" }}">
                        @if($level->is_popular)
                        <div class="card-header bg-primary text-white text-center py-3">
                            <span class="badge bg-white text-primary">�Ƽ�</span>
                        </div>
                        @endif
                        <div class="card-body p-4">
                            <h2 class="h4 card-title text-center mb-4">{{ $level->name }}</h2>
                            <div class="price text-center mb-4">
                                <span class="currency"></span>
                                <span class="amount display-4 fw-bold">{{ number_format($level->monthly_price, 0) }}</span>
                                <span class="period text-muted">/��</span>
                            </div>
                            <p class="text-muted text-center mb-4">{{ $level->description }}</p>
                            <ul class="list-unstyled mb-4">
                                @foreach(json_decode($level->features) as $feature)
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> {{ $feature }}
                                </li>
                                @endforeach
                            </ul>
                            <div class="text-center">
                                @auth
                                <a href="{{ route("membership.subscribe", ["id" => $level->id]) }}" class="btn {{ $level->is_popular ? "btn-primary" : "btn-outline-primary" }} w-100">
                                    ѡ��˷���
                                </a>
                                @else
                                <a href="{{ route("register") }}" class="btn {{ $level->is_popular ? "btn-primary" : "btn-outline-primary" }} w-100">
                                    ע�Ტѡ��
                                </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- ���ܶԱ� -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <h2 class="h3 mb-4 text-center">���ܶԱ�</h2>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>����</th>
                            @foreach($membershipLevels as $level)
                            <th class="text-center">{{ $level->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>API ���ô���</td>
                            @foreach($membershipLevels as $level)
                            <td class="text-center">{{ number_format($level->api_calls) }}/��</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>����������</td>
                            @foreach($membershipLevels as $level)
                            <td class="text-center">{{ $level->concurrent_requests }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>�洢�ռ�</td>
                            @foreach($membershipLevels as $level)
                            <td class="text-center">{{ $level->storage_space }}GB</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>�߼�ģ��</td>
                            @foreach($membershipLevels as $level)
                            <td class="text-center">
                                @if($level->has_advanced_models)
                                <i class="fas fa-check text-success"></i>
                                @else
                                <i class="fas fa-times text-danger"></i>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>�Զ���ѵ��</td>
                            @foreach($membershipLevels as $level)
                            <td class="text-center">
                                @if($level->has_custom_training)
                                <i class="fas fa-check text-success"></i>
                                @else
                                <i class="fas fa-times text-danger"></i>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>��������</td>
                            @foreach($membershipLevels as $level)
                            <td class="text-center">
                                @if($level->has_batch_processing)
                                <i class="fas fa-check text-success"></i>
                                @else
                                <i class="fas fa-times text-danger"></i>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>����֧��</td>
                            @foreach($membershipLevels as $level)
                            <td class="text-center">
                                @if($level->has_priority_support)
                                <i class="fas fa-check text-success"></i>
                                @else
                                <i class="fas fa-times text-danger"></i>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>ר������֧��</td>
                            @foreach($membershipLevels as $level)
                            <td class="text-center">
                                @if($level->has_dedicated_support)
                                <i class="fas fa-check text-success"></i>
                                @else
                                <i class="fas fa-times text-danger"></i>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- �������� -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <h2 class="h3 mb-4 text-center">��������</h2>
            <div class="accordion" id="pricingFaq">
                <div class="accordion-item border-0 mb-3 shadow-sm">
                    <h3 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            ���ѡ���ʺ��ҵķ�����
                        </button>
                    </h3>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#pricingFaq">
                        <div class="accordion-body">
                            ѡ�񷽰�ʱ������Ҫ��������ʹ�����󣬰��� API ���ô������������������洢�ռ�ȡ�������ǳ���ʹ�ã�������ѡ����ѷ����������飬�������������������������Ҫ���������������ϵ���ǵĿͷ��Ŷӣ����ǻ�Ϊ���ṩרҵ�Ľ��顣
                        </div>
                    </div>
                </div>
                <div class="accordion-item border-0 mb-3 shadow-sm">
                    <h3 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            ��μ��� API ���ô�����
                        </button>
                    </h3>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#pricingFaq">
                        <div class="accordion-body">
                            ÿ�ε������ǵ� API �ӿڶ������Ϊһ�� API ���á���ͬ�� API �ӿڿ��ܻ����Ĳ�ͬ�ĵ��ô������������������ο����ǵ� API �ĵ����������ڿ�������в鿴���� API ����ʹ�������
                        </div>
                    </div>
                </div>
                <div class="accordion-item border-0 mb-3 shadow-sm">
                    <h3 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            ��������򽵼��ҵķ�����
                        </button>
                    </h3>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#pricingFaq">
                        <div class="accordion-body">
                            ��������ʱ�ڿ�������еĻ�Ա����ҳ�������򽵼����ķ������������µķ�����������Ч����������������á��������µķ������ڵ�ǰ�Ʒ����ڽ�������Ч��
                        </div>
                    </div>
                </div>
                <div class="accordion-item border-0 mb-3 shadow-sm">
                    <h3 class="accordion-header" id="headingFour">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                            �Ƿ�֧���긶������
                        </button>
                    </h3>
                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#pricingFaq">
                        <div class="accordion-body">
                            �ǵģ�����֧���긶���������ṩһ�����Żݡ��긶������������ 10 ���µļ۸�ʹ�� 12 ���µķ������������Ȥ��������ѡ�񷽰�ʱ�л����긶ѡ�
                        </div>
                    </div>
                </div>
                <div class="accordion-item border-0 shadow-sm">
                    <h3 class="accordion-header" id="headingFive">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                            �Ƿ��ṩ��ҵ���Ʒ�����
                        </button>
                    </h3>
                    <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#pricingFaq">
                        <div class="accordion-body">
                            �ǵģ�����Ϊ��ҵ�ͻ��ṩ���ƻ��Ľ�������ͼ۸񷽰����������������������ϵ���ǵ������Ŷӣ����ǻ�Ϊ�����������ʺϵķ�����
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ��ҵ���� -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body p-5 text-center">
                    <h2 class="h3 mb-4">��Ҫ��ҵ���Ʒ�����</h2>
                    <p class="mb-4">����Ϊ��ҵ�ͻ��ṩ���ƻ��Ľ�������ͼ۸񷽰�������������������</p>
                    <a href="{{ route("contact") }}" class="btn btn-primary">
                        <i class="fas fa-envelope me-1"></i> ��ϵ����
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .price .currency {
        font-size: 1.5rem;
        position: relative;
        top: -1.5rem;
    }
    .price .period {
        font-size: 1rem;
    }
</style>
@endsection
