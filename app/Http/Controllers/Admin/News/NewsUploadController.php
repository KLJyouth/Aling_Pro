<?php

namespace App\Http\Controllers\Admin\News;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

/**
 * 新闻图片上传控制器
 * 
 * 处理新闻相关的图片上传功能
 */
class NewsUploadController extends Controller
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin,editor');
    }
    
    /**
     * 上传富文本编辑器中的图片
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImage(Request $request)
    {
        // 验证请求
        $request->validate([
            'image' => 'required|image|max:5120', // 最大5MB
        ]);
        
        try {
            // 获取图片文件
            $image = $request->file('image');
            
            // 生成唯一文件名
            $filename = date('YmdHis') . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            
            // 保存原始图片
            $path = 'uploads/news/content/' . date('Ym') . '/';
            $image->storeAs('public/' . $path, $filename);
            
            // 生成缩略图（如果需要）
            $this->createThumbnail($image, $path, $filename);
            
            // 返回图片URL
            $url = asset('storage/' . $path . $filename);
            
            return response()->json([
                'success' => true,
                'url' => $url,
                'path' => $path . $filename,
                'filename' => $filename
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '图片上传失败: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 上传新闻封面图片
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadCover(Request $request)
    {
        // 验证请求
        $request->validate([
            'cover' => 'required|image|max:5120', // 最大5MB
        ]);
        
        try {
            // 获取图片文件
            $image = $request->file('cover');
            
            // 生成唯一文件名
            $filename = date('YmdHis') . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            
            // 保存原始图片
            $path = 'uploads/news/covers/' . date('Ym') . '/';
            $image->storeAs('public/' . $path, $filename);
            
            // 生成不同尺寸的封面图
            $this->createCoverSizes($image, $path, $filename);
            
            // 返回图片URL
            $url = asset('storage/' . $path . $filename);
            
            return response()->json([
                'success' => true,
                'url' => $url,
                'path' => $path . $filename,
                'filename' => $filename
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '封面上传失败: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 创建内容图片缩略图
     *
     * @param \Illuminate\Http\UploadedFile $image
     * @param string $path
     * @param string $filename
     * @return void
     */
    protected function createThumbnail($image, $path, $filename)
    {
        // 使用Intervention/Image处理图片
        $img = Image::make($image->getRealPath());
        
        // 创建缩略图目录
        $thumbPath = 'public/' . $path . 'thumbs/';
        if (!Storage::exists($thumbPath)) {
            Storage::makeDirectory($thumbPath);
        }
        
        // 生成缩略图（最大宽度800px）
        $img->resize(800, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        
        // 保存缩略图
        $img->save(storage_path('app/' . $thumbPath . $filename), 80);
    }
    
    /**
     * 创建不同尺寸的封面图
     *
     * @param \Illuminate\Http\UploadedFile $image
     * @param string $path
     * @param string $filename
     * @return void
     */
    protected function createCoverSizes($image, $path, $filename)
    {
        // 使用Intervention/Image处理图片
        $img = Image::make($image->getRealPath());
        
        // 定义不同尺寸
        $sizes = [
            'small' => [300, 200],
            'medium' => [600, 400],
            'large' => [1200, 800]
        ];
        
        foreach ($sizes as $size => $dimensions) {
            // 创建尺寸目录
            $sizePath = 'public/' . $path . $size . '/';
            if (!Storage::exists($sizePath)) {
                Storage::makeDirectory($sizePath);
            }
            
            // 裁剪并调整图片大小
            $sizedImg = Image::make($image->getRealPath());
            
            // 保持宽高比裁剪
            $width = $dimensions[0];
            $height = $dimensions[1];
            
            // 先调整大小保持宽高比
            $sizedImg->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
            // 然后从中心裁剪到目标尺寸
            if ($sizedImg->width() > $width || $sizedImg->height() > $height) {
                $sizedImg->crop($width, $height);
            }
            
            // 保存调整后的图片
            $sizedImg->save(storage_path('app/' . $sizePath . $filename), 80);
        }
    }
    
    /**
     * 从富文本内容中提取图片
     *
     * @param string $content
     * @return array
     */
    public function extractImagesFromContent($content)
    {
        $images = [];
        $pattern = '/<img[^>]+src=([\'"])(.*?)\1[^>]*>/i';
        
        if (preg_match_all($pattern, $content, $matches)) {
            foreach ($matches[2] as $match) {
                if (Str::contains($match, 'storage/uploads/news/content')) {
                    // 提取相对路径
                    $path = Str::after($match, 'storage/');
                    $images[] = $path;
                }
            }
        }
        
        return $images;
    }
    
    /**
     * 检查并清理未使用的图片
     *
     * @return void
     */
    public function cleanUnusedImages()
    {
        // 此方法可以通过计划任务定期执行
        // 查找所有已上传但未关联到任何新闻的图片
        // 并删除它们以释放存储空间
    }
}