<?php

namespace AlingAi\Core;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;

/**
 * 依赖注入容器
 * 
 * 管理应用程序的服务和依赖
 * 
 * @package AlingAi\Core
 * @version 6.0.0
 */
class Container implements ContainerInterface
{
    /**
     * 已注册的服务定义
     */
    protected array $definitions = [];
    
    /**
     * 已实例化的服务
     */
    protected array $instances = [];
    
    /**
     * 服务别名
     */
    protected array $aliases = [];
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 注册自身
        $this->instances[self::class] = $this;
        $this->instances[ContainerInterface::class] = $this;
    }
    
    /**
     * 注册服务
     * 
     * @param string $id 服务ID
     * @param mixed $concrete 服务定义（类名或回调函数）
     * @param bool $shared 是否共享实例
     * @return self
     */
    public function set(string $id, $concrete = null, bool $shared = true): self
    {
        // 如果没有提供具体实现，使用ID作为类名
        if (is_null($concrete)) {
            $concrete = $id;
        }
        
        // 存储服务定义
        $this->definitions[$id] = [
            'concrete' => $concrete,
            'shared' => $shared
        ];
        
        // 如果是共享服务且已存在实例，则移除实例以便重新创建
        if (isset($this->instances[$id])) {
            unset($this->instances[$id]);
        }
        
        return $this;
    }
    
    /**
     * 注册共享服务
     * 
     * @param string $id 服务ID
     * @param mixed $concrete 服务定义
     * @return self
     */
    public function singleton(string $id, $concrete = null): self
    {
        return $this->set($id, $concrete, true);
    }
    
    /**
     * 注册非共享服务
     * 
     * @param string $id 服务ID
     * @param mixed $concrete 服务定义
     * @return self
     */
    public function factory(string $id, $concrete = null): self
    {
        return $this->set($id, $concrete, false);
    }
    
    /**
     * 注册服务别名
     * 
     * @param string $id 服务ID
     * @param string $alias 别名
     * @return self
     */
    public function alias(string $id, string $alias): self
    {
        $this->aliases[$alias] = $id;
        return $this;
    }
    
    /**
     * 检查服务是否已注册
     * 
     * @param string $id 服务ID
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->definitions[$id]) || 
               isset($this->instances[$id]) || 
               isset($this->aliases[$id]);
    }
    
    /**
     * 获取服务实例
     * 
     * @param string $id 服务ID
     * @return mixed
     * @throws \Exception 如果服务未找到
     */
    public function get(string $id)
    {
        // 解析别名
        $id = $this->aliases[$id] ?? $id;
        
        // 如果已有实例，直接返回
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }
        
        // 如果没有定义，尝试自动解析
        if (!isset($this->definitions[$id])) {
            if (class_exists($id)) {
                $this->set($id, $id);
            } else {
                throw new \Exception("服务未找到: {$id}");
            }
        }
        
        // 获取服务定义
        $definition = $this->definitions[$id];
        $concrete = $definition['concrete'];
        
        // 根据定义类型创建实例
        if (is_callable($concrete)) {
            $instance = $concrete($this);
        } elseif (is_string($concrete) && class_exists($concrete)) {
            $instance = $this->build($concrete);
        } elseif (is_object($concrete)) {
            $instance = $concrete;
        } else {
            $instance = $concrete;
        }
        
        // 如果是共享服务，存储实例
        if ($definition['shared']) {
            $this->instances[$id] = $instance;
        }
        
        return $instance;
    }
    
    /**
     * 构建类实例
     * 
     * @param string $concrete 类名
     * @return object
     * @throws \Exception 如果无法解析依赖
     */
    protected function build(string $concrete): object
    {
        // 创建反射类
        $reflector = new ReflectionClass($concrete);
        
        // 检查是否可实例化
        if (!$reflector->isInstantiable()) {
            throw new \Exception("类 {$concrete} 不可实例化");
        }
        
        // 获取构造函数
        $constructor = $reflector->getConstructor();
        
        // 如果没有构造函数，直接实例化
        if (is_null($constructor)) {
            return new $concrete();
        }
        
        // 解析构造函数参数
        $parameters = $constructor->getParameters();
        $dependencies = $this->resolveDependencies($parameters);
        
        // 创建实例
        return $reflector->newInstanceArgs($dependencies);
    }
    
    /**
     * 解析依赖
     * 
     * @param ReflectionParameter[] $parameters
     * @return array
     * @throws \Exception 如果无法解析依赖
     */
    protected function resolveDependencies(array $parameters): array
    {
        $dependencies = [];
        
        foreach ($parameters as $parameter) {
            // 获取参数类型
            $type = $parameter->getType();
            
            // 如果是内置类型或没有类型提示，检查是否有默认值
            if (is_null($type) || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } elseif ($parameter->isOptional()) {
                    $dependencies[] = null;
                } else {
                    throw new \Exception("无法解析参数 {$parameter->getName()}");
                }
            } else {
                // 解析类依赖
                $typeName = $type->getName();
                $dependencies[] = $this->get($typeName);
            }
        }
        
        return $dependencies;
    }
    
    /**
     * 移除服务
     * 
     * @param string $id 服务ID
     * @return void
     */
    public function remove(string $id): void
    {
        unset($this->definitions[$id], $this->instances[$id]);
        
        // 移除相关别名
        foreach ($this->aliases as $alias => $target) {
            if ($target === $id) {
                unset($this->aliases[$alias]);
            }
        }
    }
    
    /**
     * 清除所有服务
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->definitions = [];
        $this->instances = [];
        $this->aliases = [];
        
        // 重新注册自身
        $this->instances[self::class] = $this;
        $this->instances[ContainerInterface::class] = $this;
    }
}
