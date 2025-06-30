<?php
/**
 * PHP信息页面
 */
?>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">PHP信息</h5>
                        <p class="text-muted mb-0">查看PHP配置、扩展和环境信息</p>
                    </div>
                    <div>
                        <a href="/admin/tools" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> 返回工具列表
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="phpinfo-container">
                    <style>
                        .phpinfo-container {
                            padding: 15px;
                        }
                        .phpinfo-container table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 1rem;
                        }
                        .phpinfo-container table td,
                        .phpinfo-container table th {
                            padding: 0.75rem;
                            border: 1px solid #dee2e6;
                        }
                        .phpinfo-container table th {
                            background-color: #f8f9fa;
                            font-weight: 600;
                        }
                        .phpinfo-container h1 {
                            font-size: 1.5rem;
                            margin-bottom: 1rem;
                        }
                        .phpinfo-container h2 {
                            font-size: 1.25rem;
                            margin-top: 1.5rem;
                            margin-bottom: 1rem;
                            padding-bottom: 0.5rem;
                            border-bottom: 1px solid #dee2e6;
                        }
                        .phpinfo-container hr {
                            display: none;
                        }
                        .phpinfo-container a {
                            color: #0d6efd;
                            text-decoration: none;
                        }
                        .phpinfo-container a:hover {
                            text-decoration: underline;
                        }
                    </style>
                    
                    <?php
                    // 捕获phpinfo输出并进行样式调整
                    ob_start();
                    phpinfo();
                    $phpinfo = ob_get_clean();
                    
                    // 提取body内容
                    $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
                    
                    // 替换样式和图片
                    $phpinfo = str_replace('class="e"', 'class="bg-light"', $phpinfo);
                    $phpinfo = str_replace('class="v"', 'class="bg-white"', $phpinfo);
                    $phpinfo = str_replace('class="h"', 'class="bg-primary text-white"', $phpinfo);
                    
                    // 输出处理后的内容
                    echo $phpinfo;
                    ?>
                </div>
            </div>
        </div>
    </div>
</div> 