<?php
declare(strict_types=1);

namespace Tests\Feature\AI;

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
        $speechResult = $response->json();
        $this->assertArrayHasKey('text', $speechResult);
        $recognizedText = $speechResult['text'];
        
        // 第二步：NLP分析
        $nlpResponse = $this->post('/api/ai/nlp/analyze', [
            'text' => $recognizedText,
            'analysis_types' => ['sentiment', 'entities', 'keywords'],
        ]);
        
        $nlpResponse->assertStatus(200);
        $nlpResult = $nlpResponse->json();
        
        $this->assertArrayHasKey('sentiment', $nlpResult);
        $this->assertArrayHasKey('entities', $nlpResult);
        $this->assertArrayHasKey('keywords', $nlpResult);
    }
    
    /**
     * 测试图像识别与知识图谱集成
     * 识别图像中的对象，然后查询知识图谱获取相关信息
     */
    public function test_image_recognition_with_knowledge_graph(): void
    {
        $imageFile = 'tests/fixtures/images/office_scene.jpg';
        
        // 第一步：图像识别
        $response = $this->post('/api/ai/cv/recognize', [
            'image_file' => $imageFile,
            'detection_type' => 'objects',
        ]);
        
        $response->assertStatus(200);
        $imageResult = $response->json();
        $this->assertArrayHasKey('objects', $imageResult);
        $this->assertNotEmpty($imageResult['objects']);
        
        // 获取第一个识别对象
        $firstObject = $imageResult['objects'][0]['label'];
        
        // 第二步：知识图谱查询
        $kgResponse = $this->post('/api/ai/knowledge-graph/query', [
            'entity' => $firstObject,
            'query_depth' => 2,
            'relationship_types' => ['is_a', 'part_of', 'used_for'],
        ]);
        
        $kgResponse->assertStatus(200);
        $kgResult = $kgResponse->json();
        
        $this->assertArrayHasKey('entity_info', $kgResult);
        $this->assertArrayHasKey('relationships', $kgResult);
    }
    
    /**
     * 测试OCR、NLP与知识图谱的集成
     * 从图像提取文本，进行NLP分析，然后丰富知识图谱
     */
    public function test_ocr_nlp_knowledge_graph_integration(): void
    {
        $documentImage = 'tests/fixtures/images/business_document.jpg';
        
        // 第一步：OCR文字识别
        $response = $this->post('/api/ai/cv/ocr', [
            'image_file' => $documentImage,
        ]);
        
        $response->assertStatus(200);
        $ocrResult = $response->json();
        $this->assertArrayHasKey('text', $ocrResult);
        $extractedText = $ocrResult['text'];
        
        // 第二步：NLP实体提取
        $nlpResponse = $this->post('/api/ai/nlp/extract-entities', [
            'text' => $extractedText,
            'entity_types' => ['person', 'organization', 'location', 'date'],
        ]);
        
        $nlpResponse->assertStatus(200);
        $nlpResult = $nlpResponse->json();
        $this->assertArrayHasKey('entities', $nlpResult);
        $this->assertNotEmpty($nlpResult['entities']);
        
        // 第三步：知识图谱更新
        $entities = $nlpResult['entities'];
        $kgResponse = $this->post('/api/ai/knowledge-graph/update', [
            'entities' => $entities,
            'source_document' => 'business_document.jpg',
            'confidence_threshold' => 0.7,
        ]);
        
        $kgResponse->assertStatus(200);
        $kgResult = $kgResponse->json();
        
        $this->assertArrayHasKey('added_entities', $kgResult);
        $this->assertArrayHasKey('added_relationships', $kgResult);
        $this->assertArrayHasKey('updated_entities', $kgResult);
    }
    
    /**
     * 测试语音合成与文本分析的集成
     * 分析文本，优化后进行语音合成
     */
    public function test_text_analysis_with_speech_synthesis(): void
    {
        $text = "这是一段需要进行语音合成的示例文本，包含多个句子和标点符号。这将测试文本处理和语音合成的集成。";
        
        // 第一步：文本分析和优化
        $response = $this->post('/api/ai/nlp/optimize-text', [
            'text' => $text,
            'optimization_goals' => ['readability', 'speech_friendly'],
        ]);
        
        $response->assertStatus(200);
        $textResult = $response->json();
        $this->assertArrayHasKey('optimized_text', $textResult);
        $optimizedText = $textResult['optimized_text'];
        
        // 第二步：语音合成
        $synthesisResponse = $this->post('/api/ai/speech/synthesize', [
            'text' => $optimizedText,
            'voice_id' => 'female_1',
            'output_format' => 'mp3',
            'speech_rate' => 1.0,
        ]);
        
        $synthesisResponse->assertStatus(200);
        $synthesisResult = $synthesisResponse->json();
        
        $this->assertArrayHasKey('audio_url', $synthesisResult);
        $this->assertArrayHasKey('duration', $synthesisResult);
        $this->assertArrayHasKey('char_count', $synthesisResult);
    }
    
    /**
     * 测试多模态分析集成
     * 同时处理文本、图像和音频数据
     */
    public function test_multimodal_analysis_integration(): void
    {
        $data = [
            'text' => '这是一段产品描述文本',
            'image_file' => 'tests/fixtures/images/product.jpg',
            'audio_file' => 'tests/fixtures/audio/customer_feedback.mp3',
        ];
        
        $response = $this->post('/api/ai/multimodal/analyze', $data);
        
        $response->assertStatus(200);
        $result = $response->json();
        
        $this->assertArrayHasKey('text_analysis', $result);
        $this->assertArrayHasKey('image_analysis', $result);
        $this->assertArrayHasKey('audio_analysis', $result);
        $this->assertArrayHasKey('integrated_insights', $result);
        
        // 验证文本分析结果
        $this->assertArrayHasKey('sentiment', $result['text_analysis']);
        $this->assertArrayHasKey('keywords', $result['text_analysis']);
        
        // 验证图像分析结果
        $this->assertArrayHasKey('objects', $result['image_analysis']);
        $this->assertArrayHasKey('colors', $result['image_analysis']);
        
        // 验证音频分析结果
        $this->assertArrayHasKey('transcription', $result['audio_analysis']);
        $this->assertArrayHasKey('sentiment', $result['audio_analysis']);
        
        // 验证集成洞察
        $this->assertArrayHasKey('product_quality_score', $result['integrated_insights']);
        $this->assertArrayHasKey('customer_satisfaction', $result['integrated_insights']);
        $this->assertArrayHasKey('recommendations', $result['integrated_insights']);
    }
}