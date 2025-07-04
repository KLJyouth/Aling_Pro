
@extends('admin.layouts.admin')

@section('title', '会员等级详情')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">会员等级详情</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.membership.levels.index') }}" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> 返回列表
                        </a>
                        <a href="{{ route('admin.membership.levels.edit', $level->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> 编辑
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">基本信息</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 30%">ID</th>
                                            <td>{{ $level->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>等级名称</th>
                                            <td>{{ $level->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>等级描述</th>
                                            <td>{{ $level->description }}</td>
                                        </tr>
                                        <tr>
                                            <th>月费用</th>
                                            <td>{{ $level->price_monthly }}</td>
                                        </tr>
                                        <tr>
                                            <th>年费用</th>
                                            <td>{{ $level->price_yearly }}</td>
                                        </tr>
                                        <tr>
                                            <th>状态</th>
                                            <td>
                                                @if($level->is_active)
                                                <span class="badge badge-success">启用</span>
                                                @else
                                                <span class="badge badge-danger">禁用</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">权益信息</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 30%">每日额度</th>
                                            <td>{{ $level->daily_quota }}</td>
                                        </tr>
                                        <tr>
                                            <th>最大存储空间</th>
                                            <td>{{ $level->storage_limit }} MB</td>
                                        </tr>
                                        <tr>
                                            <th>最大文件大小</th>
                                            <td>{{ $level->max_file_size }} MB</td>
                                        </tr>
                                        <tr>
                                            <th>并发请求数</th>
                                            <td>{{ $level->concurrent_requests }}</td>
                                        </tr>
                                        <tr>
                                            <th>高级模型访问</th>
                                            <td>
                                                @if($level->advanced_models_access)
                                                <span class="badge badge-success">允许</span>
                                                @else
                                                <span class="badge badge-danger">不允许</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>优先队列</th>
                                            <td>
                                                @if($level->priority_queue)
                                                <span class="badge badge-success">是</span>
                                                @else
                                                <span class="badge badge-danger">否</span>
                                                @endif
                                            </td>
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
                                    <h3 class="card-title">会员订阅统计</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 col-sm-6 col-12">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">总订阅数</span>
                                                    <span class="info-box-number">{{ $stats['total_subscriptions'] ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 col-12">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success"><i class="fas fa-user-check"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">活跃订阅</span>
                                                    <span class="info-box-number">{{ $stats['active_subscriptions'] ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 col-12">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-warning"><i class="fas fa-dollar-sign"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">月收入</span>
                                                    <span class="info-box-number">{{ $stats['monthly_revenue'] ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 col-12">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-danger"><i class="fas fa-chart-line"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">年收入</span>
                                                    <span class="info-box-number">{{ $stats['yearly_revenue'] ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
