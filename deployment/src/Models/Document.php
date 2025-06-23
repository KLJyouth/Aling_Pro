<?php

declare(strict_types=1);

namespace AlingAi\Models;

use AlingAi\Models\BaseModel;
use AlingAi\Models\User;

/**
 * 文档模型
 * 
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $content
 * @property string|null $description
 * @property string $type
 * @property string $format
 * @property string $filename
 * @property string|null $file_path
 * @property int $file_size
 * @property string $mime_type
 * @property string $hash
 * @property string $status
 * @property string $version
 * @property array $tags
 * @property array $metadata
 * @property array $settings
 * @property int $view_count * @property int $download_count
 * @property bool $is_public
 * @property bool $is_encrypted
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * 
 * @property-read User $user
 */
class Document extends BaseModel
{
    protected $table = 'documents';

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'description',
        'type',
        'format',
        'filename',
        'file_path',
        'file_size',
        'mime_type',
        'hash',
        'status',
        'version',
        'tags',
        'metadata',
        'settings',
        'view_count',
        'download_count',
        'is_public',
        'is_encrypted'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'file_size' => 'integer',
        'view_count' => 'integer',
        'download_count' => 'integer',
        'is_public' => 'boolean',
        'is_encrypted' => 'boolean',
        'tags' => 'array',
        'metadata' => 'array',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $hidden = [
        'content', // 在列表中隐藏内容，只在详情中显示
    ];

    protected $attributes = [
        'type' => 'text',
        'format' => 'plain',
        'status' => 'active',
        'version' => '1.0',
        'view_count' => 0,
        'download_count' => 0,
        'is_public' => false,
        'is_encrypted' => false,
        'tags' => '[]',
        'metadata' => '[]',
        'settings' => '[]'
    ];    /**
     * 获取关联的用户
     */
    public function user(): ?User
    {
        return User::find($this->user_id);
    }

    /**
     * 获取格式化的文件大小
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes >= 1024 && $i < 4; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * 获取文档摘要
     */
    public function getExcerptAttribute(): string
    {
        return mb_substr(strip_tags($this->content), 0, 200) . '...';
    }

    /**
     * 检查是否为图片文档
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * 检查是否为文本文档
     */
    public function isText(): bool
    {
        return str_starts_with($this->mime_type, 'text/') || 
               in_array($this->mime_type, [
                   'application/json',
                   'application/xml',
                   'application/javascript'
               ]);
    }

    /**
     * 检查是否为PDF文档
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * 检查是否为Office文档
     */
    public function isOffice(): bool
    {
        return in_array($this->mime_type, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        ]);
    }

    /**
     * 检查用户是否可以访问此文档
     */
    public function canAccess(?User $user = null): bool
    {
        // 公开文档所有人都可以访问
        if ($this->is_public) {
            return true;
        }

        // 未登录用户无法访问私有文档
        if (!$user) {
            return false;
        }

        // 管理员可以访问所有文档
        if ($user->role === 'admin') {
            return true;
        }

        // 文档所有者可以访问
        return $this->user_id === $user->id;
    }

    /**
     * 检查用户是否可以编辑此文档
     */
    public function canEdit(?User $user = null): bool
    {
        if (!$user) {
            return false;
        }

        // 管理员可以编辑所有文档
        if ($user->role === 'admin') {
            return true;
        }

        // 文档所有者可以编辑
        return $this->user_id === $user->id;
    }

    /**
     * 检查用户是否可以删除此文档
     */
    public function canDelete(?User $user = null): bool
    {
        return $this->canEdit($user);
    }

    /**
     * 按类型过滤
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * 按状态过滤
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * 只显示公开文档
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * 只显示私有文档
     */
    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    /**
     * 按用户过滤
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * 搜索文档
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('title', 'LIKE', "%{$search}%")
              ->orWhere('content', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%");
        });
    }

    /**
     * 按标签过滤
     */
    public function scopeByTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * 最受欢迎的文档
     */
    public function scopePopular($query, int $limit = 10)
    {
        return $query->orderBy('view_count', 'desc')->limit($limit);
    }

    /**
     * 最新文档
     */
    public function scopeLatest($query, int $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * 最近更新的文档
     */
    public function scopeRecentlyUpdated($query, int $limit = 10)
    {
        return $query->orderBy('updated_at', 'desc')->limit($limit);
    }    /**
     * 文档统计信息
     */
    public static function getStatistics(): array
    {
        $total = static::count();
        
        // 按类型统计
        $byTypeResults = static::query()
                        ->selectRaw('type, COUNT(*) as count')
                        ->groupBy('type')
                        ->get();
        $byType = [];
        foreach ($byTypeResults as $row) {
            $byType[$row->type] = $row->count;
        }
        
        // 按状态统计
        $byStatusResults = static::query()
                          ->selectRaw('status, COUNT(*) as count')
                          ->groupBy('status')
                          ->get();
        $byStatus = [];
        foreach ($byStatusResults as $row) {
            $byStatus[$row->status] = $row->count;
        }

        $today = date('Y-m-d');
        $thisWeekStart = date('Y-m-d', strtotime('monday this week'));
        $thisWeekEnd = date('Y-m-d', strtotime('sunday this week'));
        $thisMonth = date('Y-m');

        return [
            'total' => $total,
            'public' => static::where('is_public', true)->count(),
            'private' => static::where('is_public', false)->count(),
            'encrypted' => static::where('is_encrypted', true)->count(),
            'total_size' => static::query()->sum('file_size'),
            'total_views' => static::query()->sum('view_count'),
            'total_downloads' => static::query()->sum('download_count'),
            'by_type' => $byType,
            'by_status' => $byStatus,
            'today' => static::where('created_at', '>=', $today . ' 00:00:00')
                            ->where('created_at', '<=', $today . ' 23:59:59')
                            ->count(),
            'this_week' => static::where('created_at', '>=', $thisWeekStart . ' 00:00:00')
                                ->where('created_at', '<=', $thisWeekEnd . ' 23:59:59')
                                ->count(),
            'this_month' => static::where('created_at', '>=', $thisMonth . '-01 00:00:00')
                                 ->where('created_at', '<=', $thisMonth . '-31 23:59:59')
                                 ->count(),
        ];    }
}
