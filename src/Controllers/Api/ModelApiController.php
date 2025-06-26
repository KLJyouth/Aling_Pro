<?php

namespace AlingAi\Controllers\Api;

use AlingAi\Controllers\BaseController;
use AlingAi\Models\BaseModel;
use AlingAi\Services\ValidatorService;
use AlingAi\Services\AuthService;
use AlingAi\Exceptions\ApiException;
use AlingAi\Exceptions\ValidationException;
use AlingAi\Exceptions\NotFoundException;
use AlingAi\Exceptions\UnauthorizedException;
use AlingAi\Exceptions\ForbiddenException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Exception;

/**
 * 通用数据模型API控制器
 * 
 * 提供对数据模型的标准RESTful API接口
 * 支持增删改查等基本操作，并进行权限验证和参数检查
 *
 * @package AlingAi\Controllers\Api
 * @author AlingAi Team
 * @version 1.0.0
 * @since 2025-06-26
 */
class ModelApiController extends BaseController
{
    /**
     * @var BaseModel 当前操作的模型类
     */
    protected $model;
    
    /**
     * @var string 模型类名
     */
    protected $modelClass;
    
    /**
     * @var array 查询过滤字段
     */
    protected $allowedFilters = [];
    
    /**
     * @var array 排序字段
     */
    protected $allowedSorts = [];
    
    /**
     * @var array 可包含的关联
     */
    protected $allowedIncludes = [];
    
    /**
     * @var array 可查询的字段
     */
    protected $allowedFields = [];
    
    /**
     * @var array 创建验证规则
     */
    protected $createRules = [];
    
    /**
     * @var array 更新验证规则
     */
    protected $updateRules = [];
    
    /**
     * @var string 操作需要的权限前缀
     */
    protected $permissionPrefix = '';
    
    /**
     * 初始化模型实例
     *
     * @param string $modelClass 模型类名
     * @return void
     */
    protected function initModel(string $modelClass): void
    {
        $this->modelClass = $modelClass;
        $this->model = new $modelClass();
    }
    
    /**
     * 获取所有记录
     *
     * @param Request $request 请求对象
     * @param Response $response 响应对象
     * @return Response
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            // 权限检查
            $this->checkPermission("{$this->permissionPrefix}.view");
            
            // 获取请求参数
            $params = $request->getQueryParams();
            
            // 构建查询
            $query = $this->modelClass::query();
            
            // 应用过滤器
            $this->applyFilters($query, $params);
            
            // 应用排序
            $this->applySorting($query, $params);
            
            // 应用关联
            $this->applyIncludes($query, $params);
            
            // 分页
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $perPage = isset($params['per_page']) ? (int)$params['per_page'] : 15;
            $perPage = min($perPage, 100); // 防止请求过多记录
            
            // 执行查询
            $results = $query->paginate($perPage, $this->getAllowedFields($params), 'page', $page);
            
            // 构建响应
            $data = [
                'data' => $results['data'],
                'meta' => [
                    'current_page' => $results['current_page'],
                    'per_page' => $results['per_page'],
                    'total' => $results['total'],
                    'last_page' => $results['last_page']
                ],
                'links' => [
                    'first' => $this->buildPageUrl($request, 1, $perPage),
                    'last' => $this->buildPageUrl($request, $results['last_page'], $perPage),
                    'prev' => $results['current_page'] > 1
                        ? $this->buildPageUrl($request, $results['current_page'] - 1, $perPage)
                        : null,
                    'next' => $results['current_page'] < $results['last_page']
                        ? $this->buildPageUrl($request, $results['current_page'] + 1, $perPage)
                        : null
                ]
            ];
            
            return $this->respondWithJson($response, $data);
        } catch (UnauthorizedException $e) {
            return $this->respondWithError($response, $e->getMessage(), 401);
        } catch (ForbiddenException $e) {
            return $this->respondWithError($response, $e->getMessage(), 403);
        } catch (Exception $e) {
            return $this->respondWithError($response, '查询失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 获取单条记录
     *
     * @param Request $request 请求对象
     * @param Response $response 响应对象
     * @param array $args 路径参数
     * @return Response
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            // 权限检查
            $this->checkPermission("{$this->permissionPrefix}.view");
            
            // 获取ID
            $id = $args['id'] ?? null;
            if (!$id) {
                throw new ApiException('未提供ID', 400);
            }
            
            // 获取请求参数
            $params = $request->getQueryParams();
            
            // 构建查询
            $query = $this->modelClass::query();
            
            // 应用关联
            $this->applyIncludes($query, $params);
            
            // 查找记录
            $record = $query->findOrFail($id);
            
            // 检查用户是否有权限查看该记录
            if (!$this->canViewRecord($record)) {
                throw new ForbiddenException('无权查看该记录');
            }
            
            // 构建响应
            $data = [
                'data' => $record->toArray()
            ];
            
            return $this->respondWithJson($response, $data);
        } catch (UnauthorizedException $e) {
            return $this->respondWithError($response, $e->getMessage(), 401);
        } catch (ForbiddenException $e) {
            return $this->respondWithError($response, $e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->respondWithError($response, '记录不存在', 404);
        } catch (ApiException $e) {
            return $this->respondWithError($response, $e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->respondWithError($response, '获取记录失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 创建记录
     *
     * @param Request $request 请求对象
     * @param Response $response 响应对象
     * @return Response
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            // 权限检查
            $this->checkPermission("{$this->permissionPrefix}.create");
            
            // 获取请求数据
            $data = $request->getParsedBody();
            
            // 验证数据
            $validator = new ValidatorService();
            $validatedData = $validator->validate($data, $this->createRules);
            
            // 创建记录
            $record = $this->model->create($validatedData);
            
            // 构建响应
            $responseData = [
                'data' => $record->toArray(),
                'message' => '创建成功'
            ];
            
            return $this->respondWithJson($response, $responseData, 201);
        } catch (UnauthorizedException $e) {
            return $this->respondWithError($response, $e->getMessage(), 401);
        } catch (ForbiddenException $e) {
            return $this->respondWithError($response, $e->getMessage(), 403);
        } catch (ValidationException $e) {
            return $this->respondWithValidationError($response, $e->getErrors());
        } catch (Exception $e) {
            return $this->respondWithError($response, '创建失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 更新记录
     *
     * @param Request $request 请求对象
     * @param Response $response 响应对象
     * @param array $args 路径参数
     * @return Response
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            // 权限检查
            $this->checkPermission("{$this->permissionPrefix}.update");
            
            // 获取ID
            $id = $args['id'] ?? null;
            if (!$id) {
                throw new ApiException('未提供ID', 400);
            }
            
            // 查找记录
            $record = $this->model->findOrFail($id);
            
            // 检查用户是否有权限更新该记录
            if (!$this->canUpdateRecord($record)) {
                throw new ForbiddenException('无权更新该记录');
            }
            
            // 获取请求数据
            $data = $request->getParsedBody();
            
            // 验证数据
            $validator = new ValidatorService();
            $validatedData = $validator->validate($data, $this->updateRules);
            
            // 更新记录
            $record->update($validatedData);
            
            // 构建响应
            $responseData = [
                'data' => $record->toArray(),
                'message' => '更新成功'
            ];
            
            return $this->respondWithJson($response, $responseData);
        } catch (UnauthorizedException $e) {
            return $this->respondWithError($response, $e->getMessage(), 401);
        } catch (ForbiddenException $e) {
            return $this->respondWithError($response, $e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->respondWithError($response, '记录不存在', 404);
        } catch (ValidationException $e) {
            return $this->respondWithValidationError($response, $e->getErrors());
        } catch (ApiException $e) {
            return $this->respondWithError($response, $e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->respondWithError($response, '更新失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 删除记录
     *
     * @param Request $request 请求对象
     * @param Response $response 响应对象
     * @param array $args 路径参数
     * @return Response
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            // 权限检查
            $this->checkPermission("{$this->permissionPrefix}.delete");
            
            // 获取ID
            $id = $args['id'] ?? null;
            if (!$id) {
                throw new ApiException('未提供ID', 400);
            }
            
            // 查找记录
            $record = $this->model->findOrFail($id);
            
            // 检查用户是否有权限删除该记录
            if (!$this->canDeleteRecord($record)) {
                throw new ForbiddenException('无权删除该记录');
            }
            
            // 删除记录
            $record->delete();
            
            // 构建响应
            $responseData = [
                'message' => '删除成功'
            ];
            
            return $this->respondWithJson($response, $responseData);
        } catch (UnauthorizedException $e) {
            return $this->respondWithError($response, $e->getMessage(), 401);
        } catch (ForbiddenException $e) {
            return $this->respondWithError($response, $e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->respondWithError($response, '记录不存在', 404);
        } catch (ApiException $e) {
            return $this->respondWithError($response, $e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->respondWithError($response, '删除失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 应用过滤条件
     *
     * @param object $query 查询构建器
     * @param array $params 请求参数
     * @return void
     */
    protected function applyFilters($query, array $params): void
    {
        // 检查是否有过滤参数
        if (!isset($params['filter']) || !is_array($params['filter'])) {
            return;
        }
        
        // 应用过滤条件
        foreach ($params['filter'] as $field => $value) {
            // 只允许过滤指定字段
            if (!in_array($field, $this->allowedFilters)) {
                continue;
            }
            
            // 检查是否为范围过滤
            if (is_array($value) && isset($value['min']) && isset($value['max'])) {
                $query->whereBetween($field, [$value['min'], $value['max']]);
            }
            // 检查是否为模糊搜索
            elseif (is_array($value) && isset($value['like'])) {
                $query->where($field, 'LIKE', "%{$value['like']}%");
            }
            // 检查是否为IN查询
            elseif (is_array($value) && isset($value['in']) && is_array($value['in'])) {
                $query->whereIn($field, $value['in']);
            }
            // 检查是否为NOT IN查询
            elseif (is_array($value) && isset($value['not_in']) && is_array($value['not_in'])) {
                $query->whereNotIn($field, $value['not_in']);
            }
            // 检查是否为NULL查询
            elseif (is_array($value) && isset($value['is_null']) && $value['is_null']) {
                $query->whereNull($field);
            }
            // 检查是否为NOT NULL查询
            elseif (is_array($value) && isset($value['is_not_null']) && $value['is_not_null']) {
                $query->whereNotNull($field);
            }
            // 常规相等查询
            else {
                $query->where($field, $value);
            }
        }
    }
    
    /**
     * 应用排序
     *
     * @param object $query 查询构建器
     * @param array $params 请求参数
     * @return void
     */
    protected function applySorting($query, array $params): void
    {
        // 默认排序
        $defaultSort = 'id';
        $defaultDirection = 'desc';
        
        // 检查是否有排序参数
        if (!isset($params['sort'])) {
            $query->orderBy($defaultSort, $defaultDirection);
            return;
        }
        
        // 解析排序参数
        $sortFields = explode(',', $params['sort']);
        
        foreach ($sortFields as $sortField) {
            $direction = 'asc';
            $field = $sortField;
            
            // 检查是否为降序排序
            if (strpos($sortField, '-') === 0) {
                $direction = 'desc';
                $field = substr($sortField, 1);
            }
            
            // 检查是否为允许的排序字段
            if (in_array($field, $this->allowedSorts)) {
                $query->orderBy($field, $direction);
            }
        }
    }
    
    /**
     * 应用关联包含
     *
     * @param object $query 查询构建器
     * @param array $params 请求参数
     * @return void
     */
    protected function applyIncludes($query, array $params): void
    {
        // 检查是否有包含参数
        if (!isset($params['include'])) {
            return;
        }
        
        // 解析包含参数
        $includes = explode(',', $params['include']);
        
        // 只包含允许的关联
        $validIncludes = array_intersect($includes, $this->allowedIncludes);
        
        foreach ($validIncludes as $include) {
            $query->with($include);
        }
    }
    
    /**
     * 获取允许的查询字段
     *
     * @param array $params 请求参数
     * @return array
     */
    protected function getAllowedFields(array $params): array
    {
        // 默认返回所有允许的字段
        if (!isset($params['fields'])) {
            return $this->allowedFields;
        }
        
        // 解析字段参数
        $fields = explode(',', $params['fields']);
        
        // 只返回允许的字段
        return array_intersect($fields, $this->allowedFields);
    }
    
    /**
     * 构建分页URL
     *
     * @param Request $request 请求对象
     * @param int $page 页码
     * @param int $perPage 每页记录数
     * @return string
     */
    protected function buildPageUrl(Request $request, int $page, int $perPage): string
    {
        $uri = $request->getUri();
        $query = $request->getQueryParams();
        
        // 更新分页参数
        $query['page'] = $page;
        $query['per_page'] = $perPage;
        
        // 重建查询字符串
        return $uri->getPath() . '?' . http_build_query($query);
    }
    
    /**
     * 检查是否有权限查看记录
     *
     * @param BaseModel $record 记录对象
     * @return bool
     */
    protected function canViewRecord(BaseModel $record): bool
    {
        // 默认实现：只需要有查看权限即可
        try {
            $this->checkPermission("{$this->permissionPrefix}.view");
            return true;
        } catch (UnauthorizedException | ForbiddenException $e) {
            return false;
        }
    }
    
    /**
     * 检查是否有权限更新记录
     *
     * @param BaseModel $record 记录对象
     * @return bool
     */
    protected function canUpdateRecord(BaseModel $record): bool
    {
        // 默认实现：只需要有更新权限即可
        try {
            $this->checkPermission("{$this->permissionPrefix}.update");
            return true;
        } catch (UnauthorizedException | ForbiddenException $e) {
            return false;
        }
    }
    
    /**
     * 检查是否有权限删除记录
     *
     * @param BaseModel $record 记录对象
     * @return bool
     */
    protected function canDeleteRecord(BaseModel $record): bool
    {
        // 默认实现：只需要有删除权限即可
        try {
            $this->checkPermission("{$this->permissionPrefix}.delete");
            return true;
        } catch (UnauthorizedException | ForbiddenException $e) {
            return false;
        }
    }
    
    /**
     * 检查是否有指定权限
     *
     * @param string $permission 权限名称
     * @return void
     * @throws UnauthorizedException 未登录时抛出
     * @throws ForbiddenException 无权限时抛出
     */
    protected function checkPermission(string $permission): void
    {
        // 获取当前用户
        $user = AuthService::getCurrentUser();
        
        // 检查是否登录
        if (!$user) {
            throw new UnauthorizedException('未登录或会话已过期');
        }
        
        // 检查是否有权限
        if (!AuthService::hasPermission($user, $permission)) {
            throw new ForbiddenException("无权执行此操作: {$permission}");
        }
    }
    
    /**
     * 获取当前用户
     *
     * @param mixed $request 请求对象，可选
     * @return array|null 当前用户数据
     */
    protected function getCurrentUser($request = null): ?array
    {
        return AuthService::getCurrentUser();
    }
} 