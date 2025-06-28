@extends('admin.news.layout')

@section('title', isset($news) ? '编辑新闻' : '添加新闻')

@section('styles')
<style>
    .note-editor {
        margin-bottom: 20px;
    }
    .dropzone {
        border: 2px dashed #0087F7;
        border-radius: 5px;
        background: #F9F9F9;
        min-height: 150px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .bootstrap-tagsinput {
        width: 100%;
        padding: 8px 12px;
    }
    .bootstrap-tagsinput .tag {
        margin-right: 2px;
        color: white;
        background-color: #0d6efd;
        padding: 2px 5px;
        border-radius: 3px;
    }
    .preview-image {
        max-width: 200px;
        max-height: 200px;
        margin-top: 10px;
    }
    .seo-counter {
        font-size: 12px;
        margin-top: 5px;
    }
    .good-count {
        color: green;
    }
    .warning-count {
        color: orange;
    }
    .danger-count {
        color: red;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ isset($news) ? '编辑新闻' : '添加新闻' }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ isset($news) ? route('admin.news.update', $news->id) : route('admin.news.store') }}" method="POST" enctype="multipart/form-data" id="newsForm">
                        @csrf
                        @if(isset($news))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <!-- 左侧主要内容 -->
                            <div class="col-md-8">
                                <!-- 标题 -->
                                <div class="mb-3">
                                    <label for="title" class="form-label">标题 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $news->title ?? '') }}" required>
                                    <div class="seo-counter" id="titleCounter"></div>
                                </div>

                                <!-- Slug -->
                                <div class="mb-3">
                                    <label for="slug" class="form-label">别名 (URL)</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug', $news->slug ?? '') }}" placeholder="自动生成">
                                        <button class="btn btn-outline-secondary" type="button" id="generateSlug">生成</button>
                                    </div>
                                    <small class="text-muted">留空将根据标题自动生成</small>
                                </div>

                                <!-- 摘要 -->
                                <div class="mb-3">
                                    <label for="summary" class="form-label">摘要</label>
                                    <textarea class="form-control" id="summary" name="summary" rows="3">{{ old('summary', $news->summary ?? '') }}</textarea>
                                    <div class="seo-counter" id="summaryCounter"></div>
                                </div>

                                <!-- 内容 -->
                                <div class="mb-3">
                                    <label for="content" class="form-label">内容 <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="content" name="content" rows="10">{{ old('content', $news->content ?? '') }}</textarea>
                                </div>

                                <!-- SEO 设置 -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSEO" aria-expanded="false" aria-controls="collapseSEO">
                                                SEO 设置
                                            </button>
                                        </h5>
                                    </div>
                                    <div id="collapseSEO" class="collapse">
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="meta_keywords" class="form-label">Meta 关键词</label>
                                                <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $news->meta_keywords ?? '') }}" placeholder="关键词1,关键词2,关键词3">
                                                <small class="text-muted">多个关键词用逗号分隔</small>
                                            </div>
                                            <div class="mb-3">
                                                <label for="meta_description" class="form-label">Meta 描述</label>
                                                <textarea class="form-control" id="meta_description" name="meta_description" rows="2">{{ old('meta_description', $news->meta_description ?? '') }}</textarea>
                                                <div class="seo-counter" id="metaDescriptionCounter"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 右侧边栏 -->
                            <div class="col-md-4">
                                <!-- 发布选项 -->
                                <div class="card mb-3">
                                    <div class="card-header">发布选项</div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="status" class="form-label">状态</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="draft" {{ (old('status', $news->status ?? 'draft') == 'draft') ? 'selected' : '' }}>草稿</option>
                                                <option value="published" {{ (old('status', $news->status ?? '') == 'published') ? 'selected' : '' }}>发布</option>
                                                <option value="archived" {{ (old('status', $news->status ?? '') == 'archived') ? 'selected' : '' }}>归档</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="published_at" class="form-label">发布时间</label>
                                            <input type="datetime-local" class="form-control" id="published_at" name="published_at" value="{{ old('published_at', isset($news) && $news->published_at ? $news->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1" {{ old('featured', $news->featured ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="featured">
                                                设为推荐
                                            </label>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary" name="submit" value="save">保存</button>
                                            <button type="submit" class="btn btn-success" name="submit" value="publish">发布</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- 分类 -->
                                <div class="card mb-3">
                                    <div class="card-header">分类</div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <select class="form-select" id="category_id" name="category_id">
                                                <option value="">选择分类</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" {{ (old('category_id', $news->category_id ?? '') == $category->id) ? 'selected' : '' }}>{{ $category->name }}</option>
                                                    @if($category->children->count() > 0)
                                                        @foreach($category->children as $child)
                                                            <option value="{{ $child->id }}" {{ (old('category_id', $news->category_id ?? '') == $child->id) ? 'selected' : '' }}>&nbsp;&nbsp;└ {{ $child->name }}</option>
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- 标签 -->
                                <div class="card mb-3">
                                    <div class="card-header">标签</div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <input type="text" class="form-control" id="tags" name="tags" value="{{ old('tags', isset($news) ? $news->tags->pluck('name')->implode(',') : '') }}" data-role="tagsinput">
                                            <small class="text-muted">输入标签后按回车添加</small>
                                        </div>
                                        <div class="popular-tags mt-2">
                                            <small class="text-muted">热门标签：</small>
                                            @foreach($popularTags as $tag)
                                                <a href="javascript:void(0)" class="badge bg-secondary add-tag">{{ $tag->name }}</a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- 封面图片 -->
                                <div class="card mb-3">
                                    <div class="card-header">封面图片</div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div id="cover-dropzone" class="dropzone">
                                                <div class="dz-message" data-dz-message>
                                                    <span>点击或拖拽文件到这里上传封面图片</span>
                                                </div>
                                            </div>
                                            <input type="hidden" name="cover_image" id="cover_image" value="{{ old('cover_image', $news->cover_image ?? '') }}">
                                        </div>
                                        @if(isset($news) && $news->cover_image)
                                        <div class="current-cover mb-3">
                                            <label class="form-label">当前封面：</label>
                                            <img src="{{ asset($news->cover_image) }}" alt="当前封面" class="img-thumbnail">
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // 初始化富文本编辑器
        $('#content').summernote({
            height: 400,
            lang: 'zh-CN',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            callbacks: {
                onImageUpload: function(files) {
                    // 图片上传处理
                    for (let i = 0; i < files.length; i++) {
                        uploadImage(files[i]);
                    }
                }
            }
        });
        
        // 图片上传处理函数
        function uploadImage(file) {
            let formData = new FormData();
            formData.append('image', file);
            formData.append('_token', '{{ csrf_token() }}');
            
            $.ajax({
                url: '{{ route("admin.news.upload.image") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    if (data.success) {
                        $('#content').summernote('insertImage', data.url, data.filename);
                    } else {
                        alert('图片上传失败：' + data.message);
                    }
                },
                error: function() {
                    alert('图片上传失败，请重试');
                }
            });
        }
        
        // 初始化封面图片上传
        Dropzone.autoDiscover = false;
        let coverDropzone = new Dropzone("#cover-dropzone", {
            url: "{{ route('admin.news.upload.cover') }}",
            paramName: "cover",
            maxFilesize: 5, // MB
            maxFiles: 1,
            acceptedFiles: "image/*",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            addRemoveLinks: true,
            dictRemoveFile: "删除",
            dictCancelUpload: "取消",
            dictDefaultMessage: "点击或拖拽文件到这里上传封面图片",
            init: function() {
                let dz = this;
                let currentCover = $('#cover_image').val();
                
                if (currentCover) {
                    // 显示当前封面图片
                    let mockFile = { name: "当前封面", size: 0 };
                    dz.displayExistingFile(mockFile, "{{ isset($news) && $news->cover_image ? asset($news->cover_image) : '' }}");
                }
                
                this.on("success", function(file, response) {
                    if (response.success) {
                        $('#cover_image').val(response.path);
                    } else {
                        alert('封面上传失败：' + response.message);
                    }
                });
                
                this.on("removedfile", function() {
                    $('#cover_image').val('');
                });
            }
        });
        
        // 初始化标签输入
        $('#tags').tagsinput({
            trimValue: true,
            confirmKeys: [13, 44, 32], // Enter, comma, space
            maxTags: 10
        });
        
        // 点击添加热门标签
        $('.add-tag').click(function() {
            $('#tags').tagsinput('add', $(this).text());
        });
        
        // 生成Slug
        $('#generateSlug').click(function() {
            let title = $('#title').val();
            if (title) {
                // 简单的Slug生成
                let slug = title.toLowerCase()
                    .replace(/[^\w\u4e00-\u9fa5]+/g, '-') // 非字母数字汉字替换为连字符
                    .replace(/[\s\u4e00-\u9fa5]+/g, '-') // 空格和汉字替换为连字符
                    .replace(/^-+|-+$/g, ''); // 去除首尾连字符
                
                $('#slug').val(slug);
                
                // 检查Slug是否可用
                checkSlugAvailability(slug);
            }
        });
        
        // 检查Slug是否可用
        function checkSlugAvailability(slug) {
            $.ajax({
                url: '{{ route("admin.news.check.slug") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    slug: slug,
                    news_id: '{{ $news->id ?? 0 }}'
                },
                success: function(response) {
                    if (!response.available) {
                        alert('此别名已被使用，请修改');
                    }
                }
            });
        }
        
        // SEO字数统计
        function updateCounter(element, value) {
            let length = value.length;
            let counterElement = $('#' + element + 'Counter');
            counterElement.text(length + ' 个字符');
            
            if (element === 'title') {
                if (length < 30) {
                    counterElement.removeClass('warning-count danger-count').addClass('good-count');
                } else if (length < 60) {
                    counterElement.removeClass('good-count danger-count').addClass('warning-count');
                } else {
                    counterElement.removeClass('good-count warning-count').addClass('danger-count');
                }
            } else if (element === 'metaDescription') {
                if (length < 70) {
                    counterElement.removeClass('warning-count danger-count').addClass('good-count');
                } else if (length < 160) {
                    counterElement.removeClass('good-count danger-count').addClass('warning-count');
                } else {
                    counterElement.removeClass('good-count warning-count').addClass('danger-count');
                }
            } else if (element === 'summary') {
                if (length < 100) {
                    counterElement.removeClass('warning-count danger-count').addClass('good-count');
                } else if (length < 200) {
                    counterElement.removeClass('good-count danger-count').addClass('warning-count');
                } else {
                    counterElement.removeClass('good-count warning-count').addClass('danger-count');
                }
            }
        }
        
        $('#title').on('input', function() {
            updateCounter('title', $(this).val());
        });
        
        $('#meta_description').on('input', function() {
            updateCounter('metaDescription', $(this).val());
        });
        
        $('#summary').on('input', function() {
            updateCounter('summary', $(this).val());
        });
        
        // 初始化计数器
        updateCounter('title', $('#title').val());
        updateCounter('metaDescription', $('#meta_description').val());
        updateCounter('summary', $('#summary').val());
        
        // 表单提交前验证
        $('#newsForm').submit(function(e) {
            let title = $('#title').val().trim();
            let content = $('#content').summernote('code').trim();
            
            if (!title) {
                e.preventDefault();
                alert('请填写新闻标题');
                $('#title').focus();
                return false;
            }
            
            if (!content || content === '<p><br></p>') {
                e.preventDefault();
                alert('请填写新闻内容');
                $('#content').summernote('focus');
                return false;
            }
            
            // 如果提交按钮是发布
            if ($(document.activeElement).val() === 'publish') {
                $('#status').val('published');
            }
            
            return true;
        });
    });
</script>
@endsection