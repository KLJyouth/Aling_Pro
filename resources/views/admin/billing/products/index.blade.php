@extends('admin.layouts.admin')

@section('title', '商品管理')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">商品列表</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> 添加商品
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <table id="products-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>商品名称</th>
                                <th>价格</th>
                                <th>类型</th>
                                <th>状态</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr>
                                <td>{{ $product->id }}</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->price }}</td>
                                <td>{{ $product->type }}</td>
                                <td>
                                    @if($product->status == 1)
                                    <span class="badge badge-success">上架中</span>
                                    @else
                                    <span class="badge badge-danger">已下架</span>
                                    @endif
                                </td>
                                <td>{{ $product->created_at }}</td>
                                <td>
                                    <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> 查看
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> 编辑
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('确定要删除该商品吗？')">
                                            <i class="fas fa-trash"></i> 删除
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{ $products->links() }}
                </div>
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        $('#products-table').DataTable({
            "paging": false,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "responsive": true,
        });
    });
</script>
@endsection
