@extends("layouts.app")

@section("title", "��������")

@section("content")
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 mb-4">ǿ��� AI ����</h1>
            <p class="lead text-muted">̽�� AlingAi ƽ̨�ṩ�ķḻ���ܣ���������ҵ��չ�ʹ��¡�</p>
        </div>
    </div>
    
    <!-- ���Ĺ��� -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <h2 class="h3 mb-4 text-center">���Ĺ���</h2>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="icon-box mb-4">
                                <i class="fas fa-robot fa-3x text-primary"></i>
                            </div>
                            <h3 class="h5 card-title">��Ȼ���Դ���</h3>
                            <p class="card-text">ǿ�����Ȼ���Դ���������֧���ı���������з�����ʵ��ʶ��ȹ��ܡ�</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="icon-box mb-4">
                                <i class="fas fa-image fa-3x text-primary"></i>
                            </div>
                            <h3 class="h5 card-title">������Ӿ�</h3>
                            <p class="card-text">�Ƚ���ͼ��ʶ��ʹ�������֧�������⡢����ʶ�𡢳������ȹ��ܡ�</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="icon-box mb-4">
                                <i class="fas fa-brain fa-3x text-primary"></i>
                            </div>
                            <h3 class="h5 card-title">����ѧϰ</h3>
                            <p class="card-text">ǿ��Ļ���ѧϰ��ܣ�֧�����ݷ�����Ԥ�⽨ģ���쳣���ȹ��ܡ�</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ��Ȼ���Դ��� -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="row g-0">
                    <div class="col-md-6">
                        <div class="card-body p-5">
                            <h2 class="h3 mb-4">��Ȼ���Դ���</h2>
                            <p class="mb-4">���ǵ���Ȼ���Դ������ܹ����������������ԣ����������ı���������ȡ�м�ֵ����Ϣ��</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> �ı���������з���
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> ����ʵ��ʶ�����ϵ��ȡ
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> �Զ��ı�ժҪ������
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> �����Է��������
                                </li>
                                <li>
                                    <i class="fas fa-check text-primary me-2"></i> �ʴ�ϵͳ��Ի�������
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 bg-light d-flex align-items-center">
                        <img src="{{ asset("images/features/nlp.jpg") }}" class="img-fluid" alt="��Ȼ���Դ���">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ������Ӿ� -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="row g-0">
                    <div class="col-md-6 bg-light d-flex align-items-center order-md-1 order-2">
                        <img src="{{ asset("images/features/cv.jpg") }}" class="img-fluid" alt="������Ӿ�">
                    </div>
                    <div class="col-md-6 order-md-2 order-1">
                        <div class="card-body p-5">
                            <h2 class="h3 mb-4">������Ӿ�</h2>
                            <p class="mb-4">���ǵļ�����Ӿ������ܹ����ͷ���ͼ�����Ƶ���ݣ�Ϊ���ṩǿ����Ӿ����ܡ�</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> ͼ�������ʶ��
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> ��������׷��
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> ����ʶ�������
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> ���������ָ�
                                </li>
                                <li>
                                    <i class="fas fa-check text-primary me-2"></i> ��Ƶ���������
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ����ѧϰ -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="row g-0">
                    <div class="col-md-6">
                        <div class="card-body p-5">
                            <h2 class="h3 mb-4">����ѧϰ</h2>
                            <p class="mb-4">���ǵĻ���ѧϰ�����ܹ���������ѧϰ���ɺ�ģʽ��Ϊ���ṩ���ܻ��ľ���֧�֡�</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> Ԥ������뽨ģ
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> �쳣��������թ
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> �Ƽ�ϵͳ����Ի�
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> ������������
                                </li>
                                <li>
                                    <i class="fas fa-check text-primary me-2"></i> ʱ�������Ԥ��
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 bg-light d-flex align-items-center">
                        <img src="{{ asset("images/features/ml.jpg") }}" class="img-fluid" alt="����ѧϰ">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- API ���� -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <h2 class="h3 mb-4 text-center">API ����</h2>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <p class="mb-4">�����ṩ�ḻ�� API �ӿڣ��������ɼ��� AI ����������Ӧ���С�</p>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-code fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="h5">RESTful API</h4>
                                    <p>��׼�� RESTful API �ӿڣ�֧�ֶ��ֱ�����Ժ�ƽ̨��</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-plug fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="h5">SDK ֧��</h4>
                                    <p>�ṩ���ֱ�����Ե� SDK���򻯼��ɹ��̡�</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-lock fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="h5">��ȫ��֤</h4>
                                    <p>ǿ��İ�ȫ��֤���ƣ������������ݺ�����</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-tachometer-alt fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="h5">������</h4>
                                    <p>�����ܵ� API ����֧�ָ߲����͵��ӳ١�</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <a href="{{ route("api-docs") }}" class="btn btn-primary">
                            <i class="fas fa-book me-1"></i> �鿴 API �ĵ�
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ��Ա��Ȩ -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <h2 class="h3 mb-4 text-center">��Ա��Ȩ</h2>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box me-3">
                                    <i class="fas fa-rocket text-primary"></i>
                                </div>
                                <h3 class="h5 card-title mb-0">�������</h3>
                            </div>
                            <p class="card-text">��Ա�û����и��ߵ� API �������Ͳ�����������</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> ���� API ���ô���
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> ���߲���������
                                </li>
                                <li>
                                    <i class="fas fa-check text-success me-2"></i> ����洢�ռ�
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box me-3">
                                    <i class="fas fa-star text-primary"></i>
                                </div>
                                <h3 class="h5 card-title mb-0">�߼�����</h3>
                            </div>
                            <p class="card-text">��Ա�û�����ʹ�ø���߼����ܺ�ģ�͡�</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> �߼� AI ģ��
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> �Զ���ѵ��
                                </li>
                                <li>
                                    <i class="fas fa-check text-success me-2"></i> ��������
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box me-3">
                                    <i class="fas fa-headset text-primary"></i>
                                </div>
                                <h3 class="h5 card-title mb-0">����֧��</h3>
                            </div>
                            <p class="card-text">��Ա�û��������ȼ���֧�ֺ���ѯ����</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> ������Ӧ
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> ר������֧��
                                </li>
                                <li>
                                    <i class="fas fa-check text-success me-2"></i> ���ƻ��������
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="{{ route("pricing") }}" class="btn btn-primary">
                    <i class="fas fa-tag me-1"></i> �鿴��Ա�۸�
                </a>
            </div>
        </div>
    </div>
    
    <!-- ��ҵ������� -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <h2 class="h3 mb-4 text-center">��ҵ�������</h2>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="h5 card-title">������ҵ</h3>
                            <p class="card-text">Ϊ������ҵ�ṩ������������թ��⡢���ܿͷ��Ƚ��������</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> �������������
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> ��թ��������
                                </li>
                                <li>
                                    <i class="fas fa-check text-primary me-2"></i> ���ܿͷ�����ѯ
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="h5 card-title">������ҵ</h3>
                            <p class="card-text">Ϊ������ҵ�ṩ���Ի��Ƽ����������ͻ������Ƚ��������</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> ���Ի��Ƽ�ϵͳ
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> ���ܿ�����
                                </li>
                                <li>
                                    <i class="fas fa-check text-primary me-2"></i> �ͻ���Ϊ����
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="h5 card-title">ҽ����ҵ</h3>
                            <p class="card-text">Ϊҽ����ҵ�ṩ����Ԥ�⡢ҽѧӰ���������������Ƚ��������</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> ����Ԥ�������
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> ҽѧӰ�����
                                </li>
                                <li>
                                    <i class="fas fa-check text-primary me-2"></i> �������ݹ���
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="h5 card-title">������ҵ</h3>
                            <p class="card-text">Ϊ������ҵ�ṩԤ����ά�����������ơ������Ż��Ƚ��������</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> Ԥ����ά��
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i> ������������
                                </li>
                                <li>
                                    <i class="fas fa-check text-primary me-2"></i> ���������Ż�
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ��ʼʹ�� -->
    <div class="row">
        <div class="col-lg-8 mx-auto text-center">
            <h2 class="h3 mb-4">��ʼʹ�� AlingAi</h2>
            <p class="mb-4">����ע�ᣬ����ǿ��� AI ���ܣ���������ҵ��չ��</p>
            <div class="d-flex justify-content-center">
                <a href="{{ route("register") }}" class="btn btn-primary me-2">
                    <i class="fas fa-user-plus me-1"></i> ���ע��
                </a>
                <a href="{{ route("contact") }}" class="btn btn-outline-primary">
                    <i class="fas fa-envelope me-1"></i> ��ϵ����
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .icon-box {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background-color: rgba(13, 110, 253, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }
</style>
@endsection
