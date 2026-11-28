$ErrorActionPreference='Stop'
$root=(Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$runtime=Join-Path $root '.native-runtime'
$settings=Get-Content (Join-Path $runtime 'settings.json') -Raw | ConvertFrom-Json
if($settings.purpose -ne 'multifactura-native-v6' -or $settings.root -ne $root.Replace('\','/')){throw 'Wrong laboratory identity'}
if(!(Test-Path (Join-Path $runtime 'mysql-data/auto.cnf'))){throw 'Existing database marker required; resume never initializes'}
$php='C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe'
$mysql='C:\laragon\bin\mysql\mysql-8.4.3-winx64'
function Listening([int]$port){$c=New-Object Net.Sockets.TcpClient;try{return $c.ConnectAsync('127.0.0.1',$port).Wait(500) -and $c.Connected}catch{return $false}finally{$c.Dispose()}}
$stamp=Get-Date -Format 'yyyyMMdd-HHmmss'
if(!(Listening 13321)){
 $data=(Join-Path $runtime 'mysql-data').Replace('\','/')
 $p=Start-Process (Join-Path $mysql 'bin/mysqld.exe') -ArgumentList @('--no-defaults',"--basedir=`"$mysql`"","--datadir=`"$data`"",'--port=13321','--bind-address=127.0.0.1','--mysqlx=0','--restrict-fk-on-non-standard-key=OFF','--console') -WindowStyle Hidden -PassThru -RedirectStandardOutput "$runtime/mysql-resume-$stamp.out.log" -RedirectStandardError "$runtime/mysql-resume-$stamp.err.log"
 $p.Id | Set-Content "$runtime/mysql.pid"
 for($i=0;$i -lt 60 -and !(Listening 13321);$i++){Start-Sleep -Milliseconds 500}
}
Push-Location $root
try{
 # Probe checks port, datadir and allowlisted database before starting HTTP.
 & $php qa/performance/db-metrics.php
 if($LASTEXITCODE -ne 0){throw 'Database identity verification failed'}
 if(!(Listening 8878)){
  $p=Start-Process $php -ArgumentList '-d allow_url_fopen=0 -d disable_functions=curl_exec,curl_multi_exec,fsockopen,pfsockopen,stream_socket_client,mail -S 127.0.0.1:8878 -t public native-lab/router.php' -WorkingDirectory $root -WindowStyle Hidden -PassThru -RedirectStandardOutput "$runtime/web-resume-$stamp.out.log" -RedirectStandardError "$runtime/web-resume-$stamp.err.log"
  $p.Id | Set-Content "$runtime/web.pid"
 }
 & python -X utf8 qa/performance/preflight.py
 if($LASTEXITCODE -ne 0){throw 'Recovery preflight failed'}
 Write-Output 'PASS resumed existing V6 services; no migrations or fixtures executed.'
}finally{Pop-Location}
