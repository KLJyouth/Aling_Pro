@extends("admin.layouts.app")

@section("title", "���׹���")

@section("content")
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">�����б�</h3>
                    <div class="card-tools">
                        <a href="{{ route("admin.payment.transactions.export", request()->query()) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-export"></i> ��������
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session("success"))
                        <div class="alert alert-success">
                            {{ session("success") }}
                        </div>
                    @endif
                    
                    @if(session("error"))
                        <div class="alert alert-danger">
                            {{ session("error") }}
                        </div>
                    @endif
                    
                    <!-- ɸѡ�� -->
                    <div class="card card-body bg-light mb-4">
                        <form action="{{ route("admin.payment.transactions.index") }}" method="GET">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="gateway_id">֧������</label>
                                        <select class="form-control" id="gateway_id" name="gateway_id">
                                            <option value="">ȫ��</option>
                                            @foreach($gateways as $gateway)
                                                <option value="{{ $gateway->id }}" {{ request("gateway_id") == $gateway->id ? "selected" : "" }}>{{ $gateway->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="status">״̬</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="">ȫ��</option>
                                            <option value="pending" {{ request("status") === "pending" ? "selected" : "" }}>��֧��</option>
                                            <option value="completed" {{ request("status") === "completed" ? "selected" : "" }}>�����</option>
                                            <option value="failed" {{ request("status") === "failed" ? "selected" : "" }}>ʧ��</option>
                                            <option value="refunded" {{ request("status") === "refunded" ? "selected" : "" }}>���˿�</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="transaction_id">����ID</label>
                                        <input type="text" class="form-control" id="transaction_id" name="transaction_id" value="{{ request("transaction_id") }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="order_id">����ID</label>
                                        <input type="text" class="form-control" id="order_id" name="order_id" value="{{ request("order_id") }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="user_id">�û�ID</label>
                                        <input type="text" class="form-control" id="user_id" name="user_id" value="{{ request("user_id") }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="date_from">��ʼ����</label>
                                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request("date_from") }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="date_to">��������</label>
                                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request("date_to") }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search"></i> ����
                                            </button>
                                            <a href="{{ route("admin.payment.transactions.index") }}" class="btn btn-default">
                                                <i class="fas fa-redo"></i> ����
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>����ID</th>
                                <th>����ID</th>
                                <th>�û�ID</th>
                                <th>֧������</th>
                                <th>���</th>
                                <th>״̬</th>
                                <th>֧����ʽ</th>
                                <th>֧��ʱ��</th>
                                <th>����ʱ��</th>
                                <th style="width: 120px">����</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->transaction_id }}</td>
                                    <td>{{ $transaction->order_id }}</td>
                                    <td>{{ $transaction->user_id ?: "-" }}</td>
                                    <td>{{ $transaction->gateway_name }}</td>
                                    <td>{{ $transaction->amount }} {{ $transaction->currency }}</td>
                                    <td>
                                        @if($transaction->status === "pending")
                                            <span class="badge badge-warning">��֧��</span>
                                        @elseif($transaction->status === "completed")
                                            <span class="badge badge-success">�����</span>
                                        @elseif($transaction->status === "failed")
                                            <span class="badge badge-danger">ʧ��</span>
                                        @elseif($transaction->status === "refunded")
                                            <span class="badge badge-info">���˿�</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $transaction->status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $transaction->payment_method ?: "-" }}</td>
                                    <td>{{ $transaction->paid_at ?: "-" }}</td>
                                    <td>{{ $transaction->created_at }}</td>
                                    <td>
                                        <a href="{{ route("admin.payment.transactions.show", $transaction->transaction_id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> ����
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">���޽��׼�¼</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div class="mt-4">
                        {{ $transactions->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
