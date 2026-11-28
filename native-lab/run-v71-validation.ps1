param([ValidateSet('smoke','functional','browser','e2e','coherence','worker','benchmark')][string]$Profile='smoke')
$ErrorActionPreference='Stop'
$root=(Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Push-Location $root
try {
 $settings=Get-Content '.native-runtime/settings.json' -Raw | ConvertFrom-Json
 if($settings.purpose -ne 'multifactura-native-v7-1' -or [IO.Path]::GetFullPath($settings.root) -ne $root){throw 'Wrong isolated lab'}
 $node='C:\Program Files\nodejs\node.exe'
 $php='C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe'
 if($Profile -eq 'benchmark'){& $node qa/ai-reception-evaluation/v71-benchmark.mjs;exit $LASTEXITCODE}
 if($Profile -eq 'coherence'){& $node qa/ai-reception-evaluation/v71-coherence.mjs --run=coherence-iteration-3;exit $LASTEXITCODE}
 if($Profile -eq 'worker'){& $node --test gateway/inference-jobs.test.mjs gateway/private-worker.test.mjs gateway/v71-recovery.test.mjs;exit $LASTEXITCODE}
 if($Profile -eq 'e2e'){
  & $node qa/ai-reception-evaluation/v71-preflight.mjs
  if($LASTEXITCODE -ne 0){throw 'Inference resource or health guard blocked E2E before provider calls'}
  foreach($tenant in @('dental','tattoo')){& $php native-lab/v71-e2e-real.php $tenant 1 prepare;if($LASTEXITCODE -ne 0){throw 'Fixture preparation failed'}}
  & $php native-lab/configure-ollama-v7.php dental 1
  if($LASTEXITCODE -ne 0){throw 'Private gateway configuration failed'}
  if(Get-NetTCPConnection -LocalPort 8899 -State Listen -ErrorAction SilentlyContinue){throw 'Gateway port occupied; inspect owner'}
  $env:AR_GATEWAY_CONFIG=Join-Path $root '.native-runtime/ollama-gateway.json'
  $gateway=Start-Process -FilePath $node -ArgumentList 'gateway/server.mjs' -WorkingDirectory $root -WindowStyle Hidden -PassThru -RedirectStandardOutput '.native-runtime/v71-gateway.out' -RedirectStandardError '.native-runtime/v71-gateway.err'
  try {Start-Sleep -Seconds 1;foreach($tenant in @('dental','tattoo')){& $php native-lab/v71-e2e-real.php $tenant 1;if($LASTEXITCODE -ne 0){throw "Real E2E failed for $tenant"}}}finally{$gateway.Refresh();if(!$gateway.HasExited){Stop-Process -Id $gateway.Id};Remove-Item Env:AR_GATEWAY_CONFIG}
 }else{& $php native-lab/v71-new-request-test.php;if($LASTEXITCODE -ne 0){throw 'New request regression failed'};& $php native-lab/v71-state-tests.php;if($LASTEXITCODE -ne 0){throw 'State regression failed'};& python -X utf8 native-lab/run-v7-validation.py --profile $Profile;exit $LASTEXITCODE}
}finally{Pop-Location}
