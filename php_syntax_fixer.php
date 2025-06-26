<?php
/**
 * PHP Syntax Fixer
 * 
 * A comprehensive tool to check and fix PHP syntax errors and validate HTML files.
 * This tool combines functionality from multiple scripts to provide a one-stop solution
 * for fixing PHP and HTML issues in your codebase.
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration
$config = [
    'backup_dir' => 'backups/syntax_fixer_' . date('Y-m-d_H-i-s'),
    'report_dir' => 'reports',
    'max_files_to_display' => 50,
    'extensions' => [
        'php' => ['php', 'inc', 'module'],
        'html' => ['html', 'htm']
    ],
    'directories' => [
        'ai-engines',
        'apps',
        'completed',
        'public',
        'admin',
        'includes',
        'src',
        'services',
        'core'
    ],
    'exclude_dirs' => [
        'vendor',
        'node_modules',
        'backups'
    ]
];

// Create backup directory
if (!file_exists($config['backup_dir'])) {
    mkdir($config['backup_dir'], 0777, true);
    echo "Created backup directory: {$config['backup_dir']}\n";
}

// Create report directory
if (!file_exists($config['report_dir'])) {
    mkdir($config['report_dir'], 0777, true);
    echo "Created report directory: {$config['report_dir']}\n";
}

/**
 * Main class for PHP Syntax Fixer
 */
class SyntaxFixer {
    private $config;
    private $stats = [
        'php_total' => 0,
        'php_checked' => 0,
        'php_errors' => 0,
        'php_fixed' => 0,
        'html_total' => 0,
        'html_checked' => 0,
        'html_errors' => 0,
        'html_fixed' => 0
    ];
    private $errorFiles = [];
    private $fixedFiles = [];
    private $errorDetails = [];

    /**
     * Constructor
     * 
     * @param array $config Configuration array
     */
    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Run the fixer
     */
    public function run() {
        echo "Starting PHP and HTML Syntax Fixer...\n";
        
        // Find all PHP and HTML files
        $this->findFiles();
        
        // Check and fix PHP files
        $this->processPhpFiles();
        
        // Check and validate HTML files
        $this->processHtmlFiles();
        
        // Generate report
        $this->generateReport();
        
        echo "Syntax fixing completed!\n";
        echo "See report at: {$this->config['report_dir']}/syntax_fix_report_" . date('Y-m-d_H-i-s') . ".html\n";
    }

    /**
     * Find all PHP and HTML files in the specified directories
     */
    private function findFiles() {
        $phpFiles = [];
        $htmlFiles = [];
        
        foreach ($this->config['directories'] as $dir) {
            if (!file_exists($dir)) {
                continue;
            }
            
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir)
            );
            
            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    continue;
                }
                
                // Check if directory should be excluded
                $pathToCheck = $file->getPathname();
                $excluded = false;
                foreach ($this->config['exclude_dirs'] as $excludeDir) {
                    if (strpos($pathToCheck, DIRECTORY_SEPARATOR . $excludeDir . DIRECTORY_SEPARATOR) !== false) {
                        $excluded = true;
                        break;
                    }
                }
                
                if ($excluded) {
                    continue;
                }
                
                // Check file extensions
                $extension = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                
                if (in_array($extension, $this->config['extensions']['php'])) {
                    $phpFiles[] = $file->getPathname();
                } elseif (in_array($extension, $this->config['extensions']['html'])) {
                    $htmlFiles[] = $file->getPathname();
                }
            }
        }
        
        $this->stats['php_total'] = count($phpFiles);
        $this->stats['html_total'] = count($htmlFiles);
        
        echo "Found {$this->stats['php_total']} PHP files and {$this->stats['html_total']} HTML files to process.\n";
        
        return [
            'php' => $phpFiles,
            'html' => $htmlFiles
        ];
    }

    /**
     * Process PHP files
     */
    private function processPhpFiles() {
        $files = $this->findFiles();
        $phpFiles = $files['php'];
        
        echo "\nChecking PHP syntax...\n";
        
        foreach ($phpFiles as $file) {
            $this->stats['php_checked']++;
            
            echo "Checking file " . $this->stats['php_checked'] . "/{$this->stats['php_total']}: $file";
            
            // Check syntax
            $output = [];
            $returnVar = 0;
            exec("php -l \"$file\" 2>&1", $output, $returnVar);
            
            if ($returnVar !== 0) {
                // Syntax error found
                $this->stats['php_errors']++;
                $this->errorFiles[] = $file;
                $this->errorDetails[$file] = implode("\n", $output);
                
                echo " - Error found!\n";
                
                // Backup file
                $this->backupFile($file);
                
                // Try to fix the file
                if ($this->fixPhpFile($file)) {
                    $this->stats['php_fixed']++;
                    $this->fixedFiles[] = $file;
                    echo "  - Fixed!\n";
                } else {
                    echo "  - Could not fix automatically.\n";
                }
            } else {
                echo " - OK\n";
            }
        }
        
        echo "\nPHP syntax check completed.\n";
        echo "Files with errors: {$this->stats['php_errors']}\n";
        echo "Files fixed: {$this->stats['php_fixed']}\n";
    }

    /**
     * Process HTML files
     */
    private function processHtmlFiles() {
        $files = $this->findFiles();
        $htmlFiles = $files['html'];
        
        echo "\nChecking HTML files...\n";
        
        foreach ($htmlFiles as $file) {
            $this->stats['html_checked']++;
            
            echo "Checking file " . $this->stats['html_checked'] . "/{$this->stats['html_total']}: $file";
            
            // Simple HTML validation (we'll do basic checks since we don't have a full HTML validator)
            $content = file_get_contents($file);
            $errors = $this->validateHtml($content);
            
            if (!empty($errors)) {
                // HTML errors found
                $this->stats['html_errors']++;
                $this->errorFiles[] = $file;
                $this->errorDetails[$file] = implode("\n", $errors);
                
                echo " - Error found!\n";
                
                // Backup file
                $this->backupFile($file);
                
                // Try to fix the HTML
                if ($this->fixHtmlFile($file)) {
                    $this->stats['html_fixed']++;
                    $this->fixedFiles[] = $file;
                    echo "  - Fixed!\n";
                } else {
                    echo "  - Could not fix automatically.\n";
                }
            } else {
                echo " - OK\n";
            }
        }
        
        echo "\nHTML check completed.\n";
        echo "Files with errors: {$this->stats['html_errors']}\n";
        echo "Files fixed: {$this->stats['html_fixed']}\n";
    }

    /**
     * Backup a file before fixing
     * 
     * @param string $file File path
     * @return bool Success status
     */
    private function backupFile($file) {
        $relativePath = $file;
        $backupPath = $this->config['backup_dir'] . '/' . $relativePath;
        
        // Create directory structure
        $backupDir = dirname($backupPath);
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0777, true);
        }
        
        // Copy the file
        return copy($file, $backupPath);
    }

    /**
     * Fix PHP syntax errors in a file
     * 
     * @param string $file File path
     * @return bool Success status
     */
    private function fixPhpFile($file) {
        $content = file_get_contents($file);
        $originalContent = $content;
        
        // Apply fixes for common PHP syntax errors
        
        // 1. Fix array syntax
        $content = preg_replace('/array\s*\(/', '[', $content);
        $content = preg_replace('/\)(?=\s*[;,])/', ']', $content);
        
        // 2. Fix string concatenation
        $content = preg_replace('/"(.*?)"\s*\.\s*"(.*?)"/', '"$1$2"', $content);
        
        // 3. Fix spacing around operators
        $content = preg_replace('/\s+->/', '->', $content);
        $content = preg_replace('/\(\s+/', '(', $content);
        $content = preg_replace('/\s+\)/', ')', $content);
        
        // 4. Fix quotes in array keys
        $content = preg_replace('/\[([\'"])(.*?)\1\]/', '["$2"]', $content);
        
        // 5. Fix class property declarations
        $content = preg_replace('/:(\s*)protected(\s*)string(\s*)\$/', 'protected string $', $content);
        $content = preg_replace('/:(\s*)private(\s*)string(\s*)\$/', 'private string $', $content);
        $content = preg_replace('/:(\s*)public(\s*)string(\s*)\$/', 'public string $', $content);
        
        // 6. Fix regex patterns
        $content = preg_replace('/"\]\+\$\/"/', '"]+$/"', $content);
        $content = preg_replace('/"\]\+\$\/u"/', '"]+$/u"', $content);
        
        // 7. Fix string casting
        $content = preg_replace('/\(string\s+\)/', '(string)', $content);
        
        // 8. Fix semicolons
        $content = preg_replace('/;\s+\}/', ';}', $content);
        
        // 9. Fix closing brackets
        $content = preg_replace('/\)\s*\{/', ') {', $content);
        
        // 10. Fix double quotes
        $content = str_replace('""', '"', $content);
        
        // Check if content has changed
        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            
            // Verify if the fix worked
            $output = [];
            $returnVar = 0;
            exec("php -l \"$file\" 2>&1", $output, $returnVar);
            
            return ($returnVar === 0);
        }
        
        return false;
    }

    /**
     * Basic HTML validation
     * 
     * @param string $content HTML content
     * @return array List of errors
     */
    private function validateHtml($content) {
        $errors = [];
        
        // Check for unclosed tags
        $tags = ['div', 'span', 'p', 'a', 'table', 'tr', 'td', 'ul', 'ol', 'li', 'form', 'input', 'select', 'option'];
        
        foreach ($tags as $tag) {
            $openCount = substr_count(strtolower($content), "<$tag");
            $closeCount = substr_count(strtolower($content), "</$tag>");
            
            if ($openCount > $closeCount) {
                $errors[] = "Unclosed <$tag> tag found ($openCount open, $closeCount closed)";
            }
        }
        
        // Check for malformed attributes
        if (preg_match_all('/(\w+)=([^\s"\'>]+)(?=\s|>)/', $content, $matches)) {
            foreach ($matches[0] as $match) {
                $errors[] = "Unquoted attribute found: $match";
            }
        }
        
        return $errors;
    }

    /**
     * Fix HTML errors in a file
     * 
     * @param string $file File path
     * @return bool Success status
     */
    private function fixHtmlFile($file) {
        $content = file_get_contents($file);
        $originalContent = $content;
        
        // 1. Fix unquoted attributes
        $content = preg_replace('/(\w+)=([^\s"\'>]+)(?=\s|>)/', '$1="$2"', $content);
        
        // 2. Fix unclosed tags (basic approach)
        $tags = ['div', 'span', 'p', 'a', 'table', 'tr', 'td', 'ul', 'ol', 'li', 'form', 'select'];
        
        foreach ($tags as $tag) {
            $openCount = substr_count(strtolower($content), "<$tag");
            $closeCount = substr_count(strtolower($content), "</$tag>");
            
            // Add missing closing tags at the end
            if ($openCount > $closeCount) {
                $diff = $openCount - $closeCount;
                $content .= str_repeat("</$tag>", $diff);
            }
        }
        
        // Check if content has changed
        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            return true;
        }
        
        return false;
    }

    /**
     * Generate HTML report
     */
    private function generateReport() {
        $reportFile = $this->config['report_dir'] . '/syntax_fix_report_' . date('Y-m-d_H-i-s') . '.html';
        
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syntax Fix Report</title>
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
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin-bottom: 20px;
        }
        .error-files {
            margin-bottom: 20px;
        }
        .file-list {
            list-style-type: none;
            padding-left: 0;
        }
        .file-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .file-list li:last-child {
            border-bottom: none;
        }
        .fixed {
            color: #4CAF50;
        }
        .not-fixed {
            color: #F44336;
        }
        .error-details {
            background-color: #f8f9fa;
            border-left: 4px solid #F44336;
            padding: 15px;
            margin-top: 10px;
            font-family: monospace;
            white-space: pre-wrap;
            overflow-x: auto;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .stats-table th, .stats-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .stats-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Syntax Fix Report</h1>
        <p>Generated on: ' . date('Y-m-d H:i:s') . '</p>
        
        <div class="summary">
            <h2>Summary</h2>
            <table class="stats-table">
                <tr>
                    <th>Type</th>
                    <th>Total Files</th>
                    <th>Checked</th>
                    <th>With Errors</th>
                    <th>Fixed</th>
                </tr>
                <tr>
                    <td>PHP</td>
                    <td>' . $this->stats['php_total'] . '</td>
                    <td>' . $this->stats['php_checked'] . '</td>
                    <td>' . $this->stats['php_errors'] . '</td>
                    <td>' . $this->stats['php_fixed'] . '</td>
                </tr>
                <tr>
                    <td>HTML</td>
                    <td>' . $this->stats['html_total'] . '</td>
                    <td>' . $this->stats['html_checked'] . '</td>
                    <td>' . $this->stats['html_errors'] . '</td>
                    <td>' . $this->stats['html_fixed'] . '</td>
                </tr>
            </table>
        </div>';
        
        // Files with errors
        if (!empty($this->errorFiles)) {
            $html .= '
        <div class="error-files">
            <h2>Files with Errors</h2>
            <ul class="file-list">';
            
            $displayCount = 0;
            foreach ($this->errorFiles as $file) {
                $displayCount++;
                if ($displayCount > $this->config['max_files_to_display']) {
                    $html .= '<li>... and ' . (count($this->errorFiles) - $this->config['max_files_to_display']) . ' more files</li>';
                    break;
                }
                
                $fixed = in_array($file, $this->fixedFiles);
                $statusClass = $fixed ? 'fixed' : 'not-fixed';
                $statusText = $fixed ? 'Fixed' : 'Not Fixed';
                
                $html .= '
                <li>
                    ' . htmlspecialchars($file) . ' - <span class="' . $statusClass . '">' . $statusText . '</span>';
                
                if (isset($this->errorDetails[$file])) {
                    $html .= '
                    <div class="error-details">' . htmlspecialchars($this->errorDetails[$file]) . '</div>';
                }
                
                $html .= '
                </li>';
            }
            
            $html .= '
            </ul>
        </div>';
        }
        
        // Fixed files
        if (!empty($this->fixedFiles)) {
            $html .= '
        <div class="fixed-files">
            <h2>Fixed Files</h2>
            <ul class="file-list">';
            
            $displayCount = 0;
            foreach ($this->fixedFiles as $file) {
                $displayCount++;
                if ($displayCount > $this->config['max_files_to_display']) {
                    $html .= '<li>... and ' . (count($this->fixedFiles) - $this->config['max_files_to_display']) . ' more files</li>';
                    break;
                }
                
                $html .= '
                <li>' . htmlspecialchars($file) . '</li>';
            }
            
            $html .= '
            </ul>
        </div>';
        }
        
        $html .= '
    </div>
</body>
</html>';
        
        file_put_contents($reportFile, $html);
        echo "Report generated: $reportFile\n";
    }
}

// Run the fixer
$fixer = new SyntaxFixer($config);
$fixer->run();