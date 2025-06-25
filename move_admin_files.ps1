# 移动admin目录下的文件到public/admin目录

# 创建函数，移动文件并备份原始文件
function Move-FileWithBackup {
    param (
        [string]$SourcePath,
        [string]$DestinationPath
    )
    
    # 确保目标目录存在
    $destDir = Split-Path -Path $DestinationPath -Parent
    if (-not (Test-Path -Path $destDir)) {
        New-Item -Path $destDir -ItemType Directory -Force | Out-Null
        Write-Host "Created directory: $destDir"
    }
    
    # 如果源文件存在
    if (Test-Path -Path $SourcePath) {
        # 创建备份
        $backupDir = "backups\admin_cleanup"
        if (-not (Test-Path -Path $backupDir)) {
            New-Item -Path $backupDir -ItemType Directory -Force | Out-Null
            Write-Host "Created backup directory: $backupDir"
        }
        
        $fileName = Split-Path -Path $SourcePath -Leaf
        $backupPath = Join-Path -Path $backupDir -ChildPath $fileName
        
        # 如果是目录，则进行递归复制
        if ((Get-Item $SourcePath) -is [System.IO.DirectoryInfo]) {
            # 对于目录，我们需要保留目录结构
            $sourceDirName = Split-Path -Path $SourcePath -Leaf
            $backupDirPath = Join-Path -Path $backupDir -ChildPath $sourceDirName
            
            # 备份目录
            if (-not (Test-Path -Path $backupDirPath)) {
                New-Item -Path $backupDirPath -ItemType Directory -Force | Out-Null
            }
            Copy-Item -Path "$SourcePath\*" -Destination $backupDirPath -Recurse -Force
            Write-Host "Backed up directory: $SourcePath to $backupDirPath"
            
            # 复制到目标位置
            if (-not (Test-Path -Path $DestinationPath)) {
                New-Item -Path $DestinationPath -ItemType Directory -Force | Out-Null
            }
            Copy-Item -Path "$SourcePath\*" -Destination $DestinationPath -Recurse -Force
            Write-Host "Moved directory: $SourcePath to $DestinationPath"
        }
        else {
            # 对于文件，直接复制
            Copy-Item -Path $SourcePath -Destination $backupPath -Force
            Write-Host "Backed up file: $SourcePath to $backupPath"
            
            # 移动到目标位置
            Copy-Item -Path $SourcePath -Destination $DestinationPath -Force
            Write-Host "Moved file: $SourcePath to $DestinationPath"
        }
    }
    else {
        Write-Host "Source path does not exist: $SourcePath" -ForegroundColor Yellow
    }
}

# 创建备份目录
if (-not (Test-Path -Path "backups\admin_cleanup")) {
    New-Item -Path "backups\admin_cleanup" -ItemType Directory -Force | Out-Null
    Write-Host "Created backup directory: backups\admin_cleanup"
}

# 移动admin/maintenance/logs下的文件
Write-Host "Moving admin/maintenance/logs files..." -ForegroundColor Cyan
$logFiles = Get-ChildItem -Path "admin\maintenance\logs" -File
foreach ($file in $logFiles) {
    $sourcePath = $file.FullName
    $destinationPath = "public\admin\maintenance\logs\" + $file.Name
    Move-FileWithBackup -SourcePath $sourcePath -DestinationPath $destinationPath
}

# 移动admin/maintenance/reports下的文件
Write-Host "Moving admin/maintenance/reports files..." -ForegroundColor Cyan
$reportFiles = Get-ChildItem -Path "admin\maintenance\reports" -File
foreach ($file in $reportFiles) {
    $sourcePath = $file.FullName
    $destinationPath = "public\admin\maintenance\reports\" + $file.Name
    Move-FileWithBackup -SourcePath $sourcePath -DestinationPath $destinationPath
}

# 移动admin/maintenance/tools下的文件
Write-Host "Moving admin/maintenance/tools files..." -ForegroundColor Cyan
if (-not (Test-Path -Path "public\admin\maintenance\tools")) {
    New-Item -Path "public\admin\maintenance\tools" -ItemType Directory -Force | Out-Null
    Write-Host "Created directory: public\admin\maintenance\tools"
}

$toolFiles = Get-ChildItem -Path "admin\maintenance\tools" -File
foreach ($file in $toolFiles) {
    $sourcePath = $file.FullName
    $destinationPath = "public\admin\maintenance\tools\" + $file.Name
    Move-FileWithBackup -SourcePath $sourcePath -DestinationPath $destinationPath
}

# 如果有resources目录，也移动它
if (Test-Path -Path "admin\maintenance\tools\resources") {
    if (-not (Test-Path -Path "public\admin\maintenance\tools\resources")) {
        New-Item -Path "public\admin\maintenance\tools\resources" -ItemType Directory -Force | Out-Null
        Write-Host "Created directory: public\admin\maintenance\tools\resources"
    }
    
    $resourceFiles = Get-ChildItem -Path "admin\maintenance\tools\resources" -File
    foreach ($file in $resourceFiles) {
        $sourcePath = $file.FullName
        $destinationPath = "public\admin\maintenance\tools\resources\" + $file.Name
        Move-FileWithBackup -SourcePath $sourcePath -DestinationPath $destinationPath
    }
}

# 移动security目录下的文件（如果有）
if (Test-Path -Path "admin\security") {
    if (-not (Test-Path -Path "public\admin\security")) {
        New-Item -Path "public\admin\security" -ItemType Directory -Force | Out-Null
        Write-Host "Created directory: public\admin\security"
    }
    
    # 递归获取所有文件
    $securityFiles = Get-ChildItem -Path "admin\security" -File -Recurse
    foreach ($file in $securityFiles) {
        $relativePath = $file.FullName.Substring((Get-Item "admin\security").FullName.Length + 1)
        $sourcePath = $file.FullName
        $destinationPath = "public\admin\security\" + $relativePath
        
        # 确保目标目录存在
        $destDir = Split-Path -Path $destinationPath -Parent
        if (-not (Test-Path -Path $destDir)) {
            New-Item -Path $destDir -ItemType Directory -Force | Out-Null
            Write-Host "Created directory: $destDir"
        }
        
        Move-FileWithBackup -SourcePath $sourcePath -DestinationPath $destinationPath
    }
}

Write-Host "File organization completed!" -ForegroundColor Green
Write-Host "All original files have been backed up to backups\admin_cleanup" -ForegroundColor Green 