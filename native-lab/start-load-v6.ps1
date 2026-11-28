$ErrorActionPreference='Stop'
$root=(Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$runtime=Join-Path $root '.native-runtime'
Push-Location $root
try{
 & python -X utf8 qa/performance/preflight.py
 if($LASTEXITCODE -ne 0){throw 'Preflight failed'}
 foreach($port in @(8888,19061,19062,19063,19064)){
  $c=New-Object Net.Sockets.TcpClient
  try{if($c.ConnectAsync('127.0.0.1',$port).Wait(300) -and $c.Connected){throw "Port already occupied: $port"}}catch [AggregateException]{}finally{$c.Dispose()}
 }
 $folder=Join-Path $runtime ('load-pool-'+(Get-Date -Format 'yyyyMMdd-HHmmss'))
 New-Item -ItemType Directory $folder | Out-Null
 New-Item -ItemType Directory (Join-Path $folder 'logs') | Out-Null
 New-Item -ItemType Directory (Join-Path $folder 'temp') | Out-Null
 $slash=$root.Replace('\','/')
 $prefix=$folder.Replace('\','/')
 $config=@'
worker_processes 1;
pid nginx.pid;
error_log error.log;
events { worker_connections 128; }
http {
 access_log access.log;
 client_body_temp_path client_temp;
 fastcgi_temp_path fastcgi_temp;
 upstream v6 { server 127.0.0.1:19061; server 127.0.0.1:19062; server 127.0.0.1:19063; server 127.0.0.1:19064; }
 server {
  listen 127.0.0.1:8888;
  server_name localhost *.localhost;
  client_max_body_size 1m;
  location / {
   fastcgi_pass v6;
   fastcgi_next_upstream off;
   fastcgi_read_timeout 30s;
   fastcgi_param SCRIPT_FILENAME "__LAB_ROOT__/native-lab/fastcgi.php";
   fastcgi_param SCRIPT_NAME /index.php;
   fastcgi_param QUERY_STRING $query_string;
   fastcgi_param REQUEST_METHOD $request_method;
   fastcgi_param CONTENT_TYPE $content_type;
   fastcgi_param CONTENT_LENGTH $content_length;
   fastcgi_param REQUEST_URI $request_uri;
   fastcgi_param DOCUMENT_URI /index.php;
   fastcgi_param DOCUMENT_ROOT "__LAB_ROOT__/public";
   fastcgi_param SERVER_PROTOCOL $server_protocol;
   fastcgi_param GATEWAY_INTERFACE CGI/1.1;
   fastcgi_param SERVER_SOFTWARE nginx;
   fastcgi_param REMOTE_ADDR $remote_addr;
   fastcgi_param REMOTE_PORT $remote_port;
   fastcgi_param SERVER_ADDR 127.0.0.1;
   fastcgi_param SERVER_PORT 8888;
   fastcgi_param SERVER_NAME $host;
   fastcgi_param REDIRECT_STATUS 200;
  }
 }
}
'@
 $config.Replace('__LAB_ROOT__',$slash) | Set-Content "$folder/nginx.conf" -Encoding Ascii
 $nginx='C:\laragon\bin\nginx\nginx-1.28.2\nginx.exe'
 & $nginx -p "$prefix/" -c nginx.conf -t
 if($LASTEXITCODE -ne 0){throw 'Invalid isolated nginx configuration'}
 $records=@()
 foreach($port in @(19061,19062,19063,19064)){
  $p=Start-Process 'C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php-cgi.exe' -ArgumentList @('-d','allow_url_fopen=0','-d','disable_functions=curl_exec,curl_multi_exec,fsockopen,pfsockopen,stream_socket_client,mail','-d','cgi.fix_pathinfo=0','-b',"127.0.0.1:$port") -WorkingDirectory $root -WindowStyle Hidden -PassThru -RedirectStandardOutput "$folder/php-$port.out.log" -RedirectStandardError "$folder/php-$port.err.log"
  $records+=@{pid=$p.Id;port=$port;start=$p.StartTime.ToUniversalTime().ToString('o')}
 }
 $p=Start-Process $nginx -ArgumentList @('-p',"`"$prefix/`"",'-c','nginx.conf') -WorkingDirectory $folder -WindowStyle Hidden -PassThru
 $records+=@{pid=$p.Id;port=8888;start=$p.StartTime.ToUniversalTime().ToString('o')}
 $records | ConvertTo-Json | Set-Content "$folder/processes.json"
 Write-Output "V6 load pool started: $folder; loopback 8888, four PHP CGI workers."
}finally{Pop-Location}
