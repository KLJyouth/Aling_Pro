@extends('layouts.user')

@section('title', '额度使用情况')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">额度使用情况</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- API额度 -->
                        <div class="col-md-6 col-lg-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">API调用额度</h5>
                                    <div class="progress mb-3">
                                        <div class="progress-bar" role="progressbar" 
                                            style="width: {{ $currentUsage['api']['percent'] }}%;" 
                                            aria-valuenow="{{ $currentUsage['api']['percent'] }}" 
                                            aria-valuemin="0" 
                                            aria-valuemax="100">
                                            {{ $currentUsage['api']['percent'] }}%
                                        </div>
                                    </div>
                                    <p class="card-text">
                                        已使用: {{ $currentUsage['api']['used'] }} / {{ $currentUsage['api']['total'] }}
                                    </p>
                                    <button class="btn btn-sm btn-primary" 
                                        onclick="loadQuotaStats('api', 'month')">
                                        查看统计
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
