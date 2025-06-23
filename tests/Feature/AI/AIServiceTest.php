<?php
declare(strict_types=1);

namespace Tests\Feature\AI;

use Tests\TestCase;
use AlingAi\AIPlatform\Services\AIServiceManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * AI平台功能测试
 */
class AIServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    
    private AIServiceManager $aiService;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->aiService = app(AIServiceManager::class);
    }
    
    /**
     * 测试多模态AI分析
     */
    public function test_multimodal_ai_analysis(): void
    {
        $analysisData = [
            'task_type' => 'multimodal_analysis',
            'inputs' => [
                'text' => 'Analyze this product review: "Great product, fast delivery, excellent quality!"',
                'image_url' => 'https://example.com/product-image.jpg',
                'audio_url' => 'https://example.com/customer-feedback.mp3'
            ],
            'analysis_goals' => [
                'sentiment_analysis',
                'product_quality_assessment',
                'customer_satisfaction_score'
            ],
            'output_format' => 'detailed_json'
        ];
        
        $result = $this->aiService->performMultimodalAnalysis($analysisData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('analysis_results', $result);
        $this->assertArrayHasKey('sentiment_analysis', $result['analysis_results']);
        $this->assertArrayHasKey('confidence_scores', $result);
        $this->assertArrayHasKey('processing_time', $result);
    }
    
    /**
     * 测试智能对话系统
     */
    public function test_intelligent_chat_system(): void
    {
        $chatData = [
            'session_id' => 'chat_session_' . uniqid(),
            'user_input' => 'Can you help me create a business plan for a tech startup?',
            'context' => [
                'user_profile' => [
                    'industry_experience' => 'technology',
                    'business_stage' => 'ideation',
                    'target_market' => 'B2B_SaaS'
                ],
                'conversation_history' => []
            ],
            'response_style' => 'professional_advisory',
            'max_tokens' => 1000
        ];
        
        $result = $this->aiService->processIntelligentChat($chatData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('response', $result);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertArrayHasKey('follow_up_questions', $result);
        $this->assertNotEmpty($result['response']);
    }
    
    /**
     * 测试代码生成功能
     */
    public function test_code_generation(): void
    {
        $codeGenData = [
            'task_description' => 'Create a REST API endpoint for user authentication',
            'programming_language' => 'php',
            'framework' => 'laravel',
            'requirements' => [
                'jwt_authentication',
                'input_validation',
                'error_handling',
                'rate_limiting'
            ],
            'coding_style' => 'psr-12',
            'include_tests' => true,
            'include_documentation' => true
        ];
        
        $result = $this->aiService->generateCode($codeGenData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('generated_code', $result);
        $this->assertArrayHasKey('test_code', $result);
        $this->assertArrayHasKey('documentation', $result);
        $this->assertStringContainsString('<?php', $result['generated_code']);
    }
    
    /**
     * 测试智能文档处理
     */
    public function test_intelligent_document_processing(): void
    {
        $documentData = [
            'document_type' => 'business_contract',
            'content' => 'This is a sample business contract content...',
            'processing_tasks' => [
                'key_terms_extraction',
                'risk_assessment',
                'compliance_check',
                'summary_generation'
            ],
            'domain_expertise' => 'legal_business',
            'output_language' => 'zh-CN'
        ];
        
        $result = $this->aiService->processIntelligentDocument($documentData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('extracted_terms', $result);
        $this->assertArrayHasKey('risk_assessment', $result);
        $this->assertArrayHasKey('compliance_status', $result);
        $this->assertArrayHasKey('document_summary', $result);
    }
    
    /**
     * 测试AI模型管理
     */
    public function test_ai_model_management(): void
    {
        $modelData = [
            'name' => 'Custom Business Analyzer',
            'description' => 'AI model for business analysis tasks',
            'model_type' => 'nlp',
            'provider' => 'openai',
            'version' => '1.0.0',
            'config' => [
                'model_name' => 'gpt-4',
                'temperature' => 0.7,
                'max_tokens' => 2000
            ],
            'capabilities' => [
                'text_analysis',
                'business_insights',
                'trend_prediction'
            ]
        ];
        
        $result = $this->aiService->registerModel($modelData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('model', $result);
        $this->assertEquals($modelData['name'], $result['model']['name']);
        
        // 测试模型状态更新
        $updateResult = $this->aiService->updateModelStatus(
            $result['model']['model_id'],
            'production'
        );
        
        $this->assertTrue($updateResult['success']);
        $this->assertEquals('production', $updateResult['model']['deployment_status']);
    }
    
    /**
     * 测试提示词管理
     */
    public function test_prompt_management(): void
    {
        $promptData = [
            'name' => 'Business Analysis Prompt',
            'description' => 'Prompt template for business analysis tasks',
            'category' => 'business_analysis',
            'template' => 'Analyze the following business scenario: {scenario}\n\nProvide insights on: {analysis_aspects}\n\nFormat the response as: {output_format}',
            'variables' => [
                'scenario' => ['type' => 'text', 'required' => true],
                'analysis_aspects' => ['type' => 'array', 'required' => true],
                'output_format' => ['type' => 'string', 'default' => 'structured_report']
            ],
            'model_compatibility' => ['gpt-4', 'gpt-3.5-turbo'],
            'use_cases' => ['market_analysis', 'competitor_analysis', 'financial_planning']
        ];
        
        $result = $this->aiService->createPromptTemplate($promptData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('prompt', $result);
        $this->assertEquals($promptData['name'], $result['prompt']['name']);
        
        // 测试提示词使用
        $usePromptData = [
            'prompt_id' => $result['prompt']['prompt_id'],
            'variables' => [
                'scenario' => 'A new SaaS startup entering the CRM market',
                'analysis_aspects' => ['market_size', 'competition', 'pricing_strategy'],
                'output_format' => 'detailed_report'
            ]
        ];
        
        $useResult = $this->aiService->usePromptTemplate($usePromptData);
        
        $this->assertTrue($useResult['success']);
        $this->assertArrayHasKey('generated_prompt', $useResult);
    }
    
    /**
     * 测试AI伦理监控
     */
    public function test_ai_ethics_monitoring(): void
    {
        $ethicsData = [
            'task_id' => 'task_' . uniqid(),
            'model_output' => 'This is a sample AI-generated content that needs ethics review...',
            'input_context' => 'User asked for advice on business strategy',
            'monitoring_aspects' => [
                'bias_detection',
                'fairness_assessment',
                'privacy_compliance',
                'harmful_content_check'
            ]
        ];
        
        $result = $this->aiService->performEthicsMonitoring($ethicsData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('ethics_score', $result);
        $this->assertArrayHasKey('bias_analysis', $result);
        $this->assertArrayHasKey('compliance_status', $result);
        $this->assertArrayHasKey('recommendations', $result);
        
        // 伦理分数应该在0-100之间
        $this->assertGreaterThanOrEqual(0, $result['ethics_score']);
        $this->assertLessThanOrEqual(100, $result['ethics_score']);
    }
    
    /**
     * 测试AI性能追踪
     */
    public function test_ai_performance_tracking(): void
    {
        // 执行多个AI任务来生成性能数据
        for ($i = 0; $i < 5; $i++) {
            $this->aiService->processIntelligentChat([
                'session_id' => 'perf_test_' . $i,
                'user_input' => 'Test message ' . $i,
                'context' => [],
                'response_style' => 'concise'
            ]);
        }
        
        $performanceData = [
            'time_range' => [
                'start' => now()->subHour()->toISOString(),
                'end' => now()->toISOString()
            ],
            'metrics' => [
                'response_time',
                'token_usage',
                'success_rate',
                'cost_analysis'
            ],
            'model_types' => ['nlp', 'cv', 'speech']
        ];
        
        $result = $this->aiService->getPerformanceMetrics($performanceData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('performance_metrics', $result);
        $this->assertArrayHasKey('response_time', $result['performance_metrics']);
        $this->assertArrayHasKey('token_usage', $result['performance_metrics']);
        $this->assertArrayHasKey('success_rate', $result['performance_metrics']);
    }
    
    /**
     * 测试AI工作流编排
     */
    public function test_ai_workflow_orchestration(): void
    {
        $workflowData = [
            'name' => 'Content Creation Workflow',
            'description' => 'Automated workflow for content creation and optimization',
            'steps' => [
                [
                    'step_id' => 'research',
                    'type' => 'web_research',
                    'config' => [
                        'topic' => 'AI trends 2024',
                        'sources' => ['academic', 'industry_reports', 'news']
                    ]
                ],
                [
                    'step_id' => 'outline',
                    'type' => 'content_outlining',
                    'dependencies' => ['research'],
                    'config' => [
                        'content_type' => 'blog_post',
                        'target_length' => 2000,
                        'audience' => 'business_professionals'
                    ]
                ],
                [
                    'step_id' => 'writing',
                    'type' => 'content_generation',
                    'dependencies' => ['outline'],
                    'config' => [
                        'style' => 'professional',
                        'tone' => 'informative'
                    ]
                ],
                [
                    'step_id' => 'optimization',
                    'type' => 'seo_optimization',
                    'dependencies' => ['writing'],
                    'config' => [
                        'target_keywords' => ['AI trends', 'artificial intelligence', '2024'],
                        'optimization_level' => 'aggressive'
                    ]
                ]
            ]
        ];
        
        $result = $this->aiService->createWorkflow($workflowData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('workflow', $result);
        $this->assertEquals($workflowData['name'], $result['workflow']['name']);
        
        // 测试工作流执行
        $executeResult = $this->aiService->executeWorkflow(
            $result['workflow']['workflow_id'],
            ['topic' => 'AI trends in business automation']
        );
        
        $this->assertTrue($executeResult['success']);
        $this->assertArrayHasKey('execution_id', $executeResult);
    }
    
    /**
     * 测试AI模型微调
     */
    public function test_ai_model_fine_tuning(): void
    {
        $fineTuningData = [
            'base_model_id' => 'model_' . uniqid(),
            'training_data' => [
                'dataset_name' => 'business_communications',
                'data_format' => 'jsonl',
                'training_samples' => 1000,
                'validation_samples' => 200
            ],
            'fine_tuning_config' => [
                'learning_rate' => 0.0001,
                'batch_size' => 16,
                'epochs' => 3,
                'optimization_target' => 'business_accuracy'
            ],
            'evaluation_metrics' => [
                'accuracy',
                'precision',
                'recall',
                'f1_score'
            ]
        ];
        
        $result = $this->aiService->initializeFineTuning($fineTuningData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('fine_tuning_job', $result);
        $this->assertArrayHasKey('estimated_completion_time', $result);
        
        // 测试微调状态查询
        $statusResult = $this->aiService->getFineTuningStatus(
            $result['fine_tuning_job']['job_id']
        );
        
        $this->assertTrue($statusResult['success']);
        $this->assertArrayHasKey('status', $statusResult);
    }
    
    /**
     * 测试AI安全和隐私
     */
    public function test_ai_security_and_privacy(): void
    {
        $securityData = [
            'input_data' => 'User personal information: John Doe, email: john@example.com, phone: 123-456-7890',
            'processing_type' => 'data_analysis',
            'privacy_requirements' => [
                'pii_detection',
                'data_anonymization',
                'compliance_check'
            ],
            'security_level' => 'high'
        ];
        
        $result = $this->aiService->processWithPrivacyProtection($securityData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('anonymized_data', $result);
        $this->assertArrayHasKey('pii_detected', $result);
        $this->assertArrayHasKey('compliance_status', $result);
        
        // 验证个人信息已被匿名化
        $this->assertStringNotContainsString('john@example.com', $result['anonymized_data']);
        $this->assertStringNotContainsString('123-456-7890', $result['anonymized_data']);
    }
    
    /**
     * 测试负载和性能
     */
    public function test_performance_under_load(): void
    {
        $startTime = microtime(true);
        $tasks = [];
        
        // 并发执行多个AI任务
        for ($i = 0; $i < 10; $i++) {
            $tasks[] = $this->aiService->processIntelligentChat([
                'session_id' => 'load_test_' . $i,
                'user_input' => 'Performance test message ' . $i,
                'context' => [],
                'response_style' => 'concise'
            ]);
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000; // 毫秒
        
        // 验证所有任务都成功完成
        foreach ($tasks as $task) {
            $this->assertTrue($task['success']);
        }
        
        // 验证总执行时间在合理范围内（30秒内）
        $this->assertLessThan(30000, $totalTime, 'AI service should handle 10 concurrent requests within 30 seconds');
        
        // 验证平均响应时间
        $averageTime = $totalTime / count($tasks);
        $this->assertLessThan(5000, $averageTime, 'Average response time should be less than 5 seconds');
    }
    
    /**
     * 测试错误处理和恢复
     */
    public function test_error_handling_and_recovery(): void
    {
        // 测试无效输入处理
        $invalidData = [
            'task_type' => 'invalid_task_type',
            'inputs' => null
        ];
        
        $result = $this->aiService->performMultimodalAnalysis($invalidData);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertArrayHasKey('error_code', $result);
        
        // 测试API限制处理
        $rateLimitData = [
            'user_input' => 'Test message for rate limiting',
            'session_id' => 'rate_limit_test'
        ];
        
        // 模拟触发速率限制
        for ($i = 0; $i < 100; $i++) {
            $result = $this->aiService->processIntelligentChat($rateLimitData);
            
            if (!$result['success'] && isset($result['error_code']) && $result['error_code'] === 'RATE_LIMIT_EXCEEDED') {
                $this->assertArrayHasKey('retry_after', $result);
                break;
            }
        }
    }
}
