<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\FileController;
use App\Http\Controllers\User\MemoryController;
use App\Http\Controllers\User\ConversationController;
use App\Http\Controllers\User\VerificationController;
use App\Http\Controllers\User\SecurityController;
use App\Http\Controllers\User\Analytics\UserAnalyticsController;
use App\Http\Controllers\Admin\User\FileController as AdminFileController;
use App\Http\Controllers\Admin\User\MemoryController as AdminMemoryController;
use App\Http\Controllers\Admin\User\ConversationController as AdminConversationController;
use App\Http\Controllers\Admin\User\VerificationController as AdminVerificationController;
use App\Http\Controllers\Admin\User\SecurityController as AdminSecurityController;
use App\Http\Controllers\Admin\User\Analytics\AdminAnalyticsController;

/*
|--------------------------------------------------------------------------
| 用户管理路由
|--------------------------------------------------------------------------
*/

// 前台用户文件管理路由
Route::middleware([\
auth\])->prefix(\user/files\)->name(\user.files.\)->group(function () {
    Route::get(\/\, [FileController::class, \index\])->name(\index\);
    Route::get(\/create\, [FileController::class, \create\])->name(\create\);
    Route::post(\/\, [FileController::class, \store\])->name(\store\);
    Route::get(\/
id
\, [FileController::class, \show\])->name(\show\);
    Route::get(\/
id
/edit\, [FileController::class, \edit\])->name(\edit\);
    Route::put(\/
id
\, [FileController::class, \update\])->name(\update\);
    Route::delete(\/
id
\, [FileController::class, \destroy\])->name(\destroy\);
    Route::get(\/
id
/download\, [FileController::class, \download\])->name(\download\);
    
    // 分类管理
    Route::get(\/categories\, [FileController::class, \categories\])->name(\categories\);
    Route::post(\/categories\, [FileController::class, \storeCategory\])->name(\categories.store\);
    Route::put(\/categories/
id
\, [FileController::class, \updateCategory\])->name(\categories.update\);
    Route::delete(\/categories/
id
\, [FileController::class, \destroyCategory\])->name(\categories.destroy\);
});

// 前台用户记忆管理路由
Route::middleware([\auth\])->prefix(\user/memories\)->name(\user.memories.\)->group(function () {
    Route::get(\/\, [MemoryController::class, \index\])->name(\index\);
    Route::get(\/create\, [MemoryController::class, \create\])->name(\create\);
    Route::post(\/\, [MemoryController::class, \store\])->name(\store\);
    Route::get(\/
id
\, [MemoryController::class, \show\])->name(\show\);
    Route::get(\/
id
/edit\, [MemoryController::class, \edit\])->name(\edit\);
    Route::put(\/
id
\, [MemoryController::class, \update\])->name(\update\);
    Route::delete(\/
id
\, [MemoryController::class, \destroy\])->name(\destroy\);
    Route::post(\/search\, [MemoryController::class, \search\])->name(\search\);
});

// 前台用户对话管理路由
Route::middleware([\auth\])->prefix(\user/conversations\)->name(\user.conversations.\)->group(function () {
    Route::get(\/\, [ConversationController::class, \index\])->name(\index\);
    Route::get(\/create\, [ConversationController::class, \create\])->name(\create\);
    Route::post(\/\, [ConversationController::class, \store\])->name(\store\);
    Route::get(\/
id
\, [ConversationController::class, \show\])->name(\show\);
    Route::get(\/
id
/edit\, [ConversationController::class, \edit\])->name(\edit\);
    Route::put(\/
id
\, [ConversationController::class, \update\])->name(\update\);
    Route::delete(\/
id
\, [ConversationController::class, \destroy\])->name(\destroy\);
    Route::post(\/
id
/clear\, [ConversationController::class, \clear\])->name(\clear\);
    Route::post(\/
id
/pin\, [ConversationController::class, \togglePin\])->name(\pin\);
    Route::post(\/
id
/archive\, [ConversationController::class, \toggleArchive\])->name(\archive\);
    Route::post(\/
id
/message\, [ConversationController::class, \sendMessage\])->name(\message\);
    Route::get(\/
id
/export\, [ConversationController::class, \export\])->name(\export\);
});

// 前台用户认证管理路由
Route::middleware([\auth\])->prefix(\user/verifications\)->name(\user.verifications.\)->group(function () {
    Route::get(\/\, [VerificationController::class, \index\])->name(\index\);
    Route::get(\/create\, [VerificationController::class, \create\])->name(\create\);
    Route::post(\/\, [VerificationController::class, \store\])->name(\store\);
    Route::get(\/
id
\, [VerificationController::class, \show\])->name(\show\);
    Route::delete(\/
id
\, [VerificationController::class, \cancel\])->name(\cancel\);
    Route::post(\/
id
/resubmit\, [VerificationController::class, \resubmit\])->name(\resubmit\);
});

// 前台用户安全管理路由
Route::middleware([\auth\])->prefix(\user/security\)->name(\user.security.\)->group(function () {
    Route::get(\/\, [SecurityController::class, \index\])->name(\index\);
    Route::get(\/two-factor\, [SecurityController::class, \twoFactorSetup\])->name(\two-factor\);
    Route::post(\/two-factor\, [SecurityController::class, \twoFactorActivate\])->name(\two-factor.activate\);
    Route::get(\/two-factor/disable\, [SecurityController::class, \twoFactorDisableForm\])->name(\two-factor.disable-form\);
    Route::post(\/two-factor/disable\, [SecurityController::class, \twoFactorDisable\])->name(\two-factor.disable\);
    Route::get(\/logs\, [SecurityController::class, \logs\])->name(\logs\);
    Route::get(\/sessions\, [SecurityController::class, \sessions\])->name(\sessions\);
    Route::delete(\/sessions/
sessionId
\, [SecurityController::class, \revokeSession\])->name(\sessions.revoke\);
    Route::post(\/sessions/revoke-others\, [SecurityController::class, \revokeOtherSessions\])->name(\sessions.revoke-others\);
    Route::get(\/change-password\, [SecurityController::class, \changePasswordForm\])->name(\change-password\);
    Route::post(\/change-password\, [SecurityController::class, \changePassword\])->name(\change-password.update\);
});

// 前台用户统计分析路由
Route::middleware([\auth\])->prefix(\user/analytics\)->name(\user.analytics.\)->group(function () {
    Route::get(\/\, [UserAnalyticsController::class, \dashboard\])->name(\dashboard\);
    Route::get(\/activity\, [UserAnalyticsController::class, \activity\])->name(\activity\);
    Route::get(\/resources\, [UserAnalyticsController::class, \resources\])->name(\resources\);
    Route::get(\/behavior\, [UserAnalyticsController::class, \behavior\])->name(\behavior\);
});

/*
|--------------------------------------------------------------------------
| 后台用户管理路由
|--------------------------------------------------------------------------
*/

// 后台用户文件管理路由
Route::middleware([\auth:admin\])->prefix(\admin/users/
userId
/files\)->name(\admin.users.files.\)->group(function () {
    Route::get(\/\, [AdminFileController::class, \index\])->name(\index\);
    Route::get(\/
id
\, [AdminFileController::class, \show\])->name(\show\);
    Route::get(\/
id
/edit\, [AdminFileController::class, \edit\])->name(\edit\);
    Route::put(\/
id
\, [AdminFileController::class, \update\])->name(\update\);
    Route::delete(\/
id
\, [AdminFileController::class, \destroy\])->name(\destroy\);
    Route::get(\/
id
/download\, [AdminFileController::class, \download\])->name(\download\);
    
    // 分类管理
    Route::get(\/categories\, [AdminFileController::class, \categories\])->name(\categories\);
    Route::post(\/categories\, [AdminFileController::class, \storeCategory\])->name(\categories.store\);
    Route::put(\/categories/
id
\, [AdminFileController::class, \updateCategory\])->name(\categories.update\);
    Route::delete(\/categories/
id
\, [AdminFileController::class, \destroyCategory\])->name(\categories.destroy\);
});

// 后台用户记忆管理路由
Route::middleware([\auth:admin\])->prefix(\admin/users/
userId
/memories\)->name(\admin.users.memories.\)->group(function () {
    Route::get(\/\, [AdminMemoryController::class, \index\])->name(\index\);
    Route::get(\/create\, [AdminMemoryController::class, \create\])->name(\create\);
    Route::post(\/\, [AdminMemoryController::class, \store\])->name(\store\);
    Route::get(\/
id
\, [AdminMemoryController::class, \show\])->name(\show\);
    Route::get(\/
id
/edit\, [AdminMemoryController::class, \edit\])->name(\edit\);
    Route::put(\/
id
\, [AdminMemoryController::class, \update\])->name(\update\);
    Route::delete(\/
id
\, [AdminMemoryController::class, \destroy\])->name(\destroy\);
});

// 后台用户对话管理路由
Route::middleware([\auth:admin\])->prefix(\admin/users/
userId
/conversations\)->name(\admin.users.conversations.\)->group(function () {
    Route::get(\/\, [AdminConversationController::class, \index\])->name(\index\);
    Route::get(\/
id
\, [AdminConversationController::class, \show\])->name(\show\);
    Route::get(\/
id
/edit\, [AdminConversationController::class, \edit\])->name(\edit\);
    Route::put(\/
id
\, [AdminConversationController::class, \update\])->name(\update\);
    Route::delete(\/
id
\, [AdminConversationController::class, \destroy\])->name(\destroy\);
    Route::post(\/
id
/clear\, [AdminConversationController::class, \clear\])->name(\clear\);
    Route::post(\/
id
/pin\, [AdminConversationController::class, \togglePin\])->name(\pin\);
    Route::post(\/
id
/archive\, [AdminConversationController::class, \toggleArchive\])->name(\archive\);
    Route::get(\/
id
/export\, [AdminConversationController::class, \export\])->name(\export\);
});

// 后台用户认证管理路由
Route::middleware([\auth:admin\])->prefix(\admin/users/verifications\)->name(\admin.users.verifications.\)->group(function () {
    Route::get(\/\, [AdminVerificationController::class, \index\])->name(\index\);
    Route::get(\/pending\, [AdminVerificationController::class, \pending\])->name(\pending\);
    Route::get(\/
id
\, [AdminVerificationController::class, \show\])->name(\show\);
    Route::post(\/
id
/review\, [AdminVerificationController::class, \review\])->name(\review\);
    Route::get(\/documents/
documentId
/download\, [AdminVerificationController::class, \downloadDocument\])->name(\documents.download\);
});

// 后台用户安全管理路由
Route::middleware([\auth:admin\])->prefix(\admin/users/
userId
/security\)->name(\admin.users.security.\)->group(function () {
    Route::get(\/\, [AdminSecurityController::class, \index\])->name(\index\);
    Route::get(\/logs\, [AdminSecurityController::class, \logs\])->name(\logs\);
    Route::get(\/sessions\, [AdminSecurityController::class, \sessions\])->name(\sessions\);
    Route::delete(\/sessions/
sessionId
\, [AdminSecurityController::class, \revokeSession\])->name(\sessions.revoke\);
    Route::post(\/sessions/revoke-all\, [AdminSecurityController::class, \revokeAllSessions\])->name(\sessions.revoke-all\);
    Route::get(\/credentials\, [AdminSecurityController::class, \credentials\])->name(\credentials\);
    Route::post(\/credentials/
credentialId
/disable\, [AdminSecurityController::class, \disableCredential\])->name(\credentials.disable\);
    Route::post(\/credentials/
credentialId
/enable\, [AdminSecurityController::class, \enableCredential\])->name(\credentials.enable\);
    Route::delete(\/credentials/
credentialId
\, [AdminSecurityController::class, \deleteCredential\])->name(\credentials.delete\);
    Route::post(\/two-factor/reset\, [AdminSecurityController::class, \resetTwoFactor\])->name(\two-factor.reset\);
    Route::post(\/lock\, [AdminSecurityController::class, \lockAccount\])->name(\lock\);
    Route::post(\/unlock\, [AdminSecurityController::class, \unlockAccount\])->name(\unlock\);
});

// 后台用户统计分析路由
Route::middleware([\auth:admin\])->prefix(\admin/analytics\)->name(\admin.analytics.\)->group(function () {
    Route::get(\/\, [AdminAnalyticsController::class, \dashboard\])->name(\dashboard\);
    Route::get(\/platform-activity\, [AdminAnalyticsController::class, \platformActivity\])->name(\platform-activity\);
    Route::get(\/platform-resources\, [AdminAnalyticsController::class, \platformResources\])->name(\platform-resources\);
    Route::get(\/user-growth\, [AdminAnalyticsController::class, \userGrowth\])->name(\user-growth\);
    Route::post(\/generate-stats\, [AdminAnalyticsController::class, \generateGrowthStats\])->name(\generate-stats\);
    Route::get(\/users/
userId
\, [AdminAnalyticsController::class, \userStats\])->name(\user-stats\);
});
