@extends("layouts.app")

@section("title", "��������")

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
                            <i class="fas fa-camera me-1"></i> ����ͷ��
                        </button>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                        <i class="fas fa-user me-2"></i> ������Ϣ
                    </a>
                    <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="fas fa-lock me-2"></i> ��ȫ����
                    </a>
                    <a href="#points" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="fas fa-star me-2"></i> �ҵĻ���
                    </a>
                    <a href="#referrals" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="fas fa-users me-2"></i> �ҵ��Ƽ�
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
                <!-- ������Ϣ -->
                <div class="tab-pane fade show active" id="profile">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">������Ϣ</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route("profile.update") }}" method="POST">
                                @csrf
                                @method("PUT")
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">����</label>
                                        <input type="text" class="form-control @error("name") is-invalid @enderror" id="name" name="name" value="{{ old("name", $user->name) }}" required>
                                        @error("name")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">��������</label>
                                        <input type="email" class="form-control @error("email") is-invalid @enderror" id="email" name="email" value="{{ old("email", $user->email) }}" required>
                                        @error("email")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">�绰</label>
                                        <input type="text" class="form-control @error("phone") is-invalid @enderror" id="phone" name="phone" value="{{ old("phone", $user->phone) }}">
                                        @error("phone")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="company" class="form-label">��˾</label>
                                        <input type="text" class="form-control @error("company") is-invalid @enderror" id="company" name="company" value="{{ old("company", $user->company) }}">
                                        @error("company")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">��ַ</label>
                                    <input type="text" class="form-control @error("address") is-invalid @enderror" id="address" name="address" value="{{ old("address", $user->address) }}">
                                    @error("address")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="city" class="form-label">����</label>
                                        <input type="text" class="form-control @error("city") is-invalid @enderror" id="city" name="city" value="{{ old("city", $user->city) }}">
                                        @error("city")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="state" class="form-label">ʡ/��</label>
                                        <input type="text" class="form-control @error("state") is-invalid @enderror" id="state" name="state" value="{{ old("state", $user->state) }}">
                                        @error("state")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="zip_code" class="form-label">��������</label>
                                        <input type="text" class="form-control @error("zip_code") is-invalid @enderror" id="zip_code" name="zip_code" value="{{ old("zip_code", $user->zip_code) }}">
                                        @error("zip_code")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="country" class="form-label">����</label>
                                    <input type="text" class="form-control @error("country") is-invalid @enderror" id="country" name="country" value="{{ old("country", $user->country) }}">
                                    @error("country")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> �������
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- ��ȫ���� -->
                <div class="tab-pane fade" id="security">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">��������</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route("profile.password") }}" method="POST">
                                @csrf
                                @method("PUT")
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">��ǰ����</label>
                                    <input type="password" class="form-control @error("current_password") is-invalid @enderror" id="current_password" name="current_password" required>
                                    @error("current_password")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">������</label>
                                    <input type="password" class="form-control @error("password") is-invalid @enderror" id="password" name="password" required>
                                    @error("password")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">ȷ��������</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-key me-1"></i> ��������
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">˫������֤</h5>
                        </div>
                        <div class="card-body">
                            <p>˫������֤Ϊ�����˻������˶���İ�ȫ�㡣���ú󣬳��������⣬������Ҫ������ֻ�Ӧ�û�ȡ����֤�롣</p>
                            
                            @if($user->two_factor_enabled)
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i> ��������˫������֤
                                </div>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#disable2faModal">
                                        <i class="fas fa-times me-1"></i> ����˫������֤
                                    </button>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i> ����δ����˫������֤
                                </div>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#enable2faModal">
                                        <i class="fas fa-shield-alt me-1"></i> ����˫������֤
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- �ҵĻ��� -->
                <div class="tab-pane fade" id="points">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">���ָ���</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <div class="p-3 rounded bg-light">
                                        <h3>{{ $pointsStats["available"] }}</h3>
                                        <small class="text-muted">���û���</small>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <div class="p-3 rounded bg-light">
                                        <h3>{{ $pointsStats["total_earned"] }}</h3>
                                        <small class="text-muted">�ۼƻ��</small>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <div class="p-3 rounded bg-light">
                                        <h3>{{ $pointsStats["total_consumed"] }}</h3>
                                        <small class="text-muted">��ʹ��</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 rounded bg-light">
                                        <h3>{{ $pointsStats["expired"] }}</h3>
                                        <small class="text-muted">�ѹ���</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">������ʷ</h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="pointsFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-filter me-1"></i> ɸѡ
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="pointsFilterDropdown">
                                    <li><a class="dropdown-item active" href="#">ȫ��</a></li>
                                    <li><a class="dropdown-item" href="#">��û���</a></li>
                                    <li><a class="dropdown-item" href="#">ʹ�û���</a></li>
                                    <li><a class="dropdown-item" href="#">���ڻ���</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>����</th>
                                            <th>����</th>
                                            <th>����</th>
                                            <th class="text-end">����</th>
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
                                                    <p class="mb-0">���޻��ּ�¼</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- �ҵ��Ƽ� -->
                <div class="tab-pane fade" id="referrals">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">�Ƽ�����</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="p-3 rounded bg-light">
                                        <h3>{{ $referralStats["total"] }}</h3>
                                        <small class="text-muted">���Ƽ�����</small>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="p-3 rounded bg-light">
                                        <h3>{{ $referralStats["completed"] }}</h3>
                                        <small class="text-muted">�ɹ��Ƽ�</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 rounded bg-light">
                                        <h3>{{ $referralStats["points"] }}</h3>
                                        <small class="text-muted">��û���</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">�ҵ��Ƽ�����</h5>
                        </div>
                        <div class="card-body">
                            <p>���������Ƽ����ӣ��������ע�ᣬ˫��������ý�����</p>
                            
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" value="{{ $referralLink }}" id="referralLinkProfile" readonly>
                                <button class="btn btn-outline-primary" type="button" onclick="copyReferralLinkProfile()">
                                    <i class="fas fa-copy"></i> ����
                                </button>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <a href="https://twitter.com/intent/tweet?text=����AlingAi Pro��ʹ���ҵ��Ƽ����ӻ�ý�����{{ urlencode($referralLink) }}" target="_blank" class="btn btn-outline-primary">
                                    <i class="fab fa-twitter me-1"></i> Twitter
                                </a>
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($referralLink) }}" target="_blank" class="btn btn-outline-primary">
                                    <i class="fab fa-facebook me-1"></i> Facebook
                                </a>
                                <a href="https://api.whatsapp.com/send?text=����AlingAi Pro��ʹ���ҵ��Ƽ����ӻ�ý�����{{ urlencode($referralLink) }}" target="_blank" class="btn btn-outline-primary">
                                    <i class="fab fa-whatsapp me-1"></i> WhatsApp
                                </a>
                                <a href="mailto:?subject=����������AlingAi Pro&body=���ã���������������AlingAi Proƽ̨��ʹ���ҵ��Ƽ�����ע�ᣬ����˫��������ý�����{{ $referralLink }}" class="btn btn-outline-primary">
                                    <i class="fas fa-envelope me-1"></i> �ʼ�
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">�Ƽ���¼</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>�û�</th>
                                            <th>ע��ʱ��</th>
                                            <th>״̬</th>
                                            <th class="text-end">����</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($referrals as $referral)
                                            <tr>
                                                <td>{{ $referral->referred->name }}</td>
                                                <td>{{ $referral->created_at->format("Y-m-d") }}</td>
                                                <td>
                                                    <span class="badge {{ $referral->status == "completed" ? "bg-success" : "bg-warning" }}">
                                                        {{ $referral->status == "completed" ? "�����" : "������" }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    @if($referral->points_awarded > 0)
                                                        <span class="text-success">+{{ $referral->points_awarded }} ����</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4">
                                                    <p class="mb-0">�����Ƽ���¼</p>
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

<!-- ����ͷ��ģ̬�� -->
<div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="avatarModalLabel">����ͷ��</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route("profile.update") }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method("PUT")
                    
                    <div class="mb-3">
                        <label for="avatar" class="form-label">ѡ��ͼƬ</label>
                        <input class="form-control @error("avatar") is-invalid @enderror" type="file" id="avatar" name="avatar" accept="image/*">
                        <div class="form-text">֧��JPG��PNG��GIF��ʽ�����2MB</div>
                        @error("avatar")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">�ϴ�ͷ��</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ����˫������֤ģ̬�� -->
<div class="modal fade" id="enable2faModal" tabindex="-1" aria-labelledby="enable2faModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="enable2faModalLabel">����˫������֤</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>��ʹ�����������֤��Ӧ��ɨ�����¶�ά�룺</p>
                <div class="text-center mb-3">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=otpauth://totp/AlingAi:{{ $user->email }}?secret=JBSWY3DPEHPK3PXP&issuer=AlingAi" alt="��ά��" class="img-fluid">
                </div>
                <p>�����ֶ�����������Կ��</p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" value="JBSWY3DPEHPK3PXP" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copySecret()">����</button>
                </div>
                <form id="verify2faForm">
                    <div class="mb-3">
                        <label for="verification_code" class="form-label">��֤��</label>
                        <input type="text" class="form-control" id="verification_code" name="verification_code" placeholder="����6λ��֤��" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">��֤������</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ����˫������֤ģ̬�� -->
<div class="modal fade" id="disable2faModal" tabindex="-1" aria-labelledby="disable2faModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="disable2faModalLabel">����˫������֤</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i> ���棺����˫������֤���������˻��İ�ȫ�ԡ�
                </div>
                <form id="disable2faForm">
                    <div class="mb-3">
                        <label for="current_password_2fa" class="form-label">��ǰ����</label>
                        <input type="password" class="form-control" id="current_password_2fa" name="current_password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-danger">����˫������֤</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
    // �����ǩҳ�л�
    document.addEventListener("DOMContentLoaded", function() {
        // ��ȡURL�е�ê��
        var hash = window.location.hash;
        if (hash) {
            // �����Ӧ�ı�ǩҳ
            $("a[href='" + hash + "']").tab("show");
        }
        
        // ��URL�е�ê��仯ʱ�л���ǩҳ
        window.addEventListener("hashchange", function() {
            var hash = window.location.hash;
            if (hash) {
                $("a[href='" + hash + "']").tab("show");
            }
        });
        
        // ����ǩҳ�л�ʱ����URL�е�ê��
        $("a[data-bs-toggle='list']").on("shown.bs.tab", function(e) {
            history.replaceState(null, null, $(e.target).attr("href"));
        });
    });
    
    // �����Ƽ�����
    function copyReferralLinkProfile() {
        var copyText = document.getElementById("referralLinkProfile");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        
        // ��ʾ��ʾ
        alert("�Ƽ������Ѹ��Ƶ������壡");
    }
    
    // ���ƶ�ά����Կ
    function copySecret() {
        var copyText = document.querySelector("#enable2faModal .input-group input");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        
        // ��ʾ��ʾ
        alert("��Կ�Ѹ��Ƶ������壡");
    }
</script>
@endsection
