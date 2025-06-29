@extends("admin.layouts.app")

@section("title", "数据库结构")

@section("content")
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>数据库结构</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">首页</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.database.index') }}">数据库超级运维</a></li>
                <li class="breadcrumb-item active">数据库结构</li>
            </ol>
        </div>
    </div>

    <!-- 表关系图 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">表关系图</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
                <div class="btn-group">
                    <button type="button" class="btn btn-tool dropdown-toggle" data-toggle="dropdown">
                        <i class="fas fa-download"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="#" class="dropdown-item" id="download-svg">
                            <i class="fas fa-file-image mr-2"></i> 下载SVG
                        </a>
                        <a href="#" class="dropdown-item" id="download-png">
                            <i class="fas fa-file-image mr-2"></i> 下载PNG
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <div class="btn-group">
                    <button type="button" class="btn btn-default" id="zoom-in">
                        <i class="fas fa-search-plus"></i> 放大
                    </button>
                    <button type="button" class="btn btn-default" id="zoom-out">
                        <i class="fas fa-search-minus"></i> 缩小
                    </button>
                    <button type="button" class="btn btn-default" id="zoom-reset">
                        <i class="fas fa-redo"></i> 重置
                    </button>
                </div>
                <div class="btn-group ml-2">
                    <button type="button" class="btn btn-default" id="toggle-tables">
                        <i class="fas fa-table"></i> 显示/隐藏表
                    </button>
                    <button type="button" class="btn btn-default" id="toggle-columns">
                        <i class="fas fa-columns"></i> 显示/隐藏字段
                    </button>
                    <button type="button" class="btn btn-default" id="toggle-relations">
                        <i class="fas fa-project-diagram"></i> 显示/隐藏关系
                    </button>
                </div>
            </div>
            <div id="database-diagram" style="height: 600px; border: 1px solid #ddd; overflow: hidden;"></div>
        </div>
    </div>

    <!-- 表列表 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">表列表</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="tables-table">
                    <thead>
                        <tr>
                            <th>表名</th>
                            <th>字段数</th>
                            <th>关系数</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tableNames as $tableName)
                            @php
                                $relationCount = 0;
                                foreach ($relationships as $relation) {
                                    if ($relation['source'] === $tableName || $relation['target'] === $tableName) {
                                        $relationCount++;
                                    }
                                }
                            @endphp
                            <tr>
                                <td>{{ $tableName }}</td>
                                <td>{{ count($tableColumns[$tableName]) }}</td>
                                <td>{{ $relationCount }}</td>
                                <td>
                                    <a href="{{ route('admin.database.table.detail', $tableName) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> 查看
                                    </a>
                                    <button type="button" class="btn btn-sm btn-primary show-table-structure" data-table="{{ $tableName }}">
                                        <i class="fas fa-list"></i> 结构
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 关系列表 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">关系列表</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="relations-table">
                    <thead>
                        <tr>
                            <th>源表</th>
                            <th>源字段</th>
                            <th>目标表</th>
                            <th>目标字段</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($relationships as $relation)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.database.table.detail', $relation['source']) }}">
                                        {{ $relation['source'] }}
                                    </a>
                                </td>
                                <td>{{ $relation['source_column'] }}</td>
                                <td>
                                    <a href="{{ route('admin.database.table.detail', $relation['target']) }}">
                                        {{ $relation['target'] }}
                                    </a>
                                </td>
                                <td>{{ $relation['target_column'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 表结构模态框 -->
<div class="modal fade" id="table-structure-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">表结构: <span id="structure-table-name"></span></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="structure-table">
                        <thead>
                            <tr>
                                <th>字段名</th>
                                <th>类型</th>
                                <th>允许空</th>
                                <th>键</th>
                                <th>默认值</th>
                                <th>额外</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <a href="#" class="btn btn-primary" id="view-table-detail">查看详情</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section("styles")
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis.min.css">
<style>
    #database-diagram {
        background-color: #f8f9fa;
    }
    
    .table-node {
        padding: 10px;
        border-radius: 5px;
        background-color: #fff;
        border: 1px solid #ddd;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .table-title {
        background-color: #17a2b8;
        color: white;
        padding: 5px;
        text-align: center;
        border-radius: 3px 3px 0 0;
        font-weight: bold;
    }
    
    .table-columns {
        padding: 5px;
    }
    
    .column-item {
        padding: 2px 5px;
        border-bottom: 1px solid #eee;
    }
    
    .primary-key {
        color: #dc3545;
        font-weight: bold;
    }
    
    .foreign-key {
        color: #28a745;
        font-weight: bold;
    }
</style>
@endsection

@section("scripts")
<script src="https://cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis.min.js"></script>
<script>
    $(function () {
        // 表格初始化
        $('#tables-table, #relations-table').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Chinese.json"
            }
        });
        
        // 表结构数据
        var tableColumns = @json($tableColumns);
        
        // 显示表结构
        $('.show-table-structure').click(function() {
            var tableName = $(this).data('table');
            var columns = tableColumns[tableName];
            
            $('#structure-table-name').text(tableName);
            $('#view-table-detail').attr('href', '{{ route("admin.database.table.detail", "") }}/' + tableName);
            
            var tbody = '';
            for (var i = 0; i < columns.length; i++) {
                var column = columns[i];
                tbody += '<tr>';
                tbody += '<td>' + column.name + '</td>';
                tbody += '<td>' + column.type + '</td>';
                tbody += '<td>' + (column.nullable ? '是' : '否') + '</td>';
                tbody += '<td>' + column.key + '</td>';
                tbody += '<td>' + (column.default !== null ? column.default : '<em>NULL</em>') + '</td>';
                tbody += '<td>' + column.extra + '</td>';
                tbody += '</tr>';
            }
            
            $('#structure-table tbody').html(tbody);
            $('#table-structure-modal').modal('show');
        });
        
        // 初始化数据库图表
        var nodes = [];
        var edges = [];
        var showColumns = true;
        var showRelations = true;
        
        // 创建表节点
        @foreach($tableNames as $tableName)
            var tableNode = {
                id: '{{ $tableName }}',
                label: '{{ $tableName }}',
                shape: 'box',
                margin: 10,
                color: {
                    background: '#ffffff',
                    border: '#17a2b8',
                    highlight: {
                        background: '#f8f9fa',
                        border: '#17a2b8'
                    }
                },
                font: {
                    face: 'Arial',
                    size: 14
                },
                widthConstraint: {
                    minimum: 150,
                    maximum: 250
                }
            };
            nodes.push(tableNode);
        @endforeach
        
        // 创建关系边
        @foreach($relationships as $index => $relation)
            var edge = {
                id: 'edge{{ $index }}',
                from: '{{ $relation['source'] }}',
                to: '{{ $relation['target'] }}',
                arrows: 'to',
                label: '{{ $relation['source_column'] }} -> {{ $relation['target_column'] }}',
                font: {
                    align: 'middle',
                    size: 10
                },
                color: {
                    color: '#28a745',
                    highlight: '#28a745',
                    hover: '#28a745'
                },
                width: 1,
                smooth: {
                    type: 'curvedCW',
                    roundness: 0.2
                }
            };
            edges.push(edge);
        @endforeach
        
        // 创建网络图
        var container = document.getElementById('database-diagram');
        var data = {
            nodes: new vis.DataSet(nodes),
            edges: new vis.DataSet(edges)
        };
        var options = {
            layout: {
                hierarchical: {
                    direction: 'UD',
                    sortMethod: 'directed',
                    nodeSpacing: 150,
                    levelSeparation: 150
                }
            },
            physics: {
                enabled: true,
                hierarchicalRepulsion: {
                    nodeDistance: 200,
                    centralGravity: 0.1,
                    springLength: 100,
                    springConstant: 0.01,
                    damping: 0.09
                },
                solver: 'hierarchicalRepulsion'
            },
            interaction: {
                dragNodes: true,
                dragView: true,
                zoomView: true,
                hover: true
            }
        };
        var network = new vis.Network(container, data, options);
        
        // 缩放控制
        $('#zoom-in').click(function() {
            var scale = network.getScale() * 1.2;
            network.moveTo({scale: scale});
        });
        
        $('#zoom-out').click(function() {
            var scale = network.getScale() / 1.2;
            network.moveTo({scale: scale});
        });
        
        $('#zoom-reset').click(function() {
            network.fit();
        });
        
        // 切换表显示
        $('#toggle-tables').click(function() {
            var nodes = data.nodes.get();
            for (var i = 0; i < nodes.length; i++) {
                var node = nodes[i];
                if (node.hidden) {
                    data.nodes.update({id: node.id, hidden: false});
                } else {
                    data.nodes.update({id: node.id, hidden: true});
                }
            }
        });
        
        // 切换字段显示
        $('#toggle-columns').click(function() {
            showColumns = !showColumns;
            updateTableNodes();
        });
        
        // 切换关系显示
        $('#toggle-relations').click(function() {
            showRelations = !showRelations;
            var edges = data.edges.get();
            for (var i = 0; i < edges.length; i++) {
                var edge = edges[i];
                data.edges.update({id: edge.id, hidden: !showRelations});
            }
        });
        
        // 更新表节点
        function updateTableNodes() {
            var nodes = data.nodes.get();
            for (var i = 0; i < nodes.length; i++) {
                var node = nodes[i];
                var tableName = node.id;
                var columns = tableColumns[tableName];
                
                if (showColumns && columns) {
                    var html = '<div class="table-node">';
                    html += '<div class="table-title">' + tableName + '</div>';
                    html += '<div class="table-columns">';
                    
                    for (var j = 0; j < columns.length; j++) {
                        var column = columns[j];
                        var className = '';
                        
                        if (column.key === 'PRI') {
                            className = 'primary-key';
                        } else if (column.key === 'MUL') {
                            className = 'foreign-key';
                        }
                        
                        html += '<div class="column-item ' + className + '">';
                        html += column.name + ' (' + column.type + ')';
                        html += '</div>';
                    }
                    
                    html += '</div></div>';
                    
                    data.nodes.update({
                        id: tableName,
                        label: undefined,
                        shape: 'custom',
                        ctxRenderer: {
                            function: function(ctx, x, y, radius) {
                                return {
                                    drawNode: function() {
                                        // 节点已经通过HTML渲染
                                        return;
                                    },
                                    nodeDimensions: {width: 200, height: 30 * columns.length + 30}
                                };
                            }
                        },
                        color: {
                            border: '#17a2b8',
                            background: '#ffffff'
                        },
                        shapeProperties: {
                            useImageSize: false
                        },
                        size: 30,
                        margin: 10,
                        widthConstraint: {
                            minimum: 200,
                            maximum: 200
                        },
                        heightConstraint: {
                            minimum: 30 * columns.length + 30
                        },
                        title: html
                    });
                } else {
                    data.nodes.update({
                        id: tableName,
                        label: tableName,
                        shape: 'box',
                        ctxRenderer: undefined,
                        color: {
                            background: '#ffffff',
                            border: '#17a2b8'
                        },
                        size: undefined,
                        widthConstraint: {
                            minimum: 150,
                            maximum: 250
                        },
                        heightConstraint: undefined,
                        title: undefined
                    });
                }
            }
        }
        
        // 下载SVG
        $('#download-svg').click(function(event) {
            event.preventDefault();
            
            var svgData = network.getSVGString();
            var blob = new Blob([svgData], {type: 'image/svg+xml'});
            var url = URL.createObjectURL(blob);
            
            var link = document.createElement('a');
            link.href = url;
            link.download = 'database_structure.svg';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
        
        // 下载PNG
        $('#download-png').click(function(event) {
            event.preventDefault();
            
            var canvas = network.canvas.frame.canvas;
            var dataURL = canvas.toDataURL('image/png');
            
            var link = document.createElement('a');
            link.href = dataURL;
            link.download = 'database_structure.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
        
        // 双击节点查看表详情
        network.on('doubleClick', function(params) {
            if (params.nodes.length > 0) {
                var tableName = params.nodes[0];
                window.location.href = '{{ route("admin.database.table.detail", "") }}/' + tableName;
            }
        });
    });
</script>
@endsection 