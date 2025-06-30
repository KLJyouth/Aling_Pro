<?php

use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 前台路由
Route::get("/", [HomeController::class, "index"])->name("home");
Route::get("/about", [PageController::class, "about"])->name("about");
Route::get("/features", [PageController::class, "features"])->name("features");
Route::get("/pricing", [PageController::class, "pricing"])->name("pricing");
Route::get("/contact", [PageController::class, "contact"])->name("contact");
Route::post("/contact", [PageController::class, "submitContact"])->name("submit-contact");
Route::get("/terms", [PageController::class, "terms"])->name("terms");
Route::get("/privacy", [PageController::class, "privacy"])->name("privacy");
Route::get("/faq", [PageController::class, "faq"])->name("faq");
Route::get("/team", [PageController::class, "team"])->name("team");
Route::get("/careers", [PageController::class, "careers"])->name("careers");
Route::get("/examples", [PageController::class, "examples"])->name("examples");
Route::get("/tutorials", [PageController::class, "tutorials"])->name("tutorials");
Route::get("/support", [PageController::class, "support"])->name("support");
Route::get("/security", [PageController::class, "security"])->name("security");
Route::get("/blog", [PageController::class, "blog"])->name("blog");
Route::get("/blog/{slug}", [PageController::class, "blogPost"])->name("blog.post");

// 认证路由
Auth::routes(["verify" => true]);

// 需要认证的路由
Route::middleware(["auth"])->group(function () {
    // 仪表盘
    Route::get("/dashboard", [DashboardController::class, "index"])->name("dashboard");
    
    // 个人资料
    Route::get("/profile", [ProfileController::class, "show"])->name("profile");
    Route::put("/profile", [ProfileController::class, "update"])->name("profile.update");
    Route::put("/profile/password", [ProfileController::class, "updatePassword"])->name("profile.password");
    
    // 会员管理
    Route::get("/subscription", [MembershipController::class, "index"])->name("subscription");
    Route::get("/subscription/upgrade", [MembershipController::class, "showUpgrade"])->name("subscription.upgrade");
    Route::post("/subscription/upgrade", [MembershipController::class, "processUpgrade"])->name("subscription.upgrade.process");
    Route::post("/subscription/cancel", [MembershipController::class, "cancel"])->name("subscription.cancel");
    Route::get("/subscription/history", [MembershipController::class, "history"])->name("subscription.history");
    Route::get("/membership/subscribe/{id}", [MembershipController::class, "subscribe"])->name("membership.subscribe");
    
    // API密钥管理
    Route::get("/api-keys", [ApiKeyController::class, "index"])->name("api-keys");
    Route::post("/api-keys", [ApiKeyController::class, "store"])->name("api-keys.store");
    Route::put("/api-keys/{id}", [ApiKeyController::class, "update"])->name("api-keys.update");
    Route::delete("/api-keys/{id}", [ApiKeyController::class, "destroy"])->name("api-keys.destroy");
    
    // API文档和测试工具
    Route::get("/api-docs", [PageController::class, "apiDocs"])->name("api-docs");
    Route::get("/api-playground", [PageController::class, "apiPlayground"])->name("api-playground");
    
    // 支付
    Route::get("/payment/{orderNo}", [PaymentController::class, "show"])->name("payment.show");
    Route::post("/payment/{orderNo}", [PaymentController::class, "process"])->name("payment.process");
    Route::get("/payment/{orderNo}/query", [PaymentController::class, "query"])->name("payment.query");
    
    // 订单
    Route::get("/orders", [OrderController::class, "index"])->name("orders");
    Route::get("/orders/{id}", [OrderController::class, "show"])->name("order.show");
});

// 支付回调
Route::prefix("payment")->group(function () {
    // 支付通知
    Route::post("/notify/alipay", [PaymentController::class, "alipayNotify"]);
    Route::post("/notify/wechat", [PaymentController::class, "wechatNotify"]);
    Route::post("/notify/card", [PaymentController::class, "cardNotify"]);
    
    // 支付同步回调
    Route::get("/return/alipay", [PaymentController::class, "alipayReturn"]);
    Route::get("/return/wechat", [PaymentController::class, "wechatReturn"]);
    Route::get("/return/card", [PaymentController::class, "cardReturn"]);
});

// 管理员路由
Route::prefix("admin")->middleware(["auth", "admin"])->group(function () {
    // 管理员仪表盘
    Route::get("/", "App\\Http\\Controllers\\Admin\\DashboardController@index")->name("admin.dashboard");
    
    // 会员管理
    Route::resource("members", "App\\Http\\Controllers\\Admin\\MemberController");
    Route::resource("membership-levels", "App\\Http\\Controllers\\Admin\\MembershipLevelController");
    Route::resource("membership-privileges", "App\\Http\\Controllers\\Admin\\MembershipPrivilegeController");
    
    // 订单管理
    Route::resource("orders", "App\\Http\\Controllers\\Admin\\OrderController");
    
    // 系统设置
    Route::get("/settings", "App\\Http\\Controllers\\Admin\\SettingController@index")->name("admin.settings");
    Route::post("/settings", "App\\Http\\Controllers\\Admin\\SettingController@update")->name("admin.settings.update");
});
