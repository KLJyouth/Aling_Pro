<?php
/**
 * AlingAi Pro - HTML Validator
 * 
 * This script checks all HTML files in the public directory for issues:
 * - Missing resources (CSS, JS, images)
 * - Broken internal links
 * - Basic HTML structure issues
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constants
define('BASE_DIR', dirname(__DIR__));
define('PUBLIC_DIR', BASE_DIR . '/public');
define('COLOR_GREEN', "\033[32m");
define('COLOR_RED', "\033[31m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_BLUE', "\033[34m");
define('COLOR_RESET', "\033[0m");
define('BASE_URL', 'http://localhost:3000');

// Results tracking
$totalFiles = 0;
$passedFiles = 0;
$filesWithIssues = [];
$allResources = [];
$allLinks = [];

/**
 * Print a message to the console
 * 
 * @param string $message The message to print
 * @param string $color The color to use
 */
function printMessage($message, $color = COLOR_RESET) {
    echo $color . $message . COLOR_RESET . "\n";
}

/**
 * Find all HTML files in a directory
 * 
 * @param string $dir The directory to search
 * @return array List of HTML files
 */
function findHtmlFiles($dir) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'html') {
            $files[] = $file->getPathname();
        }
    }
    
    return $files;
}

/**
 * Check if a file exists in the public directory
 * 
 * @param string $path The path to check
 * @return bool Whether the file exists
 */
function resourceExists($path) {
    // Remove query strings and anchors
    $path = preg_replace('/[?#].*$/', '', $path);
    
    // Handle absolute URLs
    if (strpos($path, 'http') === 0) {
        // Only check local resources
        if (strpos($path, BASE_URL) === 0) {
            $path = str_replace(BASE_URL, '', $path);
        } else {
            // External URL, assume it exists
            return true;
        }
    }
    
    // Handle root-relative URLs
    if (strpos($path, '/') === 0) {
        $fullPath = PUBLIC_DIR . $path;
    } else {
        // Relative URL, need context
        $fullPath = PUBLIC_DIR . '/' . $path;
    }
    
    return file_exists($fullPath);
}

/**
 * Extract resources from HTML content
 * 
 * @param string $content The HTML content
 * @param string $baseFile The base file path for relative URLs
 * @return array List of resources
 */
function extractResources($content, $baseFile) {
    $resources = [];
    $baseDir = dirname($baseFile);
    $relativePath = str_replace(PUBLIC_DIR, '', $baseDir);
    
    // Extract CSS files
    preg_match_all('/href=["\']([^"\']+\.css[^"\']*)["\']/', $content, $matches);
    foreach ($matches[1] as $match) {
        $resources[] = [
            'type' => 'css',
            'path' => $match,
            'source' => $baseFile
        ];
    }
    
    // Extract JS files
    preg_match_all('/src=["\']([^"\']+\.js[^"\']*)["\']/', $content, $matches);
    foreach ($matches[1] as $match) {
        $resources[] = [
            'type' => 'js',
            'path' => $match,
            'source' => $baseFile
        ];
    }
    
    // Extract images
    preg_match_all('/src=["\']([^"\']+\.(jpg|jpeg|png|gif|svg|webp)[^"\']*)["\']/', $content, $matches);
    foreach ($matches[1] as $match) {
        $resources[] = [
            'type' => 'image',
            'path' => $match,
            'source' => $baseFile
        ];
    }
    
    return $resources;
}

/**
 * Extract links from HTML content
 * 
 * @param string $content The HTML content
 * @param string $baseFile The base file path for relative URLs
 * @return array List of links
 */
function extractLinks($content, $baseFile) {
    $links = [];
    $baseDir = dirname($baseFile);
    $relativePath = str_replace(PUBLIC_DIR, '', $baseDir);
    
    // Extract links (a href)
    preg_match_all('/href=["\']([^"\']+)["\']/', $content, $matches);
    foreach ($matches[1] as $match) {
        // Skip non-HTTP links (mailto:, tel:, javascript:)
        if (preg_match('/^(mailto:|tel:|javascript:|#)/', $match)) {
            continue;
        }
        
        // Skip external links
        if (strpos($match, 'http') === 0 && strpos($match, BASE_URL) !== 0) {
            continue;
        }
        
        $links[] = [
            'path' => $match,
            'source' => $baseFile
        ];
    }
    
    return $links;
}

/**
 * Check HTML file for issues
 * 
 * @param string $file The file to check
 * @return array Result with issues
 */
function checkHtmlFile($file) {
    global $allResources, $allLinks;
    
    $content = file_get_contents($file);
    $issues = [];
    
    // Check basic HTML structure
    if (!preg_match('/<html[^>]*>/', $content)) {
        $issues[] = "Missing <html> tag";
    }
    
    if (!preg_match('/<head[^>]*>/', $content)) {
        $issues[] = "Missing <head> tag";
    }
    
    if (!preg_match('/<body[^>]*>/', $content)) {
        $issues[] = "Missing <body> tag";
    }
    
    if (!preg_match('/<title[^>]*>/', $content)) {
        $issues[] = "Missing <title> tag";
    }
    
    // Extract and check resources
    $resources = extractResources($content, $file);
    $allResources = array_merge($allResources, $resources);
    
    foreach ($resources as $resource) {
        if (!resourceExists($resource['path'])) {
            $issues[] = "Missing resource: {$resource['type']} - {$resource['path']}";
        }
    }
    
    // Extract links
    $links = extractLinks($content, $file);
    $allLinks = array_merge($allLinks, $links);
    
    return [
        'issues' => $issues
    ];
}

// Start the validation process
printMessage("\n" . COLOR_BLUE . "=== AlingAi Pro HTML Validator ===" . COLOR_RESET);
printMessage("Starting validation at: " . date('Y-m-d H:i:s'));

// Find all HTML files
$htmlFiles = findHtmlFiles(PUBLIC_DIR);
$totalFiles = count($htmlFiles);
printMessage("Found $totalFiles HTML files to check");

// Check each file
foreach ($htmlFiles as $file) {
    $relativePath = str_replace(PUBLIC_DIR . '/', '', $file);
    printMessage("Checking $relativePath... ", COLOR_YELLOW);
    
    $result = checkHtmlFile($file);
    
    if (empty($result['issues'])) {
        $passedFiles++;
        printMessage("  " . COLOR_GREEN . "✓ PASSED" . COLOR_RESET);
    } else {
        $filesWithIssues[] = [
            'file' => $relativePath,
            'issues' => $result['issues']
        ];
        printMessage("  " . COLOR_RED . "✗ ISSUES FOUND: " . count($result['issues']) . COLOR_RESET);
        foreach ($result['issues'] as $issue) {
            printMessage("    - " . $issue, COLOR_RED);
        }
    }
}

// Check all collected links
printMessage("\n" . COLOR_BLUE . "Checking internal links..." . COLOR_RESET);
$brokenLinks = [];

foreach ($allLinks as $link) {
    $path = $link['path'];
    
    // Skip external links and anchors
    if (strpos($path, 'http') === 0 && strpos($path, BASE_URL) !== 0) {
        continue;
    }
    
    if (!resourceExists($path) && strpos($path, '#') !== 0) {
        $brokenLinks[] = [
            'path' => $path,
            'source' => str_replace(PUBLIC_DIR . '/', '', $link['source'])
        ];
    }
}

if (empty($brokenLinks)) {
    printMessage("  " . COLOR_GREEN . "✓ All internal links are valid" . COLOR_RESET);
} else {
    printMessage("  " . COLOR_RED . "✗ Found " . count($brokenLinks) . " broken internal links" . COLOR_RESET);
    foreach ($brokenLinks as $link) {
        printMessage("    - Broken link: {$link['path']} in {$link['source']}", COLOR_RED);
    }
}

// Print summary
printMessage("\n" . COLOR_BLUE . "=== Validation Summary ===" . COLOR_RESET);
printMessage("Total HTML files: $totalFiles");
printMessage("Files with no issues: " . COLOR_GREEN . $passedFiles . COLOR_RESET);
printMessage("Files with issues: " . (count($filesWithIssues) > 0 ? COLOR_RED : COLOR_GREEN) . count($filesWithIssues) . COLOR_RESET);
printMessage("Broken internal links: " . (count($brokenLinks) > 0 ? COLOR_RED : COLOR_GREEN) . count($brokenLinks) . COLOR_RESET);

// Generate a report file
$reportFile = BASE_DIR . '/tests/html_validation_report.md';
$report = "# HTML Validation Report\n\n";
$report .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";
$report .= "## Summary\n\n";
$report .= "- Total HTML files checked: $totalFiles\n";
$report .= "- Files with no issues: $passedFiles\n";
$report .= "- Files with issues: " . count($filesWithIssues) . "\n";
$report .= "- Broken internal links: " . count($brokenLinks) . "\n\n";

if (count($filesWithIssues) > 0) {
    $report .= "## Files with Issues\n\n";
    foreach ($filesWithIssues as $index => $file) {
        $report .= "### " . ($index + 1) . ". " . $file['file'] . "\n\n";
        foreach ($file['issues'] as $issue) {
            $report .= "- " . $issue . "\n";
        }
        $report .= "\n";
    }
}

if (count($brokenLinks) > 0) {
    $report .= "## Broken Internal Links\n\n";
    foreach ($brokenLinks as $index => $link) {
        $report .= "### " . ($index + 1) . ". " . $link['path'] . "\n\n";
        $report .= "- Found in: " . $link['source'] . "\n\n";
    }
}

file_put_contents($reportFile, $report);
printMessage("Validation report saved to: tests/html_validation_report.md");

printMessage("\n" . COLOR_BLUE . "=== End of Validation ===" . COLOR_RESET); 