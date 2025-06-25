# Fix PHP 8.1 Syntax Issues Script
# This script fixes common PHP 8.1 compatibility issues in all PHP files

Write-Host "Starting PHP 8.1 syntax fix script..."

# Get all PHP files
$files = Get-ChildItem -Path "ai-engines", "apps", "completed", "public" -Filter "*.php" -Recurse -File
Write-Host "Found $($files.Count) PHP files to process"

$fixCount = 0

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    
    # Fix bracket issues - one at a time to avoid errors
    $content = $content -replace '\(\]', '()'
    $content = $content -replace '\]\{', ') {'
    $content = $content -replace '\]\;', ');'
    $content = $content -replace '\]\:', '):'
    $content = $content -replace 'in_\[', 'in_array('
    $content = $content -replace 'array\s*\(', '['
    $content = $content -replace '\);', '];'
    
    # Write content back if changed
    if ($content -ne $originalContent) {
        $fixCount++
        Set-Content -Path $file.FullName -Value $content
        Write-Host "Fixed: $($file.FullName)"
    }
}

Write-Host "PHP 8.1 syntax fix completed. Fixed $fixCount files." 