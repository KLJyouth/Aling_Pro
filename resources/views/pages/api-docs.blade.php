@extends("layouts.app")

@section("title", "API 文档")

@section("content")
<div class="container-fluid py-5">
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 mb-4">API 文档</h1>
            <p class="lead text-muted">了解如何使用 AlingAi 强大的 API 服务，快速集成 AI 能力到您的应用中。</p>
        </div>
    </div>
    
    <div class="row">
        <!-- 侧边导航 -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm sticky-top" style="top: 2rem;">
                <div class="card-body p-4">
                    <h4 class="h5 mb-3">API 文档目录</h4>
                    <nav id="navbar-api" class="navbar flex-column align-items-stretch p-0">
                        <nav class="nav nav-pills flex-column">
                            <a class="nav-link" href="#introduction">介绍</a>
                            <a class="nav-link" href="#authentication">认证与授权</a>
                            <a class="nav-link" href="#rate-limits">请求限制</a>
                            <a class="nav-link" href="#errors">错误处理</a>
                            <a class="nav-link" href="#endpoints">API 端点</a>
                            <nav class="nav nav-pills flex-column ms-3 my-2">
                                <a class="nav-link" href="#nlp-api">自然语言处理 API</a>
                                <a class="nav-link" href="#vision-api">计算机视觉 API</a>
                                <a class="nav-link" href="#ml-api">机器学习 API</a>
                            </nav>
                            <a class="nav-link" href="#sdks">SDK 与客户端库</a>
                            <a class="nav-link" href="#examples">示例代码</a>
                            <a class="nav-link" href="#webhooks">Webhooks</a>
                            <a class="nav-link" href="#changelog">更新日志</a>
                        </nav>
                    </nav>
                    <hr class="my-3">
                    <div class="d-grid gap-2">
                        <a href="{{ route("register") }}" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i> 注册获取 API 密钥
                        </a>
                        <a href="{{ route("support") }}" class="btn btn-outline-secondary">
                            <i class="fas fa-headset me-1"></i> 获取技术支持
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 主要内容 -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <!-- 介绍 -->
                    <section id="introduction" class="mb-5">
                        <h2 class="h3 mb-4">介绍</h2>
                        <p>AlingAi API 是一套功能强大的 RESTful API，提供自然语言处理、计算机视觉和机器学习等人工智能能力。通过我们的 API，您可以轻松地将 AI 功能集成到您的应用中，无需构建和维护复杂的 AI 基础设施。</p>
                        <p>我们的 API 设计遵循 RESTful 原则，使用标准的 HTTP 请求方法（GET、POST、PUT、DELETE）和状态码。所有的请求和响应都使用 JSON 格式，确保与各种编程语言和平台的兼容性。</p>
                        
                        <div class="card bg-light border-0 my-4">
                            <div class="card-body">
                                <h5 class="card-title">基本信息</h5>
                                <ul class="list-unstyled mb-0">
                                    <li><strong>基础 URL：</strong> <code>https://api.alingai.com/v1</code></li>
                                    <li><strong>响应格式：</strong> JSON</li>
                                    <li><strong>认证方式：</strong> API 密钥（通过 HTTP 头部 <code>X-API-Key</code>）</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h5>API 版本</h5>
                            <p>当前 API 版本为 v1。我们会在进行不兼容更改时发布新版本，并保持旧版本的兼容性一段时间。</p>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> 我们建议在 API 请求 URL 中明确指定版本号，以确保您的应用不会受到 API 更新的影响。
                            </div>
                        </div>
                    </section>
                    
                    <!-- 认证与授权 -->
                    <section id="authentication" class="mb-5">
                        <h2 class="h3 mb-4">认证与授权</h2>
                        <p>所有 API 请求都需要进行认证。我们使用 API 密钥进行认证，您可以在控制面板中创建和管理 API 密钥。</p>
                        
                        <div class="mt-4">
                            <h5>获取 API 密钥</h5>
                            <ol>
                                <li>登录您的 AlingAi 账户</li>
                                <li>进入"API 管理"页面</li>
                                <li>点击"创建 API 密钥"按钮</li>
                                <li>填写必要信息（名称、描述、权限等）</li>
                                <li>点击"创建"按钮</li>
                            </ol>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i> 请妥善保管您的 API 密钥，不要将其泄露给他人。如果您怀疑密钥已泄露，请立即在控制面板中撤销并重新生成。
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5>使用 API 密钥</h5>
                            <p>在发送 API 请求时，您需要在 HTTP 头部中包含您的 API 密钥：</p>
                            <div class="code-block bg-dark p-3 rounded">
                                <pre class="text-light mb-0"><code>X-API-Key: your_api_key_here</code></pre>
                            </div>
                            
                            <div class="mt-3">
                                <h6>示例请求（cURL）：</h6>
                                <div class="code-block bg-dark p-3 rounded">
                                    <pre class="text-light mb-0"><code>curl -X POST \\
  https://api.alingai.com/v1/nlp/sentiment \\
  -H "X-API-Key: your_api_key_here" \\
  -H "Content-Type: application/json" \\
  -d '{"text": "这是一段需要分析情感的文本"}'</code></pre>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5>API 密钥权限</h5>
                            <p>创建 API 密钥时，您可以指定该密钥的权限范围。这允许您为不同的应用或用途创建具有不同权限的密钥。</p>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>权限</th>
                                        <th>描述</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>nlp:read</td>
                                        <td>允许访问自然语言处理 API 的只读操作</td>
                                    </tr>
                                    <tr>
                                        <td>nlp:write</td>
                                        <td>允许访问自然语言处理 API 的写入操作</td>
                                    </tr>
                                    <tr>
                                        <td>vision:read</td>
                                        <td>允许访问计算机视觉 API 的只读操作</td>
                                    </tr>
                                    <tr>
                                        <td>vision:write</td>
                                        <td>允许访问计算机视觉 API 的写入操作</td>
                                    </tr>
                                    <tr>
                                        <td>ml:read</td>
                                        <td>允许访问机器学习 API 的只读操作</td>
                                    </tr>
                                    <tr>
                                        <td>ml:write</td>
                                        <td>允许访问机器学习 API 的写入操作</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    
                    <!-- 请求限制 -->
                    <section id="rate-limits" class="mb-5">
                        <h2 class="h3 mb-4">请求限制</h2>
                        <p>为了确保服务的稳定性和公平性，我们对 API 请求实施了速率限制。限制根据您的订阅计划而异。</p>
                        
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>订阅计划</th>
                                        <th>每秒请求数 (RPS)</th>
                                        <th>每天请求数</th>
                                        <th>并发请求数</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>免费</td>
                                        <td>2</td>
                                        <td>1,000</td>
                                        <td>1</td>
                                    </tr>
                                    <tr>
                                        <td>基础</td>
                                        <td>5</td>
                                        <td>10,000</td>
                                        <td>5</td>
                                    </tr>
                                    <tr>
                                        <td>专业</td>
                                        <td>20</td>
                                        <td>100,000</td>
                                        <td>20</td>
                                    </tr>
                                    <tr>
                                        <td>企业</td>
                                        <td>100+</td>
                                        <td>1,000,000+</td>
                                        <td>100+</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            <h5>限制响应</h5>
                            <p>当您超过速率限制时，API 将返回 <code>429 Too Many Requests</code> 状态码。响应头部将包含以下信息：</p>
                            <ul>
                                <li><code>X-RateLimit-Limit</code>：您的速率限制</li>
                                <li><code>X-RateLimit-Remaining</code>：当前周期内剩余的请求数</li>
                                <li><code>X-RateLimit-Reset</code>：速率限制重置的时间（Unix 时间戳）</li>
                            </ul>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> 我们建议实施指数退避算法来处理速率限制。当收到 429 响应时，等待一段时间后再重试，并逐渐增加等待时间。
                            </div>
                        </div>
                    </section>
                    <!-- 错误处理 -->
                    <section id="errors" class="mb-5">
                        <h2 class="h3 mb-4">错误处理</h2>
                        <p>当 API 请求失败时，我们会返回适当的 HTTP 状态码和详细的错误信息。错误响应的格式如下：</p>
                        
                        <div class="code-block bg-dark p-3 rounded">
                            <pre class="text-light mb-0"><code>{
  "error": {
    "code": "error_code",
    "message": "错误描述信息",
    "details": {
      // 可选的详细错误信息
    }
  }
}</code></pre>
                        </div>
                        
                        <div class="mt-4">
                            <h5>常见错误码</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>HTTP 状态码</th>
                                            <th>错误码</th>
                                            <th>描述</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>400</td>
                                            <td>bad_request</td>
                                            <td>请求格式不正确或参数无效</td>
                                        </tr>
                                        <tr>
                                            <td>401</td>
                                            <td>unauthorized</td>
                                            <td>未提供 API 密钥或 API 密钥无效</td>
                                        </tr>
                                        <tr>
                                            <td>403</td>
                                            <td>forbidden</td>
                                            <td>API 密钥没有足够的权限</td>
                                        </tr>
                                        <tr>
                                            <td>404</td>
                                            <td>not_found</td>
                                            <td>请求的资源不存在</td>
                                        </tr>
                                        <tr>
                                            <td>429</td>
                                            <td>rate_limit_exceeded</td>
                                            <td>超过 API 请求限制</td>
                                        </tr>
                                        <tr>
                                            <td>500</td>
                                            <td>internal_error</td>
                                            <td>服务器内部错误</td>
                                        </tr>
                                        <tr>
                                            <td>503</td>
                                            <td>service_unavailable</td>
                                            <td>服务暂时不可用</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5>处理错误</h5>
                            <p>在您的应用中，应该始终检查 API 响应的状态码，并适当处理错误。以下是一个错误处理的示例：</p>
                            
                            <div class="code-block bg-dark p-3 rounded">
                                <pre class="text-light mb-0"><code>try {
  const response = await fetch('https://api.alingai.com/v1/nlp/sentiment', {
    method: 'POST',
    headers: {
      'X-API-Key': 'your_api_key_here',
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ text: '这是一段需要分析情感的文本' })
  });
  
  if (!response.ok) {
    const errorData = await response.json();
    throw new Error(`API 错误: ${errorData.error.message}`);
  }
  
  const data = await response.json();
  // 处理成功响应
} catch (error) {
  console.error('请求失败:', error);
  // 处理错误
}</code></pre>
                            </div>
                        </div>
                    </section>
                    
                    <!-- API 端点 -->
                    <section id="endpoints" class="mb-5">
                        <h2 class="h3 mb-4">API 端点</h2>
                        <p>AlingAi API 提供多种端点，分为以下几个主要类别：</p>
                        
                        <!-- 自然语言处理 API -->
                        <section id="nlp-api" class="mt-4 mb-5">
                            <h3 class="h4 mb-3">自然语言处理 API</h3>
                            <p>自然语言处理 API 提供文本分析、情感分析、实体识别等功能。</p>
                            
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-success me-2">POST</span>
                                        <h5 class="mb-0">/nlp/sentiment</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">情感分析</h5>
                                    <p class="card-text">分析文本的情感倾向，返回积极、消极或中性的评分。</p>
                                    
                                    <h6 class="mt-3">请求参数</h6>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>参数名</th>
                                                    <th>类型</th>
                                                    <th>必填</th>
                                                    <th>描述</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>text</td>
                                                    <td>string</td>
                                                    <td>是</td>
                                                    <td>要分析的文本内容</td>
                                                </tr>
                                                <tr>
                                                    <td>language</td>
                                                    <td>string</td>
                                                    <td>否</td>
                                                    <td>文本语言，默认为自动检测</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <h6 class="mt-3">响应</h6>
                                    <div class="code-block bg-dark p-3 rounded">
                                        <pre class="text-light mb-0"><code>{
  "sentiment": "positive",
  "scores": {
    "positive": 0.85,
    "neutral": 0.12,
    "negative": 0.03
  },
  "language": "zh"
}</code></pre>
                                    </div>
                                    
                                    <h6 class="mt-3">示例请求</h6>
                                    <div class="code-block bg-dark p-3 rounded">
                                        <pre class="text-light mb-0"><code>curl -X POST \\
  https://api.alingai.com/v1/nlp/sentiment \\
  -H "X-API-Key: your_api_key_here" \\
  -H "Content-Type: application/json" \\
  -d '{"text": "我非常喜欢这个产品，使用体验很好！"}'</code></pre>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-success me-2">POST</span>
                                        <h5 class="mb-0">/nlp/entities</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">命名实体识别</h5>
                                    <p class="card-text">识别文本中的命名实体，如人名、地名、组织名等。</p>
                                    
                                    <h6 class="mt-3">请求参数</h6>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>参数名</th>
                                                    <th>类型</th>
                                                    <th>必填</th>
                                                    <th>描述</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>text</td>
                                                    <td>string</td>
                                                    <td>是</td>
                                                    <td>要分析的文本内容</td>
                                                </tr>
                                                <tr>
                                                    <td>language</td>
                                                    <td>string</td>
                                                    <td>否</td>
                                                    <td>文本语言，默认为自动检测</td>
                                                </tr>
                                                <tr>
                                                    <td>types</td>
                                                    <td>array</td>
                                                    <td>否</td>
                                                    <td>要识别的实体类型，默认为所有类型</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <h6 class="mt-3">响应</h6>
                                    <div class="code-block bg-dark p-3 rounded">
                                        <pre class="text-light mb-0"><code>{
  "entities": [
    {
      "text": "张明",
      "type": "PERSON",
      "start": 0,
      "end": 2,
      "confidence": 0.95
    },
    {
      "text": "北京",
      "type": "LOCATION",
      "start": 4,
      "end": 6,
      "confidence": 0.98
    },
    {
      "text": "清华大学",
      "type": "ORGANIZATION",
      "start": 11,
      "end": 15,
      "confidence": 0.97
    }
  ],
  "language": "zh"
}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </section>
                        
                        <!-- 计算机视觉 API -->
                        <section id="vision-api" class="mt-4 mb-5">
                            <h3 class="h4 mb-3">计算机视觉 API</h3>
                            <p>计算机视觉 API 提供图像识别、物体检测、人脸识别等功能。</p>
                            
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-success me-2">POST</span>
                                        <h5 class="mb-0">/vision/analyze</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">图像分析</h5>
                                    <p class="card-text">分析图像内容，识别物体、场景、颜色等信息。</p>
                                    
                                    <h6 class="mt-3">请求参数</h6>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>参数名</th>
                                                    <th>类型</th>
                                                    <th>必填</th>
                                                    <th>描述</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>image</td>
                                                    <td>string (base64) 或 URL</td>
                                                    <td>是</td>
                                                    <td>要分析的图像，可以是 base64 编码的图像数据或图像 URL</td>
                                                </tr>
                                                <tr>
                                                    <td>features</td>
                                                    <td>array</td>
                                                    <td>否</td>
                                                    <td>要分析的特征，如 "objects", "scenes", "colors" 等，默认为所有特征</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <h6 class="mt-3">响应</h6>
                                    <div class="code-block bg-dark p-3 rounded">
                                        <pre class="text-light mb-0"><code>{
  "objects": [
    {
      "name": "猫",
      "confidence": 0.98,
      "box": {
        "x": 10,
        "y": 20,
        "width": 200,
        "height": 150
      }
    },
    {
      "name": "沙发",
      "confidence": 0.95,
      "box": {
        "x": 50,
        "y": 180,
        "width": 300,
        "height": 120
      }
    }
  ],
  "scenes": [
    {
      "name": "室内",
      "confidence": 0.97
    },
    {
      "name": "客厅",
      "confidence": 0.92
    }
  ],
  "colors": [
    {
      "name": "白色",
      "hex": "#FFFFFF",
      "percentage": 0.45
    },
    {
      "name": "灰色",
      "hex": "#808080",
      "percentage": 0.30
    }
  ]
}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </section>
                        
                        <!-- 机器学习 API -->
                        <section id="ml-api" class="mt-4 mb-5">
                            <h3 class="h4 mb-3">机器学习 API</h3>
                            <p>机器学习 API 提供预测分析、异常检测、聚类分析等功能。</p>
                            
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-success me-2">POST</span>
                                        <h5 class="mb-0">/ml/predict</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">预测分析</h5>
                                    <p class="card-text">基于历史数据进行预测分析。</p>
                                    
                                    <h6 class="mt-3">请求参数</h6>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>参数名</th>
                                                    <th>类型</th>
                                                    <th>必填</th>
                                                    <th>描述</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>model_id</td>
                                                    <td>string</td>
                                                    <td>是</td>
                                                    <td>模型 ID</td>
                                                </tr>
                                                <tr>
                                                    <td>data</td>
                                                    <td>object</td>
                                                    <td>是</td>
                                                    <td>输入数据</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <h6 class="mt-3">响应</h6>
                                    <div class="code-block bg-dark p-3 rounded">
                                        <pre class="text-light mb-0"><code>{
  "prediction": {
    "value": 42.5,
    "confidence": 0.85
  },
  "model_info": {
    "id": "model_123",
    "name": "销售预测模型",
    "version": "1.0"
  }
}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </section>
                    
                    <!-- SDK 与客户端库 -->
                    <section id="sdks" class="mb-5">
                        <h2 class="h3 mb-4">SDK 与客户端库</h2>
                        <p>为了简化 API 的使用，我们提供了多种编程语言的 SDK：</p>
                        
                        <div class="row row-cols-1 row-cols-md-2 g-4">
                            <div class="col">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">Python SDK</h5>
                                        <p class="card-text">适用于 Python 3.6+ 的客户端库。</p>
                                        <div class="code-block bg-dark p-3 rounded">
                                            <pre class="text-light mb-0"><code>pip install alingai</code></pre>
                                        </div>
                                        <a href="#" class="btn btn-primary mt-3">查看文档</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">JavaScript SDK</h5>
                                        <p class="card-text">适用于 Node.js 和浏览器的客户端库。</p>
                                        <div class="code-block bg-dark p-3 rounded">
                                            <pre class="text-light mb-0"><code>npm install alingai</code></pre>
                                        </div>
                                        <a href="#" class="btn btn-primary mt-3">查看文档</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">Java SDK</h5>
                                        <p class="card-text">适用于 Java 8+ 的客户端库。</p>
                                        <div class="code-block bg-dark p-3 rounded">
                                            <pre class="text-light mb-0"><code>&lt;dependency&gt;
  &lt;groupId&gt;com.alingai&lt;/groupId&gt;
  &lt;artifactId&gt;alingai-java&lt;/artifactId&gt;
  &lt;version&gt;1.0.0&lt;/version&gt;
&lt;/dependency&gt;</code></pre>
                                        </div>
                                        <a href="#" class="btn btn-primary mt-3">查看文档</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">PHP SDK</h5>
                                        <p class="card-text">适用于 PHP 7.4+ 的客户端库。</p>
                                        <div class="code-block bg-dark p-3 rounded">
                                            <pre class="text-light mb-0"><code>composer require alingai/alingai-php</code></pre>
                                        </div>
                                        <a href="#" class="btn btn-primary mt-3">查看文档</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- 示例代码 -->
                    <section id="examples" class="mb-5">
                        <h2 class="h3 mb-4">示例代码</h2>
                        <p>以下是一些使用 AlingAi API 的示例代码：</p>
                        
                        <ul class="nav nav-tabs" id="exampleTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="python-tab" data-bs-toggle="tab" data-bs-target="#python" type="button" role="tab" aria-controls="python" aria-selected="true">Python</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="javascript-tab" data-bs-toggle="tab" data-bs-target="#javascript" type="button" role="tab" aria-controls="javascript" aria-selected="false">JavaScript</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="php-tab" data-bs-toggle="tab" data-bs-target="#php" type="button" role="tab" aria-controls="php" aria-selected="false">PHP</button>
                            </li>
                        </ul>
                        <div class="tab-content p-3 border border-top-0 rounded-bottom" id="exampleTabsContent">
                            <div class="tab-pane fade show active" id="python" role="tabpanel" aria-labelledby="python-tab">
                                <div class="code-block bg-dark p-3 rounded">
                                    <pre class="text-light mb-0"><code>import alingai

# 初始化客户端
client = alingai.Client(api_key="your_api_key_here")

# 情感分析
sentiment_result = client.nlp.sentiment(text="这是一段需要分析情感的文本")
print(f"情感: {sentiment_result.sentiment}")
print(f"积极分数: {sentiment_result.scores.positive}")

# 图像分析
with open("image.jpg", "rb") as image_file:
    image_data = image_file.read()
    vision_result = client.vision.analyze(
        image=image_data,
        features=["objects", "scenes"]
    )
    
for obj in vision_result.objects:
    print(f"检测到对象: {obj.name}，置信度: {obj.confidence}")</code></pre>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="javascript" role="tabpanel" aria-labelledby="javascript-tab">
                                <div class="code-block bg-dark p-3 rounded">
                                    <pre class="text-light mb-0"><code>const AlingAi = require('alingai');

// 初始化客户端
const client = new AlingAi.Client('your_api_key_here');

// 情感分析
client.nlp.sentiment({ text: '这是一段需要分析情感的文本' })
  .then(result => {
    console.log(`情感: ${result.sentiment}`);
    console.log(`积极分数: ${result.scores.positive}`);
  })
  .catch(error => {
    console.error('请求失败:', error);
  });

// 图像分析
const fs = require('fs');
const imageData = fs.readFileSync('image.jpg');

client.vision.analyze({
  image: imageData.toString('base64'),
  features: ['objects', 'scenes']
})
  .then(result => {
    result.objects.forEach(obj => {
      console.log(`检测到对象: ${obj.name}，置信度: ${obj.confidence}`);
    });
  })
  .catch(error => {
    console.error('请求失败:', error);
  });</code></pre>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="php" role="tabpanel" aria-labelledby="php-tab">
                                <div class="code-block bg-dark p-3 rounded">
                                    <pre class="text-light mb-0"><code>&lt;?php

require_once 'vendor/autoload.php';

// 初始化客户端
$client = new AlingAi\Client('your_api_key_here');

// 情感分析
try {
    $sentimentResult = $client->nlp->sentiment([
        'text' => '这是一段需要分析情感的文本'
    ]);
    
    echo "情感: " . $sentimentResult->sentiment . PHP_EOL;
    echo "积极分数: " . $sentimentResult->scores->positive . PHP_EOL;
} catch (Exception $e) {
    echo "请求失败: " . $e->getMessage() . PHP_EOL;
}

// 图像分析
try {
    $imageData = file_get_contents('image.jpg');
    
    $visionResult = $client->vision->analyze([
        'image' => base64_encode($imageData),
        'features' => ['objects', 'scenes']
    ]);
    
    foreach ($visionResult->objects as $obj) {
        echo "检测到对象: " . $obj->name . "，置信度: " . $obj->confidence . PHP_EOL;
    }
} catch (Exception $e) {
    echo "请求失败: " . $e->getMessage() . PHP_EOL;
}
?></code></pre>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Webhooks -->
                    <section id="webhooks" class="mb-5">
                        <h2 class="h3 mb-4">Webhooks</h2>
                        <p>Webhooks 允许您接收异步操作的通知。当长时间运行的操作完成时，我们会向您指定的 URL 发送 HTTP POST 请求。</p>
                        
                        <div class="mt-4">
                            <h5>配置 Webhook</h5>
                            <p>您可以在控制面板的 API 设置中配置 Webhook URL。您还可以为不同类型的事件配置不同的 URL。</p>
                        </div>
                        
                        <div class="mt-4">
                            <h5>Webhook 请求格式</h5>
                            <div class="code-block bg-dark p-3 rounded">
                                <pre class="text-light mb-0"><code>{
  "event": "job.completed",
  "job_id": "job_123",
  "status": "success",
  "result": {
    // 操作结果
  },
  "created_at": "2023-06-01T12:34:56Z"
}</code></pre>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5>验证 Webhook</h5>
                            <p>为了确保 Webhook 请求的真实性，我们会在请求头中包含签名：</p>
                            <div class="code-block bg-dark p-3 rounded">
                                <pre class="text-light mb-0"><code>X-AlingAi-Signature: sha256=...</code></pre>
                            </div>
                            <p>您可以使用您的 Webhook 密钥验证此签名：</p>
                            <div class="code-block bg-dark p-3 rounded">
                                <pre class="text-light mb-0"><code>const crypto = require('crypto');

function verifyWebhook(payload, signature, secret) {
  const expectedSignature = crypto
    .createHmac('sha256', secret)
    .update(payload)
    .digest('hex');
    
  return signature === `sha256=${expectedSignature}`;
}</code></pre>
                            </div>
                        </div>
                    </section>
                    
                    <!-- 更新日志 -->
                    <section id="changelog">
                        <h2 class="h3 mb-4">更新日志</h2>
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h3 class="h5 mb-0">v1.2.0 (2023-06-01)</h3>
                                    <p class="text-muted mb-2">新功能和改进</p>
                                    <ul>
                                        <li>添加了批量处理 API</li>
                                        <li>改进了图像分析算法</li>
                                        <li>增加了对更多语言的支持</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h3 class="h5 mb-0">v1.1.0 (2023-03-15)</h3>
                                    <p class="text-muted mb-2">新功能和改进</p>
                                    <ul>
                                        <li>添加了 Webhooks 支持</li>
                                        <li>改进了 API 响应速度</li>
                                        <li>修复了多个 bug</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h3 class="h5 mb-0">v1.0.0 (2023-01-01)</h3>
                                    <p class="text-muted mb-2">初始版本</p>
                                    <ul>
                                        <li>发布了自然语言处理 API</li>
                                        <li>发布了计算机视觉 API</li>
                                        <li>发布了机器学习 API</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .code-block {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 0.875rem;
    }
    
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline:before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #e9ecef;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 30px;
    }
    
    .timeline-marker {
        position: absolute;
        left: -39px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background-color: #0d6efd;
        border: 4px solid #fff;
        box-shadow: 0 0 0 2px #e9ecef;
    }
    
    .sticky-top {
        z-index: 1020;
    }
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 监听滚动事件，更新导航高亮
    window.addEventListener("scroll", function() {
        const sections = document.querySelectorAll("section[id]");
        let currentSection = "";
        
        sections.forEach(function(section) {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            
            if (window.pageYOffset >= sectionTop - 100 && 
                window.pageYOffset < sectionTop + sectionHeight - 100) {
                currentSection = section.getAttribute("id");
            }
        });
        
        document.querySelectorAll("#navbar-api .nav-link").forEach(function(link) {
            link.classList.remove("active");
            if (link.getAttribute("href") === "#" + currentSection) {
                link.classList.add("active");
            }
        });
    });
});
</script>
@endsection
