<?php

namespace App\Http\Controllers\Admin\Notification;

use App\Http\Controllers\Controller;
use App\Models\Notification\Notification;
use App\Models\Notification\NotificationTemplate;
use App\Services\Notification\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * 批量邮件发送控制器
 */
class BulkEmailController extends Controller
{
    /**
     * 邮件服务实例
     *
     * @var EmailService
     */
    protected ;

    /**
     * 构造函数
     *
     * @param EmailService 
     */
    public function __construct(EmailService )
    {
        ->emailService = ;
    }

    /**
     * 显示批量邮件发送表单
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
         = NotificationTemplate::where('type', 'email')
            ->where('status', 'active')
            ->get();

        return view('admin.notification.bulk_email.index', [
            'templates' => ,
            'priorities' => [
                'low' => '低',
                'normal' => '普通',
                'high' => '高',
                'urgent' => '紧急',
            ],
        ]);
    }

    /**
     * 发送批量邮件
     *
     * @param Request 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(Request )
    {
        // 验证请求数据
         = Validator::make(->all(), [
            'subject' => 'required_without:template_id|string|max:255',
            'content' => 'required_without:template_id|string',
            'template_id' => 'nullable|exists:notification_templates,id',
            'priority' => 'required|string|in:low,normal,high,urgent',
            'emails' => 'required|string',
            'scheduled_at' => 'nullable|date',
            'attachments.*' => 'nullable|file|max:10240', // 最大10MB
        ]);

        if (->fails()) {
            return redirect()->back()
                ->withErrors()
                ->withInput();
        }

        try {
            // 开始事务
            DB::beginTransaction();

            // 处理邮箱列表
             = ->emails;
             = ->parseEmails();

            if (empty()) {
                return redirect()->back()
                    ->with('error', '没有有效的邮箱地址')
                    ->withInput();
            }

            // 准备附件数据
             = [];
            if (->hasFile('attachments')) {
                foreach (->file('attachments') as ) {
                     = ->store('attachments/bulk_emails', 'public');
                    [] = [
                        'file_name' => ->getClientOriginalName(),
                        'file_path' => 'storage/' . ,
                        'file_size' => ->getSize(),
                        'file_type' => ->getMimeType(),
                    ];
                }
            }

            // 准备选项
             = [
                'priority' => ->priority,
                'scheduled_at' => ->scheduled_at,
                'attachments' => ,
                'metadata' => [
                    'bulk_email' => true,
                    'total_recipients' => count(),
                ],
            ];

            // 如果使用模板
            if (->filled('template_id')) {
                 = NotificationTemplate::findOrFail(->template_id);
                
                // 准备模板数据
                 = ->input('template_data', []);
                
                // 创建接收者数据
                 = [];
                foreach ( as ) {
                    [] = [
                        'email' => ,
                    ];
                }
                
                // 使用通知服务创建通知
                 = app(\App\Services\Notification\NotificationService::class);
                 = ->createNotificationFromTemplate(
                    ->code,
                    ,
                    ,
                    
                );
                
                // 如果选择了立即发送
                if (!->filled('scheduled_at')) {
                    ->sendNotification();
                }
            } else {
                // 使用自定义内容
                 = ->subject;
                <?php

/**
 * AlingAi Pro 6.0 API Routes
 * 闆朵俊浠婚噺瀛愬姞瀵嗙郴缁?API 璺敱閰嶇疆
 */

use AlingAi\Config\Routes;
use AlingAi\Controllers\AuthController;
use AlingAi\Controllers\UserController;
use AlingAi\Controllers\SystemController;
use AlingAi\Controllers\SystemManagementController;
use AlingAi\Controllers\MonitoringController;
use AlingAi\Controllers\WalletController;
use AlingAi\Controllers\EnterpriseAdminController;
use AlingAi\Controllers\PaymentController;
use AlingAi\Controllers\DocumentController;
use AlingAi\Controllers\SimpleApiController;
use App\Http\Controllers\Security\QuantumCryptoController;
use App\Http\Controllers\Security\SecurityTestController;
use App\Http\Controllers\Security\VulnerabilityScanController;
use App\Http\Controllers\Security\IntrusionDetectionController;
use App\Http\Controllers\Security\CryptoKeyController;
use App\Http\Controllers\Security\SecurityIssueController;
use App\Http\Controllers\Security\SecurityTestResultController;
use App\Http\Controllers\Security\SecurityThreatController;
use App\Http\Controllers\Monitoring\SystemMonitorController;
use App\Http\Controllers\Monitoring\AlertController;
use App\Http\Controllers\Security\QuantumSecurityController;
use App\Http\Controllers\Security\QuantumSecurityDashboardController;
use App\Http\Controllers\Security\QuarantineController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * API璺敱瀹氫箟
 * 鍩轰簬鑷畾涔夎矾鐢辩郴缁熺殑API绔偣閰嶇疆
 */
class ApiRoutes {
    
    /**
     * 鑾峰彇鎵€鏈堿PI璺敱
     */
    public static function getRoutes() {
        return array_merge(
            self::getAuthRoutes(),
            self::getUserRoutes(),
            self::getSystemRoutes(),
            self::getEnterpriseRoutes(),
            self::getSecurityRoutes(),
            self::getTicketRoutes(),
            self::getSettingRoutes(),
            self::getNewsRoutes(),
            self::getBlockchainRoutes(),
            self::getAdminRoutes(),
            self::getMonitoringRoutes(),
            self::getPublicRoutes()
        );
    }
    
    /**
     * 璁よ瘉鐩稿叧璺敱
     */
    private static function getAuthRoutes() {
        return [
            'POST /api/auth/login' => ['controller' => 'AuthController', 'method' => 'login'],
            'POST /api/auth/register' => ['controller' => 'AuthController', 'method' => 'register'],
            'POST /api/auth/logout' => ['controller' => 'AuthController', 'method' => 'logout'],
            'POST /api/auth/refresh' => ['controller' => 'AuthController', 'method' => 'refresh'],
            'POST /api/auth/forgot-password' => ['controller' => 'AuthController', 'method' => 'forgotPassword'],
            'POST /api/auth/reset-password' => ['controller' => 'AuthController', 'method' => 'resetPassword'],
            'POST /api/auth/verify-email' => ['controller' => 'AuthController', 'method' => 'verifyEmail'],
            'POST /api/auth/resend-verification' => ['controller' => 'AuthController', 'method' => 'resendVerification'],
            'GET /api/auth/user' => ['controller' => 'AuthController', 'method' => 'getUser', 'middleware' => ['auth']],
        ];
    }
    
    /**
     * 鐢ㄦ埛鐩稿叧璺敱
     */
    private static function getUserRoutes() {
        return [
            'GET /api/user/profile' => ['controller' => 'UserController', 'method' => 'profile', 'middleware' => ['auth']],
            'PUT /api/user/profile' => ['controller' => 'UserController', 'method' => 'updateProfile', 'middleware' => ['auth']],
            'POST /api/user/avatar' => ['controller' => 'UserController', 'method' => 'uploadAvatar', 'middleware' => ['auth']],
            'GET /api/user/settings' => ['controller' => 'UserController', 'method' => 'getSettings', 'middleware' => ['auth']],
            'PUT /api/user/settings' => ['controller' => 'UserController', 'method' => 'updateSettings', 'middleware' => ['auth']],
            'POST /api/user/change-password' => ['controller' => 'UserController', 'method' => 'changePassword', 'middleware' => ['auth']],
            'GET /api/user/activity' => ['controller' => 'UserController', 'method' => 'getActivity', 'middleware' => ['auth']],
            'GET /api/user/notifications' => ['controller' => 'UserController', 'method' => 'getNotifications', 'middleware' => ['auth']],
            'PUT /api/user/notifications/{id}/read' => ['controller' => 'UserController', 'method' => 'markNotificationAsRead', 'middleware' => ['auth']],
        ];
    }
    
    /**
     * 绯荤粺鐩稿叧璺敱
     */
    private static function getSystemRoutes() {
        return [
            'GET /api/version' => ['controller' => 'SystemController', 'method' => 'getVersion'],
            'GET /api/health' => ['controller' => 'SystemController', 'method' => 'healthCheck'],
            'GET /api/health/detailed' => ['controller' => 'SystemController', 'method' => 'detailedHealthCheck'],
            'GET /api/status' => ['controller' => 'SystemController', 'method' => 'getStatus'],
            'GET /api/metrics' => ['controller' => 'SystemController', 'method' => 'getMetrics'],
            'GET /api/system/info' => ['controller' => 'SystemController', 'method' => 'getSystemInfo', 'middleware' => ['auth', 'admin']],
            'GET /api/system/config' => ['controller' => 'SystemController', 'method' => 'getConfig', 'middleware' => ['auth', 'admin']],
            'PUT /api/system/config' => ['controller' => 'SystemController', 'method' => 'updateConfig', 'middleware' => ['auth', 'admin']],
        ];
    }
    
    /**
     * 浼佷笟鏈嶅姟璺敱
     */
    private static function getEnterpriseRoutes() {
        return [
            'GET /api/enterprise/dashboard' => ['controller' => 'EnterpriseAdminController', 'method' => 'dashboard', 'middleware' => ['auth']],
            'GET /api/enterprise/organizations' => ['controller' => 'EnterpriseAdminController', 'method' => 'getOrganizations', 'middleware' => ['auth']],
            'POST /api/enterprise/organizations' => ['controller' => 'EnterpriseAdminController', 'method' => 'createOrganization', 'middleware' => ['auth']],
            'GET /api/enterprise/workspaces' => ['controller' => 'EnterpriseAdminController', 'method' => 'getWorkspaces', 'middleware' => ['auth']],
            'POST /api/enterprise/workspaces' => ['controller' => 'EnterpriseAdminController', 'method' => 'createWorkspace', 'middleware' => ['auth']],
            'GET /api/enterprise/projects' => ['controller' => 'EnterpriseAdminController', 'method' => 'getProjects', 'middleware' => ['auth']],
            'POST /api/enterprise/projects' => ['controller' => 'EnterpriseAdminController', 'method' => 'createProject', 'middleware' => ['auth']],
            'GET /api/enterprise/teams' => ['controller' => 'EnterpriseAdminController', 'method' => 'getTeams', 'middleware' => ['auth']],
            'POST /api/enterprise/teams' => ['controller' => 'EnterpriseAdminController', 'method' => 'createTeam', 'middleware' => ['auth']],
        ];
    }
    
    /**
     * 瀹夊叏鍜屽姞瀵嗚矾鐢?
     */
    private static function getSecurityRoutes() {
        return [
            // 閲忓瓙鍔犲瘑鐩稿叧璺敱
            'POST /api/security/encrypt' => ['controller' => QuantumCryptoController::class, 'method' => 'encrypt', 'middleware' => ['auth', 'quantum-security']],
            'POST /api/security/decrypt' => ['controller' => QuantumCryptoController::class, 'method' => 'decrypt', 'middleware' => ['auth', 'quantum-security']],
            'POST /api/security/sign' => ['controller' => QuantumCryptoController::class, 'method' => 'sign', 'middleware' => ['auth', 'quantum-security']],
            'POST /api/security/verify' => ['controller' => QuantumCryptoController::class, 'method' => 'verify', 'middleware' => ['auth', 'quantum-security']],
            
            // 瀵嗛挜绠＄悊璺敱
            'GET /api/security/keys' => ['controller' => CryptoKeyController::class, 'method' => 'index', 'middleware' => ['auth', 'admin']],
            'POST /api/security/keys' => ['controller' => CryptoKeyController::class, 'method' => 'store', 'middleware' => ['auth', 'admin']],
            'GET /api/security/keys/{id}' => ['controller' => CryptoKeyController::class, 'method' => 'show', 'middleware' => ['auth', 'admin']],
            'PUT /api/security/keys/{id}' => ['controller' => CryptoKeyController::class, 'method' => 'update', 'middleware' => ['auth', 'admin']],
            'DELETE /api/security/keys/{id}' => ['controller' => CryptoKeyController::class, 'method' => 'destroy', 'middleware' => ['auth', 'admin']],
            
            // 瀹夊叏娴嬭瘯鐩稿叧璺敱
            'POST /api/security/tests/run' => ['controller' => SecurityTestController::class, 'method' => 'runTest', 'middleware' => ['auth', 'admin']],
            'GET /api/security/tests/{testId}/status' => ['controller' => SecurityTestController::class, 'method' => 'getTestStatus', 'middleware' => ['auth', 'admin']],
            'GET /api/security/tests/history' => ['controller' => SecurityTestController::class, 'method' => 'getTestHistory', 'middleware' => ['auth', 'admin']],
            'GET /api/security/tests/{testId}/report' => ['controller' => SecurityTestController::class, 'method' => 'getTestReport', 'middleware' => ['auth', 'admin']],
            'POST /api/security/tests/{testId}/cancel' => ['controller' => SecurityTestController::class, 'method' => 'cancelTest', 'middleware' => ['auth', 'admin']],
            
            // 婕忔礊鎵弿鐩稿叧璺敱
            'POST /api/security/vulnerabilities/scan' => ['controller' => VulnerabilityScanController::class, 'method' => 'startScan', 'middleware' => ['auth', 'admin']],
            'GET /api/security/vulnerabilities/scan/{scanId}/status' => ['controller' => VulnerabilityScanController::class, 'method' => 'getScanStatus', 'middleware' => ['auth', 'admin']],
            'GET /api/security/vulnerabilities/scan/{scanId}/result' => ['controller' => VulnerabilityScanController::class, 'method' => 'getScanResult', 'middleware' => ['auth', 'admin']],
            'GET /api/security/vulnerabilities/history' => ['controller' => VulnerabilityScanController::class, 'method' => 'getScanHistory', 'middleware' => ['auth', 'admin']],
            'POST /api/security/vulnerabilities/scan/{scanId}/cancel' => ['controller' => VulnerabilityScanController::class, 'method' => 'cancelScan', 'middleware' => ['auth', 'admin']],
            
            // 鍏ヤ镜妫€娴嬬浉鍏宠矾鐢?
            'GET /api/security/intrusion/attempts' => ['controller' => IntrusionDetectionController::class, 'method' => 'getIntrusionAttempts', 'middleware' => ['auth', 'admin']],
            'GET /api/security/intrusion/{attemptId}/detail' => ['controller' => IntrusionDetectionController::class, 'method' => 'getIntrusionDetail', 'middleware' => ['auth', 'admin']],
            'POST /api/security/intrusion/{attemptId}/resolve' => ['controller' => IntrusionDetectionController::class, 'method' => 'resolveIntrusion', 'middleware' => ['auth', 'admin']],
            
            // 瀹夊叏闂绠＄悊璺敱
            'GET /api/security/issues' => ['controller' => SecurityIssueController::class, 'method' => 'index', 'middleware' => ['auth', 'admin']],
            'POST /api/security/issues' => ['controller' => SecurityIssueController::class, 'method' => 'store', 'middleware' => ['auth', 'admin']],
            'GET /api/security/issues/{id}' => ['controller' => SecurityIssueController::class, 'method' => 'show', 'middleware' => ['auth', 'admin']],
            'PUT /api/security/issues/{id}' => ['controller' => SecurityIssueController::class, 'method' => 'update', 'middleware' => ['auth', 'admin']],
            'POST /api/security/issues/{id}/resolve' => ['controller' => SecurityIssueController::class, 'method' => 'resolve', 'middleware' => ['auth', 'admin']],
            
            // 瀹夊叏娴嬭瘯缁撴灉璺敱
            'GET /api/security/test-results' => ['controller' => SecurityTestResultController::class, 'method' => 'index', 'middleware' => ['auth', 'admin']],
            'GET /api/security/test-results/{id}' => ['controller' => SecurityTestResultController::class, 'method' => 'show', 'middleware' => ['auth', 'admin']],
            'GET /api/security/test-results/{id}/report' => ['controller' => SecurityTestResultController::class, 'method' => 'getReport', 'middleware' => ['auth', 'admin']],
            
            // 閲忓瓙AI瀹夊叏绯荤粺璺敱
            'POST /api/security/quantum/detect-threats' => ['controller' => QuantumSecurityController::class, 'method' => 'detectThreats', 'middleware' => ['auth', 'admin']],
            'GET /api/security/quantum/status' => ['controller' => QuantumSecurityController::class, 'method' => 'getSecurityStatus', 'middleware' => ['auth', 'admin']],
            'GET /api/security/quantum/defense-level' => ['controller' => QuantumSecurityController::class, 'method' => 'getDefenseLevel', 'middleware' => ['auth', 'admin']],
            'POST /api/security/quantum/defense-level' => ['controller' => QuantumSecurityController::class, 'method' => 'setDefenseLevel', 'middleware' => ['auth', 'admin']],
            'POST /api/security/quantum/respond-to-threat' => ['controller' => QuantumSecurityController::class, 'method' => 'respondToThreat', 'middleware' => ['auth', 'admin']],
            
            // 瀹夊叏濞佽儊绠＄悊璺敱
            'GET /api/security/threats' => ['controller' => SecurityThreatController::class, 'method' => 'index', 'middleware' => ['auth', 'admin']],
            'POST /api/security/threats' => ['controller' => SecurityThreatController::class, 'method' => 'store', 'middleware' => ['auth', 'admin']],
            'GET /api/security/threats/{id}' => ['controller' => SecurityThreatController::class, 'method' => 'show', 'middleware' => ['auth', 'admin']],
            'PUT /api/security/threats/{id}' => ['controller' => SecurityThreatController::class, 'method' => 'update', 'middleware' => ['auth', 'admin']],
            'POST /api/security/threats/{id}/resolve' => ['controller' => SecurityThreatController::class, 'method' => 'resolve', 'middleware' => ['auth', 'admin']],
            'POST /api/security/threats/{id}/false-positive' => ['controller' => SecurityThreatController::class, 'method' => 'markAsFalsePositive', 'middleware' => ['auth', 'admin']],
            'POST /api/security/threats/{id}/respond' => ['controller' => SecurityThreatController::class, 'method' => 'respondToThreat', 'middleware' => ['auth', 'admin']],
            'GET /api/security/threats/statistics' => ['controller' => SecurityThreatController::class, 'method' => 'getStatistics', 'middleware' => ['auth', 'admin']],
            
            // 閲忓瓙瀹夊叏浠〃鐩樿矾鐢?
            'GET /api/security/dashboard/overview' => ['controller' => QuantumSecurityDashboardController::class, 'method' => 'getDashboardOverview', 'middleware' => ['auth', 'admin']],
            'GET /api/security/dashboard/threat-trends' => ['controller' => QuantumSecurityDashboardController::class, 'method' => 'getThreatTrends', 'middleware' => ['auth', 'admin']],
            'GET /api/security/dashboard/threat-distribution' => ['controller' => QuantumSecurityDashboardController::class, 'method' => 'getThreatDistribution', 'middleware' => ['auth', 'admin']],
            'GET /api/security/dashboard/defense-effectiveness' => ['controller' => QuantumSecurityDashboardController::class, 'method' => 'getDefenseEffectiveness', 'middleware' => ['auth', 'admin']],
            
            // 闅旂鍖篈PI璺敱
            'GET /api/security/quarantine/items' => ['controller' => QuarantineController::class, 'method' => 'getQuarantineItems', 'middleware' => ['auth', 'admin']],
            'GET /api/security/quarantine/items/{id}' => ['controller' => QuarantineController::class, 'method' => 'getQuarantineItem', 'middleware' => ['auth', 'admin']],
            'POST /api/security/quarantine/items/{id}/update-status' => ['controller' => QuarantineController::class, 'method' => 'apiUpdateStatus', 'middleware' => ['auth', 'admin']],
            'GET /api/security/quarantine/ip-bans' => ['controller' => QuarantineController::class, 'method' => 'getIpBans', 'middleware' => ['auth', 'admin']],
            'POST /api/security/quarantine/ban-ip' => ['controller' => QuarantineController::class, 'method' => 'apiBanIp', 'middleware' => ['auth', 'admin']],
            'POST /api/security/quarantine/revoke-ip-ban/{id}' => ['controller' => QuarantineController::class, 'method' => 'apiRevokeIpBan', 'middleware' => ['auth', 'admin']],
            'GET /api/security/quarantine/statistics' => ['controller' => QuarantineController::class, 'method' => 'getQuarantineStatistics', 'middleware' => ['auth', 'admin']],
        ];
    }
    
    /**
     * 宸ュ崟绯荤粺璺敱
     */
    private static function getTicketRoutes() {
        return [
            // 宸ュ崟绠＄悊璺敱
            'GET /api/tickets' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'index', 'middleware' => ['auth']],
            'POST /api/tickets' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'store', 'middleware' => ['auth']],
            'GET /api/tickets/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'show', 'middleware' => ['auth']],
            'PUT /api/tickets/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'update', 'middleware' => ['auth']],
            'DELETE /api/tickets/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'destroy', 'middleware' => ['auth', 'admin']],
            
            // 宸ュ崟鍥炲璺敱
            'POST /api/tickets/{id}/reply' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'reply', 'middleware' => ['auth']],
            'GET /api/tickets/{id}/replies' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'getReplies', 'middleware' => ['auth']],
            
            // 宸ュ崟鎿嶄綔璺敱
            'POST /api/tickets/{id}/assign' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'assign', 'middleware' => ['auth', 'role:admin,support']],
            'POST /api/tickets/{id}/close' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'close', 'middleware' => ['auth']],
            'POST /api/tickets/{id}/reopen' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'reopen', 'middleware' => ['auth']],
            
            // 宸ュ崟闄勪欢璺敱
            'POST /api/tickets/{id}/attachments' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'uploadAttachment', 'middleware' => ['auth']],
            'DELETE /api/tickets/{id}/attachments/{attachmentId}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'deleteAttachment', 'middleware' => ['auth']],
            'GET /api/tickets/{id}/attachments' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'getAttachments', 'middleware' => ['auth']],
            
            // 宸ュ崟缁熻璺敱
            'GET /api/tickets/statistics' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'getStatistics', 'middleware' => ['auth']],
            
            // 宸ュ崟閮ㄩ棬璺敱
            'GET /api/ticket-departments' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'getDepartments', 'middleware' => ['auth']],
            'POST /api/ticket-departments' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'createDepartment', 'middleware' => ['auth', 'admin']],
            'GET /api/ticket-departments/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'showDepartment', 'middleware' => ['auth']],
            'PUT /api/ticket-departments/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'updateDepartment', 'middleware' => ['auth', 'admin']],
            'DELETE /api/ticket-departments/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'deleteDepartment', 'middleware' => ['auth', 'admin']],
            
            // 宸ュ崟鍒嗙被璺敱
            'GET /api/ticket-categories' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'getCategories', 'middleware' => ['auth']],
            'POST /api/ticket-categories' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'createCategory', 'middleware' => ['auth', 'admin']],
            'GET /api/ticket-categories/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'showCategory', 'middleware' => ['auth']],
            'PUT /api/ticket-categories/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'updateCategory', 'middleware' => ['auth', 'admin']],
            'DELETE /api/ticket-categories/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'deleteCategory', 'middleware' => ['auth', 'admin']],
            'GET /api/ticket-categories/by-department/{departmentId}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'getCategoriesByDepartment', 'middleware' => ['auth']],
        ];
    }
    
    /**
     * 缃戠珯璁剧疆璺敱
     */
    private static function getSettingRoutes() {
        return [
            // 璁剧疆绠＄悊璺敱
            'GET /api/settings' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'index', 'middleware' => ['auth', 'admin']],
            'GET /api/settings/{key}' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'show', 'middleware' => ['auth', 'admin']],
            'PUT /api/settings/{key}' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'update', 'middleware' => ['auth', 'admin']],
            'POST /api/settings' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'store', 'middleware' => ['auth', 'admin']],
            'DELETE /api/settings/{key}' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'destroy', 'middleware' => ['auth', 'admin']],
            
            // 璁剧疆鍒嗙粍璺敱
            'GET /api/settings/group/{group}' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'getGroup', 'middleware' => ['auth', 'admin']],
            'PUT /api/settings/group/{group}' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'updateGroup', 'middleware' => ['auth', 'admin']],
            
            // 璁剧疆鎿嶄綔璺敱
            'POST /api/settings/clear-cache' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'clearCache', 'middleware' => ['auth', 'admin']],
            'POST /api/settings/init-system' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'initSystemSettings', 'middleware' => ['auth', 'admin']],
            
            // 鍏叡璁剧疆璺敱锛堟棤闇€鐧诲綍锛?
            'GET /api/public-settings' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'getPublicSettings'],
        ];
    }
    
    /**
     * 鍖哄潡閾惧拰閽卞寘璺敱
     */
    private static function getBlockchainRoutes() {
        return [
            'GET /api/wallet/balance' => ['controller' => 'WalletController', 'method' => 'getBalance', 'middleware' => ['auth']],
            'POST /api/wallet/transfer' => ['controller' => 'WalletController', 'method' => 'transfer', 'middleware' => ['auth']],
            'GET /api/wallet/transactions' => ['controller' => 'WalletController', 'method' => 'getTransactions', 'middleware' => ['auth']],
            'POST /api/wallet/create' => ['controller' => 'WalletController', 'method' => 'createWallet', 'middleware' => ['auth']],
            'GET /api/payment/methods' => ['controller' => 'PaymentController', 'method' => 'getPaymentMethods', 'middleware' => ['auth']],
            'POST /api/payment/process' => ['controller' => 'PaymentController', 'method' => 'processPayment', 'middleware' => ['auth']],
        ];
    }
    
    /**
     * 绠＄悊鍛樿矾鐢?
     */
    private static function getAdminRoutes() {
        return [
            'GET /api/admin/users' => ['controller' => 'UserController', 'method' => 'getUsers', 'middleware' => ['auth', 'admin']],
            'POST /api/admin/users' => ['controller' => 'UserController', 'method' => 'createUser', 'middleware' => ['auth', 'admin']],
            'GET /api/admin/users/{id}' => ['controller' => 'UserController', 'method' => 'getUser', 'middleware' => ['auth', 'admin']],
            'PUT /api/admin/users/{id}' => ['controller' => 'UserController', 'method' => 'updateUser', 'middleware' => ['auth', 'admin']],
            'DELETE /api/admin/users/{id}' => ['controller' => 'UserController', 'method' => 'deleteUser', 'middleware' => ['auth', 'admin']],
            'POST /api/admin/users/{id}/suspend' => ['controller' => 'UserController', 'method' => 'suspendUser', 'middleware' => ['auth', 'admin']],
            'POST /api/admin/users/{id}/activate' => ['controller' => 'UserController', 'method' => 'activateUser', 'middleware' => ['auth', 'admin']],
            'GET /api/admin/users/{id}/activity' => ['controller' => 'UserController', 'method' => 'getUserActivity', 'middleware' => ['auth', 'admin']],
            'GET /api/admin/system/logs' => ['controller' => 'SystemController', 'method' => 'getLogs', 'middleware' => ['auth', 'admin']],
            'POST /api/admin/system/maintenance' => ['controller' => 'SystemController', 'method' => 'toggleMaintenance', 'middleware' => ['auth', 'admin']],
            'POST /api/admin/system/cache/clear' => ['controller' => 'SystemController', 'method' => 'clearCache', 'middleware' => ['auth', 'admin']],
            'GET /api/admin/audit/logs' => ['controller' => 'SystemController', 'method' => 'getAuditLogs', 'middleware' => ['auth', 'admin']],
            'GET /api/admin/audit/logs/{id}' => ['controller' => 'SystemController', 'method' => 'getAuditLog', 'middleware' => ['auth', 'admin']],
            'GET /api/admin/audit/statistics' => ['controller' => 'SystemController', 'method' => 'getAuditStatistics', 'middleware' => ['auth', 'admin']],
        ];
    }
    
    /**
     * 鐩戞帶璺敱
     */
    private static function getMonitoringRoutes() {
        return [
            'GET /api/monitoring/metrics' => ['controller' => 'MonitoringController', 'method' => 'getMetrics', 'middleware' => ['auth', 'admin']],
            'GET /api/monitoring/performance' => ['controller' => 'MonitoringController', 'method' => 'getPerformanceMetrics', 'middleware' => ['auth', 'admin']],
            'GET /api/monitoring/alerts' => ['controller' => 'MonitoringController', 'method' => 'getAlerts', 'middleware' => ['auth', 'admin']],
            'POST /api/monitoring/alerts/{id}/acknowledge' => ['controller' => 'MonitoringController', 'method' => 'acknowledgeAlert', 'middleware' => ['auth', 'admin']],
            
            'GET /api/monitoring/system/performance' => ['controller' => SystemMonitorController::class, 'method' => 'getPerformanceMetrics', 'middleware' => ['auth', 'admin']],
            'GET /api/monitoring/system/application' => ['controller' => SystemMonitorController::class, 'method' => 'getApplicationMetrics', 'middleware' => ['auth', 'admin']],
            'GET /api/monitoring/system/health' => ['controller' => SystemMonitorController::class, 'method' => 'getHealthStatus', 'middleware' => ['auth', 'admin']],
            
            'GET /api/monitoring/system/alerts' => ['controller' => AlertController::class, 'method' => 'getActiveAlerts', 'middleware' => ['auth', 'admin']],
            'GET /api/monitoring/system/alerts/{id}' => ['controller' => AlertController::class, 'method' => 'getAlertDetail', 'middleware' => ['auth', 'admin']],
            'POST /api/monitoring/system/alerts/{id}/acknowledge' => ['controller' => AlertController::class, 'method' => 'acknowledgeAlert', 'middleware' => ['auth', 'admin']],
            'POST /api/monitoring/system/alerts/{id}/resolve' => ['controller' => AlertController::class, 'method' => 'resolveAlert', 'middleware' => ['auth', 'admin']],
            'POST /api/monitoring/system/check' => ['controller' => AlertController::class, 'method' => 'triggerSystemCheck', 'middleware' => ['auth', 'admin']],
        ];
    }
    
    /**
     * 鍏紑璺敱
     */
    private static function getPublicRoutes() {
        return [
            'GET /api/docs/api' => ['controller' => 'DocumentController', 'method' => 'getApiDocs'],
            'GET /api/docs/openapi' => ['controller' => 'DocumentController', 'method' => 'getOpenApiSpec'],
            'GET /api/stats/public' => ['controller' => 'SystemController', 'method' => 'getPublicStats'],
            'POST /api/webhooks/github' => ['controller' => 'SystemController', 'method' => 'githubWebhook'],
            'POST /api/webhooks/blockchain/{network}' => ['controller' => 'WalletController', 'method' => 'blockchainWebhook'],
            'POST /api/webhooks/ai/callback' => ['controller' => 'SimpleApiController', 'method' => 'aiCallback'],
        ];
    }
}

// 杩斿洖鎵€鏈堿PI璺敱
return ApiRoutes::getRoutes();
    /**
     * 新闻管理路由
     */
    private static function getNewsRoutes() {
        return [
            // 新闻管理路由
            'GET /api/news' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'index'],
            'GET /api/news/{slug}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'show'],
            'GET /api/news/categories' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'getCategories'],
            'GET /api/news/tags' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'getTags'],
            'GET /api/news/category/{slug}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'getByCategory'],
            'GET /api/news/tag/{slug}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'getByTag'],
            'GET /api/news/{slug}/comments' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'getComments'],
            'POST /api/news/{slug}/comment' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'addComment', 'middleware' => ['auth:api']],
            'GET /api/news/featured' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'getFeatured'],
            
            // 后台新闻管理路由
            'POST /api/admin/news' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'store', 'middleware' => ['auth:api', 'role:admin,editor']],
            'PUT /api/admin/news/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'update', 'middleware' => ['auth:api', 'role:admin,editor']],
            'DELETE /api/admin/news/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'destroy', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/{id}/toggle-featured' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'toggleFeatured', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/{id}/publish' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'publish', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/{id}/draft' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'draft', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/{id}/archive' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'archive', 'middleware' => ['auth:api', 'role:admin,editor']],
            
            // 分类管理路由
            'GET /api/admin/news/categories' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'adminGetCategories', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/categories' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'storeCategory', 'middleware' => ['auth:api', 'role:admin,editor']],
            'GET /api/admin/news/categories/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'showCategory', 'middleware' => ['auth:api', 'role:admin,editor']],
            'PUT /api/admin/news/categories/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'updateCategory', 'middleware' => ['auth:api', 'role:admin,editor']],
            'DELETE /api/admin/news/categories/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'destroyCategory', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/categories/{id}/toggle-status' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'toggleCategoryStatus', 'middleware' => ['auth:api', 'role:admin,editor']],
            
            // 标签管理路由
            'GET /api/admin/news/tags' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'adminGetTags', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/tags' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'storeTag', 'middleware' => ['auth:api', 'role:admin,editor']],
            'GET /api/admin/news/tags/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'showTag', 'middleware' => ['auth:api', 'role:admin,editor']],
            'PUT /api/admin/news/tags/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'updateTag', 'middleware' => ['auth:api', 'role:admin,editor']],
            'DELETE /api/admin/news/tags/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'destroyTag', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/tags/{id}/toggle-status' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'toggleTagStatus', 'middleware' => ['auth:api', 'role:admin,editor']],
            
            // 评论管理路由
            'GET /api/admin/news/comments' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'adminGetComments', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
            'GET /api/admin/news/comments/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'showComment', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
            'POST /api/admin/news/comments/{id}/approve' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'approveComment', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
            'POST /api/admin/news/comments/{id}/reject' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'rejectComment', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
            'POST /api/admin/news/comments/{id}/reply' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'replyToComment', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
            'DELETE /api/admin/news/comments/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'destroyComment', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
            'POST /api/admin/news/comments/batch-action' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'batchActionComments', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
        ];
    }
 = ->content;
                
                // 使用邮件服务发送批量邮件
                ->emailService->sendBulkEmails(
                    ,
                    ,
                    <?php

/**
 * AlingAi Pro 6.0 API Routes
 * 闆朵俊浠婚噺瀛愬姞瀵嗙郴缁?API 璺敱閰嶇疆
 */

use AlingAi\Config\Routes;
use AlingAi\Controllers\AuthController;
use AlingAi\Controllers\UserController;
use AlingAi\Controllers\SystemController;
use AlingAi\Controllers\SystemManagementController;
use AlingAi\Controllers\MonitoringController;
use AlingAi\Controllers\WalletController;
use AlingAi\Controllers\EnterpriseAdminController;
use AlingAi\Controllers\PaymentController;
use AlingAi\Controllers\DocumentController;
use AlingAi\Controllers\SimpleApiController;
use App\Http\Controllers\Security\QuantumCryptoController;
use App\Http\Controllers\Security\SecurityTestController;
use App\Http\Controllers\Security\VulnerabilityScanController;
use App\Http\Controllers\Security\IntrusionDetectionController;
use App\Http\Controllers\Security\CryptoKeyController;
use App\Http\Controllers\Security\SecurityIssueController;
use App\Http\Controllers\Security\SecurityTestResultController;
use App\Http\Controllers\Security\SecurityThreatController;
use App\Http\Controllers\Monitoring\SystemMonitorController;
use App\Http\Controllers\Monitoring\AlertController;
use App\Http\Controllers\Security\QuantumSecurityController;
use App\Http\Controllers\Security\QuantumSecurityDashboardController;
use App\Http\Controllers\Security\QuarantineController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * API璺敱瀹氫箟
 * 鍩轰簬鑷畾涔夎矾鐢辩郴缁熺殑API绔偣閰嶇疆
 */
class ApiRoutes {
    
    /**
     * 鑾峰彇鎵€鏈堿PI璺敱
     */
    public static function getRoutes() {
        return array_merge(
            self::getAuthRoutes(),
            self::getUserRoutes(),
            self::getSystemRoutes(),
            self::getEnterpriseRoutes(),
            self::getSecurityRoutes(),
            self::getTicketRoutes(),
            self::getSettingRoutes(),
            self::getNewsRoutes(),
            self::getBlockchainRoutes(),
            self::getAdminRoutes(),
            self::getMonitoringRoutes(),
            self::getPublicRoutes()
        );
    }
    
    /**
     * 璁よ瘉鐩稿叧璺敱
     */
    private static function getAuthRoutes() {
        return [
            'POST /api/auth/login' => ['controller' => 'AuthController', 'method' => 'login'],
            'POST /api/auth/register' => ['controller' => 'AuthController', 'method' => 'register'],
            'POST /api/auth/logout' => ['controller' => 'AuthController', 'method' => 'logout'],
            'POST /api/auth/refresh' => ['controller' => 'AuthController', 'method' => 'refresh'],
            'POST /api/auth/forgot-password' => ['controller' => 'AuthController', 'method' => 'forgotPassword'],
            'POST /api/auth/reset-password' => ['controller' => 'AuthController', 'method' => 'resetPassword'],
            'POST /api/auth/verify-email' => ['controller' => 'AuthController', 'method' => 'verifyEmail'],
            'POST /api/auth/resend-verification' => ['controller' => 'AuthController', 'method' => 'resendVerification'],
            'GET /api/auth/user' => ['controller' => 'AuthController', 'method' => 'getUser', 'middleware' => ['auth']],
        ];
    }
    
    /**
     * 鐢ㄦ埛鐩稿叧璺敱
     */
    private static function getUserRoutes() {
        return [
            'GET /api/user/profile' => ['controller' => 'UserController', 'method' => 'profile', 'middleware' => ['auth']],
            'PUT /api/user/profile' => ['controller' => 'UserController', 'method' => 'updateProfile', 'middleware' => ['auth']],
            'POST /api/user/avatar' => ['controller' => 'UserController', 'method' => 'uploadAvatar', 'middleware' => ['auth']],
            'GET /api/user/settings' => ['controller' => 'UserController', 'method' => 'getSettings', 'middleware' => ['auth']],
            'PUT /api/user/settings' => ['controller' => 'UserController', 'method' => 'updateSettings', 'middleware' => ['auth']],
            'POST /api/user/change-password' => ['controller' => 'UserController', 'method' => 'changePassword', 'middleware' => ['auth']],
            'GET /api/user/activity' => ['controller' => 'UserController', 'method' => 'getActivity', 'middleware' => ['auth']],
            'GET /api/user/notifications' => ['controller' => 'UserController', 'method' => 'getNotifications', 'middleware' => ['auth']],
            'PUT /api/user/notifications/{id}/read' => ['controller' => 'UserController', 'method' => 'markNotificationAsRead', 'middleware' => ['auth']],
        ];
    }
    
    /**
     * 绯荤粺鐩稿叧璺敱
     */
    private static function getSystemRoutes() {
        return [
            'GET /api/version' => ['controller' => 'SystemController', 'method' => 'getVersion'],
            'GET /api/health' => ['controller' => 'SystemController', 'method' => 'healthCheck'],
            'GET /api/health/detailed' => ['controller' => 'SystemController', 'method' => 'detailedHealthCheck'],
            'GET /api/status' => ['controller' => 'SystemController', 'method' => 'getStatus'],
            'GET /api/metrics' => ['controller' => 'SystemController', 'method' => 'getMetrics'],
            'GET /api/system/info' => ['controller' => 'SystemController', 'method' => 'getSystemInfo', 'middleware' => ['auth', 'admin']],
            'GET /api/system/config' => ['controller' => 'SystemController', 'method' => 'getConfig', 'middleware' => ['auth', 'admin']],
            'PUT /api/system/config' => ['controller' => 'SystemController', 'method' => 'updateConfig', 'middleware' => ['auth', 'admin']],
        ];
    }
    
    /**
     * 浼佷笟鏈嶅姟璺敱
     */
    private static function getEnterpriseRoutes() {
        return [
            'GET /api/enterprise/dashboard' => ['controller' => 'EnterpriseAdminController', 'method' => 'dashboard', 'middleware' => ['auth']],
            'GET /api/enterprise/organizations' => ['controller' => 'EnterpriseAdminController', 'method' => 'getOrganizations', 'middleware' => ['auth']],
            'POST /api/enterprise/organizations' => ['controller' => 'EnterpriseAdminController', 'method' => 'createOrganization', 'middleware' => ['auth']],
            'GET /api/enterprise/workspaces' => ['controller' => 'EnterpriseAdminController', 'method' => 'getWorkspaces', 'middleware' => ['auth']],
            'POST /api/enterprise/workspaces' => ['controller' => 'EnterpriseAdminController', 'method' => 'createWorkspace', 'middleware' => ['auth']],
            'GET /api/enterprise/projects' => ['controller' => 'EnterpriseAdminController', 'method' => 'getProjects', 'middleware' => ['auth']],
            'POST /api/enterprise/projects' => ['controller' => 'EnterpriseAdminController', 'method' => 'createProject', 'middleware' => ['auth']],
            'GET /api/enterprise/teams' => ['controller' => 'EnterpriseAdminController', 'method' => 'getTeams', 'middleware' => ['auth']],
            'POST /api/enterprise/teams' => ['controller' => 'EnterpriseAdminController', 'method' => 'createTeam', 'middleware' => ['auth']],
        ];
    }
    
    /**
     * 瀹夊叏鍜屽姞瀵嗚矾鐢?
     */
    private static function getSecurityRoutes() {
        return [
            // 閲忓瓙鍔犲瘑鐩稿叧璺敱
            'POST /api/security/encrypt' => ['controller' => QuantumCryptoController::class, 'method' => 'encrypt', 'middleware' => ['auth', 'quantum-security']],
            'POST /api/security/decrypt' => ['controller' => QuantumCryptoController::class, 'method' => 'decrypt', 'middleware' => ['auth', 'quantum-security']],
            'POST /api/security/sign' => ['controller' => QuantumCryptoController::class, 'method' => 'sign', 'middleware' => ['auth', 'quantum-security']],
            'POST /api/security/verify' => ['controller' => QuantumCryptoController::class, 'method' => 'verify', 'middleware' => ['auth', 'quantum-security']],
            
            // 瀵嗛挜绠＄悊璺敱
            'GET /api/security/keys' => ['controller' => CryptoKeyController::class, 'method' => 'index', 'middleware' => ['auth', 'admin']],
            'POST /api/security/keys' => ['controller' => CryptoKeyController::class, 'method' => 'store', 'middleware' => ['auth', 'admin']],
            'GET /api/security/keys/{id}' => ['controller' => CryptoKeyController::class, 'method' => 'show', 'middleware' => ['auth', 'admin']],
            'PUT /api/security/keys/{id}' => ['controller' => CryptoKeyController::class, 'method' => 'update', 'middleware' => ['auth', 'admin']],
            'DELETE /api/security/keys/{id}' => ['controller' => CryptoKeyController::class, 'method' => 'destroy', 'middleware' => ['auth', 'admin']],
            
            // 瀹夊叏娴嬭瘯鐩稿叧璺敱
            'POST /api/security/tests/run' => ['controller' => SecurityTestController::class, 'method' => 'runTest', 'middleware' => ['auth', 'admin']],
            'GET /api/security/tests/{testId}/status' => ['controller' => SecurityTestController::class, 'method' => 'getTestStatus', 'middleware' => ['auth', 'admin']],
            'GET /api/security/tests/history' => ['controller' => SecurityTestController::class, 'method' => 'getTestHistory', 'middleware' => ['auth', 'admin']],
            'GET /api/security/tests/{testId}/report' => ['controller' => SecurityTestController::class, 'method' => 'getTestReport', 'middleware' => ['auth', 'admin']],
            'POST /api/security/tests/{testId}/cancel' => ['controller' => SecurityTestController::class, 'method' => 'cancelTest', 'middleware' => ['auth', 'admin']],
            
            // 婕忔礊鎵弿鐩稿叧璺敱
            'POST /api/security/vulnerabilities/scan' => ['controller' => VulnerabilityScanController::class, 'method' => 'startScan', 'middleware' => ['auth', 'admin']],
            'GET /api/security/vulnerabilities/scan/{scanId}/status' => ['controller' => VulnerabilityScanController::class, 'method' => 'getScanStatus', 'middleware' => ['auth', 'admin']],
            'GET /api/security/vulnerabilities/scan/{scanId}/result' => ['controller' => VulnerabilityScanController::class, 'method' => 'getScanResult', 'middleware' => ['auth', 'admin']],
            'GET /api/security/vulnerabilities/history' => ['controller' => VulnerabilityScanController::class, 'method' => 'getScanHistory', 'middleware' => ['auth', 'admin']],
            'POST /api/security/vulnerabilities/scan/{scanId}/cancel' => ['controller' => VulnerabilityScanController::class, 'method' => 'cancelScan', 'middleware' => ['auth', 'admin']],
            
            // 鍏ヤ镜妫€娴嬬浉鍏宠矾鐢?
            'GET /api/security/intrusion/attempts' => ['controller' => IntrusionDetectionController::class, 'method' => 'getIntrusionAttempts', 'middleware' => ['auth', 'admin']],
            'GET /api/security/intrusion/{attemptId}/detail' => ['controller' => IntrusionDetectionController::class, 'method' => 'getIntrusionDetail', 'middleware' => ['auth', 'admin']],
            'POST /api/security/intrusion/{attemptId}/resolve' => ['controller' => IntrusionDetectionController::class, 'method' => 'resolveIntrusion', 'middleware' => ['auth', 'admin']],
            
            // 瀹夊叏闂绠＄悊璺敱
            'GET /api/security/issues' => ['controller' => SecurityIssueController::class, 'method' => 'index', 'middleware' => ['auth', 'admin']],
            'POST /api/security/issues' => ['controller' => SecurityIssueController::class, 'method' => 'store', 'middleware' => ['auth', 'admin']],
            'GET /api/security/issues/{id}' => ['controller' => SecurityIssueController::class, 'method' => 'show', 'middleware' => ['auth', 'admin']],
            'PUT /api/security/issues/{id}' => ['controller' => SecurityIssueController::class, 'method' => 'update', 'middleware' => ['auth', 'admin']],
            'POST /api/security/issues/{id}/resolve' => ['controller' => SecurityIssueController::class, 'method' => 'resolve', 'middleware' => ['auth', 'admin']],
            
            // 瀹夊叏娴嬭瘯缁撴灉璺敱
            'GET /api/security/test-results' => ['controller' => SecurityTestResultController::class, 'method' => 'index', 'middleware' => ['auth', 'admin']],
            'GET /api/security/test-results/{id}' => ['controller' => SecurityTestResultController::class, 'method' => 'show', 'middleware' => ['auth', 'admin']],
            'GET /api/security/test-results/{id}/report' => ['controller' => SecurityTestResultController::class, 'method' => 'getReport', 'middleware' => ['auth', 'admin']],
            
            // 閲忓瓙AI瀹夊叏绯荤粺璺敱
            'POST /api/security/quantum/detect-threats' => ['controller' => QuantumSecurityController::class, 'method' => 'detectThreats', 'middleware' => ['auth', 'admin']],
            'GET /api/security/quantum/status' => ['controller' => QuantumSecurityController::class, 'method' => 'getSecurityStatus', 'middleware' => ['auth', 'admin']],
            'GET /api/security/quantum/defense-level' => ['controller' => QuantumSecurityController::class, 'method' => 'getDefenseLevel', 'middleware' => ['auth', 'admin']],
            'POST /api/security/quantum/defense-level' => ['controller' => QuantumSecurityController::class, 'method' => 'setDefenseLevel', 'middleware' => ['auth', 'admin']],
            'POST /api/security/quantum/respond-to-threat' => ['controller' => QuantumSecurityController::class, 'method' => 'respondToThreat', 'middleware' => ['auth', 'admin']],
            
            // 瀹夊叏濞佽儊绠＄悊璺敱
            'GET /api/security/threats' => ['controller' => SecurityThreatController::class, 'method' => 'index', 'middleware' => ['auth', 'admin']],
            'POST /api/security/threats' => ['controller' => SecurityThreatController::class, 'method' => 'store', 'middleware' => ['auth', 'admin']],
            'GET /api/security/threats/{id}' => ['controller' => SecurityThreatController::class, 'method' => 'show', 'middleware' => ['auth', 'admin']],
            'PUT /api/security/threats/{id}' => ['controller' => SecurityThreatController::class, 'method' => 'update', 'middleware' => ['auth', 'admin']],
            'POST /api/security/threats/{id}/resolve' => ['controller' => SecurityThreatController::class, 'method' => 'resolve', 'middleware' => ['auth', 'admin']],
            'POST /api/security/threats/{id}/false-positive' => ['controller' => SecurityThreatController::class, 'method' => 'markAsFalsePositive', 'middleware' => ['auth', 'admin']],
            'POST /api/security/threats/{id}/respond' => ['controller' => SecurityThreatController::class, 'method' => 'respondToThreat', 'middleware' => ['auth', 'admin']],
            'GET /api/security/threats/statistics' => ['controller' => SecurityThreatController::class, 'method' => 'getStatistics', 'middleware' => ['auth', 'admin']],
            
            // 閲忓瓙瀹夊叏浠〃鐩樿矾鐢?
            'GET /api/security/dashboard/overview' => ['controller' => QuantumSecurityDashboardController::class, 'method' => 'getDashboardOverview', 'middleware' => ['auth', 'admin']],
            'GET /api/security/dashboard/threat-trends' => ['controller' => QuantumSecurityDashboardController::class, 'method' => 'getThreatTrends', 'middleware' => ['auth', 'admin']],
            'GET /api/security/dashboard/threat-distribution' => ['controller' => QuantumSecurityDashboardController::class, 'method' => 'getThreatDistribution', 'middleware' => ['auth', 'admin']],
            'GET /api/security/dashboard/defense-effectiveness' => ['controller' => QuantumSecurityDashboardController::class, 'method' => 'getDefenseEffectiveness', 'middleware' => ['auth', 'admin']],
            
            // 闅旂鍖篈PI璺敱
            'GET /api/security/quarantine/items' => ['controller' => QuarantineController::class, 'method' => 'getQuarantineItems', 'middleware' => ['auth', 'admin']],
            'GET /api/security/quarantine/items/{id}' => ['controller' => QuarantineController::class, 'method' => 'getQuarantineItem', 'middleware' => ['auth', 'admin']],
            'POST /api/security/quarantine/items/{id}/update-status' => ['controller' => QuarantineController::class, 'method' => 'apiUpdateStatus', 'middleware' => ['auth', 'admin']],
            'GET /api/security/quarantine/ip-bans' => ['controller' => QuarantineController::class, 'method' => 'getIpBans', 'middleware' => ['auth', 'admin']],
            'POST /api/security/quarantine/ban-ip' => ['controller' => QuarantineController::class, 'method' => 'apiBanIp', 'middleware' => ['auth', 'admin']],
            'POST /api/security/quarantine/revoke-ip-ban/{id}' => ['controller' => QuarantineController::class, 'method' => 'apiRevokeIpBan', 'middleware' => ['auth', 'admin']],
            'GET /api/security/quarantine/statistics' => ['controller' => QuarantineController::class, 'method' => 'getQuarantineStatistics', 'middleware' => ['auth', 'admin']],
        ];
    }
    
    /**
     * 宸ュ崟绯荤粺璺敱
     */
    private static function getTicketRoutes() {
        return [
            // 宸ュ崟绠＄悊璺敱
            'GET /api/tickets' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'index', 'middleware' => ['auth']],
            'POST /api/tickets' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'store', 'middleware' => ['auth']],
            'GET /api/tickets/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'show', 'middleware' => ['auth']],
            'PUT /api/tickets/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'update', 'middleware' => ['auth']],
            'DELETE /api/tickets/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'destroy', 'middleware' => ['auth', 'admin']],
            
            // 宸ュ崟鍥炲璺敱
            'POST /api/tickets/{id}/reply' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'reply', 'middleware' => ['auth']],
            'GET /api/tickets/{id}/replies' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'getReplies', 'middleware' => ['auth']],
            
            // 宸ュ崟鎿嶄綔璺敱
            'POST /api/tickets/{id}/assign' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'assign', 'middleware' => ['auth', 'role:admin,support']],
            'POST /api/tickets/{id}/close' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'close', 'middleware' => ['auth']],
            'POST /api/tickets/{id}/reopen' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'reopen', 'middleware' => ['auth']],
            
            // 宸ュ崟闄勪欢璺敱
            'POST /api/tickets/{id}/attachments' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'uploadAttachment', 'middleware' => ['auth']],
            'DELETE /api/tickets/{id}/attachments/{attachmentId}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'deleteAttachment', 'middleware' => ['auth']],
            'GET /api/tickets/{id}/attachments' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'getAttachments', 'middleware' => ['auth']],
            
            // 宸ュ崟缁熻璺敱
            'GET /api/tickets/statistics' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'getStatistics', 'middleware' => ['auth']],
            
            // 宸ュ崟閮ㄩ棬璺敱
            'GET /api/ticket-departments' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'getDepartments', 'middleware' => ['auth']],
            'POST /api/ticket-departments' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'createDepartment', 'middleware' => ['auth', 'admin']],
            'GET /api/ticket-departments/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'showDepartment', 'middleware' => ['auth']],
            'PUT /api/ticket-departments/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'updateDepartment', 'middleware' => ['auth', 'admin']],
            'DELETE /api/ticket-departments/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'deleteDepartment', 'middleware' => ['auth', 'admin']],
            
            // 宸ュ崟鍒嗙被璺敱
            'GET /api/ticket-categories' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'getCategories', 'middleware' => ['auth']],
            'POST /api/ticket-categories' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'createCategory', 'middleware' => ['auth', 'admin']],
            'GET /api/ticket-categories/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'showCategory', 'middleware' => ['auth']],
            'PUT /api/ticket-categories/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'updateCategory', 'middleware' => ['auth', 'admin']],
            'DELETE /api/ticket-categories/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'deleteCategory', 'middleware' => ['auth', 'admin']],
            'GET /api/ticket-categories/by-department/{departmentId}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'getCategoriesByDepartment', 'middleware' => ['auth']],
        ];
    }
    
    /**
     * 缃戠珯璁剧疆璺敱
     */
    private static function getSettingRoutes() {
        return [
            // 璁剧疆绠＄悊璺敱
            'GET /api/settings' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'index', 'middleware' => ['auth', 'admin']],
            'GET /api/settings/{key}' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'show', 'middleware' => ['auth', 'admin']],
            'PUT /api/settings/{key}' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'update', 'middleware' => ['auth', 'admin']],
            'POST /api/settings' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'store', 'middleware' => ['auth', 'admin']],
            'DELETE /api/settings/{key}' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'destroy', 'middleware' => ['auth', 'admin']],
            
            // 璁剧疆鍒嗙粍璺敱
            'GET /api/settings/group/{group}' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'getGroup', 'middleware' => ['auth', 'admin']],
            'PUT /api/settings/group/{group}' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'updateGroup', 'middleware' => ['auth', 'admin']],
            
            // 璁剧疆鎿嶄綔璺敱
            'POST /api/settings/clear-cache' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'clearCache', 'middleware' => ['auth', 'admin']],
            'POST /api/settings/init-system' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'initSystemSettings', 'middleware' => ['auth', 'admin']],
            
            // 鍏叡璁剧疆璺敱锛堟棤闇€鐧诲綍锛?
            'GET /api/public-settings' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'getPublicSettings'],
        ];
    }
    
    /**
     * 鍖哄潡閾惧拰閽卞寘璺敱
     */
    private static function getBlockchainRoutes() {
        return [
            'GET /api/wallet/balance' => ['controller' => 'WalletController', 'method' => 'getBalance', 'middleware' => ['auth']],
            'POST /api/wallet/transfer' => ['controller' => 'WalletController', 'method' => 'transfer', 'middleware' => ['auth']],
            'GET /api/wallet/transactions' => ['controller' => 'WalletController', 'method' => 'getTransactions', 'middleware' => ['auth']],
            'POST /api/wallet/create' => ['controller' => 'WalletController', 'method' => 'createWallet', 'middleware' => ['auth']],
            'GET /api/payment/methods' => ['controller' => 'PaymentController', 'method' => 'getPaymentMethods', 'middleware' => ['auth']],
            'POST /api/payment/process' => ['controller' => 'PaymentController', 'method' => 'processPayment', 'middleware' => ['auth']],
        ];
    }
    
    /**
     * 绠＄悊鍛樿矾鐢?
     */
    private static function getAdminRoutes() {
        return [
            'GET /api/admin/users' => ['controller' => 'UserController', 'method' => 'getUsers', 'middleware' => ['auth', 'admin']],
            'POST /api/admin/users' => ['controller' => 'UserController', 'method' => 'createUser', 'middleware' => ['auth', 'admin']],
            'GET /api/admin/users/{id}' => ['controller' => 'UserController', 'method' => 'getUser', 'middleware' => ['auth', 'admin']],
            'PUT /api/admin/users/{id}' => ['controller' => 'UserController', 'method' => 'updateUser', 'middleware' => ['auth', 'admin']],
            'DELETE /api/admin/users/{id}' => ['controller' => 'UserController', 'method' => 'deleteUser', 'middleware' => ['auth', 'admin']],
            'POST /api/admin/users/{id}/suspend' => ['controller' => 'UserController', 'method' => 'suspendUser', 'middleware' => ['auth', 'admin']],
            'POST /api/admin/users/{id}/activate' => ['controller' => 'UserController', 'method' => 'activateUser', 'middleware' => ['auth', 'admin']],
            'GET /api/admin/users/{id}/activity' => ['controller' => 'UserController', 'method' => 'getUserActivity', 'middleware' => ['auth', 'admin']],
            'GET /api/admin/system/logs' => ['controller' => 'SystemController', 'method' => 'getLogs', 'middleware' => ['auth', 'admin']],
            'POST /api/admin/system/maintenance' => ['controller' => 'SystemController', 'method' => 'toggleMaintenance', 'middleware' => ['auth', 'admin']],
            'POST /api/admin/system/cache/clear' => ['controller' => 'SystemController', 'method' => 'clearCache', 'middleware' => ['auth', 'admin']],
            'GET /api/admin/audit/logs' => ['controller' => 'SystemController', 'method' => 'getAuditLogs', 'middleware' => ['auth', 'admin']],
            'GET /api/admin/audit/logs/{id}' => ['controller' => 'SystemController', 'method' => 'getAuditLog', 'middleware' => ['auth', 'admin']],
            'GET /api/admin/audit/statistics' => ['controller' => 'SystemController', 'method' => 'getAuditStatistics', 'middleware' => ['auth', 'admin']],
        ];
    }
    
    /**
     * 鐩戞帶璺敱
     */
    private static function getMonitoringRoutes() {
        return [
            'GET /api/monitoring/metrics' => ['controller' => 'MonitoringController', 'method' => 'getMetrics', 'middleware' => ['auth', 'admin']],
            'GET /api/monitoring/performance' => ['controller' => 'MonitoringController', 'method' => 'getPerformanceMetrics', 'middleware' => ['auth', 'admin']],
            'GET /api/monitoring/alerts' => ['controller' => 'MonitoringController', 'method' => 'getAlerts', 'middleware' => ['auth', 'admin']],
            'POST /api/monitoring/alerts/{id}/acknowledge' => ['controller' => 'MonitoringController', 'method' => 'acknowledgeAlert', 'middleware' => ['auth', 'admin']],
            
            'GET /api/monitoring/system/performance' => ['controller' => SystemMonitorController::class, 'method' => 'getPerformanceMetrics', 'middleware' => ['auth', 'admin']],
            'GET /api/monitoring/system/application' => ['controller' => SystemMonitorController::class, 'method' => 'getApplicationMetrics', 'middleware' => ['auth', 'admin']],
            'GET /api/monitoring/system/health' => ['controller' => SystemMonitorController::class, 'method' => 'getHealthStatus', 'middleware' => ['auth', 'admin']],
            
            'GET /api/monitoring/system/alerts' => ['controller' => AlertController::class, 'method' => 'getActiveAlerts', 'middleware' => ['auth', 'admin']],
            'GET /api/monitoring/system/alerts/{id}' => ['controller' => AlertController::class, 'method' => 'getAlertDetail', 'middleware' => ['auth', 'admin']],
            'POST /api/monitoring/system/alerts/{id}/acknowledge' => ['controller' => AlertController::class, 'method' => 'acknowledgeAlert', 'middleware' => ['auth', 'admin']],
            'POST /api/monitoring/system/alerts/{id}/resolve' => ['controller' => AlertController::class, 'method' => 'resolveAlert', 'middleware' => ['auth', 'admin']],
            'POST /api/monitoring/system/check' => ['controller' => AlertController::class, 'method' => 'triggerSystemCheck', 'middleware' => ['auth', 'admin']],
        ];
    }
    
    /**
     * 鍏紑璺敱
     */
    private static function getPublicRoutes() {
        return [
            'GET /api/docs/api' => ['controller' => 'DocumentController', 'method' => 'getApiDocs'],
            'GET /api/docs/openapi' => ['controller' => 'DocumentController', 'method' => 'getOpenApiSpec'],
            'GET /api/stats/public' => ['controller' => 'SystemController', 'method' => 'getPublicStats'],
            'POST /api/webhooks/github' => ['controller' => 'SystemController', 'method' => 'githubWebhook'],
            'POST /api/webhooks/blockchain/{network}' => ['controller' => 'WalletController', 'method' => 'blockchainWebhook'],
            'POST /api/webhooks/ai/callback' => ['controller' => 'SimpleApiController', 'method' => 'aiCallback'],
        ];
    }
}

// 杩斿洖鎵€鏈堿PI璺敱
return ApiRoutes::getRoutes();
    /**
     * 新闻管理路由
     */
    private static function getNewsRoutes() {
        return [
            // 新闻管理路由
            'GET /api/news' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'index'],
            'GET /api/news/{slug}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'show'],
            'GET /api/news/categories' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'getCategories'],
            'GET /api/news/tags' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'getTags'],
            'GET /api/news/category/{slug}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'getByCategory'],
            'GET /api/news/tag/{slug}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'getByTag'],
            'GET /api/news/{slug}/comments' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'getComments'],
            'POST /api/news/{slug}/comment' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'addComment', 'middleware' => ['auth:api']],
            'GET /api/news/featured' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'getFeatured'],
            
            // 后台新闻管理路由
            'POST /api/admin/news' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'store', 'middleware' => ['auth:api', 'role:admin,editor']],
            'PUT /api/admin/news/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'update', 'middleware' => ['auth:api', 'role:admin,editor']],
            'DELETE /api/admin/news/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'destroy', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/{id}/toggle-featured' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'toggleFeatured', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/{id}/publish' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'publish', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/{id}/draft' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'draft', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/{id}/archive' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'archive', 'middleware' => ['auth:api', 'role:admin,editor']],
            
            // 分类管理路由
            'GET /api/admin/news/categories' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'adminGetCategories', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/categories' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'storeCategory', 'middleware' => ['auth:api', 'role:admin,editor']],
            'GET /api/admin/news/categories/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'showCategory', 'middleware' => ['auth:api', 'role:admin,editor']],
            'PUT /api/admin/news/categories/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'updateCategory', 'middleware' => ['auth:api', 'role:admin,editor']],
            'DELETE /api/admin/news/categories/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'destroyCategory', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/categories/{id}/toggle-status' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'toggleCategoryStatus', 'middleware' => ['auth:api', 'role:admin,editor']],
            
            // 标签管理路由
            'GET /api/admin/news/tags' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'adminGetTags', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/tags' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'storeTag', 'middleware' => ['auth:api', 'role:admin,editor']],
            'GET /api/admin/news/tags/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'showTag', 'middleware' => ['auth:api', 'role:admin,editor']],
            'PUT /api/admin/news/tags/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'updateTag', 'middleware' => ['auth:api', 'role:admin,editor']],
            'DELETE /api/admin/news/tags/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'destroyTag', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/tags/{id}/toggle-status' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'toggleTagStatus', 'middleware' => ['auth:api', 'role:admin,editor']],
            
            // 评论管理路由
            'GET /api/admin/news/comments' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'adminGetComments', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
            'GET /api/admin/news/comments/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'showComment', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
            'POST /api/admin/news/comments/{id}/approve' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'approveComment', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
            'POST /api/admin/news/comments/{id}/reject' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'rejectComment', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
            'POST /api/admin/news/comments/{id}/reply' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'replyToComment', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
            'DELETE /api/admin/news/comments/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'destroyComment', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
            'POST /api/admin/news/comments/batch-action' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'batchActionComments', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
        ];
    }
,
                    
                );
            }

            // 提交事务
            DB::commit();

            return redirect()->route('admin.notification.index')
                ->with('success', '批量邮件已' . (->filled('scheduled_at') ? '计划发送' : '发送'));
        } catch (\Exception ) {
            // 回滚事务
            DB::rollBack();

            return redirect()->back()
                ->with('error', '批量邮件发送失败: ' . ->getMessage())
                ->withInput();
        }
    }

    /**
     * 解析邮箱列表
     *
     * @param string 
     * @return array
     */
    protected function parseEmails()
    {
        // 分隔符可以是逗号、分号、空格、换行
            public static function getRoutes\(\) \{[^}]*\} = '/[,;\s\n]+/';
         = preg_split(    public static function getRoutes\(\) \{[^}]*\}, );
        
        // 过滤并验证邮箱
         = [];
        foreach ( as ) {
             = trim();
            if (!empty() && filter_var(, FILTER_VALIDATE_EMAIL)) {
                [] = ;
            }
        }
        
        // 去重
        return array_unique();
    }

    /**
     * 显示批量邮件历史记录
     *
     * @param Request 
     * @return \Illuminate\View\View
     */
    public function history(Request )
    {
         = Notification::where('type', 'email')
            ->whereJsonContains('metadata->bulk_email', true);

        // 筛选条件
        if (->filled('status')) {
            ->where('status', ->status);
        }

        if (->filled('search')) {
             = ->search;
            ->where(function () use () {
                ->where('title', 'like', \
%
$search
%\)
                  ->orWhere('content', 'like', \%
$search
%\);
            });
        }

        // 排序
         = ->input('sort_by', 'created_at');
         = ->input('sort_order', 'desc');
        ->orderBy(, );

        // 分页
         = ->with(['sender', 'template'])
            ->withCount('recipients')
            ->paginate(15)
            ->appends(->all());

        return view('admin.notification.bulk_email.history', [
            'notifications' => ,
            'statuses' => [
                'draft' => '草稿',
                'sending' => '发送中',
                'sent' => '已发送',
                'failed' => '发送失败',
            ],
        ]);
    }

    /**
     * 导入邮箱列表
     *
     * @param Request 
     * @return \Illuminate\Http\JsonResponse
     */
    public function importEmails(Request )
    {
        // 验证请求数据
         = Validator::make(->all(), [
            'file' => 'required|file|mimes:csv,txt,xls,xlsx|max:10240', // 最大10MB
        ]);

        if (->fails()) {
            return response()->json([
                'success' => false,
                'message' => ->errors()->first(),
            ]);
        }

        try {
             = ->file('file');
             = ->getClientOriginalExtension();
             = [];

            // 根据文件类型处理
            if ( === 'csv' ||  === 'txt') {
                // 读取CSV或TXT文件
                 = fopen(->getPathname(), 'r');
                while (( = fgetcsv()) !== false) {
                    foreach ( as ) {
                         = trim();
                        if (!empty() && filter_var(, FILTER_VALIDATE_EMAIL)) {
                            [] = ;
                        }
                    }
                }
                fclose();
            } elseif ( === 'xls' ||  === 'xlsx') {
                // 读取Excel文件
                 = \PhpOffice\PhpSpreadsheet\IOFactory::load(->getPathname());
                 = ->getActiveSheet();
                
                foreach (->getRowIterator() as ) {
                    foreach (->getCellIterator() as ) {
                         = trim(->getValue());
                        if (!empty() && filter_var(, FILTER_VALIDATE_EMAIL)) {
                            [] = ;
                        }
                    }
                }
            }

            // 去重
             = array_unique();

            return response()->json([
                'success' => true,
                'emails' => ,
                'count' => count(),
            ]);
        } catch (\Exception ) {
            return response()->json([
                'success' => false,
                'message' => '导入失败: ' . ->getMessage(),
            ]);
        }
    }

    /**
     * 获取模板变量
     *
     * @param Request 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTemplateVariables(Request )
    {
        // 验证请求数据
         = Validator::make(->all(), [
            'template_id' => 'required|exists:notification_templates,id',
        ]);

        if (->fails()) {
            return response()->json([
                'success' => false,
                'message' => ->errors()->first(),
            ]);
        }

        try {
             = NotificationTemplate::findOrFail(->template_id);
            
            return response()->json([
                'success' => true,
                'variables' => ->variables ?: [],
                'subject' => ->subject,
            ]);
        } catch (\Exception ) {
            return response()->json([
                'success' => false,
                'message' => '获取模板变量失败: ' . ->getMessage(),
            ]);
        }
    }
}
