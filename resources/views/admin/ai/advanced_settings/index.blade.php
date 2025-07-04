@extends("admin.layouts.app")

@section("title", "AI高级设置")

@section("content_header")
    <h1>AI高级设置</h1>
@stop

@section("content")
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">AI接口高级设置</h3>
                <div>
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#resetSettingsModal">
                        <i class="fas fa-undo"></i> 重置为默认值
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route("admin.ai.advanced-settings.update") }}" method="POST">
                @csrf
                @method("PUT")
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">API密钥轮换和负载均衡</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="enable_api_key_rotation">启用API密钥轮换</label>
                                    <select class="form-control" id="enable_api_key_rotation" name="enable_api_key_rotation">
                                        <option value="0" {{ $apiKeySettings["enable_api_key_rotation"] ? "" : "selected" }}>禁用</option>
                                        <option value="1" {{ $apiKeySettings["enable_api_key_rotation"] ? "selected" : "" }}>启用</option>
                                    </select>
                                    <small class="form-text text-muted">启用后，系统会自动轮换使用同一提供商的多个API密钥</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="rotation_strategy">轮换策略</label>
                                    <select class="form-control" id="rotation_strategy" name="rotation_strategy">
                                        <option value="round_robin" {{ $apiKeySettings["rotation_strategy"] == "round_robin" ? "selected" : "" }}>轮询 (Round Robin)</option>
                                        <option value="random" {{ $apiKeySettings["rotation_strategy"] == "random" ? "selected" : "" }}>随机 (Random)</option>
                                        <option value="weighted" {{ $apiKeySettings["rotation_strategy"] == "weighted" ? "selected" : "" }}>加权 (Weighted)</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <ul>
                                            <li>轮询：按顺序轮流使用密钥</li>
                                            <li>随机：随机选择密钥</li>
                                            <li>加权：根据使用次数的倒数作为权重，使用次数越少权重越高</li>
                                        </ul>
                                    </small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="enable_load_balancing">启用负载均衡</label>
                                    <select class="form-control" id="enable_load_balancing" name="enable_load_balancing">
                                        <option value="0" {{ $apiKeySettings["enable_load_balancing"] ? "" : "selected" }}>禁用</option>
                                        <option value="1" {{ $apiKeySettings["enable_load_balancing"] ? "selected" : "" }}>启用</option>
                                    </select>
                                    <small class="form-text text-muted">启用后，系统会根据负载情况分配请求</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="load_balancing_strategy">负载均衡策略</label>
                                    <select class="form-control" id="load_balancing_strategy" name="load_balancing_strategy">
                                        <option value="least_used" {{ $apiKeySettings["load_balancing_strategy"] == "least_used" ? "selected" : "" }}>最少使用 (Least Used)</option>
                                        <option value="percentage" {{ $apiKeySettings["load_balancing_strategy"] == "percentage" ? "selected" : "" }}>百分比 (Percentage)</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <ul>
                                            <li>最少使用：优先使用使用次数最少的密钥</li>
                                            <li>百分比：根据密钥使用次数的百分比分配请求</li>
                                        </ul>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0">缓存和故障转移</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="enable_request_caching">启用请求缓存</label>
                                    <select class="form-control" id="enable_request_caching" name="enable_request_caching">
                                        <option value="0" {{ $cachingSettings["enable_request_caching"] ? "" : "selected" }}>禁用</option>
                                        <option value="1" {{ $cachingSettings["enable_request_caching"] ? "selected" : "" }}>启用</option>
                                    </select>
                                    <small class="form-text text-muted">启用后，系统会缓存相同请求的响应，减少API调用次数</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="request_cache_ttl">请求缓存有效期（分钟）</label>
                                    <input type="number" class="form-control" id="request_cache_ttl" name="request_cache_ttl" value="{{ $cachingSettings["request_cache_ttl"] }}" min="1">
                                    <small class="form-text text-muted">缓存的有效期，单位为分钟</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="enable_fallback">启用故障转移</label>
                                    <select class="form-control" id="enable_fallback" name="enable_fallback">
                                        <option value="0" {{ $fallbackSettings["enable_fallback"] ? "" : "selected" }}>禁用</option>
                                        <option value="1" {{ $fallbackSettings["enable_fallback"] ? "selected" : "" }}>启用</option>
                                    </select>
                                    <small class="form-text text-muted">启用后，当主要提供商API调用失败时，会自动尝试使用备用提供商</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="fallback_provider">备用AI提供商</label>
                                    <select class="form-control" id="fallback_provider" name="fallback_provider">
                                        <option value="">无备用提供商</option>
                                        @foreach($providers as $provider)
                                            <option value="{{ $provider->id }}" {{ $fallbackSettings["fallback_provider"] == $provider->id ? "selected" : "" }}>
                                                {{ $provider->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">主要提供商不可用时使用的备用提供商</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">日志和审计</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="enable_detailed_logging">启用详细日志记录</label>
                                    <select class="form-control" id="enable_detailed_logging" name="enable_detailed_logging">
                                        <option value="0" {{ $loggingSettings["enable_detailed_logging"] ? "" : "selected" }}>禁用</option>
                                        <option value="1" {{ $loggingSettings["enable_detailed_logging"] ? "selected" : "" }}>启用</option>
                                    </select>
                                    <small class="form-text text-muted">启用后，系统会记录详细的API调用日志，包括请求和响应数据</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="log_retention_days">日志保留天数</label>
                                    <input type="number" class="form-control" id="log_retention_days" name="log_retention_days" value="{{ $loggingSettings["log_retention_days"] }}" min="1">
                                    <small class="form-text text-muted">系统自动清理超过保留天数的日志</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="enable_audit_logging">启用审计日志记录</label>
                                    <select class="form-control" id="enable_audit_logging" name="enable_audit_logging">
                                        <option value="0" {{ $loggingSettings["enable_audit_logging"] ? "" : "selected" }}>禁用</option>
                                        <option value="1" {{ $loggingSettings["enable_audit_logging"] ? "selected" : "" }}>启用</option>
                                    </select>
                                    <small class="form-text text-muted">启用后，系统会记录管理员对AI接口的所有操作</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle"></i> 日志功能可以帮助您监控和审计AI接口的使用情况，但会占用一定的存储空间。建议根据实际需求配置日志保留天数。
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> 保存设置
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- 重置设置确认模态框 -->
    <div class="modal fade" id="resetSettingsModal" tabindex="-1" role="dialog" aria-labelledby="resetSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resetSettingsModalLabel">确认重置</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>确定要将所有高级设置重置为默认值吗？此操作不可撤销。</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <form action="{{ route("admin.ai.advanced-settings.reset") }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger">确认重置</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
