<?php
/**
 * 管理菜单
 * @version 1.1.0
 * @author AlingAi Team
 */

// 获取当前页面
$current_page = basename($_SERVER["PHP_SELF"]);
// 获取当前完整请求路径
$current_path = $_SERVER["PHP_SELF"];

// 定义菜单项
$menu_items = [
    [
        "title" => "仪表盘",
        "icon" => "fa-tachometer-alt",
        "url" => "index.php",
        "active" => $current_page === "index.php" || $current_page === "index-enhanced.php"
    ],
    [
        "title" => "用户管理",
        "icon" => "fa-users",
        "url" => "users.php",
        "active" => $current_page === "users.php"
    ],
    [
        "title" => "安全中心",
        "icon" => "fa-shield-alt",
        "url" => "security.php",
        "active" => $current_page === "security.php" && strpos($current_path, "/security/") === false,
        "submenu" => [
            [
                "title" => "安全仪表盘",
                "url" => "security.php",
                "active" => $current_page === "security.php" && (!isset($_GET["module"]) || $_GET["module"] === "security")
            ],
            [
                "title" => "量子加密监控",
                "url" => "security.php?module=quantum",
                "active" => $current_page === "security.php" && isset($_GET["module"]) && $_GET["module"] === "quantum"
            ],
            [
                "title" => "API安全监控",
                "url" => "security.php?module=api",
                "active" => $current_page === "security.php" && isset($_GET["module"]) && $_GET["module"] === "api"
            ],
            [
                "title" => "综合安全扫描",
                "url" => "security.php?action=comprehensive_scan",
                "active" => $current_page === "security.php" && isset($_GET["action"]) && $_GET["action"] === "comprehensive_scan"
            ]
        ]
    ],
    [
        "title" => "IT运维系统",
        "icon" => "fa-tools",
        "url" => "operations/index.php",
        "active" => strpos($current_path, "/operations/") !== false
    ],
    [
        "title" => "网络安全攻防系统",
        "icon" => "fa-user-shield",
        "url" => "security/index.php",
        "active" => strpos($current_path, "/security/") !== false
    ],
    [
        "title" => "配置管理",
        "icon" => "fa-cogs",
        "url" => "config_manager.php",
        "active" => $current_page === "config_manager.php"
    ],
    [
        "title" => "系统管理",
        "icon" => "fa-server",
        "url" => "SystemManager.php",
        "active" => $current_page === "SystemManager.php"
    ],
    [
        "title" => "基线管理",
        "icon" => "fa-chart-line",
        "url" => "baseline_manager.php",
        "active" => $current_page === "baseline_manager.php"
    ],
    [
        "title" => "退出",
        "icon" => "fa-sign-out-alt",
        "url" => "logout.php",
        "active" => false
    ]
];
?>

<!-- 侧边栏菜单 -->
<div class="sidebar">
    <div class="sidebar-header">
        <h3>AlingAi Pro</h3>
        <p>管理中心</p>
    </div>
    
    <ul class="nav flex-column">
        <?php foreach ($menu_items as $item): ?>
            <li class="nav-item <?= $item["active"] ? "active" : "" ?>">
                <a class="nav-link" href="<?= $item["url"] ?>">
                    <i class="fas <?= $item["icon"] ?>"></i>
                    <span><?= $item["title"] ?></span>
                    <?php if (isset($item["submenu"])): ?>
                        <i class="fas fa-chevron-down submenu-toggle"></i>
                    <?php endif; ?>
                </a>
                
                <?php if (isset($item["submenu"])): ?>
                    <ul class="submenu <?= $item["active"] ? "show" : "" ?>">
                        <?php foreach ($item["submenu"] as $subitem): ?>
                            <li class="<?= $subitem["active"] ? "active" : "" ?>">
                                <a href="<?= $subitem["url"] ?>">
                                    <i class="fas fa-circle-notch"></i>
                                    <span><?= $subitem["title"] ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<script>
    // 切换子菜单
    document.querySelectorAll(".submenu-toggle").forEach(function(toggle) {
        toggle.addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const parent = this.closest(".nav-item");
            const submenu = parent.querySelector(".submenu");
            
            if (submenu.classList.contains("show")) {
                submenu.classList.remove("show");
                this.classList.remove("rotate");
            } else {
                submenu.classList.add("show");
                this.classList.add("rotate");
            }
        });
    });
</script>
