@extends("admin.layouts.app")

@section("title", "选择支付网关类型")

@section("content")
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">选择要添加的支付网关类型</h3>
                    <div class="card-tools">
                        <a href="{{ route("admin.payment.gateways.index") }}" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> 返回列表
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($supportedGateways as $code => $gateway)
                            <div class="col-md-4 col-sm-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">{{ $gateway["name"] }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <p>{{ $gateway["description"] }}</p>
                                    </div>
                                    <div class="card-footer">
                                        <a href="{{ route("admin.payment.gateways.create", ["gateway_code" => $code]) }}" class="btn btn-primary btn-block">
                                            <i class="fas fa-plus"></i> 添加此网关
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
