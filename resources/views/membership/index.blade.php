@extends("layouts.app")

@section("title", "我的会员")

@section("content")
<div class="container py-4">
    <h1 class="h3 mb-4">我的会员</h1>
    
    @if(session("success"))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session("success") }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session("error"))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session("error") }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <div class="row">
        <div class="col-lg-8">
            <!-- 当前会员信息 -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="flex-shrink-0">
                            <div class="membership-icon" style="background-color: {{ $currentLevel->color }}">
                                <i class="fas {{ $currentLevel->icon }} fa-lg text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-1">{{ $currentLevel->name }}</h5>
                            <p class="text-muted mb-0">{{ $currentLevel->description }}</p>
                        </div>
                    </div>
                    
                    @if($currentSubscription)
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <small class="text-muted d-block">订阅编号</small>
                                    <span>{{ $currentSubscription->subscription_no }}</span>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted d-block">订阅类型</small>
                                    <span>{{ $currentSubscription->subscription_type === "monthly" ? "月度订阅" : "年度订阅" }}</span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">自动续费</small>
                                    <span>{{ $currentSubscription->auto_renew ? "是" : "否" }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <small class="text-muted d-block">开始日期</small>
                                    <span>{{ $currentSubscription->start_date->format("Y-m-d") }}</span>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted d-block">到期日期</small>
                                    <span>{{ $currentSubscription->end_date->format("Y-m-d") }}</span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">剩余天数</small>
                                    <span>{{ $currentSubscription->end_date->diffInDays(now()) }} 天</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="progress mb-2" style="height: 10px;">
                            @php
                                $totalDays = $currentSubscription->end_date->diffInDays($currentSubscription->start_date);
                                $remainingDays = $currentSubscription->end_date->diffInDays(now());
                                $progressPercent = $totalDays > 0 ? 100 - round(($remainingDays / $totalDays) * 100) : 0;
                            @endphp
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progressPercent }}%" aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between mb-4">
                            <small>{{ $currentSubscription->start_date->format("Y-m-d") }}</small>
                            <small>{{ $currentSubscription->end_date->format("Y-m-d") }}</small>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                @if($currentSubscription->isExpiringSoon())
                                    <div class="alert alert-warning py-2 px-3 mb-0">
                                        <i class="fas fa-exclamation-triangle me-1"></i> 您的会员即将到期
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelSubscriptionModal">
                                    <i class="fas fa-times me-1"></i> 取消订阅
                                </button>
                                <a href="{{ route("subscription.upgrade") }}" class="btn btn-primary">
                                    <i class="fas fa-arrow-up me-1"></i> 升级会员
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="fas fa-crown fa-3x text-muted"></i>
                            </div>
                            <h5>您当前是免费用户</h5>
                            <p class="text-muted mb-4">升级到付费会员，享受更多特权和功能</p>
                            <a href="{{ route("subscription.upgrade") }}" class="btn btn-primary">
                                <i class="fas fa-crown me-1"></i> 升级会员
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- 会员特权 -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">会员特权</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($currentLevel->privileges as $privilege)
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas {{ $privilege->icon }} fa-lg text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0">{{ $privilege->name }}</h6>
                                        <small class="text-muted">{{ $privilege->pivot->value }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- 最近订阅记录 -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">订阅记录</h5>
                    <a href="{{ route("subscription.history") }}" class="btn btn-sm btn-outline-primary">查看全部</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>订阅编号</th>
                                    <th>会员等级</th>
                                    <th>开始日期</th>
                                    <th>结束日期</th>
                                    <th>金额</th>
                                    <th>状态</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscriptionHistory as $subscription)
                                    <tr>
                                        <td>{{ $subscription->subscription_no }}</td>
                                        <td>{{ $subscription->membershipLevel->name }}</td>
                                        <td>{{ $subscription->start_date->format("Y-m-d") }}</td>
                                        <td>{{ $subscription->end_date->format("Y-m-d") }}</td>
                                        <td>￥{{ number_format($subscription->price_paid, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $subscription->status === "active" ? "bg-success" : ($subscription->status === "cancelled" ? "bg-danger" : "bg-secondary") }}">
                                                {{ $subscription->status === "active" ? "有效" : ($subscription->status === "cancelled" ? "已取消" : "已过期") }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <p class="mb-0">暂无订阅记录</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- 会员等级对比 -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">会员等级对比</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>特性</th>
                                    <th>当前等级</th>
                                    <th>更高等级</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>API调用次数</td>
                                    <td>{{ $currentLevel->api_quota == -1 ? "无限" : $currentLevel->api_quota }}/天</td>
                                    <td>无限/天</td>
                                </tr>
                                <tr>
                                    <td>AI模型访问</td>
                                    <td>{{ $currentLevel->getPrivilegeValue("ai_models") }}</td>
                                    <td>所有模型</td>
                                </tr>
                                <tr>
                                    <td>存储空间</td>
                                    <td>{{ round($currentLevel->storage_quota / 1024, 0) }} GB</td>
                                    <td>100 GB</td>
                                </tr>
                                <tr>
                                    <td>优先技术支持</td>
                                    <td>{{ $currentLevel->priority_support ? "是" : "否" }}</td>
                                    <td>是</td>
                                </tr>
                                <tr>
                                    <td>专属功能</td>
                                    <td>{{ $currentLevel->getPrivilegeValue("exclusive_features") ?: "否" }}</td>
                                    <td>是</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-grid mt-3">
                        <a href="{{ route("subscription.upgrade") }}" class="btn btn-primary">
                            <i class="fas fa-arrow-up me-1"></i> 升级会员
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- 常见问题 -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">常见问题</h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    如何取消自动续费？
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    您可以在会员页面点击"取消订阅"按钮，选择取消自动续费选项。取消后，您的会员将在当前订阅期结束后停止。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    如何升级会员等级？
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    点击"升级会员"按钮，选择您想要的会员等级，完成支付后即可立即升级。如果您有现有订阅，系统会自动计算剩余价值并应用到新订阅中。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    会员费用是如何计算的？
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    会员费用根据您选择的会员等级和订阅周期（月度或年度）计算。选择年度订阅可享受更多优惠，通常相当于获得2个月的免费使用期。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    我可以申请退款吗？
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    根据我们的退款政策，如果您在购买后14天内提出申请，并且未使用超过总配额的20%，您可以申请全额退款。请联系客服团队处理退款事宜。
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 取消订阅模态框 -->
<div class="modal fade" id="cancelSubscriptionModal" tabindex="-1" aria-labelledby="cancelSubscriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelSubscriptionModalLabel">取消订阅</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i> 取消订阅后，您将无法继续享受会员特权。您的会员资格将持续到当前订阅期结束。
                </div>
                
                <form action="{{ route("subscription.cancel") }}" method="POST">
                    @csrf
                    
                    <input type="hidden" name="subscription_id" value="{{ $currentSubscription ? $currentSubscription->id : "" }}">
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">取消原因（可选）</label>
                        <select class="form-select" id="reason" name="reason">
                            <option value="">请选择原因...</option>
                            <option value="价格太高">价格太高</option>
                            <option value="功能不符合需求">功能不符合需求</option>
                            <option value="使用频率低">使用频率低</option>
                            <option value="服务质量不满意">服务质量不满意</option>
                            <option value="切换到其他服务">切换到其他服务</option>
                            <option value="临时不需要">临时不需要</option>
                            <option value="其他原因">其他原因</option>
                        </select>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmCancel" required>
                        <label class="form-check-label" for="confirmCancel">
                            我确认要取消我的会员订阅
                        </label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-danger" id="cancelButton" disabled>
                            确认取消
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .membership-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

@section("scripts")
<script>
    // 启用/禁用取消按钮
    document.addEventListener("DOMContentLoaded", function() {
        const confirmCheckbox = document.getElementById("confirmCancel");
        const cancelButton = document.getElementById("cancelButton");
        
        if (confirmCheckbox && cancelButton) {
            confirmCheckbox.addEventListener("change", function() {
                cancelButton.disabled = !this.checked;
            });
        }
    });
</script>
@endsection
@endsection
