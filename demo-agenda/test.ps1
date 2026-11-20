param([string]$Php='C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe',[switch]$SkipBrowser)
$ErrorActionPreference='Stop'
$labRoot=(Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Push-Location $labRoot
try {
 & (Join-Path $PSScriptRoot 'start.ps1') -Php $Php
 $logs=Join-Path $labRoot '.agenda-runtime/test-logs'
 New-Item -ItemType Directory -Force -Path $logs | Out-Null
 & powershell -NoProfile -File audit/20261120/run-local-checks.ps1 -Php $Php | Tee-Object (Join-Path $logs 'baseline.txt')
 if($LASTEXITCODE -ne 0){throw 'Baseline checks failed.'}
 foreach($file in Get-ChildItem demo-agenda -Recurse -Filter '*.php' -File){& $Php -l $file.FullName | Out-Null;if($LASTEXITCODE -ne 0){throw "Syntax failed: $($file.FullName)"}}
 & node --check demo-agenda/public/assets/agenda.js
 if($LASTEXITCODE -ne 0){throw 'JS syntax failed.'}
 & $Php demo-agenda/tests/engine.php | Tee-Object (Join-Path $logs 'mysql.txt')
 if($LASTEXITCODE -ne 0){throw 'MySQL engine tests failed.'}
 & python -X utf8 demo-agenda/tests/http_flow.py | Tee-Object (Join-Path $logs 'http.txt')
 if($LASTEXITCODE -ne 0){throw 'HTTP tests failed.'}
 if(!$SkipBrowser){& node demo-agenda/tests/browser.mjs | Tee-Object (Join-Path $logs 'browser.txt');if($LASTEXITCODE -ne 0){throw 'Browser tests failed.'}}
 else{Write-Output 'SKIPPED: browser was explicitly omitted.'}
 Write-Output 'PASS: selected local test suites complete. No production database or external providers.'
} finally {Pop-Location}
