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


 / /   A I >f:y7h_�{t
 R o u t e : : p r e f i x ( " a i / s t y l e s " ) - > n a m e ( " a i . s t y l e s . " ) - > g r o u p ( f u n c t i o n   ( )   { 
         R o u t e : : g e t ( " / p r e d e f i n e d " ,   " A I \ D i s p l a y S t y l e C o n t r o l l e r @ g e t P r e d e f i n e d S t y l e s " ) - > n a m e ( " p r e d e f i n e d " ) ; 
         R o u t e : : p u t ( " / p r o v i d e r / { i d } " ,   " A I \ D i s p l a y S t y l e C o n t r o l l e r @ u p d a t e P r o v i d e r S t y l e " ) - > n a m e ( " u p d a t e - p r o v i d e r " ) ; 
         R o u t e : : p u t ( " / m o d e l / { i d } " ,   " A I \ D i s p l a y S t y l e C o n t r o l l e r @ u p d a t e M o d e l S t y l e " ) - > n a m e ( " u p d a t e - m o d e l " ) ; 
         R o u t e : : p u t ( " / a g e n t / { i d } " ,   " A I \ D i s p l a y S t y l e C o n t r o l l e r @ u p d a t e A g e n t S t y l e " ) - > n a m e ( " u p d a t e - a g e n t " ) ; 
         R o u t e : : d e l e t e ( " / { t y p e } / { i d } " ,   " A I \ D i s p l a y S t y l e C o n t r o l l e r @ d e l e t e S t y l e " ) - > n a m e ( " d e l e t e " ) ; 
 } ) ; 
 
 / /   A I �e�_�{t
 R o u t e : : p r e f i x ( " a i / l o g s " ) - > n a m e ( " a i . l o g s . " ) - > g r o u p ( f u n c t i o n   ( )   { 
         R o u t e : : g e t ( " / a p i " ,   " A I \ L o g C o n t r o l l e r @ a p i L o g s " ) - > n a m e ( " a p i " ) ; 
         R o u t e : : g e t ( " / a p i / { i d } " ,   " A I \ L o g C o n t r o l l e r @ s h o w A p i L o g " ) - > n a m e ( " a p i . s h o w " ) ; 
         R o u t e : : g e t ( " / a p i / e x p o r t " ,   " A I \ L o g C o n t r o l l e r @ e x p o r t A p i L o g s " ) - > n a m e ( " a p i . e x p o r t " ) ; 
         R o u t e : : g e t ( " / a u d i t " ,   " A I \ L o g C o n t r o l l e r @ a u d i t L o g s " ) - > n a m e ( " a u d i t " ) ; 
         R o u t e : : g e t ( " / a u d i t / { i d } " ,   " A I \ L o g C o n t r o l l e r @ s h o w A u d i t L o g " ) - > n a m e ( " a u d i t . s h o w " ) ; 
         R o u t e : : g e t ( " / a u d i t / e x p o r t " ,   " A I \ L o g C o n t r o l l e r @ e x p o r t A u d i t L o g s " ) - > n a m e ( " a u d i t . e x p o r t " ) ; 
 } ) ; 
 
 / /   A I ؚ�~��n
 R o u t e : : p r e f i x ( " a i / a d v a n c e d - s e t t i n g s " ) - > n a m e ( " a i . a d v a n c e d - s e t t i n g s . " ) - > g r o u p ( f u n c t i o n   ( )   { 
         R o u t e : : g e t ( " / " ,   " A I \ A d v a n c e d S e t t i n g C o n t r o l l e r @ i n d e x " ) - > n a m e ( " i n d e x " ) ; 
         R o u t e : : p u t ( " / " ,   " A I \ A d v a n c e d S e t t i n g C o n t r o l l e r @ u p d a t e " ) - > n a m e ( " u p d a t e " ) ; 
         R o u t e : : p o s t ( " / r e s e t " ,   " A I \ A d v a n c e d S e t t i n g C o n t r o l l e r @ r e s e t " ) - > n a m e ( " r e s e t " ) ; 
 } ) ;  
 
 / /   A I zf��SOKmՋ�]wQ
 R o u t e : : p r e f i x ( " a i / t e s t i n g " ) - > n a m e ( " a i . t e s t i n g . " ) - > g r o u p ( f u n c t i o n   ( )   { 
         R o u t e : : g e t ( " / " ,   " A I \ A g e n t T e s t i n g C o n t r o l l e r @ i n d e x " ) - > n a m e ( " i n d e x " ) ; 
         R o u t e : : p o s t ( " / t e s t " ,   " A I \ A g e n t T e s t i n g C o n t r o l l e r @ t e s t " ) - > n a m e ( " t e s t " ) ; 
         R o u t e : : g e t ( " / c o m p a r e " ,   " A I \ A g e n t T e s t i n g C o n t r o l l e r @ c o m p a r e " ) - > n a m e ( " c o m p a r e " ) ; 
         R o u t e : : p o s t ( " / c o m p a r e - a g e n t s " ,   " A I \ A g e n t T e s t i n g C o n t r o l l e r @ c o m p a r e A g e n t s " ) - > n a m e ( " c o m p a r e - a g e n t s " ) ; 
         R o u t e : : g e t ( " / d e b u g / { i d } " ,   " A I \ A g e n t T e s t i n g C o n t r o l l e r @ d e b u g " ) - > n a m e ( " d e b u g " ) ; 
 } ) ;  
 
 / /   O A u t h �c�OFU�{t
 R o u t e : : p r e f i x ( " o a u t h " ) - > n a m e ( " o a u t h . " ) - > g r o u p ( f u n c t i o n   ( )   { 
         R o u t e : : r e s o u r c e ( " p r o v i d e r s " ,   " O A u t h P r o v i d e r C o n t r o l l e r " ) - > e x c e p t ( [ " d e s t r o y " ] ) ; 
         R o u t e : : d e l e t e ( " p r o v i d e r s / { i d } " ,   " O A u t h P r o v i d e r C o n t r o l l e r @ d e s t r o y " ) - > n a m e ( " p r o v i d e r s . d e s t r o y " ) ; 
         R o u t e : : g e t ( " l o g s " ,   " O A u t h P r o v i d e r C o n t r o l l e r @ l o g s " ) - > n a m e ( " l o g s . i n d e x " ) ; 
         R o u t e : : g e t ( " l o g s / { i d } " ,   " O A u t h P r o v i d e r C o n t r o l l e r @ s h o w L o g " ) - > n a m e ( " l o g s . s h o w " ) ; 
         R o u t e : : g e t ( " u s e r - a c c o u n t s " ,   " O A u t h P r o v i d e r C o n t r o l l e r @ u s e r A c c o u n t s " ) - > n a m e ( " u s e r - a c c o u n t s . i n d e x " ) ; 
         R o u t e : : g e t ( " u s e r - a c c o u n t s / { i d } " ,   " O A u t h P r o v i d e r C o n t r o l l e r @ s h o w U s e r A c c o u n t " ) - > n a m e ( " u s e r - a c c o u n t s . s h o w " ) ; 
 } ) ;  
 