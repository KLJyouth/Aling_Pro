@extends("layouts.app")

@section("title", "历史对话")

@section("content")
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            @include("user.partials.sidebar")
        </div>
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">历史对话</h5>
                    <div>
                        <a href="{{ route("chat.new") }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> 新建对话
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(session("success"))
                        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                            {{ session("success") }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
                        </div>
                    @endif
                    
                    @if($conversations->isEmpty())
                        <div class="text-center py-5">
                            <img src="{{ asset("images/empty-state.svg") }}" alt="暂无对话" class="img-fluid mb-3" style="max-width: 200px;">
                            <h5>暂无历史对话</h5>
                            <p class="text-muted">开始与AI助手对话，记录将显示在这里</p>
                            <a href="{{ route("chat.new") }}" class="btn btn-primary">
                                <i class="fas fa-comments"></i> 开始对话
                            </a>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($conversations as $conversation)
                                <a href="{{ route("user.conversations.show", $conversation->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $conversation->title }}</h6>
                                        <p class="mb-1 text-muted small">
                                            <i class="far fa-clock"></i> {{ $conversation->updated_at->diffForHumans() }}
                                            <span class="mx-1"></span>
                                            <i class="far fa-comment"></i> {{ $conversation->messages_count ?? "0" }} 条消息
                                        </p>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">{{ $conversation->model }}</span>
                                </a>
                            @endforeach
                        </div>
                        
                        <div class="d-flex justify-content-center py-3">
                            {{ $conversations->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
