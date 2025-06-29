@extends('admin.layouts.app')

@section('title', 'SDK详情')

@section('content')
<div class="container-fluid">
    @if(session('sdk_generated'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h5><i class="icon fas fa-check"></i> SDK生成成功!</h5>
        <p>SDK已成功生成，版本：{{ session('sdk_version') }}</p>
    </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">SDK详情</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.security.api.sdks.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-left"></i> 返回
                        </a>
                        <a href="{{ route('admin.security.api.sdks.edit', $sdk->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> 编辑
                        </a>
                        <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#generateSdkModal">
                            <i class="fas fa-code"></i> 生成SDK
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 150px;">SDK ID</th>
                                    <td>{{ $sdk->id }}</td>
                                </tr>
                                <tr>
                                    <th>名称</th>
                                    <td>{{ $sdk->name }}</td>
                                </tr>
                                <tr>
                                    <th>标识符</th>
                                    <td>{{ $sdk->slug }}</td>
                                </tr>
                                <tr>
                                    <th>语言</th>
                                    <td>
                                        <span class="badge badge-info">{{ $sdk->language_name }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>状态</th>
                                    <td>
                                        @if($sdk->status == 'active')
                                            <span class="badge badge-success">启用</span>
                                        @else
                                            <span class="badge badge-danger">禁用</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 150px;">当前版本</th>
                                    <td>
                                        @if($sdk->currentVersion)
                                            <span class="badge badge-success">{{ $sdk->currentVersion->version }}</span>
                                        @else
                                            <span class="badge badge-secondary">无版本</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>接口数量</th>
                                    <td>{{ $sdk->interfaces->count() }}</td>
                                </tr>
                                <tr>
                                    <th>总下载次数</th>
                                    <td>{{ $sdk->versions->sum('download_count') }}</td>
                                </tr>
                                <tr>
                                    <th>创建时间</th>
                                    <td>{{ $sdk->created_at }}</td>
                                </tr>
                                <tr>
                                    <th>更新时间</th>
                                    <td>{{ $sdk->updated_at }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($sdk->description)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">描述</h4>
                                </div>
                                <div class="card-body">
                                    {{ $sdk->description }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">包含的API接口</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>名称</th>
                                                    <th>路径</th>
                                                    <th>方法</th>
                                                    <th>状态</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($sdk->interfaces as $interface)
                                                <tr>
                                                    <td>{{ $interface->id }}</td>
                                                    <td>{{ $interface->name }}</td>
                                                    <td><code>{{ $interface->path }}</code></td>
                                                    <td>
                                                        @foreach(explode(',', $interface->methods) as $method)
                                                            <span class="badge badge-primary">{{ $method }}</span>
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        @if($interface->status == 'active')
                                                            <span class="badge badge-success">启用</span>
                                                        @else
                                                            <span class="badge badge-danger">禁用</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">SDK配置选项</h4>
                                </div>
                                <div class="card-body">
                                    @if($sdk->options)
                                        <pre><code>{{ json_encode(json_decode($sdk->options), JSON_PRETTY_PRINT) }}</code></pre>
                                    @else
                                        <p class="text-muted">未设置配置选项</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.security.api.sdks.edit', $sdk->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> 编辑
                    </a>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#generateSdkModal">
                        <i class="fas fa-code"></i> 生成SDK
                    </button>
                    <form action="{{ route('admin.security.api.sdks.destroy', $sdk->id) }}" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('确定要删除该SDK吗？')">
                            <i class="fas fa-trash"></i> 删除
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">SDK版本</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#generateSdkModal">
                            <i class="fas fa-plus"></i> 新版本
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>版本</th>
                                    <th>下载次数</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
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
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.security.api.sdks.download', ['id' => $sdk->id, 'version_id' => $version->id]) }}" class="btn btn-xs btn-info">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            @if(!$version->is_current)
                                                <form action="{{ route('admin.security.api.sdks.set-current', ['id' => $sdk->id, 'version_id' => $version->id]) }}" method="POST" style="display: inline-block;">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-xs btn-success">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('admin.security.api.sdks.delete-version', ['id' => $sdk->id, 'version_id' => $version->id]) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('确定要删除该版本吗？')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                
                                @if($sdk->versions->isEmpty())
                                <tr>
                                    <td colspan="4" class="text-center">暂无版本</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">SDK文档</h3>
                </div>
                <div class="card-body">
                    <p>查看SDK的使用文档和示例代码：</p>
                    <a href="{{ route('admin.security.api.sdks.documentation', $sdk->id) }}" class="btn btn-info btn-block">
                        <i class="fas fa-book"></i> 查看文档
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">SDK集成指南</h3>
                </div>
                <div class="card-body">
                    <h5>{{ $sdk->language_name }} SDK安装</h5>
                    
                    @if($sdk->language == 'php')
                    <div class="mb-3">
                        <pre><code>composer require {{ $options['php']['package_name'] ?? 'alingai/api-sdk' }}</code></pre>
                    </div>
                    @elseif($sdk->language == 'python')
                    <div class="mb-3">
                        <pre><code>pip install {{ $options['python']['package_name'] ?? 'alingai-sdk' }}</code></pre>
                    </div>
                    @elseif($sdk->language == 'javascript')
                    <div class="mb-3">
                        <pre><code>npm install {{ $options['javascript']['package_name'] ?? 'alingai-sdk' }}</code></pre>
                    </div>
                    @elseif($sdk->language == 'java')
                    <div class="mb-3">
                        <pre><code>&lt;dependency&gt;
    &lt;groupId&gt;{{ $options['java']['group_id'] ?? 'com.alingai' }}&lt;/groupId&gt;
    &lt;artifactId&gt;{{ $options['java']['artifact_id'] ?? 'api-sdk' }}&lt;/artifactId&gt;
    &lt;version&gt;{{ $sdk->currentVersion ? $sdk->currentVersion->version : '1.0.0' }}&lt;/version&gt;
&lt;/dependency&gt;</code></pre>
                    </div>
                    @elseif($sdk->language == 'csharp')
                    <div class="mb-3">
                        <pre><code>dotnet add package {{ $options['csharp']['project_name'] ?? 'AlingAi.Sdk' }}</code></pre>
                    </div>
                    @elseif($sdk->language == 'go')
                    <div class="mb-3">
                        <pre><code>go get {{ $options['go']['module_name'] ?? 'github.com/alingai/sdk' }}</code></pre>
                    </div>
                    @endif
                    
                    <p>查看完整的集成指南和示例代码，请访问SDK文档。</p>
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
                                    <input class="form-check-input" type="checkbox" id="select-all-interfaces">
                                    <label class="form-check-label font-weight-bold" for="select-all-interfaces">
                                        全选
                                    </label>
                                </div>
                                <hr>
                                @foreach($sdk->interfaces as $interface)
                                <div class="form-check">
                                    <input class="form-check-input interface-checkbox" type="checkbox" id="interface_{{ $interface->id }}" name="interfaces[]" value="{{ $interface->id }}" checked>
                                    <label class="form-check-label" for="interface_{{ $interface->id }}">
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
    });
</script>
@endsection 