@extends("admin.layouts.app")


@section("title", "创建额度套餐")

@section("content_header")
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>创建额度套餐</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route("admin.index") }}">首页</a></li>
                <li class="breadcrumb-item"><a href="{{ route("admin.billing.packages.index") }}">额度套餐管理</a></li>
                <li class="breadcrumb-item active">创建额度套餐</li>
            </ol>
        </div>
    </div>
@endsection


@section("content")
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">创建新套餐</h3>
                </div>
                <!-- /.card-header -->
                <form action="{{ route("admin.billing.packages.store") }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">套餐名称 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error("name") is-invalid @enderror" id="name" name="name" value="{{ old("name") }}" required>
                                    @error("name")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code">套餐代码 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error("code") is-invalid @enderror" id="code" name="code" value="{{ old("code") }}" required>
                                    <small class="form-text text-muted">唯一标识符，只能包含字母、数字和下划线</small>
                                    @error("code")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <label for="description">套餐描述</label>
                            <textarea class="form-control @error("description") is-invalid @enderror" id="description" name="description" rows="3">{{ old("description") }}</textarea>
                            @error("description")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="type">套餐类型 <span class="text-danger">*</span></label>
                                    <select class="form-control @error("type") is-invalid @enderror" id="type" name="type" required>
                                        <option value="api" {{ old("type") == "api" ? "selected" : "" }}>API调用额度</option>
                                        <option value="ai" {{ old("type") == "ai" ? "selected" : "" }}>AI使用额度</option>
                                        <option value="storage" {{ old("type") == "storage" ? "selected" : "" }}>存储空间</option>
                                        <option value="bandwidth" {{ old("type") == "bandwidth" ? "selected" : "" }}>带宽流量</option>
                                        <option value="comprehensive" {{ old("type") == "comprehensive" ? "selected" : "" }}>综合套餐</option>
                                    </select>
                                    @error("type")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="quota">额度数量 <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error("quota") is-invalid @enderror" id="quota" name="quota" value="{{ old("quota", 0) }}" min="0" required>
                                    @error("quota")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="duration_days">有效期(天)</label>
                                    <input type="number" class="form-control @error("duration_days") is-invalid @enderror" id="duration_days" name="duration_days" value="{{ old("duration_days") }}" min="1">
                                    <small class="form-text text-muted">留空表示永久有效</small>
                                    @error("duration_days")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price">价格 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"></span>
                                        </div>
                                        <input type="number" class="form-control @error("price") is-invalid @enderror" id="price" name="price" value="{{ old("price", 0) }}" min="0" step="0.01" required>
                                    </div>
                                    @error("price")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="original_price">原价</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"></span>
                                        </div>
                                        <input type="number" class="form-control @error("original_price") is-invalid @enderror" id="original_price" name="original_price" value="{{ old("original_price") }}" min="0" step="0.01">
                                    </div>
                                    <small class="form-text text-muted">如果有折扣，请填写原价</small>
                                    @error("original_price")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <label>套餐特性</label>
                            <div class="features-container">
                                @for ($i = 0; $i < 5; $i++)
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="features[]" value="{{ old("features." . $i) }}">
                                        @if ($i === 0)
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-success add-feature"><i class="fas fa-plus"></i></button>
                                            </div>
                                        @else
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-danger remove-feature"><i class="fas fa-minus"></i></button>
                                            </div>
                                        @endif
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sort_order">排序</label>
                                    <input type="number" class="form-control @error("sort_order") is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old("sort_order", 0) }}" min="0">
                                    <small class="form-text text-muted">数字越小排序越靠前</small>
                                    @error("sort_order")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status">状态 <span class="text-danger">*</span></label>
                                    <select class="form-control @error("status") is-invalid @enderror" id="status" name="status" required>
                                        <option value="active" {{ old("status") == "active" ? "selected" : "" }}>上架</option>
                                        <option value="inactive" {{ old("status") == "inactive" ? "selected" : "" }}>下架</option>
                                        <option value="coming_soon" {{ old("status") == "coming_soon" ? "selected" : "" }}>即将推出</option>
                                    </select>
                                    @error("status")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="custom-control custom-switch mt-4">
                                        <input type="checkbox" class="custom-control-input" id="is_popular" name="is_popular" value="1" {{ old("is_popular") ? "checked" : "" }}>
                                        <label class="custom-control-label" for="is_popular">热门套餐</label>
                                    </div>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_recommended" name="is_recommended" value="1" {{ old("is_recommended") ? "checked" : "" }}>
                                        <label class="custom-control-label" for="is_recommended">推荐套餐</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">保存</button>
                        <a href="{{ route("admin.billing.packages.index") }}" class="btn btn-default">取消</a>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection

@section("scripts")
<script>
    $(function() {
        // 添加套餐特性
        $(document).on("click", ".add-feature", function() {
            var featureHtml = `
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="features[]" value="">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger remove-feature"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
            `;
            $(".features-container").append(featureHtml);
        });

        // 移除套餐特性
        $(document).on("click", ".remove-feature", function() {
            $(this).closest(".input-group").remove();
        });
    });
</script>
@endsection
