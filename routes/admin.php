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