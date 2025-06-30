@extends("layouts.app")

@section("title", "�ҵĻ�Ա")

@section("content")
<div class="container py-4">
    <h1 class="h3 mb-4">�ҵĻ�Ա</h1>
    
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
            <!-- ��ǰ��Ա��Ϣ -->
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
                                    <small class="text-muted d-block">���ı��</small>
                                    <span>{{ $currentSubscription->subscription_no }}</span>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted d-block">��������</small>
                                    <span>{{ $currentSubscription->subscription_type === "monthly" ? "�¶ȶ���" : "��ȶ���" }}</span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">�Զ�����</small>
                                    <span>{{ $currentSubscription->auto_renew ? "��" : "��" }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <small class="text-muted d-block">��ʼ����</small>
                                    <span>{{ $currentSubscription->start_date->format("Y-m-d") }}</span>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted d-block">��������</small>
                                    <span>{{ $currentSubscription->end_date->format("Y-m-d") }}</span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">ʣ������</small>
                                    <span>{{ $currentSubscription->end_date->diffInDays(now()) }} ��</span>
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
                                        <i class="fas fa-exclamation-triangle me-1"></i> ���Ļ�Ա��������
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelSubscriptionModal">
                                    <i class="fas fa-times me-1"></i> ȡ������
                                </button>
                                <a href="{{ route("subscription.upgrade") }}" class="btn btn-primary">
                                    <i class="fas fa-arrow-up me-1"></i> ������Ա
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="fas fa-crown fa-3x text-muted"></i>
                            </div>
                            <h5>����ǰ������û�</h5>
                            <p class="text-muted mb-4">���������ѻ�Ա�����ܸ�����Ȩ�͹���</p>
                            <a href="{{ route("subscription.upgrade") }}" class="btn btn-primary">
                                <i class="fas fa-crown me-1"></i> ������Ա
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- ��Ա��Ȩ -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">��Ա��Ȩ</h5>
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
            
            <!-- ������ļ�¼ -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">���ļ�¼</h5>
                    <a href="{{ route("subscription.history") }}" class="btn btn-sm btn-outline-primary">�鿴ȫ��</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>���ı��</th>
                                    <th>��Ա�ȼ�</th>
                                    <th>��ʼ����</th>
                                    <th>��������</th>
                                    <th>���</th>
                                    <th>״̬</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscriptionHistory as $subscription)
                                    <tr>
                                        <td>{{ $subscription->subscription_no }}</td>
                                        <td>{{ $subscription->membershipLevel->name }}</td>
                                        <td>{{ $subscription->start_date->format("Y-m-d") }}</td>
                                        <td>{{ $subscription->end_date->format("Y-m-d") }}</td>
                                        <td>��{{ number_format($subscription->price_paid, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $subscription->status === "active" ? "bg-success" : ($subscription->status === "cancelled" ? "bg-danger" : "bg-secondary") }}">
                                                {{ $subscription->status === "active" ? "��Ч" : ($subscription->status === "cancelled" ? "��ȡ��" : "�ѹ���") }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <p class="mb-0">���޶��ļ�¼</p>
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
            <!-- ��Ա�ȼ��Ա� -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">��Ա�ȼ��Ա�</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>����</th>
                                    <th>��ǰ�ȼ�</th>
                                    <th>���ߵȼ�</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>API���ô���</td>
                                    <td>{{ $currentLevel->api_quota == -1 ? "����" : $currentLevel->api_quota }}/��</td>
                                    <td>����/��</td>
                                </tr>
                                <tr>
                                    <td>AIģ�ͷ���</td>
                                    <td>{{ $currentLevel->getPrivilegeValue("ai_models") }}</td>
                                    <td>����ģ��</td>
                                </tr>
                                <tr>
                                    <td>�洢�ռ�</td>
                                    <td>{{ round($currentLevel->storage_quota / 1024, 0) }} GB</td>
                                    <td>100 GB</td>
                                </tr>
                                <tr>
                                    <td>���ȼ���֧��</td>
                                    <td>{{ $currentLevel->priority_support ? "��" : "��" }}</td>
                                    <td>��</td>
                                </tr>
                                <tr>
                                    <td>ר������</td>
                                    <td>{{ $currentLevel->getPrivilegeValue("exclusive_features") ?: "��" }}</td>
                                    <td>��</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-grid mt-3">
                        <a href="{{ route("subscription.upgrade") }}" class="btn btn-primary">
                            <i class="fas fa-arrow-up me-1"></i> ������Ա
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- �������� -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">��������</h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    ���ȡ���Զ����ѣ�
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    �������ڻ�Աҳ����"ȡ������"��ť��ѡ��ȡ���Զ�����ѡ�ȡ�������Ļ�Ա���ڵ�ǰ�����ڽ�����ֹͣ��
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    ���������Ա�ȼ���
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    ���"������Ա"��ť��ѡ������Ҫ�Ļ�Ա�ȼ������֧���󼴿���������������������ж��ģ�ϵͳ���Զ�����ʣ���ֵ��Ӧ�õ��¶����С�
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    ��Ա��������μ���ģ�
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    ��Ա���ø�����ѡ��Ļ�Ա�ȼ��Ͷ������ڣ��¶Ȼ���ȣ����㡣ѡ����ȶ��Ŀ����ܸ����Żݣ�ͨ���൱�ڻ��2���µ����ʹ���ڡ�
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    �ҿ��������˿���
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    �������ǵ��˿����ߣ�������ڹ����14����������룬����δʹ�ó���������20%������������ȫ���˿����ϵ�ͷ��ŶӴ����˿����ˡ�
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ȡ������ģ̬�� -->
<div class="modal fade" id="cancelSubscriptionModal" tabindex="-1" aria-labelledby="cancelSubscriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelSubscriptionModalLabel">ȡ������</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i> ȡ�����ĺ������޷��������ܻ�Ա��Ȩ�����Ļ�Ա�ʸ񽫳�������ǰ�����ڽ�����
                </div>
                
                <form action="{{ route("subscription.cancel") }}" method="POST">
                    @csrf
                    
                    <input type="hidden" name="subscription_id" value="{{ $currentSubscription ? $currentSubscription->id : "" }}">
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">ȡ��ԭ�򣨿�ѡ��</label>
                        <select class="form-select" id="reason" name="reason">
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
                        <input class="form-check-input" type="checkbox" id="confirmCancel" required>
                        <label class="form-check-label" for="confirmCancel">
                            ��ȷ��Ҫȡ���ҵĻ�Ա����
                        </label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-danger" id="cancelButton" disabled>
                            ȷ��ȡ��
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
    // ����/����ȡ����ť
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
