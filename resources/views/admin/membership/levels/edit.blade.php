@extends("admin.layouts.app")

@section("title", "编辑会员等级")

@section("content_header")
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>编辑会员等级</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route("admin.index") }}">首页</a></li>
                <li class="breadcrumb-item"><a href="{{ route("admin.membership.levels.index") }}">会员等级管理</a></li>
                <li class="breadcrumb-item active">编辑会员等级</li>
            </ol>
        </div>
    </div>
@endsection


@section("content")
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">编辑会员等级</h3>
                </div>
                <!-- /.card-header -->
                <form action="{{ route("admin.membership.levels.update", $level->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method("PUT")
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
                                    <label for="name">等级名称 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error("name") is-invalid @enderror" id="name" name="name" value="{{ old("name", $level->name) }}" required>
                                    @error("name")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code">等级代码 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error("code") is-invalid @enderror" id="code" name="code" value="{{ old("code", $level->code) }}" required>
                                    <small class="form-text text-muted">唯一标识符，只能包含字母、数字和下划线</small>
                                    @error("code")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <label for="description">等级描述</label>
                            <textarea class="form-control @error("description") is-invalid @enderror" id="description" name="description" rows="3">{{ old("description", $level->description) }}</textarea>
                            @error("description")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="level">等级值 <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error("level") is-invalid @enderror" id="level" name="level" value="{{ old("level", $level->level) }}" min="1" required>
                                    <small class="form-text text-muted">数字越大等级越高</small>
                                    @error("level")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="icon">等级图标</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input @error("icon") is-invalid @enderror" id="icon" name="icon" accept="image/*">
                                            <label class="custom-file-label" for="icon">选择文件</label>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">推荐尺寸: 64x64px</small>
                                    @if($level->icon)
                                        <div class="mt-2">
                                            <img src="{{ asset("storage/" . $level->icon) }}" alt="{{ $level->name }}" class="img-thumbnail" style="max-height: 50px;">
                                        </div>
                                    @endif
                                    @error("icon")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="monthly_price">月度价格 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"></span>
                                        </div>
                                        <input type="number" class="form-control @error("monthly_price") is-invalid @enderror" id="monthly_price" name="monthly_price" value="{{ old("monthly_price", $level->monthly_price) }}" min="0" step="0.01" required>
                                    </div>
                                    @error("monthly_price")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="yearly_price">年度价格 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"></span>
                                        </div>
                                        <input type="number" class="form-control @error("yearly_price") is-invalid @enderror" id="yearly_price" name="yearly_price" value="{{ old("yearly_price", $level->yearly_price) }}" min="0" step="0.01" required>
                                    </div>
                                    <small class="form-text text-muted">通常年度价格会有一定折扣</small>
                                    @error("yearly_price")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>


                        <h5 class="mt-4">会员权益</h5>
                        <hr>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="api_rate_limit">API请求限制 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error("api_rate_limit") is-invalid @enderror" id="api_rate_limit" name="api_rate_limit" value="{{ old("api_rate_limit", $level->api_rate_limit) }}" min="0" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">次/分钟</span>
                                        </div>
                                    </div>
                                    @error("api_rate_limit")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="api_daily_limit">API每日限额 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error("api_daily_limit") is-invalid @enderror" id="api_daily_limit" name="api_daily_limit" value="{{ old("api_daily_limit", $level->api_daily_limit) }}" min="0" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">次/天</span>
                                        </div>
                                    </div>
                                    @error("api_daily_limit")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="storage_limit">存储空间 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error("storage_limit") is-invalid @enderror" id="storage_limit" name="storage_limit" value="{{ old("storage_limit", $level->storage_limit) }}" min="0" step="0.1" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">GB</span>
                                        </div>
                                    </div>
                                    @error("storage_limit")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="discount_percent">购买折扣 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error("discount_percent") is-invalid @enderror" id="discount_percent" name="discount_percent" value="{{ old("discount_percent", $level->discount_percent) }}" min="0" max="100" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">购买额度套餐时的折扣百分比</small>
                                    @error("discount_percent")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="max_team_members">团队成员数 <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error("max_team_members") is-invalid @enderror" id="max_team_members" name="max_team_members" value="{{ old("max_team_members", $level->max_team_members) }}" min="1" required>
                                    <small class="form-text text-muted">可添加的最大团队成员数</small>
                                    @error("max_team_members")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="priority_support">优先支持</label>
                                    <select class="form-control @error("priority_support") is-invalid @enderror" id="priority_support" name="priority_support">
                                        <option value="0" {{ old("priority_support", $level->priority_support) == "0" ? "selected" : "" }}>否</option>
                                        <option value="1" {{ old("priority_support", $level->priority_support) == "1" ? "selected" : "" }}>是</option>
                                    </select>
                                    <small class="form-text text-muted">是否提供优先客服支持</small>
                                    @error("priority_support")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <label>特权功能</label>
                            <div>
                                @php
                                    $features = old("features", $level->features ? json_decode($level->features, true) : []);
                                @endphp
                                <div class="custom-control custom-checkbox d-inline-block mr-3">
                                    <input type="checkbox" class="custom-control-input" id="feature_advanced_models" name="features[]" value="advanced_models" {{ in_array("advanced_models", $features) ? "checked" : "" }}>
                                    <label class="custom-control-label" for="feature_advanced_models">高级模型访问权限</label>
                                </div>
                                <div class="custom-control custom-checkbox d-inline-block mr-3">
                                    <input type="checkbox" class="custom-control-input" id="feature_early_access" name="features[]" value="early_access" {{ in_array("early_access", $features) ? "checked" : "" }}>
                                    <label class="custom-control-label" for="feature_early_access">新功能抢先体验</label>
                                </div>
                                <div class="custom-control custom-checkbox d-inline-block mr-3">
                                    <input type="checkbox" class="custom-control-input" id="feature_api_access" name="features[]" value="api_access" {{ in_array("api_access", $features) ? "checked" : "" }}>
                                    <label class="custom-control-label" for="feature_api_access">API访问权限</label>
                                </div>
                                <div class="custom-control custom-checkbox d-inline-block mr-3">
                                    <input type="checkbox" class="custom-control-input" id="feature_custom_domain" name="features[]" value="custom_domain" {{ in_array("custom_domain", $features) ? "checked" : "" }}>
                                    <label class="custom-control-label" for="feature_custom_domain">自定义域名</label>
                                </div>
                                <div class="custom-control custom-checkbox d-inline-block mr-3">
                                    <input type="checkbox" class="custom-control-input" id="feature_white_label" name="features[]" value="white_label" {{ in_array("white_label", $features) ? "checked" : "" }}>
                                    <label class="custom-control-label" for="feature_white_label">白标解决方案</label>
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sort_order">排序</label>
                                    <input type="number" class="form-control @error("sort_order") is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old("sort_order", $level->sort_order) }}" min="0">
                                    <small class="form-text text-muted">数字越小排序越靠前</small>
                                    @error("sort_order")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">状态 <span class="text-danger">*</span></label>
                                    <select class="form-control @error("status") is-invalid @enderror" id="status" name="status" required>
                                        <option value="active" {{ old("status", $level->status) == "active" ? "selected" : "" }}>上架</option>
                                        <option value="inactive" {{ old("status", $level->status) == "inactive" ? "selected" : "" }}>下架</option>
                                        <option value="coming_soon" {{ old("status", $level->status) == "coming_soon" ? "selected" : "" }}>即将推出</option>
                                    </select>
                                    @error("status")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_popular" name="is_popular" value="1" {{ old("is_popular", $level->is_popular) ? "checked" : "" }}>
                                <label class="custom-control-label" for="is_popular">热门等级</label>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_recommended" name="is_recommended" value="1" {{ old("is_recommended", $level->is_recommended) ? "checked" : "" }}>
                                <label class="custom-control-label" for="is_recommended">推荐等级</label>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">保存</button>
                        <a href="{{ route("admin.membership.levels.index") }}" class="btn btn-default">取消</a>
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
        // 文件上传时显示文件名
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
    });
</script>
@endsection
