@extends('admin.layouts.app')

@section('title', '创建API SDK')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">创建API SDK</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form role="form" method="POST" action="{{ route('admin.security.api.sdks.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">SDK名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="请输入SDK名称" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="slug">标识符 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" placeholder="请输入标识符" value="{{ old('slug') }}" required>
                            @error('slug')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">用于URL和文件名，只能包含字母、数字、连字符和下划线</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="language">语言 <span class="text-danger">*</span></label>
                            <select class="form-control @error('language') is-invalid @enderror" id="language" name="language" required>
                                <option value="">选择语言</option>
                                <option value="php" {{ old('language') == 'php' ? 'selected' : '' }}>PHP</option>
                                <option value="python" {{ old('language') == 'python' ? 'selected' : '' }}>Python</option>
                                <option value="javascript" {{ old('language') == 'javascript' ? 'selected' : '' }}>JavaScript</option>
                                <option value="java" {{ old('language') == 'java' ? 'selected' : '' }}>Java</option>
                                <option value="csharp" {{ old('language') == 'csharp' ? 'selected' : '' }}>C#</option>
                                <option value="go" {{ old('language') == 'go' ? 'selected' : '' }}>Go</option>
                            </select>
                            @error('language')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="description">描述</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="请输入SDK描述">{{ old('description') }}</textarea>
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
                                            <input class="form-check-input interface-checkbox" type="checkbox" id="interface_{{ $interface->id }}" name="interfaces[]" value="{{ $interface->id }}" {{ in_array($interface->id, old('interfaces', [])) ? 'checked' : '' }}>
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
                                <div id="php-options" class="language-options">
                                    <div class="form-group">
                                        <label for="php_namespace">命名空间</label>
                                        <input type="text" class="form-control" id="php_namespace" name="options[php][namespace]" placeholder="例如：AlingAi\SDK" value="{{ old('options.php.namespace') }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="php_package_name">包名</label>
                                        <input type="text" class="form-control" id="php_package_name" name="options[php][package_name]" placeholder="例如：alingai/api-sdk" value="{{ old('options.php.package_name') }}">
                                    </div>
                                </div>
                                
                                <div id="python-options" class="language-options">
                                    <div class="form-group">
                                        <label for="python_package_name">包名</label>
                                        <input type="text" class="form-control" id="python_package_name" name="options[python][package_name]" placeholder="例如：alingai_sdk" value="{{ old('options.python.package_name') }}">
                                    </div>
                                </div>
                                
                                <div id="javascript-options" class="language-options">
                                    <div class="form-group">
                                        <label for="javascript_package_name">包名</label>
                                        <input type="text" class="form-control" id="javascript_package_name" name="options[javascript][package_name]" placeholder="例如：alingai-sdk" value="{{ old('options.javascript.package_name') }}">
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="javascript_typescript" name="options[javascript][typescript]" {{ old('options.javascript.typescript') ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="javascript_typescript">包含TypeScript定义</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="java-options" class="language-options">
                                    <div class="form-group">
                                        <label for="java_package_name">包名</label>
                                        <input type="text" class="form-control" id="java_package_name" name="options[java][package_name]" placeholder="例如：com.alingai.sdk" value="{{ old('options.java.package_name') }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="java_group_id">Group ID</label>
                                        <input type="text" class="form-control" id="java_group_id" name="options[java][group_id]" placeholder="例如：com.alingai" value="{{ old('options.java.group_id') }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="java_artifact_id">Artifact ID</label>
                                        <input type="text" class="form-control" id="java_artifact_id" name="options[java][artifact_id]" placeholder="例如：api-sdk" value="{{ old('options.java.artifact_id') }}">
                                    </div>
                                </div>
                                
                                <div id="csharp-options" class="language-options">
                                    <div class="form-group">
                                        <label for="csharp_namespace">命名空间</label>
                                        <input type="text" class="form-control" id="csharp_namespace" name="options[csharp][namespace]" placeholder="例如：AlingAi.Sdk" value="{{ old('options.csharp.namespace') }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="csharp_project_name">项目名称</label>
                                        <input type="text" class="form-control" id="csharp_project_name" name="options[csharp][project_name]" placeholder="例如：AlingAi.Sdk" value="{{ old('options.csharp.project_name') }}">
                                    </div>
                                </div>
                                
                                <div id="go-options" class="language-options">
                                    <div class="form-group">
                                        <label for="go_package_name">包名</label>
                                        <input type="text" class="form-control" id="go_package_name" name="options[go][package_name]" placeholder="例如：alingaisdk" value="{{ old('options.go.package_name') }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="go_module_name">模块名称</label>
                                        <input type="text" class="form-control" id="go_module_name" name="options[go][module_name]" placeholder="例如：github.com/alingai/sdk" value="{{ old('options.go.module_name') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-3">
                            <label for="status">状态</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>启用</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>禁用</option>
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
                        <button type="submit" class="btn btn-primary">创建</button>
                        <a href="{{ route('admin.security.api.sdks.index') }}" class="btn btn-default">取消</a>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">SDK说明</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> 关于API SDK</h5>
                        <p>API SDK是为开发者提供的工具包，可以简化API的调用过程。创建SDK后，您可以生成不同语言的SDK包供开发者下载使用。</p>
                    </div>
                    <h5>支持的语言</h5>
                    <ul>
                        <li><strong>PHP</strong> - 适用于PHP 7.2+的项目</li>
                        <li><strong>Python</strong> - 适用于Python 3.6+的项目</li>
                        <li><strong>JavaScript</strong> - 支持Node.js和浏览器环境</li>
                        <li><strong>Java</strong> - 适用于Java 8+的项目</li>
                        <li><strong>C#</strong> - 适用于.NET Core和.NET Framework项目</li>
                        <li><strong>Go</strong> - 适用于Go 1.13+的项目</li>
                    </ul>
                    <h5>SDK生成流程</h5>
                    <ol>
                        <li>创建SDK配置</li>
                        <li>选择要包含的API接口</li>
                        <li>设置语言特定的选项</li>
                        <li>生成SDK包</li>
                        <li>发布SDK供开发者下载</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // 根据选择的语言显示对应的选项
        $('.language-options').hide();
        
        $('#language').change(function() {
            $('.language-options').hide();
            var language = $(this).val();
            if (language) {
                $('#' + language + '-options').show();
            }
        });
        
        // 初始化时显示已选语言的选项
        var selectedLanguage = $('#language').val();
        if (selectedLanguage) {
            $('#' + selectedLanguage + '-options').show();
        }
        
        // 自动生成标识符
        $('#name').on('input', function() {
            var slug = $(this).val()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            $('#slug').val(slug);
        });
        
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