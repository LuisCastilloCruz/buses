import subprocess,re,json
from pathlib import Path
root=Path(__file__).resolve().parents[1]
def git(*args):return subprocess.check_output(['git','-c','safe.directory='+root.as_posix(),*args],cwd=root)
files=git('diff','--cached','--name-only','-z').decode().split('\0');secrets=[]
for file in ['settings.json','admin.json','accounts.json']:
 p=root/'.native-runtime'/file
 if p.exists():
  def visit(v):
   if isinstance(v,dict):
    for k,x in v.items():
     if k in ['password','key','token'] and isinstance(x,str) and len(x)>=16:secrets.append(x.encode())
     else:visit(x)
   elif isinstance(v,list):
    for x in v:visit(x)
  visit(json.loads(p.read_text()))
patterns={'private-key':rb'-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----','aws-key':rb'AKIA[0-9A-Z]{16}','github-token':rb'(?:gh[pousr]_[A-Za-z0-9]{36,}|github_pat_[A-Za-z0-9_]{50,})','openai-key':rb'sk-(?:proj-)?[A-Za-z0-9_-]{24,}','google-key':rb'AIza[A-Za-z0-9_-]{35}','meta-token':rb'EA[A-Za-z0-9]{90,}'}
findings=[];count=0
for name in files:
 if not name:continue
 if any(x in name for x in ['.native-runtime','.agenda-runtime','.env','node_modules/','vendor/','PRIVATE.md','INFORME.md','__pycache__']) or name.endswith(('.sql','.bundle','.pfx','.pem','.key','.pyc')):findings.append((name,'private-path'));continue
 blob=git('show',':'+name);count+=1
 for secret in secrets:
  if secret in blob:findings.append((name,'runtime-secret'))
 if name.endswith('.png'):continue
 for label,pattern in patterns.items():
  for match in re.finditer(pattern,blob):
   value=match.group()
   if value.startswith(b'sk-test-') or value.startswith(b'sk-fake-'):continue
   findings.append((name,label,'offset',match.start()))
for f in findings:print('REVIEW',*f)
print('SCANNED',count,'staged files; findings',len(findings))
raise SystemExit(1 if findings else 0)
