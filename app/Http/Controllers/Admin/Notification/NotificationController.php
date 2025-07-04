<?php

namespace App\Http\Controllers\Admin\Notification;

use App\Http\Controllers\Controller;
use App\Models\Notification\Notification;
use App\Models\Notification\NotificationTemplate;
use App\Models\Notification\NotificationRecipient;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * 通知管理控制器
 */
class NotificationController extends Controller
{
    /**
     * 通知服务实例
     *
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * 构造函数
     *
     * @param NotificationService $notificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * 显示通知列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Notification::query();

        // 筛选条件
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%$search%")
                  ->orWhere('content', 'like', "%$search%");
            });
        }

        // 排序
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // 分页
        $notifications = $query->with(['sender', 'template'])
            ->withCount('recipients')
            ->paginate(15)
            ->appends($request->all());

        return view('admin.notification.index', [
            'notifications' => $notifications,
            'types' => [
                'system' => '系统通知',
                'user' => '用户通知',
                'email' => '邮件通知',
                'api' => 'API通知',
            ],
            'statuses' => [
                'draft' => '草稿',
                'sending' => '发送中',
                'sent' => '已发送',
                'failed' => '发送失败',
            ],
            'priorities' => [
                'low' => '低',
                'normal' => '普通',
                'high' => '高',
                'urgent' => '紧急',
            ],
        ]);
    }

    /**
     * 显示创建通知表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $templates = NotificationTemplate::active()->get();
        
        return view('admin.notification.create', [
            'templates' => $templates,
            'types' => [
                'system' => '系统通知',
                'user' => '用户通知',
                'email' => '邮件通知',
                'api' => 'API通知',
            ],
            'priorities' => [
                'low' => '低',
                'normal' => '普通',
                'high' => '高',
                'urgent' => '紧急',
            ],
        ]);
    }

    /**
     * 存储新创建的通知
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 验证请求数据
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|string|in:system,user,email,api',
            'priority' => 'required|string|in:low,normal,high,urgent',
            'template_id' => 'nullable|exists:notification_templates,id',
            'scheduled_at' => 'nullable|date',
            'recipients' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // 开始事务
            DB::beginTransaction();

            // 准备通知数据
            $notificationData = [
                'title' => $request->title,
                'content' => $request->content,
                'type' => $request->type,
                'priority' => $request->priority,
                'sender_id' => auth()->id(),
                'template_id' => $request->template_id,
                'scheduled_at' => $request->scheduled_at,
                'status' => $request->has('send_now') ? 'draft' : 'draft',
            ];

            // 准备接收者数据
            $recipientsData = [];
            foreach ($request->recipients as $recipient) {
                $recipientData = [];

                if ($request->type === 'system' || $request->type === 'user') {
                    // 系统通知和用户通知需要用户ID
                    $recipientData['user_id'] = $recipient;
                } elseif ($request->type === 'email') {
                    // 邮件通知需要邮箱地址
                    $recipientData['email'] = $recipient;
                } elseif ($request->type === 'api') {
                    // API通知需要API端点
                    $recipientData['api_endpoint'] = $recipient;
                }

                $recipientsData[] = $recipientData;
            }

            $notificationData['recipients'] = $recipientsData;

            // 如果有附件，添加附件数据
            if ($request->hasFile('attachments')) {
                $attachmentsData = [];
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('attachments/notifications', 'public');
                    $attachmentsData[] = [
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => 'storage/' . $path,
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getMimeType(),
                    ];
                }
                $notificationData['attachments'] = $attachmentsData;
            }

            // 创建通知
            $notification = $this->notificationService->createNotification($notificationData);

            // 如果选择了立即发送
            if ($request->has('send_now')) {
                $this->notificationService->sendNotification($notification);
            }

            // 提交事务
            DB::commit();

            return redirect()->route('admin.notification.index')
                ->with('success', '通知创建成功' . ($request->has('send_now') ? '并已发送' : ''));
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();

            return redirect()->back()
                ->with('error', '通知创建失败: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * 显示指定通知
     *
     * @param Notification $notification
     * @return \Illuminate\View\View
     */
    public function show(Notification $notification)
    {
        $notification->load(['sender', 'template', 'recipients', 'attachments']);

        return view('admin.notification.show', [
            'notification' => $notification,
            'types' => [
                'system' => '系统通知',
                'user' => '用户通知',
                'email' => '邮件通知',
                'api' => 'API通知',
            ],
            'statuses' => [
                'draft' => '草稿',
                'sending' => '发送中',
                'sent' => '已发送',
                'failed' => '发送失败',
            ],
            'priorities' => [
                'low' => '低',
                'normal' => '普通',
                'high' => '高',
                'urgent' => '紧急',
            ],
        ]);
    }

    /**
     * 显示编辑通知表单
     *
     * @param Notification $notification
     * @return \Illuminate\View\View
     */
    public function edit(Notification $notification)
    {
        // 只能编辑草稿状态的通知
        if ($notification->status !== 'draft') {
            return redirect()->route('admin.notification.show', $notification)
                ->with('error', '只能编辑草稿状态的通知');
        }

        $notification->load(['recipients', 'attachments']);
        $templates = NotificationTemplate::active()->get();

        return view('admin.notification.edit', [
            'notification' => $notification,
            'templates' => $templates,
            'types' => [
                'system' => '系统通知',
                'user' => '用户通知',
                'email' => '邮件通知',
                'api' => 'API通知',
            ],
            'priorities' => [
                'low' => '低',
                'normal' => '普通',
                'high' => '高',
                'urgent' => '紧急',
            ],
        ]);
    }

    /**
     * 更新指定通知
     *
     * @param Request $request
     * @param Notification $notification
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Notification $notification)
    {
        // 只能更新草稿状态的通知
        if ($notification->status !== 'draft') {
            return redirect()->route('admin.notification.show', $notification)
                ->with('error', '只能更新草稿状态的通知');
        }

        // 验证请求数据
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'required|string|in:low,normal,high,urgent',
            'template_id' => 'nullable|exists:notification_templates,id',
            'scheduled_at' => 'nullable|date',
            'recipients' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // 开始事务
            DB::beginTransaction();

            // 更新通知
            $notification->update([
                'title' => $request->title,
                'content' => $request->content,
                'priority' => $request->priority,
                'template_id' => $request->template_id,
                'scheduled_at' => $request->scheduled_at,
            ]);

            // 删除旧的接收者
            $notification->recipients()->delete();

            // 添加新的接收者
            $recipientsData = [];
            foreach ($request->recipients as $recipient) {
                $recipientsData[] = [
                    'notification_id' => $notification->id,
                    'status' => 'pending',
                ];

                if ($request->type === 'system' || $request->type === 'user') {
                    // 系统通知和用户通知需要用户ID
                    $recipientsData[count($recipientsData) - 1]['user_id'] = $recipient;
                } elseif ($request->type === 'email') {
                    // 邮件通知需要邮箱地址
                    $recipientsData[count($recipientsData) - 1]['email'] = $recipient;
                } elseif ($request->type === 'api') {
                    // API通知需要API端点
                    $recipientsData[count($recipientsData) - 1]['api_endpoint'] = $recipient;
                }
            }

            NotificationRecipient::insert($recipientsData);

            // 如果有新的附件，添加附件
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('attachments/notifications', 'public');
                    $notification->attachments()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => 'storage/' . $path,
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getMimeType(),
                    ]);
                }
            }

            // 如果选择了立即发送
            if ($request->has('send_now')) {
                $this->notificationService->sendNotification($notification);
            }

            // 提交事务
            DB::commit();

            return redirect()->route('admin.notification.show', $notification)
                ->with('success', '通知更新成功' . ($request->has('send_now') ? '并已发送' : ''));
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();

            return redirect()->back()
                ->with('error', '通知更新失败: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * 删除指定通知
     *
     * @param Notification $notification
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Notification $notification)
    {
        try {
            $notification->delete();
            return redirect()->route('admin.notification.index')
                ->with('success', '通知删除成功');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', '通知删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 发送指定通知
     *
     * @param Notification $notification
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(Notification $notification)
    {
        // 只能发送草稿状态的通知
        if ($notification->status !== 'draft') {
            return redirect()->route('admin.notification.show', $notification)
                ->with('error', '只能发送草稿状态的通知');
        }

        try {
            $result = $this->notificationService->sendNotification($notification);

            if ($result) {
                return redirect()->route('admin.notification.show', $notification)
                    ->with('success', '通知发送成功');
            } else {
                return redirect()->route('admin.notification.show', $notification)
                    ->with('error', '通知发送失败，请查看详情');
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.notification.show', $notification)
                ->with('error', '通知发送失败: ' . $e->getMessage());
        }
    }

    /**
     * 复制指定通知
     *
     * @param Notification $notification
     * @return \Illuminate\Http\RedirectResponse
     */
    public function duplicate(Notification $notification)
    {
        try {
            // 加载关联数据
            $notification->load(['recipients', 'attachments']);

            // 创建新通知数据
            $newNotificationData = $notification->toArray();
            unset($newNotificationData['id'], $newNotificationData['created_at'], $newNotificationData['updated_at']);
            $newNotificationData['title'] = '复制: ' . $notification['title'];
            $newNotificationData['status'] = 'draft';
            $newNotificationData['sent_at'] = null;

            // 准备接收者数据
            $newRecipientsData = [];
            foreach ($notification['recipients'] as $recipient) {
                $newRecipientsData[] = [
                    'user_id' => $recipient['user_id'],
                    'email' => $recipient['email'],
                    'phone' => $recipient['phone'],
                    'api_endpoint' => $recipient['api_endpoint'],
                ];
            }
            $newNotificationData['recipients'] = $newRecipientsData;

            // 准备附件数据
            $newAttachmentsData = [];
            foreach ($notification['attachments'] as $attachment) {
                $newAttachmentsData[] = [
                    'file_name' => $attachment['file_name'],
                    'file_path' => $attachment['file_path'],
                    'file_size' => $attachment['file_size'],
                    'file_type' => $attachment['file_type'],
                    'description' => $attachment['description'],
                ];
            }
            $newNotificationData['attachments'] = $newAttachmentsData;

            // 创建新通知
            $newNotification = $this->notificationService->createNotification($newNotificationData);

            return redirect()->route('admin.notification.edit', $newNotification)
                ->with('success', '通知复制成功，请编辑后发送');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', '通知复制失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取用户列表（用于AJAX请求）
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsers(Request $request)
    {
        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $perPage = 10;

        $users = User::query();

        if (!empty($search)) {
            $users->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        $users = $users->select('id', 'name', 'email')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'results' => $users->items(),
            'pagination' => [
                'more' => $users->hasMorePages(),
            ],
        ]);
    }
}
