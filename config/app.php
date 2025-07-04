<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 应用名称
    |--------------------------------------------------------------------------
    |
    | 此值是应用程序的名称。此值用于在框架需要放置应用程序的名称的地方，
    | 例如通知中。
    |
    */

    "name" => env("APP_NAME", "AlingAi"),

    /*
    |--------------------------------------------------------------------------
    | 应用环境
    |--------------------------------------------------------------------------
    |
    | 此值确定应用程序当前运行的"环境"。这可能会确定您希望如何配置应用程序的
    | 各种服务。在此处设置此值与在 .env 文件中设置 APP_ENV 相同。
    |
    */

    "env" => env("APP_ENV", "production"),

    /*
    |--------------------------------------------------------------------------
    | 应用调试模式
    |--------------------------------------------------------------------------
    |
    | 当您的应用程序处于调试模式时，将向您的用户显示详细的错误消息以及应用程序
    | 错误。如果禁用，则将显示一个简单的通用错误页面。
    |
    */

    "debug" => (bool) env("APP_DEBUG", false),

    /*
    |--------------------------------------------------------------------------
    | 应用URL
    |--------------------------------------------------------------------------
    |
    | 此URL用于在生成URL时由控制台命令使用。您应该设置此值为应用程序的根目录，
    | 以便在使用URL生成器时获得正确的URL。
    |
    */

    "url" => env("APP_URL", "http://localhost"),

    "asset_url" => env("ASSET_URL"),

    /*
    |--------------------------------------------------------------------------
    | 应用时区
    |--------------------------------------------------------------------------
    |
    | 这里您可以指定应用程序在处理日期和时间时使用的默认时区。
    | 我们已经为您设置了一个合理的默认值。
    |
    */

    "timezone" => "Asia/Shanghai",

    /*
    |--------------------------------------------------------------------------
    | 应用区域设置配置
    |--------------------------------------------------------------------------
    |
    | 应用程序区域设置确定用于翻译和格式化日期、数字和其他本地化值的默认区域设置。
    | 您可以根据应用程序支持的区域设置设置此值。
    |
    */

    "locale" => "zh_CN",

    /*
    |--------------------------------------------------------------------------
    | 应用回退区域设置
    |--------------------------------------------------------------------------
    |
    | 回退区域设置确定当当前区域设置不可用时使用哪个区域设置。
    | 您可以更改此值以匹配应用程序支持的任何区域设置。
    |
    */

    "fallback_locale" => "en",

    /*
    |--------------------------------------------------------------------------
    | 伪装区域设置
    |--------------------------------------------------------------------------
    |
    | 此区域设置将用于Carbon库的日期格式化，以及可能希望在将日期显示给用户之前
    | 以不同的区域设置对其进行格式化的其他库。
    |
    */

    "faker_locale" => "zh_CN",

    /*
    |--------------------------------------------------------------------------
    | 加密密钥
    |--------------------------------------------------------------------------
    |
    | 此密钥由Illuminate加密服务使用，应该在您的环境文件中设置。
    | 请不要使用示例值，而是在运行Artisan命令之前生成一个好的密钥。
    |
    */

    "key" => env("APP_KEY"),

    "cipher" => "AES-256-CBC",

    /*
    |--------------------------------------------------------------------------
    | 自动加载的服务提供者
    |--------------------------------------------------------------------------
    |
    | 下面列出的服务提供者将在应用程序的请求中自动加载。您可以随意向此数组添加
    | 自己的服务，或者如果您喜欢，您可以在"app/Providers"目录中加载它们。
    |
    */

    "providers" => [

        /*
         * Laravel框架服务提供者...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * 应用服务提供者...
         */
        App\Providers\RouteServiceProvider::class,
        App\Providers\PaymentServiceProvider::class,
        App\Providers\SecurityServiceProvider::class,
        App\Providers\MCPServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | 类别别名
    |--------------------------------------------------------------------------
    |
    | 此数组的别名将在应用程序引导时注册。但是，您可以自由地在应用程序中的任何地方
    | 注册尽可能多的别名，因为别名加载器是"延迟"加载的。
    |
    */

    "aliases" => [

        "App" => Illuminate\Support\Facades\App::class,
        "Arr" => Illuminate\Support\Arr::class,
        "Artisan" => Illuminate\Support\Facades\Artisan::class,
        "Auth" => Illuminate\Support\Facades\Auth::class,
        "Blade" => Illuminate\Support\Facades\Blade::class,
        "Broadcast" => Illuminate\Support\Facades\Broadcast::class,
        "Bus" => Illuminate\Support\Facades\Bus::class,
        "Cache" => Illuminate\Support\Facades\Cache::class,
        "Config" => Illuminate\Support\Facades\Config::class,
        "Cookie" => Illuminate\Support\Facades\Cookie::class,
        "Crypt" => Illuminate\Support\Facades\Crypt::class,
        "Date" => Illuminate\Support\Carbon::class,
        "DB" => Illuminate\Support\Facades\DB::class,
        "Eloquent" => Illuminate\Database\Eloquent\Model::class,
        "Event" => Illuminate\Support\Facades\Event::class,
        "File" => Illuminate\Support\Facades\File::class,
        "Gate" => Illuminate\Support\Facades\Gate::class,
        "Hash" => Illuminate\Support\Facades\Hash::class,
        "Http" => Illuminate\Support\Facades\Http::class,
        "Lang" => Illuminate\Support\Facades\Lang::class,
        "Log" => Illuminate\Support\Facades\Log::class,
        "Mail" => Illuminate\Support\Facades\Mail::class,
        "Notification" => Illuminate\Support\Facades\Notification::class,
        "Password" => Illuminate\Support\Facades\Password::class,
        "Queue" => Illuminate\Support\Facades\Queue::class,
        "Redirect" => Illuminate\Support\Facades\Redirect::class,
        "Request" => Illuminate\Support\Facades\Request::class,
        "Response" => Illuminate\Support\Facades\Response::class,
        "Route" => Illuminate\Support\Facades\Route::class,
        "Schema" => Illuminate\Support\Facades\Schema::class,
        "Session" => Illuminate\Support\Facades\Session::class,
        "Storage" => Illuminate\Support\Facades\Storage::class,
        "Str" => Illuminate\Support\Str::class,
        "URL" => Illuminate\Support\Facades\URL::class,
        "Validator" => Illuminate\Support\Facades\Validator::class,
        "View" => Illuminate\Support\Facades\View::class,

    ],

];
