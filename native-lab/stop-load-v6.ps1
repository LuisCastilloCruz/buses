param([Parameter(Mandatory=$true)][string]$Folder)
$ErrorActionPreference='Stop'
$root=(Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$resolved=(Resolve-Path -LiteralPath $Folder).Path
if(!$resolved.StartsWith((Join-Path $root '.native-runtime\load-pool-'),[StringComparison]::OrdinalIgnoreCase)){throw 'Not an owned V6 pool directory'}
$records=Get-Content (Join-Path $resolved 'processes.json') -Raw | ConvertFrom-Json
$verified=@()
foreach($record in $records){
 if($record.port -notin @(8888,19061,19062,19063,19064,19065,19066,19067,19068)){throw 'Unexpected port'}
 $process=Get-Process -Id $record.pid -ErrorAction SilentlyContinue
 if(!$process){continue}
 $expected=if($record.port -eq 8888){'C:\laragon\bin\nginx\nginx-1.28.2\nginx.exe'}else{'C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php-cgi.exe'}
 if($process.Path -ne $expected -or $process.StartTime.ToUniversalTime().ToString('o') -ne $record.start){throw 'PID identity changed; do not terminate'}
 $verified+=@{process=$process;record=$record}
}
foreach($entry in $verified | Where-Object {$_.record.port -eq 8888}){
 & $entry.process.Path -p ($resolved.Replace('\','/')+'/') -c nginx.conf -s quit
 if($LASTEXITCODE -ne 0){throw 'Graceful nginx stop failed'}
 $entry.process.WaitForExit(10000) | Out-Null
 if(!$entry.process.HasExited){throw 'nginx still active; do not stop workers'}
}
foreach($entry in $verified | Where-Object {$_.record.port -ne 8888}){Stop-Process -Id $entry.process.Id; $entry.process.WaitForExit(10000) | Out-Null}
Write-Output 'Stopped only identity-verified V6 load pool; primary HTTP and MySQL untouched.'
