@php
    use App\Models\OAuth\Provider;
    $providers = Provider::where("is_active", true)->get();
@endphp

@if($providers->isNotEmpty())
    <div class="social-auth-links text-center mb-3">
        <p>- 或者使用以下方式登录 -</p>
        
        <div class="d-flex flex-wrap justify-content-center">
            @foreach($providers as $provider)
                @if($provider->client_id && $provider->redirect_url)
                    <a href="{{ route("auth.oauth.redirect", $provider->identifier) }}" class="btn btn-block {{ $provider->identifier == "wechat" ? "btn-success" : ($provider->identifier == "github" ? "btn-dark" : "btn-primary") }} m-1" style="max-width: 200px;">
                        <i class="{{ $provider->icon }}"></i> {{ $provider->name }}登录
                    </a>
                @endif
            @endforeach
        </div>
    </div>
@endif
