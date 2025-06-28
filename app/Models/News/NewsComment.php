<?php

namespace App\Models\News;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

/**
 * ��������ģ��
 * 
 * ���ڹ�����������
 */
class NewsComment extends Model
{
    use SoftDeletes;

    /**
     * ��ģ�͹����ı���
     *
     * @var string
     */
    protected $table = 'news_comments';

    /**
     * ��������ֵ������
     *
     * @var array
     */
    protected $fillable = [
        'news_id',         // ����ID
        'user_id',         // �û�ID
        'parent_id',       // ������ID
        'content',         // ��������
        'status',          // ״̬��pending, approved, rejected
        'ip_address',      // IP��ַ
        'user_agent',      // �û�����
        'is_anonymous',    // �Ƿ�����
        'author_name',     // ������������
        'author_email',    // ������������
    ];

    /**
     * Ӧ�ñ�ת��Ϊ���ڵ�����
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Ӧ�ñ�ת��Ϊԭ�����͵�����
     *
     * @var array
     */
    protected $casts = [
        'is_anonymous' => 'boolean',
    ];

    /**
     * ��ȡ������������
     */
    public function news()
    {
        return $this->belongsTo(News::class, 'news_id');
    }

    /**
     * ��ȡ�����û�
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * ��ȡ������
     */
    public function parent()
    {
        return $this->belongsTo(NewsComment::class, 'parent_id');
    }

    /**
     * ��ȡ������
     */
    public function replies()
    {
        return $this->hasMany(NewsComment::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    /**
     * ��ȡ�������ƣ��������û�����
     *
     * @return string
     */
    public function getAuthorNameAttribute()
    {
        if ($this->is_anonymous) {
            return $this->attributes['author_name'] ?: '�����û�';
        }
        
        return $this->user ? $this->user->name : 'δ֪�û�';
    }

    /**
     * ���ͨ������
     *
     * @return bool
     */
    public function approve()
    {
        $this->status = 'approved';
        return $this->save();
    }

    /**
     * �ܾ�����
     *
     * @return bool
     */
    public function reject()
    {
        $this->status = 'rejected';
        return $this->save();
    }
}
