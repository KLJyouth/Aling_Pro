# 清理已移动的admin目录下的文件

# 删除函数，会检查备份是否存在，如果存在才删除
function Remove-FileIfBackedUp {
    param (
        [string]$SourcePath
    )
    
    # 如果源文件存在
    if (Test-Path -Path $SourcePath) {
        # 获取文件名
        $fileName = Split-Path -Path $SourcePath -Leaf
        $backupPath = Join-Path -Path "backups\admin_cleanup" -ChildPath $fileName
        
        # 检查备份是否存在
        if (Test-Path -Path $backupPath) {
            if ((Get-Item $SourcePath) -is [System.IO.DirectoryInfo]) {
                # 对于目录，递归删除
                Remove-Item -Path $SourcePath -Recurse -Force
                Write-Host "Deleted directory: $SourcePath" -ForegroundColor Green
            } else {
                # 对于文件，直接删除
                Remove-Item -Path $SourcePath -Force
                Write-Host "Deleted file: $SourcePath" -ForegroundColor Green
            }
        } else {
            Write-Host "Backup not found for: $SourcePath, skipping deletion" -ForegroundColor Yellow
        }
    } else {
        Write-Host "Source path does not exist: $SourcePath" -ForegroundColor Yellow
    }
}

# 删除admin/maintenance/logs下的文件
Write-Host "Cleaning up admin/maintenance/logs files..." -ForegroundColor Cyan
$logFiles = Get-ChildItem -Path "admin\maintenance\logs" -File -ErrorAction SilentlyContinue
if ($logFiles) {
    foreach ($file in $logFiles) {
        Remove-FileIfBackedUp -SourcePath $file.FullName
    }
}

# 删除admin/maintenance/reports下的文件
Write-Host "Cleaning up admin/maintenance/reports files..." -ForegroundColor Cyan
$reportFiles = Get-ChildItem -Path "admin\maintenance\reports" -File -ErrorAction SilentlyContinue
if ($reportFiles) {
    foreach ($file in $reportFiles) {
        Remove-FileIfBackedUp -SourcePath $file.FullName
    }
}

# 删除admin/maintenance/tools/resources下的文件
Write-Host "Cleaning up admin/maintenance/tools/resources files..." -ForegroundColor Cyan
$resourceFiles = Get-ChildItem -Path "admin\maintenance\tools\resources" -File -ErrorAction SilentlyContinue
if ($resourceFiles) {
    foreach ($file in $resourceFiles) {
        Remove-FileIfBackedUp -SourcePath $file.FullName
    }
}

# 删除admin/maintenance/tools下的文件
Write-Host "Cleaning up admin/maintenance/tools files..." -ForegroundColor Cyan
$toolFiles = Get-ChildItem -Path "admin\maintenance\tools" -File -ErrorAction SilentlyContinue
if ($toolFiles) {
    foreach ($file in $toolFiles) {
        Remove-FileIfBackedUp -SourcePath $file.FullName
    }
}

# 删除admin/security下的文件（如果有）
Write-Host "Cleaning up admin/security files..." -ForegroundColor Cyan
$securityFiles = Get-ChildItem -Path "admin\security" -File -Recurse -ErrorAction SilentlyContinue
if ($securityFiles) {
    foreach ($file in $securityFiles) {
        Remove-FileIfBackedUp -SourcePath $file.FullName
    }
}

# 删除空目录
Write-Host "Removing empty directories..." -ForegroundColor Cyan
if (Test-Path -Path "admin\maintenance\tools\resources") {
    if ((Get-ChildItem -Path "admin\maintenance\tools\resources" -Force -ErrorAction SilentlyContinue | Measure-Object).Count -eq 0) {
        Remove-Item -Path "admin\maintenance\tools\resources" -Force
        Write-Host "Removed empty directory: admin\maintenance\tools\resources" -ForegroundColor Green
    }
}

if (Test-Path -Path "admin\maintenance\tools") {
    if ((Get-ChildItem -Path "admin\maintenance\tools" -Force -ErrorAction SilentlyContinue | Measure-Object).Count -eq 0) {
        Remove-Item -Path "admin\maintenance\tools" -Force
        Write-Host "Removed empty directory: admin\maintenance\tools" -ForegroundColor Green
    }
}

if (Test-Path -Path "admin\maintenance\logs") {
    if ((Get-ChildItem -Path "admin\maintenance\logs" -Force -ErrorAction SilentlyContinue | Measure-Object).Count -eq 0) {
        Remove-Item -Path "admin\maintenance\logs" -Force
        Write-Host "Removed empty directory: admin\maintenance\logs" -ForegroundColor Green
    }
}

if (Test-Path -Path "admin\maintenance\reports") {
    if ((Get-ChildItem -Path "admin\maintenance\reports" -Force -ErrorAction SilentlyContinue | Measure-Object).Count -eq 0) {
        Remove-Item -Path "admin\maintenance\reports" -Force
        Write-Host "Removed empty directory: admin\maintenance\reports" -ForegroundColor Green
    }
}

if (Test-Path -Path "admin\maintenance") {
    if ((Get-ChildItem -Path "admin\maintenance" -Force -ErrorAction SilentlyContinue | Measure-Object).Count -eq 0) {
        Remove-Item -Path "admin\maintenance" -Force
        Write-Host "Removed empty directory: admin\maintenance" -ForegroundColor Green
    }
}

if (Test-Path -Path "admin\security") {
    if ((Get-ChildItem -Path "admin\security" -Force -Recurse -ErrorAction SilentlyContinue | Measure-Object).Count -eq 0) {
        Remove-Item -Path "admin\security" -Force
        Write-Host "Removed empty directory: admin\security" -ForegroundColor Green
    }
}

if (Test-Path -Path "admin") {
    if ((Get-ChildItem -Path "admin" -Force -ErrorAction SilentlyContinue | Measure-Object).Count -eq 0) {
        Remove-Item -Path "admin" -Force
        Write-Host "Removed empty directory: admin" -ForegroundColor Green
    }
}

Write-Host "Cleanup completed!" -ForegroundColor Green
Write-Host "Remember that all original files have backups in backups\admin_cleanup directory" -ForegroundColor Green 