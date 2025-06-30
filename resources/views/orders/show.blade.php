@extends("layouts.app")

@section("title", "��������")

@section("content")
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">��������</h1>
        <a href="{{ route("orders") }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> ���ض����б�
        </a>
    </div>
    
    @if(session("success"))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session("success") }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <div class="row">
        <div class="col-lg-8">
            <!-- ������Ϣ -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">������Ϣ</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th class="ps-0">������</th>
                                    <td>{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <th class="ps-0">��������</th>
                                    <td>
                                        @if($order->order_type === "subscription")
                                            <span class="badge bg-primary">��Ա����</span>
                                        @elseif($order->order_type === "point")
                                            <span class="badge bg-info">���ֹ���</span>
                                        @elseif($order->order_type === "product")
                                            <span class="badge bg-success">��Ʒ����</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $order->order_type }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="ps-0">����ʱ��</th>
                                    <td>{{ $order->created_at->format("Y-m-d H:i:s") }}</td>
                                </tr>
                                <tr>
                                    <th class="ps-0">����״̬</th>
                                    <td>
                                        @if($order->status === "pending")
                                            <span class="badge bg-warning">��֧��</span>
                                        @elseif($order->status === "completed")
                                            <span class="badge bg-success">�����</span>
                                        @elseif($order->status === "failed")
                                            <span class="badge bg-danger">��ʧ��</span>
                                        @elseif($order->status === "cancelled")
                                            <span class="badge bg-secondary">��ȡ��</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $order->status }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th class="ps-0">֧����ʽ</th>
                                    <td>
                                        @if($order->payment_method === "alipay")
                                            <img src="{{ asset("images/payment/alipay.png") }}" alt="֧����" height="20"> ֧����
                                        @elseif($order->payment_method === "wechat")
                                            <img src="{{ asset("images/payment/wechat.png") }}" alt="΢��֧��" height="20"> ΢��֧��
                                        @elseif($order->payment_method === "card")
                                            <img src="{{ asset("images/payment/card.png") }}" alt="���п�" height="20"> ���п�
                                        @else
                                            {{ $order->payment_method ?: "δѡ��" }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="ps-0">֧��״̬</th>
                                    <td>
                                        @if($order->payment_status === "pending")
                                            <span class="badge bg-warning">��֧��</span>
                                        @elseif($order->payment_status === "paid")
                                            <span class="badge bg-success">��֧��</span>
                                        @elseif($order->payment_status === "failed")
                                            <span class="badge bg-danger">֧��ʧ��</span>
                                        @elseif($order->payment_status === "refunded")
                                            <span class="badge bg-info">���˿�</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $order->payment_status }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="ps-0">֧��ʱ��</th>
                                    <td>{{ $order->paid_at ? $order->paid_at->format("Y-m-d H:i:s") : "δ֧��" }}</td>
                                </tr>
                                <tr>
                                    <th class="ps-0">����ʱ��</th>
                                    <td>{{ $order->updated_at->format("Y-m-d H:i:s") }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($order->status === "pending" && $order->payment_status === "pending")
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                            <a href="{{ route("payment.show", $order->order_number) }}" class="btn btn-primary">
                                <i class="fas fa-credit-card me-1"></i> ȥ֧��
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- �������� -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">��������</h5>
                </div>
                <div class="card-body">
                    @if($order->order_type === "subscription")
                        @if($order->subscription)
                            <div class="mb-4">
                                <h6>��Ա������Ϣ</h6>
                                <div class="table-responsive">
                                    <table class="table">
                                        <tr>
                                            <th>��Ա�ȼ�</th>
                                            <td>{{ $order->subscription->membershipLevel->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>���ı��</th>
                                            <td>{{ $order->subscription->subscription_no }}</td>
                                        </tr>
                                        <tr>
                                            <th>��������</th>
                                            <td>{{ $order->subscription->subscription_type === "monthly" ? "�¶�" : "���" }}</td>
                                        </tr>
                                        <tr>
                                            <th>��ʼ����</th>
                                            <td>{{ $order->subscription->start_date->format("Y-m-d") }}</td>
                                        </tr>
                                        <tr>
                                            <th>��������</th>
                                            <td>{{ $order->subscription->end_date->format("Y-m-d") }}</td>
                                        </tr>
                                        <tr>
                                            <th>�Զ�����</th>
                                            <td>{{ $order->subscription->auto_renew ? "��" : "��" }}</td>
                                        </tr>
                                        <tr>
                                            <th>״̬</th>
                                            <td>
                                                @if($order->subscription->status === "active")
                                                    <span class="badge bg-success">��Ч</span>
                                                @elseif($order->subscription->status === "pending")
                                                    <span class="badge bg-warning">������</span>
                                                @elseif($order->subscription->status === "cancelled")
                                                    <span class="badge bg-danger">��ȡ��</span>
                                                @elseif($order->subscription->status === "expired")
                                                    <span class="badge bg-secondary">�ѹ���</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $order->subscription->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div>
                                <h6>��Ա��Ȩ</h6>
                                <div class="row">
                                    @foreach($order->subscription->membershipLevel->privileges as $privilege)
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
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i> ������Ϣ������
                            </div>
                        @endif
                    @elseif($order->order_type === "point")
                        <div class="mb-4">
                            <h6>���ֹ�����Ϣ</h6>
                            <div class="table-responsive">
                                <table class="table">
                                    <tr>
                                        <th>��������</th>
                                        <td>{{ $order->meta["points"] ?? 0 }} ����</td>
                                    </tr>
                                    <tr>
                                        <th>����</th>
                                        <td>��{{ number_format(($order->meta["price_per_point"] ?? 0), 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    @elseif($order->order_type === "product")
                        <div class="mb-4">
                            <h6>��Ʒ������Ϣ</h6>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>��Ʒ����</th>
                                            <th>����</th>
                                            <th>����</th>
                                            <th>С��</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ $order->meta["product_name"] ?? "" }}</td>
                                            <td>{{ $order->meta["quantity"] ?? 1 }}</td>
                                            <td>��{{ number_format(($order->meta["price"] ?? 0), 2) }}</td>
                                            <td>��{{ number_format(($order->meta["subtotal"] ?? 0), 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- �����Ϣ -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">�����Ϣ</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>С��</span>
                        <span>��{{ number_format($order->subtotal_amount, 2) }}</span>
                    </div>
                    
                    @if($order->discount_amount > 0)
                        <div class="d-flex justify-content-between mb-3">
                            <span>�ۿ�</span>
                            <span class="text-danger">-��{{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">�ܼ�</span>
                        <span class="fw-bold">��{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
            
            <!-- ������¼ -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">������¼</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0">
                            <div class="d-flex justify-content-between">
                                <span>��������</span>
                                <span>{{ $order->created_at->format("Y-m-d H:i") }}</span>
                            </div>
                        </li>
                        
                        @if($order->payment_status === "paid")
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <span>֧�����</span>
                                    <span>{{ $order->paid_at->format("Y-m-d H:i") }}</span>
                                </div>
                            </li>
                        @endif
                        
                        @if($order->status === "completed")
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <span>�������</span>
                                    <span>{{ $order->updated_at->format("Y-m-d H:i") }}</span>
                                </div>
                            </li>
                        @endif
                        
                        @if($order->status === "cancelled")
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <span>����ȡ��</span>
                                    <span>{{ $order->updated_at->format("Y-m-d H:i") }}</span>
                                </div>
                            </li>
                        @endif
                        
                        @if($order->status === "failed")
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <span>����ʧ��</span>
                                    <span>{{ $order->updated_at->format("Y-m-d H:i") }}</span>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
