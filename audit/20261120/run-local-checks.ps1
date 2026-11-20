param(
    [string]$Php = 'C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe'
)
$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
Push-Location $projectRoot
try {
    if (!(Test-Path -LiteralPath $Php)) { throw 'PHP executable not found' }
    & $Php -v
    $files = @(Get-ChildItem modules\AiReception,modules\Tattoo,modules\TattooInbox -Recurse -Filter '*.php' -File)
    foreach ($file in $files) {
        $result = & $Php -l $file.FullName 2>&1
        if ($LASTEXITCODE -ne 0) { throw ($result -join "`n") }
    }
    Write-Output "PASS: syntax on $($files.Count) module PHP files"
    foreach ($test in @('IntakeClassifierSmokeTest','MetaWebhookMockTest','SlotPlannerTest','TattooAvailabilityTest','SecurityRegressionTest')) {
        & $Php "modules/AiReception/Tests/$test.php"
        if ($LASTEXITCODE -ne 0) { throw "FAILED: $test" }
    }
    foreach ($test in @('LaravelCompatibilityTest','CalendarPersistenceTest')) {
        & $Php vendor/phpunit/phpunit/phpunit --no-configuration --bootstrap vendor/autoload.php "modules/AiReception/Tests/$test.php"
        if ($LASTEXITCODE -ne 0) { throw "FAILED: $test" }
    }
    Write-Output 'PASS: isolated checks complete; application .env was not loaded.'
} finally {
    Pop-Location
}
