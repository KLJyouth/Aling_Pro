@php
    use App\Models\OAuth\Provider;
    $providers = Provider::where("is_active", true)->get();
    $userAccounts = auth()->user()->oauthAccounts()->with("provider")->get();
@endphp

<div class="card">
    <div class="card-header">
        <h3 class="card-title">第三方账号关联</h3>
    </div>
    <div class="card-body">
        <p class="text-muted">
            关联第三方账号后，您可以使用这些账号直接登录系统，无需输入密码。
        </p>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>提供商</th>
                        <th>状态</th>
                        <th>关联时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($providers as $provider)
                        @php
                            $account = $userAccounts->first(function($account) use ($provider) {
                                return $account->provider_id == $provider->id;
                            });
                        @endphp
                        <tr>
                            <td>
                                <i class="{{ $provider->icon }}"></i> {{ $provider->name }}
                            </td>
                            <td>
                                @if($account)
                                    <span class="badge badge-success">已关联</span>
                                @else
                                    <span class="badge badge-secondary">未关联</span>
                                @endif
                            </td>
                            <td>
                                {{ $account ? $account->created_at : "未关联" }}
                            </td>
                            <td>
                                @if($account)
                                    <form action="{{ route("auth.oauth.unlink", $provider->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\"确定要解除{{ $provider->name }}账号关联吗？\")">
                                            <i class="fas fa-unlink"></i> 解除关联
                                        </button>
                                    </form>
                                @else
                                    @if($provider->client_id && $provider->redirect_url)
                                        <a href="{{ route("auth.oauth.redirect", $provider->identifier) }}?redirect={{ url()->current() }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-link"></i> 关联账号
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled>未配置</button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    
                    @if($providers->isEmpty())
                        <tr>
                            <td colspan="4" class="text-center">暂无可用的第三方登录提供商</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
