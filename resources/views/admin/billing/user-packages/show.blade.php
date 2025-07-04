
@extends('admin.layouts.admin')

@section('title', '用户套餐详情')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">用户套餐详情</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.user-packages.index') }}" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> 返回列表
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">套餐信息</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 30%">ID</th>
                                            <td>{{ $userPackage->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>套餐名称</th>
                                            <td>{{ $userPackage->package->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>额度</th>
                                            <td>{{ $userPackage->quota }}</td>
                                        </tr>
                                        <tr>
                                            <th>开始时间</th>
                                            <td>{{ $userPackage->start_time }}</td>
                                        </tr>
                                        <tr>
                                            <th>到期时间</th>
                                            <td>{{ $userPackage->end_time }}</td>
                                        </tr>
                                        <tr>
                                            <th>状态</th>
                                            <td>
                                                @if($userPackage->status == 1)
                                                <span class="badge badge-success">有效</span>
                                                @else
                                                <span class="badge badge-danger">已过期</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>创建时间</th>
                                            <td>{{ $userPackage->created_at }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">用户信息</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 30%">用户ID</th>
                                            <td>{{ $userPackage->user->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>用户名</th>
                                            <td>{{ $userPackage->user->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>邮箱</th>
                                            <td>{{ $userPackage->user->email }}</td>
                                        </tr>
                                        <tr>
                                            <th>注册时间</th>
                                            <td>{{ $userPackage->user->created_at }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">额度使用记录</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>使用类型</th>
                                                <th>使用额度</th>
                                                <th>剩余额度</th>
                                                <th>使用时间</th>
                                                <th>描述</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($usageRecords as $record)
                                            <tr>
                                                <td>{{ $record->id }}</td>
                                                <td>{{ $record->type }}</td>
                                                <td>{{ $record->amount }}</td>
                                                <td>{{ $record->remaining }}</td>
                                                <td>{{ $record->created_at }}</td>
                                                <td>{{ $record->description }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <!-- /.card-body -->
                                <div class="card-footer clearfix">
                                    {{ $usageRecords->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</div>
@endsection
