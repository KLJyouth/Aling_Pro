@extends("layouts.app")

@section("title", "������ʷ")

@section("content")
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">������ʷ</h1>
        <a href="{{ route("subscription") }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> ���ػ�Աҳ��
        </a>
    </div>
    
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>���ı��</th>
                            <th>��Ա�ȼ�</th>
                            <th>��������</th>
                            <th>��ʼ����</th>
                            <th>��������</th>
                            <th>���</th>
                            <th>״̬</th>
                            <th>����</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptions as $subscription)
                            <tr>
                                <td>{{ $subscription->subscription_no }}</td>
                                <td>{{ $subscription->membershipLevel->name }}</td>
                                <td>{{ $subscription->subscription_type === "monthly" ? "�¶�" : "���" }}</td>
                                <td>{{ $subscription->start_date->format("Y-m-d") }}</td>
                                <td>{{ $subscription->end_date->format("Y-m-d") }}</td>
                                <td>��{{ number_format($subscription->price_paid, 2) }}</td>
                                <td>
                                    <span class="badge {{ $subscription->status === "active" ? "bg-success" : ($subscription->status === "cancelled" ? "bg-danger" : "bg-secondary") }}">
                                        {{ $subscription->status === "active" ? "��Ч" : ($subscription->status === "cancelled" ? "��ȡ��" : "�ѹ���") }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#subscriptionModal{{ $subscription->id }}">
                                        <i class="fas fa-eye"></i> ����
                                    </button>
                                    
                                    <!-- ��������ģ̬�� -->
                                    <div class="modal fade" id="subscriptionModal{{ $subscription->id }}" tabindex="-1" aria-labelledby="subscriptionModalLabel{{ $subscription->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="subscriptionModalLabel{{ $subscription->id }}">��������</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <table class="table table-borderless">
                                                        <tr>
                                                            <th>���ı��</th>
                                                            <td>{{ $subscription->subscription_no }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>��Ա�ȼ�</th>
                                                            <td>{{ $subscription->membershipLevel->name }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>��������</th>
                                                            <td>{{ $subscription->subscription_type === "monthly" ? "�¶�" : "���" }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>��ʼ����</th>
                                                            <td>{{ $subscription->start_date->format("Y-m-d H:i:s") }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>��������</th>
                                                            <td>{{ $subscription->end_date->format("Y-m-d H:i:s") }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>���</th>
                                                            <td>��{{ number_format($subscription->price_paid, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>�Զ�����</th>
                                                            <td>{{ $subscription->auto_renew ? "��" : "��" }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>״̬</th>
                                                            <td>
                                                                <span class="badge {{ $subscription->status === "active" ? "bg-success" : ($subscription->status === "cancelled" ? "bg-danger" : "bg-secondary") }}">
                                                                    {{ $subscription->status === "active" ? "��Ч" : ($subscription->status === "cancelled" ? "��ȡ��" : "�ѹ���") }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        @if($subscription->cancelled_at)
                                                            <tr>
                                                                <th>ȡ������</th>
                                                                <td>{{ $subscription->cancelled_at->format("Y-m-d H:i:s") }}</td>
                                                            </tr>
                                                        @endif
                                                        @if($subscription->cancellation_reason)
                                                            <tr>
                                                                <th>ȡ��ԭ��</th>
                                                                <td>{{ $subscription->cancellation_reason }}</td>
                                                            </tr>
                                                        @endif
                                                    </table>
                                                    
                                                    <div class="mt-3">
                                                        <h6>��Ա��Ȩ</h6>
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
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">�ر�</button>
                                                    @if($subscription->status === "active")
                                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelSubscriptionModal{{ $subscription->id }}">
                                                            ȡ������
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- ȡ������ģ̬�� -->
                                    @if($subscription->status === "active")
                                        <div class="modal fade" id="cancelSubscriptionModal{{ $subscription->id }}" tabindex="-1" aria-labelledby="cancelSubscriptionModalLabel{{ $subscription->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="cancelSubscriptionModalLabel{{ $subscription->id }}">ȡ������</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning">
                                                            <i class="fas fa-exclamation-triangle me-2"></i> ȡ�����ĺ������޷��������ܻ�Ա��Ȩ�����Ļ�Ա�ʸ񽫳�������ǰ�����ڽ�����
                                                        </div>
                                                        
                                                        <form action="{{ route("subscription.cancel") }}" method="POST">
                                                            @csrf
                                                            
                                                            <input type="hidden" name="subscription_id" value="{{ $subscription->id }}">
                                                            
                                                            <div class="mb-3">
                                                                <label for="reason{{ $subscription->id }}" class="form-label">ȡ��ԭ�򣨿�ѡ��</label>
                                                                <select class="form-select" id="reason{{ $subscription->id }}" name="reason">
                                                                    <option value="">��ѡ��ԭ��...</option>
                                                                    <option value="�۸�̫��">�۸�̫��</option>
                                                                    <option value="���ܲ���������">���ܲ���������</option>
                                                                    <option value="ʹ��Ƶ�ʵ�">ʹ��Ƶ�ʵ�</option>
                                                                    <option value="��������������">��������������</option>
                                                                    <option value="�л�����������">�л�����������</option>
                                                                    <option value="��ʱ����Ҫ">��ʱ����Ҫ</option>
                                                                    <option value="����ԭ��">����ԭ��</option>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="form-check mb-3">
                                                                <input class="form-check-input" type="checkbox" id="confirmCancel{{ $subscription->id }}" required>
                                                                <label class="form-check-label" for="confirmCancel{{ $subscription->id }}">
                                                                    ��ȷ��Ҫȡ���ҵĻ�Ա����
                                                                </label>
                                                            </div>
                                                            
                                                            <div class="d-grid">
                                                                <button type="submit" class="btn btn-danger" id="cancelButton{{ $subscription->id }}" disabled>
                                                                    ȷ��ȡ��
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
                                    <p class="mb-0">���޶��ļ�¼</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- ��ҳ -->
            <div class="d-flex justify-content-center mt-4">
                {{ $subscriptions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
