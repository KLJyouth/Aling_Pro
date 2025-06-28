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
 * 新闻服务类
 * 
 * 提供新闻相关的业务逻辑处理
 */
class NewsService
{
    /**
     * 创建新闻
     *
     * @param array $data 新闻数据
     * @param int $authorId 作者ID
     * @param UploadedFile|null $coverImage 封面图片
     * @param array $tags 标签数组
     * @return News
     */
    public function createNews(array $data, int $authorId, ?UploadedFile $coverImage = null, array $tags = [])
    {
        DB::beginTransaction();
        
        try {
            // 处理别名
            if (empty($data["slug"])) {
                $data["slug"] = Str::slug($data["title"]);
            }
            
            // 确保别名唯一
            $slug = $data["slug"];
            $count = 0;
            while (News::where("slug", $slug)->exists()) {
                $count++;
                $slug = $data["slug"] . "-" . $count;
            }
            $data["slug"] = $slug;
            
            // 处理封面图片
            if ($coverImage) {
                $data["cover_image"] = $this->uploadCoverImage($coverImage);
            }
            
            // 创建新闻
            $news = News::create(array_merge($data, [
                "author_id" => $authorId,
                "view_count" => 0,
            ]));
            
            // 处理标签
            if (!empty($tags)) {
                $this->syncTags($news, $tags);
            }
            
            DB::commit();
            return $news;
        } catch (Exception $e) {
            DB::rollBack();
            
            // 如果上传了图片但创建失败，删除图片
            if (isset($data["cover_image"]) && $data["cover_image"]) {
                Storage::disk("public")->delete($data["cover_image"]);
            }
            
            throw $e;
        }
    }
    
    /**
     * 更新新闻
     *
     * @param int $newsId 新闻ID
     * @param array $data 更新数据
     * @param UploadedFile|null $coverImage 封面图片
     * @param array $tags 标签数组
     * @return News
     */
    public function updateNews(int $newsId, array $data, ?UploadedFile $coverImage = null, array $tags = [])
    {
        DB::beginTransaction();
        
        try {
            $news = News::findOrFail($newsId);
            
            // 处理别名
            if (isset($data["title"]) && (!isset($data["slug"]) || empty($data["slug"]))) {
                $data["slug"] = Str::slug($data["title"]);
            }
            
            // 确保别名唯一
            if (isset($data["slug"])) {
                $slug = $data["slug"];
                $count = 0;
                while (News::where("slug", $slug)->where("id", "!=", $newsId)->exists()) {
                    $count++;
                    $slug = $data["slug"] . "-" . $count;
                }
                $data["slug"] = $slug;
            }
            
            // 处理封面图片
            if ($coverImage) {
                // 删除旧图片
                if ($news->cover_image) {
                    Storage::disk("public")->delete($news->cover_image);
                }
                
                $data["cover_image"] = $this->uploadCoverImage($coverImage);
            }
            
            // 更新新闻
            $news->update($data);
            
            // 处理标签
            if ($tags !== null) {
                $this->syncTags($news, $tags);
            }
            
            DB::commit();
            return $news;
        } catch (Exception $e) {
            DB::rollBack();
            
            // 如果上传了图片但更新失败，删除图片
            if (isset($data["cover_image"]) && $data["cover_image"] && $data["cover_image"] !== $news->cover_image) {
                Storage::disk("public")->delete($data["cover_image"]);
            }
            
            throw $e;
        }
    }
    
    /**
     * 删除新闻
     *
     * @param int $newsId 新闻ID
     * @param bool $forceDelete 是否强制删除
     * @return bool
     */
    public function deleteNews(int $newsId, bool $forceDelete = false)
    {
        $news = News::findOrFail($newsId);
        
        if ($forceDelete) {
            // 强制删除
            if ($news->cover_image) {
                Storage::disk("public")->delete($news->cover_image);
            }
            
            $news->tags()->detach();
            $news->comments()->forceDelete();
            return $news->forceDelete();
        } else {
            // 软删除
            return $news->delete();
        }
    }
    
    /**
     * 上传封面图片
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
     * 同步标签
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
     * 添加评论
     *
     * @param int $newsId 新闻ID
     * @param array $data 评论数据
     * @param int|null $userId 用户ID
     * @return NewsComment
     */
    public function addComment(int $newsId, array $data, ?int $userId = null)
    {
        $news = News::findOrFail($newsId);
        
        $commentData = [
            "news_id" => $newsId,
            "content" => $data["content"],
            "parent_id" => $data["parent_id"] ?? null,
            "status" => "pending", // 默认待审核
            "ip_address" => request()->ip(),
            "user_agent" => request()->userAgent(),
        ];
        
        // 处理用户信息
        if ($userId) {
            $commentData["user_id"] = $userId;
            $commentData["is_anonymous"] = $data["is_anonymous"] ?? false;
        } else {
            $commentData["is_anonymous"] = true;
            $commentData["author_name"] = $data["author_name"] ?? "匿名用户";
            $commentData["author_email"] = $data["author_email"] ?? null;
        }
        
        return NewsComment::create($commentData);
    }
    
    /**
     * 获取新闻统计信息
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
