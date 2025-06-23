<?php
/**
 * 简化WebSocket服务器
 * 使用PHP内置服务器提供基础WebSocket支持
 */

require_once __DIR__ . '/vendor/autoload.php';

// 读取环境配置
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}

// WebSocket配置
$host = '127.0.0.1';
$port = 8080;

echo "==================================================\n";
echo "AlingAi Pro WebSocket服务器\n";
echo "==================================================\n";
echo "启动地址: ws://{$host}:{$port}\n";
echo "启动时间: " . date('Y-m-d H:i:s') . "\n";
echo "支持端点:\n";
echo "  - /ws/chat (聊天消息)\n";
echo "  - /ws/notifications (系统通知)\n";
echo "  - /ws/monitoring (系统监控)\n";
echo "==================================================\n";

// 创建socket服务器
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socket, $host, $port);
socket_listen($socket, 5);

$clients = [];

echo "WebSocket服务器运行中...\n";
echo "前端连接地址: ws://{$host}:{$port}/ws\n";
echo "按 Ctrl+C 停止服务器\n\n";

function mask($text) {
    $b1 = 0x80 | (0x1 & 0x0f);
    $length = strlen($text);
    
    if ($length <= 125) {
        $header = pack('CC', $b1, $length);
    } elseif ($length > 125 && $length < 65536) {
        $header = pack('CCn', $b1, 126, $length);
    } elseif ($length >= 65536) {
        $header = pack('CCNN', $b1, 127, $length);
    }
    return $header.$text;
}

function unmask($text) {
    $length = ord($text[1]) & 127;
    if ($length == 126) {
        $masks = substr($text, 4, 4);
        $data = substr($text, 8);
    } elseif ($length == 127) {
        $masks = substr($text, 10, 4);
        $data = substr($text, 14);
    } else {
        $masks = substr($text, 2, 4);
        $data = substr($text, 6);
    }
    $text = "";
    for ($i = 0; $i < strlen($data); ++$i) {
        $text .= $data[$i] ^ $masks[$i%4];
    }
    return $text;
}

function doHandshake($received_header, $client_socket, $host, $port) {
    $headers = array();
    $lines = preg_split("/\r\n/", $received_header);
    foreach($lines as $line) {
        $line = chop($line);
        if(preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
            $headers[$matches[1]] = $matches[2];
        }
    }

    $secKey = $headers['Sec-WebSocket-Key'];
    $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
    
    $response = "HTTP/1.1 101 Switching Protocols\r\n" .
                "Upgrade: websocket\r\n" .
                "Connection: Upgrade\r\n" .
                "Sec-WebSocket-Accept: $secAccept\r\n\r\n";
    
    socket_write($client_socket, $response, strlen($response));
    return true;
}

while (true) {
    $changed = array_merge([$socket], $clients);
    socket_select($changed, $write = NULL, $except = NULL, 0, 10);
    
    if (in_array($socket, $changed)) {
        $client_socket = socket_accept($socket);
        $clients[] = $client_socket;
        
        $header = socket_read($client_socket, 1024);
        doHandshake($header, $client_socket, $host, $port);
        
        // 发送欢迎消息
        $welcome = json_encode([
            'type' => 'welcome',
            'message' => 'WebSocket连接已建立',
            'timestamp' => time()
        ]);
        socket_write($client_socket, mask($welcome), strlen(mask($welcome)));
        
        echo "[" . date('H:i:s') . "] 新客户端连接\n";
    }
    
    foreach ($clients as $key => $client) {
        if (in_array($client, $changed)) {
            $bytes = @socket_recv($client, $buffer, 2048, 0);
            if ($bytes == 0) {
                unset($clients[$key]);
                echo "[" . date('H:i:s') . "] 客户端断开连接\n";
                continue;
            }
            
            $received_text = unmask($buffer);
            $received_data = json_decode($received_text, true);
            
            if ($received_data) {
                echo "[" . date('H:i:s') . "] 收到消息: " . $received_data['type'] . "\n";
                
                // 处理不同类型的消息
                switch ($received_data['type']) {
                    case 'ping':
                        $response = json_encode(['type' => 'pong', 'timestamp' => time()]);
                        socket_write($client, mask($response), strlen(mask($response)));
                        break;
                        
                    case 'chat_message':
                        // 广播聊天消息给所有客户端
                        $response = json_encode([
                            'type' => 'chat_response',
                            'message' => "收到消息: " . $received_data['message'],
                            'timestamp' => time()
                        ]);
                        foreach ($clients as $other_client) {
                            socket_write($other_client, mask($response), strlen(mask($response)));
                        }
                        break;
                        
                    case 'quantum_ball_interaction':
                        // 处理量子球交互
                        $response = json_encode([
                            'type' => 'quantum_response',
                            'animation' => 'pulse',
                            'intensity' => 'medium',
                            'timestamp' => time()
                        ]);
                        socket_write($client, mask($response), strlen(mask($response)));
                        break;
                        
                    default:
                        $response = json_encode([
                            'type' => 'echo',
                            'data' => $received_data,
                            'timestamp' => time()
                        ]);
                        socket_write($client, mask($response), strlen(mask($response)));
                }
            }
        }
    }
}
