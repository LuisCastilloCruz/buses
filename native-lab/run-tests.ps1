param([string]$Php='C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe')
$ErrorActionPreference='Stop'
$root=(Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Push-Location $root
try {
 function Run-Php([string[]]$Arguments) { & $Php @Arguments; if($LASTEXITCODE -ne 0){throw 'PHP verification failed.'} }
 $paths=@('modules/AiReception','modules/Odontologia','modules/Tattoo','modules/TattooInbox','native-lab')
 $count=0
 foreach($path in $paths){foreach($file in Get-ChildItem -LiteralPath $path -Filter *.php -Recurse){& $Php -l $file.FullName | Out-Null;if($LASTEXITCODE -ne 0){throw "PHP syntax: $($file.FullName)"};$count++}}
 Write-Output "PASS syntax: $count PHP files"
 foreach($test in @('AudioContractsTest','ProviderContractsTest','IntakeClassifierSmokeTest','MetaWebhookMockTest','SecurityRegressionTest','SlotPlannerTest','TattooAvailabilityTest')){Run-Php @("modules/AiReception/Tests/$test.php")}
 foreach($test in @('LaravelCompatibilityTest','CalendarPersistenceTest')){Run-Php @('vendor/phpunit/phpunit/phpunit','--no-configuration','--bootstrap','vendor/autoload.php',"modules/AiReception/Tests/$test.php")}
 Run-Php @('native-lab/mysql-tests.php','dental')
 Run-Php @('native-lab/legacy-tests.php','tattoo')
 Run-Php @('native-lab/v4-persistence-tests.php','dental')
 Run-Php @('native-lab/proposal-tests.php','dental')
 Run-Php @('native-lab/attachment-tests.php','dental')
 Run-Php @('native-lab/budgeted-provider-tests.php','dental')
 Run-Php @('native-lab/conversation-tests.php','dental')
 Run-Php @('native-lab/reminder-policy-tests.php','dental')
 Run-Php @('native-lab/reminder-tests.php','dental')
 Run-Php @('native-lab/artisan.php','queue:work','aireception','--queue=aireception','--once','--tries=3','--sleep=0')
 Run-Php @('native-lab/artisan.php','queue:work','aireception','--queue=aireception','--once','--tries=3','--sleep=0')
 Run-Php @('native-lab/reminder-verify.php','dental')
 & python -u -X utf8 native-lab/http-tests.py
 if($LASTEXITCODE -ne 0){throw 'Native HTTP failed.'}
 & python -u -X utf8 native-lab/plan-http-tests.py
 if($LASTEXITCODE -ne 0){throw 'Plan HTTP failed.'}
 & python -u -X utf8 native-lab/agent-http-tests.py
 if($LASTEXITCODE -ne 0){throw 'Agent HTTP failed.'}
 & node native-lab/browser.mjs
 if($LASTEXITCODE -ne 0){throw 'Real Chrome failed.'}
 & node native-lab/browser-settings.mjs
 if($LASTEXITCODE -ne 0){throw 'Native settings Chrome failed.'}
 & node native-lab/browser-v4.mjs
 if($LASTEXITCODE -ne 0){throw 'V4 Chrome failed.'}
 Write-Output 'PASS all native local suites. No external providers.'
} finally {Pop-Location}
