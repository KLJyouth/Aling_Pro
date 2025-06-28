@extends('layouts.news')

@section('title', $news->title)
@section('meta_description', $news->meta_description ?? $news->summary)
@section('meta_keywords', $news->meta_keywords)
@section('og_title', $news->title)
@section('og_description', $news->meta_description ?? $news->summary)
@section('og_image', $news->cover_image ? asset($news->cover_image) : asset('assets/images/news/default-cover.jpg'))

@section('structured_data')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "NewsArticle",
  "headline": "{{ $news->title }}",
  "description": "{{ $news->summary }}",
  "image": "{{ $news->cover_image ? asset($news->cover_image) : asset('assets/images/news/default-cover.jpg') }}",
  "datePublished": "{{ $news->published_at->toIso8601String() }}",
  "dateModified": "{{ $news->updated_at->toIso8601String() }}",
  "author": {
    "@type": "Person",
    "name": "{{ $news->author->name }}"
  },
  "publisher": {
    "@type": "Organization",
    "name": "AlingAi",
    "logo": {
      "@type": "ImageObject",
      "url": "{{ asset('assets/images/logo.png') }}"
    }
  },
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "{{ url()->current() }}"
  }
}
</script>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/prism.css') }}">
@endsection

@section('content')
<div class="row">
    <!-- 主内容区 -->
    <div class="col-lg-8">
        <!-- 面包屑导航 -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('news.index') }}">新闻中心</a></li>
                <li class="breadcrumb-item"><a href="{{ route('news.category', $news->category->slug) }}">{{ $news->category->name }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $news->title }}</li>
            </ol>
        </nav>

        <!-- 文章头部 -->
        <div class="news-header mb-4">
            <h1 class="news-title">{{ $news->title }}</h1>
            <div class="news-meta">
                <span><i class="fas fa-user"></i> {{ $news->author->name }}</span>
                <span><i class="fas fa-calendar"></i> {{ $news->published_at->format('Y-m-d H:i') }}</span>
                <span><i class="fas fa-folder"></i> <a href="{{ route('news.category', $news->category->slug) }}">{{ $news->category->name }}</a></span>
                <span><i class="fas fa-eye"></i> {{ $news->view_count }} 阅读</span>
            </div>
        </div>

        <!-- 封面图 -->
        @if($news->cover_image)
        <div class="news-cover mb-4">
            <img src="{{ asset($news->cover_image) }}" alt="{{ $news->title }}" class="img-fluid rounded">
        </div>
        @endif

        <!-- 文章摘要 -->
        @if($news->summary)
        <div class="news-summary mb-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h5 class="card-title">摘要</h5>
                    <p class="card-text">{{ $news->summary }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- 文章内容 -->
        <div class="news-content mb-5">
            <div class="content-body">
                {!! $news->content !!}
            </div>
        </div>

        <!-- 文章标签 -->
        <div class="news-tags mb-4">
            <h5>标签：</h5>
            <div class="tags-list">
                @foreach($news->tags as $tag)
                <a href="{{ route('news.tag', $tag->slug) }}" class="badge bg-secondary text-decoration-none">{{ $tag->name }}</a>
                @endforeach
            </div>
        </div>

        <!-- 分享按钮 -->
        <div class="news-share mb-5">
            <h5>分享：</h5>
            <div class="share-buttons">
                <a href="https://www.weibo.com/share/share.php?url={{ urlencode(url()->current()) }}&title={{ urlencode($news->title) }}" target="_blank" class="btn btn-sm btn-danger"><i class="fab fa-weibo"></i> 微博</a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" class="btn btn-sm btn-primary"><i class="fab fa-facebook-f"></i> Facebook</a>
                <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($news->title) }}" target="_blank" class="btn btn-sm btn-info"><i class="fab fa-twitter"></i> Twitter</a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(url()->current()) }}&title={{ urlencode($news->title) }}&summary={{ urlencode($news->summary) }}" target="_blank" class="btn btn-sm btn-secondary"><i class="fab fa-linkedin-in"></i> LinkedIn</a>
                <button class="btn btn-sm btn-success copy-link" data-url="{{ url()->current() }}"><i class="fas fa-link"></i> 复制链接</button>
            </div>
        </div>

        <!-- 评论区 -->
        <div class="news-comments mb-5">
            <h3 class="comments-title mb-4">评论 ({{ $comments->count() }})</h3>
            
            <!-- 评论表单 -->
            <div class="comment-form card mb-4">
                <div class="card-body">
                    <h5 class="card-title">发表评论</h5>
                    <form action="{{ route('news.comment', $news->slug) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <textarea class="form-control" name="content" rows="4" placeholder="请输入您的评论..." required></textarea>
                        </div>
                        @guest
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="text" class="form-control" name="author_name" placeholder="您的昵称" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="email" class="form-control" name="author_email" placeholder="您的邮箱" required>
                            </div>
                        </div>
                        @endguest
                        <button type="submit" class="btn btn-primary">提交评论</button>
                    </form>
                </div>
            </div>

            <!-- 评论列表 -->
            @if($comments->count() > 0)
                <div class="comments-list">
                    @foreach($comments as $comment)
                    <div class="comment-item card mb-3">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <img src="{{ $comment->user ? asset($comment->user->avatar ?? 'assets/images/default-avatar.png') : asset('assets/images/default-avatar.png') }}" alt="用户头像" class="rounded-circle" width="50" height="50">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">{{ $comment->user ? $comment->user->name : $comment->author_name }}</h6>
                                        <small class="text-muted">{{ $comment->created_at->format('Y-m-d H:i') }}</small>
                                    </div>
                                    <p class="mt-2">{{ $comment->content }}</p>
                                    
                                    <!-- 回复按钮 -->
                                    <button class="btn btn-sm btn-outline-secondary reply-btn" data-comment-id="{{ $comment->id }}">回复</button>
                                    
                                    <!-- 回复表单 -->
                                    <div class="reply-form mt-3 d-none" id="reply-form-{{ $comment->id }}">
                                        <form action="{{ route('news.comment', $news->slug) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                            <div class="mb-3">
                                                <textarea class="form-control" name="content" rows="2" placeholder="回复 {{ $comment->user ? $comment->user->name : $comment->author_name }}..." required></textarea>
                                            </div>
                                            @guest
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <input type="text" class="form-control" name="author_name" placeholder="您的昵称" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <input type="email" class="form-control" name="author_email" placeholder="您的邮箱" required>
                                                </div>
                                            </div>
                                            @endguest
                                            <button type="submit" class="btn btn-sm btn-primary">提交回复</button>
                                            <button type="button" class="btn btn-sm btn-secondary cancel-reply" data-comment-id="{{ $comment->id }}">取消</button>
                                        </form>
                                    </div>
                                    
                                    <!-- 回复列表 -->
                                    @if($comment->replies->count() > 0)
                                    <div class="replies-list mt-3">
                                        @foreach($comment->replies as $reply)
                                        <div class="reply-item card mt-2">
                                            <div class="card-body py-2">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0">
                                                        <img src="{{ $reply->user ? asset($reply->user->avatar ?? 'assets/images/default-avatar.png') : asset('assets/images/default-avatar.png') }}" alt="用户头像" class="rounded-circle" width="30" height="30">
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <h6 class="mb-0 small">{{ $reply->user ? $reply->user->name : $reply->author_name }}</h6>
                                                            <small class="text-muted">{{ $reply->created_at->format('Y-m-d H:i') }}</small>
                                                        </div>
                                                        <p class="mt-1 mb-0 small">{{ $reply->content }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info">
                    暂无评论，成为第一个评论的人吧！
                </div>
            @endif
        </div>
    </div>

    <!-- 侧边栏 -->
    <div class="col-lg-4">
        <!-- 作者信息 -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">作者信息</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <img src="{{ asset($news->author->avatar ?? 'assets/images/default-avatar.png') }}" alt="{{ $news->author->name }}" class="rounded-circle" width="60" height="60">
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">{{ $news->author->name }}</h5>
                        <p class="mb-0">{{ $news->author->bio ?? '这个作者很懒，还没有填写个人简介' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 相关文章 -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">相关文章</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @foreach($relatedNews as $related)
                    <li class="list-group-item px-0">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <img src="{{ $related->cover_image ? asset($related->cover_image) : asset('assets/images/news/default-cover.jpg') }}" alt="{{ $related->title }}" width="60" height="60" class="rounded">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0"><a href="{{ route('news.show', $related->slug) }}" class="text-decoration-none">{{ $related->title }}</a></h6>
                                <small class="text-muted">{{ $related->published_at->format('Y-m-d') }} | {{ $related->view_count }} 阅读</small>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- 热门标签 -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">热门标签</h5>
            </div>
            <div class="card-body">
                <div class="tags-cloud">
                    @foreach(\App\Models\News\NewsTag::getActiveTags() as $tag)
                    <a href="{{ route('news.tag', $tag->slug) }}" class="tag-item" style="font-size: {{ 0.8 + min($tag->news_count / 10, 1.2) }}rem">{{ $tag->name }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/prism.js') }}"></script>
<script>
    $(document).ready(function() {
        // 代码高亮
        Prism.highlightAll();
        
        // 回复功能
        $('.reply-btn').click(function() {
            var commentId = $(this).data('comment-id');
            $('#reply-form-' + commentId).removeClass('d-none');
        });
        
        $('.cancel-reply').click(function() {
            var commentId = $(this).data('comment-id');
            $('#reply-form-' + commentId).addClass('d-none');
        });
        
        // 复制链接功能
        $('.copy-link').click(function() {
            var url = $(this).data('url');
            var tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(url).select();
            document.execCommand('copy');
            tempInput.remove();
            
            alert('链接已复制到剪贴板！');
        });
    });
</script>
@endsection