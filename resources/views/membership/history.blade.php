@extends("layouts.app")

@section("title", "订阅历史")

@section("content")
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">订阅历史</h1>
        <a href="{{ route("subscription") }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> 返回会员页面
        </a>
    </div>
    
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>订阅编号</th>
                            <th>会员等级</th>
                            <th>订阅类型</th>
                            <th>开始日期</th>
                            <th>结束日期</th>
                            <th>金额</th>
                            <th>状态</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptions as $subscription)
                            <tr>
                                <td>{{ $subscription->subscription_no }}</td>
                                <td>{{ $subscription->membershipLevel->name }}</td>
                                <td>{{ $subscription->subscription_type === "monthly" ? "月度" : "年度" }}</td>
                                <td>{{ $subscription->start_date->format("Y-m-d") }}</td>
                                <td>{{ $subscription->end_date->format("Y-m-d") }}</td>
                                <td>￥{{ number_format($subscription->price_paid, 2) }}</td>
                                <td>
                                    <span class="badge {{ $subscription->status === "active" ? "bg-success" : ($subscription->status === "cancelled" ? "bg-danger" : "bg-secondary") }}">
                                        {{ $subscription->status === "active" ? "有效" : ($subscription->status === "cancelled" ? "已取消" : "已过期") }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#subscriptionModal{{ $subscription->id }}">
                                        <i class="fas fa-eye"></i> 详情
                                    </button>
                                    
                                    <!-- 订阅详情模态框 -->
                                    <div class="modal fade" id="subscriptionModal{{ $subscription->id }}" tabindex="-1" aria-labelledby="subscriptionModalLabel{{ $subscription->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="subscriptionModalLabel{{ $subscription->id }}">订阅详情</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <table class="table table-borderless">
                                                        <tr>
                                                            <th>订阅编号</th>
                                                            <td>{{ $subscription->subscription_no }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>会员等级</th>
                                                            <td>{{ $subscription->membershipLevel->name }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>订阅类型</th>
                                                            <td>{{ $subscription->subscription_type === "monthly" ? "月度" : "年度" }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>开始日期</th>
                                                            <td>{{ $subscription->start_date->format("Y-m-d H:i:s") }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>结束日期</th>
                                                            <td>{{ $subscription->end_date->format("Y-m-d H:i:s") }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>金额</th>
                                                            <td>￥{{ number_format($subscription->price_paid, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>自动续费</th>
                                                            <td>{{ $subscription->auto_renew ? "是" : "否" }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>状态</th>
                                                            <td>
                                                                <span class="badge {{ $subscription->status === "active" ? "bg-success" : ($subscription->status === "cancelled" ? "bg-danger" : "bg-secondary") }}">
                                                                    {{ $subscription->status === "active" ? "有效" : ($subscription->status === "cancelled" ? "已取消" : "已过期") }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        @if($subscription->cancelled_at)
                                                            <tr>
                                                                <th>取消日期</th>
                                                                <td>{{ $subscription->cancelled_at->format("Y-m-d H:i:s") }}</td>
                                                            </tr>
                                                        @endif
                                                        @if($subscription->cancellation_reason)
                                                            <tr>
                                                                <th>取消原因</th>
                                                                <td>{{ $subscription->cancellation_reason }}</td>
                                                            </tr>
                                                        @endif
                                                    </table>
                                                    
                                                    <div class="mt-3">
                                                        <h6>会员特权</h6>
                                                        <ul class="list-group list-group-flush">
                                                            @foreach($subscription->membershipLevel->privileges as $privilege)
                                                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                                                    <span>{{ $privilege->name }}</span>
                                                                    <span>{{ $privilege->pivot->value }}</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                                                    @if($subscription->status === "active")
                                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelSubscriptionModal{{ $subscription->id }}">
                                                            取消订阅
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- 取消订阅模态框 -->
                                    @if($subscription->status === "active")
                                        <div class="modal fade" id="cancelSubscriptionModal{{ $subscription->id }}" tabindex="-1" aria-labelledby="cancelSubscriptionModalLabel{{ $subscription->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="cancelSubscriptionModalLabel{{ $subscription->id }}">取消订阅</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning">
                                                            <i class="fas fa-exclamation-triangle me-2"></i> 取消订阅后，您将无法继续享受会员特权。您的会员资格将持续到当前订阅期结束。
                                                        </div>
                                                        
                                                        <form action="{{ route("subscription.cancel") }}" method="POST">
                                                            @csrf
                                                            
                                                            <input type="hidden" name="subscription_id" value="{{ $subscription->id }}">
                                                            
                                                            <div class="mb-3">
                                                                <label for="reason{{ $subscription->id }}" class="form-label">取消原因（可选）</label>
                                                                <select class="form-select" id="reason{{ $subscription->id }}" name="reason">
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
                                                                <input class="form-check-input" type="checkbox" id="confirmCancel{{ $subscription->id }}" required>
                                                                <label class="form-check-label" for="confirmCancel{{ $subscription->id }}">
                                                                    我确认要取消我的会员订阅
                                                                </label>
                                                            </div>
                                                            
                                                            <div class="d-grid">
                                                                <button type="submit" class="btn btn-danger" id="cancelButton{{ $subscription->id }}" disabled>
                                                                    确认取消
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <script>
                                            document.addEventListener("DOMContentLoaded", function() {
                                                const confirmCheckbox{{ $subscription->id }} = document.getElementById("confirmCancel{{ $subscription->id }}");
                                                const cancelButton{{ $subscription->id }} = document.getElementById("cancelButton{{ $subscription->id }}");
                                                
                                                if (confirmCheckbox{{ $subscription->id }} && cancelButton{{ $subscription->id }}) {
                                                    confirmCheckbox{{ $subscription->id }}.addEventListener("change", function() {
                                                        cancelButton{{ $subscription->id }}.disabled = !this.checked;
                                                    });
                                                }
                                            });
                                        </script>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <p class="mb-0">暂无订阅记录</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- 分页 -->
            <div class="d-flex justify-content-center mt-4">
                {{ $subscriptions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
