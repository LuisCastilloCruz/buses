param(
 [string]$Php = 'C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe',
 [string]$MySqlBase = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64'
)
$ErrorActionPreference='Stop'
$Php=[IO.Path]::GetFullPath($Php)
$MySqlBase=[IO.Path]::GetFullPath($MySqlBase)
$labRoot=(Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$runtime=Join-Path $labRoot '.agenda-runtime'
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
 if (!(Test-LabPort 13317)) {
  if (!(Test-Path -LiteralPath (Join-Path $runtime 'mysql-data/auto.cnf'))) {
   if (@(Get-ChildItem -LiteralPath (Join-Path $runtime 'mysql-data') -Force).Count) { throw 'Directorio MySQL no vacio sin marcador: inspeccion requerida.' }
   $init=Start-Process -FilePath $mysqld -ArgumentList @('--no-defaults','--initialize-insecure',"--basedir=`"$MySqlBase`"","--datadir=`"$slashData`"") -WindowStyle Hidden -Wait -PassThru -RedirectStandardOutput (Join-Path $runtime 'mysql-init.out.log') -RedirectStandardError (Join-Path $runtime 'mysql-initialize.log')
   if($init.ExitCode -ne 0){throw 'Inicializacion MySQL fallida.'}
  }
  $dbProcess=Start-Process -FilePath $mysqld -ArgumentList @('--no-defaults',"--basedir=`"$MySqlBase`"","--datadir=`"$slashData`"",'--port=13317','--bind-address=127.0.0.1','--mysqlx=0','--console') -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $runtime 'mysql.out.log') -RedirectStandardError (Join-Path $runtime 'mysql.err.log')
  $dbProcess.Id | Set-Content (Join-Path $runtime 'mysql.pid')
  $ready=$false
  for($i=0;$i -lt 40;$i++){Start-Sleep -Milliseconds 250;if(Test-LabPort 13317){$ready=$true;break}}
  if(!$ready){throw 'MySQL no inicio. Revisar el registro privado.'}
 }
 & $Php demo-agenda/init-mysql.php
 if($LASTEXITCODE -ne 0){throw 'Configuracion privada fallida.'}
 & $Php demo-agenda/setup.php
 if($LASTEXITCODE -ne 0){throw 'Verificacion o migracion de laboratorio fallida.'}
 if (!(Test-LabPort 8874)) {
  $webProcess=Start-Process -FilePath $Php -ArgumentList '-S 127.0.0.1:8874 -t demo-agenda/public demo-agenda/router.php' -WorkingDirectory $labRoot -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $runtime 'web.out.log') -RedirectStandardError (Join-Path $runtime 'web.err.log')
  $webProcess.Id | Set-Content (Join-Path $runtime 'web.pid')
 }
 $sha=[Security.Cryptography.SHA256]::Create()
 $expected=([BitConverter]::ToString($sha.ComputeHash([Text.Encoding]::UTF8.GetBytes($labRoot.Replace('\','/').ToLowerInvariant())))).Replace('-','').ToLowerInvariant()
 $webReady=$false
 for($i=0;$i -lt 20;$i++){
  try{$response=Invoke-WebRequest 'http://127.0.0.1:8874/login' -UseBasicParsing -TimeoutSec 3;if($response.Headers['X-Agenda-Lab'] -ne $expected){throw 'La URL pertenece a otra instancia.'};$webReady=$true;break}catch{if($i -eq 19){throw};Start-Sleep -Milliseconds 250}
 }
 if(!$webReady){throw 'Web no disponible.'}
 Write-Output 'Agenda: http://127.0.0.1:8874'
 Write-Output "Credenciales ficticias privadas: $runtime\ACCESS.txt"
} finally { Pop-Location }
