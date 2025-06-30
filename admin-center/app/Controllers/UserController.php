<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Security;

/**
 * 用户管理控制器
 * 负责处理用户管理相关请求
 */
class UserController extends Controller
{
    /**
     * 用户列表页
     */
    public function index()
    {
        // 获取分页参数
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10;
        
        // 获取过滤参数
        $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
        $roleFilter = isset($_GET['role']) ? trim($_GET['role']) : '';
        $statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
        
        // 查询用户
        $userList = $this->getUsers($page, $perPage, $searchTerm, $roleFilter, $statusFilter);
        $totalUsers = $this->countUsers($searchTerm, $roleFilter, $statusFilter);
        
        // 获取角色列表
        $roles = $this->getRoles();
        
        // 渲染视图
        View::display('users.index', [
            'pageTitle' => '用户管理 - IT运维中心',
            'pageHeader' => '用户管理',
            'currentPage' => 'users',
            'breadcrumbs' => [
                '/admin' => '首页',
                '/admin/users' => '用户管理'
            ],
            'users' => $userList,
            'totalUsers' => $totalUsers,
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($totalUsers / $perPage),
            'searchTerm' => $searchTerm,
            'roleFilter' => $roleFilter,
            'statusFilter' => $statusFilter,
            'roles' => $roles,
            'pageActions' => '
                <a href="/admin/users/create" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> 添加用户
                </a>
            '
        ]);
    }
    
    /**
     * 创建用户表单页
     */
    public function create()
    {
        // 获取角色列表
        $roles = $this->getRoles();
        
        // 渲染视图
        View::display('users.create', [
            'pageTitle' => '添加用户 - IT运维中心',
            'pageHeader' => '添加用户',
            'currentPage' => 'users',
            'breadcrumbs' => [
                '/admin' => '首页',
                '/admin/users' => '用户管理',
                '/admin/users/create' => '添加用户'
            ],
            'roles' => $roles
        ]);
    }
    
    /**
     * 保存新用户
     */
    public function store()
    {
        // 验证CSRF令牌
        if (!Security::validateCsrfToken()) {
            $_SESSION['flash_message'] = '安全验证失败，请重试';
            $_SESSION['flash_message_type'] = 'danger';
            header('Location: /admin/users/create');
            exit;
        }
        
        // 获取表单数据
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? 'user');
        $status = trim($_POST['status'] ?? 'active');
        
        // 验证数据
        $errors = [];
        
        if (empty($username)) {
            $errors[] = '用户名不能为空';
        } elseif ($this->isUsernameExists($username)) {
            $errors[] = '该用户名已被使用';
        }
        
        if (empty($password)) {
            $errors[] = '密码不能为空';
        } elseif (strlen($password) < 6) {
            $errors[] = '密码长度不能少于6个字符';
        }
        
        if (empty($email)) {
            $errors[] = '邮箱不能为空';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = '邮箱格式不正确';
        } elseif ($this->isEmailExists($email)) {
            $errors[] = '该邮箱已被使用';
        }
        
        // 如果有错误，重新显示表单
        if (!empty($errors)) {
            $_SESSION['flash_message'] = implode('<br>', $errors);
            $_SESSION['flash_message_type'] = 'danger';
            $_SESSION['form_data'] = $_POST;
            header('Location: /admin/users/create');
            exit;
        }
        
        // 创建用户
        $success = $this->createUser([
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'status' => $status
        ]);
        
        if ($success) {
            $_SESSION['flash_message'] = '用户添加成功';
            $_SESSION['flash_message_type'] = 'success';
            header('Location: /admin/users');
        } else {
            $_SESSION['flash_message'] = '用户添加失败，请重试';
            $_SESSION['flash_message_type'] = 'danger';
            $_SESSION['form_data'] = $_POST;
            header('Location: /admin/users/create');
        }
        
        exit;
    }
    
    /**
     * 编辑用户表单页
     */
    public function edit($id)
    {
        // 获取用户数据
        $user = $this->getUserById($id);
        
        if (!$user) {
            $_SESSION['flash_message'] = '用户不存在';
            $_SESSION['flash_message_type'] = 'danger';
            header('Location: /admin/users');
            exit;
        }
        
        // 获取角色列表
        $roles = $this->getRoles();
        
        // 渲染视图
        View::display('users.edit', [
            'pageTitle' => '编辑用户 - IT运维中心',
            'pageHeader' => '编辑用户',
            'currentPage' => 'users',
            'breadcrumbs' => [
                '/admin' => '首页',
                '/admin/users' => '用户管理',
                '/admin/users/edit/' . $id => '编辑用户'
            ],
            'user' => $user,
            'roles' => $roles
        ]);
    }
    
    /**
     * 更新用户
     */
    public function update($id)
    {
        // 验证CSRF令牌
        if (!Security::validateCsrfToken()) {
            $_SESSION['flash_message'] = '安全验证失败，请重试';
            $_SESSION['flash_message_type'] = 'danger';
            header('Location: /admin/users/edit/' . $id);
            exit;
        }
        
        // 获取用户数据
        $user = $this->getUserById($id);
        
        if (!$user) {
            $_SESSION['flash_message'] = '用户不存在';
            $_SESSION['flash_message_type'] = 'danger';
            header('Location: /admin/users');
            exit;
        }
        
        // 获取表单数据
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? 'user');
        $status = trim($_POST['status'] ?? 'active');
        
        // 验证数据
        $errors = [];
        
        if (empty($username)) {
            $errors[] = '用户名不能为空';
        } elseif ($username !== $user['username'] && $this->isUsernameExists($username)) {
            $errors[] = '该用户名已被使用';
        }
        
        if (empty($email)) {
            $errors[] = '邮箱不能为空';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = '邮箱格式不正确';
        } elseif ($email !== $user['email'] && $this->isEmailExists($email)) {
            $errors[] = '该邮箱已被使用';
        }
        
        // 如果有错误，重新显示表单
        if (!empty($errors)) {
            $_SESSION['flash_message'] = implode('<br>', $errors);
            $_SESSION['flash_message_type'] = 'danger';
            $_SESSION['form_data'] = $_POST;
            header('Location: /admin/users/edit/' . $id);
            exit;
        }
        
        // 准备更新数据
        $updateData = [
            'username' => $username,
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'status' => $status
        ];
        
        // 如果提供了新密码，更新密码
        if (!empty($password)) {
            $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        
        // 更新用户
        $success = $this->updateUser($id, $updateData);
        
        if ($success) {
            $_SESSION['flash_message'] = '用户更新成功';
            $_SESSION['flash_message_type'] = 'success';
            header('Location: /admin/users');
        } else {
            $_SESSION['flash_message'] = '用户更新失败，请重试';
            $_SESSION['flash_message_type'] = 'danger';
            $_SESSION['form_data'] = $_POST;
            header('Location: /admin/users/edit/' . $id);
        }
        
        exit;
    }
    
    /**
     * 删除用户
     */
    public function delete($id)
    {
        // 验证CSRF令牌
        if (!Security::validateCsrfToken()) {
            $_SESSION['flash_message'] = '安全验证失败，请重试';
            $_SESSION['flash_message_type'] = 'danger';
            header('Location: /admin/users');
            exit;
        }
        
        // 获取用户数据
        $user = $this->getUserById($id);
        
        if (!$user) {
            $_SESSION['flash_message'] = '用户不存在';
            $_SESSION['flash_message_type'] = 'danger';
            header('Location: /admin/users');
            exit;
        }
        
        // 防止删除当前登录用户
        if ($user['id'] == $_SESSION['admin_user_id']) {
            $_SESSION['flash_message'] = '无法删除当前登录的用户';
            $_SESSION['flash_message_type'] = 'danger';
            header('Location: /admin/users');
            exit;
        }
        
        // 删除用户
        $success = $this->deleteUser($id);
        
        if ($success) {
            $_SESSION['flash_message'] = '用户删除成功';
            $_SESSION['flash_message_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = '用户删除失败，请重试';
            $_SESSION['flash_message_type'] = 'danger';
        }
        
        header('Location: /admin/users');
        exit;
    }
    
    /**
     * 查看用户详情
     */
    public function show($id)
    {
        // 获取用户数据
        $user = $this->getUserById($id);
        
        if (!$user) {
            $_SESSION['flash_message'] = '用户不存在';
            $_SESSION['flash_message_type'] = 'danger';
            header('Location: /admin/users');
            exit;
        }
        
        // 获取用户登录历史
        $loginHistory = $this->getUserLoginHistory($id);
        
        // 渲染视图
        View::display('users.show', [
            'pageTitle' => '用户详情 - IT运维中心',
            'pageHeader' => '用户详情',
            'currentPage' => 'users',
            'breadcrumbs' => [
                '/admin' => '首页',
                '/admin/users' => '用户管理',
                '/admin/users/show/' . $id => '用户详情'
            ],
            'user' => $user,
            'loginHistory' => $loginHistory
        ]);
    }
    
    /**
     * 获取用户列表
     * 
     * @param int $page 当前页码
     * @param int $perPage 每页数量
     * @param string $searchTerm 搜索关键词
     * @param string $role 角色过滤
     * @param string $status 状态过滤
     * @return array 用户列表
     */
    private function getUsers($page = 1, $perPage = 10, $searchTerm = '', $role = '', $status = '')
    {
        try {
            $db = Database::getInstance();
            
            $sql = "
                SELECT id, username, name, email, role, status, created_at, last_login
                FROM admin_users
                WHERE 1=1
            ";
            
            $params = [];
            
            // 添加搜索条件
            if (!empty($searchTerm)) {
                $sql .= " AND (username LIKE ? OR name LIKE ? OR email LIKE ?)";
                $searchParam = "%{$searchTerm}%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            // 添加角色过滤
            if (!empty($role)) {
                $sql .= " AND role = ?";
                $params[] = $role;
            }
            
            // 添加状态过滤
            if (!empty($status)) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }
            
            // 添加排序和分页
            $sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = ($page - 1) * $perPage;
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            Logger::error('获取用户列表失败: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 获取用户总数
     * 
     * @param string $searchTerm 搜索关键词
     * @param string $role 角色过滤
     * @param string $status 状态过滤
     * @return int 用户总数
     */
    private function countUsers($searchTerm = '', $role = '', $status = '')
    {
        try {
            $db = Database::getInstance();
            
            $sql = "SELECT COUNT(*) FROM admin_users WHERE 1=1";
            
            $params = [];
            
            // 添加搜索条件
            if (!empty($searchTerm)) {
                $sql .= " AND (username LIKE ? OR name LIKE ? OR email LIKE ?)";
                $searchParam = "%{$searchTerm}%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            // 添加角色过滤
            if (!empty($role)) {
                $sql .= " AND role = ?";
                $params[] = $role;
            }
            
            // 添加状态过滤
            if (!empty($status)) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            Logger::error('获取用户总数失败: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * 获取角色列表
     * @return array 角色列表
     */
    private function getRoles()
    {
        return [
            'admin' => '管理员',
            'operator' => '运维人员',
            'user' => '普通用户'
        ];
    }
    
    /**
     * 检查用户名是否已存在
     * @param string $username 用户名
     * @return bool 是否存在
     */
    private function isUsernameExists($username)
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            
            return (int)$stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            Logger::error('检查用户名是否存在失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 检查邮箱是否已存在
     * @param string $email 邮箱
     * @return bool 是否存在
     */
    private function isEmailExists($email)
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT COUNT(*) FROM admin_users WHERE email = ?");
            $stmt->execute([$email]);
            
            return (int)$stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            Logger::error('检查邮箱是否存在失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 创建用户
     * @param array $userData 用户数据
     * @return bool 是否成功
     */
    private function createUser($userData)
    {
        try {
            $db = Database::getInstance();
            $sql = "
                INSERT INTO admin_users (username, password, name, email, role, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ";
            
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                $userData['username'],
                $userData['password'],
                $userData['name'],
                $userData['email'],
                $userData['role'],
                $userData['status'],
                date('Y-m-d H:i:s')
            ]);
            
            return $result;
        } catch (\Exception $e) {
            Logger::error('创建用户失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取单个用户信息
     * @param int $id 用户ID
     * @return array|false 用户信息
     */
    private function getUserById($id)
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT id, username, name, email, role, status, created_at, last_login
                FROM admin_users
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            Logger::error('获取用户信息失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 更新用户
     * @param int $id 用户ID
     * @param array $userData 用户数据
     * @return bool 是否成功
     */
    private function updateUser($id, $userData)
    {
        try {
            $db = Database::getInstance();
            
            $fields = [];
            $params = [];
            
            foreach ($userData as $field => $value) {
                $fields[] = "{$field} = ?";
                $params[] = $value;
            }
            
            $fields[] = "updated_at = ?";
            $params[] = date('Y-m-d H:i:s');
            
            $sql = "UPDATE admin_users SET " . implode(', ', $fields) . " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $db->prepare($sql);
            $result = $stmt->execute($params);
            
            return $result;
        } catch (\Exception $e) {
            Logger::error('更新用户失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 删除用户
     * @param int $id 用户ID
     * @return bool 是否成功
     */
    private function deleteUser($id)
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("DELETE FROM admin_users WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            return $result;
        } catch (\Exception $e) {
            Logger::error('删除用户失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取用户登录历史
     * @param int $id 用户ID
     * @param int $limit 限制数量
     * @return array 登录历史
     */
    private function getUserLoginHistory($id, $limit = 10)
    {
        try {
            $db = Database::getInstance();
            $sql = "
                SELECT id, user_id, ip_address, user_agent, action, created_at
                FROM admin_login_history
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT ?
            ";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$id, $limit]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            Logger::error('获取用户登录历史失败: ' . $e->getMessage());
            return [];
        }
    }
} 