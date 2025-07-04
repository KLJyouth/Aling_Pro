
@extends('layouts.app')

@section('title', '会员套餐')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">会员套餐</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="text-center mb-5">
                        <h2>选择适合您的会员套餐</h2>
                        <p class="text-muted">享受更多功能和更好的服务</p>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-md-10">
                            <div class="btn-group btn-group-toggle mb-4 d-flex justify-content-center" data-toggle="buttons">
                                <label class="btn btn-outline-primary active">
                                    <input type="radio" name="subscription-type" id="monthly" autocomplete="off" checked> 月付
                                </label>
                                <label class="btn btn-outline-primary">
                                    <input type="radio" name="subscription-type" id="yearly" autocomplete="off"> 年付 <span class="badge badge-warning">省20%</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row membership-plans">
                        @foreach($levels as $level)
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 {{ $level->is_popular ? 'border-primary' : '' }}">
                                @if($level->is_popular)
                                <div class="ribbon ribbon-top-right"><span>推荐</span></div>
                                @endif
                                <div class="card-header text-center {{ $level->is_popular ? 'bg-primary text-white' : '' }}">
                                    <h4 class="my-0 font-weight-bold">{{ $level->name }}</h4>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <div class="text-center mb-4">
                                        <h1 class="card-title pricing-card-title monthly-price">{{ $level->price_monthly }}<small class="text-muted">/ 月</small></h1>
                                        <h1 class="card-title pricing-card-title yearly-price" style="display: none;">{{ $level->price_yearly }}<small class="text-muted">/ 年</small></h1>
                                    </div>
                                    <p class="card-text">{{ $level->description }}</p>
                                    <ul class="list-unstyled mt-3 mb-4">
                                        <li><i class="fas fa-check-circle text-success mr-2"></i> 每日额度: {{ $level->daily_quota }}</li>
                                        <li><i class="fas fa-check-circle text-success mr-2"></i> 存储空间: {{ $level->storage_limit }}MB</li>
                                        <li><i class="fas fa-check-circle text-success mr-2"></i> 最大文件: {{ $level->max_file_size }}MB</li>
                                        <li><i class="fas fa-check-circle text-success mr-2"></i> 并发请求: {{ $level->concurrent_requests }}</li>
                                        <li>
                                            @if($level->advanced_models_access)
                                                <i class="fas fa-check-circle text-success mr-2"></i> 高级模型访问
                                            @else
                                                <i class="fas fa-times-circle text-danger mr-2"></i> 高级模型访问
                                            @endif
                                        </li>
                                        <li>
                                            @if($level->priority_queue)
                                                <i class="fas fa-check-circle text-success mr-2"></i> 优先队列
                                            @else
                                                <i class="fas fa-times-circle text-danger mr-2"></i> 优先队列
                                            @endif
                                        </li>
                                    </ul>
                                    <div class="mt-auto">
                                        <form action="{{ route('user.membership.subscribe') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="level_id" value="{{ $level->id }}">
                                            <input type="hidden" name="subscription_type" value="monthly" class="subscription-type-input">
                                            <button type="submit" class="btn btn-block {{ $level->is_popular ? 'btn-primary' : 'btn-outline-primary' }}">
                                                立即订阅
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .ribbon {
        width: 150px;
        height: 150px;
        overflow: hidden;
        position: absolute;
    }
    .ribbon span {
        position: absolute;
        display: block;
        width: 225px;
        padding: 8px 0;
        background-color: #3490dc;
        box-shadow: 0 5px 10px rgba(0,0,0,.1);
        color: #fff;
        font-size: 13px;
        text-transform: uppercase;
        text-align: center;
    }
    .ribbon-top-right {
        top: -10px;
        right: -10px;
    }
    .ribbon-top-right span {
        left: -25px;
        top: 30px;
        transform: rotate(45deg);
    }
</style>
@endsection

@section('scripts')
<script>
    $(function() {
        // 切换月付/年付显示
        $('input[name="subscription-type"]').change(function() {
            if ($('#monthly').is(':checked')) {
                $('.monthly-price').show();
                $('.yearly-price').hide();
                $('.subscription-type-input').val('monthly');
            } else {
                $('.monthly-price').hide();
                $('.yearly-price').show();
                $('.subscription-type-input').val('yearly');
            }
        });
    });
</script>
@endsection
