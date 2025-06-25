@echo off
REM PHP???????????
REM ??????PHP????????

echo ????PHP??????...

if "%1"=="" (
    echo ??: fix_php_file.bat [PHP????]
    exit /b 1
)

set FILE_PATH=%1
set BACKUP_PATH=%FILE_PATH%.bak

echo ??????: %FILE_PATH%

REM ????????
if not exist "%FILE_PATH%" (
    echo ??: ????? - %FILE_PATH%
    exit /b 1
)

REM ????
echo ????: %BACKUP_PATH%
copy "%FILE_PATH%" "%BACKUP_PATH%" > nul

REM ?????BOM??
powershell -Command "$bytes = Get-Content -Path '%FILE_PATH%' -Encoding Byte -TotalCount 3; if ($bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF) { echo '????BOM???????...'; $content = [System.IO.File]::ReadAllText('%FILE_PATH%').Substring(1); [System.IO.File]::WriteAllText('%FILE_PATH%', $content, [System.Text.Encoding]::UTF8); } else { echo '?????BOM??'; }"

REM ??PHP????
powershell -Command "$content = Get-Content -Path '%FILE_PATH%' -Raw; $modified = $false; if ($content -match '^<\?(?!php)') { $content = $content -replace '^<\?(?!php)', '<?php'; $modified = $true; echo '???PHP???? <? -> <?php'; }; if ($content -match '^<\?hp') { $content = $content -replace '^<\?hp', '<?php'; $modified = $true; echo '???PHP???? <?hp -> <?php'; }; if ($content -match '^<\?php;') { $content = $content -replace '^<\?php;', '<?php'; $modified = $true; echo '???PHP???? <?php; -> <?php'; }; if ($modified) { Set-Content -Path '%FILE_PATH%' -Value $content -NoNewline; }"

REM ????????????
powershell -Command "$content = Get-Content -Path '%FILE_PATH%' -Raw; $modified = $false; if ($content -match '([\"'']);\s*$') { $content = $content -replace '([\"'']);\s*$', '$1,'; $modified = $true; echo '?????????????'; }; if ($content -match '([''\""])\s*=>\s*([^,\s\n\r\]]+)([''\""]);\s*$') { $content = $content -replace '([''\""])\s*=>\s*([^,\s\n\r\]]+)([''\""]);\s*$', '$1 => $2$3,'; $modified = $true; echo '???????????'; }; if ($modified) { Set-Content -Path '%FILE_PATH%' -Value $content -NoNewline; }"

REM ??????
powershell -Command "$content = Get-Content -Path '%FILE_PATH%' -Raw; if ($content -match '//\s*?????\s*;') { $content = $content -replace '//\s*?????\s*;', '// ?????'; Set-Content -Path '%FILE_PATH%' -Value $content -NoNewline; echo '???????'; }"

echo ??????: %FILE_PATH%
echo.

exit /b 0
