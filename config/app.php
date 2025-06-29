<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 应用名称
    |--------------------------------------------------------------------------
    |
    | 此值是应用程序的名称。此值用于在框架需要放置应用程序名称的
    | 任何位置显示应用程序的名称。
    |
    */

    'name' => env('APP_NAME', 'AlingAi_pro'),

    /*
    |--------------------------------------------------------------------------
    | 应用环境
    |--------------------------------------------------------------------------
    |
    | 此值确定应用程序当前运行的"环境"。这可能会决定您希望如何
    | 配置应用程序的各种服务。可以在 .env 文件中设置此值。
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | 应用调试模式
    |--------------------------------------------------------------------------
    |
    | 当您的应用程序处于调试模式时，将向用户显示详细的错误消息以及
    | 应用程序错误。如果禁用，将显示一个简单的通用错误页面。
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | 应用URL
    |--------------------------------------------------------------------------
    |
    | 此URL用于控制台命令和其他功能需要知道应用程序的URL时。
    | 您应该在根目录的 .env 文件中设置此值。
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | 应用时区
    |--------------------------------------------------------------------------
    |
    | 这里您可以指定应用程序使用的默认时区，该时区将由PHP日期
    | 和日期时间函数使用。我们已经为您设置了一个合理的默认值。
    |
    */

    'timezone' => 'Asia/Shanghai',

    /*
    |--------------------------------------------------------------------------
    | 应用语言环境
    |--------------------------------------------------------------------------
    |
    | 应用程序语言环境决定了用于显示日期、数字等格式的默认语言环境，
    | 可以根据用户的偏好设置为任何语言环境。
    |
    */

    'locale' => 'zh_CN',

    /*
    |--------------------------------------------------------------------------
    | 应用回退语言环境
    |--------------------------------------------------------------------------
    |
    | 回退语言环境决定了当当前语言环境不可用时要使用的语言环境，例如当
    | 翻译器无法找到当前语言环境的翻译时。
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | 伪语言环境
    |--------------------------------------------------------------------------
    |
    | 此语言环境将用于在开发过程中测试您的应用程序的翻译。当您设置此值时，
    | 所有的翻译键都会被包裹在一个符号中，以便您快速注意到哪些字符串尚未翻译。
    |
    */

    'faker_locale' => 'zh_CN',

    /*
    |--------------------------------------------------------------------------
    | 加密密钥
    |--------------------------------------------------------------------------
    |
    | 此密钥由 Illuminate 加密服务使用，应在您的环境文件中设置。
    | 不要在版本控制中部署应用程序而不设置此值。
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | 自动加载的服务提供者
    |--------------------------------------------------------------------------
    |
    | 下面列出的服务提供者将在应用程序的请求中自动加载。您可以随意
    | 将自己的服务添加到此数组中，以便在应用程序中使用它们。
    |
    */

    'providers' => [

        /*
         * Laravel 框架服务提供者...
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
         * 包服务提供者...
         */

        /*
         * 应用服务提供者...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        
        /*
         * 数据库安全服务提供者...
         */
        App\Providers\DatabaseSecurityServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | 类别别名
    |--------------------------------------------------------------------------
    |
    | 此数组的别名将在应用程序引导时注册，并允许方便地
    | 为应用程序的类使用"真实"名称。
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'Date' => Illuminate\Support\Carbon::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Http' => Illuminate\Support\Facades\Http::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'Str' => Illuminate\Support\Str::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,

    ],

];
