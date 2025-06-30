@extends("layouts.app")

@section("title", "用户仪表盘")

@section("styles")
<style>
    .dashboard-card {
        transition: all 0.3s ease;
    }
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .progress {
        height: 10px;
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }
</style>
@endsection

@section("content")
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">欢迎回来，{{ $user->name }}</h1>
            <p class="text-muted">这是您的个人仪表盘，您可以在这里管理您的账户和查看使用情况。</p>
        </div>
        <div class="col-md-4 text-md-end">
            @if($subscription)
                <div class="mb-2">
                    <span class="badge bg-success">{{ $membershipLevel->name }} 会员</span>
                    <span class="text-muted">有效期至 {{ $subscription->end_date->format("Y-m-d") }}</span>
                </div>
                @if($subscription->isExpiringSoon())
                    <a href="{{ route("subscription.upgrade") }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-sync-alt me-1"></i> 续费会员
                    </a>
                @endif
            @else
                <a href="{{ route("subscription.upgrade") }}" class="btn btn-primary">
                    <i class="fas fa-crown me-1"></i> 升级会员
                </a>
            @endif
        </div>
    </div>
    
    <!-- 使用统计卡片 -->
    <div class="row g-4 mb-4">
        <!-- API使用情况 -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm dashboard-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stat-icon bg-primary text-white me-3">
                            <i class="fas fa-code fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">API使用情况</h6>
                            <small class="text-muted">今日使用</small>
                        </div>
                    </div>
                    <h3 class="mb-2">{{ $apiUsageToday }} / {{ $apiQuota == -1 ? "无限" : $apiQuota }}</h3>
                    <div class="progress mb-2">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $apiUsagePercent }}%" aria-valuenow="{{ $apiUsagePercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted">已使用 {{ $apiUsagePercent }}%</small>
                </div>
            </div>
        </div>
        
        <!-- AI使用情况 -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm dashboard-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stat-icon bg-success text-white me-3">
                            <i class="fas fa-robot fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">AI使用情况</h6>
                            <small class="text-muted">今日使用</small>
                        </div>
                    </div>
                    <h3 class="mb-2">{{ $aiUsageToday }} / {{ $aiQuota == -1 ? "无限" : $aiQuota }}</h3>
                    <div class="progress mb-2">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $aiUsagePercent }}%" aria-valuenow="{{ $aiUsagePercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted">已使用 {{ $aiUsagePercent }}%</small>
                </div>
            </div>
        </div>
        
        <!-- 存储使用情况 -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm dashboard-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stat-icon bg-info text-white me-3">
                            <i class="fas fa-hdd fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">存储使用情况</h6>
                            <small class="text-muted">总计</small>
                        </div>
                    </div>
                    <h3 class="mb-2">{{ round($storageUsed / 1024, 2) }} GB / {{ round($storageQuota / 1024, 2) }} GB</h3>
                    <div class="progress mb-2">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $storageUsagePercent }}%" aria-valuenow="{{ $storageUsagePercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted">已使用 {{ $storageUsagePercent }}%</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- 使用趋势图表 -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">使用趋势</h5>
                </div>
                <div class="card-body">
                    <canvas id="usageTrendChart" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <!-- 会员积分 -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">会员积分</h5>
                    <a href="{{ route("profile") }}#points" class="btn btn-sm btn-outline-primary">查看全部</a>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h2 class="mb-0">{{ $pointsStats["available"] }}</h2>
                        <small class="text-muted">可用积分</small>
                    </div>
                    <div class="row text-center">
                        <div class="col">
                            <h5>{{ $pointsStats["total_earned"] }}</h5>
                            <small class="text-muted">累计获得</small>
                        </div>
                        <div class="col">
                            <h5>{{ $pointsStats["total_consumed"] }}</h5>
                            <small class="text-muted">已使用</small>
                        </div>
                        <div class="col">
                            <h5>{{ $pointsStats["expired"] }}</h5>
                            <small class="text-muted">已过期</small>
                        </div>
                    </div>
                    
                    @if(count($recentPoints) > 0)
                        <hr>
                        <h6>最近积分记录</h6>
                        <ul class="list-group list-group-flush">
                            @foreach($recentPoints as $point)
                                <li class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="mb-0">{{ $point->description }}</p>
                                            <small class="text-muted">{{ $point->created_at->format("Y-m-d H:i") }}</small>
                                        </div>
                                        <span class="{{ $point->points > 0 ? "text-success" : "text-danger" }}">
                                            {{ $point->points > 0 ? "+" : "" }}{{ $point->points }}
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- 推荐信息 -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">推荐计划</h5>
                    <a href="{{ route("profile") }}#referrals" class="btn btn-sm btn-outline-primary">管理推荐</a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4 text-center mb-3 mb-md-0">
                            <h3>{{ $referralStats["total"] }}</h3>
                            <small class="text-muted">总推荐人数</small>
                        </div>
                        <div class="col-md-4 text-center mb-3 mb-md-0">
                            <h3>{{ $referralStats["completed"] }}</h3>
                            <small class="text-muted">成功推荐</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <h3>{{ $referralStats["points"] }}</h3>
                            <small class="text-muted">获得积分</small>
                        </div>
                    </div>
                    
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" value="{{ $referralLink }}" id="referralLink" readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="copyReferralLink()">
                            <i class="fas fa-copy"></i> 复制
                        </button>
                    </div>
                    
                    <div class="d-grid">
                        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#shareReferralModal">
                            <i class="fas fa-share-alt me-1"></i> 分享推荐链接
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 最近API调用 -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">最近API调用</h5>
                    <a href="{{ route("api-keys") }}" class="btn btn-sm btn-outline-primary">管理API密钥</a>
                </div>
                <div class="card-body">
                    @if(count($recentApiCalls) > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>端点</th>
                                        <th>状态</th>
                                        <th>响应时间</th>
                                        <th>时间</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentApiCalls as $call)
                                        <tr>
                                            <td>{{ Str::limit($call->endpoint, 30) }}</td>
                                            <td>
                                                <span class="badge {{ $call->status_code >= 200 && $call->status_code < 300 ? "bg-success" : "bg-danger" }}">
                                                    {{ $call->status_code }}
                                                </span>
                                            </td>
                                            <td>{{ $call->response_time }}ms</td>
                                            <td>{{ $call->created_at->format("m-d H:i") }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-code fa-3x text-muted mb-3"></i>
                            <p>您还没有API调用记录</p>
                            <a href="{{ route("api-docs") }}" class="btn btn-sm btn-primary">查看API文档</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 分享推荐链接模态框 -->
<div class="modal fade" id="shareReferralModal" tabindex="-1" aria-labelledby="shareReferralModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shareReferralModalLabel">分享推荐链接</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>分享您的推荐链接，邀请好友注册，双方都将获得奖励！</p>
                <div class="d-grid gap-2">
                    <a href="https://twitter.com/intent/tweet?text=加入AlingAi Pro，使用我的推荐链接获得奖励：{{ urlencode($referralLink) }}" target="_blank" class="btn btn-outline-primary">
                        <i class="fab fa-twitter me-2"></i> 分享到Twitter
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($referralLink) }}" target="_blank" class="btn btn-outline-primary">
                        <i class="fab fa-facebook me-2"></i> 分享到Facebook
                    </a>
                    <a href="https://api.whatsapp.com/send?text=加入AlingAi Pro，使用我的推荐链接获得奖励：{{ urlencode($referralLink) }}" target="_blank" class="btn btn-outline-primary">
                        <i class="fab fa-whatsapp me-2"></i> 分享到WhatsApp
                    </a>
                    <a href="mailto:?subject=邀请您加入AlingAi Pro&body=您好，我想邀请您加入AlingAi Pro平台。使用我的推荐链接注册，我们双方都将获得奖励：{{ $referralLink }}" class="btn btn-outline-primary">
                        <i class="fas fa-envelope me-2"></i> 通过邮件分享
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 复制推荐链接
    function copyReferralLink() {
        var copyText = document.getElementById("referralLink");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        
        // 显示提示
        alert("推荐链接已复制到剪贴板！");
    }
    
    // 使用趋势图表
    document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById("usageTrendChart").getContext("2d");
        
        var usageTrendChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: @json($usageTrend["dates"]),
                datasets: [
                    {
                        label: "API调用",
                        data: @json($usageTrend["api"]),
                        borderColor: "#3498db",
                        backgroundColor: "rgba(52, 152, 219, 0.1)",
                        borderWidth: 2,
                        pointBackgroundColor: "#3498db",
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: "AI使用",
                        data: @json($usageTrend["ai"]),
                        borderColor: "#2ecc71",
                        backgroundColor: "rgba(46, 204, 113, 0.1)",
                        borderWidth: 2,
                        pointBackgroundColor: "#2ecc71",
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "top",
                    },
                    tooltip: {
                        mode: "index",
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
