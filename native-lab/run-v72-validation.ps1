param([ValidateSet('smoke','functional','browser','e2e','independent','stress','recovery')][string]$Profile='smoke')
$ErrorActionPreference='Stop'
$root=(Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Push-Location $root
try{
 $s=Get-Content '.native-runtime/settings.json' -Raw | ConvertFrom-Json
 if($s.purpose -ne 'multifactura-native-v7-2' -or [IO.Path]::GetFullPath($s.root) -ne $root){throw 'Wrong laboratory'}
 $node='C:\Program Files\nodejs\node.exe'
 $php='C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe'
 & $php native-lab/v72-policy-tests.php
 if($LASTEXITCODE -ne 0){throw 'Policy regression failed'}
 if($Profile -eq 'independent'){& $node qa/ai-reception-evaluation/v72-conversations.mjs --manifest=INDEPENDENT_MANIFEST --attempt=independent-final}
 elseif($Profile -eq 'stress'){& $node qa/ai-reception-evaluation/v72-conversations.mjs --stress ("--attempt=stress-"+(Get-Date -Format yyyyMMdd-HHmmss))}
 elseif($Profile -eq 'recovery'){& python -X utf8 native-lab/backup-restore-v6.py --offline-source}
 else{& powershell -NoProfile -File native-lab/run-v71-validation.ps1 -Profile $Profile}
 exit $LASTEXITCODE
}finally{Pop-Location}
