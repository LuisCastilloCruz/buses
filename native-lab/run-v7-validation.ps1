param([ValidateSet('smoke','integration','functional','browser','ollama','benchmark','recovery','full')][string]$Profile='smoke',[string]$Resume='')
$ErrorActionPreference='Stop'
Push-Location (Join-Path $PSScriptRoot '..')
try {$arguments=@('-X','utf8','native-lab/run-v7-validation.py','--profile',$Profile);if($Resume){$arguments+=@('--resume',$Resume)};& python @arguments;exit $LASTEXITCODE}finally{Pop-Location}
