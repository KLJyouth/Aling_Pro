<?php
/**
 * 新闻详情页面
 * 展示单篇新闻的详细内容，包括评论功能
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

// 获取新闻ID
$news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 如果没有提供有效的新闻ID，重定向到新闻列表页
if ($news_id <= 0) {
    header('Location: /news.php');
    exit;
}

// 查询新闻详情
$sql = "SELECT n.*, nc.name as category_name, u.username as author_name, u.avatar as author_avatar 
        FROM news n 
        LEFT JOIN news_categories nc ON n.category_id = nc.id 
        LEFT JOIN users u ON n.author_id = u.id 
        WHERE n.id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$news_id]);
$news = $stmt->fetch(PDO::FETCH_ASSOC);

// 如果新闻不存在，重定向到新闻列表页
if (!$news) {
    header('Location: /news.php');
    exit;
}

// 更新浏览量
$update_views_sql = "INSERT INTO news_analytics (news_id, view_count, last_viewed) 
                     VALUES (?, 1, NOW()) 
                     ON DUPLICATE KEY UPDATE 
                     view_count = view_count + 1, 
                     last_viewed = NOW()";
$update_stmt = $pdo->prepare($update_views_sql);
$update_stmt->execute([$news_id]);

// 获取新闻标签
$tags_sql = "SELECT nt.* 
             FROM news_tags nt 
             JOIN news_tag_relations ntr ON nt.id = ntr.tag_id 
             WHERE ntr.news_id = ?";
$tags_stmt = $pdo->prepare($tags_sql);
$tags_stmt->execute([$news_id]);
$tags = $tags_stmt->fetchAll(PDO::FETCH_ASSOC);

// 获取相关新闻（同类别）
$related_sql = "SELECT n.*, nc.name as category_name 
                FROM news n 
                LEFT JOIN news_categories nc ON n.category_id = nc.id 
                WHERE n.category_id = ? AND n.id != ? 
                ORDER BY n.publish_date DESC 
                LIMIT 5";
$related_stmt = $pdo->prepare($related_sql);
$related_stmt->execute([$news['category_id'], $news_id]);
$related_news = $related_stmt->fetchAll(PDO::FETCH_ASSOC);

// 获取新闻评论
$comments_sql = "SELECT nc.*, u.username, u.avatar 
                 FROM news_comments nc 
                 LEFT JOIN users u ON nc.user_id = u.id 
                 WHERE nc.news_id = ? AND nc.status = 'approved' 
                 ORDER BY nc.created_at DESC";
$comments_stmt = $pdo->prepare($comments_sql);
$comments_stmt->execute([$news_id]);
$comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);

// 处理评论提交
$comment_error = '';
$comment_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    // 检查用户是否登录
    if (!isset($_SESSION['user_id'])) {
        $comment_error = '请先登录后再发表评论';
    } else {
        $comment_content = trim($_POST['comment_content']);
        
        // 验证评论内容
        if (empty($comment_content)) {
            $comment_error = '评论内容不能为空';
        } else if (strlen($comment_content) > 1000) {
            $comment_error = '评论内容不能超过1000个字符';
        } else {
            // 插入评论
            $insert_comment_sql = "INSERT INTO news_comments (news_id, user_id, content, status, created_at) 
                                   VALUES (?, ?, ?, ?, NOW())";
            $status = 'approved'; // 可以根据系统设置决定是否需要审核
            
            $insert_stmt = $pdo->prepare($insert_comment_sql);
            $result = $insert_stmt->execute([$news_id, $_SESSION['user_id'], $comment_content, $status]);
            
            if ($result) {
                $comment_success = true;
                
                // 重定向以防止表单重复提交
                header("Location: /news-detail.php?id=$news_id&comment_added=1");
                exit;
            } else {
                $comment_error = '评论发布失败，请稍后再试';
            }
        }
    }
}

// 显示评论成功消息
if (isset($_GET['comment_added']) && $_GET['comment_added'] == 1) {
    $comment_success = true;
}

// 页面标题
$page_title = $news['title'] . ' - 新闻中心';

// 引入头部模板
include 'templates/header.php';
?>

<!-- 页面内容 -->
<div class="page-container">
    <!-- 页面头部 -->
    <div class="page-header">
        <div class="container">
            <div class="breadcrumb">
                <a href="/">首页</a> &gt; 
                <a href="/news.php">新闻中心</a> &gt; 
                <a href="/news.php?category=<?php echo $news['category_id']; ?>"><?php echo htmlspecialchars($news['category_name']); ?></a> &gt; 
                <span><?php echo htmlspecialchars($news['title']); ?></span>
            </div>
        </div>
    </div>

    <!-- 主要内容 -->
    <div class="page-content">
        <div class="container">
            <div class="news-detail-container">
                <!-- 主要内容 -->
                <div class="news-detail-main">
                    <article class="news-detail">
                        <header class="news-header">
                            <h1 class="news-title"><?php echo htmlspecialchars($news['title']); ?></h1>
                            <div class="news-meta">
                                <div class="news-meta-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <span><?php echo date('Y-m-d', strtotime($news['publish_date'])); ?></span>
                                </div>
                                <div class="news-meta-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    <span><?php echo htmlspecialchars($news['author_name']); ?></span>
                                </div>
                                <div class="news-meta-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                    </svg>
                                    <a href="/news.php?category=<?php echo $news['category_id']; ?>"><?php echo htmlspecialchars($news['category_name']); ?></a>
                                </div>
                            </div>
                        </header>

                        <?php if ($news['cover_image']): ?>
                            <div class="news-cover">
                                <img src="<?php echo $news['cover_image']; ?>" alt="<?php echo htmlspecialchars($news['title']); ?>">
                            </div>
                        <?php endif; ?>

                        <div class="news-content">
                            <?php echo $news['content']; ?>
                        </div>

                        <?php if (count($tags) > 0): ?>
                            <div class="news-tags">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                                </svg>
                                <?php foreach ($tags as $tag): ?>
                                    <a href="/news.php?tag=<?php echo $tag['id']; ?>" class="tag"><?php echo htmlspecialchars($tag['name']); ?></a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="news-share">
                            <span>分享到：</span>
                            <a href="https://www.weibo.com/share?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&title=<?php echo urlencode($news['title']); ?>" target="_blank" class="share-btn weibo">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20.01 14.95c-.6.61-1.46 1.25-2.58 1.92-1.11.67-2.39 1.27-3.83 1.79-1.44.53-2.95.95-4.53 1.26-1.58.31-3.09.46-4.53.46-2.39 0-4.53-.46-6.43-1.39-1.9-.92-2.84-2.04-2.84-3.37 0-.71.22-1.37.65-2 .43-.63 1.03-1.19 1.79-1.68.76-.49 1.65-.92 2.68-1.29 1.03-.37 2.15-.67 3.36-.9 1.21-.23 2.45-.39 3.73-.49 1.28-.09 2.54-.14 3.79-.14.61 0 1.21.01 1.82.04.61.03 1.19.07 1.76.13.57.06 1.12.14 1.65.24.53.1 1.01.22 1.43.36.42.14.79.31 1.11.5.32.19.58.42.77.68.19.26.29.56.29.9 0 .26-.08.51-.23.75-.15.24-.36.46-.63.66-.27.2-.59.38-.96.54-.37.16-.77.3-1.2.42-.43.12-.89.21-1.38.28-.49.07-.99.1-1.49.1-.54 0-1.07-.03-1.59-.09-.52-.06-1.03-.15-1.53-.28-.5-.13-.97-.3-1.42-.51-.45-.21-.85-.47-1.2-.77-.35-.3-.62-.66-.82-1.08-.2-.42-.3-.91-.3-1.46 0-.76.25-1.44.75-2.06.5-.62 1.17-1.1 2.02-1.45-.02-.09-.03-.18-.04-.27-.01-.09-.02-.18-.02-.27 0-.36.07-.68.2-.96.13-.28.31-.51.54-.7.23-.19.5-.33.8-.42.3-.09.62-.14.96-.14.32 0 .63.05.93.14.3.09.57.23.8.42.23.19.42.43.56.72.14.29.21.62.21.99 0 .74-.27 1.33-.82 1.76z"/>
                                </svg>
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="share-btn facebook">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M18.77 7.46H14.5v-1.9c0-.9.6-1.1 1-1.1h3V.5h-4.33C10.24.5 9.5 3.44 9.5 5.32v2.15h-3v4h3v12h5v-12h3.85l.42-4z"/>
                                </svg>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($news['title']); ?>" target="_blank" class="share-btn twitter">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M23.44 4.83c-.8.37-1.5.38-2.22.02.93-.56.98-.96 1.32-2.02-.88.52-1.86.9-2.9 1.1-.82-.88-2-1.43-3.3-1.43-2.5 0-4.55 2.04-4.55 4.54 0 .36.03.7.1 1.04-3.77-.2-7.12-2-9.36-4.75-.4.67-.6 1.45-.6 2.3 0 1.56.8 2.95 2 3.77-.74-.03-1.44-.23-2.05-.57v.06c0 2.2 1.56 4.03 3.64 4.44-.67.2-1.37.2-2.06.08.58 1.8 2.26 3.12 4.25 3.16C5.78 18.1 3.37 18.74 1 18.46c2 1.3 4.4 2.04 6.97 2.04 8.35 0 12.92-6.92 12.92-12.93 0-.2 0-.4-.02-.6.9-.63 1.96-1.22 2.56-2.14z"/>
                                </svg>
                            </a>
                        </div>
                    </article>

                    <!-- 评论区 -->
                    <div class="news-comments">
                        <h3 class="comments-title">评论 (<?php echo count($comments); ?>)</h3>
                        
                        <?php if ($comment_success): ?>
                            <div class="alert alert-success">
                                评论发布成功！
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($comment_error): ?>
                            <div class="alert alert-error">
                                <?php echo $comment_error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- 评论表单 -->
                        <div class="comment-form-container">
                            <form class="comment-form" action="/news-detail.php?id=<?php echo $news_id; ?>" method="POST">
                                <div class="form-group">
                                    <label for="comment_content">发表评论</label>
                                    <textarea id="comment_content" name="comment_content" rows="4" placeholder="请输入您的评论..." required></textarea>
                                </div>
                                <div class="form-actions">
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <button type="submit" name="submit_comment" class="btn">提交评论</button>
                                    <?php else: ?>
                                        <p class="login-notice">请 <a href="/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">登录</a> 后发表评论</p>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                        
                        <!-- 评论列表 -->
                        <?php if (count($comments) > 0): ?>
                            <div class="comments-list">
                                <?php foreach ($comments as $comment): ?>
                                    <div class="comment-item">
                                        <div class="comment-avatar">
                                            <img src="<?php echo !empty($comment['avatar']) ? $comment['avatar'] : '/assets/images/default-avatar.png'; ?>" alt="用户头像">
                                        </div>
                                        <div class="comment-content">
                                            <div class="comment-header">
                                                <span class="comment-author"><?php echo htmlspecialchars($comment['username']); ?></span>
                                                <span class="comment-date"><?php echo date('Y-m-d H:i', strtotime($comment['created_at'])); ?></span>
                                            </div>
                                            <div class="comment-text">
                                                <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-comments">
                                <p>暂无评论，成为第一个评论的人吧！</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 侧边栏 -->
                <div class="news-detail-sidebar">
                    <!-- 作者信息 -->
                    <div class="sidebar-widget author-widget">
                        <div class="author-header">
                            <div class="author-avatar">
                                <img src="<?php echo !empty($news['author_avatar']) ? $news['author_avatar'] : '/assets/images/default-avatar.png'; ?>" alt="作者头像">
                            </div>
                            <div class="author-info">
                                <h4 class="author-name"><?php echo htmlspecialchars($news['author_name']); ?></h4>
                                <p class="author-role">作者</p>
                            </div>
                        </div>
                        <div class="author-bio">
                            <?php echo !empty($news['author_bio']) ? htmlspecialchars($news['author_bio']) : '这位作者暂时没有填写个人简介。'; ?>
                        </div>
                    </div>

                    <!-- 相关新闻 -->
                    <?php if (count($related_news) > 0): ?>
                        <div class="sidebar-widget">
                            <h3 class="widget-title">相关新闻</h3>
                            <ul class="related-news-list">
                                <?php foreach ($related_news as $related): ?>
                                    <li>
                                        <a href="/news-detail.php?id=<?php echo $related['id']; ?>">
                                            <?php if ($related['cover_image']): ?>
                                                <div class="related-news-image">
                                                    <img src="<?php echo $related['cover_image']; ?>" alt="<?php echo htmlspecialchars($related['title']); ?>">
                                                </div>
                                            <?php endif; ?>
                                            <div class="related-news-content">
                                                <h4 class="related-news-title"><?php echo htmlspecialchars($related['title']); ?></h4>
                                                <span class="related-news-date"><?php echo date('Y-m-d', strtotime($related['publish_date'])); ?></span>
                                            </div>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- 标签云 -->
                    <?php if (count($tags) > 0): ?>
                        <div class="sidebar-widget">
                            <h3 class="widget-title">文章标签</h3>
                            <div class="tag-cloud">
                                <?php foreach ($tags as $tag): ?>
                                    <a href="/news.php?tag=<?php echo $tag['id']; ?>" class="tag">
                                        <?php echo htmlspecialchars($tag['name']); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// 引入页脚模板
include 'templates/footer.php';
?> 