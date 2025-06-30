@extends('admin.layouts.admin')

@section('title', '用户套餐')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">用户套餐列表</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>用户</th>
                                <th>套餐名称</th>
                                <th>额度</th>
                                <th>开始时间</th>
                                <th>到期时间</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($userPackages as $userPackage)
                            <tr>
                                <td>{{ $userPackage->id }}</td>
                                <td>{{ $userPackage->user->name }}</td>
                                <td>{{ $userPackage->package->name }}</td>
                                <td>{{ $userPackage->quota }}</td>
                                <td>{{ $userPackage->start_time }}</td>
                                <td>{{ $userPackage->end_time }}</td>
                                <td>
                                    @if($userPackage->status == 1)
                                    <span class="badge badge-success">有效</span>
                                    @else
                                    <span class="badge badge-danger">已过期</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.user-packages.show', $userPackage->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> 查看
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tbody>
                            @foreach($userPackages as $userPackage)
                            <tr>
                                <td>{{ $userPackage->id }}</td>
                                <td>{{ $userPackage->user->name }}</td>
                                <td>{{ $userPackage->package->name }}</td>
                                <td>{{ $userPackage->quota }}</td>
                                <td>{{ $userPackage->start_time }}</td>
                                <td>{{ $userPackage->end_time }}</td>
                                <td>
                                    @if($userPackage->status == 1)
                                    <span class="badge badge-success">有效</span>
                                    @else
                                    <span class="badge badge-danger">已过期</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.user-packages.show', $userPackage->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> 查看
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{ $userPackages->links() }}
                </div>
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</div>
@endsection
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{ $userPackages->links() }}
                </div>
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</div>
@endsection
