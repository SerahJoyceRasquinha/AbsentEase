# =====================================================================
#  AbsentEase - one-click starter  (run by START_ABSENTEASE.bat)
#
#  1. Finds your XAMPP folder
#  2. Starts Apache and MySQL (skips whichever is already running)
#  3. Opens the XAMPP Control Panel (shows both as running)
#  4. Runs database\setup_database.php: creates the database on first run,
#     upgrades an older AbsentEase database in place (keeps your data)
#  5. Makes the project reachable at http://localhost/... even if this
#     folder is NOT inside htdocs (it creates a shortcut-folder "junction")
#  6. Opens the site in a new Chrome tab
# =====================================================================

# If XAMPP is somewhere unusual, put the folder here, e.g. "F:\tools\xampp"
$CustomXamppPath = ""

$ErrorActionPreference = "Stop"
$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
$SchemaFile  = Join-Path $ProjectRoot "database\absentease_schema.sql"
$ConfigFile  = Join-Path $ProjectRoot "backend\config\app_config.php"

function Say($text, $color = "Gray") { Write-Host $text -ForegroundColor $color }
function Step($text)  { Write-Host ""; Write-Host ">> $text" -ForegroundColor Cyan }
function Fail($text)  { Write-Host ""; Write-Host "ERROR: $text" -ForegroundColor Red; exit 1 }

# Is something listening on this TCP port on this PC?
function Test-Port([int]$port) {
    $client = New-Object System.Net.Sockets.TcpClient
    try {
        $connect = $client.BeginConnect("127.0.0.1", $port, $null, $null)
        $ok = $connect.AsyncWaitHandle.WaitOne(400) -and $client.Connected
        return [bool]$ok
    } catch { return $false } finally { $client.Close() }
}

function Wait-Port([int]$port, [int]$seconds, [string]$name) {
    for ($i = 0; $i -lt ($seconds * 2); $i++) {
        if (Test-Port $port) { return $true }
        Start-Sleep -Milliseconds 500
    }
    return $false
}

# Read a value like:  const DB_PASSWORD = 'secret';  from backend\config\app_config.php
function Read-PhpConst([string]$name, [string]$default) {
    if (-not (Test-Path $ConfigFile)) { return $default }
    $m = Select-String -Path $ConfigFile -Pattern ("const\s+" + $name + "\s*=\s*'([^']*)'") | Select-Object -First 1
    if ($m) { return $m.Matches[0].Groups[1].Value }
    return $default
}

Write-Host ""
Write-Host "  =============================================" -ForegroundColor Green
Write-Host "             Starting AbsentEase" -ForegroundColor Green
Write-Host "  =============================================" -ForegroundColor Green
Say "  Project folder: $ProjectRoot"

# ---------------------------------------------------------------------
Step "1/6  Finding XAMPP"
$candidates = @($CustomXamppPath, $env:XAMPP_HOME, "C:\xampp", "D:\xampp", "E:\xampp", "F:\xampp",
                "$env:ProgramFiles\xampp", "${env:ProgramFiles(x86)}\xampp")
$Xampp = $null
foreach ($c in $candidates) {
    if ($c -and (Test-Path (Join-Path $c "xampp-control.exe"))) { $Xampp = (Resolve-Path $c).Path; break }
}
if (-not $Xampp) {
    Fail "XAMPP was not found in C:\xampp, D:\xampp, E:\xampp or F:\xampp.`n       Open launcher\start_absentease.ps1 in Notepad and set `$CustomXamppPath to your XAMPP folder."
}
Say "  XAMPP: $Xampp" Green

$HttpdExe   = Join-Path $Xampp "apache\bin\httpd.exe"
$HttpdConf  = Join-Path $Xampp "apache\conf\httpd.conf"
$MysqldExe  = Join-Path $Xampp "mysql\bin\mysqld.exe"
$MysqlExe   = Join-Path $Xampp "mysql\bin\mysql.exe"
$MyIni      = Join-Path $Xampp "mysql\bin\my.ini"

# Ports + web folder from XAMPP's own config (in case you changed 80 -> 8080 etc.)
$ApachePort = 80
$Htdocs = Join-Path $Xampp "htdocs"
if (Test-Path $HttpdConf) {
    $listen = Select-String -Path $HttpdConf -Pattern '^\s*Listen\s+(?:[\d\.]+:)?(\d+)' | Select-Object -First 1
    if ($listen) { $ApachePort = [int]$listen.Matches[0].Groups[1].Value }
    $docRoot = Select-String -Path $HttpdConf -Pattern '^\s*DocumentRoot\s+"([^"]+)"' | Select-Object -First 1
    if ($docRoot) { $Htdocs = $docRoot.Matches[0].Groups[1].Value -replace '/', '\' }
}
$MysqlPort = 3306
if (Test-Path $MyIni) {
    $inMysqld = $false
    foreach ($line in Get-Content $MyIni) {
        if ($line -match '^\s*\[(.+)\]') { $inMysqld = ($matches[1] -eq "mysqld") ; continue }
        if ($inMysqld -and $line -match '^\s*port\s*=\s*(\d+)') { $MysqlPort = [int]$matches[1]; break }
    }
}
Say "  Apache port: $ApachePort    MySQL port: $MysqlPort    Web folder: $Htdocs"

# ---------------------------------------------------------------------
Step "2/6  Starting Apache and MySQL"
if (Test-Port $ApachePort) {
    Say "  Apache: already running" Green
} else {
    Start-Process -FilePath $HttpdExe -WorkingDirectory $Xampp -WindowStyle Hidden
    if (Wait-Port $ApachePort 30 "Apache") { Say "  Apache: started" Green }
    else { Fail "Apache did not start on port $ApachePort.`n       Another program (Skype, IIS, VMware...) may be using the port. Check the XAMPP Control Panel log." }
}

if (Test-Port $MysqlPort) {
    Say "  MySQL : already running" Green
} else {
    Start-Process -FilePath $MysqldExe -ArgumentList '--defaults-file=mysql\bin\my.ini', '--standalone' -WorkingDirectory $Xampp -WindowStyle Hidden
    if (Wait-Port $MysqlPort 40 "MySQL") { Say "  MySQL : started" Green }
    else { Fail "MySQL did not start on port $MysqlPort. Open the XAMPP Control Panel and look at the MySQL log." }
}

# ---------------------------------------------------------------------
Step "3/6  Opening the XAMPP Control Panel"
if (Get-Process -Name "xampp-control" -ErrorAction SilentlyContinue) {
    Say "  Already open" Green
} else {
    Start-Process -FilePath (Join-Path $Xampp "xampp-control.exe") -WorkingDirectory $Xampp
    Say "  Opened" Green
}

# ---------------------------------------------------------------------
Step "4/6  Preparing the database (create on first run / upgrade old versions)"
$PhpExe = Join-Path $Xampp "php\php.exe"
$SetupScript = Join-Path $ProjectRoot "database\setup_database.php"
if (Test-Path $PhpExe) {
    # database\setup_database.php: creates tables, upgrades an older AbsentEase database in place, keeps your data
    & $PhpExe $SetupScript $MysqlPort
    if ($LASTEXITCODE -ne 0) {
        Say "  WARNING: the database setup reported a problem (see above). The site will still open." Yellow
    }
} else {
    # No php.exe (unusual XAMPP): fall back to importing the schema file (fresh installs only)
    $dbUser = Read-PhpConst "DB_USER" "root"
    $dbPass = Read-PhpConst "DB_PASSWORD" ""
    $mysqlArgs = @("--user=$dbUser", "--host=127.0.0.1", "--port=$MysqlPort", "--default-character-set=utf8mb4")
    if ($dbPass -ne "") { $mysqlArgs += "--password=$dbPass" }
    $errFile = Join-Path $env:TEMP "absentease_db_error.txt"
    $proc = Start-Process -FilePath $MysqlExe -ArgumentList $mysqlArgs -RedirectStandardInput $SchemaFile `
            -RedirectStandardError $errFile -NoNewWindow -Wait -PassThru
    if ($proc.ExitCode -eq 0) { Say "  Database ready" Green }
    else { Say ("  WARNING: could not import database\absentease_schema.sql : " + (Get-Content $errFile -Raw)) Yellow }
}

# ---------------------------------------------------------------------
Step "5/6  Making the project reachable in the browser"
$htdocsFull  = [System.IO.Path]::GetFullPath($Htdocs).TrimEnd('\') + '\'
$projectFull = [System.IO.Path]::GetFullPath($ProjectRoot).TrimEnd('\')

if (($projectFull + '\').StartsWith($htdocsFull, [System.StringComparison]::OrdinalIgnoreCase)) {
    $relative = $projectFull.Substring($htdocsFull.Length)
    Say "  Project is inside htdocs - nothing to do" Green
} else {
    # Not inside htdocs: create htdocs\AbsentEase -> this folder (a junction, like a shortcut folder; no admin needed)
    $linkName = "AbsentEase"
    $link = Join-Path $Htdocs $linkName
    if (Test-Path $link) {
        $item = Get-Item $link -Force
        if ($item.Attributes -band [System.IO.FileAttributes]::ReparsePoint) {
            cmd /c "rmdir `"$link`"" | Out-Null             # removes only the old link, never files
        } else {
            $linkName = "AbsentEase_launcher"                # a real folder with that name exists: don't touch it
            $link = Join-Path $Htdocs $linkName
            if (Test-Path $link) { cmd /c "rmdir `"$link`"" | Out-Null }
        }
    }
    cmd /c "mklink /J `"$link`" `"$projectFull`"" | Out-Null
    if (-not (Test-Path (Join-Path $link "index.php"))) { Fail "Could not create the link $link -> $projectFull" }
    $relative = $linkName
    Say "  Linked $link  ->  $projectFull" Green
}

$urlPath = ($relative -split '\\' | Where-Object { $_ -ne "" } | ForEach-Object { [uri]::EscapeDataString($_) }) -join '/'
$portPart = ""
if ($ApachePort -ne 80) { $portPart = ":$ApachePort" }
$Url = "http://localhost$portPart/$urlPath/"
Say "  URL: $Url" Green

# Wait until the page really answers (Apache may need a moment)
for ($i = 0; $i -lt 20; $i++) {
    try {
        $r = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec 3
        if ($r.StatusCode -eq 200) { break }
    } catch { Start-Sleep -Milliseconds 500 }
}

# ---------------------------------------------------------------------
Step "6/6  Opening Chrome"
$chrome = $null
$chromePaths = @("$env:ProgramFiles\Google\Chrome\Application\chrome.exe",
                 "${env:ProgramFiles(x86)}\Google\Chrome\Application\chrome.exe",
                 "$env:LocalAppData\Google\Chrome\Application\chrome.exe")
foreach ($p in $chromePaths) { if ($p -and (Test-Path $p)) { $chrome = $p; break } }
if (-not $chrome) {
    $reg = Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\chrome.exe" -ErrorAction SilentlyContinue
    if ($reg -and (Test-Path $reg.'(default)')) { $chrome = $reg.'(default)' }
}
if ($chrome) {
    Start-Process -FilePath $chrome -ArgumentList "--new-tab", $Url
    Say "  Opened in Chrome" Green
} else {
    Start-Process $Url
    Say "  Chrome not found - opened in your default browser" Yellow
}

Write-Host ""
Write-Host "  AbsentEase is running at $Url" -ForegroundColor Green
Write-Host "  Default admin login: admin@absentease.com / admin123" -ForegroundColor Green
Write-Host "  To shut everything down, double-click STOP_ABSENTEASE.bat" -ForegroundColor Green
exit 0
