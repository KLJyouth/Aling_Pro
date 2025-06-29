@extends('admin.layouts.app')

@section('title', 'API SDK管理')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">API SDK列表</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.security.api.sdks.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> 创建SDK
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <form action="{{ route('admin.security.api.sdks.index') }}" method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <input type="text" name="name" class="form-control" placeholder="SDK名称" value="{{ request('name') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <select name="language" class="form-control">
                                        <option value="">所有语言</option>
                                        <option value="php" {{ request('language') == 'php' ? 'selected' : '' }}>PHP</option>
                                        <option value="python" {{ request('language') == 'python' ? 'selected' : '' }}>Python</option>
                                        <option value="javascript" {{ request('language') == 'javascript' ? 'selected' : '' }}>JavaScript</option>
                                        <option value="java" {{ request('language') == 'java' ? 'selected' : '' }}>Java</option>
                                        <option value="csharp" {{ request('language') == 'csharp' ? 'selected' : '' }}>C#</option>
                                        <option value="go" {{ request('language') == 'go' ? 'selected' : '' }}>Go</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select name="status" class="form-control">
                                        <option value="">所有状态</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>启用</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>禁用</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 搜索
                                </button>
                                <a href="{{ route('admin.security.api.sdks.index') }}" class="btn btn-default">
                                    <i class="fas fa-redo"></i> 重置
                                </a>
                            </div>
                        </div>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>名称</th>
                                    <th>语言</th>
                                    <th>当前版本</th>
                                    <th>接口数量</th>
                                    <th>下载次数</th>
                                    <th>状态</th>
                                    <th>更新时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sdks as $sdk)
                                <tr>
                                    <td>{{ $sdk->id }}</td>
                                    <td>{{ $sdk->name }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $sdk->language_name }}</span>
                                    </td>
                                    <td>
                                        @if($sdk->currentVersion)
                                            {{ $sdk->currentVersion->version }}
                                        @else
                                            <span class="text-muted">无版本</span>
                                        @endif
                                    </td>
                                    <td>{{ $sdk->interfaces->count() }}</td>
                                    <td>
                                        {{ $sdk->versions->sum('download_count') }}
                                    </td>
                                    <td>
                                        @if($sdk->status == 'active')
                                            <span class="badge badge-success">启用</span>
                                        @else
                                            <span class="badge badge-danger">禁用</span>
                                        @endif
                                    </td>
                                    <td>{{ $sdk->updated_at }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.security.api.sdks.show', $sdk->id) }}" class="btn btn-xs btn-info">
                                                <i class="fas fa-eye"></i> 详情
                                            </a>
                                            <a href="{{ route('admin.security.api.sdks.edit', $sdk->id) }}" class="btn btn-xs btn-primary">
                                                <i class="fas fa-edit"></i> 编辑
                                            </a>
                                            <button type="button" class="btn btn-xs btn-success" data-toggle="modal" data-target="#generateSdkModal" data-sdk-id="{{ $sdk->id }}" data-sdk-name="{{ $sdk->name }}" data-sdk-language="{{ $sdk->language }}">
                                                <i class="fas fa-code"></i> 生成
                                            </button>
                                            <form action="{{ route('admin.security.api.sdks.destroy', $sdk->id) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('确定要删除该SDK吗？')">
                                                    <i class="fas fa-trash"></i> 删除
                                                </button>
                                            </form>
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
                    {{ $sdks->appends(request()->except('page'))->links() }}
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>

<!-- 生成SDK模态框 -->
<div class="modal fade" id="generateSdkModal" tabindex="-1" role="dialog" aria-labelledby="generateSdkModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="generateSdkModalLabel">生成SDK</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="generate-sdk-form" action="" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>SDK名称</label>
                        <input type="text" class="form-control" id="sdk-name" disabled>
                    </div>
                    <div class="form-group">
                        <label>语言</label>
                        <input type="text" class="form-control" id="sdk-language" disabled>
                    </div>
                    <div class="form-group">
                        <label for="version">版本号 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="version" name="version" placeholder="例如：1.0.0" required>
                    </div>
                    <div class="form-group">
                        <label for="changelog">版本说明</label>
                        <textarea class="form-control" id="changelog" name="changelog" rows="3" placeholder="请输入版本更新说明"></textarea>
                    </div>
                    <div class="form-group">
                        <label>选择接口</label>
                        <div class="card">
                            <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="select-all-interfaces">
                                    <label class="form-check-label font-weight-bold" for="select-all-interfaces">
                                        全选
                                    </label>
                                </div>
                                <hr>
                                <div id="interface-list">
                                    <!-- 接口列表将通过AJAX加载 -->
                                    <div class="text-center">
                                        <i class="fas fa-spinner fa-spin"></i> 加载中...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_current" name="is_current" checked>
                            <label class="custom-control-label" for="is_current">设为当前版本</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-code"></i> 生成SDK
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // 生成SDK模态框
        $('#generateSdkModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var sdkId = button.data('sdk-id');
            var sdkName = button.data('sdk-name');
            var sdkLanguage = button.data('sdk-language');
            
            var modal = $(this);
            modal.find('#sdk-name').val(sdkName);
            modal.find('#sdk-language').val(sdkLanguage);
            modal.find('#generate-sdk-form').attr('action', '{{ url("admin/security/api/sdks") }}/' + sdkId + '/generate');
            
            // 加载接口列表
            $.ajax({
                url: '{{ url("admin/security/api/sdks") }}/' + sdkId + '/interfaces',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var html = '';
                    
                    if(response.interfaces.length > 0) {
                        $.each(response.interfaces, function(index, interface) {
                            html += '<div class="form-check">';
                            html += '<input class="form-check-input interface-checkbox" type="checkbox" id="interface_' + interface.id + '" name="interfaces[]" value="' + interface.id + '" ' + (interface.selected ? 'checked' : '') + '>';
                            html += '<label class="form-check-label" for="interface_' + interface.id + '">';
                            html += interface.name + ' <small class="text-muted">(' + interface.path + ')</small>';
                            html += '</label>';
                            html += '</div>';
                        });
                    } else {
                        html = '<div class="text-center text-muted">没有可用的接口</div>';
                    }
                    
                    $('#interface-list').html(html);
                },
                error: function() {
                    $('#interface-list').html('<div class="text-center text-danger">加载接口失败</div>');
                }
            });
        });
        
        // 全选/取消全选接口
        $(document).on('change', '#select-all-interfaces', function() {
            $('.interface-checkbox').prop('checked', this.checked);
        });
        
        // 检查是否所有接口都被选中
        $(document).on('change', '.interface-checkbox', function() {
            var allChecked = $('.interface-checkbox:checked').length === $('.interface-checkbox').length;
            $('#select-all-interfaces').prop('checked', allChecked);
        });
    });
</script>
@endsection 