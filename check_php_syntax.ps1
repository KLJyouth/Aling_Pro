# PHP Syntax Check Script

# Check if PHP is installed
try {
    $phpVersion = & php -v
    if ($LASTEXITCODE -ne 0) {
        Write-Host "PHP is not available. Make sure PHP is installed and added to PATH." -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "PHP is not available. Make sure PHP is installed and added to PATH." -ForegroundColor Red
    exit 1
}

Write-Host "Starting PHP syntax check..." -ForegroundColor Cyan

# Files to check
$filesToCheck = @(
    "apps\ai-platform\Services\NLP\fixed_nlp_new.php",
    "ai-engines\knowledge-graph\ReasoningEngine.php",
    "completed\Config\cache.php",
    "ai-engines\knowledge-graph\MemoryGraphStore.php",
    "apps\blockchain\Services\SmartContractManager.php",
    "public\install\config.php",
    "apps\ai-platform\Services\CV\ComputerVisionProcessor.php",
    "apps\blockchain\Services\BlockchainServiceManager.php",
    "completed\Config\database.php",
    "apps\ai-platform\Services\Speech\SpeechProcessor.php",
    "public\install\check.php",
    "ai-engines\knowledge-graph\RelationExtractor.php",
    "apps\blockchain\Services\WalletManager.php",
    "public\assets\docs\Stanfai_docs\login_form_example.php",
    "ai-engines\nlp\EnglishTokenizer.php",
    "completed\Config\websocket.php",
    "public\api\v1\user\profile.php",
    "public\admin\api\documentation\index.php",
    "public\admin\api\monitoring\index.php",
    "apps\ai-platform\Services\NLP\NaturalLanguageProcessor.php",
    "public\admin\api\users\index.php",
    "ai-engines\nlp\ChineseTokenizer.php",
    "ai-engines\knowledge-graph\GraphStoreInterface.php",
    "ai-engines\nlp\POSTagger.php",
    "apps\ai-platform\services\AIServiceManager.php",
    "completed\Config\routes.php",
    "public\install\install.php"
)

# Store results
$passedFiles = @()
$failedFiles = @()
$notFoundFiles = @()

# Check each file
foreach ($file in $filesToCheck) {
    if (Test-Path -Path $file) {
        Write-Host "Checking file: $file" -NoNewline
        
        $output = & php -l $file 2>&1
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host " - Passed" -ForegroundColor Green
            $passedFiles += $file
        } else {
            Write-Host " - Failed" -ForegroundColor Red
            Write-Host $output -ForegroundColor Red
            $failedFiles += @{
                "file" = $file
                "error" = $output
            }
        }
    } else {
        Write-Host "File not found: $file" -ForegroundColor Yellow
        $notFoundFiles += $file
    }
}

# Generate report content
$reportContent = "# PHP Syntax Check Report`r`n`r`n"
$reportContent += "## Check Time`r`n"
$reportContent += "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')`r`n`r`n"
$reportContent += "## Check Results`r`n`r`n"

# Passed files
$reportContent += "### Passed Files ($($passedFiles.Count))`r`n`r`n"
foreach ($file in $passedFiles) {
    $reportContent += "- $file`r`n"
}
$reportContent += "`r`n"

# Failed files
$reportContent += "### Failed Files ($($failedFiles.Count))`r`n`r`n"
foreach ($failedItem in $failedFiles) {
    $reportContent += "#### $($failedItem.file)`r`n`r`n"
    $reportContent += "````r`n$($failedItem.error)`r`n````r`n`r`n"
}

# Not found files
$reportContent += "### Not Found Files ($($notFoundFiles.Count))`r`n`r`n"
foreach ($file in $notFoundFiles) {
    $reportContent += "- $file`r`n"
}
$reportContent += "`r`n"

# Summary
$reportContent += "## Summary`r`n`r`n"
$reportContent += "- Total files: $($filesToCheck.Count)`r`n"
$reportContent += "- Passed: $($passedFiles.Count)`r`n"
$reportContent += "- Failed: $($failedFiles.Count)`r`n"
$reportContent += "- Not found: $($notFoundFiles.Count)`r`n"

# Save report
$reportPath = "public\docs\reports\PHP_SYNTAX_CHECK_REPORT.md"
Set-Content -Path $reportPath -Value $reportContent

Write-Host "`nCheck completed!" -ForegroundColor Green
Write-Host "Total files: $($filesToCheck.Count)" -ForegroundColor Cyan
Write-Host "Passed: $($passedFiles.Count)" -ForegroundColor Green
Write-Host "Failed: $($failedFiles.Count)" -ForegroundColor $(if ($failedFiles.Count -gt 0) { "Red" } else { "Green" })
Write-Host "Not found: $($notFoundFiles.Count)" -ForegroundColor Yellow
Write-Host "`nCheck report saved to: $reportPath" -ForegroundColor Green 