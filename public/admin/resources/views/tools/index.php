<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'IT��ά����' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Microsoft YaHei', 'PingFang SC', sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            background-color: #343a40;
            min-height: 100vh;
            color: #fff;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75];
            padding: 0.75rem 1rem;
        }
        .sidebar .nav-link:hover {
            color: #fff;
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1];
        }
        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075];
            margin-bottom: 1.5rem;
        }
        .card-header {
            font-weight: 600;
            background-color: rgba(0, 0, 0, 0.03];
        }
        .tool-item {
            border: 1px solid #eee;
            border-radius: 0.25rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #fff;
            transition: all 0.2s;
        }
        .tool-item:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15];
            border-color: #ddd;
        }
        .tool-item h5 {
            margin-bottom: 0.5rem;
            color: #2563eb;
        }
        .tool-item .tool-meta {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .tool-item .tool-description {
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }
        .tool-category {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 0.25rem;
            margin-right: 0.5rem;
        }
        .category-fix {
            background-color: #e6f7ff;
            color: #0070f3;
        }
        .category-check {
            background-color: #e6fffb;
            color: #13c2c2;
        }
        .category-validate {
            background-color: #f6ffed;
            color: #52c41a;
        }
        .category-namespace {
            background-color: #fff7e6;
            color: #fa8c16;
        }
        .category-encoding {
            background-color: #fff1f0;
            color: #f5222d;
        }
        .category-php {
            background-color: #f9f0ff;
            color: #722ed1;
        }
        .category-other {
            background-color: #f5f5f5;
            color: #595959;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- ����� -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5>AlingAi_pro</h5>
                        <p class="text-muted">IT��ά����</p>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard">
                                <i class="bi bi-speedometer2"></i> �Ǳ���
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/tools">
                                <i class="bi bi-tools"></i> ά������
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/monitoring">
                                <i class="bi bi-graph-up"></i> ϵͳ���
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/security">
                                <i class="bi bi-shield-lock"></i> ��ȫ����
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/reports">
                                <i class="bi bi-file-earmark-text"></i> ��ά����
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/logs">
                                <i class="bi bi-journal-text"></i> ��־����
                            </a>
                        </li>
                    </ul>
                    
                    <hr>
                    
                    <div class="text-center mb-3">
                        <a href="/logout" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-box-arrow-right"></i> �˳�
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- �������� -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">ά������</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="/tools/php-fix" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-wrench"></i> PHP�޸�����
                            </a>
                            <a href="/tools/namespace-check" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-check-circle"></i> �����ռ���
                            </a>
                            <a href="/tools/encoding-fix" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-translate"></i> �����޸�
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- �������� -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="text" id="toolSearch" class="form-control" placeholder="��������...">
                                    <button class="btn btn-primary" type="button" id="searchButton">
                                        <i class="bi bi-search"></i> ����
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select id="categoryFilter" class="form-select">
                                    <option value="">�������</option>
                                    <option value="fix">�޸�����</option>
                                    <option value="check">��鹤��</option>
                                    <option value="validate">��֤����</option>
                                    <option value="namespace">�����ռ乤��</option>
                                    <option value="encoding">���빤��</option>
                                    <option value="php">PHP����</option>
                                    <option value="other">��������</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- �����б� -->
                <div id="toolsList">
                    <?php if (empty($tools)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> δ�ҵ��κι��ߡ���ȷ������Ŀ¼���ڲ�����PHP�����ļ���
                    </div>
                    <?php else: ?>
                        <?php foreach ($toolsByCategory as $category => $categoryTools): ?>
                        <div class="card mb-4 category-section" data-category="<?= $category ?>">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <?php
                                    $categoryIcon = 'bi-tools';
                                    $categoryLabel = '��������';
                                    
                                    switch ($category) {
                                        case 'fix':
                                            $categoryIcon = 'bi-wrench';
                                            $categoryLabel = '�޸�����';
                                            break;
                                        case 'check':
                                            $categoryIcon = 'bi-check-circle';
                                            $categoryLabel = '��鹤��';
                                            break;
                                        case 'validate':
                                            $categoryIcon = 'bi-shield-check';
                                            $categoryLabel = '��֤����';
                                            break;
                                        case 'namespace':
                                            $categoryIcon = 'bi-diagram-3';
                                            $categoryLabel = '�����ռ乤��';
                                            break;
                                        case 'encoding':
                                            $categoryIcon = 'bi-translate';
                                            $categoryLabel = '���빤��';
                                            break;
                                        case 'php':
                                            $categoryIcon = 'bi-filetype-php';
                                            $categoryLabel = 'PHP����';
                                            break;
                                    }
                                    ?>
                                    <i class="bi <?= $categoryIcon ?>"></i> <?= $categoryLabel ?> <span class="badge bg-secondary"><?= count($categoryTools) ?></span>
                                </h5>
                                <button class="btn btn-sm btn-outline-secondary toggle-category" data-category="<?= $category ?>">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                            </div>
                            <div class="card-body category-tools">
                                <div class="row">
                                    <?php foreach ($categoryTools as $tool): ?>
                                    <div class="col-md-6 tool-item-container">
                                        <div class="tool-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <h5><?= htmlspecialchars($tool['name']) ?></h5>
                                                <span class="tool-category category-<?= $tool['category'] ?>"><?= $tool['category'] ?></span>
                                            </div>
                                            <div class="tool-meta">
                                                <span><i class="bi bi-clock"></i> <?= $tool['lastModified'] ?></span>
                                                <span class="ms-2"><i class="bi bi-file-earmark"></i> <?= number_format($tool['size'] / 1024, 2) ?> KB</span>
                                            </div>
                                            <div class="tool-description">
                                                <?= htmlspecialchars($tool['description']) ?>
                                            </div>
                                            <div class="mt-3">
                                                <button class="btn btn-sm btn-primary run-tool" data-tool="<?= $tool['name'] ?>">
                                                    <i class="bi bi-play"></i> ����
                                                </button>
                                                <button class="btn btn-sm btn-outline-secondary view-source" data-tool="<?= $tool['file'] ?>">
                                                    <i class="bi bi-code-slash"></i> �鿴Դ��
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ���й���ģ̬�� -->
    <div class="modal fade" id="runToolModal" tabindex="-1" aria-labelledby="runToolModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="runToolModalLabel">���й���</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="�ر�"></button>
                </div>
                <div class="modal-body">
                    <form id="runToolForm">
                        <input type="hidden" id="toolName" name="tool_name">
                        <div class="mb-3">
                            <label for="toolParams" class="form-label">����</label>
                            <input type="text" class="form-control" id="toolParams" name="params" placeholder="���빤�߲���">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">���</label>
                            <pre id="toolOutput" class="p-3 bg-light" style="max-height: 300px; overflow-y: auto;">�ȴ�����...</pre>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">�ر�</button>
                    <button type="button" class="btn btn-primary" id="executeToolBtn">ִ��</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- �鿴Դ��ģ̬�� -->
    <div class="modal fade" id="viewSourceModal" tabindex="-1" aria-labelledby="viewSourceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewSourceModalLabel">�鿴Դ��</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="�ر�"></button>
                </div>
                <div class="modal-body">
                    <pre id="sourceCode" class="p-3 bg-light" style="max-height: 500px; overflow-y: auto;">������...</pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">�ر�</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ��������
            const toolSearch = document.getElementById('toolSearch'];
            const searchButton = document.getElementById('searchButton'];
            const categoryFilter = document.getElementById('categoryFilter'];
            const toolItems = document.querySelectorAll('.tool-item-container'];
            const categorySections = document.querySelectorAll('.category-section'];
            
            function filterTools() {
                const searchTerm = toolSearch.value.toLowerCase(];
                const category = categoryFilter.value.toLowerCase(];
                
                // ������������������ʾ
                categorySections.forEach(section => {
                    section.style.display = 'block';
                }];
                
                // ���˹�����
                let visibleItemsCount = {};
                
                toolItems.forEach(item => {
                    const toolName = item.querySelector('h5').textContent.toLowerCase(];
                    const toolDescription = item.querySelector('.tool-description').textContent.toLowerCase(];
                    const toolCategory = item.closest('.category-section').dataset.category;
                    
                    const matchesSearch = searchTerm === '' || 
                                          toolName.includes(searchTerm) || 
                                          toolDescription.includes(searchTerm];
                    const matchesCategory = category === '' || toolCategory === category;
                    
                    if (matchesSearch && matchesCategory) {
                        item.style.display = 'block';
                        if (!visibleItemsCount[toolCategory]) {
                            visibleItemsCount[toolCategory] = 0;
                        }
                        visibleItemsCount[toolCategory]++;
                    } else {
                        item.style.display = 'none';
                    }
                }];
                
                // ����û�пɼ����ߵ��������
                categorySections.forEach(section => {
                    const sectionCategory = section.dataset.category;
                    if (!visibleItemsCount[sectionCategory] || visibleItemsCount[sectionCategory] === 0) {
                        section.style.display = 'none';
                    }
                }];
            }
            
            searchButton.addEventListener('click', filterTools];
            toolSearch.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    filterTools(];
                }
            }];
            categoryFilter.addEventListener('change', filterTools];
            
            // �л������ʾ/����
            const toggleButtons = document.querySelectorAll('.toggle-category'];
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const category = this.dataset.category;
                    const toolsContainer = this.closest('.card').querySelector('.category-tools'];
                    const icon = this.querySelector('i'];
                    
                    if (toolsContainer.style.display === 'none') {
                        toolsContainer.style.display = 'block';
                        icon.classList.remove('bi-chevron-right'];
                        icon.classList.add('bi-chevron-down'];
                    } else {
                        toolsContainer.style.display = 'none';
                        icon.classList.remove('bi-chevron-down'];
                        icon.classList.add('bi-chevron-right'];
                    }
                }];
            }];
            
            // ���й���
            const runToolButtons = document.querySelectorAll('.run-tool'];
            const runToolModal = new bootstrap.Modal(document.getElementById('runToolModal')];
            const executeToolBtn = document.getElementById('executeToolBtn'];
            
            runToolButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const toolName = this.dataset.tool;
                    document.getElementById('toolName').value = toolName;
                    document.getElementById('runToolModalLabel').textContent = `���й���: ${toolName}`;
                    document.getElementById('toolOutput').textContent = '�ȴ�����...';
                    document.getElementById('toolParams').value = '';
                    runToolModal.show(];
                }];
            }];
            
            executeToolBtn.addEventListener('click', function() {
                const toolName = document.getElementById('toolName').value;
                const params = document.getElementById('toolParams').value;
                const output = document.getElementById('toolOutput'];
                
                output.textContent = '����ִ��...';
                
                // ����AJAX����ִ�й���
                fetch('/tools/run-fix', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `tool_name=${encodeURIComponent(toolName)}&params=${encodeURIComponent(params)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        output.textContent = data.output || 'ִ�гɹ��������';
                    } else {
                        const errors = data.errors || {};
                        let errorMsg = 'ִ��ʧ�ܣ�\n';
                        for (const field in errors) {
                            errorMsg += `${errors[field].join('\n')}\n`;
                        }
                        output.textContent = errorMsg;
                    }
                })
                .catch(error => {
                    output.textContent = `ִ�г���: ${error.message}`;
                }];
            }];
            
            // �鿴Դ��
            const viewSourceButtons = document.querySelectorAll('.view-source'];
            const viewSourceModal = new bootstrap.Modal(document.getElementById('viewSourceModal')];
            
            viewSourceButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const toolFile = this.dataset.tool;
                    document.getElementById('viewSourceModalLabel').textContent = `Դ��: ${toolFile}`;
                    document.getElementById('sourceCode').textContent = '������...';
                    viewSourceModal.show(];
                    
                    // ����Ӧ����һ��API����ȡ����Դ�룬��Ŀǰ����û��ʵ�ָ�API
                    // ��������ֻ��һ��ʾ��
                    document.getElementById('sourceCode').textContent = '// Դ����ع�����δʵ��\n// ��ֱ�Ӳ鿴�ļ�: ' + toolFile;
                }];
            }];
        }];
    </script>
</body>
</html>
