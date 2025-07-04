<?php

namespace App\Http\Controllers\Admin\Notification;

use App\Http\Controllers\Controller;
use App\Models\Notification\EmailProvider;
use App\Services\Notification\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 邮件发送接口管理控制器
 */
class EmailProviderController extends Controller
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
     * 显示邮件发送接口列表
     *
     * @param Request 
     * @return \Illuminate\View\View
     */
    public function index(Request )
    {
         = EmailProvider::query();

        // 筛选条件
        if (->filled('provider_type')) {
            ->where('provider_type', ->provider_type);
        }

        if (->filled('status')) {
            ->where('status', ->status);
        }

        if (->filled('search')) {
             = ->search;
            ->where(function () use () {
                ->where('name', 'like', \
%
$search
%\)
                  ->orWhere('host', 'like', \%
$search
%\)
                  ->orWhere('from_email', 'like', \%
$search
%\);
            });
        }

        // 排序
         = ->input('sort_by', 'created_at');
         = ->input('sort_order', 'desc');
        ->orderBy(, );

        // 分页
         = ->with('creator')
            ->paginate(15)
            ->appends(->all());

        return view('admin.notification.email_provider.index', [
            'providers' => ,
            'providerTypes' => [
                'smtp' => 'SMTP',
                'sendgrid' => 'SendGrid',
                'mailgun' => 'Mailgun',
                'ses' => 'Amazon SES',
            ],
            'statuses' => [
                'active' => '活动',
                'inactive' => '非活动',
            ],
        ]);
    }

    /**
     * 显示创建邮件发送接口表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.notification.email_provider.create', [
            'providerTypes' => [
                'smtp' => 'SMTP',
                'sendgrid' => 'SendGrid',
                'mailgun' => 'Mailgun',
                'ses' => 'Amazon SES',
            ],
        ]);
    }

    /**
     * 存储新创建的邮件发送接口
     *
     * @param Request 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request )
    {
        // 验证请求数据
         = Validator::make(->all(), [
            'name' => 'required|string|max:255',
            'provider_type' => 'required|string|in:smtp,sendgrid,mailgun,ses',
            'host' => 'required_if:provider_type,smtp|nullable|string|max:255',
            'port' => 'required_if:provider_type,smtp|nullable|integer',
            'username' => 'required_if:provider_type,smtp|nullable|string|max:255',
            'password' => 'required_if:provider_type,smtp|nullable|string|max:255',
            'encryption' => 'nullable|string|in:tls,ssl',
            'api_key' => 'required_if:provider_type,sendgrid,mailgun|nullable|string|max:255',
            'api_secret' => 'required_if:provider_type,ses|nullable|string|max:255',
            'region' => 'required_if:provider_type,ses|nullable|string|max:255',
            'from_email' => 'required|email|max:255',
            'from_name' => 'nullable|string|max:255',
            'reply_to_email' => 'nullable|email|max:255',
            'status' => 'required|string|in:active,inactive',
            'is_default' => 'boolean',
            'daily_limit' => 'nullable|integer|min:1',
        ]);

        if (->fails()) {
            return redirect()->back()
                ->withErrors()
                ->withInput();
        }

        try {
            // 准备额外设置
             = [];
            if (->provider_type === 'mailgun') {
                ['domain'] = ->input('mailgun_domain');
                ['endpoint'] = ->input('mailgun_endpoint', 'api.mailgun.net');
            }

            // 创建邮件发送接口
             = EmailProvider::create([
                'name' => ->name,
                'provider_type' => ->provider_type,
                'host' => ->host,
                'port' => ->port,
                'username' => ->username,
                'password' => ->password,
                'encryption' => ->encryption,
                'api_key' => ->api_key,
                'api_secret' => ->api_secret,
                'region' => ->region,
                'from_email' => ->from_email,
                'from_name' => ->from_name,
                'reply_to_email' => ->reply_to_email,
                'status' => ->status,
                'is_default' => ->is_default ?? false,
                'daily_limit' => ->daily_limit,
                'creator_id' => auth()->id(),
                'settings' => ,
            ]);

            // 如果设置为默认接口
            if (->is_default) {
                ->setAsDefault();
            }

            return redirect()->route('admin.notification.email_provider.index')
                ->with('success', '邮件发送接口创建成功');
        } catch (\Exception ) {
            return redirect()->back()
                ->with('error', '邮件发送接口创建失败: ' . ->getMessage())
                ->withInput();
        }
    }

    /**
     * 显示指定邮件发送接口
     *
     * @param EmailProvider 
     * @return \Illuminate\View\View
     */
    public function show(EmailProvider )
    {
        ->load('creator');

        return view('admin.notification.email_provider.show', [
            'provider' => ,
            'providerTypes' => [
                'smtp' => 'SMTP',
                'sendgrid' => 'SendGrid',
                'mailgun' => 'Mailgun',
                'ses' => 'Amazon SES',
            ],
            'statuses' => [
                'active' => '活动',
                'inactive' => '非活动',
            ],
        ]);
    }

    /**
     * 显示编辑邮件发送接口表单
     *
     * @param EmailProvider 
     * @return \Illuminate\View\View
     */
    public function edit(EmailProvider )
    {
        return view('admin.notification.email_provider.edit', [
            'provider' => ,
            'providerTypes' => [
                'smtp' => 'SMTP',
                'sendgrid' => 'SendGrid',
                'mailgun' => 'Mailgun',
                'ses' => 'Amazon SES',
            ],
        ]);
    }

    /**
     * 更新指定邮件发送接口
     *
     * @param Request 
     * @param EmailProvider 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request , EmailProvider )
    {
        // 验证请求数据
         = Validator::make(->all(), [
            'name' => 'required|string|max:255',
            'host' => 'required_if:provider_type,smtp|nullable|string|max:255',
            'port' => 'required_if:provider_type,smtp|nullable|integer',
            'username' => 'required_if:provider_type,smtp|nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'encryption' => 'nullable|string|in:tls,ssl',
            'api_key' => 'required_if:provider_type,sendgrid,mailgun|nullable|string|max:255',
            'api_secret' => 'required_if:provider_type,ses|nullable|string|max:255',
            'region' => 'required_if:provider_type,ses|nullable|string|max:255',
            'from_email' => 'required|email|max:255',
            'from_name' => 'nullable|string|max:255',
            'reply_to_email' => 'nullable|email|max:255',
            'status' => 'required|string|in:active,inactive',
            'is_default' => 'boolean',
            'daily_limit' => 'nullable|integer|min:1',
        ]);

        if (->fails()) {
            return redirect()->back()
                ->withErrors()
                ->withInput();
        }

        try {
            // 准备额外设置
             = ->settings ?: [];
            if (->provider_type === 'mailgun') {
                ['domain'] = ->input('mailgun_domain');
                ['endpoint'] = ->input('mailgun_endpoint', 'api.mailgun.net');
            }

            // 准备更新数据
             = [
                'name' => ->name,
                'host' => ->host,
                'port' => ->port,
                'username' => ->username,
                'encryption' => ->encryption,
                'api_key' => ->api_key,
                'api_secret' => ->api_secret,
                'region' => ->region,
                'from_email' => ->from_email,
                'from_name' => ->from_name,
                'reply_to_email' => ->reply_to_email,
                'status' => ->status,
                'is_default' => ->is_default ?? false,
                'daily_limit' => ->daily_limit,
                'settings' => ,
            ];

            // 如果提供了新密码，更新密码
            if (->filled('password')) {
                ['password'] = ->password;
            }

            // 更新邮件发送接口
            ->update();

            // 如果设置为默认接口
            if (->is_default) {
                ->setAsDefault();
            }

            return redirect()->route('admin.notification.email_provider.show', )
                ->with('success', '邮件发送接口更新成功');
        } catch (\Exception ) {
            return redirect()->back()
                ->with('error', '邮件发送接口更新失败: ' . ->getMessage())
                ->withInput();
        }
    }

    /**
     * 删除指定邮件发送接口
     *
     * @param EmailProvider 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(EmailProvider )
    {
        try {
            // 如果是默认接口，不允许删除
            if (->is_default) {
                return redirect()->back()
                    ->with('error', '不能删除默认邮件发送接口，请先设置其他接口为默认');
            }

            ->delete();
            return redirect()->route('admin.notification.email_provider.index')
                ->with('success', '邮件发送接口删除成功');
        } catch (\Exception ) {
            return redirect()->back()
                ->with('error', '邮件发送接口删除失败: ' . ->getMessage());
        }
    }

    /**
     * 设置默认邮件发送接口
     *
     * @param EmailProvider 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setDefault(EmailProvider )
    {
        try {
            // 只有活动状态的接口才能设为默认
            if (->status !== 'active') {
                return redirect()->back()
                    ->with('error', '只有活动状态的邮件发送接口才能设为默认');
            }

            ->setAsDefault();
            return redirect()->route('admin.notification.email_provider.index')
                ->with('success', '默认邮件发送接口设置成功');
        } catch (\Exception ) {
            return redirect()->back()
                ->with('error', '默认邮件发送接口设置失败: ' . ->getMessage());
        }
    }

    /**
     * 测试邮件发送接口
     *
     * @param Request 
     * @param EmailProvider 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function test(Request , EmailProvider )
    {
        // 验证请求数据
         = Validator::make(->all(), [
            'test_email' => 'required|email|max:255',
        ]);

        if (->fails()) {
            return redirect()->back()
                ->withErrors()
                ->withInput();
        }

        try {
            // 测试邮件发送接口
             = ->emailService->testEmailProvider(, ->test_email);

            if () {
                return redirect()->back()
                    ->with('success', '测试邮件发送成功，请检查收件箱');
            } else {
                return redirect()->back()
                    ->with('error', '测试邮件发送失败，请检查配置')
                    ->withInput();
            }
        } catch (\Exception ) {
            return redirect()->back()
                ->with('error', '测试邮件发送失败: ' . ->getMessage())
                ->withInput();
        }
    }
}
