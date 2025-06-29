@extends("admin.layouts.app")

@section("title", "添加支付网关")

@section("content")
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">添加 {{ $gateway["name"] }} 支付网关</h3>
                    <div class="card-tools">
                        <a href="{{ route("admin.payment.gateways.index") }}" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> 返回列表
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session("error"))
                        <div class="alert alert-danger">
                            {{ session("error") }}
                        </div>
                    @endif
                    
                    <form action="{{ route("admin.payment.gateways.store") }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="code" value="{{ $gatewayCode }}">
                        
                        <div class="form-group">
                            <label for="name">网关名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error("name") is-invalid @enderror" id="name" name="name" value="{{ old("name", $gateway["name"]) }}" required>
                            @error("name")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="description">描述</label>
                            <textarea class="form-control @error("description") is-invalid @enderror" id="description" name="description" rows="3">{{ old("description", $gateway["description"]) }}</textarea>
                            @error("description")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="logo">Logo</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error("logo") is-invalid @enderror" id="logo" name="logo" accept="image/*">
                                    <label class="custom-file-label" for="logo">选择文件</label>
                                </div>
                            </div>
                            <small class="form-text text-muted">支持的格式：JPG, PNG, GIF。最大文件大小：2MB</small>
                            @error("logo")
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old("is_active") ? "checked" : "" }}>
                                        <label class="custom-control-label" for="is_active">启用此网关</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_test_mode" name="is_test_mode" value="1" {{ old("is_test_mode", "1") ? "checked" : "" }}>
                                        <label class="custom-control-label" for="is_test_mode">测试模式</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="sort_order">排序</label>
                            <input type="number" class="form-control @error("sort_order") is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old("sort_order", 0) }}">
                            <small class="form-text text-muted">数字越小排序越靠前</small>
                            @error("sort_order")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <h4 class="mt-4 mb-3">配置信息</h4>
                        <div class="card card-body bg-light">
                            @foreach($fields as $field => $label)
                                <div class="form-group">
                                    <label for="config_{{ $field }}">{{ $label }} <span class="text-danger">*</span></label>
                                    @if(in_array($field, ["private_key", "key", "secret_key", "client_secret", "cert_password"]))
                                        <input type="password" class="form-control @error("config.$field") is-invalid @enderror" id="config_{{ $field }}" name="config[{{ $field }}]" value="{{ old("config.$field") }}" required>
                                    @elseif(in_array($field, ["cert_path", "key_path"]))
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input @error("config.$field") is-invalid @enderror" id="config_{{ $field }}" name="config[{{ $field }}]">
                                                <label class="custom-file-label" for="config_{{ $field }}">选择文件</label>
                                            </div>
                                        </div>
                                    @elseif($field === "mode" && $gatewayCode === "paypal")
                                        <select class="form-control @error("config.$field") is-invalid @enderror" id="config_{{ $field }}" name="config[{{ $field }}]" required>
                                            <option value="sandbox" {{ old("config.$field") === "sandbox" ? "selected" : "" }}>沙箱环境 (Sandbox)</option>
                                            <option value="live" {{ old("config.$field") === "live" ? "selected" : "" }}>生产环境 (Live)</option>
                                        </select>
                                    @elseif($field === "currency")
                                        <select class="form-control @error("config.$field") is-invalid @enderror" id="config_{{ $field }}" name="config[{{ $field }}]" required>
                                            <option value="CNY" {{ old("config.$field") === "CNY" ? "selected" : "" }}>人民币 (CNY)</option>
                                            <option value="USD" {{ old("config.$field") === "USD" ? "selected" : "" }}>美元 (USD)</option>
                                            <option value="EUR" {{ old("config.$field") === "EUR" ? "selected" : "" }}>欧元 (EUR)</option>
                                            <option value="GBP" {{ old("config.$field") === "GBP" ? "selected" : "" }}>英镑 (GBP)</option>
                                            <option value="JPY" {{ old("config.$field") === "JPY" ? "selected" : "" }}>日元 (JPY)</option>
                                        </select>
                                    @else
                                        <input type="text" class="form-control @error("config.$field") is-invalid @enderror" id="config_{{ $field }}" name="config[{{ $field }}]" value="{{ old("config.$field") }}" required>
                                    @endif
                                    @error("config.$field")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 保存
                            </button>
                            <a href="{{ route("admin.payment.gateways.index") }}" class="btn btn-default">
                                <i class="fas fa-times"></i> 取消
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
    $(function() {
        // 文件上传显示文件名
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
    });
</script>
@endsection
