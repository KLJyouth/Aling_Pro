@extends('layouts.user')

@section('title', '���ʹ�����')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">���ʹ�����</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- API��� -->
                        <div class="col-md-6 col-lg-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">API���ö��</h5>
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
                                        ��ʹ��: {{ $currentUsage['api']['used'] }} / {{ $currentUsage['api']['total'] }}
                                    </p>
                                    <button class="btn btn-sm btn-primary" 
                                        onclick="loadQuotaStats('api', 'month')">
                                        �鿴ͳ��
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
