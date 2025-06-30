@extends('admin.layouts.admin')

@section('title', '�༭��Ʒ')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">�༭��Ʒ</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> �����б�
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="name">��Ʒ����</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="price">��Ʒ�۸�</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"></span>
                                </div>
                                <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price) }}" required>
                                @error('price')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="type">��Ʒ����</label>
                            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">��ѡ����Ʒ����</option>
                                <option value="physical" {{ old('type', $product->type) == 'physical' ? 'selected' : '' }}>ʵ����Ʒ</option>
                                <option value="digital" {{ old('type', $product->type) == 'digital' ? 'selected' : '' }}>������Ʒ</option>
                                <option value="service" {{ old('type', $product->type) == 'service' ? 'selected' : '' }}>������Ʒ</option>
                            </select>
                            @error('type')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
