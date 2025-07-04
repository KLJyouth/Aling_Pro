# AlingAi Pro API参考文档

## 概述

AlingAi Pro提供了一系列强大的AI能力API，包括语音处理、计算机视觉、自然语言处理和知识图谱四大核心模块。本文档详细介绍了各个API的使用方法、参数和返回值。

## 目录

1. [语音处理API](#语音处理api)
   - [语音识别](#语音识别)
   - [语音合成](#语音合成)
   - [声纹识别](#声纹识别)
2. [计算机视觉API](#计算机视觉api)
   - [图像识别](#图像识别)
   - [人脸识别](#人脸识别)
   - [物体检测](#物体检测)
   - [OCR文字识别](#ocr文字识别)
3. [自然语言处理API](#自然语言处理api)
   - [文本分析](#文本分析)
   - [文本分类](#文本分类)
   - [情感分析](#情感分析)
   - [文本生成](#文本生成)
4. [知识图谱API](#知识图谱api)
   - [实体查询](#实体查询)
   - [关系查询](#关系查询)
   - [知识推理](#知识推理)

## 语音处理API

### 语音识别

将语音转换为文本。

**请求**

```http
POST /api/ai/speech/recognize
Content-Type: multipart/form-data
```

**参数**

| 参数名 | 类型 | 必选 | 描述 |
|-------|------|------|------|
| audio_file | File | 是 | 音频文件，支持wav、mp3、flac等格式 |
| language | String | 否 | 语言代码，默认为"zh-CN" |
| model | String | 否 | 识别模型，可选值："standard"、"enhanced"，默认为"standard" |
| sample_rate | Integer | 否 | 采样率，默认为16000 |

**响应**

```json
{
  "success": true,
  "text": "识别出的文本内容",
  "confidence": 0.95,
  "duration": 3.5,
  "language": "zh-CN",
  "segments": [
    {
      "text": "第一段文本",
      "start": 0.0,
      "end": 1.2,
      "confidence": 0.96
    },
    {
      "text": "第二段文本",
      "start": 1.3,
      "end": 2.5,
      "confidence": 0.94
    }
  ]
}
```

### 语音合成

将文本转换为语音。

**请求**

```http
POST /api/ai/speech/synthesize
Content-Type: application/json
```

**参数**

| 参数名 | 类型 | 必选 | 描述 |
|-------|------|------|------|
| text | String | 是 | 要合成的文本内容 |
| voice | String | 否 | 声音类型，可选值："female_1"、"female_2"、"male_1"、"male_2"，默认为"female_1" |
| speed | Float | 否 | 语速，范围0.5-2.0，默认为1.0 |
| format | String | 否 | 输出格式，可选值："wav"、"mp3"，默认为"mp3" |
| sample_rate | Integer | 否 | 采样率，默认为16000 |

**响应**

```
二进制音频数据，Content-Type为audio/wav或audio/mpeg
```

### 声纹识别

识别和验证说话人身份。

**请求**

```http
POST /api/ai/speech/voice-identify
Content-Type: multipart/form-data
```

**参数**

| 参数名 | 类型 | 必选 | 描述 |
|-------|------|------|------|
| audio_file | File | 是 | 音频文件，支持wav、mp3、flac等格式 |
| speaker_id | String | 否 | 说话人ID，用于验证模式 |
| mode | String | 否 | 模式，可选值："enroll"(注册)、"verify"(验证)、"identify"(识别)，默认为"identify" |

**响应**

```json
{
  "success": true,
  "mode": "identify",
  "results": [
    {
      "speaker_id": "speaker_12345",
      "confidence": 0.92,
      "name": "用户名称"
    },
    {
      "speaker_id": "speaker_67890",
      "confidence": 0.45,
      "name": "其他用户"
    }
  ]
}
```

## 计算机视觉API

### 图像识别

识别图像内容，包括场景分类、物体识别等。

**请求**

```http
POST /api/ai/cv/recognize
Content-Type: multipart/form-data
```

**参数**

| 参数名 | 类型 | 必选 | 描述 |
|-------|------|------|------|
| image_file | File | 是 | 图像文件，支持jpg、png、bmp等格式 |
| detection_type | String | 否 | 检测类型，可选值："scene"、"objects"、"all"，默认为"all" |
| confidence_threshold | Float | 否 | 置信度阈值，范围0-1，默认为0.6 |

**响应**

```json
{
  "success": true,
  "scene": {
    "label": "办公室",
    "confidence": 0.95
  },
  "objects": [
    {
      "label": "笔记本电脑",
      "confidence": 0.96,
      "bounding_box": [10, 20, 300, 250]
    },
    {
      "label": "咖啡杯",
      "confidence": 0.88,
      "bounding_box": [320, 150, 380, 210]
    }
  ]
}
```

### 人脸识别

检测、识别和分析人脸。

**请求**

```http
POST /api/ai/cv/face-recognize
Content-Type: multipart/form-data
```

**参数**

| 参数名 | 类型 | 必选 | 描述 |
|-------|------|------|------|
| image_file | File | 是 | 图像文件，支持jpg、png、bmp等格式 |
| mode | String | 否 | 模式，可选值："detect"(检测)、"recognize"(识别)、"analyze"(分析)，默认为"detect" |
| include_features | Boolean | 否 | 是否包含面部特征点，默认为false |
| include_attributes | Boolean | 否 | 是否包含面部属性分析，默认为false |

**响应**

```json
{
  "success": true,
  "faces": [
    {
      "bounding_box": [100, 50, 200, 150],
      "confidence": 0.98,
      "person_id": "person_12345",
      "name": "张三",
      "attributes": {
        "age": 28,
        "gender": "male",
        "emotion": "happy",
        "glasses": true
      },
      "features": {
        "landmarks": [
          {"type": "left_eye", "position": [120, 80]},
          {"type": "right_eye", "position": [170, 80]},
          {"type": "nose", "position": [145, 100]},
          {"type": "mouth_left", "position": [125, 130]},
          {"type": "mouth_right", "position": [165, 130]}
        ]
      }
    }
  ]
}
```

### 物体检测

检测和定位图像中的物体。

**请求**

```http
POST /api/ai/cv/detect-objects
Content-Type: multipart/form-data
```

**参数**

| 参数名 | 类型 | 必选 | 描述 |
|-------|------|------|------|
| image_file | File | 是 | 图像文件，支持jpg、png、bmp等格式 |
| confidence_threshold | Float | 否 | 置信度阈值，范围0-1，默认为0.5 |
| include_mask | Boolean | 否 | 是否包含分割蒙版，默认为false |
| max_detections | Integer | 否 | 最大检测数量，默认为20 |

**响应**

```json
{
  "success": true,
  "objects": [
    {
      "label": "人",
      "confidence": 0.96,
      "bounding_box": [10, 20, 300, 500],
      "mask": "base64编码的二值图像"
    },
    {
      "label": "汽车",
      "confidence": 0.92,
      "bounding_box": [400, 100, 800, 350],
      "mask": "base64编码的二值图像"
    }
  ]
}
```

### OCR文字识别

识别图像中的文字内容。

**请求**

```http
POST /api/ai/cv/ocr
Content-Type: multipart/form-data
```

**参数**

| 参数名 | 类型 | 必选 | 描述 |
|-------|------|------|------|
| image_file | File | 是 | 图像文件，支持jpg、png、bmp等格式 |
| language | String | 否 | 语言，可选值："auto"、"zh-CN"、"en-US"等，默认为"auto" |
| detect_orientation | Boolean | 否 | 是否检测文字方向，默认为true |
| with_layout_analysis | Boolean | 否 | 是否进行版面分析，默认为false |

**响应**

```json
{
  "success": true,
  "language": "zh-CN",
  "orientation": 0,
  "text": "完整识别出的文本内容",
  "blocks": [
    {
      "type": "text",
      "content": "第一段文本",
      "confidence": 0.98,
      "bounding_box": [10, 20, 200, 50],
      "words": [
        {
          "text": "第一",
          "confidence": 0.99,
          "bounding_box": [10, 20, 60, 50]
        },
        {
          "text": "段文本",
          "confidence": 0.97,
          "bounding_box": [65, 20, 200, 50]
        }
      ]
    },
    {
      "type": "table",
      "content": "表格内容",
      "confidence": 0.95,
      "bounding_box": [10, 60, 500, 200],
      "cells": [
        {
          "text": "单元格1",
          "row": 0,
          "col": 0,
          "bounding_box": [10, 60, 100, 100]
        },
        {
          "text": "单元格2",
          "row": 0,
          "col": 1,
          "bounding_box": [105, 60, 200, 100]
        }
      ]
    }
  ]
}
```

## 自然语言处理API

### 文本分析

分析文本内容，提取关键信息。

**请求**

```http
POST /api/ai/nlp/analyze
Content-Type: application/json
```

**参数**

| 参数名 | 类型 | 必选 | 描述 |
|-------|------|------|------|
| text | String | 是 | 要分析的文本内容 |
| analysis_types | Array | 是 | 分析类型，可包含："sentiment"、"entities"、"keywords"、"summary"、"categories" |
| language | String | 否 | 语言代码，默认为"zh-CN" |

**响应**

```json
{
  "success": true,
  "sentiment": {
    "polarity": "positive",
    "score": 0.85
  },
  "entities": [
    {
      "text": "北京",
      "type": "location",
      "confidence": 0.92,
      "start": 5,
      "end": 7
    },
    {
      "text": "张三",
      "type": "person",
      "confidence": 0.88,
      "start": 10,
      "end": 12
    }
  ],
  "keywords": ["人工智能", "机器学习", "深度学习"],
  "summary": "这是原文的摘要内容",
  "categories": [
    {
      "name": "科技",
      "confidence": 0.95
    },
    {
      "name": "教育",
      "confidence": 0.45
    }
  ]
}
```

### 文本分类

将文本分类到预定义的类别。

**请求**

```http
POST /api/ai/nlp/classify
Content-Type: application/json
```

**参数**

| 参数名 | 类型 | 必选 | 描述 |
|-------|------|------|------|
| text | String | 是 | 要分类的文本内容 |
| taxonomy | String | 否 | 分类体系，可选值："general"、"news"、"product"，默认为"general" |
| top_k | Integer | 否 | 返回前k个分类结果，默认为3 |

**响应**

```json
{
  "success": true,
  "taxonomy": "general",
  "categories": [
    {
      "name": "科技",
      "confidence": 0.92
    },
    {
      "name": "教育",
      "confidence": 0.65
    },
    {
      "name": "商业",
      "confidence": 0.43
    }
  ]
}
```

### 情感分析

分析文本的情感倾向。

**请求**

```http
POST /api/ai/nlp/sentiment
Content-Type: application/json
```

**参数**

| 参数名 | 类型 | 必选 | 描述 |
|-------|------|------|------|
| text | String | 是 | 要分析的文本内容 |
| mode | String | 否 | 分析模式，可选值："basic"、"detailed"，默认为"basic" |

**响应**

```json
{
  "success": true,
  "mode": "detailed",
  "overall": {
    "polarity": "positive",
    "score": 0.78
  },
  "aspects": [
    {
      "text": "服务",
      "polarity": "positive",
      "score": 0.92
    },
    {
      "text": "价格",
      "polarity": "negative",
      "score": 0.65
    }
  ],
  "emotions": {
    "happiness": 0.75,
    "anger": 0.05,
    "sadness": 0.10,
    "fear": 0.03,
    "surprise": 0.07
  }
}
```

### 文本生成

基于给定条件生成文本内容。

**请求**

```http
POST /api/ai/nlp/generate
Content-Type: application/json
```

**参数**

| 参数名 | 类型 | 必选 | 描述 |
|-------|------|------|------|
| template | String | 是 | 模板类型，如"article"、"summary"、"dialogue"、"image_description" |
| data | Object | 是 | 生成所需的结构化数据 |
| language | String | 否 | 语言代码，默认为"zh-CN" |
| style | String | 否 | 文本风格，可选值："formal"、"casual"、"detailed"、"concise"，默认为"formal" |
| max_length | Integer | 否 | 生成文本的最大长度，默认为500 |

**响应**

```json
{
  "success": true,
  "text": "生成的文本内容",
  "metadata": {
    "template": "article",
    "style": "formal",
    "length": 320,
    "keywords": ["关键词1", "关键词2"]
  }
}
```

## 知识图谱API

### 实体查询

查询知识图谱中的实体信息。

**请求**

```http
POST /api/ai/knowledge-graph/query
Content-Type: application/json
```

**参数**

| 参数名 | 类型 | 必选 | 描述 |
|-------|------|------|------|
| entity | String | 是 | 实体名称或ID |
| query_depth | Integer | 否 | 查询深度，默认为1 |
| relationship_types | Array | 否 | 关系类型过滤，如["is_a", "part_of"] |
| include_attributes | Boolean | 否 | 是否包含属性信息，默认为true |

**响应**

```json
{
  "success": true,
  "entity_info": {
    "id": "entity_12345",
    "name": "人工智能",
    "description": "人工智能是计算机科学的一个分支，致力于创造能够模拟人类智能的机器",
    "categories": ["计算机科学", "技术"]
  },
  "relationships": {
    "is_a": ["技术", "研究领域"],
    "part_of": [],
    "has_part": ["机器学习", "深度学习", "自然语言处理", "计算机视觉"],
    "related_to": ["大数据", "云计算", "物联网"]
  },
  "attributes": {
    "创始时间": "1956年",
    "发展阶段": "快速发展期",
    "应用领域": ["医疗", "金融", "教育", "制造业"]
  }
}
```

### 关系查询

查询知识图谱中两个实体之间的关系。

**请求**

```http
POST /api/ai/knowledge-graph/relationship
Content-Type: application/json
```

**参数**

| 参数名 | 类型 | 必选 | 描述 |
|-------|------|------|------|
| source_entity | String | 是 | 源实体名称或ID |
| target_entity | String | 是 | 目标实体名称或ID |
| max_path_length | Integer | 否 | 最大路径长度，默认为3 |
| limit | Integer | 否 | 返回路径数量限制，默认为5 |

**响应**

```json
{
  "success": true,
  "source": {
    "id": "entity_12345",
    "name": "深度学习"
  },
  "target": {
    "id": "entity_67890",
    "name": "图像识别"
  },
  "direct_relationships": [
    {
      "type": "used_for",
      "confidence": 0.95
    }
  ],
  "paths": [
    {
      "path": [
        {
          "entity": "深度学习",
          "id": "entity_12345"
        },
        {
          "relationship": "is_a",
          "direction": "outgoing"
        },
        {
          "entity": "机器学习",
          "id": "entity_24680"
        },
        {
          "relationship": "used_for",
          "direction": "outgoing"
        },
        {
          "entity": "图像识别",
          "id": "entity_67890"
        }
      ],
      "confidence": 0.85
    }
  ]
}
```

### 知识推理

基于知识图谱进行逻辑推理。

**请求**

```http
POST /api/ai/knowledge-graph/reason
Content-Type: application/json
```

**参数**

| 参数名 | 类型 | 必选 | 描述 |
|-------|------|------|------|
| query | String | 是 | 推理查询语句 |
| context_entities | Array | 否 | 上下文实体列表 |
| reasoning_depth | Integer | 否 | 推理深度，默认为3 |
| include_explanation | Boolean | 否 | 是否包含推理解释，默认为true |

**响应**

```json
{
  "success": true,
  "result": {
    "answer": "是的",
    "confidence": 0.87,
    "facts_used": [
      {
        "entity": "深度学习",
        "relationship": "is_a",
        "target": "机器学习",
        "confidence": 0.98
      },
      {
        "entity": "机器学习",
        "relationship": "is_part_of",
        "target": "人工智能",
        "confidence": 0.95
      }
    ]
  },
  "explanation": {
    "steps": [
      "深度学习是机器学习的一种",
      "机器学习是人工智能的一部分",
      "因此，深度学习是人工智能的一部分"
    ],
    "reasoning_type": "transitive"
  }
}
```

## 错误处理

所有API在发生错误时，将返回相应的HTTP状态码和错误信息：

```json
{
  "success": false,
  "error": {
    "code": "invalid_parameter",
    "message": "参数错误的具体描述",
    "details": {
      "parameter": "出错的参数名",
      "reason": "参数错误的原因"
    }
  }
}
```

常见错误代码：

| 错误代码 | 描述 |
|---------|------|
| invalid_parameter | 参数错误 |
| missing_parameter | 缺少必要参数 |
| unsupported_format | 不支持的格式 |
| resource_not_found | 资源不存在 |
| quota_exceeded | 超出配额限制 |
| internal_error | 内部服务错误 |

## API限制

- 请求频率限制：每分钟100次请求
- 文件大小限制：音频文件不超过10MB，图像文件不超过5MB
- 文本长度限制：单次请求不超过5000个字符
- 并发请求限制：每个API密钥最多10个并发请求

## 版本信息

- API版本：v1.0
- 文档更新日期：2025-XX-XX
