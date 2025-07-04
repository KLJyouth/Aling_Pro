<?php
/**
 * 会员等级管理页面
 *
 * 用于管理系统中的会员等级设置
 */

// 安全检查
if (!defined("ADMIN_ACCESS")) {
    exit("无权访问");
}

$pageTitle = "会员等级管理";
$breadcrumbs = [
    ["name" => "首页", "url" => "index.php"],
    ["name" => "会员管理", "url" => "index.php?module=membership"],
    ["name" => "等级设置", "url" => "#"]
];

// 包含头部
include(ADMIN_PATH . "/views/common/header.php");
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo $pageTitle; ?></h1>

    <!-- 面包屑导航 -->
    <ol class="breadcrumb mb-4">
        <?php foreach ($breadcrumbs as $index => $item): ?>
            <?php if ($index === count($breadcrumbs) - 1): ?>
                <li class="breadcrumb-item active"><?php echo $item["name"]; ?></li>
            <?php else: ?>
                <li class="breadcrumb-item"><a href="<?php echo $item["url"]; ?>"><?php echo $item["name"]; ?></a></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>

    <!-- 操作提示框 -->
    <?php if (isset($_SESSION["admin_message"])): ?>
        <div class="alert alert-<?php echo $_SESSION["admin_message_type"]; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION["admin_message"]; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
        </div>
        <?php unset($_SESSION["admin_message"], $_SESSION["admin_message_type"]); ?>
    <?php endif; ?>

    <!-- 卡片容器 -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-table me-1"></i>
                会员等级列表
            </div>
            <div>
                <a href="index.php?module=membership&action=level_edit" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> 添加等级
                </a>
            </div>
        </div>
        <div class="card-body">
            <table id="levelTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="10%">图标</th>
                        <th width="15%">名称</th>
                        <th width="10%">代码</th>
                        <th width="10%">价格</th>
                        <th width="10%">有效期(天)</th>
                        <th width="20%">特权</th>
                        <th width="10%">状态</th>
                        <th width="10%">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- 数据将通过AJAX加载 -->
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- 会员等级管理的JavaScript代码 -->
<script>
$(document).ready(function() {
    // 初始化DataTables
    var levelTable = $("#levelTable").DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "index.php?module=membership&action=level_data",
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { 
                "data": "icon", 
                "render": function(data, type, row) {
                    if (data) {
                        return "<img src=\"" + data + "\" alt=\"" + row.name + "\" class=\"level-icon\" style=\"max-width: 40px; max-height: 40px;\">";
                    }
                    return "<span class=\"badge bg-secondary\">无图标</span>";
                }
            },
            { "data": "name" },
            { "data": "code" },
            { 
                "data": "price", 
                "render": function(data) {
                    return parseFloat(data).toFixed(2) + " 元";
                }
            },
            { "data": "duration_days" },
            { 
                "data": "benefits", 
                "render": function(data) {
                    if (data && typeof data === "object") {
                        var benefitsList = "";
                        var count = 0;
                        for (var key in data) {
                            if (count < 3) {
                                benefitsList += "<span class=\"badge bg-info me-1\">" + data[key] + "</span>";
                            }
                            count++;
                        }
                        if (count > 3) {
                            benefitsList += "<span class=\"badge bg-secondary\">+" + (count - 3) + "</span>";
                        }
                        return benefitsList;
                    }
                    return "<span class=\"badge bg-secondary\">无特权</span>";
                }
            },
            { 
                "data": "status", 
                "render": function(data) {
                    if (data == 1) {
                        return "<span class=\"badge bg-success\">启用</span>";
                    } else {
                        return "<span class=\"badge bg-danger\">禁用</span>";
                    }
                }
            },
            { 
                "data": null, 
                "render": function(data, type, row) {
                    var actions = "<div class=\"btn-group btn-group-sm\" role=\"group\">";
                    actions += "<a href=\"index.php?module=membership&action=level_edit&id=" + row.id + "\" class=\"btn btn-primary\"><i class=\"fas fa-edit\"></i></a>";
                    actions += "<button type=\"button\" class=\"btn btn-danger delete-level\" data-id=\"" + row.id + "\"><i class=\"fas fa-trash\"></i></button>";
                    actions += "</div>";
                    return actions;
                }
            }
        ],
        "order": [[0, "asc"]],
        "language": {
            "url": "assets/js/plugins/datatables/zh-CN.json"
        }
    });

    
    // 删除会员等级
    $(document).on("click", ".delete-level", function() {
        var id = $(this).data("id");
        
        Swal.fire({
            title: "确认删除",
            text: "您确定要删除这个会员等级吗？删除后不可恢复！",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "确认删除",
            cancelButtonText: "取消"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "index.php?module=membership&action=level_delete",
                    type: "POST",
                    data: {
                        id: id,
                        csrf_token: $("#csrf_token").val()
                    },
                    success: function(response) {
                        try {
                            var data = JSON.parse(response);
                            if (data.success) {
                                Swal.fire(
                                    "已删除!",
                                    data.message,
                                    "success"
                                );
                                levelTable.ajax.reload();
                            } else {
                                Swal.fire(
                                    "错误!",
                                    data.message,
                                    "error"
                                );
                            }
                        } catch(e) {
                            Swal.fire(
                                "错误!",
                                "操作失败，请重试",
                                "error"
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            "错误!",
                            "服务器错误，请稍后重试",
                            "error"
                        );
                    }
                });
            }
        });
    });
});
</script>

<?php
// 包含底部
include(ADMIN_PATH . "/views/common/footer.php");
?>
