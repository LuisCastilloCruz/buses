param([string]$Tenant='dental')
$ErrorActionPreference='Stop'
$env:AR_GATEWAY_CONFIG=Join-Path (Get-Location) '.native-runtime/ollama-gateway.json'
$gatewayV7=Start-Process -FilePath 'C:\Program Files\nodejs\node.exe' -ArgumentList 'gateway/server.mjs' -WorkingDirectory (Get-Location).Path -WindowStyle Hidden -PassThru -RedirectStandardOutput '.native-runtime/ollama-real-server.out' -RedirectStandardError '.native-runtime/ollama-real-server.err'
try {
 Start-Sleep -Seconds 1
 & 'C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe' native-lab/v7-ollama-real-tests.php $Tenant 1
 $result=$LASTEXITCODE
} finally { if (-not $gatewayV7.HasExited) { Stop-Process -Id $gatewayV7.Id }; Remove-Item Env:AR_GATEWAY_CONFIG }
exit $result
