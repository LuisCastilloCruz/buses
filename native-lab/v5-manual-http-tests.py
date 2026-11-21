import importlib,json,secrets,subprocess
from pathlib import Path
m=importlib.import_module('http-smoke');root=Path(__file__).resolve().parents[1];php='C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe'
subprocess.run([php,'native-lab/v5-manual-fixture.php','dental'],cwd=root,check=True)
f=json.loads((root/'.native-runtime/manual-fixture.json').read_text());accounts=json.loads((root/'.native-runtime/accounts.json').read_text());c=m.Client(accounts['dental']);key=secrets.token_hex(16)
for _ in range(2):
 code,body=c.request('/aireception/conversations/'+str(f['conversation_id'])+'/reply',{'text':'Respuesta manual FICTICIA V5','request_key':key});assert code==200
subprocess.run([php,'native-lab/v5-manual-fixture.php','dental','1','verify',key],cwd=root,check=True)
print('RESULT manual reply HTTP and persistent dedup approved; no external delivery')