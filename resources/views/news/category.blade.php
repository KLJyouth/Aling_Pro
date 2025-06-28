@extends('layouts.news')

@section('title', $category->name . ' - 新闻分类')
@section('meta_description', $category->meta_description ?? $category->name . '分类下的所有新闻文章')
@section('meta_keywords', $category->meta_keywords ?? $category->name . ',新闻,AlingAi')

@section('structured_data')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "CollectionPage",
  "headline": "{{ $category->name }} - 新闻分类",
  "description": "{{ $category->description }}",
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
        <!-- 分类信息 -->
        <div class="category-header mb-4">
            <h1 class="category-title">{{ $category->name }}</h1>
            @if($category->description)
            <div class="category-description">
                {{ $category->description }}
            </div>
            @endif
        </div>

        <!-- 筛选和排序 -->
        <div class="news-filters mb-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2>分类文章</h2>
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
                    该分类下暂无新闻，请稍后再来查看。
                </div>
            @endif
        </div>
    </div>

    <!-- 侧边栏 -->
    <div class="col-lg-4">
        <!-- 子分类列表 -->
        @if($category->children->count() > 0)
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">子分类</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @foreach($category->children as $child)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="{{ route('news.category', $child->slug) }}" class="text-decoration-none">{{ $child->name }}</a>
                        <span class="badge bg-primary rounded-pill">{{ $child->news_count ?? 0 }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <!-- 分类列表 -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">所有分类</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @foreach($categories as $cat)
                    <li class="list-group-item d-flex justify-content-between align-items-center {{ $cat->id == $category->id ? 'active' : '' }}">
                        <a href="{{ route('news.category', $cat->slug) }}" class="text-decoration-none {{ $cat->id == $category->id ? 'text-white' : '' }}">{{ $cat->name }}</a>
                        <span class="badge {{ $cat->id == $category->id ? 'bg-white text-primary' : 'bg-primary' }} rounded-pill">{{ $cat->news_count ?? 0 }}</span>
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
    </div>
</div>
@endsection