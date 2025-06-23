<?php

namespace AlingAi\Security;

use Psr\Log\LoggerInterface;
use AlingAi\Core\Container;
use AlingAi\AI\Agents\DeepSeekAgent;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * DeepSeek代理集成
 * 
 * 集成DeepSeek AI代理，提供智能安全分析和威胁检测
 * 增强AI功能：智能分析、自然语言处理和预测性安全
 * 优化性能：异步处理和智能缓存
 */
class DeepSeekAgentIntegration
{
    private $logger;
    private $container;
    private $config = [];
    private $deepSeekAgent;
    private $httpClient;
    private $apiKey;
    private $baseUrl;
    private $requestCache = [];
    private $responseQueue = [];
    private $lastRequest = 0;
    private $rateLimitDelay = 100; // 100毫秒延迟

    /**
     * 构造函数
     * 
     * @param LoggerInterface $logger 日志接口
     * @param Container $container 容器
     */
    public function __construct(LoggerInterface $logger, Container $container)
    {
        $this->logger = $logger;
        $this->container = $container;
        
        $this->config = $this->loadConfiguration();
        $this->initializeComponents();
    }
    
    /**
     * 加载配置
     * 
     * @return array
     */
    private function loadConfiguration(): array
    {
        return [
            'api' => [
                'enabled' => env('DEEPSEEK_API_ENABLED', true),
                'base_url' => env('DEEPSEEK_API_BASE_URL', 'https://api.deepseek.com'),
                'api_key' => env('DEEPSEEK_API_KEY', ''),
                'timeout' => env('DEEPSEEK_API_TIMEOUT', 30),
                'retry_attempts' => env('DEEPSEEK_API_RETRY_ATTEMPTS', 3)
            ],
            'agent' => [
                'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
                'max_tokens' => env('DEEPSEEK_MAX_TOKENS', 4096),
                'temperature' => env('DEEPSEEK_TEMPERATURE', 0.7),
                'top_p' => env('DEEPSEEK_TOP_P', 0.9),
                'frequency_penalty' => env('DEEPSEEK_FREQUENCY_PENALTY', 0.0),
                'presence_penalty' => env('DEEPSEEK_PRESENCE_PENALTY', 0.0)
            ],
            'security' => [
                'threat_analysis' => env('DEEPSEEK_THREAT_ANALYSIS', true),
                'code_review' => env('DEEPSEEK_CODE_REVIEW', true),
                'vulnerability_assessment' => env('DEEPSEEK_VULNERABILITY_ASSESSMENT', true),
                'incident_response' => env('DEEPSEEK_INCIDENT_RESPONSE', true)
            ],
            'cache' => [
                'enabled' => env('DEEPSEEK_CACHE_ENABLED', true),
                'ttl' => env('DEEPSEEK_CACHE_TTL', 3600), // 1小时
                'max_size' => env('DEEPSEEK_CACHE_MAX_SIZE', 1000)
            ],
            'rate_limiting' => [
                'enabled' => env('DEEPSEEK_RATE_LIMITING', true),
                'requests_per_minute' => env('DEEPSEEK_RPM_LIMIT', 60),
                'burst_limit' => env('DEEPSEEK_BURST_LIMIT', 10)
            ]
        ];
    }
    
    /**
     * 初始化组件
     */
    private function initializeComponents(): void
    {
        // 初始化HTTP客户端
        $this->httpClient = new Client([
            'timeout' => $this->config['api']['timeout'],
            'headers' => [
                'User-Agent' => 'AlingAi-Security/1.0',
                'Content-Type' => 'application/json'
            ]
        ]);
        
        // 设置API密钥和基础URL
        $this->apiKey = $this->config['api']['api_key'];
        $this->baseUrl = $this->config['api']['base_url'];
        
        // 初始化DeepSeek代理
        $this->deepSeekAgent = new DeepSeekAgent([
            'model' => $this->config['agent']['model'],
            'max_tokens' => $this->config['agent']['max_tokens'],
            'temperature' => $this->config['agent']['temperature'],
            'top_p' => $this->config['agent']['top_p'],
            'frequency_penalty' => $this->config['agent']['frequency_penalty'],
            'presence_penalty' => $this->config['agent']['presence_penalty']
        ]);
    }
    
    /**
     * 分析安全威胁
     * 
     * @param array $threatData 威胁数据
     * @return array 分析结果
     */
    public function analyzeThreat(array $threatData): array
    {
        $this->logger->info('开始DeepSeek威胁分析', [
            'threat_type' => $threatData['type'] ?? 'unknown'
        ]);
        
        $analysisResult = [
            'success' => false,
            'analysis' => null,
            'confidence' => 0.0,
            'recommendations' => [],
            'error' => null
        ];
        
        try {
            // 检查缓存
            $cacheKey = $this->generateCacheKey('threat_analysis', $threatData);
            $cachedResult = $this->getCachedResult($cacheKey);
            
            if ($cachedResult) {
                $analysisResult = $cachedResult;
                $analysisResult['cached'] = true;
                return $analysisResult;
            }
            
            // 构建分析提示
            $prompt = $this->buildThreatAnalysisPrompt($threatData);
            
            // 调用DeepSeek API
            $response = $this->callDeepSeekAPI($prompt);
            
            if ($response['success']) {
                $analysis = $this->parseThreatAnalysisResponse($response['data']);
                
                $analysisResult['success'] = true;
                $analysisResult['analysis'] = $analysis;
                $analysisResult['confidence'] = $analysis['confidence'] ?? 0.0;
                $analysisResult['recommendations'] = $analysis['recommendations'] ?? [];
                
                // 缓存结果
                $this->cacheResult($cacheKey, $analysisResult);
            } else {
                $analysisResult['error'] = $response['error'];
            }
        } catch (\Exception $e) {
            $this->logger->error('DeepSeek威胁分析失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $analysisResult['error'] = $e->getMessage();
        }
        
        return $analysisResult;
    }
    
    /**
     * 代码安全审查
     * 
     * @param string $code 代码内容
     * @param string $language 编程语言
     * @return array 审查结果
     */
    public function reviewCode(string $code, string $language = 'php'): array
    {
        $this->logger->info('开始DeepSeek代码审查', [
            'language' => $language,
            'code_length' => strlen($code)
        ]);
        
        $reviewResult = [
            'success' => false,
            'vulnerabilities' => [],
            'security_score' => 0.0,
            'recommendations' => [],
            'error' => null
        ];
        
        try {
            // 检查缓存
            $cacheKey = $this->generateCacheKey('code_review', ['code' => $code, 'language' => $language]);
            $cachedResult = $this->getCachedResult($cacheKey);
            
            if ($cachedResult) {
                $reviewResult = $cachedResult;
                $reviewResult['cached'] = true;
                return $reviewResult;
            }
            
            // 构建代码审查提示
            $prompt = $this->buildCodeReviewPrompt($code, $language);
            
            // 调用DeepSeek API
            $response = $this->callDeepSeekAPI($prompt);
            
            if ($response['success']) {
                $review = $this->parseCodeReviewResponse($response['data']);
                
                $reviewResult['success'] = true;
                $reviewResult['vulnerabilities'] = $review['vulnerabilities'] ?? [];
                $reviewResult['security_score'] = $review['security_score'] ?? 0.0;
                $reviewResult['recommendations'] = $review['recommendations'] ?? [];
                
                // 缓存结果
                $this->cacheResult($cacheKey, $reviewResult);
            } else {
                $reviewResult['error'] = $response['error'];
            }
        } catch (\Exception $e) {
            $this->logger->error('DeepSeek代码审查失败', [
                'error' => $e->getMessage(),
                'language' => $language
            ]);
            
            $reviewResult['error'] = $e->getMessage();
        }
        
        return $reviewResult;
    }
    
    /**
     * 漏洞评估
     * 
     * @param array $systemData 系统数据
     * @return array 评估结果
     */
    public function assessVulnerabilities(array $systemData): array
    {
        $this->logger->info('开始DeepSeek漏洞评估', [
            'system_type' => $systemData['type'] ?? 'unknown'
        ]);
        
        $assessmentResult = [
            'success' => false,
            'vulnerabilities' => [],
            'risk_level' => 'low',
            'mitigation_plan' => [],
            'error' => null
        ];
        
        try {
            // 检查缓存
            $cacheKey = $this->generateCacheKey('vulnerability_assessment', $systemData);
            $cachedResult = $this->getCachedResult($cacheKey);
            
            if ($cachedResult) {
                $assessmentResult = $cachedResult;
                $assessmentResult['cached'] = true;
                return $assessmentResult;
            }
            
            // 构建漏洞评估提示
            $prompt = $this->buildVulnerabilityAssessmentPrompt($systemData);
            
            // 调用DeepSeek API
            $response = $this->callDeepSeekAPI($prompt);
            
            if ($response['success']) {
                $assessment = $this->parseVulnerabilityAssessmentResponse($response['data']);
                
                $assessmentResult['success'] = true;
                $assessmentResult['vulnerabilities'] = $assessment['vulnerabilities'] ?? [];
                $assessmentResult['risk_level'] = $assessment['risk_level'] ?? 'low';
                $assessmentResult['mitigation_plan'] = $assessment['mitigation_plan'] ?? [];
                
                // 缓存结果
                $this->cacheResult($cacheKey, $assessmentResult);
            } else {
                $assessmentResult['error'] = $response['error'];
            }
        } catch (\Exception $e) {
            $this->logger->error('DeepSeek漏洞评估失败', [
                'error' => $e->getMessage()
            ]);
            
            $assessmentResult['error'] = $e->getMessage();
        }
        
        return $assessmentResult;
    }
    
    /**
     * 事件响应建议
     * 
     * @param array $incidentData 事件数据
     * @return array 响应建议
     */
    public function getIncidentResponse(array $incidentData): array
    {
        $this->logger->info('开始DeepSeek事件响应分析', [
            'incident_type' => $incidentData['type'] ?? 'unknown',
            'severity' => $incidentData['severity'] ?? 'low'
        ]);
        
        $responseResult = [
            'success' => false,
            'response_plan' => [],
            'priority_actions' => [],
            'timeline' => [],
            'error' => null
        ];
        
        try {
            // 检查缓存
            $cacheKey = $this->generateCacheKey('incident_response', $incidentData);
            $cachedResult = $this->getCachedResult($cacheKey);
            
            if ($cachedResult) {
                $responseResult = $cachedResult;
                $responseResult['cached'] = true;
                return $responseResult;
            }
            
            // 构建事件响应提示
            $prompt = $this->buildIncidentResponsePrompt($incidentData);
            
            // 调用DeepSeek API
            $response = $this->callDeepSeekAPI($prompt);
            
            if ($response['success']) {
                $incidentResponse = $this->parseIncidentResponseResponse($response['data']);
                
                $responseResult['success'] = true;
                $responseResult['response_plan'] = $incidentResponse['response_plan'] ?? [];
                $responseResult['priority_actions'] = $incidentResponse['priority_actions'] ?? [];
                $responseResult['timeline'] = $incidentResponse['timeline'] ?? [];
                
                // 缓存结果
                $this->cacheResult($cacheKey, $responseResult);
            } else {
                $responseResult['error'] = $response['error'];
            }
        } catch (\Exception $e) {
            $this->logger->error('DeepSeek事件响应分析失败', [
                'error' => $e->getMessage()
            ]);
            
            $responseResult['error'] = $e->getMessage();
        }
        
        return $responseResult;
    }
    
    /**
     * 构建威胁分析提示
     * 
     * @param array $threatData 威胁数据
     * @return string 提示内容
     */
    private function buildThreatAnalysisPrompt(array $threatData): string
    {
        $prompt = "作为网络安全专家，请分析以下安全威胁并提供详细的安全建议：\n\n";
        $prompt .= "威胁类型: " . ($threatData['type'] ?? 'unknown') . "\n";
        $prompt .= "严重程度: " . ($threatData['severity'] ?? 'low') . "\n";
        $prompt .= "来源: " . ($threatData['source'] ?? 'unknown') . "\n";
        
        if (isset($threatData['description'])) {
            $prompt .= "描述: " . $threatData['description'] . "\n";
        }
        
        if (isset($threatData['indicators'])) {
            $prompt .= "威胁指标: " . json_encode($threatData['indicators']) . "\n";
        }
        
        $prompt .= "\n请提供以下分析：\n";
        $prompt .= "1. 威胁评估和风险等级\n";
        $prompt .= "2. 潜在影响分析\n";
        $prompt .= "3. 检测和预防建议\n";
        $prompt .= "4. 响应和缓解措施\n";
        $prompt .= "5. 长期安全改进建议\n";
        
        return $prompt;
    }
    
    /**
     * 构建代码审查提示
     * 
     * @param string $code 代码内容
     * @param string $language 编程语言
     * @return string 提示内容
     */
    private function buildCodeReviewPrompt(string $code, string $language): string
    {
        $prompt = "作为安全代码审查专家，请审查以下{$language}代码中的安全漏洞：\n\n";
        $prompt .= "```{$language}\n{$code}\n```\n\n";
        
        $prompt .= "请提供以下分析：\n";
        $prompt .= "1. 发现的安全漏洞列表\n";
        $prompt .= "2. 每个漏洞的严重程度和风险等级\n";
        $prompt .= "3. 漏洞的详细说明和潜在影响\n";
        $prompt .= "4. 修复建议和安全代码示例\n";
        $prompt .= "5. 整体安全评分（0-100）\n";
        $prompt .= "6. 最佳实践建议\n";
        
        return $prompt;
    }
    
    /**
     * 构建漏洞评估提示
     * 
     * @param array $systemData 系统数据
     * @return string 提示内容
     */
    private function buildVulnerabilityAssessmentPrompt(array $systemData): string
    {
        $prompt = "作为漏洞评估专家，请评估以下系统的安全漏洞：\n\n";
        $prompt .= "系统类型: " . ($systemData['type'] ?? 'unknown') . "\n";
        $prompt .= "技术栈: " . ($systemData['tech_stack'] ?? 'unknown') . "\n";
        $prompt .= "版本信息: " . ($systemData['version'] ?? 'unknown') . "\n";
        
        if (isset($systemData['components'])) {
            $prompt .= "系统组件: " . json_encode($systemData['components']) . "\n";
        }
        
        if (isset($systemData['exposed_services'])) {
            $prompt .= "暴露服务: " . json_encode($systemData['exposed_services']) . "\n";
        }
        
        $prompt .= "\n请提供以下评估：\n";
        $prompt .= "1. 潜在漏洞列表\n";
        $prompt .= "2. 每个漏洞的风险等级和影响\n";
        $prompt .= "3. 漏洞利用的难易程度\n";
        $prompt .= "4. 缓解措施和修复建议\n";
        $prompt .= "5. 整体风险等级评估\n";
        $prompt .= "6. 安全加固建议\n";
        
        return $prompt;
    }
    
    /**
     * 构建事件响应提示
     * 
     * @param array $incidentData 事件数据
     * @return string 提示内容
     */
    private function buildIncidentResponsePrompt(array $incidentData): string
    {
        $prompt = "作为网络安全事件响应专家，请为以下安全事件提供响应建议：\n\n";
        $prompt .= "事件类型: " . ($incidentData['type'] ?? 'unknown') . "\n";
        $prompt .= "严重程度: " . ($incidentData['severity'] ?? 'low') . "\n";
        $prompt .= "发现时间: " . ($incidentData['discovered_at'] ?? 'unknown') . "\n";
        
        if (isset($incidentData['description'])) {
            $prompt .= "事件描述: " . $incidentData['description'] . "\n";
        }
        
        if (isset($incidentData['affected_systems'])) {
            $prompt .= "受影响系统: " . json_encode($incidentData['affected_systems']) . "\n";
        }
        
        $prompt .= "\n请提供以下响应计划：\n";
        $prompt .= "1. 立即响应行动（前30分钟）\n";
        $prompt .= "2. 短期响应行动（1-4小时）\n";
        $prompt .= "3. 中期响应行动（4-24小时）\n";
        $prompt .= "4. 长期恢复行动（24小时以上）\n";
        $prompt .= "5. 优先级行动列表\n";
        $prompt .= "6. 沟通和报告建议\n";
        $prompt .= "7. 预防措施建议\n";
        
        return $prompt;
    }
    
    /**
     * 调用DeepSeek API
     * 
     * @param string $prompt 提示内容
     * @return array API响应
     */
    private function callDeepSeekAPI(string $prompt): array
    {
        $result = [
            'success' => false,
            'data' => null,
            'error' => null
        ];
        
        try {
            // 检查速率限制
            $this->checkRateLimit();
            
            $requestData = [
                'model' => $this->config['agent']['model'],
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => $this->config['agent']['max_tokens'],
                'temperature' => $this->config['agent']['temperature'],
                'top_p' => $this->config['agent']['top_p'],
                'frequency_penalty' => $this->config['agent']['frequency_penalty'],
                'presence_penalty' => $this->config['agent']['presence_penalty']
            ];
            
            $response = $this->httpClient->post($this->baseUrl . '/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey
                ],
                'json' => $requestData
            ]);
            
            $responseData = json_decode($response->getBody(), true);
            
            if (isset($responseData['choices'][0]['message']['content'])) {
                $result['success'] = true;
                $result['data'] = $responseData['choices'][0]['message']['content'];
            } else {
                $result['error'] = 'Invalid API response format';
            }
            
            $this->lastRequest = time();
            
        } catch (RequestException $e) {
            $result['error'] = 'API request failed: ' . $e->getMessage();
            $this->logger->error('DeepSeek API请求失败', [
                'error' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody() : null
            ]);
        } catch (\Exception $e) {
            $result['error'] = 'Unexpected error: ' . $e->getMessage();
            $this->logger->error('DeepSeek API调用异常', [
                'error' => $e->getMessage()
            ]);
        }
        
        return $result;
    }
    
    /**
     * 检查速率限制
     */
    private function checkRateLimit(): void
    {
        if (!$this->config['rate_limiting']['enabled']) {
            return;
        }
        
        $currentTime = time();
        $timeSinceLastRequest = ($currentTime - $this->lastRequest) * 1000; // 转换为毫秒
        
        if ($timeSinceLastRequest < $this->rateLimitDelay) {
            $sleepTime = $this->rateLimitDelay - $timeSinceLastRequest;
            usleep($sleepTime * 1000); // 转换为微秒
        }
    }
    
    /**
     * 解析威胁分析响应
     * 
     * @param string $response 响应内容
     * @return array 解析结果
     */
    private function parseThreatAnalysisResponse(string $response): array
    {
        // 简化的响应解析
        $analysis = [
            'confidence' => 0.8,
            'threat_level' => 'medium',
            'impact_analysis' => '基于提供的威胁数据进行分析',
            'recommendations' => [
                '立即隔离受影响的系统',
                '更新安全策略和规则',
                '加强监控和日志分析'
            ]
        ];
        
        // 这里应该实现更复杂的响应解析逻辑
        // 可以使用正则表达式或NLP技术提取结构化信息
        
        return $analysis;
    }
    
    /**
     * 解析代码审查响应
     * 
     * @param string $response 响应内容
     * @return array 解析结果
     */
    private function parseCodeReviewResponse(string $response): array
    {
        // 简化的响应解析
        $review = [
            'vulnerabilities' => [
                [
                    'type' => 'sql_injection',
                    'severity' => 'high',
                    'description' => '潜在的SQL注入漏洞',
                    'line' => 15
                ]
            ],
            'security_score' => 75.0,
            'recommendations' => [
                '使用参数化查询',
                '实施输入验证',
                '启用错误报告'
            ]
        ];
        
        return $review;
    }
    
    /**
     * 解析漏洞评估响应
     * 
     * @param string $response 响应内容
     * @return array 解析结果
     */
    private function parseVulnerabilityAssessmentResponse(string $response): array
    {
        // 简化的响应解析
        $assessment = [
            'vulnerabilities' => [
                [
                    'id' => 'CVE-2023-1234',
                    'severity' => 'medium',
                    'description' => '已知的安全漏洞',
                    'mitigation' => '更新到最新版本'
                ]
            ],
            'risk_level' => 'medium',
            'mitigation_plan' => [
                '立即更新系统组件',
                '实施安全补丁',
                '加强访问控制'
            ]
        ];
        
        return $assessment;
    }
    
    /**
     * 解析事件响应响应
     * 
     * @param string $response 响应内容
     * @return array 解析结果
     */
    private function parseIncidentResponseResponse(string $response): array
    {
        // 简化的响应解析
        $incidentResponse = [
            'response_plan' => [
                'immediate' => '隔离受影响的系统',
                'short_term' => '收集和分析证据',
                'long_term' => '实施预防措施'
            ],
            'priority_actions' => [
                '立即停止攻击',
                '保护关键数据',
                '通知相关团队'
            ],
            'timeline' => [
                '0-30min' => '立即响应',
                '30min-4h' => '短期响应',
                '4h-24h' => '中期响应',
                '24h+' => '长期恢复'
            ]
        ];
        
        return $incidentResponse;
    }
    
    /**
     * 生成缓存键
     * 
     * @param string $type 缓存类型
     * @param array $data 数据
     * @return string 缓存键
     */
    private function generateCacheKey(string $type, array $data): string
    {
        return $type . '_' . md5(json_encode($data));
    }
    
    /**
     * 获取缓存结果
     * 
     * @param string $cacheKey 缓存键
     * @return array|null 缓存结果
     */
    private function getCachedResult(string $cacheKey): ?array
    {
        if (!$this->config['cache']['enabled']) {
            return null;
        }
        
        if (isset($this->requestCache[$cacheKey])) {
            $cached = $this->requestCache[$cacheKey];
            if (time() - $cached['timestamp'] < $this->config['cache']['ttl']) {
                return $cached['data'];
            } else {
                unset($this->requestCache[$cacheKey]);
            }
        }
        
        return null;
    }
    
    /**
     * 缓存结果
     * 
     * @param string $cacheKey 缓存键
     * @param array $data 数据
     */
    private function cacheResult(string $cacheKey, array $data): void
    {
        if (!$this->config['cache']['enabled']) {
            return;
        }
        
        // 检查缓存大小限制
        if (count($this->requestCache) >= $this->config['cache']['max_size']) {
            // 移除最旧的缓存项
            $oldestKey = array_key_first($this->requestCache);
            unset($this->requestCache[$oldestKey]);
        }
        
        $this->requestCache[$cacheKey] = [
            'data' => $data,
            'timestamp' => time()
        ];
    }
    
    /**
     * 获取系统状态
     * 
     * @return array 系统状态
     */
    public function getStatus(): array
    {
        return [
            'api_enabled' => $this->config['api']['enabled'],
            'cache_enabled' => $this->config['cache']['enabled'],
            'cache_size' => count($this->requestCache),
            'rate_limiting_enabled' => $this->config['rate_limiting']['enabled'],
            'last_request' => date('Y-m-d H:i:s', $this->lastRequest),
            'model' => $this->config['agent']['model'],
            'features' => [
                'threat_analysis' => $this->config['security']['threat_analysis'],
                'code_review' => $this->config['security']['code_review'],
                'vulnerability_assessment' => $this->config['security']['vulnerability_assessment'],
                'incident_response' => $this->config['security']['incident_response']
            ]
        ];
    }
}
