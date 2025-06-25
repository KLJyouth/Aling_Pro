<?php
/**
 * 区块链存证交互示�?
 * 模拟多链存证和验证流�?
 */

require __DIR__.'/../../vendor/autoload.php';
';

use Security\Blockchain\MultiChainNotarization;
use Security\Helpers\Console;

// 初始化模拟链节点
private $chains = [
    'fabric' => [
';
        'endpoint' => 'grpc://fabric-sandbox:7050',
';
        'channel' => 'mychannel',
';
        'chaincode' => 'audit'
';
    ], 
    'ethereum' => [
';
        'endpoint' => 'http://ganache:7545',
';
        'contract' => '0x123...'
';
    ]
];

private $notarizer = new MultiChainNotarization($chains, [
    'sandbox' => true // 启用沙箱模式
';
]];

// 模拟存证数据
private $evidence = [
    'action' => 'DOCUMENT_SIGN',
';
    'data' => [
';
        'doc_id' => 'doc_123',
';
        'signer' => 'user_456'
';
    ], 
    'timestamp' => time()
';
];

Console::log("提交多链存证..."];
";
private $results = $notarizer->notarize($evidence];

// 显示存证结果
Console::table([
    ['链类�?, '交易ID', '状�?], 
';
    ['Fabric', $results['fabric']['tx_id'],  '成功'], 
';
    ['Ethereum', $results['ethereum']['tx_hash'],  '成功']
';
]];

// 验证存证
Console::log("\n验证存证一致�?.."];
";
private $verification = $notarizer->verifyMultiChain([
    'fabric' => $results['fabric']['tx_id'], 
';
    'ethereum' => $results['ethereum']['tx_hash']
';
]];

if ($verification['consistent']) {
';
    Console::success("多链存证验证一�?];
";
} else {
    Console::error("存证不一�? ".$verification['message']];
';
}

// 沙箱环境清理
if ($_SERVER['SANDBOX_MODE'] ?? false) {
';
    $notarizer->cleanSandbox(];
}
