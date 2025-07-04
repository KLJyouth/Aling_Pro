@extends("layouts.news")

@section("title", $tag->name . " - 新闻标签")
@section("meta_description", $tag->description ?? $tag->name . "标签下的所有新闻文章")
@section("meta_keywords", $tag->name . ",新闻标签,AlingAi")

@section("content")
<div class="row">
    <!-- 主内容区 -->
    <div class="col-lg-8">
        <!-- 标签信息 -->
        <div class="tag-header mb-4">
            <h1 class="tag-title">
                <span class="badge bg-primary">{{ $tag->name }}</span> 标签下的文章
            </h1>
            @if($tag->description)
            <div class="tag-description mt-2">
                {{ $tag->description }}
            </div>
            @endif
        </div>

        <!-- 筛选和排序 -->
        <div class="news-filters mb-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2>标签文章</h2>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="btn-group">
                        <a href="{{ request()->fullUrlWithQuery([\"sort\" => \"latest\"]) }}" class="btn btn-sm {{ request()->input(\"sort\", \"latest\") == \"latest\" ? \"btn-primary\" : \"btn-outline-primary\" }}">最新</a>
                        <a href="{{ request()->fullUrlWithQuery([\"sort\" => \"popular\"]) }}" class="btn btn-sm {{ request()->input(\"sort\") == \"popular\" ? \"btn-primary\" : \"btn-outline-primary\" }}">热门</a>
                        <a href="{{ request()->fullUrlWithQuery([\"sort\" => \"oldest\"]) }}" class="btn btn-sm {{ request()->input(\"sort\") == \"oldest\" ? \"btn-primary\" : \"btn-outline-primary\" }}">最早</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 新闻列表 -->
        <div class="news-list">
            @if($news->count() > 0)
                @foreach($news as $item)
                <div class="news-item card mb-4">
                    <div class="row g-0">
                        <div class="col-md-4">
                            <a href="{{ route(\"news.show\", $item->slug) }}">
                                <img src="{{ $item->cover_image ? asset($item->cover_image) : asset(\"assets/images/news/default-cover.jpg\") }}" class="img-fluid rounded-start" alt="{{ $item->title }}">
                            </a>
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="{{ route(\"news.show\", $item->slug) }}" class="text-decoration-none text-dark">{{ $item->title }}</a>
                                </h5>
                                <div class="card-text news-meta">
                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> {{ $item->author->name }} | 
                                        <i class="fas fa-calendar"></i> {{ $item->published_at->format(\"Y-m-d\") }} | 
                                        <i class="fas fa-folder"></i> <a href="{{ route(\"news.category\", $item->category->slug) }}" class="text-decoration-none">{{ $item->category->name }}</a> | 
                                        <i class="fas fa-eye"></i> {{ $item->view_count }} 阅读
                                    </small>
                                </div>
                                <p class="card-text mt-2">{{ $item->summary }}</p>
                                <div class="news-tags">
                                    @foreach($item->tags as $itemTag)
                                    <a href="{{ route(\"news.tag\", $itemTag->slug) }}" class="badge {{ $itemTag->id == $tag->id ? \"bg-primary\" : \"bg-secondary\" }} text-decoration-none">{{ $itemTag->name }}</a>
                                    @endforeach
                                </div>
                                <a href="{{ route(\"news.show\", $item->slug) }}" class="btn btn-sm btn-outline-primary mt-2">阅读全文</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                <!-- 分页 -->
                <div class="d-flex justify-content-center">
                    {{ $news->withQueryString()->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    该标签下暂无新闻，请稍后再来查看。
                </div>
            @endif
        </div>
    </div>

    <!-- 侧边栏 -->
    <div class="col-lg-4">
        <!-- 分类列表 -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">新闻分类</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @foreach($categories as $category)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="{{ route(\"news.category\", $category->slug) }}" class="text-decoration-none">{{ $category->name }}</a>
                        <span class="badge bg-primary rounded-pill">{{ $category->news_count ?? 0 }}</span>
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
                    @foreach($tags as $hotTag)
                    <a href="{{ route(\"news.tag\", $hotTag->slug) }}" class="tag-item {{ $hotTag->id == $tag->id ? \"active\" : \"\" }}" style="font-size: {{ 0.8 + min($hotTag->news_count / 10, 1.2) }}rem">{{ $hotTag->name }}</a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 热门文章 -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">热门文章</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @foreach(\App\Models\News\News::getPopularNews(5) as $popularNews)
                    <li class="list-group-item">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <img src="{{ $popularNews->cover_image ? asset($popularNews->cover_image) : asset(\"assets/images/news/default-cover.jpg\") }}" alt="{{ $popularNews->title }}" width="60" height="60" class="rounded">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0"><a href="{{ route(\"news.show\", $popularNews->slug) }}" class="text-decoration-none">{{ $popularNews->title }}</a></h6>
                                <small class="text-muted">{{ $popularNews->published_at->format(\"Y-m-d\") }} | {{ $popularNews->view_count }} 阅读</small>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
