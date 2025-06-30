@extends("layouts.app")

@section("title", "个人资料")

@section("content")
<div class="container py-4">
    <div class="row">
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <img src="{{ $user->avatar ? asset($user->avatar) : "https://ui-avatars.com/api/?name=" . urlencode($user->name) . "&background=random" }}" alt="{{ $user->name }}" class="rounded-circle img-thumbnail mb-3" width="100">
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-3">{{ $user->email }}</p>
                    <div class="d-grid">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#avatarModal">
                            <i class="fas fa-camera me-1"></i> 更换头像
                        </button>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                        <i class="fas fa-user me-2"></i> 个人信息
                    </a>
                    <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="fas fa-lock me-2"></i> 安全设置
                    </a>
                    <a href="#points" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="fas fa-star me-2"></i> 我的积分
                    </a>
                    <a href="#referrals" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="fas fa-users me-2"></i> 我的推荐
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            @if(session("success"))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session("success") }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <div class="tab-content">
                <!-- 个人信息 -->
                <div class="tab-pane fade show active" id="profile">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">个人信息</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route("profile.update") }}" method="POST">
                                @csrf
                                @method("PUT")
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">姓名</label>
                                        <input type="text" class="form-control @error("name") is-invalid @enderror" id="name" name="name" value="{{ old("name", $user->name) }}" required>
                                        @error("name")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">电子邮箱</label>
                                        <input type="email" class="form-control @error("email") is-invalid @enderror" id="email" name="email" value="{{ old("email", $user->email) }}" required>
                                        @error("email")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">电话</label>
                                        <input type="text" class="form-control @error("phone") is-invalid @enderror" id="phone" name="phone" value="{{ old("phone", $user->phone) }}">
                                        @error("phone")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="company" class="form-label">公司</label>
                                        <input type="text" class="form-control @error("company") is-invalid @enderror" id="company" name="company" value="{{ old("company", $user->company) }}">
                                        @error("company")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">地址</label>
                                    <input type="text" class="form-control @error("address") is-invalid @enderror" id="address" name="address" value="{{ old("address", $user->address) }}">
                                    @error("address")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="city" class="form-label">城市</label>
                                        <input type="text" class="form-control @error("city") is-invalid @enderror" id="city" name="city" value="{{ old("city", $user->city) }}">
                                        @error("city")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="state" class="form-label">省/州</label>
                                        <input type="text" class="form-control @error("state") is-invalid @enderror" id="state" name="state" value="{{ old("state", $user->state) }}">
                                        @error("state")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="zip_code" class="form-label">邮政编码</label>
                                        <input type="text" class="form-control @error("zip_code") is-invalid @enderror" id="zip_code" name="zip_code" value="{{ old("zip_code", $user->zip_code) }}">
                                        @error("zip_code")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="country" class="form-label">国家</label>
                                    <input type="text" class="form-control @error("country") is-invalid @enderror" id="country" name="country" value="{{ old("country", $user->country) }}">
                                    @error("country")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> 保存更改
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- 安全设置 -->
                <div class="tab-pane fade" id="security">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">更改密码</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route("profile.password") }}" method="POST">
                                @csrf
                                @method("PUT")
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">当前密码</label>
                                    <input type="password" class="form-control @error("current_password") is-invalid @enderror" id="current_password" name="current_password" required>
                                    @error("current_password")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">新密码</label>
                                    <input type="password" class="form-control @error("password") is-invalid @enderror" id="password" name="password" required>
                                    @error("password")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">确认新密码</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-key me-1"></i> 更改密码
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">双因素认证</h5>
                        </div>
                        <div class="card-body">
                            <p>双因素认证为您的账户增加了额外的安全层。启用后，除了密码外，您还需要输入从手机应用获取的验证码。</p>
                            
                            @if($user->two_factor_enabled)
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i> 您已启用双因素认证
                                </div>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#disable2faModal">
                                        <i class="fas fa-times me-1"></i> 禁用双因素认证
                                    </button>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i> 您尚未启用双因素认证
                                </div>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#enable2faModal">
                                        <i class="fas fa-shield-alt me-1"></i> 启用双因素认证
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- 我的积分 -->
                <div class="tab-pane fade" id="points">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">积分概览</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <div class="p-3 rounded bg-light">
                                        <h3>{{ $pointsStats["available"] }}</h3>
                                        <small class="text-muted">可用积分</small>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <div class="p-3 rounded bg-light">
                                        <h3>{{ $pointsStats["total_earned"] }}</h3>
                                        <small class="text-muted">累计获得</small>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <div class="p-3 rounded bg-light">
                                        <h3>{{ $pointsStats["total_consumed"] }}</h3>
                                        <small class="text-muted">已使用</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 rounded bg-light">
                                        <h3>{{ $pointsStats["expired"] }}</h3>
                                        <small class="text-muted">已过期</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">积分历史</h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="pointsFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-filter me-1"></i> 筛选
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="pointsFilterDropdown">
                                    <li><a class="dropdown-item active" href="#">全部</a></li>
                                    <li><a class="dropdown-item" href="#">获得积分</a></li>
                                    <li><a class="dropdown-item" href="#">使用积分</a></li>
                                    <li><a class="dropdown-item" href="#">过期积分</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>日期</th>
                                            <th>描述</th>
                                            <th>类型</th>
                                            <th class="text-end">积分</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pointsHistory as $point)
                                            <tr>
                                                <td>{{ $point->created_at->format("Y-m-d H:i") }}</td>
                                                <td>{{ $point->description }}</td>
                                                <td>
                                                    <span class="badge {{ $point->points > 0 ? "bg-success" : ($point->action == "points_expired" ? "bg-warning" : "bg-danger") }}">
                                                        {{ $point->action }}
                                                    </span>
                                                </td>
                                                <td class="text-end {{ $point->points > 0 ? "text-success" : "text-danger" }}">
                                                    {{ $point->points > 0 ? "+" : "" }}{{ $point->points }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4">
                                                    <p class="mb-0">暂无积分记录</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 我的推荐 -->
                <div class="tab-pane fade" id="referrals">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">推荐概览</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="p-3 rounded bg-light">
                                        <h3>{{ $referralStats["total"] }}</h3>
                                        <small class="text-muted">总推荐人数</small>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="p-3 rounded bg-light">
                                        <h3>{{ $referralStats["completed"] }}</h3>
                                        <small class="text-muted">成功推荐</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 rounded bg-light">
                                        <h3>{{ $referralStats["points"] }}</h3>
                                        <small class="text-muted">获得积分</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">我的推荐链接</h5>
                        </div>
                        <div class="card-body">
                            <p>分享您的推荐链接，邀请好友注册，双方都将获得奖励！</p>
                            
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" value="{{ $referralLink }}" id="referralLinkProfile" readonly>
                                <button class="btn btn-outline-primary" type="button" onclick="copyReferralLinkProfile()">
                                    <i class="fas fa-copy"></i> 复制
                                </button>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <a href="https://twitter.com/intent/tweet?text=加入AlingAi Pro，使用我的推荐链接获得奖励：{{ urlencode($referralLink) }}" target="_blank" class="btn btn-outline-primary">
                                    <i class="fab fa-twitter me-1"></i> Twitter
                                </a>
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($referralLink) }}" target="_blank" class="btn btn-outline-primary">
                                    <i class="fab fa-facebook me-1"></i> Facebook
                                </a>
                                <a href="https://api.whatsapp.com/send?text=加入AlingAi Pro，使用我的推荐链接获得奖励：{{ urlencode($referralLink) }}" target="_blank" class="btn btn-outline-primary">
                                    <i class="fab fa-whatsapp me-1"></i> WhatsApp
                                </a>
                                <a href="mailto:?subject=邀请您加入AlingAi Pro&body=您好，我想邀请您加入AlingAi Pro平台。使用我的推荐链接注册，我们双方都将获得奖励：{{ $referralLink }}" class="btn btn-outline-primary">
                                    <i class="fas fa-envelope me-1"></i> 邮件
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">推荐记录</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>用户</th>
                                            <th>注册时间</th>
                                            <th>状态</th>
                                            <th class="text-end">奖励</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($referrals as $referral)
                                            <tr>
                                                <td>{{ $referral->referred->name }}</td>
                                                <td>{{ $referral->created_at->format("Y-m-d") }}</td>
                                                <td>
                                                    <span class="badge {{ $referral->status == "completed" ? "bg-success" : "bg-warning" }}">
                                                        {{ $referral->status == "completed" ? "已完成" : "待处理" }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    @if($referral->points_awarded > 0)
                                                        <span class="text-success">+{{ $referral->points_awarded }} 积分</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4">
                                                    <p class="mb-0">暂无推荐记录</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 更换头像模态框 -->
<div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="avatarModalLabel">更换头像</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route("profile.update") }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method("PUT")
                    
                    <div class="mb-3">
                        <label for="avatar" class="form-label">选择图片</label>
                        <input class="form-control @error("avatar") is-invalid @enderror" type="file" id="avatar" name="avatar" accept="image/*">
                        <div class="form-text">支持JPG、PNG、GIF格式，最大2MB</div>
                        @error("avatar")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">上传头像</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 启用双因素认证模态框 -->
<div class="modal fade" id="enable2faModal" tabindex="-1" aria-labelledby="enable2faModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="enable2faModalLabel">启用双因素认证</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>请使用您的身份验证器应用扫描以下二维码：</p>
                <div class="text-center mb-3">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=otpauth://totp/AlingAi:{{ $user->email }}?secret=JBSWY3DPEHPK3PXP&issuer=AlingAi" alt="二维码" class="img-fluid">
                </div>
                <p>或者手动输入以下密钥：</p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" value="JBSWY3DPEHPK3PXP" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copySecret()">复制</button>
                </div>
                <form id="verify2faForm">
                    <div class="mb-3">
                        <label for="verification_code" class="form-label">验证码</label>
                        <input type="text" class="form-control" id="verification_code" name="verification_code" placeholder="输入6位验证码" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">验证并启用</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 禁用双因素认证模态框 -->
<div class="modal fade" id="disable2faModal" tabindex="-1" aria-labelledby="disable2faModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="disable2faModalLabel">禁用双因素认证</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i> 警告：禁用双因素认证将降低您账户的安全性。
                </div>
                <form id="disable2faForm">
                    <div class="mb-3">
                        <label for="current_password_2fa" class="form-label">当前密码</label>
                        <input type="password" class="form-control" id="current_password_2fa" name="current_password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-danger">禁用双因素认证</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
    // 处理标签页切换
    document.addEventListener("DOMContentLoaded", function() {
        // 获取URL中的锚点
        var hash = window.location.hash;
        if (hash) {
            // 激活对应的标签页
            $("a[href='" + hash + "']").tab("show");
        }
        
        // 当URL中的锚点变化时切换标签页
        window.addEventListener("hashchange", function() {
            var hash = window.location.hash;
            if (hash) {
                $("a[href='" + hash + "']").tab("show");
            }
        });
        
        // 当标签页切换时更新URL中的锚点
        $("a[data-bs-toggle='list']").on("shown.bs.tab", function(e) {
            history.replaceState(null, null, $(e.target).attr("href"));
        });
    });
    
    // 复制推荐链接
    function copyReferralLinkProfile() {
        var copyText = document.getElementById("referralLinkProfile");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        
        // 显示提示
        alert("推荐链接已复制到剪贴板！");
    }
    
    // 复制二维码密钥
    function copySecret() {
        var copyText = document.querySelector("#enable2faModal .input-group input");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        
        // 显示提示
        alert("密钥已复制到剪贴板！");
    }
</script>
@endsection
