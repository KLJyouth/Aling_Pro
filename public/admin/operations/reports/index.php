<?php // Reports Index View

include_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mt-4">��ά��������</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">��ҳ</a></li>
                <li class="breadcrumb-item active">��ά����</li>
            </ol>
            
            <!-- ������� -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <h2><?= $reportsOverview['totalReports'] ?></h2>
                            <div>�ܱ�����</div>
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
                            <h2><?= $reportsOverview['generatedToday'] ?></h2>
                            <div>��������</div>
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
                            <h2><?= $reportsOverview['scheduledReports'] ?></h2>
                            <div>�ƻ�����</div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">�鿴����</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-info text-white mb-4">
                        <div class="card-body">
                            <h2><?= $reportsOverview['customReports'] ?></h2>
                            <div>�Զ��屨��</div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">�鿴����</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- �������͵��� -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-chart-bar mr-1"></i>
                            ��������
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/reports/performance" class="btn btn-outline-primary btn-lg btn-block">
                                        <i class="fas fa-tachometer-alt fa-2x mb-2"></i><br>
                                        ϵͳ���ܱ���
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/reports/security" class="btn btn-outline-success btn-lg btn-block">
                                        <i class="fas fa-shield-alt fa-2x mb-2"></i><br>
                                        ��ȫ��Ʊ���
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/reports/errors" class="btn btn-outline-danger btn-lg btn-block">
                                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>
                                        ����ͳ�Ʊ���
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button type="button" class="btn btn-outline-info btn-lg btn-block" data-toggle="modal" data-target="#customReportModal">
                                        <i class="fas fa-file-alt fa-2x mb-2"></i><br>
                                        �����Զ��屨��
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ������� -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table mr-1"></i>
                    �������
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="reportsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>����</th>
                                    <th>����</th>
                                    <th>��С</th>
                                    <th>����ʱ��</th>
                                    <th>״̬</th>
                                    <th>������</th>
                                    <th>����</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentReports as $report): ?>
                                <tr>
                                    <td><?= $report['id'] ?></td>
                                    <td><?= $report['name'] ?></td>
                                    <td>
                                        <?php 
                                        switch ($report['type']) {
                                            case 'performance':
                                                echo '<span class="badge badge-primary">���ܱ���</span>';
                                                break;
                                            case 'security':
                                                echo '<span class="badge badge-success">��ȫ����</span>';
                                                break;
                                            case 'errors':
                                                echo '<span class="badge badge-danger">���󱨸�</span>';
                                                break;
                                            case 'usage':
                                                echo '<span class="badge badge-info">ʹ���������</span>';
                                                break;
                                            case 'custom':
                                                echo '<span class="badge badge-warning">�Զ��屨��</span>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td><?= $report['size'] ?></td>
                                    <td><?= $report['date'] ?></td>
                                    <td>
                                        <?php 
                                        switch ($report['status']) {
                                            case 'completed':
                                                echo '<span class="badge badge-success">���</span>';
                                                break;
                                            case 'scheduled':
                                                echo '<span class="badge badge-warning">�ƻ���</span>';
                                                break;
                                            case 'failed':
                                                echo '<span class="badge badge-danger">ʧ��</span>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td><?= $report['creator'] ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-primary view-report" data-id="<?= $report['id'] ?>">
                                                <i class="fas fa-eye"></i> �鿴
                                            </button>
                                            <button type="button" class="btn btn-sm btn-success export-report" data-id="<?= $report['id'] ?>">
                                                <i class="fas fa-file-export"></i> ����
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> ɾ��
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

<!-- �Զ��屨��ģ̬�� -->
<div class="modal fade" id="customReportModal" tabindex="-1" role="dialog" aria-labelledby="customReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customReportModalLabel">�����Զ��屨��</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="customReportForm">
                    <div class="form-group">
                        <label for="reportType">��������</label>
                        <select class="form-control" id="reportType" name="report_type">
                            <option value="performance">ϵͳ���ܱ���</option>
                            <option value="security">��ȫ��Ʊ���</option>
                            <option value="errors">����ͳ�Ʊ���</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="startDate">��ʼ����</label>
                                <input type="date" class="form-control" id="startDate" name="start_date" value="<?= date('Y-m-d', strtotime('-30 days')) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="endDate">��������</label>
                                <input type="date" class="form-control" id="endDate" name="end_date" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>����ѡ��</label>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="includeCharts" name="filters[include_charts]" checked>
                            <label class="custom-control-label" for="includeCharts">����ͼ��</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="includeDetails" name="filters[include_details]" checked>
                            <label class="custom-control-label" for="includeDetails">������ϸ����</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="includeRecommendations" name="filters[include_recommendations]">
                            <label class="custom-control-label" for="includeRecommendations">�����Ż�����</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ȡ��</button>
                <button type="button" class="btn btn-primary" id="generateReportBtn">���ɱ���</button>
            </div>
        </div>
    </div>
</div>

<!-- ��������ģ̬�� -->
<div class="modal fade" id="exportReportModal" tabindex="-1" role="dialog" aria-labelledby="exportReportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportReportModalLabel">��������</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="exportReportForm">
                    <input type="hidden" id="exportReportId" name="report_id">
                    <div class="form-group">
                        <label for="exportFormat">������ʽ</label>
                        <select class="form-control" id="exportFormat" name="format">
                            <option value="pdf">PDF</option>
                            <option value="xlsx">Excel</option>
                            <option value="csv">CSV</option>
                            <option value="html">HTML</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ȡ��</button>
                <button type="button" class="btn btn-primary" id="confirmExportBtn">����</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // ��ʼ�����ݱ��
        $('#reportsTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Chinese.json"
            }
        }];
        
        // �����Զ��屨��
        $('#generateReportBtn').on('click', function() {
            var formData = $('#customReportForm').serialize(];
            
            $('#generateReportBtn').html('<i class="fas fa-spinner fa-spin mr-1"></i> ������...').attr('disabled', true];
            
            // ����AJAX����
            $.ajax({
                url: '<?= BASE_URL ?>/reports/generate',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#customReportModal').modal('hide'];
                    $('#generateReportBtn').html('���ɱ���').attr('disabled', false];
                    
                    if (response.success) {
                        // ��ʾ�ɹ���Ϣ
                        alert('�������ɳɹ�'];
                        // ˢ��ҳ��
                        window.location.reload(];
                    } else {
                        alert('��������ʧ��: ' + response.message];
                    }
                },
                error: function() {
                    $('#customReportModal').modal('hide'];
                    $('#generateReportBtn').html('���ɱ���').attr('disabled', false];
                    alert('�����������Ժ�����'];
                }
            }];
        }];
        
        // ��������
        $('.export-report').on('click', function() {
            var reportId = $(this).data('id'];
            $('#exportReportId').val(reportId];
            $('#exportReportModal').modal('show'];
        }];
        
        $('#confirmExportBtn').on('click', function() {
            var formData = $('#exportReportForm').serialize(];
            
            $('#confirmExportBtn').html('<i class="fas fa-spinner fa-spin mr-1"></i> ������...').attr('disabled', true];
            
            // ����AJAX����
            $.ajax({
                url: '<?= BASE_URL ?>/reports/export',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#exportReportModal').modal('hide'];
                    $('#confirmExportBtn').html('����').attr('disabled', false];
                    
                    if (response.success) {
                        // ��ʾ�ɹ���Ϣ
                        alert('���浼���ɹ�'];
                        // ����������
                        window.open(response.details.downloadUrl, '_blank'];
                    } else {
                        alert('���浼��ʧ��: ' + response.message];
                    }
                },
                error: function() {
                    $('#exportReportModal').modal('hide'];
                    $('#confirmExportBtn').html('����').attr('disabled', false];
                    alert('�����������Ժ�����'];
                }
            }];
        }];
        
        // �鿴����
        $('.view-report').on('click', function() {
            var reportId = $(this).data('id'];
            var reportType = $(this).closest('tr').find('td:eq(2)').text().trim(];
            
            // ���ݱ���������ת����ͬҳ��
            if (reportType.includes('����')) {
                window.location.href = '<?= BASE_URL ?>/reports/performance?id=' + reportId;
            } else if (reportType.includes('��ȫ')) {
                window.location.href = '<?= BASE_URL ?>/reports/security?id=' + reportId;
            } else if (reportType.includes('����')) {
                window.location.href = '<?= BASE_URL ?>/reports/errors?id=' + reportId;
            } else {
                alert('�޷��鿴�����ͱ���'];
            }
        }];
    }];
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
