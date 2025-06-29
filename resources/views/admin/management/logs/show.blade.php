@extends('admin.layouts.app')

@section('title', '日志详情')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">日志详情</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.management.logs.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-left"></i> 返回列表
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 150px;">日志ID</th>
                                    <td>{{ $log->id }}</td>
                                </tr>
                                <tr>
                                    <th>日志类型</th>
                                    <td>
                                        @if($log->log_type == 'system')
                                        <span class="badge badge-primary">系统</span>
                                        @elseif($log->log_type == 'login')
                                        <span class="badge badge-info">登录</span>
                                        @elseif($log->log_type == 'operation')
                                        <span class="badge badge-success">操作</span>
                                        @elseif($log->log_type == 'error')
                                        <span class="badge badge-danger">错误</span>
                                        @elseif($log->log_type == 'security')
                                        <span class="badge badge-warning">安全</span>
                                        @else
                                        <span class="badge badge-secondary">其他</span>
                                        @endif
                                        {{ $log->log_type }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>日志级别</th>
                                    <td>
                                        @if($log->level == 'info')
                                        <span class="badge badge-info">信息</span>
                                        @elseif($log->level == 'warning')
                                        <span class="badge badge-warning">警告</span>
                                        @elseif($log->level == 'error')
                                        <span class="badge badge-danger">错误</span>
                                        @elseif($log->level == 'critical')
                                        <span class="badge badge-dark">严重</span>
                                        @elseif($log->level == 'debug')
                                        <span class="badge badge-secondary">调试</span>
                                        @else
                                        <span class="badge badge-light">{{ $log->level }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>用户</th>
                                    <td>
                                        @if($log->user_id)
                                        <a href="{{ route('admin.management.users.show', $log->user_id) }}">
                                            {{ $log->user_name ?? 'ID: '.$log->user_id }}
                                        </a>
                                        @else
                                        <span class="text-muted">系统</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>IP地址</th>
                                    <td>{{ $log->ip_address ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>用户代理</th>
                                    <td style="word-break: break-all;">{{ $log->user_agent ?: '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 150px;">请求URL</th>
                                    <td style="word-break: break-all;">{{ $log->url ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>请求方法</th>
                                    <td>{{ $log->method ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>请求参数</th>
                                    <td>
                                        @if($log->request_data)
                                        <button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#requestDataModal">
                                            <i class="fas fa-eye"></i> 查看请求参数
                                        </button>
                                        @else
                                        -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>关联ID</th>
                                    <td>{{ $log->related_id ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>创建时间</th>
                                    <td>{{ $log->created_at }}</td>
                                </tr>
                                <tr>
                                    <th>更新时间</th>
                                    <td>{{ $log->updated_at }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">日志内容</h3>
                                </div>
                                <div class="card-body">
                                    <div class="p-3 bg-light" style="max-height: 400px; overflow-y: auto;">
                                        <pre style="white-space: pre-wrap;">{{ $log->message }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($log->context)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">上下文数据</h3>
                                </div>
                                <div class="card-body">
                                    <div class="p-3 bg-light" style="max-height: 400px; overflow-y: auto;">
                                        <pre style="white-space: pre-wrap;">@php
                                            try {
                                                $contextObj = json_decode($log->context, true);
                                                echo json_encode($contextObj, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                            } catch (\Exception $e) {
                                                echo $log->context;
                                            }
                                        @endphp</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($log->stack_trace)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">堆栈跟踪</h3>
                                </div>
                                <div class="card-body">
                                    <div class="p-3 bg-light" style="max-height: 400px; overflow-y: auto;">
                                        <pre style="white-space: pre-wrap;">{{ $log->stack_trace }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.management.logs.index') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> 返回列表
                    </a>
                    
                    @if($prevLog)
                    <a href="{{ route('admin.management.logs.show', $prevLog->id) }}" class="btn btn-primary">
                        <i class="fas fa-chevron-left"></i> 上一条
                    </a>
                    @else
                    <button class="btn btn-primary" disabled>
                        <i class="fas fa-chevron-left"></i> 上一条
                    </button>
                    @endif
                    
                    @if($nextLog)
                    <a href="{{ route('admin.management.logs.show', $nextLog->id) }}" class="btn btn-primary">
                        下一条 <i class="fas fa-chevron-right"></i>
                    </a>
                    @else
                    <button class="btn btn-primary" disabled>
                        下一条 <i class="fas fa-chevron-right"></i>
                    </button>
                    @endif
                    
                    @if($log->log_type == 'error')
                    <a href="{{ route('admin.management.logs.similar', $log->id) }}" class="btn btn-warning">
                        <i class="fas fa-search"></i> 查找类似错误
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 请求参数模态框 -->
@if($log->request_data)
<div class="modal fade" id="requestDataModal" tabindex="-1" role="dialog" aria-labelledby="requestDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestDataModalLabel">请求参数</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="p-3 bg-light">
                    <pre style="white-space: pre-wrap;">@php
                        try {
                            $requestData = json_decode($log->request_data, true);
                            echo json_encode($requestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                        } catch (\Exception $e) {
                            echo $log->request_data;
                        }
                    @endphp</pre>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection 