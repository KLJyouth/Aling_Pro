@extends("admin.layouts.app")

@section("title", "套餐详情")

@section("content_header")
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>套餐详情</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route("admin.index") }}">首页</a></li>
                <li class="breadcrumb-item"><a href="{{ route("admin.billing.packages.index") }}">额度套餐管理</a></li>
                <li class="breadcrumb-item active">套餐详情</li>
            </ol>
        </div>
    </div>
@endsection


@section("content")
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">套餐信息</h3>
                    <div class="card-tools">
                        <a href="{{ route("admin.billing.packages.edit", $package->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> 编辑
                        </a>
                        <a href="{{ route("admin.billing.packages.index") }}" class="btn btn-sm btn-default">
                            <i class="fas fa-list"></i> 返回列表
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">套餐名称</th>
                                    <td>{{ $package->name }}</td>
                                </tr>
                                <tr>
                                    <th>套餐代码</th>
                                    <td>{{ $package->code }}</td>
                                </tr>
                                <tr>
                                    <th>套餐类型</th>
                                    <td>
                                        @switch($package->type)
                                            @case("api")
                                                <span class="badge badge-info">API调用额度</span>
                                                @break
                                            @case("ai")
                                                <span class="badge badge-primary">AI使用额度</span>
                                                @break
                                            @case("storage")
                                                <span class="badge badge-success">存储空间</span>
                                                @break
                                            @case("bandwidth")
                                                <span class="badge badge-warning">带宽流量</span>
                                                @break
                                            @case("comprehensive")
                                                <span class="badge badge-secondary">综合套餐</span>
                                                @break
                                            @default
                                                <span class="badge badge-secondary">{{ $package->type }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <th>额度数量</th>
                                    <td>{{ number_format($package->quota) }}</td>
                                </tr>
                                <tr>
                                    <th>有效期</th>
                                    <td>
                                        @if($package->duration_days)
                                            {{ $package->duration_days }} 天
                                        @else
                                            永久有效
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">价格</th>
                                    <td>
                                        <span class="text-success font-weight-bold">{{ number_format($package->price, 2) }}</span>
                                        @if($package->original_price && $package->original_price > $package->price)
                                            <span class="text-muted ml-2"><del>{{ number_format($package->original_price, 2) }}</del></span>
                                            <span class="badge badge-danger ml-2">
                                                {{ round((1 - $package->price / $package->original_price) * 100) }}% 折扣
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>状态</th>
                                    <td>
                                        @switch($package->status)
                                            @case("active")
                                                <span class="badge badge-success">上架</span>
                                                @break
                                            @case("inactive")
                                                <span class="badge badge-danger">下架</span>
                                                @break
                                            @case("coming_soon")
                                                <span class="badge badge-warning">即将推出</span>
                                                @break
                                            @default
                                                <span class="badge badge-secondary">{{ $package->status }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <th>排序</th>
                                    <td>{{ $package->sort_order }}</td>
                                </tr>
                                <tr>
                                    <th>标记</th>
                                    <td>
                                        @if($package->is_popular)
                                            <span class="badge badge-warning"><i class="fas fa-fire"></i> 热门套餐</span>
                                        @endif
                                        @if($package->is_recommended)
                                            <span class="badge badge-success"><i class="fas fa-thumbs-up"></i> 推荐套餐</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>创建时间</th>
                                    <td>{{ $package->created_at }}</td>
                                </tr>
                                <tr>
                                    <th>更新时间</th>
                                    <td>{{ $package->updated_at }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>


                    @if($package->description)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>套餐描述</h5>
                                <div class="callout callout-info">
                                    {{ $package->description }}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($package->features)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>套餐特性</h5>
                                <ul class="list-group">
                                    @foreach(json_decode($package->features, true) as $feature)
                                        @if(!empty($feature))
                                            <li class="list-group-item">
                                                <i class="fas fa-check-circle text-success mr-2"></i> {{ $feature }}
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>销售统计</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-shopping-cart"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">总销售量</span>
                                            <span class="info-box-number">{{ $package->sales_count ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success"><i class="fas fa-money-bill-wave"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">总销售额</span>
                                            <span class="info-box-number">{{ number_format($package->sales_amount ?? 0, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-users"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">购买用户数</span>
                                            <span class="info-box-number">{{ $package->user_count ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-danger"><i class="fas fa-chart-line"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">转化率</span>
                                            <span class="info-box-number">{{ $package->conversion_rate ?? "0%" }}</span>
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
    </div>
@endsection
