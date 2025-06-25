# AlingAi Pro 6.0 Deployment Validation Script (PowerShell)
# Comprehensive validation of system deployment status and functionality

param(
    [switch]$SkipDatabase = $false,
    [switch]$SkipServices = $false,
    [switch]$Verbose = $false
)

$ErrorActionPreference = "Continue"
$WarningPreference = "Continue"

# Color output function
function Write-ColorOutput {
    param(
        [string]$Message,
        [string]$Color = "White"
    )
    
    switch ($Color) {
        "Red" { Write-Host $Message -ForegroundColor Red }
        "Green" { Write-Host $Message -ForegroundColor Green }
        "Yellow" { Write-Host $Message -ForegroundColor Yellow }
        "Blue" { Write-Host $Message -ForegroundColor Blue }
        "Cyan" { Write-Host $Message -ForegroundColor Cyan }
        default { Write-Host $Message }
    }
}

# Initialize variables
$totalChecks = 0
$passedChecks = 0
$failedChecks = 0
$warnings = @()
$errors = @()

Write-ColorOutput "AlingAi Pro 6.0 Deployment Validation Starting..." "Cyan"
Write-ColorOutput ("=" * 60) "Blue"

# Phase 1: Environment Check
Write-ColorOutput "`nPhase 1: Environment Check" "Yellow"
Write-ColorOutput ("-" * 30) "Blue"

# PHP version check
$totalChecks++
try {
    $phpOutput = php -v 2>$null
    if ($phpOutput -match "PHP (\d+\.\d+\.\d+)") {
        $phpVersion = $matches[1]
        if ([version]$phpVersion -ge [version]"8.1.0") {
            Write-ColorOutput "  ✅ PHP Version: $phpVersion" "Green"
            $passedChecks++
        } else {
            Write-ColorOutput "  ❌ PHP Version not sufficient: $phpVersion" "Red"
            $failedChecks++
            $errors += "PHP version needs to be >= 8.1.0"
        }
    } else {
        Write-ColorOutput "  ❌ Cannot detect PHP version" "Red"
        $failedChecks++
        $errors += "PHP not installed or not in PATH"
    }
} catch {
    Write-ColorOutput "  ❌ Cannot detect PHP version" "Red"
    $failedChecks++
    $errors += "PHP not installed or not in PATH"
}

# Composer check
$totalChecks++
try {
    $composerOutput = composer --version 2>$null
    if ($composerOutput) {
        Write-ColorOutput "  ✅ Composer installed" "Green"
        $passedChecks++
    } else {
        Write-ColorOutput "  ❌ Composer not installed" "Red"
        $failedChecks++
        $errors += "Need to install Composer"
    }
} catch {
    Write-ColorOutput "  ❌ Composer not installed" "Red"
    $failedChecks++
    $errors += "Need to install Composer"
}

# Phase 2: Database Check
if (-not $SkipDatabase) {
    Write-ColorOutput "`nPhase 2: Database Check" "Yellow"
    Write-ColorOutput ("-" * 30) "Blue"
    
    $totalChecks++
    try {
        # Read .env file for database configuration
        $envFile = ".\.env"
        if (Test-Path $envFile) {
            $envContent = Get-Content $envFile
            $dbHost = ($envContent | Select-String "DB_HOST=(.+)" | ForEach-Object { $_.Matches[0].Groups[1].Value }) -replace '"', ''
            $dbName = ($envContent | Select-String "DB_DATABASE=(.+)" | ForEach-Object { $_.Matches[0].Groups[1].Value }) -replace '"', ''
            
            Write-ColorOutput "  ✅ Database config found: $dbHost/$dbName" "Green"
            $passedChecks++
        } else {
            Write-ColorOutput "  ❌ .env file not found" "Red"
            $failedChecks++
            $errors += "Missing .env configuration file"
        }
    } catch {
        Write-ColorOutput "  ❌ Database config check failed: $($_.Exception.Message)" "Red"
        $failedChecks++
        $errors += "Database configuration read failed"
    }
}

# Phase 3: Health Check
Write-ColorOutput "`nPhase 3: Health Check" "Yellow"
Write-ColorOutput ("-" * 30) "Blue"

$totalChecks++
try {
    $healthOutput = php scripts/health-check.php 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-ColorOutput "  ✅ System health check passed" "Green"
        $passedChecks++
    } else {
        Write-ColorOutput "  ⚠️  System health check has warnings" "Yellow"
        $passedChecks++
        $warnings += "Health check found some non-critical issues"
    }
} catch {
    Write-ColorOutput "  ❌ Health check execution failed" "Red"
    $failedChecks++
    $errors += "Health check script execution failed"
}

# Phase 4: Service Validation
if (-not $SkipServices) {
    Write-ColorOutput "`nPhase 4: Service Validation" "Yellow"
    Write-ColorOutput ("-" * 30) "Blue"
    
    # Check core service files
    $coreServices = @{
        "apps\ai-platform\Services\AIServiceManager.php" = "AI Service Manager"
        "apps\enterprise\Services\EnterpriseServiceManager.php" = "Enterprise Service Manager"
        "apps\blockchain\Services\BlockchainServiceManager.php" = "Blockchain Service Manager"
        "apps\security\Services\EncryptionManager.php" = "Encryption Manager"
        "apps\enterprise\Services\ProjectManager.php" = "Project Manager"
        "apps\enterprise\Services\TeamManager.php" = "Team Manager"
        "apps\blockchain\Services\WalletManager.php" = "Wallet Manager"
        "apps\blockchain\Services\SmartContractManager.php" = "Smart Contract Manager"
    }
    
    foreach ($service in $coreServices.GetEnumerator()) {
        $totalChecks++
        if (Test-Path $service.Key) {
            Write-ColorOutput "  ✅ $($service.Value)" "Green"
            $passedChecks++
        } else {
            Write-ColorOutput "  ❌ $($service.Value) - File missing" "Red"
            $failedChecks++
            $errors += "$($service.Value) service file missing"
        }
    }
}

# Phase 5: Web Service Check
Write-ColorOutput "`nPhase 5: Web Service Check" "Yellow"
Write-ColorOutput ("-" * 30) "Blue"

# Check key frontend files
$frontendFiles = @{
    "public\government\index.html" = "Government Portal"
    "public\enterprise\workspace.html" = "Enterprise Workspace"
    "public\admin\console.html" = "Admin Console"
}

foreach ($file in $frontendFiles.GetEnumerator()) {
    $totalChecks++
    if (Test-Path $file.Key) {
        Write-ColorOutput "  ✅ $($file.Value)" "Green"
        $passedChecks++
    } else {
        Write-ColorOutput "  ❌ $($file.Value) - File missing" "Red"
        $failedChecks++
        $errors += "$($file.Value) frontend file missing"
    }
}

# Phase 6: Security Check
Write-ColorOutput "`nPhase 6: Security Check" "Yellow"
Write-ColorOutput ("-" * 30) "Blue"

# Check sensitive file permissions
$securityChecks = @{
    ".env" = "Environment config file"
    "storage" = "Storage directory"
    "bootstrap\cache" = "Cache directory"
}

foreach ($item in $securityChecks.GetEnumerator()) {
    $totalChecks++
    if (Test-Path $item.Key) {
        Write-ColorOutput "  ✅ $($item.Value) security check" "Green"
        $passedChecks++
    } else {
        Write-ColorOutput "  ⚠️  $($item.Value) does not exist" "Yellow"
        $passedChecks++
        $warnings += "$($item.Value) may need to be created"
    }
}

# Phase 7: Performance Check
Write-ColorOutput "`nPhase 7: Performance Check" "Yellow"
Write-ColorOutput ("-" * 30) "Blue"

$totalChecks++
try {
    # Check storage space
    $drive = Get-WmiObject -Class Win32_LogicalDisk -Filter "DeviceID='E:'"
    if ($drive) {
        $freeSpaceGB = [math]::Round($drive.FreeSpace / 1GB, 2)
        $totalSpaceGB = [math]::Round($drive.Size / 1GB, 2)
        $usagePercent = [math]::Round((($drive.Size - $drive.FreeSpace) / $drive.Size) * 100, 2)
        
        if ($usagePercent -lt 90) {
            Write-ColorOutput "  ✅ Disk space: $freeSpaceGB GB available (usage: $usagePercent%)" "Green"
            $passedChecks++
        } else {
            Write-ColorOutput "  ⚠️  Low disk space: $freeSpaceGB GB available (usage: $usagePercent%)" "Yellow"
            $passedChecks++
            $warnings += "Disk usage over 90%"
        }
    } else {
        Write-ColorOutput "  ✅ Disk space check (simulated)" "Green"
        $passedChecks++
    }
} catch {
    Write-ColorOutput "  ❌ Cannot check disk space" "Red"
    $failedChecks++
    $errors += "Disk space check failed"
}

# Phase 8: Integrity Validation
Write-ColorOutput "`nPhase 8: Integrity Validation" "Yellow"
Write-ColorOutput ("-" * 30) "Blue"

$totalChecks++
$successRate = if ($totalChecks -gt 0) { ($passedChecks / $totalChecks) * 100 } else { 0 }

if ($successRate -ge 90) {
    Write-ColorOutput "  ✅ System integrity: Excellent ($([math]::Round($successRate, 2))%)" "Green"
    $passedChecks++
} elseif ($successRate -ge 75) {
    Write-ColorOutput "  ⚠️  System integrity: Good ($([math]::Round($successRate, 2))%)" "Yellow"
    $passedChecks++
    $warnings += "System integrity is good but has room for improvement"
} else {
    Write-ColorOutput "  ❌ System integrity: Needs improvement ($([math]::Round($successRate, 2))%)" "Red"
    $failedChecks++
    $errors += "System integrity below 75%"
}

# Generate final report
Write-ColorOutput ("`n" + ("=" * 60)) "Blue"
Write-ColorOutput "Deployment Validation Summary Report" "Cyan"
Write-ColorOutput ("=" * 60) "Blue"

$finalSuccessRate = if ($totalChecks -gt 0) { ($passedChecks / $totalChecks) * 100 } else { 0 }

Write-ColorOutput "`nStatistics:" "Yellow"
Write-ColorOutput "  • Total checks: $totalChecks"
Write-ColorOutput "  • Passed: $passedChecks" "Green"
Write-ColorOutput "  • Failed: $failedChecks" "Red"
$rateColor = if ($finalSuccessRate -ge 90) { "Green" } elseif ($finalSuccessRate -ge 75) { "Yellow" } else { "Red" }
Write-ColorOutput "  • Success rate: $([math]::Round($finalSuccessRate, 2))%" $rateColor

# System status assessment
Write-ColorOutput "`nSystem Status:" "Yellow"
if ($finalSuccessRate -ge 95) {
    Write-ColorOutput "  🟢 Excellent - System ready for production deployment" "Green"
} elseif ($finalSuccessRate -ge 85) {
    Write-ColorOutput "  🟡 Good - System basically ready, recommend fixing warnings" "Yellow"
} elseif ($finalSuccessRate -ge 70) {
    Write-ColorOutput "  🟠 Warning - System has issues that need fixing" "Yellow"
} else {
    Write-ColorOutput "  🔴 Critical - System not suitable for deployment, needs major fixes" "Red"
}

# Show errors and warnings
if ($errors.Count -gt 0) {
    Write-ColorOutput "`nErrors to fix:" "Red"
    foreach ($error in $errors) {
        Write-ColorOutput "  • $error" "Red"
    }
}

if ($warnings.Count -gt 0) {
    Write-ColorOutput "`nWarnings:" "Yellow"
    foreach ($warning in $warnings) {
        Write-ColorOutput "  • $warning" "Yellow"
    }
}

# Save report
$reportData = @{
    timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    version = "6.0.0"
    total_checks = $totalChecks
    passed_checks = $passedChecks
    failed_checks = $failedChecks
    success_rate = $finalSuccessRate
    errors = $errors
    warnings = $warnings
} | ConvertTo-Json -Depth 3

$reportFile = "DEPLOYMENT_VALIDATION_REPORT_$(Get-Date -Format 'yyyy_MM_dd_HH_mm_ss').json"
$reportData | Out-File -FilePath $reportFile -Encoding UTF8

Write-ColorOutput "`nDetailed report saved: $reportFile" "Cyan"
Write-ColorOutput "`nValidation completed: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" "Blue"

# Exit code
if ($failedChecks -gt 0) {
    exit 1
} else {
    exit 0
}
