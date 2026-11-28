"""Real MySQL writers, ready barrier, bounded resources, final persisted-state checks."""
import argparse,concurrent.futures,datetime,json,math,re,secrets,subprocess,sys,time
from pathlib import Path
from preflight import check,ROOT,PHP
from resources import sample,require_margin

def fixture(action,run):
 p=subprocess.run([PHP,'qa/performance/concurrency-fixture.php','dental','1',action,run],cwd=ROOT,capture_output=True,text=True,timeout=60)
 if p.returncode: raise RuntimeError('Fixture failed: '+p.stdout[:300]+p.stderr[:100])
 return json.loads(p.stdout)

def main():
 parser=argparse.ArgumentParser();parser.add_argument('--resume');args=parser.parse_args()
 if args.resume and not re.fullmatch('[a-f0-9]{24}',args.resume):raise ValueError('Invalid resume identifier')
 baseline=check();run=args.resume or secrets.token_hex(12)
 if not args.resume:fixture('prepare',run)
 data=json.loads((ROOT/f'.native-runtime/concurrency-{run}.json').read_text())['data']
 report={'run':run,'started':datetime.datetime.now(datetime.timezone.utc).isoformat(),'baseline':baseline,'mode':'real concurrent CLI/MySQL; not HTTP capacity','levels':[]}
 target=ROOT/f'.native-runtime/concurrency-results-{run}.json'
 if args.resume:
  report=json.loads(target.read_text())
  report.setdefault('resumed',[]).append(datetime.datetime.now(datetime.timezone.utc).isoformat())
 def save():
  tmp=target.with_suffix('.tmp');tmp.write_text(json.dumps(report,indent=2));tmp.replace(target)
 save()
 for index,n in enumerate([2,5,10,25,50]):
  if any(row['workers']==n and row['passed'] for row in report['levels']):
   rows=fixture('verify',run)['rows']
   if len([r for r in rows if r['start'][:5]==f'{9+index:02}:00' and r['status']!='cancelled'])!=1:raise RuntimeError('Checkpoint database invariant changed')
   continue
  resources,_=sample(ROOT);require_margin(resources)
  # Reserve conservative headroom for each PHP worker before spawning it.
  if resources['memory_free_mb']-n*80<2048:
   report['blocked_level']={'workers':n,'reason':'80 MiB per worker plus 2 GiB headroom','resources':resources};save();break
  if report.get('blocked_level',{}).get('workers')==n:report.pop('blocked_level')
  data['start']=f'{9+index:02}:00';children=[];results=[];key=f'v6-{run}-{n}'
  try:
   for i in range(n):
    f=ROOT/f'.native-runtime/concurrency-input-{run}-{n}-{i}.json'
    f.write_text(json.dumps({'data':data,'key':key+f'-{i}'}))
    children.append(subprocess.Popen([PHP,'qa/performance/concurrency-worker.php','dental','1',str(f)],cwd=ROOT,stdin=subprocess.PIPE,stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True))
   with concurrent.futures.ThreadPoolExecutor(max_workers=n) as pool:
    ready=list(pool.map(lambda p:p.stdout.readline(),children,timeout=60))
   if any(line.strip()!='READY' for line in ready):raise RuntimeError('Worker bootstrap failed')
   for p in children:p.stdin.write('GO\n');p.stdin.flush()
   for p in children:
    stdout,stderr=p.communicate(timeout=60)
    if p.returncode:raise RuntimeError('Worker failed: '+stdout[:200])
    results.append(json.loads(stdout))
   rows=fixture('verify',run)['rows']
   current=[r for r in rows if r['start'][:5]==data['start'] and r['status']!='cancelled']
   latencies=sorted(r['ms'] for r in results)
   row={'workers':n,'results':results,'persisted_count':len(current),'p50_ms':latencies[math.ceil(n*.5)-1],'p95_ms':latencies[math.ceil(n*.95)-1],'p99_ms':latencies[math.ceil(n*.99)-1],'resources':resources}
   # A resumed winner may be replayed; each losing key must still conflict.
   row['passed']=sum(r['status']==200 for r in results)==1 and sum(r['status']==409 for r in results)==n-1 and len(current)==1
   report['levels'].append(row);save()
   print(json.dumps({k:v for k,v in row.items() if k not in ('results','resources')}),flush=True)
   if not row['passed']:raise RuntimeError('Concurrency invariant failed')
  finally:
   # Only processes created by this invocation may be stopped on failure.
   for p in children:
    if p.poll() is None:p.terminate();p.wait(timeout=10)
 report['finished']=True;save();print('Evidence: '+target.name)
 if report.get('blocked_level'):
  print('BLOCKED by resource preflight: '+str(report['blocked_level']['workers'])+' workers; not executed')
  sys.exit(2)

if __name__=='__main__':main()
