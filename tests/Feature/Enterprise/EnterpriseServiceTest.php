<?php
declare(strict_types=1);

namespace Tests\Feature\Enterprise;

use Tests\TestCase;
use AlingAi\Enterprise\Services\EnterpriseServiceManager;
use AlingAi\Enterprise\Services\WorkspaceManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * 企业服务功能测试
 */
class EnterpriseServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    
    private EnterpriseServiceManager $enterpriseService;
    private WorkspaceManager $workspaceManager;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->enterpriseService = app(EnterpriseServiceManager::class);
        $this->workspaceManager = app(WorkspaceManager::class);
    }
    
    /**
     * 测试创建企业工作空间
     */
    public function test_create_enterprise_workspace(): void
    {
        $workspaceData = [
            'name' => 'Test Enterprise Workspace',
            'description' => 'A test workspace for enterprise features',
            'type' => 'enterprise',
            'organization_id' => 'org_' . uniqid(),
            'owner_id' => 'user_' . uniqid(),
            'settings' => [
                'max_users' => 100,
                'storage_limit' => '100GB',
                'features' => ['ai_assistance', 'collaboration', 'analytics']
            ]
        ];
        
        $result = $this->workspaceManager->createWorkspace($workspaceData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('workspace', $result);
        $this->assertEquals($workspaceData['name'], $result['workspace']['name']);
        $this->assertEquals($workspaceData['type'], $result['workspace']['type']);
        
        // 验证数据库记录
        $this->assertDatabaseHas('enterprise_workspaces', [
            'workspace_id' => $result['workspace']['workspace_id'],
            'name' => $workspaceData['name'],
            'type' => $workspaceData['type']
        ]);
    }
    
    /**
     * 测试工作空间配置优化
     */
    public function test_workspace_configuration_optimization(): void
    {
        // 创建测试工作空间
        $workspace = $this->createTestWorkspace();
        
        $optimizationParams = [
            'workspace_id' => $workspace['workspace_id'],
            'optimization_goals' => ['performance', 'cost_efficiency', 'user_experience'],
            'constraints' => [
                'budget_limit' => 10000,
                'performance_target' => 95
            ]
        ];
        
        $result = $this->workspaceManager->optimizeConfiguration($optimizationParams);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('optimized_config', $result);
        $this->assertArrayHasKey('performance_improvement', $result);
        $this->assertGreaterThan(0, $result['performance_improvement']);
    }
    
    /**
     * 测试项目管理功能
     */
    public function test_project_management(): void
    {
        $workspace = $this->createTestWorkspace();
        
        $projectData = [
            'workspace_id' => $workspace['workspace_id'],
            'name' => 'Test Project',
            'description' => 'A test project for enterprise features',
            'type' => 'software_development',
            'priority' => 'high',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'budget' => 50000.00,
            'team_members' => [
                'user_1' => ['role' => 'project_manager'],
                'user_2' => ['role' => 'developer'],
                'user_3' => ['role' => 'designer']
            ]
        ];
        
        $result = $this->enterpriseService->createProject($projectData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('project', $result);
        $this->assertEquals($projectData['name'], $result['project']['name']);
        
        // 测试项目状态更新
        $updateResult = $this->enterpriseService->updateProjectStatus(
            $result['project']['project_id'],
            'active'
        );
        
        $this->assertTrue($updateResult['success']);
        $this->assertEquals('active', $updateResult['project']['status']);
    }
    
    /**
     * 测试团队管理功能
     */
    public function test_team_management(): void
    {
        $workspace = $this->createTestWorkspace();
        
        $teamData = [
            'workspace_id' => $workspace['workspace_id'],
            'name' => 'Development Team',
            'description' => 'Core development team',
            'team_type' => 'development',
            'lead_id' => 'user_lead_' . uniqid(),
            'members' => [
                'user_1' => ['role' => 'senior_developer', 'skills' => ['php', 'javascript']],
                'user_2' => ['role' => 'junior_developer', 'skills' => ['python', 'react']],
                'user_3' => ['role' => 'qa_engineer', 'skills' => ['testing', 'automation']]
            ]
        ];
        
        $result = $this->enterpriseService->createTeam($teamData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('team', $result);
        $this->assertEquals($teamData['name'], $result['team']['name']);
        
        // 测试团队成员添加
        $addMemberResult = $this->enterpriseService->addTeamMember(
            $result['team']['team_id'],
            'user_4',
            ['role' => 'devops_engineer', 'skills' => ['docker', 'kubernetes']]
        );
        
        $this->assertTrue($addMemberResult['success']);
    }
    
    /**
     * 测试任务自动化
     */
    public function test_task_automation(): void
    {
        $workspace = $this->createTestWorkspace();
        
        $automationData = [
            'workspace_id' => $workspace['workspace_id'],
            'name' => 'Daily Report Automation',
            'description' => 'Generate daily progress reports automatically',
            'trigger_type' => 'schedule',
            'trigger_config' => [
                'schedule' => 'daily',
                'time' => '09:00',
                'timezone' => 'UTC'
            ],
            'actions' => [
                [
                    'type' => 'generate_report',
                    'config' => [
                        'report_type' => 'daily_progress',
                        'recipients' => ['team_leads', 'managers']
                    ]
                ],
                [
                    'type' => 'send_notification',
                    'config' => [
                        'channels' => ['email', 'slack'],
                        'message' => 'Daily report has been generated'
                    ]
                ]
            ]
        ];
        
        $result = $this->enterpriseService->createAutomation($automationData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('automation', $result);
        $this->assertEquals($automationData['name'], $result['automation']['name']);
        
        // 测试自动化执行
        $executeResult = $this->enterpriseService->executeAutomation(
            $result['automation']['automation_id']
        );
        
        $this->assertTrue($executeResult['success']);
    }
    
    /**
     * 测试文档管理
     */
    public function test_document_management(): void
    {
        $workspace = $this->createTestWorkspace();
        
        $documentData = [
            'workspace_id' => $workspace['workspace_id'],
            'name' => 'Project Requirements',
            'description' => 'Detailed project requirements document',
            'content' => 'This is a test document content...',
            'document_type' => 'requirements',
            'tags' => ['project', 'requirements', 'important'],
            'permissions' => [
                'read' => ['team_all'],
                'write' => ['team_leads'],
                'admin' => ['project_managers']
            ]
        ];
        
        $result = $this->enterpriseService->createDocument($documentData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('document', $result);
        $this->assertEquals($documentData['name'], $result['document']['name']);
        
        // 测试文档版本控制
        $updateResult = $this->enterpriseService->updateDocument(
            $result['document']['document_id'],
            ['content' => 'Updated document content...']
        );
        
        $this->assertTrue($updateResult['success']);
        $this->assertEquals('1.1', $updateResult['document']['version']);
    }
    
    /**
     * 测试企业分析
     */
    public function test_enterprise_analytics(): void
    {
        $workspace = $this->createTestWorkspace();
        
        // 创建一些测试数据
        $this->createTestProjectsAndTeams($workspace['workspace_id']);
        
        $analyticsParams = [
            'workspace_id' => $workspace['workspace_id'],
            'metrics' => [
                'productivity',
                'resource_utilization',
                'team_performance',
                'project_progress'
            ],
            'time_range' => [
                'start' => now()->subMonth()->format('Y-m-d'),
                'end' => now()->format('Y-m-d')
            ]
        ];
        
        $result = $this->enterpriseService->generateAnalytics($analyticsParams);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('analytics', $result);
        $this->assertArrayHasKey('productivity', $result['analytics']);
        $this->assertArrayHasKey('resource_utilization', $result['analytics']);
        $this->assertArrayHasKey('recommendations', $result['analytics']);
    }
    
    /**
     * 测试实时协作
     */
    public function test_realtime_collaboration(): void
    {
        $workspace = $this->createTestWorkspace();
        
        $sessionData = [
            'workspace_id' => $workspace['workspace_id'],
            'session_type' => 'video_conference',
            'participants' => ['user_1', 'user_2', 'user_3'],
            'settings' => [
                'max_participants' => 50,
                'recording_enabled' => true,
                'screen_sharing' => true,
                'chat_enabled' => true
            ]
        ];
        
        $result = $this->enterpriseService->startCollaborationSession($sessionData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('session', $result);
        $this->assertEquals($sessionData['session_type'], $result['session']['session_type']);
        
        // 测试会话管理
        $joinResult = $this->enterpriseService->joinCollaborationSession(
            $result['session']['session_id'],
            'user_4'
        );
        
        $this->assertTrue($joinResult['success']);
    }
    
    /**
     * 创建测试工作空间
     */
    private function createTestWorkspace(): array
    {
        $workspaceData = [
            'name' => 'Test Workspace ' . uniqid(),
            'description' => 'Test workspace for feature testing',
            'type' => 'enterprise',
            'organization_id' => 'org_test_' . uniqid(),
            'owner_id' => 'user_test_' . uniqid()
        ];
        
        $result = $this->workspaceManager->createWorkspace($workspaceData);
        
        return $result['workspace'];
    }
    
    /**
     * 创建测试项目和团队
     */
    private function createTestProjectsAndTeams(string $workspaceId): void
    {
        // 创建测试项目
        for ($i = 1; $i <= 3; $i++) {
            $this->enterpriseService->createProject([
                'workspace_id' => $workspaceId,
                'name' => "Test Project {$i}",
                'description' => "Test project {$i} description",
                'type' => 'software_development',
                'priority' => ['low', 'medium', 'high'][array_rand(['low', 'medium', 'high'])],
                'start_date' => now()->subDays(rand(10, 30))->format('Y-m-d'),
                'end_date' => now()->addDays(rand(30, 90))->format('Y-m-d'),
                'budget' => rand(10000, 100000)
            ]);
        }
        
        // 创建测试团队
        for ($i = 1; $i <= 2; $i++) {
            $this->enterpriseService->createTeam([
                'workspace_id' => $workspaceId,
                'name' => "Test Team {$i}",
                'description' => "Test team {$i} description",
                'team_type' => ['development', 'design'][array_rand(['development', 'design'])],
                'lead_id' => "user_lead_{$i}_" . uniqid()
            ]);
        }
    }
    
    /**
     * 测试性能指标
     */
    public function test_performance_metrics(): void
    {
        $startTime = microtime(true);
        
        // 执行一系列企业服务操作
        $workspace = $this->createTestWorkspace();
        $this->createTestProjectsAndTeams($workspace['workspace_id']);
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // 毫秒
        
        // 断言执行时间在合理范围内（5秒内）
        $this->assertLessThan(5000, $executionTime, 'Enterprise service operations should complete within 5 seconds');
        
        // 测试内存使用
        $memoryUsage = memory_get_peak_usage(true);
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsage, 'Memory usage should be less than 50MB');
    }
    
    /**
     * 测试错误处理
     */
    public function test_error_handling(): void
    {
        // 测试无效工作空间创建
        $invalidWorkspaceData = [
            'name' => '', // 空名称应该失败
            'type' => 'invalid_type'
        ];
        
        $result = $this->workspaceManager->createWorkspace($invalidWorkspaceData);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        
        // 测试无效项目创建
        $invalidProjectData = [
            'workspace_id' => 'non_existent_workspace',
            'name' => 'Test Project'
        ];
        
        $result = $this->enterpriseService->createProject($invalidProjectData);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }
}
