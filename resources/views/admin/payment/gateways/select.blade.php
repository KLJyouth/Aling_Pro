@extends("admin.layouts.app")

@section("title", "ѡ��֧����������")

@section("content")
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ѡ��Ҫ��ӵ�֧����������</h3>
                    <div class="card-tools">
                        <a href="{{ route("admin.payment.gateways.index") }}" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> �����б�
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
                                            <i class="fas fa-plus"></i> ��Ӵ�����
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
