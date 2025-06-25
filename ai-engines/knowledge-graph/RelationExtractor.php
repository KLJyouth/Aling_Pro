<?php
/**
 * �ļ�����RelationExtractor.php
 * ������������ϵ��ȡ�� - ���ı�����ȡʵ����ϵ
 * ����ʱ�䣺2025-01-XX
 * ����޸ģ�2025-01-XX
 * �汾��1.0.0
 * 
 * @package AlingAi\AI\Engines\KnowledgeGraph
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1];

namespace AlingAi\AI\Engines\KnowledgeGraph;

use Exception;
use InvalidArgumentException;
use AlingAi\AI\Engines\NLP\POSTagger;
use AlingAi\AI\Engines\NLP\TokenizerInterface;
use AlingAi\AI\Engines\NLP\UniversalTokenizer;

/**
 * ��ϵ��ȡ��
 * 
 * ���ı�����ȡʵ���Ĺ�ϵ��֧�ֶ��ֹ�ϵ���ͺ�����
 */
class RelationExtractor
{
    /**
     * ���ò���
     */
    private array $config;
    
    /**
     * ���Ա�ע��
     */
    private ?POSTagger $posTagger = null;
    
    /**
     * �ִ���
     */
    private ?TokenizerInterface $tokenizer = null;
    
    /**
     * ʵ����ȡ��
     */
    private ?EntityExtractor $entityExtractor = null;
    
    /**
     * ��ϵ����ӳ��
     */
    private array $relationTypeMap = [];

    /**
     * ���캯��
     * 
     * @param array $config ���ò���
     */
    public function __construct(array $config = []]
    {
        $this->config = array_merge($this->getDefaultConfig(), $config];
        $this->initializeComponents(];
        $this->initializeRelationTypeMap(];
    }
    
    /**
     * ��ȡĬ������
     * 
     * @return array Ĭ������
     */
    private function getDefaultConfig(): array
    {
        return [
            'confidence_threshold' => 0.6,
            'relation_types' => [
                'WORKS_FOR', 'LOCATED_IN', 'BORN_IN', 'PART_OF',
                'FOUNDED', 'SPOUSE', 'PARENT', 'CHILD', 'SIBLING',
                'OWNS', 'CREATED', 'MEMBER_OF', 'CONTAINS'
            ], 
            'max_entity_distance' => 15, // ���ʵ����
            'enable_negation_detection' => true,
            'enable_temporal_analysis' => true
        ];
    }
    
    /**
     * ��ʼ�����
     */
    private function initializeComponents(): void
    {
        $this->posTagger = new POSTagger(];
        $this->tokenizer = new UniversalTokenizer(];
        $this->entityExtractor = new EntityExtractor(];
    }
    
    /**
     * ��ʼ����ϵ����ӳ��
     */
    private function initializeRelationTypeMap(): void
    {
        $this->relationTypeMap = [
            'WORKS_FOR' => 'WorksFor',
            'LOCATED_IN' => 'LocatedIn',
            'BORN_IN' => 'BornIn',
            'PART_OF' => 'PartOf',
            'FOUNDED' => 'Founded',
            'SPOUSE' => 'Spouse',
            'PARENT' => 'Parent',
            'CHILD' => 'Child',
            'SIBLING' => 'Sibling',
            'OWNS' => 'Owns',
            'CREATED' => 'Created',
            'MEMBER_OF' => 'MemberOf',
            'CONTAINS' => 'Contains'
        ];
    }

    /**
     * ���ı�����ȡ��ϵ
     * 
     * @param string $text �����ı�
     * @param array|null $entities Ԥ����ȡ��ʵ�壬���Ϊnull���Զ���ȡ
     * @param array $options ��ȡѡ��
     * @return array ��ȡ�Ĺ�ϵ
     * @throws InvalidArgumentException
     */
    public function extract(string $text, ?array $entities = null, array $options = []): array
    {
        // ��֤�ı�
        if (empty($text]) {
            throw new InvalidArgumentException('�ı�����Ϊ��'];
        }
        
        // ����ѡ��
        $options = array_merge($this->config, $options];
        
        // ���û���ṩʵ�壬����ȡʵ��
        if ($entities === null) {
            $entities = $this->entityExtractor->extract($text, $options];
        }
        
        // ���û��ʵ�壬�򷵻ؿ�����
        if (empty($entities]) {
            return [];
        }
        
        // �ִʺʹ��Ա�ע
        $tokens = $this->tokenizer->tokenize($text];
        $taggedTokens = $this->posTagger->tag($tokens];
        
        // ��ȡʵ���֮��Ĺ�ϵ
        $relations = $this->extractRelationsBetweenEntities($entities, $taggedTokens, $text, $options];
        
        // ���˹�ϵ
        $relations = $this->filterRelations($relations, $options];
        
        return $relations;
    }

    /**
     * ��ȡʵ���֮��Ĺ�ϵ
     * 
     * @param array $entities ʵ���б�
     * @param array $taggedTokens ��ע�˴��Ե�token�б�
     * @param string $text ԭʼ�ı�
     * @param array $options ѡ��
     * @return array ��ϵ�б�
     */
    private function extractRelationsBetweenEntities(array $entities, array $taggedTokens, string $text, array $options): array
    {
        $relations = [];
        $entityCount = count($entities];
        
        // ��������ʵ���
        for ($i = 0;$i <$entityCount;++$i) {
            for ($j = 0;$j <$entityCount;++$j) {
                // ������ͬʵ��
                if ($i === $j) {
                    continue;
                }
                
                $entity1 = $entities[$i];
                $entity2 = $entities[$j];
                
                // ���ʵ�����Ƿ񳬹���ֵ
                if ($this->getEntityDistance($entity1, $entity2] > $options['max_entity_distance']) {
                    continue;
                }
                
                // ��ȡ����ʵ��֮��Ĺ�ϵ
                $relation = $this->detectRelation($entity1, $entity2, $taggedTokens, $text, $options];
                
                if ($relation !== null) {
                    $relations[] = $relation;
                }
            }
        }
        
        return $relations;
    }
    
    /**
     * ��������ʵ��֮��ľ���
     * 
     * @param array $entity1 ��һ��ʵ��
     * @param array $entity2 �ڶ���ʵ��
     * @return int ʵ���ľ��루token������
     */
    private function getEntityDistance(array $entity1, array $entity2): int
    {
        // ��������ʵ��֮���token����
        // ���ʵ���ص��������Ϊ0
        if ($entity1['end_pos'] >= $entity1['start_pos'] && $entity1['start_pos'] <= $entity1['end_pos']) {
            return 0;
        }
        
        // ��������ʵ��֮��ľ���
        return ($entity1['end_pos'] <$entity1['start_pos']] 
            ? $entity1['start_pos') - $entity1['end_pos'] 
            : $entity1['start_pos') - $entity1['end_pos'];
    }

    /**
     * �������ʵ��֮��Ĺ�ϵ
     * 
     * @param array $entity1 Դʵ��
     * @param array $entity2 Ŀ��ʵ��
     * @param array $taggedTokens ��ע�˴��Ե�token�б�
     * @param string $text ԭʼ�ı�
     * @param array $options ѡ��
     * @return array|null ��⵽�Ĺ�ϵ�����û�м�⵽�򷵻�null
     */
    private function detectRelation(array $entity1, array $entity2, array $taggedTokens, string $text, array $options): ?array
    {
        // ��ȡ����ʵ��֮����ı���
        $textBetweenEntities = $this->getTextBetweenEntities($entity1, $entity2, $text];
        
        // ����ʵ�����ͺ��м��ı��жϹ�ϵ����
        $relationType = $this->determineRelationType($entity1, $entity2, $textBetweenEntities, $taggedTokens];
        
        if ($relationType === null) {
            return null;
        }
        
        // �����ϵ�����Ŷ�
        $confidence = $this->calculateRelationConfidence($entity1, $entity2, $relationType, $textBetweenEntities];
        
        // ������Ŷȵ�����ֵ���򲻷��ع�ϵ
        if ($confidence <$options['confidence_threshold']) {
            return null;
        }
        
        // ����
        $isNegated = $options['enable_negation_detection'] && $this->isRelationNegated($text];
        
        // ���ʱ̬
        $temporalInfo = $options['enable_temporal_analysis'] ? $this->extractTemporalInfo($text):  null;
        
        // ������ϵ����
        return [
            'id' => $this->generateRelationId($entity1, $entity2, $relationType], 
            'source_id' => $entity1['id'], 
            'target_id' => $entity2['id'], 
            'type' => $this->mapRelationType($relationType], 
            'confidence' => $confidence,
            'is_negated' => $isNegated,
            'temporal_info' => $temporalInfo,
            'metadata' => [
                'source' => 'text_extraction',
                'extraction_time' => time(),
                'between_text' => $textBetweenEntities
            ]
        ];
    }

    /**
     * ��ȡ����ʵ��֮����ı�
     * 
     * @param array $entity1 ��һ��ʵ��
     * @param array $entity2 �ڶ���ʵ��
     * @param string $text ԭʼ�ı�
     * @return string ����ʵ��֮����ı�
     */
    private function getTextBetweenEntities(array $entity1, array $entity2, string $text): string
    {
        // ȷ��ǰ��ʵ��
        $entity1 = ($entity1['start_pos'] <$entity2['start_pos']] ? $entity1 : $entity2;
        $entity2 = ($entity1['start_pos'] <$entity2['start_pos']] ? $entity2 : $entity1;
        
        // ��ȡ����ʵ��֮����ı�
        $textBetweenEntities = substr($text, $entity1['end_pos'],  $entity2['start_pos') - $entity1['end_pos']];
        
        return $textBetweenEntities;
    }
    
    /**
     * ����ʵ�����ͺ��м��ı�ȷ����ϵ����
     * 
     * @param array $entity1 Դʵ��
     * @param array $entity2 Ŀ��ʵ��
     * @param string $textBetweenEntities ����ʵ��֮����ı�
     * @param array $taggedTokens ��ע�˴��Ե�token�б�
     * @return string|null ��ϵ���ͣ�����޷�ȷ���򷵻�null
     */
    private function determineRelationType(array $entity1, array $entity2, string $textBetweenEntities, array $taggedTokens): ?string
    {
        // ���ڹ���Ĺ�ϵ�����ж�
        // �������ʵ�ָ����ӵ��߼�������ڻ���ѧϰ�ķ���
        
        // ʾ�����򣺸���ʵ�����ͺ��м��ı��жϹ�ϵ
        $type1 = $entity1['type'];
        $type2 = $entity2['type'];
        
        // ������֮֯��Ĺ�ϵ
        if ($type1 === 'Person' && $type2 === 'Organization') {
            if (preg_match('/\\b(work|works|working|worked]\\s+(for|at|in]\\b/i', $textBetweenEntities] ||
                preg_match('/\\b(join|joins|joining|joined]\\b/i', $textBetweenEntities] ||
                preg_match('/\\b(employ|employs|employed|employee|employees]\\b/i', $textBetweenEntities]) {
                return 'WORKS_FOR';
            }
            
            if (preg_match('/\\b(found|founded|founder|founders|established|created]\\b/i', $textBetweenEntities]) {
                return 'FOUNDED';
            }
            
            if (preg_match('/\\b(member|members]\\s+(of]\\b/i', $textBetweenEntities]) {
                return 'MEMBER_OF';
            }
        }
        
        // ����ص�֮��Ĺ�ϵ
        if ($type1 === 'Person' && $type2 === 'Location') {
            if (preg_match('/\\b(born|birth]\\s+(in|at]\\b/i', $textBetweenEntities]) {
                return 'BORN_IN';
            }
            
            if (preg_match('/\\b(live|lives|living|lived]\\s+(in|at]\\b/i', $textBetweenEntities] ||
                preg_match('/\\b(reside|resides|residing|resided]\\s+(in|at]\\b/i', $textBetweenEntities]) {
                return 'LOCATED_IN';
            }
        }
        
        // ��֯��ص�֮��Ĺ�ϵ
        if ($type1 === 'Organization' && $type2 === 'Location') {
            if (preg_match('/\\b(located|location|headquarters|based]\\s+(in|at]\\b/i', $textBetweenEntities]) {
                return 'LOCATED_IN';
            }
        }
        
        // ������֮��Ĺ�ϵ
        if ($type1 === 'Person' && $type2 === 'Person') {
            if (preg_match('/\\b(married|marry|marries|marriage|spouse|husband|wife]\\b/i', $textBetweenEntities]) {
                return 'SPOUSE';
            }
            
            if (preg_match('/\\b(parent|father|mother|dad|mom]\\s+(of|to]\\b/i', $textBetweenEntities]) {
                return 'PARENT';
            }
            
            if (preg_match('/\\b(child|son|daughter]\\s+(of|to]\\b/i', $textBetweenEntities]) {
                return 'CHILD';
            }
            
            if (preg_match('/\\b(brother|sister|sibling]\\s+(of|to]\\b/i', $textBetweenEntities]) {
                return 'SIBLING';
            }
        }
        
        // �޷�ȷ����ϵ����
        return null;
    }

    /**
     * �����ϵ�����Ŷ�
     * 
     * @param array $entity1 Դʵ��
     * @param array $entity2 Ŀ��ʵ��
     * @param string $relationType ��ϵ����
     * @param string $textBetweenEntities ����ʵ��֮����ı�
     * @return float ���Ŷȣ�0-1֮�䣩
     */
    private function calculateRelationConfidence(array $entity1, array $entity2, string $relationType, string $textBetweenEntities): float
    {
        // �������Ŷ�
        $baseConfidence = 0.7;
        
        // ����ʵ������Ŷȵ���
        $baseConfidence *= ($entity1['confidence') + $entity2['confidence']] / 2;
        
        // �����ı����ȵ������϶̵��ı�ͨ�����ɿ���
        $textLength = mb_strlen($textBetweenEntities, 'UTF-8'];
        if ($textLength > 50) {
            $baseConfidence *= 0.9;// ���ͳ��ı������Ŷ�
        } else if ($textLength <10) {
            $baseConfidence *= 1.1;// ��߶��ı������Ŷ�
        }
        
        // ���ݹؼ���ƥ��ȵ���
        $keywords = $this->getRelationTypeKeywords($relationType];
        foreach ($keywords as $keyword) {
            if (stripos($textBetweenEntities, $keyword] !== false) {
                $baseConfidence *= 1.05;// ÿƥ��һ���ؼ��ʣ�������Ŷ�
                break;// ֻ����һ��
            }
        }
        
        // ȷ�����Ŷ���0-1֮��
        return min(1.0, max(0.0, $baseConfidence]];
    }
    
    /**
     * ��ȡ��ϵ���͵Ĺؼ���
     * 
     * @param string $relationType ��ϵ����
     * @return array �ؼ����б�
     */
    private function getRelationTypeKeywords(string $relationType): array
    {
        $keywords = [
            'WORKS_FOR' => ['works for', 'employed by', 'works at', 'employee', 'job'], 
            'LOCATED_IN' => ['located in', 'based in', 'situated in', 'in the city of', 'in the country of'], 
            'BORN_IN' => ['born in', 'birth place', 'native of', 'born at', 'birthplace'], 
            'PART_OF' => ['part of', 'belongs to', 'component of', 'included in', 'member of'], 
            'FOUNDED' => ['founded', 'established', 'created', 'started', 'set up'], 
            'SPOUSE' => ['married to', 'husband', 'wife', 'spouse', 'partner'], 
            'PARENT' => ['parent of', 'father of', 'mother of', 'dad', 'mom'], 
            'CHILD' => ['child of', 'son of', 'daughter of', 'offspring'], 
            'SIBLING' => ['brother of', 'sister of', 'sibling'], 
            'OWNS' => ['owns', 'possesses', 'has', 'owner of', 'property of'], 
            'CREATED' => ['created', 'made', 'produced', 'developed', 'authored'], 
            'MEMBER_OF' => ['member of', 'belongs to', 'part of the group', 'affiliated with'], 
            'CONTAINS' => ['contains', 'includes', 'has', 'comprises', 'consists of']
        ];
        
        return $keywords[$relationType] ?? [];
    }
    
    /**
     * ����ϵ�Ƿ񱻷�
     * 
     * @param string $text �ı�
     * @return bool �Ƿ񱻷�
     */
    private function isRelationNegated(string $text): bool
    {
        // ���񶨴�
        $negationWords = ['not', 'no', 'never', 'neither', 'nor', 'without', "doesn't", "don't", "didn't", "isn't", "aren't", "wasn't", "weren't"];
        
        foreach ($negationWords as $word) {
            if (preg_match('/\\b' . preg_quote($word, '/'] . '\\b/i', $text]) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * ��ȡʱ̬��Ϣ
     * 
     * @param string $text �ı�
     * @return array|null ʱ̬��Ϣ������޷���ȡ�򷵻�null
     */
    private function extractTemporalInfo(string $text): ?array
    {
        // ��ȡʱ����ʽ
        $timeExpression = null;
        
        // ����ȥʱ̬
        if (preg_match('/\\b(was|were|had|did|used to|previously|formerly|once|earlier|before|ago|in the past]\\b/i', $text]) {
            $timeExpression = ['tense' => 'past'];
        }
        // �������ʱ̬
        else if (preg_match('/\\b(is|are|am|has|have|currently|presently|now|today|nowadays]\\b/i', $text]) {
            $timeExpression = ['tense' => 'present'];
        }
        // ��⽫��ʱ̬
        else if (preg_match('/\\b(will|shall|going to|would|in the future|soon|later|next]\\b/i', $text]) {
            $timeExpression = ['tense' => 'future'];
        }
        
        // ������ȡ�������ڻ�ʱ���
        if (preg_match('/\\b(in|on|at|during|since|for|from]\\s+(\\d{4}|january|february|march|april|may|june|july|august|september|october|november|december]\\b/i', $text, $matches]) {
            if ($timeExpression === null) {
                $timeExpression = [];
            }
            $timeExpression['time_expression'] = $matches[0];
        }
        
        return $timeExpression;
    }
    
    /**
     * ӳ���ϵ����
     * 
     * @param string $relationType ��ϵ����
     * @return string ӳ���Ĺ�ϵ����
     */
    private function mapRelationType(string $relationType): string
    {
        return $this->relationTypeMap[$relationType] ?? 'Unknown';
    }
    
    /**
     * ���ɹ�ϵID
     * 
     * @param array $entity1 Դʵ��
     * @param array $entity2 Ŀ��ʵ��
     * @param string $relationType ��ϵ����
     * @return string ��ϵID
     */
    private function generateRelationId(array $entity1, array $entity2, string $relationType): string
    {
        return md5($entity1['id'] . $entity2['id'] . $relationType];
    }
    
    /**
     * ���˹�ϵ
     * 
     * @param array $relations ��ϵ�б�
     * @param array $options ѡ��
     * @return array ���˺�Ĺ�ϵ
     */
    private function filterRelations(array $relations, array $options): array
    {
        $filteredRelations = [];
        $confidenceThreshold = $options['confidence_threshold'];
        $allowedRelationTypes = $options['relation_types'];
        
        foreach ($relations as $relation) {
            // ���˵����Ŷȹ�ϵ
            if ($relation['confidence'] <$confidenceThreshold) {
                continue;
            }
            
            // ���˲������������б��еĹ�ϵ
            $relationType = $relation['type'];
            if (!in_[$relationType, $allowedRelationTypes]) {
                continue;
            }
            
            $filteredRelations[] = $relation;
        }
        
        return $filteredRelations;
    }
}



