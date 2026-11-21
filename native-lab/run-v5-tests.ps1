param([switch]$Full,[string]$Php='C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe',[string]$Node='C:\Program Files\nodejs\node.exe')
$ErrorActionPreference='Stop'
Push-Location (Join-Path $PSScriptRoot '..')
try {
 if($Full){& ./native-lab/run-tests.ps1 -Php $Php}
 & $Node --test gateway/core.test.mjs gateway/http.test.mjs
 if($LASTEXITCODE -ne 0){throw 'Gateway tests failed'}
 foreach($hostName in @('tattoo','dental')){& $Php native-lab/v5-flow-tests.php $hostName;if($LASTEXITCODE -ne 0){throw "V5 flow failed: $hostName"}}
 & $Php native-lab/diagnose-fourth.php cuarta
 if($LASTEXITCODE -ne 0){throw 'Fourth company permissions failed'}
 python -u -X utf8 native-lab/v5-manual-http-tests.py
 if($LASTEXITCODE -ne 0){throw 'Manual reply HTTP failed'}
 Write-Output 'PASS V5 simulated circuit. No external provider approval is implied.'
}finally{Pop-Location}