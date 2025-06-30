<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Security\QuantumSecurityDashboardController;
use App\Http\Controllers\User\MembershipController;
use App\Http\Controllers\User\QuotaController;

/*
|--------------------------------------------------------------------------
| Web·��
|--------------------------------------------------------------------------
|
| ���ﶨ����������Web��ص�·��
|
*/

// Ĭ����ҳ
Route::get("/", function () {
    return redirect("/dashboard");
});

// �Ǳ���·��
Route::get("/dashboard", function () {
    return view("dashboard");
})->name("dashboard");

// ���Ӱ�ȫ�Ǳ���·��
Route::get("/security/quantum-dashboard", [QuantumSecurityDashboardController::class, "index"])
    ->name("security.quantum-dashboard");

// ������ȫ���·��
Route::prefix("security")->group(function () {
    // ��ȫ����
    Route::get("/", function () {
        return view("security.overview");
    })->name("security.overview");
    
    // ��в����
    Route::get("/threats", function () {
        return view("security.threats");
    })->name("security.threats");
    
    // ©��ɨ��
    Route::get("/vulnerabilities", function () {
        return view("security.vulnerabilities");
    })->name("security.vulnerabilities");
    
    // ��ȫ����
    Route::get("/tests", function () {
        return view("security.tests");
    })->name("security.tests");
    
    // ���Ӽ���
    Route::get("/quantum-crypto", function () {
        return view("security.quantum-crypto");
    })->name("security.quantum-crypto");
});

// ��ȫ������·��
Route::prefix("security")->name("security.")->middleware(["auth", "admin"])->group(function () {
    // ���Ӱ�ȫ�Ǳ���
    Route::get("/quantum-dashboard", "Security\QuantumSecurityDashboardController@index")->name("quantum-dashboard");
    
    // ������·��
    Route::prefix("quarantine")->name("quarantine.")->group(function () {
        Route::get("/", "Security\QuarantineController@index")->name("index");
        Route::get("/ip-bans", "Security\QuarantineController@ipBans")->name("ip-bans");
        Route::get("/{id}", "Security\QuarantineController@show")->name("show");
        Route::post("/{id}/update-status", "Security\QuarantineController@updateStatus")->name("update-status");
        Route::post("/ban-ip", "Security\QuarantineController@banIp")->name("ban-ip");
        Route::post("/revoke-ip-ban/{id}", "Security\QuarantineController@revokeIpBan")->name("revoke-ip-ban");
    });
});

// ����ϵͳ·��
Route::prefix("tickets")->middleware(["auth"])->group(function () {
    // ��������
    Route::get("/", "Ticket\TicketController@index")->name("tickets.index");
    Route::get("/create", "Ticket\TicketController@create")->name("tickets.create");
    Route::post("/", "Ticket\TicketController@store")->name("tickets.store");
    Route::get("/{id}", "Ticket\TicketController@show")->name("tickets.show");
    Route::put("/{id}", "Ticket\TicketController@update")->name("tickets.update");
    
    // �����ظ�
    Route::post("/{id}/reply", "Ticket\TicketController@reply")->name("tickets.reply");
    
    // ��������
    Route::post("/{id}/assign", "Ticket\TicketController@assign")->name("tickets.assign");
    Route::post("/{id}/close", "Ticket\TicketController@close")->name("tickets.close");
    Route::post("/{id}/reopen", "Ticket\TicketController@reopen")->name("tickets.reopen");
    
    // ��������
    Route::delete("/{id}/attachment/{attachmentId}", "Ticket\TicketController@deleteAttachment")
        ->name("tickets.attachment.delete");
});

// �������źͷ������·�ɣ�������Ա��
Route::prefix("admin/tickets")->middleware(["auth", "role:admin"])->group(function () {
    // ���Ź���
    Route::get("/departments", "Ticket\TicketDepartmentController@index")->name("ticket.departments.index");
    Route::get("/departments/create", "Ticket\TicketDepartmentController@create")->name("ticket.departments.create");
    Route::post("/departments", "Ticket\TicketDepartmentController@store")->name("ticket.departments.store");
    Route::get("/departments/{id}/edit", "Ticket\TicketDepartmentController@edit")->name("ticket.departments.edit");
    Route::put("/departments/{id}", "Ticket\TicketDepartmentController@update")->name("ticket.departments.update");
    Route::delete("/departments/{id}", "Ticket\TicketDepartmentController@destroy")->name("ticket.departments.destroy");
    
    // �������
    Route::get("/categories", "Ticket\TicketCategoryController@index")->name("ticket.categories.index");
    Route::get("/categories/create", "Ticket\TicketCategoryController@create")->name("ticket.categories.create");
    Route::post("/categories", "Ticket\TicketCategoryController@store")->name("ticket.categories.store");
    Route::get("/categories/{id}/edit", "Ticket\TicketCategoryController@edit")->name("ticket.categories.edit");
    Route::put("/categories/{id}", "Ticket\TicketCategoryController@update")->name("ticket.categories.update");
    Route::delete("/categories/{id}", "Ticket\TicketCategoryController@destroy")->name("ticket.categories.destroy");
    Route::get("/categories/by-department", "Ticket\TicketCategoryController@getByDepartment")
        ->name("ticket.categories.by-department");
});

// ��վ��������·��
Route::prefix("admin/settings")->middleware(["auth", "role:admin"])->group(function () {
    Route::get("/", "Admin\SettingController@index")->name("admin.settings.index");
    Route::get("/group/{group}", "Admin\SettingController@showGroup")->name("admin.settings.group");
    Route::post("/group/{group}", "Admin\SettingController@saveGroup")->name("admin.settings.group.save");
    
    Route::get("/create", "Admin\SettingController@create")->name("admin.settings.create");
    Route::post("/", "Admin\SettingController@store")->name("admin.settings.store");
    Route::get("/{id}/edit", "Admin\SettingController@edit")->name("admin.settings.edit");
    Route::put("/{id}", "Admin\SettingController@update")->name("admin.settings.update");
    Route::delete("/{id}", "Admin\SettingController@destroy")->name("admin.settings.destroy");
    
    Route::post("/clear-cache", "Admin\SettingController@clearCache")->name("admin.settings.clear-cache");
    Route::post("/init-system", "Admin\SettingController@initSystemSettings")->name("admin.settings.init-system");
});

// ǰ̨����·��
Route::prefix("news")->name("news.")->group(function () {
    Route::get("/", "News\NewsController@index")->name("index");
    Route::get("/{slug}", "News\NewsController@show")->name("show");
    Route::get("/category/{slug}", "News\NewsController@category")->name("category");
    Route::get("/tag/{slug}", "News\NewsController@tag")->name("tag");
    Route::post("/{slug}/comment", "News\NewsController@comment")->name("comment");
});

// ��̨���Ź���·��
Route::prefix("admin/news")->name("admin.news.")->middleware(["auth", "role:admin,editor"])->group(function () {
    // ���Ź���
    Route::get("/", "Admin\News\NewsController@index")->name("index");
    Route::get("/create", "Admin\News\NewsController@create")->name("create");
    Route::post("/", "Admin\News\NewsController@store")->name("store");
    Route::get("/{id}/edit", "Admin\News\NewsController@edit")->name("edit");
    Route::put("/{id}", "Admin\News\NewsController@update")->name("update");
    Route::delete("/{id}", "Admin\News\NewsController@destroy")->name("destroy");
    
    // ���Ų���
    Route::post("/{id}/toggle-featured", "Admin\News\NewsController@toggleFeatured")->name("toggle-featured");
    Route::post("/{id}/publish", "Admin\News\NewsController@publish")->name("publish");
    Route::post("/{id}/draft", "Admin\News\NewsController@draft")->name("draft");
    Route::post("/{id}/archive", "Admin\News\NewsController@archive")->name("archive");
    
    // �������
    Route::get("/categories", "Admin\News\NewsCategoryController@index")->name("categories.index");
    Route::get("/categories/create", "Admin\News\NewsCategoryController@create")->name("categories.create");
    Route::post("/categories", "Admin\News\NewsCategoryController@store")->name("categories.store");
    Route::get("/categories/{id}/edit", "Admin\News\NewsCategoryController@edit")->name("categories.edit");
    Route::put("/categories/{id}", "Admin\News\NewsCategoryController@update")->name("categories.update");
    Route::delete("/categories/{id}", "Admin\News\NewsCategoryController@destroy")->name("categories.destroy");
    Route::post("/categories/{id}/toggle-status", "Admin\News\NewsCategoryController@toggleStatus")->name("categories.toggle-status");
    
    // ��ǩ����
    Route::get("/tags", "Admin\News\NewsTagController@index")->name("tags.index");
    Route::get("/tags/create", "Admin\News\NewsTagController@create")->name("tags.create");
    Route::post("/tags", "Admin\News\NewsTagController@store")->name("tags.store");
    Route::get("/tags/{id}/edit", "Admin\News\NewsTagController@edit")->name("tags.edit");
    Route::put("/tags/{id}", "Admin\News\NewsTagController@update")->name("tags.update");
    Route::delete("/tags/{id}", "Admin\News\NewsTagController@destroy")->name("tags.destroy");
    Route::post("/tags/{id}/toggle-status", "Admin\News\NewsTagController@toggleStatus")->name("tags.toggle-status");
    
    // ���۹���
    Route::get("/comments", "Admin\News\NewsCommentController@index")->name("comments.index");
    Route::get("/comments/{id}", "Admin\News\NewsCommentController@show")->name("comments.show");
    Route::post("/comments/{id}/approve", "Admin\News\NewsCommentController@approve")->name("comments.approve");
    Route::post("/comments/{id}/reject", "Admin\News\NewsCommentController@reject")->name("comments.reject");
    Route::post("/comments/{id}/reply", "Admin\News\NewsCommentController@reply")->name("comments.reply");
    Route::delete("/comments/{id}", "Admin\News\NewsCommentController@destroy")->name("comments.destroy");
    Route::post("/comments/batch-action", "Admin\News\NewsCommentController@batchAction")->name("comments.batch-action");
}); 

// OAuth·��
Route::prefix("auth")->name("auth.")->group(function () {
    Route::get("/{provider}/redirect", "OAuth\OAuthController@redirect")->name("oauth.redirect");
    Route::get("/{provider}/callback", "OAuth\OAuthController@callback")->name("oauth.callback");
    Route::post("/{provider}/unlink", "OAuth\OAuthController@unlink")->middleware("auth")->name("oauth.unlink");
});

// ֧�����·��
Route::prefix("payment")->name("payment.")->group(function () {
    // ֧���ص�
    Route::any("/alipay/notify", "PaymentController@alipayNotify")->name("alipay.notify");
    Route::any("/wechat/notify", "PaymentController@wechatNotify")->name("wechat.notify");
    
    // ֧��״̬��ѯ
    Route::get("/query/{order}", "PaymentController@queryStatus")->name("query")->middleware("auth");
    
    // ΢�Ŷ�ά������
    Route::get("/wechat/qrcode", "PaymentController@qrcode")->name("wechat.qrcode");
});

// �û����ײ͹���·��
Route::prefix("user/billing")->name("user.billing.")->middleware(["auth"])->group(function () {
    // ���ҳ��
    Route::get("/quota", "User\BillingController@quota")->name("quota");
    
    // �ײ͹���
    Route::get("/packages", "User\BillingController@packages")->name("packages");
    
    // ����
    Route::post("/checkout/{package}", "PaymentController@checkout")->name("checkout");
    
    // ֧��ҳ��
    Route::get("/pay", "User\BillingController@pay")->name("pay");
    
    // ֧���ɹ�
    Route::get("/success/{order}", "User\BillingController@success")->name("success");
    
    // �����б�
    Route::get("/orders", "User\BillingController@orders")->name("orders");
    
    // ��������
    Route::get("/order/{order}", "User\BillingController@orderDetail")->name("order");
    
    // ���ʹ��ͳ��
    Route::get("/stats", "User\BillingController@stats")->name("stats");
});

// ��Ա�������·��
Route::prefix("user/membership")->name("user.membership.")->middleware(["auth"])->group(function () {
    // ��Ա������ҳ
    Route::get("/", [MembershipController::class, "index"])->name("index");
    
    // ��Ա�ײ�ѡ��
    Route::get("/plans", [MembershipController::class, "plans"])->name("plans");
    
    // ��Ա����
    Route::post("/subscribe", [MembershipController::class, "subscribe"])->name("subscribe");
    
    // ��Ա����
    Route::get("/renew", [MembershipController::class, "renew"])->name("renew");
    
    // �����Զ�����
    Route::post("/auto-renew/enable", [MembershipController::class, "enableAutoRenew"])->name("auto-renew.enable");
    
    // ȡ���Զ�����
    Route::post("/auto-renew/cancel", [MembershipController::class, "cancelAutoRenew"])->name("auto-renew.cancel");
    
    // ֧��ҳ��
    Route::get("/pay", [MembershipController::class, "pay"])->name("pay");
});

// ���ʹ��ͳ��·��
Route::prefix("user/quota")->name("user.quota.")->middleware(["auth"])->group(function () {
    // ���ʹ�����
    Route::get("/", [QuotaController::class, "index"])->name("index");
    
    // ���ʹ��ͳ������
    Route::get("/stats", [QuotaController::class, "stats"])->name("stats");
    
    // ���ʹ����������
    Route::get("/trend", [QuotaController::class, "trend"])->name("trend");
});

// ��̨�ײ͹���·��
Route::prefix("admin/billing")->name("admin.billing.")->middleware(["auth", "role:admin"])->group(function () {
    // �ײ͹���
    Route::resource("packages", "Admin\Billing\PackageController");
    
    // ��������
    Route::resource("orders", "Admin\Billing\OrderController")->except(["create", "store"]);
});

// ��̨��Ա����·��
Route::prefix("admin/membership")->name("admin.membership.")->middleware(["auth", "role:admin"])->group(function () {
    // ��Ա�ȼ�����
    Route::resource("levels", "Admin\Membership\MembershipLevelController");
    
    // ��Ա���Ĺ���
    Route::resource("subscriptions", "Admin\Membership\MembershipSubscriptionController")->except(["create", "store"]);
    
    // ��Աͳ��
    Route::get("/stats", "Admin\Membership\MembershipStatsController@index")->name("stats");
});
