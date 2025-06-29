@extends('admin.layouts.app')

@section('title', '添加风控规则')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">添加风控规则</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.security.api.risk-rules.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-left"></i> 返回
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <form method="POST" action="{{ route('admin.security.api.risk-rules.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">规则名称</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="输入规则名称" required>
                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="api_interface_id">关联接口</label>
                                    <select class="form-control @error('api_interface_id') is-invalid @enderror" id="api_interface_id" name="api_interface_id">
                                        <option value="">全局规则（适用于所有接口）</option>
                                        @foreach($interfaces as $interface)
                                        <option value="{{ $interface->id }}" {{ old('api_interface_id', request('interface_id')) == $interface->id ? 'selected' : '' }}>{{ $interface->name }} ({{ $interface->method }} /api/{{ $interface->path }})</option>
                                        @endforeach
                                    </select>
                                    @error('api_interface_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="risk_type">风险类型</label>
                                    <select class="form-control @error('risk_type') is-invalid @enderror" id="risk_type" name="risk_type" required>
                                        <option value="">选择风险类型</option>
                                        <option value="rate_limit" {{ old('risk_type') == 'rate_limit' ? 'selected' : '' }}>频率限制</option>
                                        <option value="ip_blacklist" {{ old('risk_type') == 'ip_blacklist' ? 'selected' : '' }}>IP黑名单</option>
                                        <option value="parameter_check" {{ old('risk_type') == 'parameter_check' ? 'selected' : '' }}>参数检查</option>
                                        <option value="sql_injection" {{ old('risk_type') == 'sql_injection' ? 'selected' : '' }}>SQL注入</option>
                                        <option value="xss_attack" {{ old('risk_type') == 'xss_attack' ? 'selected' : '' }}>XSS攻击</option>
                                        <option value="unauthorized_access" {{ old('risk_type') == 'unauthorized_access' ? 'selected' : '' }}>未授权访问</option>
                                        <option value="abnormal_behavior" {{ old('risk_type') == 'abnormal_behavior' ? 'selected' : '' }}>异常行为</option>
                                        <option value="custom" {{ old('risk_type') == 'custom' ? 'selected' : '' }}>自定义规则</option>
                                    </select>
                                    @error('risk_type')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="risk_level">风险等级</label>
                                    <select class="form-control @error('risk_level') is-invalid @enderror" id="risk_level" name="risk_level" required>
                                        <option value="low" {{ old('risk_level') == 'low' ? 'selected' : '' }}>低</option>
                                        <option value="medium" {{ old('risk_level') == 'medium' ? 'selected' : '' }}>中</option>
                                        <option value="high" {{ old('risk_level') == 'high' ? 'selected' : '' }}>高</option>
                                    </select>
                                    @error('risk_level')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="description">规则描述</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="输入规则描述">{{ old('description') }}</textarea>
                                    @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="status">状态</label>
                                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>启用</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>禁用</option>
                                    </select>
                                    @error('status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="action">触发动作</label>
                                    <select class="form-control @error('action') is-invalid @enderror" id="action" name="action" required>
                                        <option value="log" {{ old('action') == 'log' ? 'selected' : '' }}>仅记录日志</option>
                                        <option value="block" {{ old('action') == 'block' ? 'selected' : '' }}>阻止请求</option>
                                        <option value="captcha" {{ old('action') == 'captcha' ? 'selected' : '' }}>要求验证码</option>
                                        <option value="add_to_blacklist" {{ old('action') == 'add_to_blacklist' ? 'selected' : '' }}>加入黑名单</option>
                                        <option value="custom_action" {{ old('action') == 'custom_action' ? 'selected' : '' }}>自定义动作</option>
                                    </select>
                                    @error('action')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="priority">优先级</label>
                                    <input type="number" class="form-control @error('priority') is-invalid @enderror" id="priority" name="priority" value="{{ old('priority', 10) }}" min="1" max="100">
                                    @error('priority')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                    <small class="form-text text-muted">数字越小优先级越高，范围1-100</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">规则配置</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="rule-config" id="rate_limit_config" style="display: none;">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="rate_limit_count">请求次数</label>
                                                        <input type="number" class="form-control" id="rate_limit_count" name="config[rate_limit_count]" value="{{ old('config.rate_limit_count', 60) }}" min="1">
                                                        <small class="form-text text-muted">在指定时间窗口内允许的最大请求次数</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="rate_limit_period">时间窗口（秒）</label>
                                                        <input type="number" class="form-control" id="rate_limit_period" name="config[rate_limit_period]" value="{{ old('config.rate_limit_period', 60) }}" min="1">
                                                        <small class="form-text text-muted">统计请求次数的时间窗口，单位为秒</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="rate_limit_per_ip" name="config[rate_limit_per_ip]" value="1" {{ old('config.rate_limit_per_ip') ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="rate_limit_per_ip">按IP限制</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="rate_limit_per_user" name="config[rate_limit_per_user]" value="1" {{ old('config.rate_limit_per_user') ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="rate_limit_per_user">按用户限制</label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="rule-config" id="parameter_check_config" style="display: none;">
                                            <div class="form-group">
                                                <label for="param_name">参数名称</label>
                                                <input type="text" class="form-control" id="param_name" name="config[param_name]" value="{{ old('config.param_name') }}" placeholder="输入需要检查的参数名称">
                                            </div>
                                            <div class="form-group">
                                                <label for="check_type">检查类型</label>
                                                <select class="form-control" id="check_type" name="config[check_type]">
                                                    <option value="required" {{ old('config.check_type') == 'required' ? 'selected' : '' }}>必填</option>
                                                    <option value="regex" {{ old('config.check_type') == 'regex' ? 'selected' : '' }}>正则表达式</option>
                                                    <option value="length" {{ old('config.check_type') == 'length' ? 'selected' : '' }}>长度限制</option>
                                                    <option value="enum" {{ old('config.check_type') == 'enum' ? 'selected' : '' }}>枚举值</option>
                                                    <option value="type" {{ old('config.check_type') == 'type' ? 'selected' : '' }}>类型检查</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="check_value">检查值</label>
                                                <input type="text" class="form-control" id="check_value" name="config[check_value]" value="{{ old('config.check_value') }}" placeholder="输入检查值，如正则表达式、长度范围或枚举值列表">
                                                <small class="form-text text-muted">根据检查类型填写不同的值：正则表达式、长度范围(如10-100)、枚举值(如value1,value2,value3)等</small>
                                            </div>
                                        </div>
                                        
                                        <div class="rule-config" id="custom_config" style="display: none;">
                                            <div class="form-group">
                                                <label for="rule_condition">规则条件（JSON格式）</label>
                                                <textarea class="form-control" id="rule_condition" name="config[rule_condition]" rows="5" placeholder='{"operator": "AND", "conditions": [{"field": "ip_address", "operator": "in", "value": ["192.168.1.1", "10.0.0.1"]}, {"field": "user_agent", "operator": "contains", "value": "Bot"}]}'>{{ old('config.rule_condition') }}</textarea>
                                                <small class="form-text text-muted">使用JSON格式定义规则条件，支持AND/OR逻辑组合多个条件</small>
                                            </div>
                                        </div>
                                        
                                        <div class="rule-config" id="action_config" style="display: none;">
                                            <div class="form-group">
                                                <label for="blacklist_duration">黑名单时长（小时）</label>
                                                <input type="number" class="form-control" id="blacklist_duration" name="config[blacklist_duration]" value="{{ old('config.blacklist_duration', 24) }}" min="1">
                                                <small class="form-text text-muted">加入黑名单的时长，单位为小时，设置为0表示永久</small>
                                            </div>
                                            <div class="form-group">
                                                <label for="custom_action_code">自定义动作代码</label>
                                                <textarea class="form-control" id="custom_action_code" name="config[custom_action_code]" rows="3" placeholder="输入自定义动作的PHP代码">{{ old('config.custom_action_code') }}</textarea>
                                                <small class="form-text text-muted">当选择自定义动作时，可以在这里输入PHP代码来定义具体的动作</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">提交</button>
                        <a href="{{ route('admin.security.api.risk-rules.index') }}" class="btn btn-default">取消</a>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // 根据风险类型显示不同的配置表单
        function updateRuleConfigForm() {
            var riskType = $('#risk_type').val();
            $('.rule-config').hide();
            
            if (riskType === 'rate_limit') {
                $('#rate_limit_config').show();
            } else if (riskType === 'parameter_check') {
                $('#parameter_check_config').show();
            } else if (riskType === 'custom') {
                $('#custom_config').show();
            }
            
            // 根据动作类型显示不同的动作配置
            var action = $('#action').val();
            if (action === 'add_to_blacklist' || action === 'custom_action') {
                $('#action_config').show();
            }
        }
        
        $('#risk_type').on('change', updateRuleConfigForm);
        $('#action').on('change', updateRuleConfigForm);
        
        // 初始化表单
        updateRuleConfigForm();
        
        // 自动格式化JSON
        $('#rule_condition').on('blur', function() {
            try {
                var value = $(this).val();
                if (value) {
                    var formatted = JSON.stringify(JSON.parse(value), null, 2);
                    $(this).val(formatted);
                }
            } catch (e) {
                // 如果不是有效的JSON，保持原样
            }
        });
    });
</script>
@endsection 