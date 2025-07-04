@extends('layouts.news')

@section('title', '新闻中心')

@section('meta_description', 'AlingAi新闻中心，提供最新的AI技术资讯、行业动态和前沿研究')
@section('meta_keywords', 'AlingAi,人工智能,AI新闻,技术资讯,行业动态,AI研究')

@section('structured_data')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "CollectionPage",
  "headline": "AlingAi新闻中心",
  "description": "AlingAi新闻中心，提供最新的AI技术资讯、行业动态和前沿研究",
  "url": "{{ url()->current() }}",
  "publisher": {
    "@type": "Organization",
    "name": "AlingAi",
    "logo": {
      "@type": "ImageObject",
      "url": "{{ asset('assets/images/logo.png') }}"
    }
  }
}
</script>
@endsection

@section('content')
<div class="row">
    <!-- 主内容区 -->
    <div class="col-lg-8">
        <!-- 推荐新闻轮播 -->
        @if($featuredNews->count() > 0)
        <div class="featured-news mb-4">
            <div id="featuredNewsCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    @foreach($featuredNews as $index => $featured)
                    <button type="button" data-bs-target="#featuredNewsCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index == 0 ? 'active' : '' }}" aria-current="{{ $index == 0 ? 'true' : 'false' }}" aria-label="幻灯片 {{ $index + 1 }}"></button>
                    @endforeach
                </div>
                <div class="carousel-inner">
                    @foreach($featuredNews as $index => $featured)
                    <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                        <a href="{{ route('news.show', $featured->slug) }}">
                            <img src="{{ $featured->cover_image ? asset($featured->cover_image) : asset('assets/images/news/default-cover.jpg') }}" class="d-block w-100" alt="{{ $featured->title }}">
                            <div class="carousel-caption d-none d-md-block">
                                <h5>{{ $featured->title }}</h5>
                                <p>{{ $featured->summary }}</p>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#featuredNewsCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">上一个</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#featuredNewsCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">下一个</span>
                </button>
            </div>
        </div>
        @endif

        <!-- 筛选和排序 -->
        <div class="news-filters mb-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2>{{ request()->has('search') ? '搜索结果: '.request()->input('search') : '最新新闻' }}</h2>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="btn-group">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}" class="btn btn-sm {{ request()->input('sort', 'latest') == 'latest' ? 'btn-primary' : 'btn-outline-primary' }}">最新</a>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'popular']) }}" class="btn btn-sm {{ request()->input('sort') == 'popular' ? 'btn-primary' : 'btn-outline-primary' }}">热门</a>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'oldest']) }}" class="btn btn-sm {{ request()->input('sort') == 'oldest' ? 'btn-primary' : 'btn-outline-primary' }}">最早</a>
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
                            <a href="{{ route('news.show', $item->slug) }}">
                                <img src="{{ $item->cover_image ? asset($item->cover_image) : asset('assets/images/news/default-cover.jpg') }}" class="img-fluid rounded-start" alt="{{ $item->title }}">
                            </a>
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="{{ route('news.show', $item->slug) }}" class="text-decoration-none text-dark">{{ $item->title }}</a>
                                </h5>
                                <div class="card-text news-meta">
                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> {{ $item->author->name }} | 
                                        <i class="fas fa-calendar"></i> {{ $item->published_at->format('Y-m-d') }} | 
                                        <i class="fas fa-folder"></i> <a href="{{ route('news.category', $item->category->slug) }}" class="text-decoration-none">{{ $item->category->name }}</a> | 
                                        <i class="fas fa-eye"></i> {{ $item->view_count }} 阅读
                                    </small>
                                </div>
                                <p class="card-text mt-2">{{ $item->summary }}</p>
                                <div class="news-tags">
                                    @foreach($item->tags as $tag)
                                    <a href="{{ route('news.tag', $tag->slug) }}" class="badge bg-secondary text-decoration-none">{{ $tag->name }}</a>
                                    @endforeach
                                </div>
                                <a href="{{ route('news.show', $item->slug) }}" class="btn btn-sm btn-outline-primary mt-2">阅读全文</a>
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
                    暂无相关新闻，请稍后再来查看。
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
                        <a href="{{ route('news.category', $category->slug) }}" class="text-decoration-none">{{ $category->name }}</a>
                        <span class="badge bg-primary rounded-pill">{{ $category->news_count ?? 0 }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- 标签云 -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">热门标签</h5>
            </div>
            <div class="card-body">
                <div class="tags-cloud">
                    @foreach($tags as $tag)
                    <a href="{{ route('news.tag', $tag->slug) }}" class="tag-item" style="font-size: {{ 0.8 + min($tag->news_count / 10, 1.2) }}rem">{{ $tag->name }}</a>
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
                                <img src="{{ $popularNews->cover_image ? asset($popularNews->cover_image) : asset('assets/images/news/default-cover.jpg') }}" alt="{{ $popularNews->title }}" width="60" height="60" class="rounded">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0"><a href="{{ route('news.show', $popularNews->slug) }}" class="text-decoration-none">{{ $popularNews->title }}</a></h6>
                                <small class="text-muted">{{ $popularNews->published_at->format('Y-m-d') }} | {{ $popularNews->view_count }} 阅读</small>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- 订阅新闻 -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">订阅新闻</h5>
            </div>
            <div class="card-body">
                <p>订阅我们的新闻通讯，获取最新的AI技术资讯和行业动态。</p>
                <form action="{{ route('news.subscribe') }}" method="POST">
                    @csrf
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" name="email" placeholder="您的邮箱地址" required>
                        <button class="btn btn-primary" type="submit">订阅</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
