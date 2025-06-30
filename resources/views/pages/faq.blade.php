@extends("layouts.app")

@section("title", "��������")

@section("content")
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 mb-4">��������</h1>
            <p class="lead text-muted">�˽���� AlingAi ƽ̨�ĳ�������ͽ��</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- ������ -->
            <div class="card border-0 shadow-sm mb-5">
                <div class="card-body p-4">
                    <form action="#" method="GET" class="d-flex">
                        <input type="text" class="form-control form-control-lg" placeholder="��������..." name="q">
                        <button type="submit" class="btn btn-primary ms-2">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- FAQ ���� -->
            <ul class="nav nav-pills justify-content-center mb-5">
                <li class="nav-item">
                    <a class="nav-link active" href="#general" data-bs-toggle="tab">һ������</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#account" data-bs-toggle="tab">�˻����</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#billing" data-bs-toggle="tab">�Ʒ��붩��</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#api" data-bs-toggle="tab">API ���</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#technical" data-bs-toggle="tab">����֧��</a>
                </li>
            </ul>
            
            <!-- FAQ ���� -->
            <div class="tab-content">
                <!-- һ������ -->
                <div class="tab-pane fade show active" id="general">
                    <div class="accordion" id="accordionGeneral">
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingG1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseG1" aria-expanded="true" aria-controls="collapseG1">
                                    ʲô�� AlingAi��
                                </button>
                            </h2>
                            <div id="collapseG1" class="accordion-collapse collapse show" aria-labelledby="headingG1" data-bs-parent="#accordionGeneral">
                                <div class="accordion-body">
                                    AlingAi ��һ���˹�����ƽ̨���ṩ��Ȼ���Դ���������Ӿ��ͻ���ѧϰ�� AI ���ܡ����ǵ�Ŀ���ǰ�����ҵ�Ϳ��������ɼ��� AI ���������ǵ�Ӧ���У���߹���Ч�ʺʹ����ֵ��
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingG2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseG2" aria-expanded="false" aria-controls="collapseG2">
                                    AlingAi �ʺ���Щ�û���
                                </button>
                            </h2>
                            <div id="collapseG2" class="accordion-collapse collapse" aria-labelledby="headingG2" data-bs-parent="#accordionGeneral">
                                <div class="accordion-body">
                                    AlingAi �ʺϸ������͵��û���������
                                    <ul>
                                        <li>��ҵ����Ҫ AI ����������Ż�ҵ�����̺����Ч��</li>
                                        <li>�����ߣ�ϣ����Ӧ���м��� AI ����</li>
                                        <li>�о���Ա����Ҫǿ��� AI ���߽����о���ʵ��</li>
                                        <li>��ҵ��˾��Ѱ����ٲ��� AI ���ܶ��������Ͷ��</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingG3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseG3" aria-expanded="false" aria-controls="collapseG3">
                                    ��ο�ʼʹ�� AlingAi��
                                </button>
                            </h2>
                            <div id="collapseG3" class="accordion-collapse collapse" aria-labelledby="headingG3" data-bs-parent="#accordionGeneral">
                                <div class="accordion-body">
                                    ��ʼʹ�� AlingAi �ǳ��򵥣�
                                    <ol>
                                        <li>ע��һ���˻�</li>
                                        <li>ѡ���ʺ�������Ķ��ļƻ�</li>
                                        <li>��ȡ API ��Կ</li>
                                        <li>�������ǵ��ĵ����� API</li>
                                        <li>��ʼʹ�� AI ����</li>
                                    </ol>
                                    ���ǻ��ṩ��ϸ���ĵ���ʾ�����룬�������������֡�
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm">
                            <h2 class="accordion-header" id="headingG4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseG4" aria-expanded="false" aria-controls="collapseG4">
                                    AlingAi ֧����Щ���ԣ�
                                </button>
                            </h2>
                            <div id="collapseG4" class="accordion-collapse collapse" aria-labelledby="headingG4" data-bs-parent="#accordionGeneral">
                                <div class="accordion-body">
                                    AlingAi ֧�ֶ������ԣ��������ġ�Ӣ�ġ����ġ����ġ����ġ����ġ��������ĵ���Ҫ���ԡ����ǵ���Ȼ���Դ����ܿ��Դ�����Щ���Ե��ı������з��롢��з������ı�����Ȳ�����
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- �˻���� -->
                <div class="tab-pane fade" id="account">
                    <div class="accordion" id="accordionAccount">
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingA1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseA1" aria-expanded="true" aria-controls="collapseA1">
                                    ��δ����˻���
                                </button>
                            </h2>
                            <div id="collapseA1" class="accordion-collapse collapse show" aria-labelledby="headingA1" data-bs-parent="#accordionAccount">
                                <div class="accordion-body">
                                    �����˻��ǳ��򵥣�ֻ������վ���Ͻǵ�"ע��"��ť����д��Ҫ����Ϣ��������ʼ�������ȣ�Ȼ����ָʾ���ע�����̼��ɡ�ע��ɹ��������յ�һ��ȷ���ʼ�������ʼ��е����Ӽ��������˻���
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingA2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseA2" aria-expanded="false" aria-controls="collapseA2">
                                    ����޸��˻���Ϣ��
                                </button>
                            </h2>
                            <div id="collapseA2" class="accordion-collapse collapse" aria-labelledby="headingA2" data-bs-parent="#accordionAccount">
                                <div class="accordion-body">
                                    ��¼�󣬵�����Ͻǵĸ���ͷ��ѡ��"��������"���������ڸ�������ҳ���޸������˻���Ϣ����������ͷ����ϵ��ʽ�ȡ��޸���ɺ󣬵��"�������"��ť���ɡ�
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingA3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseA3" aria-expanded="false" aria-controls="collapseA3">
                                    ����޸����룿
                                </button>
                            </h2>
                            <div id="collapseA3" class="accordion-collapse collapse" aria-labelledby="headingA3" data-bs-parent="#accordionAccount">
                                <div class="accordion-body">
                                    ��¼�󣬵�����Ͻǵĸ���ͷ��ѡ��"��ȫ����"���ڰ�ȫ����ҳ�棬�������޸����롣����Ҫ���뵱ǰ����������룬Ȼ����"��������"��ť���ɡ�
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm">
                            <h2 class="accordion-header" id="headingA4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseA4" aria-expanded="false" aria-controls="collapseA4">
                                    ���ɾ���˻���
                                </button>
                            </h2>
                            <div id="collapseA4" class="accordion-collapse collapse" aria-labelledby="headingA4" data-bs-parent="#accordionAccount">
                                <div class="accordion-body">
                                    �������ɾ���˻������¼�������Ͻǵĸ���ͷ��ѡ��"��ȫ����"����ҳ��ײ��ҵ�"ɾ���˻�"ѡ���ע�⣬ɾ���˻��ǲ�����Ĳ����������������ݽ�������ɾ������������κ����⣬����ϵ���ǵĿͷ��Ŷӡ�
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- �Ʒ��붩�� -->
                <div class="tab-pane fade" id="billing">
                    <div class="accordion" id="accordionBilling">
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingB1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseB1" aria-expanded="true" aria-controls="collapseB1">
                                    AlingAi �ļ۸���Σ�
                                </button>
                            </h2>
                            <div id="collapseB1" class="accordion-collapse collapse show" aria-labelledby="headingB1" data-bs-parent="#accordionBilling">
                                <div class="accordion-body">
                                    AlingAi �ṩ���ּ۸񷽰�������Ѽƻ�����ҵ������������۸�ȡ��������ʹ�������� API ���ô������������������洢�ռ�ȡ������������ǵ�<a href="{{ route("pricing") }}">�۸�ҳ��</a>�鿴��ϸ�ļ۸���Ϣ��
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingB2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseB2" aria-expanded="false" aria-controls="collapseB2">
                                    ��������򽵼��ҵĶ��ģ�
                                </button>
                            </h2>
                            <div id="collapseB2" class="accordion-collapse collapse" aria-labelledby="headingB2" data-bs-parent="#accordionBilling">
                                <div class="accordion-body">
                                    ��¼�󣬽���"��Ա����"ҳ�棬�����Բ鿴��ǰ�Ķ��ļƻ������������򽵼��������������µļƻ���������Ч����������������á��������µļƻ����ڵ�ǰ�Ʒ����ڽ�������Ч��
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingB3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseB3" aria-expanded="false" aria-controls="collapseB3">
                                    ֧����Щ���ʽ��
                                </button>
                            </h2>
                            <div id="collapseB3" class="accordion-collapse collapse" aria-labelledby="headingB3" data-bs-parent="#accordionBilling">
                                <div class="accordion-body">
                                    ����֧�ֶ��ָ��ʽ��������
                                    <ul>
                                        <li>���ÿ�/��ǿ���Visa��MasterCard��UnionPay�ȣ�</li>
                                        <li>֧����</li>
                                        <li>΢��֧��</li>
                                        <li>����ת�ˣ�����������ҵ�ͻ���</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm">
                            <h2 class="accordion-header" id="headingB4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseB4" aria-expanded="false" aria-controls="collapseB4">
                                    �Ƿ��ṩ��Ʊ��
                                </button>
                            </h2>
                            <div id="collapseB4" class="accordion-collapse collapse" aria-labelledby="headingB4" data-bs-parent="#accordionBilling">
                                <div class="accordion-body">
                                    �ǵģ������ṩ��ֵ˰��Ʊ����������"��Ա����"��"��Ʊ����"ҳ�����뷢Ʊ�����ṩ׼ȷ�ķ�Ʊ��Ϣ����̧ͷ��˰�ŵȡ���Ʊ��������� 3-5 ���������ڿ��߲����͸�����
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- API ��� -->
                <div class="tab-pane fade" id="api">
                    <div class="accordion" id="accordionApi">
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingAPI1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAPI1" aria-expanded="true" aria-controls="collapseAPI1">
                                    ��λ�ȡ API ��Կ��
                                </button>
                            </h2>
                            <div id="collapseAPI1" class="accordion-collapse collapse show" aria-labelledby="headingAPI1" data-bs-parent="#accordionApi">
                                <div class="accordion-body">
                                    ��¼�󣬽���"API ����"ҳ�棬���"���� API ��Կ"��ť����д��Ҫ����Ϣ������Կ���ơ�ʹ�÷�Χ�ȣ�Ȼ����"����"��ť���ɻ�ȡ API ��Կ�������Ʊ������� API ��Կ����Ҫй¶�����ˡ�
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingAPI2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAPI2" aria-expanded="false" aria-controls="collapseAPI2">
                                    ���ʹ�� API��
                                </button>
                            </h2>
                            <div id="collapseAPI2" class="accordion-collapse collapse" aria-labelledby="headingAPI2" data-bs-parent="#accordionApi">
                                <div class="accordion-body">
                                    ʹ�� API ǰ������Ҫ�Ȼ�ȡ API ��Կ��Ȼ�������԰������ǵ�<a href="{{ route("api-docs") }}">API �ĵ�</a>�е�˵����ʹ����ѡ��ı�����Է��� HTTP �������ǵ� API �˵㡣�����ṩ��ϸ���ĵ���ʾ�����룬���������ټ������ǵ� API��
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingAPI3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAPI3" aria-expanded="false" aria-controls="collapseAPI3">
                                    API ����������ʲô��
                                </button>
                            </h2>
                            <div id="collapseAPI3" class="accordion-collapse collapse" aria-labelledby="headingAPI3" data-bs-parent="#accordionApi">
                                <div class="accordion-body">
                                    API ��������ȡ�������Ķ��ļƻ�����ͬ�ļƻ��в�ͬ�� API ���ô����Ͳ������������ơ���������"��Ա����"�鿴���� API ʹ����������ơ��������Ҫ���ߵ����ƣ������������Ķ��ļƻ�����ϵ���ǵ������Ŷӻ�ȡ���Ʒ�����
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm">
                            <h2 class="accordion-header" id="headingAPI4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAPI4" aria-expanded="false" aria-controls="collapseAPI4">
                                    API ֧����Щ������ԣ�
                                </button>
                            </h2>
                            <div id="collapseAPI4" class="accordion-collapse collapse" aria-labelledby="headingAPI4" data-bs-parent="#accordionApi">
                                <div class="accordion-body">
                                    ���ǵ� API �ǻ��� RESTful �ܹ��ģ��������κ�֧�� HTTP ����ı������һ��ʹ�á������ṩ���ֱ�����Ե� SDK������ Python��Java��JavaScript��PHP��Go �ȣ����������ټ������ǵ� API��
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- ����֧�� -->
                <div class="tab-pane fade" id="technical">
                    <div class="accordion" id="accordionTechnical">
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingT1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseT1" aria-expanded="true" aria-controls="collapseT1">
                                    ��λ�ȡ����֧�֣�
                                </button>
                            </h2>
                            <div id="collapseT1" class="accordion-collapse collapse show" aria-labelledby="headingT1" data-bs-parent="#accordionTechnical">
                                <div class="accordion-body">
                                    ������ͨ�����·�ʽ��ȡ����֧�֣�
                                    <ul>
                                        <li>�������ǵ�<a href="{{ route("api-docs") }}">�ĵ�</a>��<a href="{{ route("tutorials") }}">�̳�</a></li>
                                        <li>�ڿ���������ύ����</li>
                                        <li>�����ʼ��� support@alingai.com</li>
                                        <li>������֧������ +86 10 8888 8888 ת 2</li>
                                    </ul>
                                    �߼���Ա����ҵ��Ա�����������ȼ���֧�ֺ�ר������֧�֡�
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingT2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseT2" aria-expanded="false" aria-controls="collapseT2">
                                    ����֧�ֵ���Ӧʱ���Ƕ�ã�
                                </button>
                            </h2>
                            <div id="collapseT2" class="accordion-collapse collapse" aria-labelledby="headingT2" data-bs-parent="#accordionTechnical">
                                <div class="accordion-body">
                                    ����֧�ֵ���Ӧʱ��ȡ�������Ķ��ļƻ���
                                    <ul>
                                        <li>����û���24-48 Сʱ</li>
                                        <li>������Ա��12-24 Сʱ</li>
                                        <li>�߼���Ա��6-12 Сʱ</li>
                                        <li>��ҵ��Ա��1-4 Сʱ</li>
                                    </ul>
                                    �������⽫������ȴ���
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingT3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseT3" aria-expanded="false" aria-controls="collapseT3">
                                    �Ƿ��ṩ��ѵ����ѯ����
                                </button>
                            </h2>
                            <div id="collapseT3" class="accordion-collapse collapse" aria-labelledby="headingT3" data-bs-parent="#accordionTechnical">
                                <div class="accordion-body">
                                    �ǵģ�����Ϊ��ҵ�ͻ��ṩ��ѵ����ѯ���񣬰��������õ�ʹ�����ǵĲ�Ʒ�ͷ�����ѵ���ݰ��� API ʹ�á����ʵ�����߼����ܵȡ��������Ҫ��ѵ����ѯ��������ϵ���ǵ������Ŷӡ�
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm">
                            <h2 class="accordion-header" id="headingT4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseT4" aria-expanded="false" aria-controls="collapseT4">
                                    ��α��� Bug ��������ܽ��飿
                                </button>
                            </h2>
                            <div id="collapseT4" class="accordion-collapse collapse" aria-labelledby="headingT4" data-bs-parent="#accordionTechnical">
                                <div class="accordion-body">
                                    ������ͨ�����·�ʽ���� Bug ��������ܽ��飺
                                    <ul>
                                        <li>�ڿ���������ύ����</li>
                                        <li>�����ʼ��� feedback@alingai.com</li>
                                        <li>�����ǵ�������̳�з���</li>
                                    </ul>
                                    ���Ƿǳ������û��ķ����������濼��ÿһ�����顣
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- δ�ҵ��� -->
    <div class="row mt-5">
        <div class="col-lg-8 mx-auto text-center">
            <h2 class="h3 mb-4">û���ҵ�����Ҫ�Ĵ𰸣�</h2>
            <p class="mb-4">���ǵĿͷ��Ŷ���ʱΪ���ṩ������</p>
            <div class="d-flex justify-content-center">
                <a href="{{ route("contact") }}" class="btn btn-primary me-2">
                    <i class="fas fa-envelope me-1"></i> ��ϵ����
                </a>
                <a href="{{ route("support") }}" class="btn btn-outline-primary">
                    <i class="fas fa-headset me-1"></i> ��ȡ֧��
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
