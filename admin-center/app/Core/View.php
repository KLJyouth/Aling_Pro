<?php
namespace App\Core;

/**
 * 视图渲染助手类
 * 负责渲染和管理视图
 */
class View
{
    /**
     * 渲染视图
     * @param string $view 视图路径，使用点号分隔
     * @param array $data 要传递给视图的数据
     * @param string|null $layout 布局文件路径，不使用布局则传null
     * @return string 渲染后的内容
     */
    public static function render($view, array $data = [], $layout = 'layouts.app')
    {
        // 提取数据到变量
        extract($data);
        
        // 构建视图文件路径
        $viewPath = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        
        // 检查视图文件是否存在
        if (!file_exists($viewPath)) {
            throw new \Exception("视图 {$view} 不存在");
        }
        
        // 启动输出缓冲
        ob_start();
        
        // 包含视图文件
        include $viewPath;
        
        // 获取渲染后的内容
        $content = ob_get_clean();
        
        // 不使用布局则直接返回视图内容
        if ($layout === null) {
            return $content;
        }
        
        // 构建布局文件路径
        $layoutPath = VIEWS_PATH . '/' . str_replace('.', '/', $layout) . '.php';
        
        // 检查布局文件是否存在
        if (!file_exists($layoutPath)) {
            throw new \Exception("布局 {$layout} 不存在");
        }
        
        // 再次启动输出缓冲
        ob_start();
        
        // 包含布局文件
        include $layoutPath;
        
        // 获取完整页面内容
        $page = ob_get_clean();
        
        return $page;
    }
    
    /**
     * 输出视图
     * @param string $view 视图路径，使用点号分隔
     * @param array $data 要传递给视图的数据
     * @param string|null $layout 布局文件路径，不使用布局则传null
     * @return void
     */
    public static function display($view, array $data = [], $layout = 'layouts.app')
    {
        echo self::render($view, $data, $layout);
    }
    
    /**
     * 生成完整URL
     * @param string $path 相对路径
     * @return string 完整URL
     */
    public static function url($path = '')
    {
        $baseUrl = Config::get('app.url', 'http://localhost');
        
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
    
    /**
     * 生成资源URL
     * @param string $path 资源相对路径
     * @return string 资源URL
     */
    public static function asset($path)
    {
        return self::url('assets/' . ltrim($path, '/'));
    }
    
    /**
     * 设置页面标题
     * @param string $title 页面标题
     * @param string $separator 分隔符
     * @return string 格式化的页面标题
     */
    public static function title($title, $separator = ' - ')
    {
        $appName = Config::get('app.name', 'AlingAi Pro');
        
        return $title . $separator . $appName;
    }
    
    /**
     * 加载局部视图
     * @param string $view 视图路径，使用点号分隔
     * @param array $data 要传递给视图的数据
     * @return string 渲染后的内容
     */
    public static function partial($view, array $data = [])
    {
        // 提取数据到变量
        extract($data);
        
        // 构建视图文件路径
        $viewPath = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        
        // 检查视图文件是否存在
        if (!file_exists($viewPath)) {
            throw new \Exception("局部视图 {$view} 不存在");
        }
        
        // 启动输出缓冲
        ob_start();
        
        // 包含视图文件
        include $viewPath;
        
        // 获取渲染后的内容
        return ob_get_clean();
    }
    
    /**
     * 显示CSRF令牌字段
     * @return string HTML表单字段
     */
    public static function csrfField()
    {
        return Security::csrfField();
    }
    
    /**
     * 检查当前URL是否与给定路径匹配
     * @param string $path 路径
     * @return bool 是否匹配
     */
    public static function isActive($path)
    {
        $currentPath = $_SERVER['REQUEST_URI'] ?? '';
        
        // 提取路径部分
        $currentPath = parse_url($currentPath, PHP_URL_PATH);
        
        // 规范化路径
        $currentPath = '/' . trim($currentPath, '/');
        $path = '/' . trim($path, '/');
        
        return $currentPath === $path || strpos($currentPath, $path) === 0;
    }
    
    /**
     * 格式化日期
     * @param string|int $date 日期字符串或时间戳
     * @param string $format 日期格式
     * @return string 格式化后的日期
     */
    public static function formatDate($date, $format = 'Y-m-d H:i:s')
    {
        if (is_numeric($date)) {
            return date($format, $date);
        }
        
        return date($format, strtotime($date));
    }
    
    /**
     * 将字符串截断到指定长度
     * @param string $string 原始字符串
     * @param int $length 截断长度
     * @param string $append 追加内容
     * @return string 截断后的字符串
     */
    public static function truncate($string, $length = 100, $append = '...')
    {
        if (mb_strlen($string, 'UTF-8') <= $length) {
            return $string;
        }
        
        return mb_substr($string, 0, $length, 'UTF-8') . $append;
    }
    
    /**
     * HTML转义
     * @param string $string 原始字符串
     * @return string 转义后的字符串
     */
    public static function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * 生成分页HTML
     * @param int $total 总记录数
     * @param int $perPage 每页记录数
     * @param int $currentPage 当前页
     * @param string $urlPattern URL模式，使用:page作为页码占位符
     * @return string 分页HTML
     */
    public static function paginate($total, $perPage, $currentPage, $urlPattern)
    {
        $totalPages = ceil($total / $perPage);
        
        if ($totalPages <= 1) {
            return '';
        }
        
        $html = '<nav aria-label="分页导航"><ul class="pagination">';
        
        // 上一页
        if ($currentPage > 1) {
            $prevUrl = str_replace(':page', $currentPage - 1, $urlPattern);
            $html .= '<li class="page-item"><a class="page-link" href="' . $prevUrl . '">&laquo; 上一页</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">&laquo; 上一页</span></li>';
        }
        
        // 页码
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);
        
        if ($start > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . str_replace(':page', 1, $urlPattern) . '">1</a></li>';
            if ($start > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        for ($i = $start; $i <= $end; $i++) {
            if ($i == $currentPage) {
                $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                $pageUrl = str_replace(':page', $i, $urlPattern);
                $html .= '<li class="page-item"><a class="page-link" href="' . $pageUrl . '">' . $i . '</a></li>';
            }
        }
        
        if ($end < $totalPages) {
            if ($end < $totalPages - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $html .= '<li class="page-item"><a class="page-link" href="' . str_replace(':page', $totalPages, $urlPattern) . '">' . $totalPages . '</a></li>';
        }
        
        // 下一页
        if ($currentPage < $totalPages) {
            $nextUrl = str_replace(':page', $currentPage + 1, $urlPattern);
            $html .= '<li class="page-item"><a class="page-link" href="' . $nextUrl . '">下一页 &raquo;</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">下一页 &raquo;</span></li>';
        }
        
        $html .= '</ul></nav>';
        
        return $html;
    }
} 