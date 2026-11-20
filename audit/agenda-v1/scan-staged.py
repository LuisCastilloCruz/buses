"""Check staged publication files; never print credential values."""
import json,re,subprocess
from pathlib import Path
root=Path(__file__).resolve().parents[2]
git=['git','-c','safe.directory='+root.as_posix(),'-C',str(root)]
def run(*a):return subprocess.check_output(git+list(a))
private=[]
for name in ['settings.json','accounts.json','mysql-admin.json']:
 p=root/'.agenda-runtime'/name
 if p.exists():
  obj=json.loads(p.read_text())
  for row in obj if isinstance(obj,list) else [obj]:
   private += [v.encode() for k,v in row.items() if k in ['password','db_password','app_key'] and isinstance(v,str) and v]
patterns=[re.compile(rb'-----BEGIN (?:RSA |EC |OPENSSH |ENCRYPTED )?PRIVATE KEY-----'),re.compile(rb'(?:sk-(?:proj-)?[A-Za-z0-9_-]{28,}|gh[pousr]_[A-Za-z0-9]{30,}|AKIA[A-Z0-9]{16})')]
paths=[p.decode() for p in run('diff','--cached','--name-only','-z','--diff-filter=ACM').split(b'\0') if p]
fail=[]
for p in paths:
 data=run('show',':'+p)
 if '.agenda-runtime' in p or p.endswith(('PRIVATE.md','.sql','.bundle','.p12','.pfx')) or p.split('/')[-1].startswith('.env'):fail.append((p,'private path'))
 if any(v in data for v in private):fail.append((p,'private runtime value'))
 if any(x.search(data) for x in patterns):fail.append((p,'secret marker'))
print('Scanned staged files:',len(paths))
for p,reason in fail:print('BLOCKED:',p,reason,'(value withheld)')
if fail:raise SystemExit(1)
print('PASS: no targeted secret markers or private runtime credentials in staged files.')
