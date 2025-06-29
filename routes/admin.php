// 支付接口管理路由
Route::prefix('payment')->name('payment.')->group(function () {
    // 支付网关管理
    Route::prefix('gateways')->name('gateways.')->group(function () {
        Route::get('/', 'Payment\PaymentGatewayController@index')->name('index');
        Route::get('/create', 'Payment\PaymentGatewayController@create')->name('create');
        Route::post('/', 'Payment\PaymentGatewayController@store')->name('store');
        Route::get('/{id}', 'Payment\PaymentGatewayController@show')->name('show');
        Route::get('/{id}/edit', 'Payment\PaymentGatewayController@edit')->name('edit');
        Route::put('/{id}', 'Payment\PaymentGatewayController@update')->name('update');
        Route::delete('/{id}', 'Payment\PaymentGatewayController@destroy')->name('destroy');
        Route::post('/{id}/toggle', 'Payment\PaymentGatewayController@toggle')->name('toggle');
        Route::post('/{id}/test-mode', 'Payment\PaymentGatewayController@toggleTestMode')->name('test-mode');
        Route::post('/{id}/test', 'Payment\PaymentGatewayController@test')->name('test');
    });
    
    // 交易管理
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', 'Payment\TransactionController@index')->name('index');
        Route::get('/{id}', 'Payment\TransactionController@show')->name('show');
        Route::post('/{id}/status', 'Payment\TransactionController@updateStatus')->name('update-status');
        Route::post('/{id}/refund', 'Payment\TransactionController@createRefund')->name('refund');
        Route::post('/{id}/refund/{refundId}/status', 'Payment\TransactionController@updateRefundStatus')->name('refund-status');
        Route::get('/export', 'Payment\TransactionController@export')->name('export');
    });
    
    // 支付设置
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', 'Payment\PaymentSettingController@index')->name('index');
        Route::put('/', 'Payment\PaymentSettingController@update')->name('update');
        Route::get('/create', 'Payment\PaymentSettingController@create')->name('create');
        Route::post('/', 'Payment\PaymentSettingController@store')->name('store');
        Route::delete('/{id}', 'Payment\PaymentSettingController@destroy')->name('destroy');
    });
});

// AI和智能体接口管理路由
Route::prefix('ai')->name('ai.')->group(function () {
    // 模型提供商管理
    Route::prefix('providers')->name('providers.')->group(function () {
        Route::get('/', 'AI\AIModelProviderController@index')->name('index');
        Route::get('/create', 'AI\AIModelProviderController@create')->name('create');
        Route::post('/', 'AI\AIModelProviderController@store')->name('store');
        Route::get('/{id}', 'AI\AIModelProviderController@show')->name('show');
        Route::get('/{id}/edit', 'AI\AIModelProviderController@edit')->name('edit');
        Route::put('/{id}', 'AI\AIModelProviderController@update')->name('update');
        Route::delete('/{id}', 'AI\AIModelProviderController@destroy')->name('destroy');
        Route::post('/{id}/toggle', 'AI\AIModelProviderController@toggle')->name('toggle');
        Route::post('/{id}/test', 'AI\AIModelProviderController@testConnection')->name('test');
    });
    
    // 智能体管理
    Route::prefix('agents')->name('agents.')->group(function () {
        Route::get('/', 'AI\AIAgentController@index')->name('index');
        Route::get('/create', 'AI\AIAgentController@create')->name('create');
        Route::post('/', 'AI\AIAgentController@store')->name('store');
        Route::get('/{id}', 'AI\AIAgentController@show')->name('show');
        Route::get('/{id}/edit', 'AI\AIAgentController@edit')->name('edit');
        Route::put('/{id}', 'AI\AIAgentController@update')->name('update');
        Route::delete('/{id}', 'AI\AIAgentController@destroy')->name('destroy');
        Route::post('/{id}/toggle', 'AI\AIAgentController@toggle')->name('toggle');
        Route::post('/{id}/test', 'AI\AIAgentController@testConnection')->name('test');
        Route::post('/chat', 'AI\AIAgentController@chat')->name('chat');
        Route::post('/execute-task', 'AI\AIAgentController@executeTask')->name('execute-task');
    });
    
    // API密钥管理
    Route::prefix('api-keys')->name('api-keys.')->group(function () {
        Route::get('/', 'AI\AIApiKeyController@index')->name('index');
        Route::get('/create', 'AI\AIApiKeyController@create')->name('create');
        Route::post('/', 'AI\AIApiKeyController@store')->name('store');
        Route::get('/{id}', 'AI\AIApiKeyController@show')->name('show');
        Route::get('/{id}/edit', 'AI\AIApiKeyController@edit')->name('edit');
        Route::put('/{id}', 'AI\AIApiKeyController@update')->name('update');
        Route::delete('/{id}', 'AI\AIApiKeyController@destroy')->name('destroy');
        Route::post('/{id}/toggle', 'AI\AIApiKeyController@toggle')->name('toggle');
        Route::post('/{id}/reset-quota', 'AI\AIApiKeyController@resetQuota')->name('reset-quota');
    });
    
    // AI接口设置
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', 'AI\AISettingController@index')->name('index');
        Route::put('/', 'AI\AISettingController@update')->name('update');
        Route::get('/create', 'AI\AISettingController@create')->name('create');
        Route::post('/', 'AI\AISettingController@store')->name('store');
        Route::delete('/{id}', 'AI\AISettingController@destroy')->name('destroy');
        Route::get('/usage-stats', 'AI\AISettingController@usageStats')->name('usage-stats');
        Route::post('/clear-cache', 'AI\AISettingController@clearCache')->name('clear-cache');
    });
});

// 通知管理路由
Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', 'NotificationController@index')->name('index');
    Route::get('/create', 'NotificationController@create')->name('create');
    Route::post('/', 'NotificationController@store')->name('store');
    Route::get('/{id}', 'NotificationController@show')->name('show');
    Route::get('/{id}/edit', 'NotificationController@edit')->name('edit');
    Route::put('/{id}', 'NotificationController@update')->name('update');
    Route::delete('/{id}', 'NotificationController@destroy')->name('destroy');
    Route::get('/{id}/send', 'NotificationController@send')->name('send');
    Route::post('/preview', 'NotificationController@preview')->name('preview');
    Route::delete('/bulk-delete', 'NotificationController@bulkDelete')->name('bulk-delete');
    
    // 通知统计
    Route::get('/{id}/statistics', 'NotificationStatisticsController@show')->name('statistics');
    Route::get('/recipient-detail', 'NotificationStatisticsController@recipientDetail')->name('recipient-detail');
    Route::get('/{id}/export', 'NotificationStatisticsController@export')->name('export');
});

// 通知模板路由
Route::prefix('notification/templates')->name('notification.templates.')->group(function () {
    Route::get('/', 'NotificationTemplateController@index')->name('index');
    Route::get('/create', 'NotificationTemplateController@create')->name('create');
    Route::post('/', 'NotificationTemplateController@store')->name('store');
    Route::get('/{id}', 'NotificationTemplateController@show')->name('show');
    Route::get('/{id}/edit', 'NotificationTemplateController@edit')->name('edit');
    Route::put('/{id}', 'NotificationTemplateController@update')->name('update');
    Route::delete('/{id}', 'NotificationTemplateController@destroy')->name('destroy');
    Route::delete('/bulk-delete', 'NotificationTemplateController@bulkDelete')->name('bulk-delete');
});

// 通知规则路由
Route::prefix('notification/rules')->name('notification.rules.')->group(function () {
    Route::get('/', 'NotificationRuleController@index')->name('index');
    Route::get('/create', 'NotificationRuleController@create')->name('create');
    Route::post('/', 'NotificationRuleController@store')->name('store');
    Route::get('/{id}', 'NotificationRuleController@show')->name('show');
    Route::get('/{id}/edit', 'NotificationRuleController@edit')->name('edit');
    Route::put('/{id}', 'NotificationRuleController@update')->name('update');
    Route::delete('/{id}', 'NotificationRuleController@destroy')->name('destroy');
    Route::post('/toggle', 'NotificationRuleController@toggle')->name('toggle');
    Route::post('/bulk-toggle', 'NotificationRuleController@bulkToggle')->name('bulk-toggle');
    Route::delete('/bulk-delete', 'NotificationRuleController@bulkDelete')->name('bulk-delete');
});

// 邮件接口路由
Route::prefix('notification/email-providers')->name('notification.email-providers.')->group(function () {
    Route::get('/', 'EmailProviderController@index')->name('index');
    Route::get('/create', 'EmailProviderController@create')->name('create');
    Route::post('/', 'EmailProviderController@store')->name('store');
    Route::get('/{id}', 'EmailProviderController@show')->name('show');
    Route::get('/{id}/edit', 'EmailProviderController@edit')->name('edit');
    Route::put('/{id}', 'EmailProviderController@update')->name('update');
    Route::delete('/{id}', 'EmailProviderController@destroy')->name('destroy');
    Route::post('/{id}/test', 'EmailProviderController@test')->name('test');
    Route::post('/set-default', 'EmailProviderController@setDefault')->name('set-default');
});

// 批量邮件发送路由
Route::prefix('notification/bulk-email')->name('notification.bulk-email.')->group(function () {
    Route::get('/', 'BulkEmailController@index')->name('index');
    Route::get('/create', 'BulkEmailController@create')->name('create');
    Route::post('/', 'BulkEmailController@store')->name('store');
    Route::get('/{id}', 'BulkEmailController@show')->name('show');
    Route::post('/import', 'BulkEmailController@import')->name('import');
    Route::get('/{id}/status', 'BulkEmailController@status')->name('status');
    Route::post('/{id}/cancel', 'BulkEmailController@cancel')->name('cancel');
});

// 通知管理路由
Route::prefix('notification')->name('notification.')->group(function () {
    // 通知管理
    Route::get('/', 'Notification\NotificationController@index')->name('index');
    Route::get('/create', 'Notification\NotificationController@create')->name('create');
    Route::post('/', 'Notification\NotificationController@store')->name('store');
    Route::get('/{notification}', 'Notification\NotificationController@show')->name('show');
    Route::get('/{notification}/edit', 'Notification\NotificationController@edit')->name('edit');
    Route::put('/{notification}', 'Notification\NotificationController@update')->name('update');
    Route::delete('/{notification}', 'Notification\NotificationController@destroy')->name('destroy');
    Route::post('/{notification}/send', 'Notification\NotificationController@send')->name('send');
    Route::post('/{notification}/duplicate', 'Notification\NotificationController@duplicate')->name('duplicate');
    Route::get('/get-users', 'Notification\NotificationController@getUsers')->name('get-users');

    // 通知模板管理
    Route::prefix('template')->name('template.')->group(function () {
        Route::get('/', 'Notification\NotificationTemplateController@index')->name('index');
        Route::get('/create', 'Notification\NotificationTemplateController@create')->name('create');
        Route::post('/', 'Notification\NotificationTemplateController@store')->name('store');
        Route::get('/{template}', 'Notification\NotificationTemplateController@show')->name('show');
        Route::get('/{template}/edit', 'Notification\NotificationTemplateController@edit')->name('edit');
        Route::put('/{template}', 'Notification\NotificationTemplateController@update')->name('update');
        Route::delete('/{template}', 'Notification\NotificationTemplateController@destroy')->name('destroy');
        Route::post('/{template}/duplicate', 'Notification\NotificationTemplateController@duplicate')->name('duplicate');
        Route::post('/{template}/preview', 'Notification\NotificationTemplateController@preview')->name('preview');
        Route::post('/{template}/test', 'Notification\NotificationTemplateController@test')->name('test');
    });

    // 邮件发送接口管理
    Route::prefix('email-provider')->name('email_provider.')->group(function () {
        Route::get('/', 'Notification\EmailProviderController@index')->name('index');
        Route::get('/create', 'Notification\EmailProviderController@create')->name('create');
        Route::post('/', 'Notification\EmailProviderController@store')->name('store');
        Route::get('/{provider}', 'Notification\EmailProviderController@show')->name('show');
        Route::get('/{provider}/edit', 'Notification\EmailProviderController@edit')->name('edit');
        Route::put('/{provider}', 'Notification\EmailProviderController@update')->name('update');
        Route::delete('/{provider}', 'Notification\EmailProviderController@destroy')->name('destroy');
        Route::post('/{provider}/set-default', 'Notification\EmailProviderController@setDefault')->name('set-default');
        Route::post('/{provider}/test', 'Notification\EmailProviderController@test')->name('test');
    });

    // 批量邮件发送
    Route::prefix('bulk-email')->name('bulk_email.')->group(function () {
        Route::get('/', 'Notification\BulkEmailController@index')->name('index');
        Route::post('/send', 'Notification\BulkEmailController@send')->name('send');
        Route::get('/history', 'Notification\BulkEmailController@history')->name('history');
        Route::post('/import-emails', 'Notification\BulkEmailController@importEmails')->name('import-emails');
        Route::get('/get-template-variables', 'Notification\BulkEmailController@getTemplateVariables')->name('get-template-variables');
    });
});

// 数据库超级运维与超级管理
Route::prefix('database')->name('database.')->group(function () {
    Route::get('/', 'DatabaseManagerController@index')->name('index');
    Route::get('/tables', 'DatabaseManagerController@tables')->name('tables');
    Route::get('/table/{table}', 'DatabaseManagerController@tableDetail')->name('table.detail');
    Route::post('/execute-query', 'DatabaseManagerController@executeQuery')->name('execute-query');
    Route::post('/optimize', 'DatabaseManagerController@optimize')->name('optimize');
    Route::get('/backup', 'DatabaseManagerController@backupIndex')->name('backup.index');
    Route::post('/backup/create', 'DatabaseManagerController@createBackup')->name('backup.create');
    Route::get('/backup/download/{filename}', 'DatabaseManagerController@downloadBackup')->name('backup.download');
    Route::delete('/backup/delete/{filename}', 'DatabaseManagerController@deleteBackup')->name('backup.delete');
    Route::post('/backup/restore/{filename}', 'DatabaseManagerController@restoreBackup')->name('backup.restore');
    Route::get('/monitor', 'DatabaseManagerController@monitor')->name('monitor');
    Route::get('/slow-queries', 'DatabaseManagerController@slowQueries')->name('slow-queries');
    Route::get('/structure', 'DatabaseManagerController@structure')->name('structure');
});

// API管理路由
Route::prefix('security/api')->name('security.api.')->group(function () {
    // API密钥管理
    Route::get('keys', 'Security\ApiKeyController@index')->name('keys.index');
    Route::get('keys/create', 'Security\ApiKeyController@create')->name('keys.create');
    Route::post('keys', 'Security\ApiKeyController@store')->name('keys.store');
    Route::get('keys/{id}', 'Security\ApiKeyController@show')->name('keys.show');
    Route::get('keys/{id}/edit', 'Security\ApiKeyController@edit')->name('keys.edit');
    Route::put('keys/{id}', 'Security\ApiKeyController@update')->name('keys.update');
    Route::delete('keys/{id}', 'Security\ApiKeyController@destroy')->name('keys.destroy');
    Route::put('keys/{id}/reset', 'Security\ApiKeyController@reset')->name('keys.reset');
    
    // API请求日志
    Route::get('request-logs', 'Security\ApiRequestLogController@index')->name('request-logs.index');
    Route::get('request-logs/{id}', 'Security\ApiRequestLogController@show')->name('request-logs.show');
    Route::get('request-logs/export', 'Security\ApiRequestLogController@export')->name('request-logs.export');
    Route::get('request-logs/statistics', 'Security\ApiRequestLogController@statistics')->name('request-logs.statistics');
    
    // SDK管理
    Route::get('sdks', 'Security\ApiSdkController@index')->name('sdks.index');
    Route::get('sdks/create', 'Security\ApiSdkController@create')->name('sdks.create');
    Route::post('sdks', 'Security\ApiSdkController@store')->name('sdks.store');
    Route::get('sdks/{id}', 'Security\ApiSdkController@show')->name('sdks.show');
    Route::get('sdks/{id}/edit', 'Security\ApiSdkController@edit')->name('sdks.edit');
    Route::put('sdks/{id}', 'Security\ApiSdkController@update')->name('sdks.update');
    Route::delete('sdks/{id}', 'Security\ApiSdkController@destroy')->name('sdks.destroy');
    Route::post('sdks/{id}/generate', 'Security\ApiSdkController@generate')->name('sdks.generate');
    Route::get('sdks/{id}/download/{version_id}', 'Security\ApiSdkController@download')->name('sdks.download');
    Route::put('sdks/{id}/set-current/{version_id}', 'Security\ApiSdkController@setCurrent')->name('sdks.set-current');
    Route::delete('sdks/{id}/delete-version/{version_id}', 'Security\ApiSdkController@deleteVersion')->name('sdks.delete-version');
    Route::get('sdks/{id}/interfaces', 'Security\ApiSdkController@getInterfaces')->name('sdks.interfaces');
    Route::get('sdks/{id}/documentation', 'Security\ApiSdkController@documentation')->name('sdks.documentation');
    
    // API文档
    Route::get('documentation', 'Security\ApiDocumentationController@index')->name('documentation.index');
    Route::get('documentation/{id}', 'Security\ApiDocumentationController@show')->name('documentation.show');
    Route::post('documentation/generate', 'Security\ApiDocumentationController@generate')->name('documentation.generate');
    Route::get('documentation/test-tool', 'Security\ApiDocumentationController@testTool')->name('documentation.test-tool');
    Route::post('documentation/execute-test', 'Security\ApiDocumentationController@executeTest')->name('documentation.execute-test');
    
    // API接口管理
    Route::get('interfaces', 'Security\ApiInterfaceController@index')->name('interfaces.index');
    Route::get('interfaces/create', 'Security\ApiInterfaceController@create')->name('interfaces.create');
    Route::post('interfaces', 'Security\ApiInterfaceController@store')->name('interfaces.store');
    Route::get('interfaces/{id}', 'Security\ApiInterfaceController@show')->name('interfaces.show');
    Route::get('interfaces/{id}/edit', 'Security\ApiInterfaceController@edit')->name('interfaces.edit');
    Route::put('interfaces/{id}', 'Security\ApiInterfaceController@update')->name('interfaces.update');
    Route::delete('interfaces/{id}', 'Security\ApiInterfaceController@destroy')->name('interfaces.destroy');
    Route::get('interfaces/{id}/parameters', 'Security\ApiInterfaceController@getParameters')->name('interfaces.parameters');
});

// 额度套餐、商品和会员相关的后台路由
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // 管理员首页
    Route::get('/', [AdminController::class, 'index'])->name('index');
    
    // API密钥管理
    Route::prefix('api-keys')->name('api-keys.')->group(function () {
        Route::get('/', [ApiKeyController::class, 'index'])->name('index');
        Route::get('/create', [ApiKeyController::class, 'create'])->name('create');
        Route::post('/', [ApiKeyController::class, 'store'])->name('store');
        Route::get('/{apiKey}', [ApiKeyController::class, 'show'])->name('show');
        Route::get('/{apiKey}/edit', [ApiKeyController::class, 'edit'])->name('edit');
        Route::put('/{apiKey}', [ApiKeyController::class, 'update'])->name('update');
        Route::delete('/{apiKey}', [ApiKeyController::class, 'destroy'])->name('destroy');
        Route::post('/{apiKey}/regenerate', [ApiKeyController::class, 'regenerate'])->name('regenerate');
        Route::post('/{apiKey}/toggle', [ApiKeyController::class, 'toggle'])->name('toggle');
    });

    // API请求日志
    Route::prefix('api-logs')->name('api-logs.')->group(function () {
        Route::get('/', [ApiRequestLogController::class, 'index'])->name('index');
        Route::get('/{log}', [ApiRequestLogController::class, 'show'])->name('show');
        Route::delete('/{log}', [ApiRequestLogController::class, 'destroy'])->name('destroy');
        Route::get('/statistics', [ApiRequestLogController::class, 'statistics'])->name('statistics');
    });

    // 额度套餐管理
    Route::prefix('billing/packages')->name('billing.packages.')->group(function () {
        Route::get('/', [PackageController::class, 'index'])->name('index');
        Route::get('/create', [PackageController::class, 'create'])->name('create');
        Route::post('/', [PackageController::class, 'store'])->name('store');
        Route::get('/{package}', [PackageController::class, 'show'])->name('show');
        Route::get('/{package}/edit', [PackageController::class, 'edit'])->name('edit');
        Route::put('/{package}', [PackageController::class, 'update'])->name('update');
        Route::delete('/{package}', [PackageController::class, 'destroy'])->name('destroy');
    });

    // 商品管理
    Route::prefix('billing/products')->name('billing.products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });

    // 订单管理
    Route::prefix('billing/orders')->name('billing.orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::post('/{order}/update-status', [OrderController::class, 'updateStatus'])->name('update-status');
        Route::get('/statistics', [OrderController::class, 'statistics'])->name('statistics');
    });

    // 用户套餐管理
    Route::prefix('billing/user-packages')->name('billing.user-packages.')->group(function () {
        Route::get('/', [UserPackageController::class, 'index'])->name('index');
        Route::get('/create', [UserPackageController::class, 'create'])->name('create');
        Route::post('/', [UserPackageController::class, 'store'])->name('store');
        Route::get('/{userPackage}', [UserPackageController::class, 'show'])->name('show');
        Route::get('/{userPackage}/edit', [UserPackageController::class, 'edit'])->name('edit');
        Route::put('/{userPackage}', [UserPackageController::class, 'update'])->name('update');
        Route::delete('/{userPackage}', [UserPackageController::class, 'destroy'])->name('destroy');
        Route::post('/{userPackage}/adjust-quota', [UserPackageController::class, 'adjustQuota'])->name('adjust-quota');
    });

    // 会员等级管理
    Route::prefix('membership/levels')->name('membership.levels.')->group(function () {
        Route::get('/', [MembershipLevelController::class, 'index'])->name('index');
        Route::get('/create', [MembershipLevelController::class, 'create'])->name('create');
        Route::post('/', [MembershipLevelController::class, 'store'])->name('store');
        Route::get('/{membershipLevel}', [MembershipLevelController::class, 'show'])->name('show');
        Route::get('/{membershipLevel}/edit', [MembershipLevelController::class, 'edit'])->name('edit');
        Route::put('/{membershipLevel}', [MembershipLevelController::class, 'update'])->name('update');
        Route::delete('/{membershipLevel}', [MembershipLevelController::class, 'destroy'])->name('destroy');
    });

    // 会员订阅管理
    Route::prefix('membership/subscriptions')->name('membership.subscriptions.')->group(function () {
        Route::get('/', [MembershipSubscriptionController::class, 'index'])->name('index');
        Route::get('/create', [MembershipSubscriptionController::class, 'create'])->name('create');
        Route::post('/', [MembershipSubscriptionController::class, 'store'])->name('store');
        Route::get('/{subscription}', [MembershipSubscriptionController::class, 'show'])->name('show');
        Route::get('/{subscription}/edit', [MembershipSubscriptionController::class, 'edit'])->name('edit');
        Route::put('/{subscription}', [MembershipSubscriptionController::class, 'update'])->name('update');
        Route::delete('/{subscription}', [MembershipSubscriptionController::class, 'destroy'])->name('destroy');
        Route::post('/{subscription}/cancel', [MembershipSubscriptionController::class, 'cancel'])->name('cancel');
    });
});

