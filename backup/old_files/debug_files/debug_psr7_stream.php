<?php
/**
 * 验证 PSR-7 响应体的工作原理
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== PSR-7 Response Body Investigation ===\n";

$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();

// 测试 1：基本写入和读取
echo "\n--- Test 1: Basic write and read ---\n";
$response1 = $psr17Factory->createResponse();
$testContent = "Hello World Test Content";

echo "Writing content: '$testContent'\n";
$response1->getBody()->write($testContent);

echo "Reading with getContents():\n";
$content1 = $response1->getBody()->getContents();
echo "Content: '$content1' (length: " . strlen($content1) . ")\n";

echo "Reading again with getContents():\n";
$content2 = $response1->getBody()->getContents();
echo "Content: '$content2' (length: " . strlen($content2) . ")\n";

// 测试 2：使用 rewind
echo "\n--- Test 2: Using rewind ---\n";
$response2 = $psr17Factory->createResponse();
$response2->getBody()->write($testContent);

echo "After write, before rewind:\n";
$content3 = $response2->getBody()->getContents();
echo "Content: '$content3' (length: " . strlen($content3) . ")\n";

echo "After rewind:\n";
$response2->getBody()->rewind();
$content4 = $response2->getBody()->getContents();
echo "Content: '$content4' (length: " . strlen($content4) . ")\n";

// 测试 3：检查响应体的位置
echo "\n--- Test 3: Stream position ---\n";
$response3 = $psr17Factory->createResponse();
$stream = $response3->getBody();

echo "Initial position: " . $stream->tell() . "\n";
echo "Initial size: " . $stream->getSize() . "\n";

$stream->write($testContent);
echo "After write position: " . $stream->tell() . "\n";
echo "After write size: " . $stream->getSize() . "\n";

$content5 = $stream->getContents();
echo "After getContents position: " . $stream->tell() . "\n";
echo "Content: '$content5' (length: " . strlen($content5) . ")\n";

$stream->rewind();
echo "After rewind position: " . $stream->tell() . "\n";
$content6 = $stream->getContents();
echo "Content after rewind: '$content6' (length: " . strlen($content6) . ")\n";

echo "\n=== Conclusion ===\n";
echo "The issue is that getContents() reads from current position to end.\n";
echo "After writing, the position is at the end, so getContents() returns empty.\n";
echo "We need to rewind() before reading, or use a different approach.\n";
