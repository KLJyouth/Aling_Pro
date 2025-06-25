@echo off
setlocal enabledelayedexpansion

title AlingAi Pro Documentation

:menu
cls
echo ===================================
echo     AlingAi Pro Documentation
echo ===================================
echo.
echo  1. User Guides
echo  2. Developer Guides
echo  3. Project Reports
echo  4. Project Structure
echo  5. Search Documentation
echo  0. Exit
echo.
echo ===================================
echo.

set /p choice=Enter your choice (0-5): 

if "%choice%"=="1" goto user_guides
if "%choice%"=="2" goto dev_guides
if "%choice%"=="3" goto project_reports
if "%choice%"=="4" goto project_structure
if "%choice%"=="5" goto search_docs
if "%choice%"=="0" goto end

echo Invalid choice. Please try again.
timeout /t 2 >nul
goto menu

:user_guides
cls
echo ===================================
echo           User Guides
echo ===================================
echo.
cd /d "%~dp0public\docs\guides"
echo Available User Guides:
echo ----------------------------
dir /b *user*.md
echo.
echo  0. Back to Main Menu
echo.
echo ===================================
echo.

set /p guide=Enter guide name (or 0 to go back): 
if "%guide%"=="0" goto menu

if exist "%guide%" (
    notepad "%guide%"
    goto user_guides
) else (
    echo Guide not found. Please try again.
    timeout /t 2 >nul
    goto user_guides
)

:dev_guides
cls
echo ===================================
echo        Developer Guides
echo ===================================
echo.
cd /d "%~dp0public\docs\guides"
echo Available Developer Guides:
echo ----------------------------
dir /b *dev*.md
echo.
echo  0. Back to Main Menu
echo.
echo ===================================
echo.

set /p guide=Enter guide name (or 0 to go back): 
if "%guide%"=="0" goto menu

if exist "%guide%" (
    notepad "%guide%"
    goto dev_guides
) else (
    echo Guide not found. Please try again.
    timeout /t 2 >nul
    goto dev_guides
)

:project_reports
cls
echo ===================================
echo        Project Reports
echo ===================================
echo.
cd /d "%~dp0public\docs\reports"
echo Available Project Reports:
echo ----------------------------
dir /b *.md
echo.
echo  0. Back to Main Menu
echo.
echo ===================================
echo.

set /p report=Enter report name (or 0 to go back): 
if "%report%"=="0" goto menu

if exist "%report%" (
    notepad "%report%"
    goto project_reports
) else (
    echo Report not found. Please try again.
    timeout /t 2 >nul
    goto project_reports
)

:project_structure
cls
echo ===================================
echo        Project Structure
echo ===================================
echo.
notepad "%~dp0PROJECT_STRUCTURE.md"
goto menu

:search_docs
cls
echo ===================================
echo        Search Documentation
echo ===================================
echo.
set /p search_term=Enter search term: 
echo.
echo Searching for "%search_term%" in all documentation files...
echo.

cd /d "%~dp0public\docs"
echo Results in Guides:
echo ----------------------------
findstr /s /i /c:"%search_term%" "guides\*.md"
echo.
echo Results in Reports:
echo ----------------------------
findstr /s /i /c:"%search_term%" "reports\*.md"
echo.
echo.
echo Search completed.
echo.
pause
goto menu

:end
echo Thank you for using AlingAi Pro Documentation.
timeout /t 2 >nul
exit /b 0 