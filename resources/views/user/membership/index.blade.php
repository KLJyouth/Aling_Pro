
@extends('layouts.app')

@section('title', '会员中心')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">会员中心</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header">当前会员</div>
                                <div class="card-body text-center">
                                    @if($currentSubscription)
                                        <h4>{{ $currentSubscription->membershipLevel->name }}</h4>
                                        <p class="text-muted">{{ $currentSubscription->membershipLevel->description }}</p>
                                        
                                        <div class="my-3">
                                            @if($currentSubscription->subscription_type == 'monthly')
                                                <span class="badge badge-info">月付会员</span>
                                            @elseif($currentSubscription->subscription_type == 'yearly')
                                                <span class="badge badge-primary">年付会员</span>
                                            @endif
                                        </div>
                                        
                                        <div class="mb-3">
                                            <p>到期时间: {{ $currentSubscription->end_date }}</p>
                                            <div class="progress">
                                                @php
                                                    $startDate = new DateTime($currentSubscription->start_date);
                                                    $endDate = new DateTime($currentSubscription->end_date);
                                                    $now = new DateTime();
                                                    $totalDays = $startDate->diff($endDate)->days;
                                                    $usedDays = $startDate->diff($now)->days;
                                                    $percentage = min(100, round(($usedDays / $totalDays) * 100));
                                                @endphp
                                                <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%;" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">{{ $percentage }}%</div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-4">
                                            <a href="{{ route('user.membership.renew') }}" class="btn btn-primary">续费会员</a>
                                            @if($currentSubscription->auto_renew)
                                                <form action="{{ route('user.membership.cancel-auto-renew') }}" method="POST" class="mt-2">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">取消自动续费</button>
                                                </form>
                                            @else
                                                <form action="{{ route('user.membership.enable-auto-renew') }}" method="POST" class="mt-2">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success btn-sm">开启自动续费</button>
                                                </form>
                                            @endif
                                        </div>
                                    @else
                                        <p>您还不是会员</p>
                                        <a href="{{ route('user.membership.plans') }}" class="btn btn-primary">立即开通</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card mb-4">
                                <div class="card-header">会员权益</div>
                                <div class="card-body">
                                    @if($currentSubscription)
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <th>每日额度</th>
                                                    <td>{{ $currentSubscription->membershipLevel->daily_quota }}</td>
                                                </tr>
                                                <tr>
                                                    <th>存储空间</th>
                                                    <td>{{ $currentSubscription->membershipLevel->storage_limit }} MB</td>
                                                </tr>
                                                <tr>
                                                    <th>最大文件大小</th>
                                                    <td>{{ $currentSubscription->membershipLevel->max_file_size }} MB</td>
                                                </tr>
                                                <tr>
                                                    <th>并发请求数</th>
                                                    <td>{{ $currentSubscription->membershipLevel->concurrent_requests }}</td>
                                                </tr>
                                                <tr>
                                                    <th>高级模型访问</th>
                                                    <td>
                                                        @if($currentSubscription->membershipLevel->advanced_models_access)
                                                            <span class="text-success"><i class="fas fa-check"></i> 允许</span>
                                                        @else
                                                            <span class="text-danger"><i class="fas fa-times"></i> 不允许</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>优先队列</th>
                                                    <td>
                                                        @if($currentSubscription->membershipLevel->priority_queue)
                                                            <span class="text-success"><i class="fas fa-check"></i> 是</span>
                                                        @else
                                                            <span class="text-danger"><i class="fas fa-times"></i> 否</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <p>开通会员可享受更多权益：</p>
                                            <ul>
                                                <li>更多的每日使用额度</li>
                                                <li>更大的存储空间</li>
                                                <li>访问高级AI模型</li>
                                                <li>优先处理请求</li>
                                                <li>专属客户服务</li>
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header">订阅记录</div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>会员等级</th>
                                                    <th>订阅类型</th>
                                                    <th>开始时间</th>
                                                    <th>结束时间</th>
                                                    <th>状态</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($subscriptionHistory as $subscription)
                                                <tr>
                                                    <td>{{ $subscription->membershipLevel->name }}</td>
                                                    <td>
                                                        @if($subscription->subscription_type == 'monthly')
                                                            月付
                                                        @elseif($subscription->subscription_type == 'yearly')
                                                            年付
                                                        @else
                                                            {{ $subscription->subscription_type }}
                                                        @endif
                                                    </td>
                                                    <td>{{ $subscription->start_date }}</td>
                                                    <td>{{ $subscription->end_date }}</td>
                                                    <td>
                                                        @if($subscription->status == 'active')
                                                            <span class="badge badge-success">活跃</span>
                                                        @elseif($subscription->status == 'expired')
                                                            <span class="badge badge-danger">已过期</span>
                                                        @elseif($subscription->status == 'cancelled')
                                                            <span class="badge badge-warning">已取消</span>
                                                        @else
                                                            <span class="badge badge-secondary">{{ $subscription->status }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">暂无订阅记录</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
