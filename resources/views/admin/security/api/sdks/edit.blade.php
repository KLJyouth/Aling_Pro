@extends('admin.layouts.app')

@section('title', '编辑API SDK')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">编辑API SDK</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form role="form" method="POST" action="{{ route('admin.security.api.sdks.update', $sdk->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">SDK名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="请输入SDK名称" value="{{ old('name', $sdk->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="slug">标识符 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" placeholder="请输入标识符" value="{{ old('slug', $sdk->slug) }}" required>
                            @error('slug')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">用于URL和文件名，只能包含字母、数字、连字符和下划线</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="language">语言</label>
                            <input type="text" class="form-control" value="{{ $sdk->language_name }}" disabled>
                            <small class="form-text text-muted">SDK语言创建后不可修改</small>
                            <input type="hidden" name="language" value="{{ $sdk->language }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">描述</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="请输入SDK描述">{{ old('description', $sdk->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>选择接口</label>
                            <div class="card">
                                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="select-all-interfaces">
                                        <label class="form-check-label font-weight-bold" for="select-all-interfaces">
                                            全选
                                        </label>
                                    </div>
                                    <hr>
                                    @foreach($interfaces as $interface)
                                        <div class="form-check">
                                            <input class="form-check-input interface-checkbox" type="checkbox" id="interface_{{ $interface->id }}" name="interfaces[]" value="{{ $interface->id }}" {{ in_array($interface->id, old('interfaces', $sdkInterfaces)) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="interface_{{ $interface->id }}">
                                                {{ $interface->name }} <small class="text-muted">({{ $interface->path }})</small>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @error('interfaces')
                                <span class="text-danger">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">SDK配置选项</h3>
                            </div>
                            <div class="card-body">
                                @if($sdk->language == 'php')
                                <div id="php-options">
                                    <div class="form-group">
                                        <label for="php_namespace">命名空间</label>
                                        <input type="text" class="form-control" id="php_namespace" name="options[php][namespace]" placeholder="例如：AlingAi\SDK" value="{{ old('options.php.namespace', $options['php']['namespace'] ?? '') }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="php_package_name">包名</label>
                                        <input type="text" class="form-control" id="php_package_name" name="options[php][package_name]" placeholder="例如：alingai/api-sdk" value="{{ old('options.php.package_name', $options['php']['package_name'] ?? '') }}">
                                    </div>
                                </div>
                                @elseif($sdk->language == 'python')
                                <div id="python-options">
                                    <div class="form-group">
                                        <label for="python_package_name">包名</label>
                                        <input type="text" class="form-control" id="python_package_name" name="options[python][package_name]" placeholder="例如：alingai_sdk" value="{{ old('options.python.package_name', $options['python']['package_name'] ?? '') }}">
                                    </div>
                                </div>
                                @elseif($sdk->language == 'javascript')
                                <div id="javascript-options">
                                    <div class="form-group">
                                        <label for="javascript_package_name">包名</label>
                                        <input type="text" class="form-control" id="javascript_package_name" name="options[javascript][package_name]" placeholder="例如：alingai-sdk" value="{{ old('options.javascript.package_name', $options['javascript']['package_name'] ?? '') }}">
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="javascript_typescript" name="options[javascript][typescript]" {{ old('options.javascript.typescript', $options['javascript']['typescript'] ?? false) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="javascript_typescript">包含TypeScript定义</label>
                                        </div>
                                    </div>
                                </div>
                                @elseif($sdk->language == 'java')
                                <div id="java-options">
                                    <div class="form-group">
                                        <label for="java_package_name">包名</label>
                                        <input type="text" class="form-control" id="java_package_name" name="options[java][package_name]" placeholder="例如：com.alingai.sdk" value="{{ old('options.java.package_name', $options['java']['package_name'] ?? '') }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="java_group_id">Group ID</label>
                                        <input type="text" class="form-control" id="java_group_id" name="options[java][group_id]" placeholder="例如：com.alingai" value="{{ old('options.java.group_id', $options['java']['group_id'] ?? '') }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="java_artifact_id">Artifact ID</label>
                                        <input type="text" class="form-control" id="java_artifact_id" name="options[java][artifact_id]" placeholder="例如：api-sdk" value="{{ old('options.java.artifact_id', $options['java']['artifact_id'] ?? '') }}">
                                    </div>
                                </div>
                                @elseif($sdk->language == 'csharp')
                                <div id="csharp-options">
                                    <div class="form-group">
                                        <label for="csharp_namespace">命名空间</label>
                                        <input type="text" class="form-control" id="csharp_namespace" name="options[csharp][namespace]" placeholder="例如：AlingAi.Sdk" value="{{ old('options.csharp.namespace', $options['csharp']['namespace'] ?? '') }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="csharp_project_name">项目名称</label>
                                        <input type="text" class="form-control" id="csharp_project_name" name="options[csharp][project_name]" placeholder="例如：AlingAi.Sdk" value="{{ old('options.csharp.project_name', $options['csharp']['project_name'] ?? '') }}">
                                    </div>
                                </div>
                                @elseif($sdk->language == 'go')
                                <div id="go-options">
                                    <div class="form-group">
                                        <label for="go_package_name">包名</label>
                                        <input type="text" class="form-control" id="go_package_name" name="options[go][package_name]" placeholder="例如：alingaisdk" value="{{ old('options.go.package_name', $options['go']['package_name'] ?? '') }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="go_module_name">模块名称</label>
                                        <input type="text" class="form-control" id="go_module_name" name="options[go][module_name]" placeholder="例如：github.com/alingai/sdk" value="{{ old('options.go.module_name', $options['go']['module_name'] ?? '') }}">
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="form-group mt-3">
                            <label for="status">状态</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="active" {{ old('status', $sdk->status) == 'active' ? 'selected' : '' }}>启用</option>
                                <option value="inactive" {{ old('status', $sdk->status) == 'inactive' ? 'selected' : '' }}>禁用</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">保存</button>
                        <a href="{{ route('admin.security.api.sdks.show', $sdk->id) }}" class="btn btn-default">取消</a>
                        <button type="button" class="btn btn-success float-right" data-toggle="modal" data-target="#generateSdkModal">
                            <i class="fas fa-code"></i> 生成SDK
                        </button>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">SDK信息</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>SDK ID</th>
                            <td>{{ $sdk->id }}</td>
                        </tr>
                        <tr>
                            <th>当前版本</th>
                            <td>
                                @if($sdk->currentVersion)
                                    <span class="badge badge-success">{{ $sdk->currentVersion->version }}</span>
                                @else
                                    <span class="badge badge-secondary">无版本</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>创建时间</th>
                            <td>{{ $sdk->created_at }}</td>
                        </tr>
                        <tr>
                            <th>最后更新</th>
                            <td>{{ $sdk->updated_at }}</td>
                        </tr>
                    </table>
                    
                    <div class="alert alert-info mt-3">
                        <h5><i class="icon fas fa-info"></i> 提示</h5>
                        <p>修改SDK配置后，需要重新生成SDK才能应用更改。</p>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">版本列表</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>版本</th>
                                    <th>下载次数</th>
                                    <th>创建时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sdk->versions as $version)
                                <tr>
                                    <td>
                                        {{ $version->version }}
                                        @if($version->is_current)
                                            <span class="badge badge-success">当前</span>
                                        @endif
                                    </td>
                                    <td>{{ $version->download_count }}</td>
                                    <td>{{ $version->created_at->format('Y-m-d') }}</td>
                                </tr>
                                @endforeach
                                
                                @if($sdk->versions->isEmpty())
                                <tr>
                                    <td colspan="3" class="text-center">暂无版本</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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
            <form action="{{ route('admin.security.api.sdks.generate', $sdk->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>SDK名称</label>
                        <input type="text" class="form-control" value="{{ $sdk->name }}" disabled>
                    </div>
                    <div class="form-group">
                        <label>语言</label>
                        <input type="text" class="form-control" value="{{ $sdk->language_name }}" disabled>
                    </div>
                    <div class="form-group">
                        <label for="version">版本号 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="version" name="version" placeholder="例如：1.0.0" required>
                        <small class="form-text text-muted">请使用语义化版本号，例如：1.0.0、1.0.1、1.1.0等</small>
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
                                    <input class="form-check-input" type="checkbox" id="modal-select-all-interfaces">
                                    <label class="form-check-label font-weight-bold" for="modal-select-all-interfaces">
                                        全选
                                    </label>
                                </div>
                                <hr>
                                @foreach($sdk->interfaces as $interface)
                                <div class="form-check">
                                    <input class="form-check-input modal-interface-checkbox" type="checkbox" id="modal_interface_{{ $interface->id }}" name="interfaces[]" value="{{ $interface->id }}" checked>
                                    <label class="form-check-label" for="modal_interface_{{ $interface->id }}">
                                        {{ $interface->name }} <small class="text-muted">({{ $interface->path }})</small>
                                    </label>
                                </div>
                                @endforeach
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
        // 全选/取消全选接口
        $('#select-all-interfaces').change(function() {
            $('.interface-checkbox').prop('checked', this.checked);
        });
        
        // 检查是否所有接口都被选中
        $('.interface-checkbox').change(function() {
            var allChecked = $('.interface-checkbox:checked').length === $('.interface-checkbox').length;
            $('#select-all-interfaces').prop('checked', allChecked);
        });
        
        // 初始化时检查是否所有接口都被选中
        var allChecked = $('.interface-checkbox:checked').length === $('.interface-checkbox').length;
        $('#select-all-interfaces').prop('checked', allChecked);
        
        // 模态框中的全选/取消全选接口
        $('#modal-select-all-interfaces').change(function() {
            $('.modal-interface-checkbox').prop('checked', this.checked);
        });
        
        // 模态框中检查是否所有接口都被选中
        $('.modal-interface-checkbox').change(function() {
            var allChecked = $('.modal-interface-checkbox:checked').length === $('.modal-interface-checkbox').length;
            $('#modal-select-all-interfaces').prop('checked', allChecked);
        });
        
        // 模态框初始化时检查是否所有接口都被选中
        var modalAllChecked = $('.modal-interface-checkbox:checked').length === $('.modal-interface-checkbox').length;
        $('#modal-select-all-interfaces').prop('checked', modalAllChecked);
    });
</script>
@endsection 