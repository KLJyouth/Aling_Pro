<?php
/**
 * 文件名：RelationExtractor.php
 * 功能描述：关系提取器 - 从文本中提取实体间关系
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 * 
 * @package AlingAi\AI\Engines\KnowledgeGraph
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1);

namespace AlingAi\AI\Engines\KnowledgeGraph;

use Exception;
use InvalidArgumentException;
use AlingAi\AI\Engines\NLP\POSTagger;
use AlingAi\AI\Engines\NLP\TokenizerInterface;
use AlingAi\AI\Engines\NLP\UniversalTokenizer;

/**
 * 关系提取器
 * 
 * 从文本中提取实体间的关系，支持多种关系类型和语言
 */
class RelationExtractor
{
    /**
     * 配置参数
     */
    private array ;
    
    /**
     * 词性标注器
     */
    private ?POSTagger  = null;
    
    /**
     * 分词器
     */
    private ?TokenizerInterface  = null;
    
    /**
     * 实体提取器
     */
    private ?EntityExtractor  = null;
    
    /**
     * 关系类型映射
     */
    private array  = [];

    /**
     * 构造函数
     * 
     * @param array  配置参数
     */
    public function __construct(array  = [])
    {
        ->config = array_merge(->getDefaultConfig(), );
        ->initializeComponents();
        ->initializeRelationTypeMap();
    }
    
    /**
     * 获取默认配置
     * 
     * @return array 默认配置
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
            'max_entity_distance' => 15, // 最大实体间距
            'enable_negation_detection' => true,
            'enable_temporal_analysis' => true
        ];
    }
    
    /**
     * 初始化组件
     */
    private function initializeComponents(): void
    {
        ->posTagger = new POSTagger();
        ->tokenizer = new UniversalTokenizer();
        ->entityExtractor = new EntityExtractor();
    }
    
    /**
     * 初始化关系类型映射
     */
    private function initializeRelationTypeMap(): void
    {
        ->relationTypeMap = [
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
     * 从文本中提取关系
     * 
     * @param string  输入文本
     * @param array|null  预先提取的实体，如果为null则自动提取
     * @param array  提取选项
     * @return array 提取的关系
     * @throws InvalidArgumentException
     */
    public function extract(string , ?array  = null, array  = []): array
    {
        // 验证文本
        if (empty()) {
            throw new InvalidArgumentException('文本不能为空');
        }
        
        // 处理选项
         = array_merge(->config, );
        
        // 如果没有提供实体，则提取实体
        if ( === null) {
             = ->entityExtractor->extract(, );
        }
        
        // 如果没有实体，则返回空数组
        if (empty()) {
            return [];
        }
        
        // 分词和词性标注
         = ->tokenizer->tokenize();
         = ->posTagger->tag();
        
        // 提取实体对之间的关系
         = ->extractRelationsBetweenEntities(, , , );
        
        // 过滤关系
         = ->filterRelations(, );
        
        return ;
    }

    /**
     * 提取实体对之间的关系
     * 
     * @param array  实体列表
     * @param array  标注了词性的token列表
     * @param string  原始文本
     * @param array  选项
     * @return array 关系列表
     */
    private function extractRelationsBetweenEntities(array , array , string , array ): array
    {
         = [];
         = count();
        
        // 遍历所有实体对
        for ( = 0;  < ; ++) {
            for ( = 0;  < ; ++) {
                // 跳过相同实体
                if ( === ) {
                    continue;
                }
                
                 = [];
                 = [];
                
                // 检查实体间距是否超过阈值
                if (->getEntityDistance(, ) > ['max_entity_distance']) {
                    continue;
                }
                
                // 提取两个实体之间的关系
                 = ->detectRelation(, , , , );
                
                if ( !== null) {
                    [] = ;
                }
            }
        }
        
        return ;
    }
    
    /**
     * 计算两个实体之间的距离
     * 
     * @param array  第一个实体
     * @param array  第二个实体
     * @return int 实体间的距离（token数量）
     */
    private function getEntityDistance(array , array ): int
    {
        // 计算两个实体之间的token数量
        // 如果实体重叠，则距离为0
        if (['end_pos'] >= ['start_pos'] && ['start_pos'] <= ['end_pos']) {
            return 0;
        }
        
        // 计算两个实体之间的距离
        return (['end_pos'] < ['start_pos']) 
            ? ['start_pos'] - ['end_pos'] 
            : ['start_pos'] - ['end_pos'];
    }

    /**
     * 检测两个实体之间的关系
     * 
     * @param array  源实体
     * @param array  目标实体
     * @param array  标注了词性的token列表
     * @param string  原始文本
     * @param array  选项
     * @return array|null 检测到的关系，如果没有检测到则返回null
     */
    private function detectRelation(array , array , array , string , array ): ?array
    {
        // 获取两个实体之间的文本段
         = ->getTextBetweenEntities(, , );
        
        // 根据实体类型和中间文本判断关系类型
         = ->determineRelationType(, , , );
        
        if ( === null) {
            return null;
        }
        
        // 计算关系的置信度
         = ->calculateRelationConfidence(, , , );
        
        // 如果置信度低于阈值，则不返回关系
        if ( < ['confidence_threshold']) {
            return null;
        }
        
        // 检测否定
         = ['enable_negation_detection'] && ->isRelationNegated();
        
        // 检测时态
         = ['enable_temporal_analysis'] ? ->extractTemporalInfo() : null;
        
        // 构建关系对象
        return [
            'id' => ->generateRelationId(, , ),
            'source_id' => ['id'],
            'target_id' => ['id'],
            'type' => ->mapRelationType(),
            'confidence' => ,
            'is_negated' => ,
            'temporal_info' => ,
            'metadata' => [
                'source' => 'text_extraction',
                'extraction_time' => time(),
                'between_text' => 
            ]
        ];
    }

    /**
     * 获取两个实体之间的文本
     * 
     * @param array  第一个实体
     * @param array  第二个实体
     * @param string  原始文本
     * @return string 两个实体之间的文本
     */
    private function getTextBetweenEntities(array , array , string ): string
    {
        // 确定前后实体
         = (['start_pos'] < ['start_pos']) ?  : ;
         = (['start_pos'] < ['start_pos']) ?  : ;
        
        // 提取两个实体之间的文本
         = ['end_pos'];
         = ['start_pos'] - ;
        
        return substr(, , );
    }
    
    /**
     * 根据实体类型和中间文本确定关系类型
     * 
     * @param array  源实体
     * @param array  目标实体
     * @param string  两个实体之间的文本
     * @param array  标注了词性的token列表
     * @return string|null 关系类型，如果无法确定则返回null
     */
    private function determineRelationType(array , array , string , array ): ?string
    {
        // 基于规则的关系类型判断
        // 这里可以实现更复杂的逻辑，如基于机器学习的方法
        
        // 示例规则：根据实体类型和中间文本判断关系
         = ['type'];
         = ['type'];
        
        // 人与组织之间的关系
        if ( === 'Person' &&  === 'Organization') {
            if (preg_match('/\\b(work|works|working|worked)\\s+(for|at|in)\\b/i', ) ||
                preg_match('/\\b(join|joins|joining|joined)\\b/i', ) ||
                preg_match('/\\b(employ|employs|employed|employee|employees)\\b/i', )) {
                return 'WORKS_FOR';
            }
            
            if (preg_match('/\\b(found|founded|founder|founders|established|created)\\b/i', )) {
                return 'FOUNDED';
            }
            
            if (preg_match('/\\b(member|members)\\s+(of)\\b/i', )) {
                return 'MEMBER_OF';
            }
        }
        
        // 人与地点之间的关系
        if ( === 'Person' &&  === 'Location') {
            if (preg_match('/\\b(born|birth)\\s+(in|at)\\b/i', )) {
                return 'BORN_IN';
            }
            
            if (preg_match('/\\b(live|lives|living|lived)\\s+(in|at)\\b/i', ) ||
                preg_match('/\\b(reside|resides|residing|resided)\\s+(in|at)\\b/i', )) {
                return 'LOCATED_IN';
            }
        }
        
        // 组织与地点之间的关系
        if ( === 'Organization' &&  === 'Location') {
            if (preg_match('/\\b(located|location|headquarters|based)\\s+(in|at)\\b/i', )) {
                return 'LOCATED_IN';
            }
        }
        
        // 人与人之间的关系
        if ( === 'Person' &&  === 'Person') {
            if (preg_match('/\\b(married|marry|marries|marriage|spouse|husband|wife)\\b/i', )) {
                return 'SPOUSE';
            }
            
            if (preg_match('/\\b(parent|father|mother|dad|mom)\\s+(of|to)\\b/i', )) {
                return 'PARENT';
            }
            
            if (preg_match('/\\b(child|son|daughter)\\s+(of|to)\\b/i', )) {
                return 'CHILD';
            }
            
            if (preg_match('/\\b(brother|sister|sibling)\\s+(of|to)\\b/i', )) {
                return 'SIBLING';
            }
        }
        
        // 无法确定关系类型
        return null;
    }

    /**
     * 计算关系的置信度
     * 
     * @param array  源实体
     * @param array  目标实体
     * @param string  关系类型
     * @param string  两个实体之间的文本
     * @return float 置信度（0-1之间）
     */
    private function calculateRelationConfidence(array , array , string , string ): float
    {
        // 基础置信度
         = 0.7;
        
        // 根据实体的置信度调整
         *= (['confidence'] + ['confidence']) / 2;
        
        // 根据文本长度调整（较短的文本通常更可靠）
         = mb_strlen(, 'UTF-8');
        if ( > 50) {
             *= 0.9;  // 降低长文本的置信度
        } else if ( < 10) {
             *= 1.1;  // 提高短文本的置信度
        }
        
        // 根据关键词匹配度调整
         = ->getRelationTypeKeywords();
        foreach ( as ) {
            if (stripos(, ) !== false) {
                 *= 1.05;  // 每匹配一个关键词，提高置信度
                break;  // 只计算一次
            }
        }
        
        // 确保置信度在0-1之间
        return min(1.0, max(0.0, ));
    }
    
    /**
     * 获取关系类型的关键词
     * 
     * @param string  关系类型
     * @return array 关键词列表
     */
    private function getRelationTypeKeywords(string ): array
    {
         = [
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
        
        return [] ?? [];
    }
    
    /**
     * 检测关系是否被否定
     * 
     * @param string  文本
     * @return bool 是否被否定
     */
    private function isRelationNegated(string ): bool
    {
        // 检测否定词
         = ['not', 'no', 'never', 'neither', 'nor', 'without', 'doesn\'t', 'don\'t', 'didn\'t', 'isn\'t', 'aren\'t', 'wasn\'t', 'weren\'t'];
        
        foreach ( as ) {
            if (preg_match('/\\b' . preg_quote(, '/') . '\\b/i', )) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 提取时态信息
     * 
     * @param string  文本
     * @return array|null 时态信息，如果无法提取则返回null
     */
    private function extractTemporalInfo(string ): ?array
    {
        // 提取时间表达式
         = null;
        
        // 检测过去时态
        if (preg_match('/\\b(was|were|had|did|used to|previously|formerly|once|earlier|before|ago|in the past)\\b/i', )) {
             = ['tense' => 'past'];
        }
        // 检测现在时态
        else if (preg_match('/\\b(is|are|am|has|have|currently|presently|now|today|nowadays)\\b/i', )) {
             = ['tense' => 'present'];
        }
        // 检测将来时态
        else if (preg_match('/\\b(will|shall|going to|would|in the future|soon|later|next)\\b/i', )) {
             = ['tense' => 'future'];
        }
        
        // 尝试提取具体日期或时间段
        if (preg_match('/\\b(in|on|at|during|since|for|from)\\s+(\\d{4}|january|february|march|april|may|june|july|august|september|october|november|december)\\b/i', , )) {
            if ( === null) {
                 = [];
            }
            ['time_expression'] = [0];
        }
        
        return ;
    }
    
    /**
     * 映射关系类型
     * 
     * @param string  关系类型
     * @return string 映射后的关系类型
     */
    private function mapRelationType(string ): string
    {
        return ->relationTypeMap[] ?? 'Unknown';
    }
    
    /**
     * 生成关系ID
     * 
     * @param array  源实体
     * @param array  目标实体
     * @param string  关系类型
     * @return string 关系ID
     */
    private function generateRelationId(array , array , string ): string
    {
        return md5(['id'] . ['id'] . );
    }
    
    /**
     * 过滤关系
     * 
     * @param array  关系列表
     * @param array  选项
     * @return array 过滤后的关系
     */
    private function filterRelations(array , array ): array
    {
         = [];
         = ['confidence_threshold'];
         = ['relation_types'];
        
        foreach ( as ) {
            // 过滤低置信度关系
            if (['confidence'] < ) {
                continue;
            }
            
            // 过滤不在允许类型列表中的关系
             = array_search(['type'], ->relationTypeMap);
            if ( === false || !in_array(, )) {
                continue;
            }
            
            [] = ;
        }
        
        return ;
    }
}
