<?php
/**
 * ��WebSocket·�ɴ�����
 * ���� /ws ·����WebSocket����
 */

// ������Ӧͷ
header('HTTP/1.1 101 Switching Protocols'];
header('Upgrade: websocket'];
header('Connection: Upgrade'];
header('Sec-WebSocket-Accept: ' . base64_encode(pack('H*', sha1($_SERVER['HTTP_SEC_WEBSOCKET_KEY'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')))];

// ģ��WebSocket������Ҫʵ�ʵ�WebSocket�⣩
echo "WebSocket�����ɹ�\n";

// ����Ӧ�ý�����ʵ��WebSocket����
// ����PHP���÷��������ƣ���ֻ��һ��ռλ��
exit;
