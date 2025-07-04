@extends("layouts.app")

@section("title", "我的额度")

@section("content")
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">我的额度</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light mb-3">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-3">API调用额度</h6>
                                    <h2 class="mb-3">{{ number_format($user->api_quota) }}</h2>
                                    <div class="progress mb-3" style="height: 10px;">
                                        @php
                                            $apiQuotaPercent = $user->api_quota_limit > 0 ? ($user->api_quota / $user->api_quota_limit) * 100 : 0;
                                            $apiQuotaPercent = min($apiQuotaPercent, 100);
                                        @endphp
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $apiQuotaPercent }}%" aria-valuenow="{{ $apiQuotaPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mb-0">
                                        @if($user->api_quota_limit > 0)
                                            已使用 {{ number_format($user->api_quota_used ?? 0) }} / {{ number_format($user->api_quota_limit) }}
                                        @else
                                            无限制
                                        @endif
                                    </p>
                                </div>
                                <div class="card-footer bg-white text-center">
                                    <a href="{{ route("user.billing.packages", ["type" => "api"]) }}" class="btn btn-sm btn-primary">购买额度</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-light mb-3">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-3">AI使用额度</h6>
                                    <h2 class="mb-3">{{ number_format($user->ai_quota) }}</h2>
                                    <div class="progress mb-3" style="height: 10px;">
                                        @php
                                            $aiQuotaPercent = $user->ai_quota_limit > 0 ? ($user->ai_quota / $user->ai_quota_limit) * 100 : 0;
                                            $aiQuotaPercent = min($aiQuotaPercent, 100);
                                        @endphp
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $aiQuotaPercent }}%" aria-valuenow="{{ $aiQuotaPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mb-0">
                                        @if($user->ai_quota_limit > 0)
                                            已使用 {{ number_format($user->ai_quota_used ?? 0) }} / {{ number_format($user->ai_quota_limit) }}
                                        @else
                                            无限制
                                        @endif
                                    </p>
                                </div>
                                <div class="card-footer bg-white text-center">
                                    <a href="{{ route("user.billing.packages", ["type" => "ai"]) }}" class="btn btn-sm btn-success">购买额度</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light mb-3">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-3">存储空间</h6>
                                    <h2 class="mb-3">{{ number_format($user->storage_quota / 1024, 2) }} GB</h2>
                                    <div class="progress mb-3" style="height: 10px;">
                                        @php
                                            $storageQuotaPercent = $user->storage_quota_limit > 0 ? ($user->storage_quota / $user->storage_quota_limit) * 100 : 0;
                                            $storageQuotaPercent = min($storageQuotaPercent, 100);
                                        @endphp
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $storageQuotaPercent }}%" aria-valuenow="{{ $storageQuotaPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mb-0">
                                        @if($user->storage_quota_limit > 0)
                                            已使用 {{ number_format($user->storage_quota_used / 1024, 2) ?? 0 }} / {{ number_format($user->storage_quota_limit / 1024, 2) }} GB
                                        @else
                                            无限制
                                        @endif
                                    </p>
                                </div>
                                <div class="card-footer bg-white text-center">
                                    <a href="{{ route("user.billing.packages", ["type" => "storage"]) }}" class="btn btn-sm btn-warning">购买存储</a>
                                </div>
                            </div>
                        </div>
                    </div>


                    <h5 class="mt-4 mb-3">我的套餐</h5>
                    @if(count($userPackages) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>套餐名称</th>
                                        <th>类型</th>
                                        <th>额度</th>
                                        <th>购买日期</th>
                                        <th>到期日期</th>
                                        <th>状态</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($userPackages as $userPackage)
                                        <tr>
                                            <td>{{ $userPackage->package->name }}</td>
                                            <td>
                                                @switch($userPackage->package->type)
                                                    @case("api")
                                                        <span class="badge badge-info">API调用额度</span>
                                                        @break
                                                    @case("ai")
                                                        <span class="badge badge-success">AI使用额度</span>
                                                        @break
                                                    @case("storage")
                                                        <span class="badge badge-warning">存储空间</span>
                                                        @break
                                                    @case("bandwidth")
                                                        <span class="badge badge-primary">带宽流量</span>
                                                        @break
                                                    @case("comprehensive")
                                                        <span class="badge badge-secondary">综合套餐</span>
                                                        @break
                                                    @default
                                                        <span class="badge badge-secondary">{{ $userPackage->package->type }}</span>
                                                @endswitch
                                            </td>
                                            <td>{{ number_format($userPackage->quota_remaining) }} / {{ number_format($userPackage->quota_total) }}</td>
                                            <td>{{ $userPackage->created_at->format("Y-m-d") }}</td>
                                            <td>
                                                @if($userPackage->expires_at)
                                                    {{ $userPackage->expires_at->format("Y-m-d") }}
                                                    @if($userPackage->expires_at->isPast())
                                                        <span class="badge badge-danger">已过期</span>
                                                    @elseif($userPackage->expires_at->diffInDays(now()) <= 7)
                                                        <span class="badge badge-warning">即将到期</span>
                                                    @endif
                                                @else
                                                    永久有效
                                                @endif
                                            </td>
                                            <td>
                                                @if($userPackage->status === "active")
                                                    <span class="badge badge-success">有效</span>
                                                @elseif($userPackage->status === "expired")
                                                    <span class="badge badge-danger">已过期</span>
                                                @elseif($userPackage->status === "depleted")
                                                    <span class="badge badge-warning">已用完</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ $userPackage->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $userPackages->links() }}
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i> 您还没有购买任何套餐，<a href="{{ route("user.billing.packages") }}">立即购买</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
