<?php
/**
 * 文件名：NERModel.php
 * 功能描述：命名实体识别模型 - 实现文本中命名实体的识别
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 *
 * @package AlingAi\AI\Engines\NLP
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1);

namespace AlingAi\AI\Engines\NLP;

use Exception;
use InvalidArgumentException;

/**
 * 命名实体识别模型
 *
 * 实现文本中命名实体的识别，支持多种语言和实体类型
 */
class NERModel
{
    /**
     * 配置参数
     */
    private array $config;

    /**
     * 英文命名实体规则
     */
    private array $englishRules;

    /**
     * 中文命名实体规则
     */
    private array $chineseRules;

    /**
     * 实体类型
     */
    private array $entityTypes;

    /**
     * 构造函数
     *
     * @param array $config 配置参数
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->entityTypes = $this->config['entity_types'];
        $this->loadResources();
    }

    /**
     * 加载资源
     */
    private function loadResources(): void
    {
        $this->loadEnglishRules();
        $this->loadChineseRules();
    }

    /**
     * 加载英文命名实体规则
     */
    private function loadEnglishRules(): void
    {
        // 简化版的英文命名实体规则
        $this->englishRules = [
            // 人名规则
            'PERSON' => [
                'patterns' => [
                    '/\b[A-Z][a-z]+ [A-Z][a-z]+\b/',  // 名 姓
                    '/\b(Mr|Mrs|Ms|Dr|Prof)\. [A-Z][a-z]+\b/',  // 称谓 姓
                ],
                'keywords' => [
                    'John', 'David', 'Michael', 'Robert', 'William', 'Richard', 'Joseph',
                    'Thomas', 'Charles', 'Christopher', 'Daniel', 'Matthew', 'Anthony', 'Mark',
                    'Donald', 'Steven', 'Paul', 'Andrew', 'Joshua', 'Kenneth', 'Kevin', 'Brian',
                    'Mary', 'Jennifer', 'Linda', 'Patricia', 'Elizabeth', 'Susan', 'Jessica',
                    'Sarah', 'Karen', 'Nancy', 'Lisa', 'Betty', 'Margaret', 'Sandra', 'Ashley',
                    'Kimberly', 'Emily', 'Donna', 'Michelle', 'Dorothy', 'Carol', 'Amanda'
                ]
            ],
            // 组织机构规则
            'ORGANIZATION' => [
                'patterns' => [
                    '/\b[A-Z][a-z]+ (Inc|Corp|Corporation|Company|Co|Ltd|Limited)\b/',  // 公司
                    '/\b[A-Z][a-z]+ (University|College|School|Institute|Association|Organization)\b/',  // 教育机构
                ],
                'keywords' => [
                    'Google', 'Microsoft', 'Apple', 'Amazon', 'Facebook', 'IBM', 'Intel',
                    'Oracle', 'Samsung', 'Sony', 'Toyota', 'Honda', 'Ford', 'BMW', 'Audi',
                    'Walmart', 'Target', 'Coca-Cola', 'Pepsi', 'McDonald\'s', 'Starbucks',
                    'Harvard', 'Stanford', 'MIT', 'Oxford', 'Cambridge', 'Yale', 'Princeton',
                    'NASA', 'FBI', 'CIA', 'WHO', 'UN', 'NATO', 'EU', 'IMF', 'World Bank'
                ]
            ],
            // 地点规则
            'LOCATION' => [
                'patterns' => [
                    '/\b[A-Z][a-z]+ (Street|Avenue|Boulevard|Road|Lane|Drive|Place|Square)\b/',  // 街道
                    '/\b[A-Z][a-z]+ (City|Town|Village|County|District|State|Province|Country)\b/',  // 行政区划
                ],
                'keywords' => [
                    'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia',
                    'San Antonio', 'San Diego', 'Dallas', 'San Jose', 'Austin', 'Jacksonville',
                    'San Francisco', 'Columbus', 'Indianapolis', 'Seattle', 'Denver', 'Boston',
                    'United States', 'Canada', 'Mexico', 'Brazil', 'Argentina', 'United Kingdom',
                    'France', 'Germany', 'Italy', 'Spain', 'Russia', 'China', 'Japan', 'India',
                    'Australia', 'New Zealand', 'South Africa', 'Egypt', 'Nigeria', 'Kenya'
                ]
            ],
            // 日期规则
            'DATE' => [
                'patterns' => [
                    '/\b\d{1,2}\/\d{1,2}\/\d{2,4}\b/',  // MM/DD/YYYY 或 DD/MM/YYYY
                    '/\b(January|February|March|April|May|June|July|August|September|October|November|December) \d{1,2}, \d{4}\b/',  // 月 日, 年
                    '/\b\d{1,2} (January|February|March|April|May|June|July|August|September|October|November|December) \d{4}\b/',  // 日 月 年
                ],
                'keywords' => [
                    'today', 'tomorrow', 'yesterday', 'last week', 'next week',
                    'last month', 'next month', 'last year', 'next year'
                ]
            ],
            // 时间规则
            'TIME' => [
                'patterns' => [
                    '/\b\d{1,2}:\d{2}\b/',  // HH:MM
                    '/\b\d{1,2}:\d{2}:\d{2}\b/',  // HH:MM:SS
                    '/\b\d{1,2}:\d{2} (AM|PM)\b/',  // HH:MM AM/PM
                ],
                'keywords' => [
                    'noon', 'midnight', 'morning', 'afternoon', 'evening', 'night'
                ]
            ],
            // 金额规则
            'MONEY' => [
                'patterns' => [
                    '/\$\d+(\.\d{2})?\b/',  // $金额
                    '/\b\d+ dollars\b/',  // 金额 dollars
                    '/\b\d+ USD\b/',  // 金额 USD
                ],
                'keywords' => []
            ],
            // 百分比规则
            'PERCENT' => [
                'patterns' => [
                    '/\b\d+(\.\d+)?%\b/',  // 数字%
                    '/\b\d+(\.\d+)? percent\b/',  // 数字 percent
                ],
                'keywords' => []
            ]
        ];
    }

    /**
     * 加载中文命名实体规则
     */
    private function loadChineseRules(): void
    {
        // 简化版的中文命名实体规则
        $this->chineseRules = [
            // 人名规则
            'PERSON' => [
                'patterns' => [
                    '/[\x{4e00}-\x{9fa5}]{2,3}/u',  // 2-3个汉字
                ],
                'keywords' => [
                    '张三', '李四', '王五', '赵六', '钱七', '孙八', '周九', '吴十',
                    '郑', '王', '李', '张', '刘', '陈', '杨', '黄', '赵', '周', '吴', '徐',
                    '孙', '马', '朱', '胡', '林', '郭', '何', '高', '罗', '郑', '梁', '谢',
                    '宋', '唐', '许', '邓', '冯', '韩', '曹', '曾', '彭', '萧', '蔡', '潘',
                    '田', '董', '袁', '于', '余', '叶', '蒋', '杜', '苏', '魏', '程', '吕',
                    '丁', '沈', '任', '姚', '卢', '傅', '钟', '姜', '崔', '谭', '廖', '范',
                    '汪', '陆', '金', '石', '戴', '贾', '韦', '夏', '邱', '方', '侯', '邹',
                    '熊', '孟', '秦', '白', '江', '阎', '薛', '尹', '段', '雷', '黎', '史',
                    '龙', '陶', '贺', '顾', '毛', '郝', '龚', '邵', '万', '钱', '严', '赖',
                    '覃', '洪', '武', '莫', '孔'
                ]
            ],
            // 组织机构规则
            'ORGANIZATION' => [
                'patterns' => [
                    '/[\x{4e00}-\x{9fa5}]+(公司|集团|企业|工厂|学校|大学|医院|银行|机构|组织|部门|协会|研究所)/u',  // 组织机构
                ],
                'keywords' => [
                    '腾讯', '阿里巴巴', '百度', '华为', '小米', '京东', '网易', '美团', '字节跳动',
                    '中国移动', '中国电信', '中国联通', '中国石油', '中国石化', '中国银行', '工商银行',
                    '建设银行', '农业银行', '招商银行', '平安保险', '太平洋保险', '中国人寿',
                    '清华大学', '北京大学', '复旦大学', '上海交通大学', '浙江大学', '南京大学',
                    '中国科学院', '中国社会科学院', '中国工程院', '国务院', '中央政府', '国家发改委'
                ]
            ],
            // 地点规则
            'LOCATION' => [
                'patterns' => [
                    '/[\x{4e00}-\x{9fa5}]+(省|市|县|区|镇|村|街|路|道|大道|广场)/u',  // 地点
                ],
                'keywords' => [
                    '北京', '上海', '广州', '深圳', '杭州', '南京', '武汉', '成都', '重庆', '西安',
                    '天津', '苏州', '无锡', '宁波', '青岛', '大连', '厦门', '福州', '哈尔滨', '长春',
                    '沈阳', '济南', '郑州', '长沙', '昆明', '贵阳', '南宁', '海口', '三亚', '兰州',
                    '西宁', '银川', '乌鲁木齐', '拉萨', '呼和浩特', '南昌', '合肥', '太原', '石家庄',
                    '中国', '美国', '日本', '韩国', '俄罗斯', '英国', '法国', '德国', '意大利', '加拿大',
                    '澳大利亚', '新西兰', '印度', '巴西', '南非', '埃及', '尼日利亚', '肯尼亚'
                ]
            ],
            // 日期规则
            'DATE' => [
                'patterns' => [
                    '/\d{4}年\d{1,2}月\d{1,2}日/',  // YYYY年MM月DD日
                    '/\d{4}-\d{1,2}-\d{1,2}/',  // YYYY-MM-DD
                    '/\d{4}\/\d{1,2}\/\d{1,2}/',  // YYYY/MM/DD
                ],
                'keywords' => [
                    '今天', '明天', '后天', '昨天', '前天', '上周', '本周', '下周',
                    '上个月', '这个月', '下个月', '去年', '今年', '明年',
                    '春天', '夏天', '秋天', '冬天', '周一', '周二', '周三', '周四', '周五', '周六', '周日'
                ]
            ],
            // 时间规则
            'TIME' => [
                'patterns' => [
                    '/\d{1,2}时\d{1,2}分(\d{1,2}秒)?/',  // HH时MM分SS秒
                    '/\d{1,2}:\d{2}(:\d{2})?/',  // HH:MM:SS
                ],
                'keywords' => [
                    '早上', '上午', '中午', '下午', '晚上', '凌晨', '深夜',
                    '正午', '午夜', '黎明', '傍晚', '清晨', '日出', '日落'
                ]
            ],
            // 金额规则
            'MONEY' => [
                'patterns' => [
                    '/\d+(\.\d+)?(元|块|圆|角|分|美元|欧元|英镑|日元|韩元)/',  // 金额单位
                    '/人民币\d+(\.\d+)?/',  // 人民币金额
                    '/\$\d+(\.\d{2})?/',  // $金额
                ],
                'keywords' => []
            ],
            // 百分比规则
            'PERCENT' => [
                'patterns' => [
                    '/\d+(\.\d+)?%/',  // 数字%
                    '/百分之\d+(\.\d+)?/',  // 百分之数字
                ],
                'keywords' => []
            ]
        ];
    }

    /**
     * 获取默认配置
     *
     * @return array 默认配置
     */
    private function getDefaultConfig(): array
    {
        return [
            'model' => 'default', // default, hmm, crf
            'default_language' => 'en',
            'entity_types' => ['PERSON', 'ORGANIZATION', 'LOCATION', 'DATE', 'TIME', 'MONEY', 'PERCENT'],
            'min_confidence' => 0.6
        ];
    }

    /**
     * 识别命名实体
     *
     * @param array $tokens 分词结果
     * @param string|null $language 语言代码，如果为null则自动检测
     * @return array 命名实体识别结果
     */
    public function recognize(array $tokens, ?string $language = null): array
    {
        if (empty($tokens)) {
            return [];
        }

        // 检测语言
        if ($language === null) {
            $language = $this->detectLanguage($tokens);
        }

        // 根据语言选择识别方法
        switch ($language) {
            case 'en':
                return $this->recognizeEnglish($tokens);
            case 'zh':
                return $this->recognizeChinese($tokens);
            default:
                return $this->recognizeEnglish($tokens); // 默认使用英文识别
        }
    }

    /**
     * 检测语言
     *
     * @param array $tokens 分词结果
     * @return string 语言代码
     */
    private function detectLanguage(array $tokens): string
    {
        $chineseCount = 0;
        $englishCount = 0;
        $totalCount = count($tokens);

        if ($totalCount === 0) {
            return $this->config['default_language'];
        }

        foreach ($tokens as $token) {
            if (isset($token['text'])) {
                if (preg_match('/\p{Han}/u', $token['text'])) {
                    $chineseCount++;
                } elseif (preg_match('/[a-zA-Z]/u', $token['text'])) {
                    $englishCount++;
                }
            }
        }

        $chineseRatio = $chineseCount / $totalCount;
        $englishRatio = $englishCount / $totalCount;

        if ($chineseRatio > $englishRatio) {
            return 'zh';
        } else {
            return 'en';
        }
    }

    /**
     * 英文命名实体识别
     *
     * @param array $tokens 分词结果
     * @return array 命名实体识别结果
     */
    private function recognizeEnglish(array $tokens): array
    {
        $text = $this->tokensToText($tokens);
        $entities = [];

        // 使用规则识别实体
        foreach ($this->entityTypes as $entityType) {
            if (!isset($this->englishRules[$entityType])) {
                continue;
            }

            $rules = $this->englishRules[$entityType];

            // 使用模式匹配
            foreach ($rules['patterns'] as $pattern) {
                if (preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
                    foreach ($matches[0] as $match) {
                        $entityText = $match[0];
                        $startPos = $match[1];
                        $endPos = $startPos + strlen($entityText) - 1;

                        // 查找对应的tokens
                        $entityTokens = $this->findTokensByPosition($tokens, $startPos, $endPos);

                        if (!empty($entityTokens)) {
                            $entities[] = [
                                'text' => $entityText,
                                'type' => $entityType,
                                'start' => $startPos,
                                'end' => $endPos,
                                'tokens' => $entityTokens,
                                'confidence' => 0.8
                            ];
                        }
                    }
                }
            }

            // 使用关键词匹配
            foreach ($rules['keywords'] as $keyword) {
                $pos = 0;
                while (($pos = stripos($text, $keyword, $pos)) !== false) {
                    $entityText = substr($text, $pos, strlen($keyword));
                    $startPos = $pos;
                    $endPos = $pos + strlen($keyword) - 1;

                    // 查找对应的tokens
                    $entityTokens = $this->findTokensByPosition($tokens, $startPos, $endPos);

                    if (!empty($entityTokens)) {
                        $entities[] = [
                            'text' => $entityText,
                            'type' => $entityType,
                            'start' => $startPos,
                            'end' => $endPos,
                            'tokens' => $entityTokens,
                            'confidence' => 0.7
                        ];
                    }

                    $pos += strlen($keyword);
                }
            }
        }

        // 去重和合并
        $entities = $this->mergeOverlappingEntities($entities);

        return $entities;
    }

    /**
     * 中文命名实体识别
     *
     * @param array $tokens 分词结果
     * @return array 命名实体识别结果
     */
    private function recognizeChinese(array $tokens): array
    {
        $text = $this->tokensToText($tokens);
        $entities = [];

        // 使用规则识别实体
        foreach ($this->entityTypes as $entityType) {
            if (!isset($this->chineseRules[$entityType])) {
                continue;
            }

            $rules = $this->chineseRules[$entityType];

            // 使用模式匹配
            foreach ($rules['patterns'] as $pattern) {
                if (preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
                    foreach ($matches[0] as $match) {
                        $entityText = $match[0];
                        $startPos = $match[1];
                        $endPos = $startPos + mb_strlen($entityText, 'UTF-8') - 1;

                        // 查找对应的tokens
                        $entityTokens = $this->findTokensByPosition($tokens, $startPos, $endPos);

                        if (!empty($entityTokens)) {
                            $entities[] = [
                                'text' => $entityText,
                                'type' => $entityType,
                                'start' => $startPos,
                                'end' => $endPos,
                                'tokens' => $entityTokens,
                                'confidence' => 0.8
                            ];
                        }
                    }
                }
            }

            // 使用关键词匹配
            foreach ($rules['keywords'] as $keyword) {
                $pos = 0;
                while (($pos = mb_stripos($text, $keyword, $pos, 'UTF-8')) !== false) {
                    $entityText = mb_substr($text, $pos, mb_strlen($keyword, 'UTF-8'), 'UTF-8');
                    $startPos = $pos;
                    $endPos = $pos + mb_strlen($keyword, 'UTF-8') - 1;

                    // 查找对应的tokens
                    $entityTokens = $this->findTokensByPosition($tokens, $startPos, $endPos);

                    if (!empty($entityTokens)) {
                        $entities[] = [
                            'text' => $entityText,
                            'type' => $entityType,
                            'start' => $startPos,
                            'end' => $endPos,
                            'tokens' => $entityTokens,
                            'confidence' => 0.7
                        ];
                    }

                    $pos += mb_strlen($keyword, 'UTF-8');
                }
            }
        }

        // 去重和合并
        $entities = $this->mergeOverlappingEntities($entities);

        return $entities;
    }

    /**
     * 将tokens转换为文本
     *
     * @param array $tokens 分词结果
     * @return string 文本
     */
    private function tokensToText(array $tokens): string
    {
        $text = '';
        foreach ($tokens as $token) {
            if (isset($token['text'])) {
                $text .= $token['text'];
            }
        }
        return $text;
    }

    /**
     * 根据位置查找tokens
     *
     * @param array $tokens 分词结果
     * @param int $startPos 起始位置
     * @param int $endPos 结束位置
     * @return array 对应的tokens
     */
    private function findTokensByPosition(array $tokens, int $startPos, int $endPos): array
    {
        $result = [];
        foreach ($tokens as $token) {
            if (isset($token['start']) && isset($token['end'])) {
                // 检查token是否在指定范围内
                if (($token['start'] >= $startPos && $token['start'] <= $endPos) ||
                    ($token['end'] >= $startPos && $token['end'] <= $endPos) ||
                    ($token['start'] <= $startPos && $token['end'] >= $endPos)) {
                    $result[] = $token;
                }
            }
        }
        return $result;
    }

    /**
     * 合并重叠的实体
     *
     * @param array $entities 实体列表
     * @return array 合并后的实体列表
     */
    private function mergeOverlappingEntities(array $entities): array
    {
        if (count($entities) <= 1) {
            return $entities;
        }

        // 按起始位置排序
        usort($entities, function($a, $b) {
            if ($a['start'] == $b['start']) {
                return $b['end'] - $a['end']; // 如果起始位置相同，优先选择更长的实体
            }
            return $a['start'] - $b['start'];
        });

        $result = [];
        $current = $entities[0];

        for ($i = 1; $i < count($entities); $i++) {
            $next = $entities[$i];

            // 检查是否重叠
            if ($current['end'] >= $next['start']) {
                // 如果当前实体的置信度更高，保留当前实体
                if ($current['confidence'] >= $next['confidence']) {
                    continue;
                } else {
                    // 否则，使用下一个实体替换当前实体
                    $current = $next;
                }
            } else {
                // 不重叠，添加当前实体到结果中，并更新当前实体
                $result[] = $current;
                $current = $next;
            }
        }

        // 添加最后一个实体
        $result[] = $current;

        return $result;
    }

    /**
     * 获取配置
     *
     * @return array 配置
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * 设置配置
     *
     * @param array $config 配置
     * @return void
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
        if (isset($config['entity_types'])) {
            $this->entityTypes = $config['entity_types'];
        }
    }

    /**
     * 获取英文命名实体规则
     *
     * @return array 英文命名实体规则
     */
    public function getEnglishRules(): array
    {
        return $this->englishRules;
    }

    /**
     * 添加英文命名实体规则
     *
     * @param string $entityType 实体类型
     * @param string $pattern 模式
     * @param bool $isPattern 是否为正则表达式模式
     * @return void
     */
    public function addEnglishRule(string $entityType, string $pattern, bool $isPattern = true): void
    {
        if (!in_array($entityType, $this->entityTypes)) {
            $this->entityTypes[] = $entityType;
        }

        if (!isset($this->englishRules[$entityType])) {
            $this->englishRules[$entityType] = [
                'patterns' => [],
                'keywords' => []
            ];
        }

        if ($isPattern) {
            $this->englishRules[$entityType]['patterns'][] = $pattern;
        } else {
            $this->englishRules[$entityType]['keywords'][] = $pattern;
        }
    }

    /**
     * 获取中文命名实体规则
     *
     * @return array 中文命名实体规则
     */
    public function getChineseRules(): array
    {
        return $this->chineseRules;
    }

    /**
     * 添加中文命名实体规则
     *
     * @param string $entityType 实体类型
     * @param string $pattern 模式
     * @param bool $isPattern 是否为正则表达式模式
     * @return void
     */
    public function addChineseRule(string $entityType, string $pattern, bool $isPattern = true): void
    {
        if (!in_array($entityType, $this->entityTypes)) {
            $this->entityTypes[] = $entityType;
        }

        if (!isset($this->chineseRules[$entityType])) {
            $this->chineseRules[$entityType] = [
                'patterns' => [],
                'keywords' => []
            ];
        }

        if ($isPattern) {
            $this->chineseRules[$entityType]['patterns'][] = $pattern;
        } else {
            $this->chineseRules[$entityType]['keywords'][] = $pattern;
        }
    }
}
