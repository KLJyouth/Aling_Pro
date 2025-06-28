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