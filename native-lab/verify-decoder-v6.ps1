$ErrorActionPreference='Stop'
$root=(Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$folder=Join-Path $root '.native-runtime/tools/ffmpeg-9.1'
$pins=@{
 'ffmpeg.exe'='ed8f66cb877087a96f5bbb40a2944f0e764f2ad8870d5cb647360d912e62bc07'
 'ffprobe.exe'='df0199f606faa88e9ad192732a7c3987acfb647663534c297772e17730611a08'
}
foreach($name in $pins.Keys){
 $path=Join-Path $folder $name
 if(!(Test-Path -LiteralPath $path) -or (Get-FileHash -LiteralPath $path -Algorithm SHA256).Hash.ToLowerInvariant() -ne $pins[$name]){throw "Decoder binary verification failed: $name"}
}
Write-Output 'PASS FFmpeg/FFprobe 9.1 pinned binary SHA256 verification.'
