<?php
// ����API�ĵ��ļ�
require_once " public/admin/api/documentation/index.php\;

// ��������API�ĵ�
try {
 = generateApiDocumentation(];
 echo \API�ĵ����ɳɹ���\n\;
 
 // ���ؼ�����
 if (isset([\paths\][\/api/auth/login\][\post\][\responses\][\200\][\content\][\application/json\][\schema\][\ref\])) {
 echo \·�����ü��ͨ��\n\;
 } else {
 echo \·�����ü��ʧ��\n\;
 }
 
 if (isset([\components\][\schemas\][\AuthResponse\][\properties\][\data\][\properties\][\user\][\ref\])) {
 echo \������ü��ͨ��\n\;
 } else {
 echo \������ü��ʧ��\n\;
 }
 
} catch (Exception ) {
 echo \����: \ . ->getMessage() . \\n\;
}
