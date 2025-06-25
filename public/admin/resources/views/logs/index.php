<?php // Logs Index View

include_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mt-4">��־��������</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">��ҳ</a></li>
                <li class="breadcrumb-item active">��־����</li>
            </ol>
            
            <!-- ��־���� -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <h2><?= $logsOverview['totalLogFiles'] ?></h2>
                            <div>��־�ļ�����</div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">�鿴����</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">
                            <h2><?= $logsOverview['totalSize'] ?></h2>
                            <div>��־�ܴ�С</div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">�鿴����</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning text-white mb-4">
                        <div class="card-body">
                            <h2><?= $logsOverview['todayLogs'] ?></h2>
                            <div>������־</div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">�鿴����</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-danger text-white mb-4">
                        <div class="card-body">
                            <h2><?= $logsOverview['errorLogs'] ?></h2>
                            <div>������־</div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">�鿴����</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ��־���͵��� -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-file-alt mr-1"></i>
                            ��־����
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/logs/system" class="btn btn-outline-primary btn-lg btn-block">
                                        <i class="fas fa-server fa-2x mb-2"></i><br>
                                        ϵͳ��־
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/logs/errors" class="btn btn-outline-danger btn-lg btn-block">
                                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>
                                        ������־
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/logs/access" class="btn btn-outline-success btn-lg btn-block">
                                        <i class="fas fa-users fa-2x mb-2"></i><br>
                                        ������־
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/logs/security" class="btn btn-outline-warning btn-lg btn-block">
                                        <i class="fas fa-shield-alt fa-2x mb-2"></i><br>
                                        ��ȫ��־
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ��־���� -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-search mr-1"></i>
                    ��־����
                </div>
                <div class="card-body">
                    <form id="logSearchForm">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="keyword">�ؼ���</label>
                                    <input type="text" class="form-control" id="keyword" name="keyword" placeholder="������ؼ���">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="logType">��־����</label>
                                    <select class="form-control" id="logType" name="log_type">
                                        <option value="all">ȫ��</option>
                                        <option value="system">ϵͳ��־</option>
                                        <option value="error">������־</option>
                                        <option value="access">������־</option>
                                        <option value="security">��ȫ��־</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="startDate">��ʼ����</label>
                                    <input type="date" class="form-control" id="startDate" name="start_date" value="<?= date('Y-m-d', strtotime('-7 days')) ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="endDate">��������</label>
                                    <input type="date" class="form-control" id="endDate" name="end_date" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="searchBtn">
                            <i class="fas fa-search mr-1"></i> ����
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- ������� -->
            <div class="card mb-4" id="searchResultsCard" style="display: none;">
                <div class="card-header">
                    <i class="fas fa-table mr-1"></i>
                    �������
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="searchResultsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>�ļ�</th>
                                    <th>�к�</th>
                                    <th>ʱ���</th>
                                    <th>����</th>
                                </tr>
                            </thead>
                            <tbody id="searchResultsBody">
                                <!-- ���������ͨ��JavaScript��̬��� -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- �����־�ļ� -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-file-alt mr-1"></i>
                    �����־�ļ�
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="recentLogsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>�ļ���</th>
                                    <th>����</th>
                                    <th>��С</th>
                                    <th>����</th>
                                    <th>�޸�ʱ��</th>
                                    <th>����</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentLogs as $log): ?>
                                <tr>
                                    <td><?= $log['name'] ?></td>
                                    <td>
                                        <?php 
                                        switch ($log['type']) {
                                            case 'system':
                                                echo '<span class="badge badge-primary">ϵͳ��־</span>';
                                                break;
                                            case 'error':
                                                echo '<span class="badge badge-danger">������־</span>';
                                                break;
                                            case 'access':
                                                echo '<span class="badge badge-success">������־</span>';
                                                break;
                                            case 'security':
                                                echo '<span class="badge badge-warning">��ȫ��־</span>';
                                                break;
                                            default:
                                                echo '<span class="badge badge-secondary">������־</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?= $log['size'] ?></td>
                                    <td><?= $log['lineCount'] ?></td>
                                    <td><?= $log['modifiedTime'] ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= BASE_URL ?>/logs/<?= $log['type'] ?>?file=<?= $log['name'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> �鿴
                                            </a>
                                            <a href="<?= BASE_URL ?>/logs/download?file=<?= $log['name'] ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-download"></i> ����
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger clear-log" data-file="<?= $log['name'] ?>">
                                                <i class="fas fa-trash"></i> ���
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- �����־ȷ��ģ̬�� -->
<div class="modal fade" id="clearLogModal" tabindex="-1" role="dialog" aria-labelledby="clearLogModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clearLogModalLabel">�����־ȷ��</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>��ȷ��Ҫ��մ���־�ļ��𣿴˲����޷�������</p>
                <p>�ļ���: <span id="clearLogFileName"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ȡ��</button>
                <button type="button" class="btn btn-danger" id="confirmClearBtn">ȷ�����</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // ��ʼ�����ݱ��
        $('#recentLogsTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Chinese.json"
            }
        }];
        
        // ��־����
        $('#logSearchForm').on('submit', function(e) {
            e.preventDefault(];
            
            var formData = $(this).serialize(];
            
            $('#searchBtn').html('<i class="fas fa-spinner fa-spin mr-1"></i> ������...').attr('disabled', true];
            
            // ����AJAX����
            $.ajax({
                url: '<?= BASE_URL ?>/logs/search',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#searchBtn').html('<i class="fas fa-search mr-1"></i> ����').attr('disabled', false];
                    
                    if (response.success) {
                        // ��ʾ�������
                        displaySearchResults(response.results];
                    } else {
                        alert('����ʧ��: ' + response.message];
                    }
                },
                error: function() {
                    $('#searchBtn').html('<i class="fas fa-search mr-1"></i> ����').attr('disabled', false];
                    alert('�����������Ժ�����'];
                }
            }];
        }];
        
        // ��ʾ�������
        function displaySearchResults(results) {
            var tbody = $('#searchResultsBody'];
            tbody.empty(];
            
            if (results.length === 0) {
                tbody.append('<tr><td colspan="4" class="text-center">û���ҵ�ƥ��Ľ��</td></tr>'];
            } else {
                $.each(results, function(index, result) {
                    var row = '<tr>' +
                        '<td>' + result.file + '</td>' +
                        '<td>' + result.line + '</td>' +
                        '<td>' + (result.timestamp || '-') + '</td>' +
                        '<td>' + result.content + '</td>' +
                        '</tr>';
                    tbody.append(row];
                }];
            }
            
            // ��ʾ�����Ƭ
            $('#searchResultsCard').show(];
            
            // ��ʼ������������
            if ($.fn.dataTable.isDataTable('#searchResultsTable')) {
                $('#searchResultsTable').DataTable().destroy(];
            }
            
            $('#searchResultsTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Chinese.json"
                }
            }];
        }
        
        // �����־
        var fileToDelete;
        
        $('.clear-log').on('click', function() {
            fileToDelete = $(this).data('file'];
            $('#clearLogFileName').text(fileToDelete];
            $('#clearLogModal').modal('show'];
        }];
        
        $('#confirmClearBtn').on('click', function() {
            // ����AJAX����
            $.ajax({
                url: '<?= BASE_URL ?>/logs/clear',
                type: 'POST',
                data: {
                    file: fileToDelete
                },
                dataType: 'json',
                success: function(response) {
                    $('#clearLogModal').modal('hide'];
                    
                    if (response.success) {
                        // ��ʾ�ɹ���Ϣ
                        alert('��־�ļ������'];
                        // ˢ��ҳ��
                        window.location.reload(];
                    } else {
                        alert('�����־�ļ�ʧ��: ' + response.message];
                    }
                },
                error: function() {
                    $('#clearLogModal').modal('hide'];
                    alert('�����������Ժ�����'];
                }
            }];
        }];
    }];
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
