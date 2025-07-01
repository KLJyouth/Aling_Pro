<?php
/**
 * PHP信息页面视图
 */
// 引入布局模板
include_once VIEWS_PATH . '/layouts/header.php';
?>

<div class="container-fluid py-4">
    <h2 class="mb-4"><?= $pageHeader ?? 'PHP信息' ?></h2>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-info-circle me-2"></i> PHP 配置信息
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="phpinfo-container">
                <?php
                // 使用输出缓冲捕获phpinfo()的输出
                ob_start();
                phpinfo();
                $phpinfo = ob_get_clean();
                
                // 提取body内容
                $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
                
                // 美化样式
                $phpinfo = str_replace('<table', '<table class="table table-striped phpinfo-table"', $phpinfo);
                $phpinfo = str_replace('class="e"', 'class="col-md-4"', $phpinfo);
                $phpinfo = str_replace('class="v"', 'class="col-md-8"', $phpinfo);
                $phpinfo = str_replace('class="h"', 'class="bg-primary text-white"', $phpinfo);
                
                // 输出修改后的phpinfo内容
                echo $phpinfo;
                ?>
            </div>
        </div>
    </div>
</div>

<style>
    /* PHPInfo 样式 */
    .phpinfo-container {
        padding: 0;
    }
    
    .phpinfo-container hr {
        display: none;
    }
    
    .phpinfo-container h1 {
        font-size: 1.5rem;
        margin: 0;
        padding: 1rem;
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .phpinfo-container h2 {
        font-size: 1.2rem;
        margin: 0;
        padding: 0.75rem 1rem;
        background-color: #f1f3f5;
        border-bottom: 1px solid #dee2e6;
        border-top: 1px solid #dee2e6;
    }
    
    .phpinfo-table {
        margin-bottom: 0 !important;
    }
</style>

<?php
// 引入布局底部
include_once VIEWS_PATH . '/layouts/footer.php';
?> 