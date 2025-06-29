# ���ݿⰲȫϵͳ

��ϵͳ�ṩ��ȫ������ݿⰲȫ�������ܣ����������ơ���SQLע�롢�������ơ������־�����ݿ����ǽ�ȹ��ܡ�

## �����ص�

1. **�����ƹ���**����ز���ֹ�����ƽⳢ�ԣ��Զ�������IP�����������
2. **SQLע�����**����Ⲣ��ֹSQLע�빥������¼���ɲ�ѯ��
3. **��������**������ÿ��IP����������ݿ�����������ֹ���Ӻľ�������
4. **�����־**����¼�������ݿ������������ѯ���޸ĺ�ɾ��������
5. **���ݿ����ǽ**�����ڹ���Ĳ�ѯ���ˣ���ֹΣ�ղ�����
6. **����ѯ���**���Զ���ֹ��ʱ�����еĲ�ѯ����ֹ��Դ�ľ���
7. **�ṹ�仯���**��������ݿ�ṹ�仯����ʱ����δ��Ȩ���޸ġ�
8. **©��ɨ��**������ɨ�����ݿ�©�����ṩ�޸����顣

## ��װ������

### 1. �������ݿ�Ǩ��

```bash
php artisan migrate
```

�⽫�������б�Ҫ�����ݿ��

### 2. ע������ṩ��

�� `config/app.php` �ļ��е� `providers` ��������ӣ�

```php
App\Providers\DatabaseSecurityServiceProvider::class,
```

### 3. ע���м��

�� `app/Http/Kernel.php` �ļ��е� `$routeMiddleware` ��������ӣ�

```php
'db.security' => \App\Http\Middleware\DatabaseSecurityMiddleware::class,
```

### 4. Ӧ���м��

����Ҫ������·����Ӧ���м����

```php
Route::group(['middleware' => ['db.security']], function () {
    // �ܱ�����·��
});
```

### 5. ���üƻ�����

ȷ���ڷ�������������Laravel��������

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## ����ѡ��

��������ѡ��� `config/database_security.php` �ļ��У������Ը�����Ҫ������Щ���á�

### ��Ҫ������

- **brute_force**: �����ƹ�������
- **sql_injection**: SQLע���������
- **connection_limits**: ������������
- **audit**: �����־����
- **firewall**: ����ǽ��������
- **monitoring**: �������
- **backup**: ��������
- **vulnerability_scan**: ©��ɨ������

## ʹ�÷���

### �ֶ����а�ȫ���

```bash
# �������а�ȫ�������
php artisan db:security-monitor

# ֻ��ֹ��ʱ�����еĲ�ѯ
php artisan db:security-monitor --kill-long-queries

# ֻ������ݿ�ṹ�仯
php artisan db:security-monitor --monitor-changes

# ������ƴ�����
php artisan db:security-monitor --setup-triggers

# �������ݿ����ǽ
php artisan db:security-monitor --setup-firewall
```

### �鿴��ȫ��־

���а�ȫ�¼�����¼�� `database_security_logs` ���У�������ͨ����������ֱ�Ӳ�ѯ���ݿ����鿴��Щ��־��

### �������ǽ����

������ͨ�� `database_firewall_rules` ����ӡ��޸Ļ�ɾ������ǽ����

## ע������

1. �״�ʹ��ʱ�������� `php artisan db:security-monitor --setup-triggers` �� `php artisan db:security-monitor --setup-firewall` ��������ƴ������ͷ���ǽ����
2. �����־���ܻ�ռ�ô����洢�ռ䣬�붨���������־��
3. ������������ʹ��ǰ�������ڲ��Ի����в������й��ܡ�
4. ĳЩ����ǽ������ܻ���ֹ�Ϸ������������ʵ�������������

## ��ȫ���ʵ��

1. ���ڸ������ݿ����룬ʹ��ǿ���롣
2. �������ݿ��û�Ȩ�ޣ���ѭ��СȨ��ԭ��
3. ���ڱ������ݿ⣬���Իָ����̡�
4. �������ݿ�������£�Ӧ�ð�ȫ������
5. ʹ�ü������ӣ�SSL/TLS���������ݿ⡣
6. ����������ݿ��û���Ȩ�ޡ�
7. ������ݿ����ܺ��쳣���
