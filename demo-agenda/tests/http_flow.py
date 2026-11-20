"""Real loopback HTTP + Laravel sessions/CSRF + disposable MySQL. No external providers."""
import json, re, urllib.request, urllib.parse, urllib.error, http.cookiejar, datetime, uuid
from pathlib import Path
ROOT=Path(__file__).resolve().parents[2]
accounts=json.loads((ROOT/'.agenda-runtime/accounts.json').read_text())
n=0
def check(ok,name):
 global n
 assert ok,name
 n+=1;print('PASS '+name)
class Client:
 def __init__(self):
  self.opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()));self.csrf=''
 def call(self,path,body=None,key=None,csrf=True,form=False):
  h={'Accept':'application/json'}
  if csrf and self.csrf:h['X-CSRF-TOKEN']=self.csrf
  if key:h['Idempotency-Key']=key
  raw=None
  if body is not None:
   raw=(urllib.parse.urlencode(body) if form else json.dumps(body)).encode();h['Content-Type']='application/x-www-form-urlencoded' if form else 'application/json'
  req=urllib.request.Request('http://127.0.0.1:8874'+path,data=raw,headers=h)
  try:
   with self.opener.open(req) as r:status=r.status;text=r.read().decode()
  except urllib.error.HTTPError as e:status=e.code;text=e.read().decode()
  try:return status,json.loads(text)
  except ValueError:return status,text
 def login(self,id):
  status,page=self.call('/login');assert status==200
  token=re.search(r'name="_token" value="([^"]+)"',page).group(1)
  a=next(a for a in accounts if a['id']==id)
  status,page=self.call('/login',{'email':a['email'],'password':a['password'],'_token':token},form=True)
  assert status==200,(status,page)
  self.csrf=re.search(r'name="csrf-token" content="([^"]+)"',page).group(1)
  return self
anon=Client();check(anon.call('/api/bootstrap')[0]==401,'unauthenticated API denied')
c=Client().login(1);check(c.call('/api/bootstrap')[1]['business']['id']==1,'session authenticated in business 1')
check(c.call('/api/business',{'business_id':2},csrf=False)[0]==419,'CSRF mandatory')
tomorrow=datetime.date.today()+datetime.timedelta(days=2)
while tomorrow.isoweekday()==7:tomorrow+=datetime.timedelta(days=1)
day=tomorrow.isoformat()
status,msg=c.call('/api/agent',{'text':'Quiero reservar una cita'})
check(status==200 and msg['simulated'] and msg['provider_calls']==0,'deterministic chat without providers')
status,slots=c.call('/api/availability?'+urllib.parse.urlencode({'day':day,'professional_id':1,'service_id':1}))
check(status==200 and len(slots)>1,'HTTP availability')
payload={'professional_id':1,'service_id':1,'day':day,'start':slots[0]['start'],'client_name':'Cliente ficticio HTTP','business_id':2}
k=str(uuid.uuid4());status,result=c.call('/api/commands/book',payload,k)
check(status==200 and result['booking']['business_id']==1,'booking uses server session, ignores body tenant')
b=result['booking'];id=b['id']
check(c.call('/api/commands/book',payload,k)[1]['replayed'],'HTTP retry idempotent')
check(any(x['id']==id for x in c.call('/api/calendar?day='+day)[1]),'persisted calendar read')
other=Client().login(3)
check(not any(x['id']==id for x in other.call('/api/calendar?day='+day)[1]),'dental calendar cannot read tattoo booking')
check(other.call('/api/bookings/'+str(id)+'/events')[0]==404,'cross-business audit denied')
check(other.call('/api/commands/cancel',{'booking_id':id,'version':1},str(uuid.uuid4()))[0]==404,'cross-business mutation denied')
check(Client().login(2).call('/api/business',{'business_id':2})[0]==403,'unauthorized business switch denied')
check(Client().login(4).call('/api/commands/book',payload,str(uuid.uuid4()))[0]==403,'viewer HTTP write denied')
free=c.call('/api/availability?'+urllib.parse.urlencode({'day':day,'professional_id':1,'service_id':1}))[1]
status,result=c.call('/api/commands/reschedule',{'booking_id':id,'version':1,'day':day,'start':free[0]['start']},str(uuid.uuid4()))
check(status==200 and result['booking']['version']==2,'HTTP reschedule')
status,result=c.call('/api/commands/cancel',{'booking_id':id,'version':2},str(uuid.uuid4()))
check(status==200 and result['booking']['status']=='cancelled','HTTP cancellation')
check(len(c.call('/api/bookings/'+str(id)+'/events')[1])==3,'HTTP audit trail')
check(c.call('/api/business',{'business_id':2})[0]==200 and c.call('/api/bootstrap')[1]['business']['id']==2,'authorized switch isolates business 2')
for path in ['/calendar','/agent','/settings']:
 check(c.call(path)[0]==200,'render '+path)
print('RESULT',n,'HTTP assertions passed')
