@extends('admin.news.layout')

@section('title', '新闻列表')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">新闻列表</h3>
                        <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> 添加新闻
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- 筛选表单 -->
                    <form action="{{ route('admin.news.index') }}" method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="搜索标题..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="category" class="form-select">
                                    <option value="">所有分类</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">所有状态</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>草稿</option>
                                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>已发布</option>
                                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>已归档</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="featured" class="form-select">
                                    <option value="">推荐状态</option>
                                    <option value="1" {{ request('featured') == '1' ? 'selected' : '' }}>已推荐</option>
                                    <option value="0" {{ request('featured') == '0' ? 'selected' : '' }}>未推荐</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="sort" class="form-select">
                                    <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>最新创建</option>
                                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>最早创建</option>
                                    <option value="published_desc" {{ request('sort') == 'published_desc' ? 'selected' : '' }}>最新发布</option>
                                    <option value="published_asc" {{ request('sort') == 'published_asc' ? 'selected' : '' }}>最早发布</option>
                                    <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>最多阅读</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">筛选</button>
                            </div>
                        </div>
                    </form>

                    <!-- 批量操作 -->
                    <div class="mb-3">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                批量操作
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item batch-action" href="#" data-action="publish">发布所选</a></li>
                                <li><a class="dropdown-item batch-action" href="#" data-action="draft">设为草稿</a></li>
                                <li><a class="dropdown-item batch-action" href="#" data-action="archive">归档所选</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item batch-action" href="#" data-action="feature">设为推荐</a></li>
                                <li><a class="dropdown-item batch-action" href="#" data-action="unfeature">取消推荐</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item batch-action text-danger" href="#" data-action="delete">删除所选</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- 新闻列表 -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="30px">
                                        <input type="checkbox" class="select-all">
                                    </th>
                                    <th width="60px">ID</th>
                                    <th width="100px">封面</th>
                                    <th>标题</th>
                                    <th width="120px">分类</th>
                                    <th width="100px">作者</th>
                                    <th width="100px">状态</th>
                                    <th width="100px">阅读量</th>
                                    <th width="160px">发布时间</th>
                                    <th width="180px">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($news as $item)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="select-item" value="{{ $item->id }}">
                                    </td>
                                    <td>{{ $item->id }}</td>
                                    <td>
                                        <img src="{{ $item->cover_image ? asset($item->cover_image) : asset('assets/images/news/default-cover.jpg') }}" alt="{{ $item->title }}" class="img-thumbnail" width="80">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->featured)
                                            <span class="badge bg-success me-1" title="推荐"><i class="fas fa-star"></i></span>
                                            @endif
                                            <a href="{{ route('admin.news.edit', $item->id) }}">{{ $item->title }}</a>
                                        </div>
                                        <div class="small text-muted mt-1">
                                            <strong>标签:</strong> 
                                            @foreach($item->tags as $tag)
                                            <span class="badge bg-secondary">{{ $tag->name }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>{{ $item->category->name ?? '无分类' }}</td>
                                    <td>{{ $item->author->name }}</td>
                                    <td>
                                        @if($item->status == 'published')
                                        <span class="badge bg-success">已发布</span>
                                        @elseif($item->status == 'draft')
                                        <span class="badge bg-warning">草稿</span>
                                        @elseif($item->status == 'archived')
                                        <span class="badge bg-secondary">已归档</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->view_count }}</td>
                                    <td>{{ $item->published_at ? $item->published_at->format('Y-m-d H:i') : '未发布' }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('news.show', $item->slug) }}" class="btn btn-sm btn-info" target="_blank" title="查看">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.news.edit', $item->id) }}" class="btn btn-sm btn-primary" title="编辑">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="{{ $item->id }}" title="删除">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <div class="btn-group mt-1">
                                            @if($item->status != 'published')
                                            <button type="button" class="btn btn-sm btn-success status-btn" data-id="{{ $item->id }}" data-action="publish" title="发布">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            @endif
                                            @if($item->status != 'draft')
                                            <button type="button" class="btn btn-sm btn-warning status-btn" data-id="{{ $item->id }}" data-action="draft" title="设为草稿">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>
                                            @endif
                                            @if($item->status != 'archived')
                                            <button type="button" class="btn btn-sm btn-secondary status-btn" data-id="{{ $item->id }}" data-action="archive" title="归档">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                            @endif
                                            @if(!$item->featured)
                                            <button type="button" class="btn btn-sm btn-outline-success feature-btn" data-id="{{ $item->id }}" data-action="feature" title="设为推荐">
                                                <i class="far fa-star"></i>
                                            </button>
                                            @else
                                            <button type="button" class="btn btn-sm btn-success feature-btn" data-id="{{ $item->id }}" data-action="unfeature" title="取消推荐">
                                                <i class="fas fa-star"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- 分页 -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $news->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 删除确认模态框 -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">确认删除</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                确定要删除这条新闻吗？此操作不可恢复！
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <form id="deleteForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">确认删除</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 批量操作表单 -->
<form id="batchForm" action="{{ route('admin.news.batch') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="action" id="batchAction">
    <input type="hidden" name="ids" id="batchIds">
</form>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // 全选/取消全选
        $('.select-all').change(function() {
            $('.select-item').prop('checked', $(this).prop('checked'));
        });
        
        // 删除按钮点击事件
        $('.delete-btn').click(function() {
            var id = $(this).data('id');
            $('#deleteForm').attr('action', '{{ url("admin/news") }}/' + id);
            $('#deleteModal').modal('show');
        });
        
        // 状态按钮点击事件
        $('.status-btn').click(function() {
            var id = $(this).data('id');
            var action = $(this).data('action');
            
            $.ajax({
                url: '{{ url("admin/news") }}/' + id + '/' + action,
                type: 'POST',
                data: {_token: '{{ csrf_token() }}'},
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('操作失败：' + response.message);
                    }
                },
                error: function() {
                    alert('操作失败，请重试');
                }
            });
        });
        
        // 推荐按钮点击事件
        $('.feature-btn').click(function() {
            var id = $(this).data('id');
            var action = $(this).data('action');
            
            $.ajax({
                url: '{{ url("admin/news") }}/' + id + '/toggle-featured',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    action: action
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('操作失败：' + response.message);
                    }
                },
                error: function() {
                    alert('操作失败，请重试');
                }
            });
        });
        
        // 批量操作
        $('.batch-action').click(function(e) {
            e.preventDefault();
            
            var action = $(this).data('action');
            var selectedIds = [];
            
            $('.select-item:checked').each(function() {
                selectedIds.push($(this).val());
            });
            
            if (selectedIds.length === 0) {
                alert('请至少选择一条新闻');
                return;
            }
            
            if (action === 'delete' && !confirm('确定要删除所选的 ' + selectedIds.length + ' 条新闻吗？此操作不可恢复！')) {
                return;
            }
            
            $('#batchAction').val(action);
            $('#batchIds').val(selectedIds.join(','));
            $('#batchForm').submit();
        });
    });
</script>
@endsection