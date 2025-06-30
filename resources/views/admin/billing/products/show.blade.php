@extends('admin.layouts.admin')

@section('title', '商品详情')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">商品详情</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> 返回列表
                        </a>
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> 编辑
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">基本信息</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 30%">ID</th>
                                            <td>{{ $product->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>商品名称</th>
                                            <td>{{ $product->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>价格</th>
                                            <td>{{ $product->price }}</td>
                                        </tr>
                                        <tr>
                                            <th>类型</th>
                                            <td>
                                                @if($product->type == 'physical')
                                                    实物商品
                                                @elseif($product->type == 'digital')
                                                    数字商品
                                                @elseif($product->type == 'service')
                                                    服务商品
                                                @else
                                                    {{ $product->type }}
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>状态</th>
                                            <td>
                                                @if($product->status == 1)
                                                <span class="badge badge-success">上架中</span>
                                                @else
                                                <span class="badge badge-danger">已下架</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>库存</th>
                                            <td>{{ $product->stock }}</td>
                                        </tr>
                                        <tr>
                                            <th>创建时间</th>
                                            <td>{{ $product->created_at }}</td>
                                        </tr>
                                        <tr>
                                            <th>更新时间</th>
                                            <td>{{ $product->updated_at }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">商品图片</h3>
                                </div>
                                <div class="card-body text-center">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-fluid">
                                    @else
                                        <div class="alert alert-info">
                                            没有上传商品图片
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">商品描述</h3>
                                </div>
                                <div class="card-body">
                                    {!! $product->description !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">销售统计</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 col-sm-6 col-12">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info"><i class="fas fa-shopping-cart"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">总销量</span>
                                                    <span class="info-box-number">{{ $product->sales_count ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 col-12">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">总收入</span>
                                                    <span class="info-box-number">{{ $product->sales_amount ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 col-12">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-warning"><i class="fas fa-eye"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">浏览量</span>
                                                    <span class="info-box-number">{{ $product->view_count ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 col-12">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-danger"><i class="fas fa-chart-line"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">转化率</span>
                                                    <span class="info-box-number">
                                                        @if(($product->view_count ?? 0) > 0)
                                                            {{ round((($product->sales_count ?? 0) / ($product->view_count ?? 1)) * 100, 2) }}%
                                                        @else
                                                            0%
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</div>
@endsection
