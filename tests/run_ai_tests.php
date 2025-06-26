<?php
/**
 * AlingAi Pro - AI Engine Test Runner
 * 
 * This script tests the AI engines in the AlingAi Pro system to ensure
 * they work correctly with PHP 8.1.
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define paths
define('BASE_PATH', dirname(__DIR__));
define('AI_ENGINES_PATH', BASE_PATH . '/ai-engines');

// Test results storage
$testResults = [
    'total' => 0,
    'passed' => 0,
    'failed' => 0,
    'details' => []
];

// Header
echo "=================================================\n";
echo "AlingAi Pro - AI Engine Test Runner\n";
echo "=================================================\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "=================================================\n\n";

// Test NLP engine
echo "Testing NLP Engine...\n";
testNLPEngine($testResults);

// Test CV engine
echo "\nTesting Computer Vision Engine...\n";
testCVEngine($testResults);

// Test Speech engine
echo "\nTesting Speech Engine...\n";
testSpeechEngine($testResults);

// Test Knowledge Graph engine
echo "\nTesting Knowledge Graph Engine...\n";
testKnowledgeGraphEngine($testResults);

// Print summary
echo "\n=================================================\n";
echo "Test Summary\n";
echo "=================================================\n";
echo "Total Tests: {$testResults['total']}\n";
echo "Passed: {$testResults['passed']}\n";
echo "Failed: {$testResults['failed']}\n";

// Print failed tests if any
if ($testResults['failed'] > 0) {
    echo "\nFailed Tests:\n";
    foreach ($testResults['details'] as $test) {
        if ($test['status'] === 'FAIL') {
            echo "- {$test['name']}: {$test['message']}\n";
        }
    }
}

// Generate HTML report
$reportFile = BASE_PATH . '/tests/ai_engine_test_report_' . date('Ymd_His') . '.html';
generateHtmlReport($testResults, $reportFile);
echo "\nTest report generated at: {$reportFile}\n";

/**
 * Test NLP Engine
 * 
 * @param array &$testResults Test results array
 */
function testNLPEngine(&$testResults) {
    // Test TextAnalysisEngine
    testClass('TextAnalysisEngine', 'ai-engines/nlp/TextAnalysisEngine.php', $testResults);
    
    // Test SentimentAnalyzer
    testClass('SentimentAnalyzer', 'ai-engines/nlp/SentimentAnalyzer.php', $testResults);
    
    // Test TextSummarizer
    testClass('TextSummarizer', 'ai-engines/nlp/TextSummarizer.php', $testResults);
    
    // Test NERModel
    testClass('NERModel', 'ai-engines/nlp/NERModel.php', $testResults);
    
    // Test LanguageDetector
    testClass('LanguageDetector', 'ai-engines/nlp/LanguageDetector.php', $testResults);
}

/**
 * Test CV Engine
 * 
 * @param array &$testResults Test results array
 */
function testCVEngine(&$testResults) {
    // Test ImageRecognitionEngine
    testClass('ImageRecognitionEngine', 'ai-engines/cv/ImageRecognitionEngine.php', $testResults);
    
    // Test ObjectDetectionModel
    testClass('ObjectDetectionModel', 'ai-engines/cv/ObjectDetectionModel.php', $testResults);
    
    // Test FaceRecognitionModel
    testClass('FaceRecognitionModel', 'ai-engines/cv/FaceRecognitionModel.php', $testResults);
    
    // Test OCRModel
    testClass('OCRModel', 'ai-engines/cv/OCRModel.php', $testResults);
}

/**
 * Test Speech Engine
 * 
 * @param array &$testResults Test results array
 */
function testSpeechEngine(&$testResults) {
    // Test SpeechRecognitionEngine
    testClass('SpeechRecognitionEngine', 'ai-engines/speech/SpeechRecognitionEngine.php', $testResults);
    
    // Test SpeechSynthesisEngine
    testClass('SpeechSynthesisEngine', 'ai-engines/speech/SpeechSynthesisEngine.php', $testResults);
    
    // Test FeatureExtractor
    testClass('FeatureExtractor', 'ai-engines/speech/FeatureExtractor.php', $testResults);
    
    // Test AcousticModel
    testClass('AcousticModel', 'ai-engines/speech/AcousticModel.php', $testResults);
}

/**
 * Test Knowledge Graph Engine
 * 
 * @param array &$testResults Test results array
 */
function testKnowledgeGraphEngine(&$testResults) {
    // Test KnowledgeGraphEngine
    testClass('KnowledgeGraphEngine', 'ai-engines/knowledge-graph/KnowledgeGraphEngine.php', $testResults);
    
    // Test QueryProcessor
    testClass('QueryProcessor', 'ai-engines/knowledge-graph/QueryProcessor.php', $testResults);
    
    // Test ReasoningEngine
    testClass('ReasoningEngine', 'ai-engines/knowledge-graph/ReasoningEngine.php', $testResults);
    
    // Test EntityExtractor
    testClass('EntityExtractor', 'ai-engines/knowledge-graph/EntityExtractor.php', $testResults);
}

/**
 * Test a class file
 * 
 * @param string $className Name of the class to test
 * @param string $filePath Path to the class file
 * @param array &$testResults Test results array
 */
function testClass($className, $filePath, &$testResults) {
    $fullPath = BASE_PATH . '/' . $filePath;
    
    echo "  Testing {$className}... ";
    
    // Test if file exists
    $testResults['total']++;
    if (file_exists($fullPath)) {
        $testResults['passed']++;
        $testResults['details'][] = [
            'name' => "{$className} - File exists",
            'status' => 'PASS',
            'message' => ''
        ];
        echo "File exists. ";
    } else {
        $testResults['failed']++;
        $testResults['details'][] = [
            'name' => "{$className} - File exists",
            'status' => 'FAIL',
            'message' => "File {$filePath} not found"
        ];
        echo "File not found. ";
        return;
    }
    
    // Test if file has syntax errors
    $testResults['total']++;
    $output = [];
    $returnVar = 0;
    exec("php -l {$fullPath} 2>&1", $output, $returnVar);
    
    if ($returnVar === 0) {
        $testResults['passed']++;
        $testResults['details'][] = [
            'name' => "{$className} - Syntax check",
            'status' => 'PASS',
            'message' => ''
        ];
        echo "Syntax OK. ";
    } else {
        $testResults['failed']++;
        $errorMsg = implode("\n", $output);
        $testResults['details'][] = [
            'name' => "{$className} - Syntax check",
            'status' => 'FAIL',
            'message' => $errorMsg
        ];
        echo "Syntax error. ";
        return;
    }
    
    // Test if class is loadable
    $testResults['total']++;
    try {
        include_once $fullPath;
        
        if (class_exists($className)) {
            $testResults['passed']++;
            $testResults['details'][] = [
                'name' => "{$className} - Class loadable",
                'status' => 'PASS',
                'message' => ''
            ];
            echo "Class loadable. ";
        } else {
            $testResults['failed']++;
            $testResults['details'][] = [
                'name' => "{$className} - Class loadable",
                'status' => 'FAIL',
                'message' => "Class {$className} not found in file"
            ];
            echo "Class not found. ";
        }
    } catch (Throwable $e) {
        $testResults['failed']++;
        $testResults['details'][] = [
            'name' => "{$className} - Class loadable",
            'status' => 'FAIL',
            'message' => "Error loading class: " . $e->getMessage()
        ];
        echo "Error loading class. ";
    }
    
    echo "Done.\n";
}

/**
 * Generate HTML test report
 * 
 * @param array $testResults Test results
 * @param string $reportFile Output file path
 */
function generateHtmlReport($testResults, $reportFile) {
    $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlingAi Pro - AI Engine Test Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .summary {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .pass {
            color: #28a745;
        }
        .fail {
            color: #dc3545;
        }
        .details {
            margin-top: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>AlingAi Pro - AI Engine Test Report</h1>
        <p>Generated on: ' . date('Y-m-d H:i:s') . '</p>
        <p>PHP Version: ' . PHP_VERSION . '</p>
        
        <div class="summary">
            <h2>Summary</h2>
            <p>Total Tests: ' . $testResults['total'] . '</p>
            <p>Passed: <span class="pass">' . $testResults['passed'] . '</span></p>
            <p>Failed: <span class="fail">' . $testResults['failed'] . '</span></p>
        </div>
        
        <h2>Test Details</h2>
        <table>
            <tr>
                <th>Test Name</th>
                <th>Status</th>
                <th>Message</th>
            </tr>';
    
    foreach ($testResults['details'] as $test) {
        $statusClass = $test['status'] === 'PASS' ? 'pass' : 'fail';
        $html .= '<tr>
                <td>' . htmlspecialchars($test['name']) . '</td>
                <td class="' . $statusClass . '">' . $test['status'] . '</td>
                <td>' . htmlspecialchars($test['message']) . '</td>
            </tr>';
    }
    
    $html .= '</table>
    </div>
</body>
</html>';
    
    file_put_contents($reportFile, $html);
} 