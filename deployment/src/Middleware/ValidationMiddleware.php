<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\ValidationException;

class ValidationMiddleware implements MiddlewareInterface
{
    private array $rules;
    private array $config;
    
    public function __construct(array $rules = [], array $config = [])
    {
        $this->rules = $rules;
        $this->config = array_merge([
            'stop_on_first_failure' => false,
            'sanitize_input' => true,
            'validate_headers' => false,
            'validate_query' => true,
            'validate_body' => true,
        ], $config);
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = $request->getMethod();
        $uri = $request->getUri()->getPath();
        
        // 获取适用的验证规则
        $applicableRules = $this->getApplicableRules($method, $uri);
        
        if (empty($applicableRules)) {
            return $handler->handle($request);
        }
        
        $errors = [];
        $sanitizedData = [];
        
        try {
            // 验证和清理数据
            if ($this->config['validate_query']) {
                $queryResult = $this->validateData(
                    $request->getQueryParams(),
                    $applicableRules['query'] ?? []
                );
                $errors = array_merge($errors, $queryResult['errors']);
                $sanitizedData['query'] = $queryResult['data'];
            }
            
            if ($this->config['validate_body']) {
                $bodyData = $this->getBodyData($request);
                $bodyResult = $this->validateData(
                    $bodyData,
                    $applicableRules['body'] ?? []
                );
                $errors = array_merge($errors, $bodyResult['errors']);
                $sanitizedData['body'] = $bodyResult['data'];
            }
            
            if ($this->config['validate_headers']) {
                $headerResult = $this->validateData(
                    $this->flattenHeaders($request->getHeaders()),
                    $applicableRules['headers'] ?? []
                );
                $errors = array_merge($errors, $headerResult['errors']);
                $sanitizedData['headers'] = $headerResult['data'];
            }
            
            // 如果有验证错误，返回错误响应
            if (!empty($errors)) {
                return $this->createValidationErrorResponse($errors);
            }
            
            // 将清理后的数据添加到请求中
            $request = $this->addSanitizedDataToRequest($request, $sanitizedData);
            
        } catch (\Exception $e) {
            return $this->createValidationErrorResponse([
                'validation_error' => 'Validation process failed: ' . $e->getMessage()
            ]);
        }
        
        return $handler->handle($request);
    }
    
    private function getApplicableRules(string $method, string $uri): array
    {
        $applicableRules = [];
        
        foreach ($this->rules as $pattern => $rules) {
            if ($this->matchesPattern($pattern, $method, $uri)) {
                $applicableRules = array_merge_recursive($applicableRules, $rules);
            }
        }
        
        return $applicableRules;
    }
    
    private function matchesPattern(string $pattern, string $method, string $uri): bool
    {
        // 支持格式: "POST /api/users", "/api/users", "POST *", "*"
        if ($pattern === '*') {
            return true;
        }
        
        $parts = explode(' ', $pattern, 2);
        
        if (count($parts) === 2) {
            [$patternMethod, $patternUri] = $parts;
            if (strtoupper($patternMethod) !== $method) {
                return false;
            }
            $pattern = $patternUri;
        } else {
            $pattern = $parts[0];
        }
        
        // 支持通配符匹配
        $pattern = str_replace(['*', '/'], ['.*', '\/'], $pattern);
        return preg_match("/^{$pattern}$/", $uri);
    }
    
    private function validateData(array $data, array $rules): array
    {
        $errors = [];
        $sanitizedData = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            try {
                // 创建验证器
                $validator = $this->createValidator($rule);
                
                // 执行验证
                if ($value !== null) {
                    $validator->assert($value);
                    
                    // 清理数据
                    $sanitizedData[$field] = $this->sanitizeValue($value, $rule);
                } elseif ($this->isRequired($rule)) {
                    throw new ValidationException('Field is required');
                }
                
            } catch (ValidationException $e) {
                $errors[$field] = $this->formatValidationError($e);
                
                if ($this->config['stop_on_first_failure']) {
                    break;
                }
            }
        }
        
        return ['errors' => $errors, 'data' => $sanitizedData];
    }
    
    private function createValidator($rule): v
    {
        if ($rule instanceof v) {
            return $rule;
        }
        
        if (is_string($rule)) {
            return $this->createValidatorFromString($rule);
        }
        
        if (is_array($rule)) {
            return $this->createValidatorFromArray($rule);
        }
        
        throw new \InvalidArgumentException('Invalid validation rule format');
    }
    
    private function createValidatorFromString(string $rule): v
    {
        // 支持简单的字符串规则，如: "required|string|min:3|max:255"
        $rules = explode('|', $rule);
        $validator = v::alwaysValid();
        
        foreach ($rules as $r) {
            $validator = $this->addRuleToValidator($validator, $r);
        }
        
        return $validator;
    }
    
    private function createValidatorFromArray(array $rule): v
    {
        $validator = v::alwaysValid();
        
        foreach ($rule as $r) {
            if (is_string($r)) {
                $validator = $this->addRuleToValidator($validator, $r);
            } elseif ($r instanceof v) {
                $validator = $validator->addRule($r);
            }
        }
        
        return $validator;
    }
    
    private function addRuleToValidator(v $validator, string $rule): v
    {
        if (str_contains($rule, ':')) {
            [$ruleName, $params] = explode(':', $rule, 2);
            $params = explode(',', $params);
        } else {
            $ruleName = $rule;
            $params = [];
        }
        
        switch (strtolower($ruleName)) {
            case 'required':
                return $validator->notEmpty();
            case 'string':
                return $validator->stringType();
            case 'int':
            case 'integer':
                return $validator->intType();
            case 'float':
            case 'numeric':
                return $validator->floatType();
            case 'email':
                return $validator->email();
            case 'url':
                return $validator->url();
            case 'min':
                return $validator->length((int)$params[0], null);
            case 'max':
                return $validator->length(null, (int)$params[0]);
            case 'between':
                return $validator->length((int)$params[0], (int)$params[1]);
            case 'regex':
                return $validator->regex($params[0]);
            case 'in':
                return $validator->in($params);
            case 'date':
                return $validator->date();
            case 'uuid':
                return $validator->uuid();
            case 'json':
                return $validator->json();
            case 'ip':
                return $validator->ip();
            default:
                return $validator;
        }
    }
    
    private function isRequired($rule): bool
    {
        if ($rule instanceof v) {
            // 这里需要检查验证器是否包含必填规则
            // 由于Respect\Validation的限制，这里简化处理
            return false;
        }
        
        if (is_string($rule)) {
            return str_contains(strtolower($rule), 'required');
        }
        
        if (is_array($rule)) {
            foreach ($rule as $r) {
                if (is_string($r) && str_contains(strtolower($r), 'required')) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    private function sanitizeValue($value, $rule)
    {
        if (!$this->config['sanitize_input']) {
            return $value;
        }
        
        if (is_string($value)) {
            // 基本的HTML清理
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            
            // 移除多余的空白字符
            $value = trim($value);
            
            // 根据规则进行特定清理
            if (is_string($rule) && str_contains($rule, 'email')) {
                $value = filter_var($value, FILTER_SANITIZE_EMAIL);
            }
        }
        
        return $value;
    }
    
    private function formatValidationError(ValidationException $e): string
    {
        $messages = $e->getMessages();
        return is_array($messages) ? implode(', ', $messages) : (string)$messages;
    }
    
    private function getBodyData(ServerRequestInterface $request): array
    {
        $contentType = $request->getHeaderLine('Content-Type');
        
        if (str_contains($contentType, 'application/json')) {
            $body = (string) $request->getBody();
            return json_decode($body, true) ?: [];
        }
        
        $parsedBody = $request->getParsedBody();
        return is_array($parsedBody) ? $parsedBody : [];
    }
    
    private function flattenHeaders(array $headers): array
    {
        $flattened = [];
        foreach ($headers as $name => $values) {
            $flattened[strtolower($name)] = is_array($values) ? implode(', ', $values) : $values;
        }
        return $flattened;
    }
    
    private function addSanitizedDataToRequest(ServerRequestInterface $request, array $sanitizedData): ServerRequestInterface
    {
        if (isset($sanitizedData['body'])) {
            $request = $request->withParsedBody($sanitizedData['body']);
        }
        
        if (isset($sanitizedData['query'])) {
            $request = $request->withQueryParams($sanitizedData['query']);
        }
        
        // 将清理后的数据作为属性添加到请求中
        $request = $request->withAttribute('sanitized_data', $sanitizedData);
        
        return $request;
    }
    
    private function createValidationErrorResponse(array $errors): ResponseInterface
    {
        $response = new \Slim\Psr7\Response();
        
        $body = json_encode([
            'error' => 'Validation failed',
            'errors' => $errors,
            'timestamp' => date('c')
        ], JSON_PRETTY_PRINT);
        
        $response->getBody()->write($body);
        
        return $response
            ->withStatus(422)
            ->withHeader('Content-Type', 'application/json');
    }
}
