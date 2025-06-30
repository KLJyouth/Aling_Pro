@extends("layouts.app")

@section("title", "��������")

@section("content")
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 mb-4">���� AlingAi</h1>
            <p class="lead text-muted">����������Ϊ��ҵ�Ϳ������ṩ�Ƚ����˹����ܽ���������������Ǹ��õ�������ݡ��Զ������̲������ֵ��</p>
        </div>
    </div>
    
    <!-- ���ǵ�ʹ�� -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h2 class="h3 mb-4">���ǵ�ʹ��</h2>
                    <p>AlingAi ��ʹ����ͨ���˹����ܼ���������ҵ�͸��ˣ��������Ǹ��õ������������ݣ���߹���Ч�ʣ��������ļ�ֵ���������ţ��˹����ܲ�������һ�ּ���������һ��˼ά��ʽ�ͽ������Ĺ��ߡ�</p>
                    <p>���������ڣ�</p>
                    <ul>
                        <li>Ϊ��ҵ�ṩ���á���Ч���˹����ܽ������</li>
                        <li>�����˹����ܼ�����ʹ���ż����ø������ܹ�����</li>
                        <li>�ƶ��˹����ܼ����Ĵ��ºͷ�չ</li>
                        <li>�����˹������˲ţ��ƹ��˹����ܽ���</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ���ǵ���ʷ -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h2 class="h3 mb-4">���ǵ���ʷ</h2>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h3 class="h5 mb-0">2020��</h3>
                                <p class="text-muted mb-2">��˾����</p>
                                <p>AlingAi ��һȺ����������˹�����ר�Һ���ҵ�ҹ�ͬ������������Ϊ��ҵ�ṩ�˹����ܽ��������</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h3 class="h5 mb-0">2021��</h3>
                                <p class="text-muted mb-2">��Ʒ����</p>
                                <p>�����˵�һ����Ʒ AlingAi Platform��Ϊ��ҵ�ṩһվʽ�˹����ܽ��������</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h3 class="h5 mb-0">2022��</h3>
                                <p class="text-muted mb-2">ҵ����չ</p>
                                <p>ҵ����չ��ȫ��������У�����ͻ�����100�ҡ�</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h3 class="h5 mb-0">2023��</h3>
                                <p class="text-muted mb-2">����ͻ��</p>
                                <p>����Ȼ���Դ���ͼ�����Ӿ�����ȡ���ش���ͻ�ƣ��Ƴ��˶�����²�Ʒ��</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h3 class="h5 mb-0">2024��</h3>
                                <p class="text-muted mb-2">���ʻ���չ</p>
                                <p>��ʼ���ʻ���չ����Ʒ�ͷ��񸲸Ƕ�����Һ͵�����</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ���ǵ��Ŷ� -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h2 class="h3 mb-4">���ǵ��Ŷ�</h2>
                    <p>AlingAi ӵ��һ֧���˹�����ר�ҡ��������ʦ����Ʒ���ʦ��ҵ��ר����ɵ������Ŷӡ����ǵ��Ŷӳ�Ա����������أ�ӵ�зḻ����ҵ�����רҵ֪ʶ��</p>
                    <div class="row row-cols-1 row-cols-md-3 g-4 mt-4">
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm">
                                <img src="{{ asset("images/team/ceo.jpg") }}" class="card-img-top" alt="CEO">
                                <div class="card-body text-center">
                                    <h5 class="card-title mb-1">����</h5>
                                    <p class="text-muted small">��ʼ�� & CEO</p>
                                    <p class="card-text">�˹���������ר�ң�ӵ��10�����ϵ���ҵ���顣</p>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm">
                                <img src="{{ asset("images/team/cto.jpg") }}" class="card-img-top" alt="CTO">
                                <div class="card-body text-center">
                                    <h5 class="card-title mb-1">��ǿ</h5>
                                    <p class="text-muted small">CTO</p>
                                    <p class="card-text">ǰ�ȸ蹤��ʦ������ѧϰ����Ȼ���Դ���ר�ҡ�</p>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm">
                                <img src="{{ asset("images/team/cpo.jpg") }}" class="card-img-top" alt="CPO">
                                <div class="card-body text-center">
                                    <h5 class="card-title mb-1">����</h5>
                                    <p class="text-muted small">CPO</p>
                                    <p class="card-text">��Ʒ���ר�ң�רע���û�����Ͳ�Ʒ���¡�</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ���ǵļ�ֵ�� -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h2 class="h3 mb-4">���ǵļ�ֵ��</h2>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-lightbulb fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="h5">����</h4>
                                    <p>���ǹ�������˼ά������̽���µļ����ͽ��������</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-users fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="h5">Э��</h4>
                                    <p>���������Ŷ�Э������������ͬ�������ļ�ֵ��</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-shield-alt fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="h5">����</h4>
                                    <p>���Ǽ�ֳ���ԭ��Ӯ�ÿͻ��ͺ����������Ρ�</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-chart-line fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="h5">׿Խ</h4>
                                    <p>����׷��׿Խ��Ϊ�ͻ��ṩ��õĲ�Ʒ�ͷ���</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ��ϵ���� -->
    <div class="row">
        <div class="col-lg-8 mx-auto text-center">
            <h2 class="h3 mb-4">��ϵ����</h2>
            <p>��������κ�������飬��ӭ��ʱ��ϵ���ǡ�</p>
            <div class="d-flex justify-content-center">
                <a href="{{ route("contact") }}" class="btn btn-primary me-2">
                    <i class="fas fa-envelope me-1"></i> ��ϵ����
                </a>
                <a href="{{ route("careers") }}" class="btn btn-outline-primary">
                    <i class="fas fa-briefcase me-1"></i> ��������
                </a>
            </div>
        </div>
    </div>
</div>

<style>
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
</style>
@endsection
