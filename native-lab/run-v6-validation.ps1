param(
 [ValidateSet('smoke','functional','integration','security','load','stress','soak','recovery','full')][string]$Profile='smoke',
 [switch]$Pool
)
$ErrorActionPreference='Stop'
$root=(Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$runtime=Join-Path $root '.native-runtime'
$php='C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe'
$node='C:\Program Files\nodejs\node.exe'
if(!(Test-Path "$runtime/settings.json")){throw 'Existing V6 runtime required'}
# OS releases this exclusive lock on process death; stale PID files cannot bypass it.
$lock=[IO.File]::Open("$runtime/validation.lock",[IO.FileMode]::OpenOrCreate,[IO.FileAccess]::ReadWrite,[IO.FileShare]::None)
$run=Get-Date -Format 'yyyyMMdd-HHmmss-fff'
$script:V6Blocked=@()
Push-Location $root
try {
 function Stage([string]$Name,[string]$Executable,[string[]]$Arguments){
  $log="$runtime/validation-$run-$Name.log"
  & python -X utf8 native-lab/checkpoint-v6.py --stage "$Name-running" --completed 'See previous checkpoints' --pending $Name --next 'Inspect interrupted stage before replaying stateful actions' --tests 'RUNNING; no pass implied'
  if($LASTEXITCODE -ne 0){throw 'Checkpoint failed'}
  & $Executable @Arguments *> $log
  $code=$LASTEXITCODE
  & python -X utf8 native-lab/checkpoint-v6.py --stage "$Name-finished" --completed "$Name exit=$code" --pending 'Remaining V6 acceptance criteria' --next 'Continue sequentially; investigate nonzero exit' --tests "$Name exit=$code; evidence=$log"
  if($code -eq 2 -and $Name -eq 'concurrency'){
   $script:V6Blocked+=$Name
   Write-Output "BLOCKED resource-limited level in $Name; continuing independent stages ($log)"
   return
  }
  if($code -ne 0){throw "Stage failed: $Name; inspect $log"}
  Write-Output "PASS $Name ($log)"
 }
 Stage 'preflight' 'python' @('-X','utf8','qa/performance/preflight.py')
 if($Profile -in @('smoke','security','full')){
  Stage 'token-unit' $php @('native-lab/token-guard-tests.php')
  Stage 'token-integration' $php @('native-lab/token-guard-integration.php','dental')
  Stage 'gateway' $node @('--test','gateway/core.test.mjs','gateway/http.test.mjs','gateway/concurrency.test.mjs','gateway/stress.test.mjs','gateway/recovery.test.mjs','gateway/provider-failures.test.mjs')
 }
 if($Profile -in @('functional','integration','full')){
  Stage 'regression' 'powershell' @('-NoProfile','-ExecutionPolicy','Bypass','-File','native-lab/run-v5-tests.ps1','-Full')
  Stage 'dashboard-loaded' $node @('native-lab/browser-dashboard-v6.mjs')
 }
 if($Profile -in @('integration','stress','full')){
  Stage 'race-matrix' 'python' @('-u','-X','utf8','qa/performance/race-matrix.py')
  Stage 'concurrency' 'python' @('-u','-X','utf8','qa/performance/concurrency.py')
  Stage 'synthetic-ingress' $node @('--test','gateway/stress.test.mjs','gateway/recovery.test.mjs')
 }
 if($Profile -in @('security','full')){
  Stage 'decoder-integrity' 'powershell' @('-NoProfile','-ExecutionPolicy','Bypass','-File','native-lab/verify-decoder-v6.ps1')
  $env:AR_TEST_FFMPEG="$runtime/tools/ffmpeg-9.1/ffmpeg.exe"
  $env:AR_TEST_FFPROBE="$runtime/tools/ffmpeg-9.1/ffprobe.exe"
  if(!(Test-Path $env:AR_TEST_FFMPEG) -or !(Test-Path $env:AR_TEST_FFPROBE)){throw 'Verified local decoder required; do not count a skipped test as pass'}
  Stage 'real-decoder' $node @('--test','gateway/audio-real.test.mjs')
 }
 if($Profile -in @('load','full')){
  $loadArgs=@('-u','-X','utf8','qa/performance/http-load.py');if($Pool){$loadArgs+='--pool'}
  Stage 'http-load' 'python' $loadArgs
 }
 if($Profile -in @('soak','full')){
  $soakArgs=@('-u','-X','utf8','qa/performance/http-load.py','--soak','--users','1','--seconds','600');if($Pool){$soakArgs+='--pool'}
  Stage 'http-soak' 'python' $soakArgs
 }
 if($Profile -in @('recovery','full')){
  Stage 'backup-restore' 'python' @('-u','-X','utf8','native-lab/backup-restore-v6.py')
 }
 if($Profile -eq 'full'){
  throw 'Implemented stages complete, but comprehensive fault injection and external/clinical acceptance remain pending. No release approval implied.'
 }
 if($script:V6Blocked.Count){Write-Output ('Incomplete resource-limited profile: '+($script:V6Blocked -join ', '));exit 2}
 Write-Output "Completed implemented profile $Profile; external sandbox and production approval excluded."
} finally {
 Pop-Location
 $lock.Dispose()
}
