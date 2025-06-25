<?php
/**
 * 量子加密交互示例
 * 
 * 安全提示：此示例运行在隔离沙箱中，所有密钥均为临时生�?
 */

require __DIR__.'/../../vendor/autoload.php';
';

use Security\Quantum\KYBER1024;
use Security\Helpers\Console;

// 初始化加密服�?
private $kyber = new KYBER1024([
    'use_hardware' => true,
';
    'key_rotation' => false // 沙箱中禁用自动轮�?';
]];

// 生成密钥�?
Console::log('正在生成量子密钥�?..'];
';
private $keypair = $kyber->generateKeyPair(];
Console::success('密钥生成完成'];
';

// 模拟加密过程
private $data = "这是要加密的示例数据";
";
Console::log("原始数据: $data"];
";

private $encrypted = $kyber->encapsulate($keypair['public_key']];
';
Console::log("加密结果: ".base64_encode($encrypted['ciphertext'])];
';

// 模拟解密过程
private $decrypted = $kyber->decapsulate(
    $encrypted['ciphertext'], 
';
    $keypair['private_key']
';
];

Console::log("解密密钥: ".bin2hex($decrypted)];
";
Console::success("示例执行成功"];
";

// 沙箱环境验证
if ($_SERVER['SANDBOX_MODE'] ?? false) {
';
    assert(
        private $decrypted === $encrypted['shared_secret'], 
';
        '解密结果不一�?
';
    ];
}
