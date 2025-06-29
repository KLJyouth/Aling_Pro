@extends('admin.layouts.app')

@section('title', '额度套餐管理')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>额度套餐管理</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">首页</a></li>
                <li class="breadcrumb-item active">额度套餐管理</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">额度套餐列表</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.billing.packages.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> 创建套餐
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="mb-3">
                        <form action="{{ route('admin.billing.packages.index') }}" method="GET" class="form-inline">
                            <div class="form-group mr-2">
                                <select name="type" class="form-control">
                                    <option value="">全部类型</option>
                                    <option value="api" {{ request('type') == 'api' ? 'selected' : '' }}>API调用额度</option>
                                    <option value="ai" {{ request('type') == 'ai' ? 'selected' : '' }}>AI使用额度</option>
                                    <option value="storage" {{ request('type') == 'storage' ? 'selected' : '' }}>存储空间</option>
                                    <option value="bandwidth" {{ request('type') == 'bandwidth' ? 'selected' : '' }}>带宽流量</option>
                                    <option value="comprehensive" {{ request('type') == 'comprehensive' ? 'selected' : '' }}>综合套餐</option>
                                </select>
                            </div>
                            <div class="form-group mr-2">
                                <select name="status" class="form-control">
                                    <option value="">全部状态</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>上架</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>下架</option>
                                    <option value="coming_soon" {{ request('status') == 'coming_soon' ? 'selected' : '' }}>即将推出</option>
                                </select>
                            </div>
                            <div class="form-group mr-2">
                                <input type="text" name="search" class="form-control" placeholder="搜索..." value="{{ request('search') }}">
                            </div>
                            <button type="submit" class="btn btn-primary">筛选</button>
                            <a href="{{ route('admin.billing.packages.index') }}" class="btn btn-default ml-2">重置</a>
                        </form>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>名称</th>
                                    <th>代码</th>
                                    <th>类型</th>
                                    <th>额度</th>
                                    <th>价格</th>
                                    <th>原价</th>
                                    <th>有效期(天)</th>
                                    <th>排序</th>
                                    <th>状态</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($packages as $package)
                                    <tr>
                                        <td>{{ $package->id }}</td>
                                        <td>
                                            {{ $package->name }}
                                            @if($package->is_popular)
                                                <span class="badge badge-success">热门</span>
                                            @endif
                                            @if($package->is_recommended)
                                                <span class="badge badge-info">推荐</span>
                                            @endif
                                        </td>
                                        <td>{{ $package->code }}</td>
                                        <td>{{ $package->type_name }}</td>
                                        <td>{{ number_format($package->quota) }}</td>
                                        <td>¥{{ number_format($package->price, 2) }}</td>
                                        <td>
                                            @if($package->original_price)
                                                <del>¥{{ number_format($package->original_price, 2) }}</del>
                                                <span class="text-danger">{{ $package->discount_percent }}% 折扣</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $package->duration_days ?: '永久' }}</td>
                                        <td>{{ $package->sort_order }}</td>
                                        <td>
                                            @if($package->status == 'active')
                                                <span class="badge badge-success">上架</span>
                                            @elseif($package->status == 'inactive')
                                                <span class="badge badge-danger">下架</span>
                                            @else
                                                <span class="badge badge-warning">即将推出</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.billing.packages.show', $package) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.billing.packages.edit', $package) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal{{ $package->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            
                                            <!-- 删除确认模态框 -->
                                            <div class="modal fade" id="deleteModal{{ $package->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel{{ $package->id }}" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteModalLabel{{ $package->id }}">确认删除</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>确定要删除套餐 "{{ $package->name }}" 吗？此操作不可逆。</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                                                            <form action="{{ route('admin.billing.packages.destroy', $package) }}" method="POST" style="display: inline-block;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">确认删除</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{ $packages->appends(request()->query())->links() }}
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(function () {
        // 初始化表格排序
        $('table').DataTable({
            "paging": false,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "responsive": true,
        });
    });
</script>
@endsection 