"""Bounded loopback baseline. The PHP development server is NOT production capacity."""
import argparse,concurrent.futures,datetime,importlib,json,math,subprocess,sys,threading,time,urllib.request,urllib.error
from preflight import ROOT,PHP,check
from resources import sample,require_margin
sys.path.insert(0,str(ROOT/'native-lab'))
http=importlib.import_module('http-smoke')

def main():
 p=argparse.ArgumentParser();p.add_argument('--seconds',type=int,default=20);p.add_argument('--users',default='1,5,10');p.add_argument('--soak',action='store_true');p.add_argument('--pool',action='store_true');args=p.parse_args()
 levels=[int(n) for n in args.users.split(',')]
 if any(n not in [1,5,10,20] for n in levels) or not 10<=args.seconds<=900:raise ValueError('Bounded local profile only')
 if args.soak and (levels!=[1] or args.seconds<600):raise ValueError('Initial soak requires 1 user, at least 600 seconds')
 report={'baseline':check(),'mode':'loopback PHP single-process; no production capacity claim','levels':[],'slo':{'read_p95_ms':2000,'unexpected_errors':0,'timeout_seconds':15}}
 port=8889 if args.pool else 8881
 if args.pool:
  report['mode']='loopback nginx with four PHP CGI workers; HP laboratory only'
  response=urllib.request.build_opener(urllib.request.ProxyHandler({})).open('http://127.0.0.1:8889/login',timeout=15)
  if response.headers.get('X-MultiFactura-Pool') not in ['v6-four-workers','v6-4-workers','v6-8-workers']:raise RuntimeError('Wrong load pool')
  report['pool_identity']=response.headers.get('X-MultiFactura-Pool')
  report['mode']='loopback nginx '+report['pool_identity']+'; HP laboratory only'
  report['opcode_cache']=response.headers.get('X-MultiFactura-Opcode-Cache','unknown')
  response.close()
 accounts=json.loads((ROOT/'.native-runtime/accounts.json').read_text())
 client=http.Client(accounts['dental'])
 cat=json.loads(client.request('/aireception/api/bootstrap')[1]);pro=cat['professionals'][0]['id'];svc=cat['services'][0]['id']
 day=(datetime.date.today()+datetime.timedelta(days=20)).isoformat()
 paths=['/aireception/api/bootstrap',f'/aireception/api/calendar?day={day}',f'/aireception/api/availability?professional_id={pro}&service_id={svc}&day={day}']
 req=urllib.request.Request(client.base+'/aireception/api/bootstrap');client.jar.add_cookie_header(req);cookie=req.get_header('Cookie')
 if not cookie:raise RuntimeError('No authenticated session')
 target=ROOT/'.native-runtime'/('http-load-'+datetime.datetime.now().strftime('%Y%m%d-%H%M%S')+'.json')
 def save():
  tmp=target.with_suffix('.tmp');tmp.write_text(json.dumps(report,indent=2));tmp.replace(target)
 for n in levels:
  require_margin(sample(ROOT)[0]);stop=threading.Event();results=[];metrics=[];mysql=[];lock=threading.Lock();start=time.monotonic();deadline=start+args.seconds
  def worker(index):
   opener=urllib.request.build_opener(urllib.request.ProxyHandler({}));counter=index
   while not stop.is_set() and time.monotonic()<deadline:
    begin=time.monotonic();status=0;error=None
    try:
     r=opener.open(urllib.request.Request(f'http://127.0.0.1:{port}'+paths[counter%len(paths)],headers={'Host':'dental.localhost:8881','Cookie':cookie,'Accept':'application/json'}),timeout=15)
     status=r.status;raw=r.read(2097153);r.close()
     if len(raw)>2097152:raise ValueError('Oversized response')
     json.loads(raw)
    except urllib.error.HTTPError as e:status=e.code;error='HTTP'
    except Exception as e:error=type(e).__name__
    with lock:results.append({'ms':(time.monotonic()-begin)*1000,'status':status,'error':error,'elapsed_s':time.monotonic()-start})
    counter+=1;stop.wait(.25 if not args.soak else 1)
  with concurrent.futures.ThreadPoolExecutor(max_workers=n) as pool:
   futures=[pool.submit(worker,i) for i in range(n)];previous=None;high=0
   while time.monotonic()<deadline and not stop.is_set():
    row,previous=sample(ROOT,previous);metrics.append(row)
    try:
     require_margin(row);high=high+1 if (row['cpu_percent'] or 0)>90 else 0
     if high>=5:raise RuntimeError('CPU above 90 percent for five samples')
     if len(metrics)%5==1:
      probe=subprocess.run([PHP,str(ROOT/'qa/performance/db-metrics.php')],cwd=ROOT,capture_output=True,text=True,timeout=20)
      if probe.returncode:raise RuntimeError('Database telemetry failed')
      db=json.loads(probe.stdout);db['elapsed_s']=time.monotonic()-start;mysql.append(db)
      if db['status']['Threads_connected']>db['max_connections']*.8:raise RuntimeError('MySQL connections above 80 percent')
    except RuntimeError as e:report['resource_stop']=str(e);stop.set()
    # Preserve partial evidence during long runs; an interrupted run remains incomplete.
    if len(metrics)%15==0:
     with lock:report['in_progress']={'users':n,'elapsed_seconds':time.monotonic()-start,'samples':list(results),'resources':list(metrics),'mysql':list(mysql)}
     save()
    stop.wait(2)
   for f in futures:f.result()
  elapsed=time.monotonic()-start;times=sorted(r['ms'] for r in results)
  def percentile(q):return times[max(0,math.ceil(len(times)*q)-1)] if times else None
  errors=sum(r['status']!=200 or r['error'] is not None for r in results)
  row={'users':n,'seconds':elapsed,'requests':len(results),'rps':len(results)/elapsed,'p50_ms':percentile(.5),'p95_ms':percentile(.95),'p99_ms':percentile(.99),'errors':errors,'error_rate':errors/max(1,len(results)),'samples':results,'resources':metrics,'mysql':mysql}
  row['slo_pass']=bool(results) and errors==0 and row['p95_ms']<=2000 and 'resource_stop' not in report
  report.pop('in_progress',None);report['levels'].append(row);save();print(json.dumps({k:v for k,v in row.items() if k not in ['samples','resources','mysql']}),flush=True)
  if 'resource_stop' in report:break
 report['finished']=True;save();print('Evidence: '+target.name)
 if any(not row['slo_pass'] for row in report['levels']) or 'resource_stop' in report:sys.exit(1)

if __name__=='__main__':main()
