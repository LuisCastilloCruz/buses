import json,copy,uuid,datetime,importlib
m=importlib.import_module('http-smoke');accounts=json.loads((m.root/'.native-runtime/accounts.json').read_text());n=0
def check(ok,label):
 global n
 if not ok:raise AssertionError(label)
 n+=1;print('PASS',n,label,flush=True)
def get(c,path):
 code,body=c.request(path);check(code==200,path+' HTTP 200');return json.loads(body)
a=m.Client(accounts['system']);t=m.Client(accounts['tattoo']);d=m.Client(accounts['dental']);c=m.Client(accounts['comercio'])
tables=get(a,'/clients/tables');apps={x['description']:x for x in tables['apps']};check('AiReception' in apps and 'Odontologia' in apps and 'Tattoo' in apps,'real system catalogs retain Tattoo and add independent modules')
original=get(a,'/clients/record/2')['data'];payload=copy.deepcopy(original);payload.update(socket=False,temp_path=None,production=False,port=None)
ai=apps['AiReception']['id'];dental=apps['Odontologia']['id']
try:
 payload['apps']=[x for x in original['apps'] if x!=ai]
 code,body=a.request('/clients/update',payload);check(code==200 and json.loads(body).get('success') is True,'superadmin actual update disables AiReception')
 check(d.request('/aireception/api/bootstrap')[0]==403,'disabled tenant blocked despite prior authenticated session')
 check(d.request('/odontologia')[0]==200,'independent Odontologia remains enabled')
 check(d.request('/documents')[0]==200,'traditional documents retained after module change')
 payload['apps']=original['apps'];code,body=a.request('/clients/update',payload);check(code==200 and json.loads(body).get('success') is True,'superadmin actual update enables AiReception')
 check(d.request('/aireception/api/bootstrap')[0]==200,'enabled tenant regains access')
finally:
 payload['apps']=original['apps'];code,body=a.request('/clients/update',payload)
 if code!=200 or not json.loads(body).get('success'):raise RuntimeError('Fixture activation restoration failed')
check(c.request('/aireception/api/bootstrap')[0]==403 and c.request('/odontologia')[0]==403,'traditional company denied new modules')
for cli,path in [(t,'/odontologia'),(d,'/tattoo-appointments'),(c,'/tattoo-appointments')]:check(cli.request(path)[0]==403,'unauthorized business rejected '+path)
# Copy an authenticated tenant cookie deliberately to another host to test server-side binding.
for cookie in t.jar:
 forged=copy.copy(cookie);forged.domain='dental.localhost';d.jar.set_cookie(forged)
check(d.request('/aireception/api/bootstrap')[0]==403,'copied tenant session cannot authenticate another business')
d=m.Client(accounts['dental'])
tcat=get(t,'/aireception/api/bootstrap');dcat=get(d,'/aireception/api/bootstrap');check(tcat['business']['id']!=dcat['business']['id'],'Hyn identifies separate businesses')
check(all('Tatuador' in x['name'] for x in tcat['professionals']),'tattoo catalog excludes dental professionals')
day=(datetime.date.today()+datetime.timedelta(days=20))
while day.weekday()==6:day+=datetime.timedelta(days=1)
params={'professional_id':dcat['professionals'][0]['id'],'service_id':dcat['services'][0]['id'],'day':day.isoformat()}
slots=get(d,'/aireception/api/availability?'+m.urllib.parse.urlencode(params));check(bool(slots),'HTTP availability')
command={**params,'start':slots[0]['start'],'person_id':dcat['persons'][-1]['id'],'status':'confirmed'};key='http-'+str(uuid.uuid4());code,body=d.request('/aireception/api/commands/book',command,key=key);check(code==200,'HTTP persistent booking');booking=json.loads(body)['booking']
code,body=d.request('/aireception/api/commands/book',command,key=key);check(code==200 and json.loads(body)['replayed'],'HTTP idempotent retry')
rows=get(d,'/odontologia/api/calendar?day='+day.isoformat());check(any(x['id']==booking['id'] for x in rows),'Odontologia and AiReception read same canonical booking')
rows=get(t,'/aireception/api/calendar?day='+day.isoformat());check(not any(x['client_name']==booking['client_name'] for x in rows),'other tenant cannot read dental booking')
code,body=d.request('/aireception/api/commands/cancel',{'booking_id':booking['id'],'version':booking['version']},key='http-'+str(uuid.uuid4()));check(code==200 and json.loads(body)['booking']['status']=='cancelled','authorized HTTP cancellation')
for cli in [t,d,c]:
 for path in ['/dashboard','/documents','/pos','/persons/customers']:
  check(cli.request(path)[0]==200,'original '+cli.base.split('//')[1]+' '+path)
check(t.request('/tattoo-inbox')[0]==200,'TattooInbox existing interface retained')
check(d.request('/tattoo-inbox')[0]==403,'unlicensed business cannot open original Inbox')
check(t.request('/api/tattoo-inbox/meta/webhook',{'object':'whatsapp_business_account'})[0]==403,'unsigned legacy webhook fails closed in native lab')
print('RESULT',n,'native HTTP assertions')
