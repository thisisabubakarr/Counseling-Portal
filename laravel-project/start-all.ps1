$ErrorActionPreference = 'Continue'
Set-Location $PSScriptRoot

function Step($n,$t){ Write-Host ""; Write-Host "[$n] $t" -ForegroundColor Cyan }
function Ok($m)  { Write-Host "      OK: $m"   -ForegroundColor Green }
function Info($m){ Write-Host "      $m"       -ForegroundColor Gray }
function Warn($m){ Write-Host "      $m"       -ForegroundColor Yellow }
function Die($m) {
  Write-Host "      FAIL: $m" -ForegroundColor Red
  Write-Host ""
  Write-Host "Stopped. Nothing was started." -ForegroundColor Red
  Write-Host ""
  Read-Host "Press Enter to close"
  exit 1
}
function Test-Oracle {
  try { $c = New-Object Net.Sockets.TcpClient; $c.Connect('127.0.0.1',1521); $c.Close(); return $true }
  catch { return $false }
}

Write-Host "=============================================="
Write-Host " BNU Counselor Portal - full stack start"
Write-Host "=============================================="

# ---------- 1. PHP ----------
Step 1 "Locating PHP"
$cand = @('C:\laragon\bin\php\php.exe')
$cand += (Get-ChildItem 'C:\laragon\bin\php' -Filter php.exe -Recurse -ErrorAction SilentlyContinue | ForEach-Object FullName)
$cand += "$env:USERPROFILE\scoop\apps\php\current\php.exe"
$cand += 'C:\xampp\php\php.exe'
$cand += (Get-Command php -ErrorAction SilentlyContinue | ForEach-Object Source)
$php = $cand | Where-Object { $_ -and (Test-Path $_) } | Select-Object -First 1
if (-not $php) { Die "no php.exe found (checked Laragon, scoop, XAMPP, PATH)" }
$phpVer = (& $php -r "echo PHP_VERSION;" 2>$null)
Ok "$php  (PHP $phpVer)"

# ---------- 2. oci8 ----------
Step 2 "Oracle driver (oci8)"
if ((& $php -m 2>$null) -match '^oci8$') {
  Ok "already loaded"
} else {
  Info "not loaded - trying to enable it"
  $iniRaw = (& $php --ini 2>$null | Select-String 'Loaded Configuration File' | ForEach-Object { $_.ToString() })
  $ini = if ($iniRaw) { ($iniRaw -split ':',2)[1].Trim() } else { $null }
  if (-not $ini -or $ini -eq '(none)' -or -not (Test-Path $ini)) { Die "PHP is running with no php.ini, so oci8 cannot be enabled automatically" }
  Info "php.ini: $ini"
  $extDir = (& $php -r "echo ini_get('extension_dir');" 2>$null)
  if (-not $extDir -or -not (Test-Path $extDir)) { $extDir = Join-Path (Split-Path $php) 'ext' }
  $dll = Get-ChildItem $extDir -Filter 'php_oci8*.dll' -ErrorAction SilentlyContinue | Select-Object -First 1
  if (-not $dll) { Die "no php_oci8*.dll in $extDir - the extension is not installed on this machine, so no config change can turn it on. It needs the oci8 DLL built for PHP $phpVer plus Oracle Instant Client." }
  $ext = ([IO.Path]::GetFileNameWithoutExtension($dll.Name)) -replace '^php_',''
  $bak = "$ini.bak-$(Get-Date -f yyyyMMdd-HHmmss)"
  Copy-Item $ini $bak
  Info "backed up php.ini -> $bak"
  $lines = Get-Content $ini
  $commented = '^\s*;\s*(extension\s*=\s*' + [regex]::Escape($ext) + '\s*)$'
  if ($lines -match $commented) {
    ($lines -replace $commented,'$1') | Set-Content $ini
    Info "uncommented extension=$ext"
  } else {
    Add-Content $ini "`r`nextension=$ext"
    Info "added extension=$ext"
  }
  if ((& $php -m 2>$null) -match '^oci8$') { Ok "oci8 now loaded" }
  else { Die "wrote extension=$ext into php.ini but PHP still reports oci8 missing - the Oracle Instant Client DLLs are probably not on PATH. php.ini backup is at $bak" }
}

# ---------- 3. Oracle ----------
Step 3 "Oracle database"

# service name from .env (never echo other .env values)
$svcName = 'FREEPDB1'
if (Test-Path '.env') {
  $m = (Get-Content '.env' | Select-String '^\s*DB_SERVICE_NAME\s*=\s*(.+)$').Matches
  if ($m.Count) { $svcName = $m[0].Groups[1].Value.Trim().Trim('"') }
}
Info "target service: $svcName"

# ground truth: can Laravel actually talk to it?
function Test-LaravelDb {
  & $php artisan migrate:status *> $null
  return ($LASTEXITCODE -eq 0)
}

function Invoke-Sysdba($sql) {
  if (-not (Get-Command sqlplus -ErrorAction SilentlyContinue)) { return $false }
  $tmp = Join-Path $env:TEMP "bnu_pdb_$([guid]::NewGuid().ToString('N')).sql"
  ($sql + "`r`nEXIT;`r`n") | Set-Content -Encoding ASCII $tmp
  $out = & sqlplus -S "/ as sysdba" "@$tmp" 2>&1
  Remove-Item $tmp -Force -ErrorAction SilentlyContinue
  # ORA-65019 = already open, which is a success for our purposes
  if ($out -match 'ORA-(?!65019)\d+') { Info ($out | Select-Object -First 2); return $false }
  return $true
}

if (Test-LaravelDb) {
  Ok "Laravel can reach $svcName"
} else {
  # a. listener down?
  if (-not (Test-Oracle)) {
    Info "nothing on 127.0.0.1:1521 - starting Oracle services"
    $admin = ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()
             ).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
    $svcs = Get-Service -ErrorAction SilentlyContinue |
            Where-Object { $_.Name -match 'Oracle(Service|.*TNSListener)' -and $_.Status -ne 'Running' }
    if ($svcs) {
      foreach ($s in $svcs) {
        Info "starting $($s.Name)..."
        if ($admin) { Start-Service $s.Name -ErrorAction SilentlyContinue }
        else {
          Warn "needs admin - approve the Windows prompt (no typing)"
          Start-Process powershell -Verb RunAs -Wait -ArgumentList @(
            '-NoProfile','-Command',"Start-Service -Name '$($s.Name)'") -ErrorAction SilentlyContinue
        }
      }
    } elseif (Get-Command docker -ErrorAction SilentlyContinue) {
      $hit = (docker ps -a --format '{{.Names}}|{{.Image}}' 2>$null |
              Where-Object { $_ -match 'oracle|freepdb|[-_]xe' } | Select-Object -First 1)
      if ($hit) { $cn=($hit -split '\|')[0]; Info "starting container '$cn'"; docker start $cn 2>$null | Out-Null }
    }
    Info "waiting for the listener..."
    foreach ($i in 1..36) { if (Test-Oracle) { break }; Start-Sleep 5; Write-Host "." -NoNewline }
    Write-Host ""
    if (-not (Test-Oracle)) { Die "Oracle never opened port 1521 on this machine" }
  }
  Ok "listener reachable"

  # b. listener up but service not usable -> PDB is MOUNTED, not OPEN (your ORA-01109 / ORA-12514)
  if (-not (Test-LaravelDb)) {
    Info "$svcName not serving yet - opening the pluggable database"
    $opened = Invoke-Sysdba @"
WHENEVER SQLERROR CONTINUE
ALTER PLUGGABLE DATABASE $svcName OPEN;
ALTER PLUGGABLE DATABASE $svcName SAVE STATE;
ALTER SYSTEM REGISTER;
"@
    if (-not $opened -and -not (Get-Command sqlplus -ErrorAction SilentlyContinue)) {
      Die "$svcName is not open and sqlplus is not on PATH, so it cannot be opened from here"
    }
    Info "waiting for $svcName to register with the listener..."
    foreach ($i in 1..24) { Start-Sleep 5; if (Test-LaravelDb) { break }; Write-Host "." -NoNewline }
    Write-Host ""
    if (-not (Test-LaravelDb)) {
      Die "$svcName still not reachable. Open it manually as sysdba: ALTER PLUGGABLE DATABASE $svcName OPEN;"
    }
    Ok "$svcName open and registered (SAVE STATE set, so it survives reboots)"
  } else {
    Ok "$svcName serving"
  }
}

# ---------- 4. node_modules ----------
Step 4 "Frontend dependencies"
if (Test-Path 'node_modules') { Ok "node_modules present" }
elseif (Get-Command npm -ErrorAction SilentlyContinue) { Info "installing..."; npm install; Ok "installed" }
else { Die "node_modules missing and npm is not installed" }

# ---------- 5. migrations ----------
Step 5 "Database schema"
& $php artisan migrate --force
if ($LASTEXITCODE -ne 0) { Die "migrations did not complete - not booting on a broken schema" }
Ok "schema current"

# ---------- 6. stale vite marker ----------
Step 6 "Stale Vite marker"
if (Test-Path 'public\hot') { Remove-Item 'public\hot' -Force; Ok "removed public\hot" } else { Ok "none present" }

# ---------- 7. launch ----------
Write-Host ""
Write-Host "=============================================="
Write-Host " Starting backend + queue + logs + frontend"
Write-Host " App:  http://localhost:8000"
Write-Host " Ctrl+C to stop"
Write-Host "=============================================="
Write-Host ""

$phar = @(
  (Join-Path (Split-Path $php) 'composer.phar'),
  'C:\laragon\bin\php\composer.phar'
) | Where-Object { Test-Path $_ } | Select-Object -First 1

if ($phar) {
  & $php $phar run dev
} elseif (Get-Command composer -ErrorAction SilentlyContinue) {
  composer run dev
} else {
  Warn "composer not found - launching the four processes in separate windows"
  Start-Process $php -ArgumentList 'artisan','serve'
  Start-Process $php -ArgumentList 'artisan','queue:listen','--tries=1'
  Start-Process $php -ArgumentList 'artisan','pail','--timeout=0'
  Start-Process 'npm' -ArgumentList 'run','dev'
  Ok "launched - see the new windows"
  Read-Host "Press Enter to close this one"
}
