<?php
/**
 * 新闻中心页面
 * 展示系统中的新闻列表，支持分类和标签筛选
 */

// 初始化会话
session_start();

// 设置当前页面标识
$current_page = 'news';

// 引入必要的配置和函数
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/functions.php';

// 引入数据库连接
require_once __DIR__ . '/../app/database/database.php';

// 获取分页参数
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // 每页显示的新闻数量
$offset = ($page - 1) * $limit;

// 获取分类筛选参数
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;

// 获取标签筛选参数
$tag_id = isset($_GET['tag']) ? (int)$_GET['tag'] : null;

// 获取搜索参数
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 构建查询条件
$where_conditions = [];
$params = [];

if ($category_id) {
    $where_conditions[] = "n.category_id = ?";
    $params[] = $category_id;
}

if ($tag_id) {
    // 使用标签关联查询
    $tag_join = "JOIN news_tag_relations ntr ON n.id = ntr.news_id AND ntr.tag_id = ?";
    $params[] = $tag_id;
}

if ($search) {
    $where_conditions[] = "(n.title LIKE ? OR n.summary LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// 组合WHERE子句
$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// 查询新闻列表
$sql = "SELECT n.*, nc.name as category_name, u.username as author_name 
        FROM news n 
        LEFT JOIN news_categories nc ON n.category_id = nc.id 
        LEFT JOIN users u ON n.author_id = u.id 
        " . (isset($tag_join) ? $tag_join : "") . " 
        $where_clause 
        ORDER BY n.publish_date DESC 
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;

// 执行查询
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$news_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 获取新闻总数，用于分页
$count_params = $params;
array_pop($count_params); // 移除OFFSET
array_pop($count_params); // 移除LIMIT

$count_sql = "SELECT COUNT(*) as total FROM news n 
              " . (isset($tag_join) ? $tag_join : "") . " 
              $where_clause";

$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($count_params);
$total_news = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

$total_pages = ceil($total_news / $limit);

// 获取所有新闻分类
$categories_sql = "SELECT * FROM news_categories ORDER BY name";
$categories_stmt = $pdo->prepare($categories_sql);
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// 获取热门标签
$tags_sql = "SELECT nt.*, COUNT(ntr.news_id) as news_count 
             FROM news_tags nt 
             JOIN news_tag_relations ntr ON nt.id = ntr.tag_id 
             GROUP BY nt.id 
             ORDER BY news_count DESC 
             LIMIT 10";
$tags_stmt = $pdo->prepare($tags_sql);
$tags_stmt->execute();
$tags = $tags_stmt->fetchAll(PDO::FETCH_ASSOC);

// 获取热门新闻
$hot_news_sql = "SELECT n.*, na.view_count 
                 FROM news n 
                 JOIN news_analytics na ON n.id = na.news_id 
                 ORDER BY na.view_count DESC 
                 LIMIT 5";
$hot_news_stmt = $pdo->prepare($hot_news_sql);
$hot_news_stmt->execute();
$hot_news = $hot_news_stmt->fetchAll(PDO::FETCH_ASSOC);

// 页面标题
$page_title = '新闻中心';
if ($category_id) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $category_id) {
            $page_title .= ' - ' . $cat['name'];
            break;
        }
    }
}
if ($tag_id) {
    foreach ($tags as $tag) {
        if ($tag['id'] == $tag_id) {
            $page_title .= ' - ' . $tag['name'];
            break;
        }
    }
}
if ($search) {
    $page_title .= ' - 搜索: ' . $search;
}

// 引入头部模板
include 'templates/header.php';
?>

<!-- 页面内容 -->
<div class="page-container">
    <!-- 页面头部 -->
    <div class="page-header">
        <div class="container">
            <h1 class="page-title"><?php echo $page_title; ?></h1>
            <div class="breadcrumb">
                <a href="/">首页</a> &gt; <span>新闻中心</span>
                <?php if ($category_id): ?>
                    <?php foreach ($categories as $cat): ?>
                        <?php if ($cat['id'] == $category_id): ?>
                            &gt; <span><?php echo $cat['name']; ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if ($tag_id): ?>
                    <?php foreach ($tags as $tag): ?>
                        <?php if ($tag['id'] == $tag_id): ?>
                            &gt; <span><?php echo $tag['name']; ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if ($search): ?>
                    &gt; <span>搜索: <?php echo htmlspecialchars($search); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 主要内容 -->
    <div class="page-content">
        <div class="container">
            <div class="news-container">
                <!-- 左侧新闻列表 -->
                <div class="news-main">
                    <!-- 搜索框 -->
                    <div class="news-search">
                        <form action="/news.php" method="GET" class="search-form">
                            <input type="text" name="search" placeholder="搜索新闻..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </button>
                        </form>
                    </div>

                    <?php if (count($news_list) > 0): ?>
                        <!-- 新闻列表 -->
                        <div class="news-list">
                            <?php foreach ($news_list as $news): ?>
                                <article class="news-card">
                                    <?php if ($news['cover_image']): ?>
                                        <div class="news-image">
                                            <a href="/news-detail.php?id=<?php echo $news['id']; ?>">
                                                <img src="<?php echo $news['cover_image']; ?>" alt="<?php echo htmlspecialchars($news['title']); ?>">
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <div class="news-content">
                                        <div class="news-meta">
                                            <span class="news-category">
                                                <a href="/news.php?category=<?php echo $news['category_id']; ?>">
                                                    <?php echo htmlspecialchars($news['category_name']); ?>
                                                </a>
                                            </span>
                                            <span class="news-date"><?php echo date('Y-m-d', strtotime($news['publish_date'])); ?></span>
                                        </div>
                                        <h2 class="news-title">
                                            <a href="/news-detail.php?id=<?php echo $news['id']; ?>">
                                                <?php echo htmlspecialchars($news['title']); ?>
                                            </a>
                                        </h2>
                                        <div class="news-summary">
                                            <?php echo htmlspecialchars($news['summary']); ?>
                                        </div>
                                        <div class="news-footer">
                                            <span class="news-author">作者: <?php echo htmlspecialchars($news['author_name']); ?></span>
                                            <a href="/news-detail.php?id=<?php echo $news['id']; ?>" class="news-read-more">阅读全文</a>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>

                        <!-- 分页 -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $tag_id ? '&tag=' . $tag_id : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="page-link">&laquo; 上一页</a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $tag_id ? '&tag=' . $tag_id : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $tag_id ? '&tag=' . $tag_id : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="page-link">下一页 &raquo;</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-results">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <h3>未找到相关新闻</h3>
                            <p>请尝试使用其他关键词或浏览其他分类</p>
                            <a href="/news.php" class="btn">返回全部新闻</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 右侧边栏 -->
                <div class="news-sidebar">
                    <!-- 分类列表 -->
                    <div class="sidebar-widget">
                        <h3 class="widget-title">新闻分类</h3>
                        <ul class="category-list">
                            <li><a href="/news.php" class="<?php echo !$category_id ? 'active' : ''; ?>">全部分类</a></li>
                            <?php foreach ($categories as $category): ?>
                                <li>
                                    <a href="/news.php?category=<?php echo $category['id']; ?>" class="<?php echo $category_id == $category['id'] ? 'active' : ''; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- 热门标签 -->
                    <div class="sidebar-widget">
                        <h3 class="widget-title">热门标签</h3>
                        <div class="tag-cloud">
                            <?php foreach ($tags as $tag): ?>
                                <a href="/news.php?tag=<?php echo $tag['id']; ?>" class="tag <?php echo $tag_id == $tag['id'] ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($tag['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- 热门新闻 -->
                    <div class="sidebar-widget">
                        <h3 class="widget-title">热门阅读</h3>
                        <ul class="hot-news-list">
                            <?php foreach ($hot_news as $hot): ?>
                                <li>
                                    <a href="/news-detail.php?id=<?php echo $hot['id']; ?>">
                                        <span class="hot-news-title"><?php echo htmlspecialchars($hot['title']); ?></span>
                                        <span class="hot-news-views"><?php echo $hot['view_count']; ?>次阅读</span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// 引入页脚模板
include 'templates/footer.php';
?> 