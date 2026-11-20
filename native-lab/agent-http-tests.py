import json,uuid,importlib
m=importlib.import_module('http-smoke');accounts=json.loads((m.root/'.native-runtime/accounts.json').read_text());n=0
def check(ok,label):
 global n
 if not ok:raise AssertionError(label)
 n+=1;print('PASS',n,label,flush=True)
d=m.Client(accounts['dental']);c=m.Client(accounts['comercio'])
for path in ['/aireception/inbox','/aireception/channels','/aireception/agent-settings','/aireception/overview','/aireception/conversations']:
 code,body=d.request(path);check(code==200 and 'sidebar-left' in body,'native page '+path)
 check(c.request(path)[0]==403,'unlicensed route rejected '+path)
cat=json.loads(d.request('/aireception/api/bootstrap')[1]);person=cat['persons'][0]['id'];key='http-agent-'+str(uuid.uuid4());payload={'person_id':person,'text':'Quiero una cita'}
code,body=d.request('/aireception/api/agent',payload,key=key);check(code==200,'agent authenticated HTTP');result=json.loads(body)
check('Simulacion local:' in result['reply'],'simulation explicitly labeled')
code,body=d.request('/aireception/api/agent',payload,key=key);check(code==200 and json.loads(body)==result,'HTTP retry returns same conversation result')
code,body=d.request('/aireception/api/agent',dict(payload,text='otro'),key=key);check(code==409,'HTTP key reuse mismatch denied')
code,body=d.request('/aireception/api/agent',dict(payload,text='Quiero hablar con una persona',conversation_id=result['conversation_id']),key='human-'+str(uuid.uuid4()));check(code==200 and json.loads(body)['handoff'],'HTTP human handoff')
code,body=d.request('/aireception/api/agent',dict(payload,conversation_id=result['conversation_id']),key='pause-'+str(uuid.uuid4()));check(code==200 and json.loads(body)['handoff'],'HTTP pause respected')
check(c.request('/aireception/api/agent',payload,key='deny-'+str(uuid.uuid4()))[0]==403,'unlicensed business cannot consume agent')
print('RESULT',n,'agent HTTP checks; no external providers')
