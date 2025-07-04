<?php
declare(strict_types=1);

namespace Tests\Feature\AI\Integration;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * AI模块集成测试
 * 测试不同AI模块之间的协同工作
 */
class AIModulesIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    
    /**
     * 测试语音识别与NLP集成
     * 将语音转换为文本，然后进行NLP分析
     */
    public function test_speech_recognition_with_nlp(): void
    {
        $audioFile = 'tests/fixtures/audio/sample_speech.wav';
        
        // 第一步：语音识别
        $response = $this->post('/api/ai/speech/recognize', [
            'audio_file' => $audioFile,
            'language' => 'zh-CN',
        ]);
        
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('text', $data);
        $recognizedText = $data['text'];
        
        // 第二步：NLP分析
        $response = $this->post('/api/ai/nlp/analyze', [
            'text' => $recognizedText,
            'analysis_types' => ['sentiment', 'entities', 'keywords'],
        ]);
        
        $response->assertStatus(200);
        $data = $response->json();
        
        $this->assertArrayHasKey('sentiment', $data);
        $this->assertArrayHasKey('entities', $data);
        $this->assertArrayHasKey('keywords', $data);
    }

    /**
     * 测试图像识别与知识图谱集成
     * 识别图像中的对象，然后查询知识图谱获取相关信息
     */
    public function test_image_recognition_with_knowledge_graph(): void
    {
        $imageFile = 'tests/fixtures/images/office_desk.jpg';
        
        // 第一步：图像识别
        $response = $this->post('/api/ai/cv/recognize', [
            'image_file' => $imageFile,
            'detection_type' => 'objects',
        ]);
        
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('objects', $data);
        $this->assertNotEmpty($data['objects']);
        
        // 获取第一个识别对象
        $firstObject = $data['objects'][0];
        $this->assertArrayHasKey('label', $firstObject);
        $objectLabel = $firstObject['label'];
        
        // 第二步：知识图谱查询
        $response = $this->post('/api/ai/knowledge-graph/query', [
            'entity' => $objectLabel,
            'query_depth' => 2,
            'relationship_types' => ['is_a', 'part_of', 'used_for'],
        ]);
        
        $response->assertStatus(200);
        $data = $response->json();
        
        $this->assertArrayHasKey('entity_info', $data);
        $this->assertArrayHasKey('relationships', $data);
    }

    /**
     * 测试语音合成与NLP集成
     * 使用NLP处理文本，然后将处理后的文本转换为语音
     */
    public function test_nlp_with_speech_synthesis(): void
    {
        $originalText = "这是一段需要处理和合成的文本。";
        
        // 第一步：NLP文本处理
        $response = $this->post('/api/ai/nlp/process', [
            'text' => $originalText,
            'operations' => ['normalize', 'segment', 'enhance'],
        ]);
        
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('processed_text', $data);
        $processedText = $data['processed_text'];
        
        // 第二步：语音合成
        $response = $this->post('/api/ai/speech/synthesize', [
            'text' => $processedText,
            'voice' => 'female_1',
            'speed' => 1.0,
            'format' => 'mp3',
        ]);
        
        $response->assertStatus(200);
        $this->assertTrue($response->headers->contains('Content-Type', 'audio/mpeg'));
    }
    
    /**
     * 测试多模态融合：语音、视觉和NLP协同工作
     * 模拟智能助手场景，处理语音指令、识别图像并结合知识图谱响应
     */
    public function test_multimodal_assistant(): void
    {
        $audioCommand = 'tests/fixtures/audio/command_describe_image.wav';
        $imageFile = 'tests/fixtures/images/scene.jpg';
        
        // 第一步：语音识别
        $response = $this->post('/api/ai/speech/recognize', [
            'audio_file' => $audioCommand,
            'language' => 'zh-CN',
        ]);
        
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('text', $data);
        $command = $data['text'];
        
        // 第二步：NLP意图识别
        $response = $this->post('/api/ai/nlp/intent', [
            'text' => $command,
        ]);
        
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('intent', $data);
        $this->assertEquals('describe_image', $data['intent']);
        
        // 第三步：图像识别
        $response = $this->post('/api/ai/cv/analyze', [
            'image_file' => $imageFile,
            'analysis_types' => ['objects', 'scene', 'caption'],
        ]);
        
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('caption', $data);
        
        // 第四步：知识图谱增强
        $objects = $data['objects'] ?? [];
        $enhancedDescriptions = [];
        
        foreach ($objects as $object) {
            $response = $this->post('/api/ai/knowledge-graph/query', [
                'entity' => $object['label'],
                'query_depth' => 1,
            ]);
            
            if ($response->status() === 200) {
                $kgData = $response->json();
                if (isset($kgData['entity_info']['description'])) {
                    $enhancedDescriptions[] = $kgData['entity_info']['description'];
                }
            }
        }
        
        $this->assertNotEmpty($enhancedDescriptions);
        
        // 第五步：生成响应并合成语音
        $responseText = "图像中包含 " . count($objects) . " 个主要对象。" . implode(' ', $enhancedDescriptions);
        
        $response = $this->post('/api/ai/speech/synthesize', [
            'text' => $responseText,
            'voice' => 'female_1',
        ]);
        
        $response->assertStatus(200);
    }

    /**
     * 测试计算机视觉与NLP集成
     * 用于图像内容理解和描述生成
     */
    public function test_cv_with_nlp_for_image_understanding(): void
    {
        $imageFile = 'tests/fixtures/images/complex_scene.jpg';
        
        // 第一步：图像分析
        $response = $this->post('/api/ai/cv/analyze', [
            'image_file' => $imageFile,
            'analysis_types' => ['objects', 'scene', 'attributes', 'relationships'],
        ]);
        
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('objects', $data);
        $this->assertArrayHasKey('scene', $data);
        
        // 提取图像分析结果
        $objects = $data['objects'];
        $scene = $data['scene'];
        $relationships = $data['relationships'] ?? [];
        
        // 构建结构化数据
        $structuredData = [
            'scene_type' => $scene['label'] ?? 'unknown',
            'objects' => array_map(function($obj) {
                return [
                    'label' => $obj['label'],
                    'confidence' => $obj['confidence'],
                    'attributes' => $obj['attributes'] ?? []
                ];
            }, $objects),
            'relationships' => $relationships
        ];
        
        // 第二步：使用NLP生成自然语言描述
        $response = $this->post('/api/ai/nlp/generate', [
            'template' => 'image_description',
            'data' => $structuredData,
            'language' => 'zh-CN',
            'style' => 'detailed',
        ]);
        
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('text', $data);
        $description = $data['text'];
        $this->assertNotEmpty($description);
        
        // 第三步：情感和上下文分析
        $response = $this->post('/api/ai/nlp/analyze', [
            'text' => $description,
            'analysis_types' => ['sentiment', 'context', 'themes'],
        ]);
        
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('sentiment', $data);
        $this->assertArrayHasKey('themes', $data);
        
        // 验证描述的情感与场景类型匹配
        if (isset($scene['mood']) && isset($data['sentiment']['polarity'])) {
            $this->assertStringContainsString(
                strtolower($scene['mood']),
                strtolower($data['sentiment']['polarity'])
            );
        }
    }
}
