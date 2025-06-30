@extends("layouts.app")

@section("title", "�ҵĶ���")

@section("content")
<div class="container py-4">
    <h1 class="h3 mb-4">�ҵĶ���</h1>
    
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
    
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a class="nav-link {{ request("type") === null ? "active" : "" }}" href="{{ route("orders") }}">ȫ������</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request("type") === "subscription" ? "active" : "" }}" href="{{ route("orders", ["type" => "subscription"]) }}">��Ա����</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request("type") === "point" ? "active" : "" }}" href="{{ route("orders", ["type" => "point"]) }}">���ֹ���</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request("type") === "product" ? "active" : "" }}" href="{{ route("orders", ["type" => "product"]) }}">��Ʒ����</a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <div class="dropdown text-end">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="statusFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-filter me-1"></i> ״̬ɸѡ
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="statusFilterDropdown">
                            <li><a class="dropdown-item {{ request("status") === null ? "active" : "" }}" href="{{ route("orders", ["type" => request("type")]) }}">ȫ��״̬</a></li>
                            <li><a class="dropdown-item {{ request("status") === "pending" ? "active" : "" }}" href="{{ route("orders", ["type" => request("type"), "status" => "pending"]) }}">��֧��</a></li>
                            <li><a class="dropdown-item {{ request("status") === "completed" ? "active" : "" }}" href="{{ route("orders", ["type" => request("type"), "status" => "completed"]) }}">�����</a></li>
                            <li><a class="dropdown-item {{ request("status") === "failed" ? "active" : "" }}" href="{{ route("orders", ["type" => request("type"), "status" => "failed"]) }}">��ʧ��</a></li>
                            <li><a class="dropdown-item {{ request("status") === "cancelled" ? "active" : "" }}" href="{{ route("orders", ["type" => request("type"), "status" => "cancelled"]) }}">��ȡ��</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>������</th>
                            <th>��������</th>
                            <th>���</th>
                            <th>֧����ʽ</th>
                            <th>����ʱ��</th>
                            <th>״̬</th>
                            <th>����</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
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
                                <td>��{{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    @if($order->payment_method === "alipay")
                                        <img src="{{ asset("images/payment/alipay.png") }}" alt="֧����" height="20">
                                    @elseif($order->payment_method === "wechat")
                                        <img src="{{ asset("images/payment/wechat.png") }}" alt="΢��֧��" height="20">
                                    @elseif($order->payment_method === "card")
                                        <img src="{{ asset("images/payment/card.png") }}" alt="���п�" height="20">
                                    @else
                                        {{ $order->payment_method ?: "δѡ��" }}
                                    @endif
                                </td>
                                <td>{{ $order->created_at->format("Y-m-d H:i") }}</td>
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
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route("order.show", $order->id) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($order->status === "pending")
                                            <a href="{{ route("payment.show", $order->order_number) }}" class="btn btn-outline-success">
                                                <i class="fas fa-credit-card"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="mb-0">���޶�����¼</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- ��ҳ -->
            <div class="d-flex justify-content-center mt-4">
                {{ $orders->appends(["type" => request("type"), "status" => request("status")])->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
