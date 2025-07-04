@extends("admin.layouts.app")

@section("title", "创建OAuth提供商")

@section("content_header")
    <h1>创建OAuth提供商</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">添加新的OAuth提供商</h3>
                <a href="{{ route("admin.oauth.providers.index") }}" class="btn btn-default">
                    <i class="fas fa-arrow-left"></i> 返回列表
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route("admin.oauth.providers.store") }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error("name") is-invalid @enderror" id="name" name="name" value="{{ old("name") }}" required>
                            @error("name")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="identifier">标识符 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error("identifier") is-invalid @enderror" id="identifier" name="identifier" value="{{ old("identifier") }}" required>
                            <small class="form-text text-muted">唯一标识符，如：google, github, wechat</small>
                            @error("identifier")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="icon">图标 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i id="icon-preview" class="fas fa-question"></i></span>
                                </div>
                                <input type="text" class="form-control @error("icon") is-invalid @enderror" id="icon" name="icon" value="{{ old("icon") }}" placeholder="例如：fab fa-google" required>
                            </div>
                            <small class="form-text text-muted">使用Font Awesome图标类，如：fab fa-google, fab fa-github</small>
                            @error("icon")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="is_active">状态</label>
                            <select class="form-control" id="is_active" name="is_active">
                                <option value="1" {{ old("is_active", "1") == "1" ? "selected" : "" }}>启用</option>
                                <option value="0" {{ old("is_active") == "0" ? "selected" : "" }}>禁用</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">描述</label>
                    <textarea class="form-control @error("description") is-invalid @enderror" id="description" name="description" rows="3">{{ old("description") }}</textarea>
                    @error("description")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-primary">
                        <h3 class="card-title">OAuth配置</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="client_id">客户端ID</label>
                                    <input type="text" class="form-control @error("client_id") is-invalid @enderror" id="client_id" name="client_id" value="{{ old("client_id") }}">
                                    @error("client_id")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="client_secret">客户端密钥</label>
                                    <input type="password" class="form-control @error("client_secret") is-invalid @enderror" id="client_secret" name="client_secret">
                                    @error("client_secret")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="redirect_url">回调URL</label>
                            <input type="text" class="form-control @error("redirect_url") is-invalid @enderror" id="redirect_url" name="redirect_url" value="{{ old("redirect_url") }}">
                            <small class="form-text text-muted">例如：https://yourdomain.com/auth/google/callback</small>
                            @error("redirect_url")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="auth_url">授权URL</label>
                                    <input type="text" class="form-control @error("auth_url") is-invalid @enderror" id="auth_url" name="auth_url" value="{{ old("auth_url") }}">
                                    @error("auth_url")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="token_url">令牌URL</label>
                                    <input type="text" class="form-control @error("token_url") is-invalid @enderror" id="token_url" name="token_url" value="{{ old("token_url") }}">
                                    @error("token_url")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="user_info_url">用户信息URL</label>
                                    <input type="text" class="form-control @error("user_info_url") is-invalid @enderror" id="user_info_url" name="user_info_url" value="{{ old("user_info_url") }}">
                                    @error("user_info_url")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>权限范围</label>
                            <select class="form-control select2" name="scopes[]" multiple="multiple" data-placeholder="选择权限范围">
                                <option value="profile" {{ in_array("profile", old("scopes", [])) ? "selected" : "" }}>profile</option>
                                <option value="email" {{ in_array("email", old("scopes", [])) ? "selected" : "" }}>email</option>
                                <option value="openid" {{ in_array("openid", old("scopes", [])) ? "selected" : "" }}>openid</option>
                                <option value="user:email" {{ in_array("user:email", old("scopes", [])) ? "selected" : "" }}>user:email</option>
                                <option value="snsapi_login" {{ in_array("snsapi_login", old("scopes", [])) ? "selected" : "" }}>snsapi_login</option>
                                <option value="snsapi_userinfo" {{ in_array("snsapi_userinfo", old("scopes", [])) ? "selected" : "" }}>snsapi_userinfo</option>
                                <option value="get_user_info" {{ in_array("get_user_info", old("scopes", [])) ? "selected" : "" }}>get_user_info</option>
                            </select>
                            <small class="form-text text-muted">可以添加多个权限范围，根据提供商的要求设置</small>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> 保存提供商
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@section("css")
    <link rel="stylesheet" href="{{ asset("vendor/select2/css/select2.min.css") }}">
    <link rel="stylesheet" href="{{ asset("vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css") }}">
@stop

@section("js")
    <script src="{{ asset("vendor/select2/js/select2.full.min.js") }}"></script>
    <script>
        $(function() {
            $(".select2").select2({
                theme: "bootstrap4",
                tags: true
            });
            
            // 图标预览
            $("#icon").on("input", function() {
                const iconClass = $(this).val();
                $("#icon-preview").attr("class", iconClass || "fas fa-question");
            });
            
            // 初始化图标预览
            const initialIcon = $("#icon").val();
            if (initialIcon) {
                $("#icon-preview").attr("class", initialIcon);
            }
        });
    </script>
@stop
