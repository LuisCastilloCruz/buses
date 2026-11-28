"""V7 foreground validation with immutable per-stage logs and fail-closed resume."""
from pathlib import Path
import argparse,datetime,hashlib,json,os,subprocess,sys,msvcrt
ROOT=Path(__file__).resolve().parents[1];RUNTIME=ROOT/'.native-runtime';PHP=r'C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe';NODE=r'C:\Program Files\nodejs\node.exe'

def fingerprint():
 h=hashlib.sha256()
 for folder in ['app','modules','gateway','native-lab','qa','database','.github']:
  for p in sorted((ROOT/folder).rglob('*')):
   if p.is_file() and p.suffix in ['.php','.mjs','.py','.ps1','.yml']:
    h.update(str(p.relative_to(ROOT)).encode());h.update(p.read_bytes())
 return h.hexdigest()

def snapshot():
 s=json.loads((RUNTIME/'settings.json').read_text());a=json.loads((RUNTIME/'admin.json').read_text());f=RUNTIME/'validation-digest-private.json';f.write_text(json.dumps({'port':13327,'user':'root','password':a['password'],'datadir':str(RUNTIME/'mysql-data')}))
 r=subprocess.run([PHP,'native-lab/recovery-digest.php',str(f)],cwd=ROOT,capture_output=True,timeout=180)
 if r.returncode:raise RuntimeError('Database snapshot unavailable; do not resume stateful stages')
 return hashlib.sha256(r.stdout).hexdigest()

def main():
 p=argparse.ArgumentParser();p.add_argument('--profile',choices=['smoke','integration','functional','browser','ollama','benchmark','recovery','full'],default='smoke');p.add_argument('--resume');a=p.parse_args()
 s=json.loads((RUNTIME/'settings.json').read_text());assert s['purpose']=='multifactura-native-v7-2' and Path(s['root']).resolve()==ROOT
 if (RUNTIME/'sandbox-authorization.json').exists():raise RuntimeError('External sandbox authorization must be absent')
 with (RUNTIME/'v7-validation.lock').open('a+b') as lock:
  lock.seek(0);lock.write(b'0');lock.flush();lock.seek(0);msvcrt.locking(lock.fileno(),msvcrt.LK_NBLCK,1)
  code=fingerprint();run=a.resume or datetime.datetime.now().strftime('%Y%m%d-%H%M%S')
  if not all(c.isdigit() or c=='-' for c in run):raise ValueError('Invalid run identifier')
  target=RUNTIME/('validation-v7-'+run);target.mkdir(exist_ok=bool(a.resume));reportfile=target/'results.json'
  if a.resume:
   report=json.loads(reportfile.read_text());assert report['code']==code and report['profile']==a.profile,'Code/profile changed: create a new validation run';assert report['database']==snapshot(),'Database changed since checkpoint: inspect partial stage before replay'
  else:report={'profile':a.profile,'run':run,'code':code,'database':snapshot(),'stages':[]}
  def save():reportfile.write_text(json.dumps(report,indent=2))
  save();stages=[]
  if a.profile in ['smoke','full']:
   stages += [('unit',[PHP,'native-lab/v7-unit-tests.php'],{}),('gateway',[NODE,'--test',*['gateway/'+f for f in ['core.test.mjs','http.test.mjs','concurrency.test.mjs','provider-failures.test.mjs','recovery.test.mjs','stress.test.mjs','ollama.test.mjs','ollama-gateway.test.mjs','inference-jobs.test.mjs','private-worker.test.mjs','v71-recovery.test.mjs']]],{})]
  if a.profile in ['integration','full']:
   stages += [('persistence',[PHP,'native-lab/v7-persistence-tests.php','dental'],{}),('tattoo',[PHP,'native-lab/v5-flow-tests.php','tattoo'],{}),('dental',[PHP,'native-lab/v5-flow-tests.php','dental'],{}),('concurrency',[sys.executable,'-X','utf8','native-lab/v71-concurrency.py'],{})]
  if a.profile in ['functional','full']:
   stages += [('http',[sys.executable,'-X','utf8','native-lab/http-tests.py'],{}),('agent-http',[sys.executable,'-X','utf8','native-lab/agent-http-tests.py'],{}),('v7-http',[sys.executable,'-X','utf8','native-lab/v7-http-tests.py'],{})]
  if a.profile in ['browser','full']:
   stages += [('chrome-'+phase,[NODE,'native-lab/browser.mjs'],{'AR_BROWSER_PHASE':phase}) for phase in ['tattoo','dental','comercio','system']]
   stages += [('chrome-v7',[NODE,'native-lab/browser-v7.mjs'],{})]
  if a.profile in ['ollama','full']:stages += [('real-model-tools',[NODE,'qa/ai-reception-evaluation/tools-real.mjs','validation-'+run],{})]
  if a.profile in ['benchmark','full']:stages += [('inference-benchmark',[NODE,'qa/ai-reception-evaluation/benchmark.mjs'],{})]
  if a.profile=='recovery':stages += [('recovery',[sys.executable,'-X','utf8','native-lab/backup-restore-v6.py','--offline-source'],{})]
  for name,command,env in stages:
   if any(x['name']==name and x['status']=='passed' for x in report['stages']):continue
   row={'name':name,'status':'running','started':datetime.datetime.now(datetime.timezone.utc).isoformat()};report['stages'].append(row);save()
   with (target/(name+'-'+str(len(report['stages']))+'.log')).open('wb') as log:
    try:result=subprocess.run(command,cwd=ROOT,env=dict(os.environ,**env),stdout=log,stderr=log,timeout=1200);row['exit_code']=result.returncode
    except subprocess.TimeoutExpired:row['exit_code']=124
   row['status']='passed' if row['exit_code']==0 else ('blocked' if row['exit_code']==2 else 'failed');row['finished']=datetime.datetime.now(datetime.timezone.utc).isoformat();report['database']=snapshot();save();print(name,row['status'],flush=True)
   if row['status']=='failed':break
  report['release_approved']=False;report['limitations']='No automatic approval of linguistic quality, external sandbox or production security';save()
  return 1 if any(x['status']=='failed' for x in report['stages']) else (2 if any(x['status']=='blocked' for x in report['stages']) else 0)
if __name__=='__main__':sys.exit(main())
