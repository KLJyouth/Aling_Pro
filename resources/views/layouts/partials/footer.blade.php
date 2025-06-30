<footer class="bg-dark text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="mb-3">��������</h5>
                <p class="text-muted">AlingAi Pro��һ��ǿ����˹�����ƽ̨���ṩ�Ƚ���AI���ߺ�API���񣬰��������ߺ���ҵ����ʵ��AI���ܡ�</p>
                <div class="d-flex gap-2 mt-3">
                    <a href="#" class="text-white"><i class="fab fa-facebook-f fa-lg"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-linkedin-in fa-lg"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-github fa-lg"></i></a>
                </div>
            </div>
            <div class="col-md-2 mb-4 mb-md-0">
                <h5 class="mb-3">��Ʒ</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route("features") }}" class="text-muted text-decoration-none">����</a></li>
                    <li class="mb-2"><a href="{{ route("pricing") }}" class="text-muted text-decoration-none">�۸�</a></li>
                    <li class="mb-2"><a href="{{ route("api-docs") }}" class="text-muted text-decoration-none">API�ĵ�</a></li>
                    <li class="mb-2"><a href="{{ route("examples") }}" class="text-muted text-decoration-none">ʾ��</a></li>
                </ul>
            </div>
            <div class="col-md-2 mb-4 mb-md-0">
                <h5 class="mb-3">��Դ</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route("blog") }}" class="text-muted text-decoration-none">����</a></li>
                    <li class="mb-2"><a href="{{ route("tutorials") }}" class="text-muted text-decoration-none">�̳�</a></li>
                    <li class="mb-2"><a href="{{ route("faq") }}" class="text-muted text-decoration-none">��������</a></li>
                    <li class="mb-2"><a href="{{ route("support") }}" class="text-muted text-decoration-none">֧��</a></li>
                </ul>
            </div>
            <div class="col-md-2 mb-4 mb-md-0">
                <h5 class="mb-3">��˾</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route("about") }}" class="text-muted text-decoration-none">��������</a></li>
                    <li class="mb-2"><a href="{{ route("team") }}" class="text-muted text-decoration-none">�Ŷ�</a></li>
                    <li class="mb-2"><a href="{{ route("careers") }}" class="text-muted text-decoration-none">��Ƹ</a></li>
                    <li class="mb-2"><a href="{{ route("contact") }}" class="text-muted text-decoration-none">��ϵ����</a></li>
                </ul>
            </div>
            <div class="col-md-2 mb-4 mb-md-0">
                <h5 class="mb-3">����</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route("terms") }}" class="text-muted text-decoration-none">��������</a></li>
                    <li class="mb-2"><a href="{{ route("privacy") }}" class="text-muted text-decoration-none">��˽����</a></li>
                    <li class="mb-2"><a href="{{ route("security") }}" class="text-muted text-decoration-none">��ȫ</a></li>
                </ul>
            </div>
        </div>
        <hr class="my-4 bg-secondary">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                <p class="text-muted mb-0">&copy; {{ date("Y") }} AlingAi Pro. ��������Ȩ����</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-globe me-1"></i> ��������
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                        <li><a class="dropdown-item active" href="#">��������</a></li>
                        <li><a class="dropdown-item" href="#">English</a></li>
                        <li><a class="dropdown-item" href="#">�ձ��Z</a></li>
                        <li><a class="dropdown-item" href="#">???</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
