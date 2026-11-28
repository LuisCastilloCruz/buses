param(
 [string]$Php = 'C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe',
 [string]$MySqlBase = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64'
)
$ErrorActionPreference='Stop'
$Php=[IO.Path]::GetFullPath($Php)
$MySqlBase=[IO.Path]::GetFullPath($MySqlBase)
$labRoot=(Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$runtime=Join-Path $labRoot '.native-runtime'
if (!(Test-Path -LiteralPath (Join-Path $labRoot 'vendor/autoload.php'))) { throw 'Falta vendor. Copia solo vendor desde la instalacion HP verificada; NO copies .env.' }
if (!(Test-Path -LiteralPath $Php)) { throw 'PHP 7.4 no encontrado.' }
$mysqld=Join-Path $MySqlBase 'bin/mysqld.exe'
if (!(Test-Path -LiteralPath $mysqld)) { throw 'MySQL 8.4 no encontrado.' }
foreach($folder in @('mysql-data','cache','storage/framework/sessions','storage/framework/views','storage/framework/cache','storage/logs')) { New-Item -ItemType Directory -Force -Path (Join-Path $runtime $folder) | Out-Null }
# Private credentials are accessible only to this Windows user, SYSTEM and Administrators.
$privateUser=[Security.Principal.WindowsIdentity]::GetCurrent().Name
& icacls $runtime /inheritance:r /grant:r "${privateUser}:(OI)(CI)F" '*S-1-5-18:(OI)(CI)F' '*S-1-5-32-544:(OI)(CI)F' | Out-Null
if($LASTEXITCODE -ne 0){throw 'No se pudo proteger la configuracion privada.'}
function Test-LabPort([int]$Port) {
 $client=New-Object System.Net.Sockets.TcpClient
 try{$task=$client.ConnectAsync('127.0.0.1',$Port);if(!$task.Wait(1000)){return $false};return $client.Connected}catch{return $false}finally{$client.Dispose()}
}
$slashData=(Join-Path $runtime 'mysql-data').Replace('\','/')
Push-Location $labRoot
try {
 if (!(Test-LabPort 13323)) {
  if (!(Test-Path -LiteralPath (Join-Path $runtime 'mysql-data/auto.cnf'))) {
   if (@(Get-ChildItem -LiteralPath (Join-Path $runtime 'mysql-data') -Force).Count) { throw 'Directorio MySQL no vacio sin marcador: inspeccion requerida.' }
   $init=Start-Process -FilePath $mysqld -ArgumentList @('--no-defaults','--initialize-insecure',"--basedir=`"$MySqlBase`"","--datadir=`"$slashData`"") -WindowStyle Hidden -Wait -PassThru -RedirectStandardOutput (Join-Path $runtime 'mysql-init.out.log') -RedirectStandardError (Join-Path $runtime 'mysql-initialize.log')
   if($init.ExitCode -ne 0){throw 'Inicializacion MySQL fallida.'}
  }
  $dbProcess=Start-Process -FilePath $mysqld -ArgumentList @('--no-defaults',"--basedir=`"$MySqlBase`"","--datadir=`"$slashData`"",'--port=13323','--bind-address=127.0.0.1','--mysqlx=0','--innodb-buffer-pool-size=64M','--max-connections=20','--performance-schema=OFF','--table-open-cache=128','--restrict-fk-on-non-standard-key=OFF','--console') -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $runtime 'mysql.out.log') -RedirectStandardError (Join-Path $runtime 'mysql.err.log')
  $dbProcess.Id | Set-Content (Join-Path $runtime 'mysql.pid')
  $ready=$false
  for($i=0;$i -lt 40;$i++){Start-Sleep -Milliseconds 250;if(Test-LabPort 13323){$ready=$true;break}}
  if(!$ready){throw 'MySQL no inicio. Revisar el registro privado.'}
 }
 & $Php native-lab/init.php
 if($LASTEXITCODE -ne 0){throw 'Configuracion privada fallida.'}
 # Every installer verifies port, datadir and an exact disposable database allowlist.
 foreach($database in @('mf_v7_system','mf_v7_tattoo','mf_v7_dental','mf_v7_commerce')) {
  & $Php native-lab/migrate-original.php $database
  if($LASTEXITCODE -ne 0){throw "Migracion fallida: $database"}
 }
 foreach($database in @('mf_v7_tattoo','mf_v7_dental','mf_v7_commerce')) {
  & $Php native-lab/migrate-modules.php $database
  if($LASTEXITCODE -ne 0){throw "Migracion de modulos fallida: $database"}
 }
 foreach($database in @('mf_v7_system','mf_v7_tattoo','mf_v7_dental','mf_v7_commerce')) {
  & $Php native-lab/seed.php $database
  if($LASTEXITCODE -ne 0){throw "Fixtures fallidos: $database"}
 }
 foreach($database in @('mf_v7_tattoo','mf_v7_dental','mf_v7_commerce')) {
  & $Php native-lab/configure-fixtures.php $database
  if($LASTEXITCODE -ne 0){throw "Configuracion fallida: $database"}
  & $Php native-lab/finish-fixtures.php $database
  if($LASTEXITCODE -ne 0){throw "Referencias fallidas: $database"}
 }
 if(Test-Path (Join-Path $runtime 'fourth-account.json')){
  & $Php native-lab/migrate-original.php mf_v7_cuarta
  if($LASTEXITCODE -ne 0){throw 'Migracion cuarta empresa fallida.'}
 }
 if (!(Test-LabPort 8879)) {
  $webProcess=Start-Process -FilePath $Php -ArgumentList '-d allow_url_fopen=0 -d disable_functions=curl_exec,curl_multi_exec,fsockopen,pfsockopen,stream_socket_client,mail -S 127.0.0.1:8879 -t public native-lab/router.php' -WorkingDirectory $labRoot -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $runtime 'web.out.log') -RedirectStandardError (Join-Path $runtime 'web.err.log')
  $webProcess.Id | Set-Content (Join-Path $runtime 'web.pid')
 }
 $sha=[Security.Cryptography.SHA256]::Create()
 $expected=([BitConverter]::ToString($sha.ComputeHash([Text.Encoding]::UTF8.GetBytes($labRoot.Replace('\','/').ToLowerInvariant())))).Replace('-','').ToLowerInvariant()
 $webReady=$false
 for($i=0;$i -lt 20;$i++){
  try{$response=Invoke-WebRequest 'http://127.0.0.1:8879/login' -UseBasicParsing -TimeoutSec 3;if($response.Headers['X-MultiFactura-Native'] -ne $expected){throw 'La URL pertenece a otra instancia.'};$webReady=$true;break}catch{if($i -eq 19){throw};Start-Sleep -Milliseconds 250}
 }
 if(!$webReady){throw 'Web no disponible.'}
 Write-Output 'MultiFactura: http://localhost:8879 | tattoo.localhost:8879 | dental.localhost:8879 | comercio.localhost:8879'
 Write-Output "Credenciales ficticias privadas: $runtime\ACCESS.txt"
} finally { Pop-Location }
