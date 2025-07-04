@extends("layouts.app")

@section("title", $conversation->title)

@section("styles")
<style>
    .message-container {
        max-height: 70vh;
        overflow-y: auto;
        padding: 1rem;
    }
    .message {
        margin-bottom: 1.5rem;
        padding: 1rem;
        border-radius: 0.5rem;
    }
    .message-user {
        background-color: #f0f0f0;
    }
    .message-assistant {
        background-color: #e6f7ff;
    }
    .message-system {
        background-color: #fff3cd;
        font-style: italic;
    }
    .message-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }
    .message-content {
        white-space: pre-wrap;
    }
    .message-time {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .export-dropdown .dropdown-menu {
        min-width: 8rem;
    }
</style>
@endsection

@section("content")
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            @include("user.partials.sidebar")
        </div>
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route("user.conversations.index") }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <span class="fw-bold">{{ $conversation->title }}</span>
                    </div>
                    <div class="d-flex">
                        <div class="dropdown export-dropdown me-2">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-download"></i> 导出
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                <li><a class="dropdown-item" href="{{ route("user.conversations.export", ["id" => $conversation->id, "format" => "txt"]) }}">文本文件 (.txt)</a></li>
                                <li><a class="dropdown-item" href="{{ route("user.conversations.export", ["id" => $conversation->id, "format" => "markdown"]) }}">Markdown (.md)</a></li>
                                <li><a class="dropdown-item" href="{{ route("user.conversations.export", ["id" => $conversation->id, "format" => "html"]) }}">HTML 文件 (.html)</a></li>
                                <li><a class="dropdown-item" href="{{ route("user.conversations.export", ["id" => $conversation->id, "format" => "json"]) }}">JSON 文件 (.json)</a></li>
                            </ul>
                        </div>
                        <form action="{{ route("user.conversations.delete", $conversation->id) }}" method="POST" onsubmit="return confirm("确定要删除这个对话吗？此操作不可恢复。")">
                            @csrf
                            @method("DELETE")
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i> 删除
                            </button>
                        </form>
                    </div>
                </div>
                <div class="message-container">
                    @foreach($messages as $message)
                        <div class="message message-{{ $message->role }}">
                            <div class="message-header">
                                <div>
                                    @if($message->role == "user")
                                        <strong>我</strong>
                                    @elseif($message->role == "assistant")
                                        <strong>AI助手</strong>
                                    @else
                                        <strong>系统</strong>
                                    @endif
                                </div>
                                <div class="message-time">
                                    {{ $message->created_at->format("Y-m-d H:i:s") }}
                                </div>
                            </div>
                            <div class="message-content">
                                {{ $message->content }}
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="card-footer bg-white">
                    <a href="{{ route("chat.continue", $conversation->id) }}" class="btn btn-primary">
                        <i class="fas fa-reply"></i> 继续对话
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
