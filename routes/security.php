<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Security\SecurityController;
use App\Http\Controllers\Security\PaymentSecurityController;

/*
|--------------------------------------------------------------------------
| 安全路由
|--------------------------------------------------------------------------
|
| 这里是应用程序的安全相关路由。
|
*/

// 安全验证
Route::get('/security/verify', [SecurityController::class, 'showVerifyPage'])
    ->name('security.verify');
Route::post('/security/verify', [SecurityController::class, 'verify']);

// 多因素认证
Route::group(['prefix' => 'auth/mfa', 'middleware' => ['auth']], function () {
    Route::get('/verify', [SecurityController::class, 'showMfaVerifyPage'])
        ->name('auth.mfa.verify');
    Route::post('/verify', [SecurityController::class, 'verifyMfa']);
    
    Route::get('/setup/{method}', [SecurityController::class, 'showMfaSetupPage'])
        ->name('auth.mfa.setup');
    Route::post('/setup/{method}', [SecurityController::class, 'setupMfa']);
    
    Route::get('/setup/verify', [SecurityController::class, 'showMfaSetupVerifyPage'])
        ->name('security.mfa.setup.verify');
    Route::post('/setup/verify', [SecurityController::class, 'verifyMfaSetup']);
    
    Route::post('/disable', [SecurityController::class, 'disableMfa'])
        ->name('auth.mfa.disable');
    Route::post('/primary', [SecurityController::class, 'setPrimaryMfaMethod'])
        ->name('auth.mfa.primary');
    Route::get('/recovery-codes', [SecurityController::class, 'generateRecoveryCodes'])
        ->name('auth.mfa.recovery-codes');
    Route::post('/recovery-codes/verify', [SecurityController::class, 'verifyRecoveryCode'])
        ->name('auth.mfa.verify-recovery');
});

// 设备绑定
Route::group(['prefix' => 'auth/device', 'middleware' => ['auth']], function () {
    Route::get('/bind', [SecurityController::class, 'showDeviceBindPage'])
        ->name('auth.device.bind');
    Route::post('/bind', [SecurityController::class, 'bindDevice']);
    
    Route::get('/verify', [SecurityController::class, 'showDeviceVerifyPage'])
        ->name('auth.device.verify');
    Route::post('/verify', [SecurityController::class, 'verifyDevice']);
    
    Route::post('/unbind', [SecurityController::class, 'unbindDevice'])
        ->name('auth.device.unbind');
});

// 安全设置
Route::group(['prefix' => 'user/security', 'middleware' => ['auth']], function () {
    Route::get('/settings', [SecurityController::class, 'showSecuritySettings'])
        ->name('user.security.settings');
    
    Route::post('/mfa/enable', [SecurityController::class, 'enableMfa'])
        ->name('user.security.mfa.enable');
});

// 支付安全
Route::group(['prefix' => 'payment/security', 'middleware' => ['auth']], function () {
    Route::get('/verify', [PaymentSecurityController::class, 'showPaymentVerifyPage'])
        ->name('payment.security.verify');
    Route::post('/verify', [PaymentSecurityController::class, 'verifyPayment']);
    
    Route::get('/risk-order/{orderId}', [PaymentSecurityController::class, 'showRiskOrderDetails'])
        ->name('payment.security.risk-order');
});

// 管理员安全路由
Route::group(['prefix' => 'admin/security', 'middleware' => ['auth', 'role:admin']], function () {
    Route::get('/payment-api', [PaymentSecurityController::class, 'showPaymentApiSecurity'])
        ->name('admin.security.payment-api');
    Route::post('/payment-api/rotate-keys', [PaymentSecurityController::class, 'rotateApiKeys'])
        ->name('admin.security.rotate-api-keys');
    
    Route::get('/payment-risk', [PaymentSecurityController::class, 'showPaymentRiskMonitoring'])
        ->name('admin.security.payment-risk');
    
    Route::get('/alerts', [SecurityController::class, 'showSecurityAlerts'])
        ->name('admin.security.alerts');
    Route::post('/alerts/{alertId}/resolve', [SecurityController::class, 'resolveAlert'])
        ->name('admin.security.resolve-alert');
    
    Route::get('/logs', [SecurityController::class, 'showSecurityLogs'])
        ->name('admin.security.logs');
});
