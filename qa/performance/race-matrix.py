"""Additional actual BookingService races, commit-loss recovery and tenant isolation."""
import concurrent.futures,json,secrets,subprocess
from preflight import check,ROOT,PHP

def fixture(action,run,host='dental'):
 p=subprocess.run([PHP,'qa/performance/concurrency-fixture.php',host,'1',action,run],cwd=ROOT,capture_output=True,text=True,timeout=60)
 if p.returncode:raise RuntimeError('Fixture failure: '+p.stdout[:200])
 return json.loads(p.stdout)

def race(commands):
 children=[]
 try:
  for c in commands:
   f=ROOT/f'.native-runtime/matrix-input-{secrets.token_hex(12)}.json';f.write_text(json.dumps(c))
   children.append(subprocess.Popen([PHP,'qa/performance/concurrency-worker.php',c.get('host','dental'),str(c.get('actor',1)),str(f)],cwd=ROOT,stdin=subprocess.PIPE,stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True))
  with concurrent.futures.ThreadPoolExecutor(max_workers=len(children)) as pool:
   ready=list(pool.map(lambda p:p.stdout.readline(),children,timeout=60))
  if any(s.strip()!='READY' for s in ready):raise RuntimeError('Worker did not reach barrier')
  for p in children:p.stdin.write('GO\n');p.stdin.flush()
  results=[]
  for p,c in zip(children,commands):
   out,err=p.communicate(timeout=60)
   if c.get('crash_after_commit'):
    if p.returncode!=17:raise RuntimeError('Expected owned-worker interruption')
    results.append({'status':'commit-response-lost'});continue
   if p.returncode:raise RuntimeError('Worker error: '+out[:200])
   results.append(json.loads(out))
  return results
 finally:
  for p in children:
   if p.poll() is None:p.terminate();p.wait(timeout=10)

def command(data,action='book',**kwargs):return dict(data=data,action=action,key='matrix-'+secrets.token_hex(12),**kwargs)

def main():
 check();run=secrets.token_hex(12);fixture('prepare',run)
 data=json.loads((ROOT/f'.native-runtime/concurrency-{run}.json').read_text())['data'];evidence=[]
 def record(name,results):
  rows=fixture('verify',run)['rows'];active=[r for r in rows if r['status']!='cancelled']
  for i,a in enumerate(active):
   for b in active[i+1:]:
    if a['day']==b['day'] and a['start']<b['end'] and b['start']<a['end']:raise RuntimeError('Persisted overlap')
  evidence.append({'scenario':name,'results':results,'persisted_rows':rows,'no_overlaps':True})
  target=ROOT/f'.native-runtime/race-matrix-{run}.json';tmp=target.with_suffix('.tmp');tmp.write_text(json.dumps(evidence,indent=2));tmp.replace(target)
  print('PASS '+name,flush=True)
 original=race([command(data)])[0]['booking']
 result=race([command({'booking_id':original['id'],'version':original['version']},'cancel'),command(data)])
 assert result[0]['status']==200 and result[1]['status'] in [200,409];record('cancel versus new booking',result)
 data={**data,'start':'10:00'};original=race([command(data)])[0]['booking']
 result=race([command({'booking_id':original['id'],'version':original['version'],'day':data['day'],'start':'11:00'},'reschedule'),command({**data,'start':'11:00'})])
 assert sorted(r['status'] for r in result)==[200,409];record('reschedule versus new booking',result)
 pending=race([command({**data,'start':'12:00','status':'pending'})])[0]['booking']
 c=command({'booking_id':pending['id'],'version':pending['version']},'confirm');result=race([c,c])
 assert all(r['status']==200 for r in result) and sum(r['replayed'] for r in result)==1
 assert len({r['booking']['id'] for r in result})==1;record('duplicate concurrent confirmation',result)
 c=command({**data,'start':'13:00'});result=race([{**c,'crash_after_commit':True}]);result+=race([c])
 assert result[1]['replayed'] is True;record('process death after commit before confirmation',result)
 second=secrets.token_hex(12);fixture('prepare',second);other=json.loads((ROOT/f'.native-runtime/concurrency-{second}.json').read_text())['data']
 result=race([command({**data,'start':'14:00'}),command({**other,'start':'14:00'})]);assert all(r['status']==200 for r in result);record('independent professionals same tenant',result)
 tattoo=secrets.token_hex(12);fixture('prepare',tattoo,'tattoo');foreign=json.loads((ROOT/f'.native-runtime/concurrency-{tattoo}.json').read_text())['data']
 result=race([command({**data,'start':'15:00'}),command({**foreign,'start':'15:00'},host='tattoo')]);assert all(r['status']==200 for r in result)
 assert len(fixture('verify',tattoo,'tattoo')['rows'])==1;record('separate tenants simultaneous booking',result)
 result=race([command({**data,'start':'16:00'},actor=3)]);assert result[0]['status']==403;record('unauthorized viewer cannot write',result)
 print('RESULT 7 actual MySQL race/recovery scenarios; '+run)

if __name__=='__main__':main()
