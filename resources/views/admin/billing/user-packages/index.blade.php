@extends('admin.layouts.admin')

@section('title', '�û��ײ�')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">�û��ײ��б�</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>�û�</th>
                                <th>�ײ�����</th>
                                <th>���</th>
                                <th>��ʼʱ��</th>
                                <th>����ʱ��</th>
                                <th>״̬</th>
                                <th>����</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($userPackages as $userPackage)
                            <tr>
                                <td>{{ $userPackage->id }}</td>
                                <td>{{ $userPackage->user->name }}</td>
                                <td>{{ $userPackage->package->name }}</td>
                                <td>{{ $userPackage->quota }}</td>
                                <td>{{ $userPackage->start_time }}</td>
                                <td>{{ $userPackage->end_time }}</td>
                                <td>
                                    @if($userPackage->status == 1)
                                    <span class="badge badge-success">��Ч</span>
                                    @else
                                    <span class="badge badge-danger">�ѹ���</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.user-packages.show', $userPackage->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> �鿴
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tbody>
                            @foreach($userPackages as $userPackage)
                            <tr>
                                <td>{{ $userPackage->id }}</td>
                                <td>{{ $userPackage->user->name }}</td>
                                <td>{{ $userPackage->package->name }}</td>
                                <td>{{ $userPackage->quota }}</td>
                                <td>{{ $userPackage->start_time }}</td>
                                <td>{{ $userPackage->end_time }}</td>
                                <td>
                                    @if($userPackage->status == 1)
                                    <span class="badge badge-success">��Ч</span>
                                    @else
                                    <span class="badge badge-danger">�ѹ���</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.user-packages.show', $userPackage->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> �鿴
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{ $userPackages->links() }}
                </div>
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</div>
@endsection
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{ $userPackages->links() }}
                </div>
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</div>
@endsection
