<?php

namespace AlingAi\Controllers\Api;

use AlingAi\Models\User;
use AlingAi\Models\BaseModel;
use AlingAi\Services\AuthService;
use AlingAi\Services\ValidatorService;
use AlingAi\Services\EmailService;
use AlingAi\Exceptions\ApiException;
use AlingAi\Exceptions\ValidationException;
use AlingAi\Exceptions\UnauthorizedException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * 用户模型API控制器
 * 
 * 提供对用户模型的RESTful API接口
 * 实现用户资源的创建、读取、更新和删除操作
 *
 * @package AlingAi\Controllers\Api
 * @author AlingAi Team
 * @version 1.0.0
 * @since 2025-06-26
 */
class UserApiController extends ModelApiController
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 初始化用户模型
        $this->initModel(User::class);
        
        // 设置权限前缀
        $this->permissionPrefix = 'users';
        
        // 设置允许过滤的字段
        $this->allowedFilters = [
            'id',
            'username',
            'email',
            'status',
            'role',
            'created_at',
            'last_login_at'
        ];
        
        // 设置允许排序的字段
        $this->allowedSorts = [
            'id',
            'username',
            'email',
            'created_at',
            'last_login_at'
        ];
        
        // 设置允许包含的关联
        $this->allowedIncludes = [
            'conversations',
            'documents',
            'userLogs'
        ];
        
        // 设置允许查询的字段
        $this->allowedFields = [
            'id',
            'username',
            'email',
            'first_name',
            'last_name',
            'phone',
            'avatar',
            'bio',
            'role',
            'status',
            'email_verified_at',
            'last_login_at',
            'last_login_ip',
            'created_at',
            'updated_at'
        ];
        
        // 设置创建验证规则
        $this->createRules = [
            'username' => 'required|string|min:3|max:50|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'first_name' => 'nullable|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:500',
            'role' => 'nullable|string|in:user,moderator,admin,super_admin',
            'status' => 'nullable|string|in:pending,active,suspended,banned'
        ];
        
        // 设置更新验证规则
        $this->updateRules = [
            'username' => 'sometimes|string|min:3|max:50|unique:users,username',
            'email' => 'sometimes|email|max:255|unique:users,email',
            'password' => 'sometimes|string|min:8',
            'first_name' => 'nullable|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:500',
            'role' => 'nullable|string|in:user,moderator,admin,super_admin',
            'status' => 'nullable|string|in:pending,active,suspended,banned'
        ];
    }
    
    /**
     * 获取当前登录用户信息
     *
     * @param Request $request 请求对象
     * @param Response $response 响应对象
     * @return Response
     */
    public function me(Request $request, Response $response): Response
    {
        try {
            // 获取当前用户
            $user = AuthService::getCurrentUser();
            if (!$user) {
                throw new UnauthorizedException('未授权');
            }
            
            // 获取请求参数
            $params = $request->getQueryParams();
            
            // 判断是否需要包含关联数据
            if (isset($params['include'])) {
                $includes = explode(',', $params['include']);
                $allowedIncludes = array_intersect($includes, $this->allowedIncludes);
                
                foreach ($allowedIncludes as $include) {
                    $user->load($include);
                }
            }
            
            // 构建响应
            $data = [
                'data' => $user->toArray()
            ];
            
            return $this->respondWithJson($response, $data);
        } catch (UnauthorizedException $e) {
            return $this->respondWithError($response, $e->getMessage(), 401);
        } catch (\Exception $e) {
            return $this->respondWithError($response, '获取用户信息失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 验证用户电子邮件
     *
     * @param Request $request 请求对象
     * @param Response $response 响应对象
     * @return Response
     */
    public function verifyEmail(Request $request, Response $response): Response
    {
        try {
            // 获取请求数据
            $data = $request->getParsedBody();
            
            // 验证数据
            if (empty($data['token'])) {
                throw new ValidationException(['token' => ['验证令牌不能为空']]);
            }
            
            // 验证邮箱
            $result = AuthService::verifyEmail($data['token']);
            
            if ($result) {
                return $this->respondWithJson($response, [
                    'success' => true,
                    'message' => '邮箱验证成功'
                ]);
            } else {
                throw new ApiException('邮箱验证失败，令牌无效或已过期');
            }
        } catch (ValidationException $e) {
            return $this->respondWithValidationError($response, $e->getErrors());
        } catch (ApiException $e) {
            return $this->respondWithError($response, $e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->respondWithError($response, '邮箱验证失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 更新用户密码
     *
     * @param Request $request 请求对象
     * @param Response $response 响应对象
     * @return Response
     */
    public function updatePassword(Request $request, Response $response): Response
    {
        try {
            // 获取当前用户
            $user = AuthService::getCurrentUser();
            if (!$user) {
                throw new UnauthorizedException('未授权');
            }
            
            // 获取请求数据
            $data = $request->getParsedBody();
            
            // 验证数据
            if (empty($data['current_password'])) {
                throw new ValidationException(['current_password' => ['当前密码不能为空']]);
            }
            
            if (empty($data['new_password'])) {
                throw new ValidationException(['new_password' => ['新密码不能为空']]);
            }
            
            if (strlen($data['new_password']) < 8) {
                throw new ValidationException(['new_password' => ['新密码长度不能小于8位']]);
            }
            
            // 验证当前密码
            if (!AuthService::verifyPassword($user, $data['current_password'])) {
                throw new ValidationException(['current_password' => ['当前密码不正确']]);
            }
            
            // 更新密码
            AuthService::updatePassword($user, $data['new_password']);
            
            return $this->respondWithJson($response, [
                'success' => true,
                'message' => '密码更新成功'
            ]);
        } catch (UnauthorizedException $e) {
            return $this->respondWithError($response, $e->getMessage(), 401);
        } catch (ValidationException $e) {
            return $this->respondWithValidationError($response, $e->getErrors());
        } catch (\Exception $e) {
            return $this->respondWithError($response, '密码更新失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 生成API令牌
     *
     * @param Request $request 请求对象
     * @param Response $response 响应对象
     * @return Response
     */
    public function generateToken(Request $request, Response $response): Response
    {
        try {
            // 获取当前用户
            $user = AuthService::getCurrentUser();
            if (!$user) {
                throw new UnauthorizedException('未授权');
            }
            
            // 获取请求数据
            $data = $request->getParsedBody();
            
            // 验证数据
            if (empty($data['name'])) {
                throw new ValidationException(['name' => ['令牌名称不能为空']]);
            }
            
            // 生成令牌
            $token = AuthService::generateApiToken($user, $data['name'], $data['permissions'] ?? []);
            
            return $this->respondWithJson($response, [
                'success' => true,
                'message' => '令牌生成成功',
                'data' => [
                    'token' => $token,
                    'name' => $data['name']
                ]
            ]);
        } catch (UnauthorizedException $e) {
            return $this->respondWithError($response, $e->getMessage(), 401);
        } catch (ValidationException $e) {
            return $this->respondWithValidationError($response, $e->getErrors());
        } catch (\Exception $e) {
            return $this->respondWithError($response, '令牌生成失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 撤销API令牌
     *
     * @param Request $request 请求对象
     * @param Response $response 响应对象
     * @return Response
     */
    public function revokeToken(Request $request, Response $response): Response
    {
        try {
            // 获取当前用户
            $user = AuthService::getCurrentUser();
            if (!$user) {
                throw new UnauthorizedException('未授权');
            }
            
            // 获取请求数据
            $data = $request->getParsedBody();
            
            // 验证数据
            if (empty($data['token_id'])) {
                throw new ValidationException(['token_id' => ['令牌ID不能为空']]);
            }
            
            // 撤销令牌
            $result = AuthService::revokeApiToken($user, $data['token_id']);
            
            if ($result) {
                return $this->respondWithJson($response, [
                    'success' => true,
                    'message' => '令牌已撤销'
                ]);
            } else {
                throw new ApiException('令牌撤销失败，可能是令牌ID无效');
            }
        } catch (UnauthorizedException $e) {
            return $this->respondWithError($response, $e->getMessage(), 401);
        } catch (ValidationException $e) {
            return $this->respondWithValidationError($response, $e->getErrors());
        } catch (ApiException $e) {
            return $this->respondWithError($response, $e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->respondWithError($response, '令牌撤销失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 获取用户的API令牌列表
     *
     * @param Request $request 请求对象
     * @param Response $response 响应对象
     * @return Response
     */
    public function listTokens(Request $request, Response $response): Response
    {
        try {
            // 获取当前用户
            $user = AuthService::getCurrentUser();
            if (!$user) {
                throw new UnauthorizedException('未授权');
            }
            
            // 获取用户的令牌列表
            $tokens = AuthService::getUserTokens($user);
            
            return $this->respondWithJson($response, [
                'success' => true,
                'data' => $tokens
            ]);
        } catch (UnauthorizedException $e) {
            return $this->respondWithError($response, $e->getMessage(), 401);
        } catch (\Exception $e) {
            return $this->respondWithError($response, '获取令牌列表失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 检查当前用户是否有权限查看记录
     *
     * @param BaseModel $record 记录
     * @return bool
     */
    protected function canViewRecord(BaseModel $record): bool
    {
        $auth = AuthService::getInstance();
        $currentUser = $auth->getUser();
        
        if (!$currentUser) {
            return false;
        }
        
        // 管理员可以查看所有用户
        if ($currentUser->isAdmin()) {
            return true;
        }
        
        // 用户只能查看自己的信息
        return $currentUser->id == $record->id;
    }
    
    /**
     * 检查当前用户是否有权限更新记录
     *
     * @param BaseModel $record 记录
     * @return bool
     */
    protected function canUpdateRecord(BaseModel $record): bool
    {
        $auth = AuthService::getInstance();
        $currentUser = $auth->getUser();
        
        if (!$currentUser) {
            return false;
        }
        
        // 管理员可以更新所有用户
        if ($currentUser->isAdmin()) {
            return true;
        }
        
        // 普通用户只能更新自己的信息
        return $currentUser->id == $record->id;
    }
    
    /**
     * 检查当前用户是否有权限删除记录
     *
     * @param BaseModel $record 记录
     * @return bool
     */
    protected function canDeleteRecord(BaseModel $record): bool
    {
        $auth = AuthService::getInstance();
        $currentUser = $auth->getUser();
        
        if (!$currentUser) {
            return false;
        }
        
        // 只有管理员可以删除用户
        if (!$currentUser->isAdmin()) {
            return false;
        }
        
        // 管理员不能删除超级管理员，除非自己也是超级管理员
        if ($record->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            return false;
        }
        
        // 不能删除自己的账户
        return $currentUser->id != $record->id;
    }
} 