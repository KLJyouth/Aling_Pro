@extends("layouts.app")

@section("title", "API �ĵ�")

@section("content")
<div class="container-fluid py-5">
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 mb-4">API �ĵ�</h1>
            <p class="lead text-muted">�˽����ʹ�� AlingAi ǿ��� API ���񣬿��ټ��� AI ����������Ӧ���С�</p>
        </div>
    </div>
    
    <div class="row">
        <!-- ��ߵ��� -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm sticky-top" style="top: 2rem;">
                <div class="card-body p-4">
                    <h4 class="h5 mb-3">API �ĵ�Ŀ¼</h4>
                    <nav id="navbar-api" class="navbar flex-column align-items-stretch p-0">
                        <nav class="nav nav-pills flex-column">
                            <a class="nav-link" href="#introduction">����</a>
                            <a class="nav-link" href="#authentication">��֤����Ȩ</a>
                            <a class="nav-link" href="#rate-limits">��������</a>
                            <a class="nav-link" href="#errors">������</a>
                            <a class="nav-link" href="#endpoints">API �˵�</a>
                            <nav class="nav nav-pills flex-column ms-3 my-2">
                                <a class="nav-link" href="#nlp-api">��Ȼ���Դ��� API</a>
                                <a class="nav-link" href="#vision-api">������Ӿ� API</a>
                                <a class="nav-link" href="#ml-api">����ѧϰ API</a>
                            </nav>
                            <a class="nav-link" href="#sdks">SDK ��ͻ��˿�</a>
                            <a class="nav-link" href="#examples">ʾ������</a>
                            <a class="nav-link" href="#webhooks">Webhooks</a>
                            <a class="nav-link" href="#changelog">������־</a>
                        </nav>
                    </nav>
                    <hr class="my-3">
                    <div class="d-grid gap-2">
                        <a href="{{ route("register") }}" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i> ע���ȡ API ��Կ
                        </a>
                        <a href="{{ route("support") }}" class="btn btn-outline-secondary">
                            <i class="fas fa-headset me-1"></i> ��ȡ����֧��
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ��Ҫ���� -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <!-- ���� -->
                    <section id="introduction" class="mb-5">
                        <h2 class="h3 mb-4">����</h2>
                        <p>AlingAi API ��һ�׹���ǿ��� RESTful API���ṩ��Ȼ���Դ���������Ӿ��ͻ���ѧϰ���˹�����������ͨ�����ǵ� API�����������ɵؽ� AI ���ܼ��ɵ�����Ӧ���У����蹹����ά�����ӵ� AI ������ʩ��</p>
                        <p>���ǵ� API �����ѭ RESTful ԭ��ʹ�ñ�׼�� HTTP ���󷽷���GET��POST��PUT��DELETE����״̬�롣���е��������Ӧ��ʹ�� JSON ��ʽ��ȷ������ֱ�����Ժ�ƽ̨�ļ����ԡ�</p>
                        
                        <div class="card bg-light border-0 my-4">
                            <div class="card-body">
                                <h5 class="card-title">������Ϣ</h5>
                                <ul class="list-unstyled mb-0">
                                    <li><strong>���� URL��</strong> <code>https://api.alingai.com/v1</code></li>
                                    <li><strong>��Ӧ��ʽ��</strong> JSON</li>
                                    <li><strong>��֤��ʽ��</strong> API ��Կ��ͨ�� HTTP ͷ�� <code>X-API-Key</code>��</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h5>API �汾</h5>
                            <p>��ǰ API �汾Ϊ v1�����ǻ��ڽ��в����ݸ���ʱ�����°汾�������־ɰ汾�ļ�����һ��ʱ�䡣</p>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> ���ǽ����� API ���� URL ����ȷָ���汾�ţ���ȷ������Ӧ�ò����ܵ� API ���µ�Ӱ�졣
                            </div>
                        </div>
                    </section>
                    
                    <!-- ��֤����Ȩ -->
                    <section id="authentication" class="mb-5">
                        <h2 class="h3 mb-4">��֤����Ȩ</h2>
                        <p>���� API ������Ҫ������֤������ʹ�� API ��Կ������֤���������ڿ�������д����͹��� API ��Կ��</p>
                        
                        <div class="mt-4">
                            <h5>��ȡ API ��Կ</h5>
                            <ol>
                                <li>��¼���� AlingAi �˻�</li>
                                <li>����"API ����"ҳ��</li>
                                <li>���"���� API ��Կ"��ť</li>
                                <li>��д��Ҫ��Ϣ�����ơ�������Ȩ�޵ȣ�</li>
                                <li>���"����"��ť</li>
                            </ol>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i> �����Ʊ������� API ��Կ����Ҫ����й¶�����ˡ������������Կ��й¶���������ڿ�������г������������ɡ�
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5>ʹ�� API ��Կ</h5>
                            <p>�ڷ��� API ����ʱ������Ҫ�� HTTP ͷ���а������� API ��Կ��</p>
                            <div class="code-block bg-dark p-3 rounded">
                                <pre class="text-light mb-0"><code>X-API-Key: your_api_key_here</code></pre>
                            </div>
                            
                            <div class="mt-3">
                                <h6>ʾ������cURL����</h6>
                                <div class="code-block bg-dark p-3 rounded">
                                    <pre class="text-light mb-0"><code>curl -X POST \\
  https://api.alingai.com/v1/nlp/sentiment \\
  -H "X-API-Key: your_api_key_here" \\
  -H "Content-Type: application/json" \\
  -d '{"text": "����һ����Ҫ������е��ı�"}'</code></pre>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5>API ��ԿȨ��</h5>
                            <p>���� API ��Կʱ��������ָ������Կ��Ȩ�޷�Χ����������Ϊ��ͬ��Ӧ�û���;�������в�ͬȨ�޵���Կ��</p>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Ȩ��</th>
                                        <th>����</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>nlp:read</td>
                                        <td>���������Ȼ���Դ��� API ��ֻ������</td>
                                    </tr>
                                    <tr>
                                        <td>nlp:write</td>
                                        <td>���������Ȼ���Դ��� API ��д�����</td>
                                    </tr>
                                    <tr>
                                        <td>vision:read</td>
                                        <td>������ʼ�����Ӿ� API ��ֻ������</td>
                                    </tr>
                                    <tr>
                                        <td>vision:write</td>
                                        <td>������ʼ�����Ӿ� API ��д�����</td>
                                    </tr>
                                    <tr>
                                        <td>ml:read</td>
                                        <td>������ʻ���ѧϰ API ��ֻ������</td>
                                    </tr>
                                    <tr>
                                        <td>ml:write</td>
                                        <td>������ʻ���ѧϰ API ��д�����</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    
                    <!-- �������� -->
                    <section id="rate-limits" class="mb-5">
                        <h2 class="h3 mb-4">��������</h2>
                        <p>Ϊ��ȷ��������ȶ��Ժ͹�ƽ�ԣ����Ƕ� API ����ʵʩ���������ơ����Ƹ������Ķ��ļƻ����졣</p>
                        
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>���ļƻ�</th>
                                        <th>ÿ�������� (RPS)</th>
                                        <th>ÿ��������</th>
                                        <th>����������</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>���</td>
                                        <td>2</td>
                                        <td>1,000</td>
                                        <td>1</td>
                                    </tr>
                                    <tr>
                                        <td>����</td>
                                        <td>5</td>
                                        <td>10,000</td>
                                        <td>5</td>
                                    </tr>
                                    <tr>
                                        <td>רҵ</td>
                                        <td>20</td>
                                        <td>100,000</td>
                                        <td>20</td>
                                    </tr>
                                    <tr>
                                        <td>��ҵ</td>
                                        <td>100+</td>
                                        <td>1,000,000+</td>
                                        <td>100+</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            <h5>������Ӧ</h5>
                            <p>����������������ʱ��API ������ <code>429 Too Many Requests</code> ״̬�롣��Ӧͷ��������������Ϣ��</p>
                            <ul>
                                <li><code>X-RateLimit-Limit</code>��������������</li>
                                <li><code>X-RateLimit-Remaining</code>����ǰ������ʣ���������</li>
                                <li><code>X-RateLimit-Reset</code>�������������õ�ʱ�䣨Unix ʱ�����</li>
                            </ul>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> ���ǽ���ʵʩָ���˱��㷨�������������ơ����յ� 429 ��Ӧʱ���ȴ�һ��ʱ��������ԣ��������ӵȴ�ʱ�䡣
                            </div>
                        </div>
                    </section>
                    <!-- ������ -->
                    <section id="errors" class="mb-5">
                        <h2 class="h3 mb-4">������</h2>
                        <p>�� API ����ʧ��ʱ�����ǻ᷵���ʵ��� HTTP ״̬�����ϸ�Ĵ�����Ϣ��������Ӧ�ĸ�ʽ���£�</p>
                        
                        <div class="code-block bg-dark p-3 rounded">
                            <pre class="text-light mb-0"><code>{
  "error": {
    "code": "error_code",
    "message": "����������Ϣ",
    "details": {
      // ��ѡ����ϸ������Ϣ
    }
  }
}</code></pre>
                        </div>
                        
                        <div class="mt-4">
                            <h5>����������</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>HTTP ״̬��</th>
                                            <th>������</th>
                                            <th>����</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>400</td>
                                            <td>bad_request</td>
                                            <td>�����ʽ����ȷ�������Ч</td>
                                        </tr>
                                        <tr>
                                            <td>401</td>
                                            <td>unauthorized</td>
                                            <td>δ�ṩ API ��Կ�� API ��Կ��Ч</td>
                                        </tr>
                                        <tr>
                                            <td>403</td>
                                            <td>forbidden</td>
                                            <td>API ��Կû���㹻��Ȩ��</td>
                                        </tr>
                                        <tr>
                                            <td>404</td>
                                            <td>not_found</td>
                                            <td>�������Դ������</td>
                                        </tr>
                                        <tr>
                                            <td>429</td>
                                            <td>rate_limit_exceeded</td>
                                            <td>���� API ��������</td>
                                        </tr>
                                        <tr>
                                            <td>500</td>
                                            <td>internal_error</td>
                                            <td>�������ڲ�����</td>
                                        </tr>
                                        <tr>
                                            <td>503</td>
                                            <td>service_unavailable</td>
                                            <td>������ʱ������</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5>�������</h5>
                            <p>������Ӧ���У�Ӧ��ʼ�ռ�� API ��Ӧ��״̬�룬���ʵ��������������һ���������ʾ����</p>
                            
                            <div class="code-block bg-dark p-3 rounded">
                                <pre class="text-light mb-0"><code>try {
  const response = await fetch('https://api.alingai.com/v1/nlp/sentiment', {
    method: 'POST',
    headers: {
      'X-API-Key': 'your_api_key_here',
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ text: '����һ����Ҫ������е��ı�' })
  });
  
  if (!response.ok) {
    const errorData = await response.json();
    throw new Error(`API ����: ${errorData.error.message}`);
  }
  
  const data = await response.json();
  // ����ɹ���Ӧ
} catch (error) {
  console.error('����ʧ��:', error);
  // �������
}</code></pre>
                            </div>
                        </div>
                    </section>
                    
                    <!-- API �˵� -->
                    <section id="endpoints" class="mb-5">
                        <h2 class="h3 mb-4">API �˵�</h2>
                        <p>AlingAi API �ṩ���ֶ˵㣬��Ϊ���¼�����Ҫ���</p>
                        
                        <!-- ��Ȼ���Դ��� API -->
                        <section id="nlp-api" class="mt-4 mb-5">
                            <h3 class="h4 mb-3">��Ȼ���Դ��� API</h3>
                            <p>��Ȼ���Դ��� API �ṩ�ı���������з�����ʵ��ʶ��ȹ��ܡ�</p>
                            
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-success me-2">POST</span>
                                        <h5 class="mb-0">/nlp/sentiment</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">��з���</h5>
                                    <p class="card-text">�����ı���������򣬷��ػ��������������Ե����֡�</p>
                                    
                                    <h6 class="mt-3">�������</h6>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>������</th>
                                                    <th>����</th>
                                                    <th>����</th>
                                                    <th>����</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>text</td>
                                                    <td>string</td>
                                                    <td>��</td>
                                                    <td>Ҫ�������ı�����</td>
                                                </tr>
                                                <tr>
                                                    <td>language</td>
                                                    <td>string</td>
                                                    <td>��</td>
                                                    <td>�ı����ԣ�Ĭ��Ϊ�Զ����</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <h6 class="mt-3">��Ӧ</h6>
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
                                    
                                    <h6 class="mt-3">ʾ������</h6>
                                    <div class="code-block bg-dark p-3 rounded">
                                        <pre class="text-light mb-0"><code>curl -X POST \\
  https://api.alingai.com/v1/nlp/sentiment \\
  -H "X-API-Key: your_api_key_here" \\
  -H "Content-Type: application/json" \\
  -d '{"text": "�ҷǳ�ϲ�������Ʒ��ʹ������ܺã�"}'</code></pre>
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
                                    <h5 class="card-title">����ʵ��ʶ��</h5>
                                    <p class="card-text">ʶ���ı��е�����ʵ�壬����������������֯���ȡ�</p>
                                    
                                    <h6 class="mt-3">�������</h6>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>������</th>
                                                    <th>����</th>
                                                    <th>����</th>
                                                    <th>����</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>text</td>
                                                    <td>string</td>
                                                    <td>��</td>
                                                    <td>Ҫ�������ı�����</td>
                                                </tr>
                                                <tr>
                                                    <td>language</td>
                                                    <td>string</td>
                                                    <td>��</td>
                                                    <td>�ı����ԣ�Ĭ��Ϊ�Զ����</td>
                                                </tr>
                                                <tr>
                                                    <td>types</td>
                                                    <td>array</td>
                                                    <td>��</td>
                                                    <td>Ҫʶ���ʵ�����ͣ�Ĭ��Ϊ��������</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <h6 class="mt-3">��Ӧ</h6>
                                    <div class="code-block bg-dark p-3 rounded">
                                        <pre class="text-light mb-0"><code>{
  "entities": [
    {
      "text": "����",
      "type": "PERSON",
      "start": 0,
      "end": 2,
      "confidence": 0.95
    },
    {
      "text": "����",
      "type": "LOCATION",
      "start": 4,
      "end": 6,
      "confidence": 0.98
    },
    {
      "text": "�廪��ѧ",
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
                        
                        <!-- ������Ӿ� API -->
                        <section id="vision-api" class="mt-4 mb-5">
                            <h3 class="h4 mb-3">������Ӿ� API</h3>
                            <p>������Ӿ� API �ṩͼ��ʶ�������⡢����ʶ��ȹ��ܡ�</p>
                            
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-success me-2">POST</span>
                                        <h5 class="mb-0">/vision/analyze</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">ͼ�����</h5>
                                    <p class="card-text">����ͼ�����ݣ�ʶ�����塢��������ɫ����Ϣ��</p>
                                    
                                    <h6 class="mt-3">�������</h6>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>������</th>
                                                    <th>����</th>
                                                    <th>����</th>
                                                    <th>����</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>image</td>
                                                    <td>string (base64) �� URL</td>
                                                    <td>��</td>
                                                    <td>Ҫ������ͼ�񣬿����� base64 �����ͼ�����ݻ�ͼ�� URL</td>
                                                </tr>
                                                <tr>
                                                    <td>features</td>
                                                    <td>array</td>
                                                    <td>��</td>
                                                    <td>Ҫ�������������� "objects", "scenes", "colors" �ȣ�Ĭ��Ϊ��������</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <h6 class="mt-3">��Ӧ</h6>
                                    <div class="code-block bg-dark p-3 rounded">
                                        <pre class="text-light mb-0"><code>{
  "objects": [
    {
      "name": "è",
      "confidence": 0.98,
      "box": {
        "x": 10,
        "y": 20,
        "width": 200,
        "height": 150
      }
    },
    {
      "name": "ɳ��",
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
      "name": "����",
      "confidence": 0.97
    },
    {
      "name": "����",
      "confidence": 0.92
    }
  ],
  "colors": [
    {
      "name": "��ɫ",
      "hex": "#FFFFFF",
      "percentage": 0.45
    },
    {
      "name": "��ɫ",
      "hex": "#808080",
      "percentage": 0.30
    }
  ]
}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </section>
                        
                        <!-- ����ѧϰ API -->
                        <section id="ml-api" class="mt-4 mb-5">
                            <h3 class="h4 mb-3">����ѧϰ API</h3>
                            <p>����ѧϰ API �ṩԤ��������쳣��⡢��������ȹ��ܡ�</p>
                            
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-success me-2">POST</span>
                                        <h5 class="mb-0">/ml/predict</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">Ԥ�����</h5>
                                    <p class="card-text">������ʷ���ݽ���Ԥ�������</p>
                                    
                                    <h6 class="mt-3">�������</h6>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>������</th>
                                                    <th>����</th>
                                                    <th>����</th>
                                                    <th>����</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>model_id</td>
                                                    <td>string</td>
                                                    <td>��</td>
                                                    <td>ģ�� ID</td>
                                                </tr>
                                                <tr>
                                                    <td>data</td>
                                                    <td>object</td>
                                                    <td>��</td>
                                                    <td>��������</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <h6 class="mt-3">��Ӧ</h6>
                                    <div class="code-block bg-dark p-3 rounded">
                                        <pre class="text-light mb-0"><code>{
  "prediction": {
    "value": 42.5,
    "confidence": 0.85
  },
  "model_info": {
    "id": "model_123",
    "name": "����Ԥ��ģ��",
    "version": "1.0"
  }
}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </section>
                    
                    <!-- SDK ��ͻ��˿� -->
                    <section id="sdks" class="mb-5">
                        <h2 class="h3 mb-4">SDK ��ͻ��˿�</h2>
                        <p>Ϊ�˼� API ��ʹ�ã������ṩ�˶��ֱ�����Ե� SDK��</p>
                        
                        <div class="row row-cols-1 row-cols-md-2 g-4">
                            <div class="col">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">Python SDK</h5>
                                        <p class="card-text">������ Python 3.6+ �Ŀͻ��˿⡣</p>
                                        <div class="code-block bg-dark p-3 rounded">
                                            <pre class="text-light mb-0"><code>pip install alingai</code></pre>
                                        </div>
                                        <a href="#" class="btn btn-primary mt-3">�鿴�ĵ�</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">JavaScript SDK</h5>
                                        <p class="card-text">������ Node.js ��������Ŀͻ��˿⡣</p>
                                        <div class="code-block bg-dark p-3 rounded">
                                            <pre class="text-light mb-0"><code>npm install alingai</code></pre>
                                        </div>
                                        <a href="#" class="btn btn-primary mt-3">�鿴�ĵ�</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">Java SDK</h5>
                                        <p class="card-text">������ Java 8+ �Ŀͻ��˿⡣</p>
                                        <div class="code-block bg-dark p-3 rounded">
                                            <pre class="text-light mb-0"><code>&lt;dependency&gt;
  &lt;groupId&gt;com.alingai&lt;/groupId&gt;
  &lt;artifactId&gt;alingai-java&lt;/artifactId&gt;
  &lt;version&gt;1.0.0&lt;/version&gt;
&lt;/dependency&gt;</code></pre>
                                        </div>
                                        <a href="#" class="btn btn-primary mt-3">�鿴�ĵ�</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">PHP SDK</h5>
                                        <p class="card-text">������ PHP 7.4+ �Ŀͻ��˿⡣</p>
                                        <div class="code-block bg-dark p-3 rounded">
                                            <pre class="text-light mb-0"><code>composer require alingai/alingai-php</code></pre>
                                        </div>
                                        <a href="#" class="btn btn-primary mt-3">�鿴�ĵ�</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- ʾ������ -->
                    <section id="examples" class="mb-5">
                        <h2 class="h3 mb-4">ʾ������</h2>
                        <p>������һЩʹ�� AlingAi API ��ʾ�����룺</p>
                        
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

# ��ʼ���ͻ���
client = alingai.Client(api_key="your_api_key_here")

# ��з���
sentiment_result = client.nlp.sentiment(text="����һ����Ҫ������е��ı�")
print(f"���: {sentiment_result.sentiment}")
print(f"��������: {sentiment_result.scores.positive}")

# ͼ�����
with open("image.jpg", "rb") as image_file:
    image_data = image_file.read()
    vision_result = client.vision.analyze(
        image=image_data,
        features=["objects", "scenes"]
    )
    
for obj in vision_result.objects:
    print(f"��⵽����: {obj.name}�����Ŷ�: {obj.confidence}")</code></pre>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="javascript" role="tabpanel" aria-labelledby="javascript-tab">
                                <div class="code-block bg-dark p-3 rounded">
                                    <pre class="text-light mb-0"><code>const AlingAi = require('alingai');

// ��ʼ���ͻ���
const client = new AlingAi.Client('your_api_key_here');

// ��з���
client.nlp.sentiment({ text: '����һ����Ҫ������е��ı�' })
  .then(result => {
    console.log(`���: ${result.sentiment}`);
    console.log(`��������: ${result.scores.positive}`);
  })
  .catch(error => {
    console.error('����ʧ��:', error);
  });

// ͼ�����
const fs = require('fs');
const imageData = fs.readFileSync('image.jpg');

client.vision.analyze({
  image: imageData.toString('base64'),
  features: ['objects', 'scenes']
})
  .then(result => {
    result.objects.forEach(obj => {
      console.log(`��⵽����: ${obj.name}�����Ŷ�: ${obj.confidence}`);
    });
  })
  .catch(error => {
    console.error('����ʧ��:', error);
  });</code></pre>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="php" role="tabpanel" aria-labelledby="php-tab">
                                <div class="code-block bg-dark p-3 rounded">
                                    <pre class="text-light mb-0"><code>&lt;?php

require_once 'vendor/autoload.php';

// ��ʼ���ͻ���
$client = new AlingAi\Client('your_api_key_here');

// ��з���
try {
    $sentimentResult = $client->nlp->sentiment([
        'text' => '����һ����Ҫ������е��ı�'
    ]);
    
    echo "���: " . $sentimentResult->sentiment . PHP_EOL;
    echo "��������: " . $sentimentResult->scores->positive . PHP_EOL;
} catch (Exception $e) {
    echo "����ʧ��: " . $e->getMessage() . PHP_EOL;
}

// ͼ�����
try {
    $imageData = file_get_contents('image.jpg');
    
    $visionResult = $client->vision->analyze([
        'image' => base64_encode($imageData),
        'features' => ['objects', 'scenes']
    ]);
    
    foreach ($visionResult->objects as $obj) {
        echo "��⵽����: " . $obj->name . "�����Ŷ�: " . $obj->confidence . PHP_EOL;
    }
} catch (Exception $e) {
    echo "����ʧ��: " . $e->getMessage() . PHP_EOL;
}
?></code></pre>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Webhooks -->
                    <section id="webhooks" class="mb-5">
                        <h2 class="h3 mb-4">Webhooks</h2>
                        <p>Webhooks �����������첽������֪ͨ������ʱ�����еĲ������ʱ�����ǻ�����ָ���� URL ���� HTTP POST ����</p>
                        
                        <div class="mt-4">
                            <h5>���� Webhook</h5>
                            <p>�������ڿ������� API ���������� Webhook URL����������Ϊ��ͬ���͵��¼����ò�ͬ�� URL��</p>
                        </div>
                        
                        <div class="mt-4">
                            <h5>Webhook �����ʽ</h5>
                            <div class="code-block bg-dark p-3 rounded">
                                <pre class="text-light mb-0"><code>{
  "event": "job.completed",
  "job_id": "job_123",
  "status": "success",
  "result": {
    // �������
  },
  "created_at": "2023-06-01T12:34:56Z"
}</code></pre>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5>��֤ Webhook</h5>
                            <p>Ϊ��ȷ�� Webhook �������ʵ�ԣ����ǻ�������ͷ�а���ǩ����</p>
                            <div class="code-block bg-dark p-3 rounded">
                                <pre class="text-light mb-0"><code>X-AlingAi-Signature: sha256=...</code></pre>
                            </div>
                            <p>������ʹ������ Webhook ��Կ��֤��ǩ����</p>
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
                    
                    <!-- ������־ -->
                    <section id="changelog">
                        <h2 class="h3 mb-4">������־</h2>
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h3 class="h5 mb-0">v1.2.0 (2023-06-01)</h3>
                                    <p class="text-muted mb-2">�¹��ܺ͸Ľ�</p>
                                    <ul>
                                        <li>������������� API</li>
                                        <li>�Ľ���ͼ������㷨</li>
                                        <li>�����˶Ը������Ե�֧��</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h3 class="h5 mb-0">v1.1.0 (2023-03-15)</h3>
                                    <p class="text-muted mb-2">�¹��ܺ͸Ľ�</p>
                                    <ul>
                                        <li>����� Webhooks ֧��</li>
                                        <li>�Ľ��� API ��Ӧ�ٶ�</li>
                                        <li>�޸��˶�� bug</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h3 class="h5 mb-0">v1.0.0 (2023-01-01)</h3>
                                    <p class="text-muted mb-2">��ʼ�汾</p>
                                    <ul>
                                        <li>��������Ȼ���Դ��� API</li>
                                        <li>�����˼�����Ӿ� API</li>
                                        <li>�����˻���ѧϰ API</li>
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
    // ���������¼������µ�������
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
