from pathlib import Path
import json,hashlib,socket,urllib.request,subprocess,sys,time
from resources import sample,require_margin
ROOT=Path(__file__).resolve().parents[2]
PHP=r'C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe'
def check():
 settings=json.loads((ROOT/'.native-runtime/settings.json').read_text())
 assert settings['purpose']=='multifactura-native-v6' and Path(settings['root']).resolve()==ROOT,'Wrong laboratory identity'
 assert not (ROOT/'.native-runtime/sandbox-authorization.json').exists(),'Load cannot run with sandbox authorization'
 row,previous=sample(ROOT);time.sleep(0.3);row,_=sample(ROOT,previous);require_margin(row)
 opener=urllib.request.build_opener(urllib.request.ProxyHandler({}));r=opener.open('http://127.0.0.1:8878/login',timeout=15)
 assert r.headers.get('X-MultiFactura-Native')==hashlib.sha256(ROOT.as_posix().lower().encode()).hexdigest(),'Wrong HTTP instance'
 probe=subprocess.run([PHP,str(ROOT/'qa/performance/db-metrics.php')],cwd=ROOT,capture_output=True,text=True,timeout=20)
 if probe.returncode:raise RuntimeError('Database identity/metrics verification failed')
 data=json.loads(probe.stdout);assert data['port']==13321 and data['database']=='mf_v6_system'
 row.update(database=data,root=ROOT.name,external_providers='disabled',http_mode='single-process baseline; not production capacity')
 return row
if __name__=='__main__':print(json.dumps(check(),indent=2))
