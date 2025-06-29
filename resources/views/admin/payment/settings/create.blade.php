@extends("admin.layouts.app")

@section("title", "���֧������")

@section("content")
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">���֧������</h3>
                    <div class="card-tools">
                        <a href="{{ route("admin.payment.settings.index") }}" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> ��������
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session("error"))
                        <div class="alert alert-danger">
                            {{ session("error") }}
                        </div>
                    @endif
                    
                    <form action="{{ route("admin.payment.settings.store") }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label for="key">���ü� <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error("key") is-invalid @enderror" id="key" name="key" value="{{ old("key") }}" required>
                            <small class="form-text text-muted">���ü�ֻ�ܰ�����ĸ�����ֺ��»���</small>
                            @error("key")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="value">����ֵ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error("value") is-invalid @enderror" id="value" name="value" value="{{ old("value") }}" required>
                            @error("value")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="group">���� <span class="text-danger">*</span></label>
                            <select class="form-control @error("group") is-invalid @enderror" id="group" name="group" required>
                                <option value="general" {{ old("group") === "general" ? "selected" : "" }}>�������� (general)</option>
                                <option value="notification" {{ old("group") === "notification" ? "selected" : "" }}>֪ͨ���� (notification)</option>
                                <option value="security" {{ old("group") === "security" ? "selected" : "" }}>��ȫ���� (security)</option>
                                <option value="custom" {{ old("group") === "custom" ? "selected" : "" }}>�Զ��� (custom)</option>
                            </select>
                            @error("group")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="description">����</label>
                            <input type="text" class="form-control @error("description") is-invalid @enderror" id="description" name="description" value="{{ old("description") }}">
                            @error("description")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> ����
                            </button>
                            <a href="{{ route("admin.payment.settings.index") }}" class="btn btn-default">
                                <i class="fas fa-times"></i> ȡ��
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
