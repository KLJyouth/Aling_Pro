<?php

namespace App\Services\News;

use App\Models\News\News;
use App\Models\News\NewsCategory;
use App\Models\News\NewsTag;
use App\Models\News\NewsComment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Exception;

/**
 * ���ŷ�����
 * 
 * �ṩ������ص�ҵ���߼�����
 */
class NewsService
{
    /**
     * ��������
     *
     * @param array $data ��������
     * @param int $authorId ����ID
     * @param UploadedFile|null $coverImage ����ͼƬ
     * @param array $tags ��ǩ����
     * @return News
     */
    public function createNews(array $data, int $authorId, ?UploadedFile $coverImage = null, array $tags = [])
    {
        DB::beginTransaction();
        
        try {
            // �������
            if (empty($data["slug"])) {
                $data["slug"] = Str::slug($data["title"]);
            }
            
            // ȷ������Ψһ
            $slug = $data["slug"];
            $count = 0;
            while (News::where("slug", $slug)->exists()) {
                $count++;
                $slug = $data["slug"] . "-" . $count;
            }
            $data["slug"] = $slug;
            
            // �������ͼƬ
            if ($coverImage) {
                $data["cover_image"] = $this->uploadCoverImage($coverImage);
            }
            
            // ��������
            $news = News::create(array_merge($data, [
                "author_id" => $authorId,
                "view_count" => 0,
            ]));
            
            // �����ǩ
            if (!empty($tags)) {
                $this->syncTags($news, $tags);
            }
            
            DB::commit();
            return $news;
        } catch (Exception $e) {
            DB::rollBack();
            
            // ����ϴ���ͼƬ������ʧ�ܣ�ɾ��ͼƬ
            if (isset($data["cover_image"]) && $data["cover_image"]) {
                Storage::disk("public")->delete($data["cover_image"]);
            }
            
            throw $e;
        }
    }
    
    /**
     * ��������
     *
     * @param int $newsId ����ID
     * @param array $data ��������
     * @param UploadedFile|null $coverImage ����ͼƬ
     * @param array $tags ��ǩ����
     * @return News
     */
    public function updateNews(int $newsId, array $data, ?UploadedFile $coverImage = null, array $tags = [])
    {
        DB::beginTransaction();
        
        try {
            $news = News::findOrFail($newsId);
            
            // �������
            if (isset($data["title"]) && (!isset($data["slug"]) || empty($data["slug"]))) {
                $data["slug"] = Str::slug($data["title"]);
            }
            
            // ȷ������Ψһ
            if (isset($data["slug"])) {
                $slug = $data["slug"];
                $count = 0;
                while (News::where("slug", $slug)->where("id", "!=", $newsId)->exists()) {
                    $count++;
                    $slug = $data["slug"] . "-" . $count;
                }
                $data["slug"] = $slug;
            }
            
            // �������ͼƬ
            if ($coverImage) {
                // ɾ����ͼƬ
                if ($news->cover_image) {
                    Storage::disk("public")->delete($news->cover_image);
                }
                
                $data["cover_image"] = $this->uploadCoverImage($coverImage);
            }
            
            // ��������
            $news->update($data);
            
            // �����ǩ
            if ($tags !== null) {
                $this->syncTags($news, $tags);
            }
            
            DB::commit();
            return $news;
        } catch (Exception $e) {
            DB::rollBack();
            
            // ����ϴ���ͼƬ������ʧ�ܣ�ɾ��ͼƬ
            if (isset($data["cover_image"]) && $data["cover_image"] && $data["cover_image"] !== $news->cover_image) {
                Storage::disk("public")->delete($data["cover_image"]);
            }
            
            throw $e;
        }
    }
    
    /**
     * ɾ������
     *
     * @param int $newsId ����ID
     * @param bool $forceDelete �Ƿ�ǿ��ɾ��
     * @return bool
     */
    public function deleteNews(int $newsId, bool $forceDelete = false)
    {
        $news = News::findOrFail($newsId);
        
        if ($forceDelete) {
            // ǿ��ɾ��
            if ($news->cover_image) {
                Storage::disk("public")->delete($news->cover_image);
            }
            
            $news->tags()->detach();
            $news->comments()->forceDelete();
            return $news->forceDelete();
        } else {
            // ��ɾ��
            return $news->delete();
        }
    }
    
    /**
     * �ϴ�����ͼƬ
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function uploadCoverImage(UploadedFile $file)
    {
        $path = $file->store("news/covers", "public");
        return $path;
    }
    
    /**
     * ͬ����ǩ
     *
     * @param News $news
     * @param array $tags
     * @return void
     */
    protected function syncTags(News $news, array $tags)
    {
        $tagIds = [];
        
        foreach ($tags as $tagName) {
            $tag = NewsTag::getOrCreate($tagName);
            $tagIds[] = $tag->id;
        }
        
        $news->tags()->sync($tagIds);
    }
    
    /**
     * �������
     *
     * @param int $newsId ����ID
     * @param array $data ��������
     * @param int|null $userId �û�ID
     * @return NewsComment
     */
    public function addComment(int $newsId, array $data, ?int $userId = null)
    {
        $news = News::findOrFail($newsId);
        
        $commentData = [
            "news_id" => $newsId,
            "content" => $data["content"],
            "parent_id" => $data["parent_id"] ?? null,
            "status" => "pending", // Ĭ�ϴ����
            "ip_address" => request()->ip(),
            "user_agent" => request()->userAgent(),
        ];
        
        // �����û���Ϣ
        if ($userId) {
            $commentData["user_id"] = $userId;
            $commentData["is_anonymous"] = $data["is_anonymous"] ?? false;
        } else {
            $commentData["is_anonymous"] = true;
            $commentData["author_name"] = $data["author_name"] ?? "�����û�";
            $commentData["author_email"] = $data["author_email"] ?? null;
        }
        
        return NewsComment::create($commentData);
    }
    
    /**
     * ��ȡ����ͳ����Ϣ
     *
     * @return array
     */
    public function getNewsStats()
    {
        $stats = [
            "total" => News::count(),
            "published" => News::where("status", "published")->count(),
            "draft" => News::where("status", "draft")->count(),
            "archived" => News::where("status", "archived")->count(),
            "featured" => News::where("featured", true)->count(),
            "categories" => NewsCategory::count(),
            "tags" => NewsTag::count(),
            "comments" => [
                "total" => NewsComment::count(),
                "pending" => NewsComment::where("status", "pending")->count(),
                "approved" => NewsComment::where("status", "approved")->count(),
                "rejected" => NewsComment::where("status", "rejected")->count(),
            ],
        ];
        
        return $stats;
    }
}
