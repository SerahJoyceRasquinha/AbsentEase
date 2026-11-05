# =====================================================================
#  AbsentEase - stop everything  (run by STOP_ABSENTEASE.bat)
#  Stops MySQL cleanly, then Apache, then closes the XAMPP Control Panel.
# =====================================================================

$CustomXamppPath = ""   # same as in start_absentease.ps1 if you set one there

$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
$candidates = @($CustomXamppPath, $env:XAMPP_HOME, "C:\xampp", "D:\xampp", "E:\xampp", "F:\xampp",
                "$env:ProgramFiles\xampp", "${env:ProgramFiles(x86)}\xampp")
$Xampp = $null
foreach ($c in $candidates) {
    if ($c -and (Test-Path (Join-Path $c "xampp-control.exe"))) { $Xampp = (Resolve-Path $c).Path; break }
}

Write-Host ""
Write-Host ">> Stopping MySQL" -ForegroundColor Cyan
if (Get-Process -Name "mysqld" -ErrorAction SilentlyContinue) {
    $stopped = $false
    if ($Xampp) {
        # Clean shutdown first (protects the database files)
        $config = Join-Path $ProjectRoot "backend\config\app_config.php"
        $user = "root"; $pass = ""
        $m = Select-String -Path $config -Pattern "const\s+DB_USER\s*=\s*'([^']*)'" -ErrorAction SilentlyContinue | Select-Object -First 1
        if ($m) { $user = $m.Matches[0].Groups[1].Value }
        $m = Select-String -Path $config -Pattern "const\s+DB_PASSWORD\s*=\s*'([^']*)'" -ErrorAction SilentlyContinue | Select-Object -First 1
        if ($m) { $pass = $m.Matches[0].Groups[1].Value }
        $adminArgs = @("--user=$user", "shutdown")
        if ($pass -ne "") { $adminArgs = @("--user=$user", "--password=$pass", "shutdown") }
        $p = Start-Process -FilePath (Join-Path $Xampp "mysql\bin\mysqladmin.exe") -ArgumentList $adminArgs -NoNewWindow -Wait -PassThru -ErrorAction SilentlyContinue
        for ($i = 0; $i -lt 20; $i++) {
            if (-not (Get-Process -Name "mysqld" -ErrorAction SilentlyContinue)) { $stopped = $true; break }
            Start-Sleep -Milliseconds 500
        }
    }
    if (-not $stopped) { Stop-Process -Name "mysqld" -Force -ErrorAction SilentlyContinue }
    Write-Host "   MySQL stopped" -ForegroundColor Green
} else {
    Write-Host "   MySQL was not running" -ForegroundColor Gray
}

Write-Host ">> Stopping Apache" -ForegroundColor Cyan
if (Get-Process -Name "httpd" -ErrorAction SilentlyContinue) {
    Stop-Process -Name "httpd" -Force -ErrorAction SilentlyContinue
    Write-Host "   Apache stopped" -ForegroundColor Green
} else {
    Write-Host "   Apache was not running" -ForegroundColor Gray
}

Write-Host ">> Closing the XAMPP Control Panel" -ForegroundColor Cyan
Stop-Process -Name "xampp-control" -Force -ErrorAction SilentlyContinue
Write-Host "   Done" -ForegroundColor Green
exit 0
