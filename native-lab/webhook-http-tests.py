import json,hashlib,hmac,urllib.request,urllib.error,uuid
from pathlib import Path
root=Path(__file__).resolve().parents[1];c=json.loads((root/'.native-runtime/webhook-fixture.json').read_text());n=0
opener=urllib.request.build_opener(urllib.request.ProxyHandler({}))
def check(x,label):
 global n
 assert x,label
 n+=1;print('PASS',n,label,flush=True)
def post(payload,valid=True,channel=None):
 raw=json.dumps(payload,separators=(',',':')).encode();signature='sha256='+hmac.new(c['app_secret'].encode(),raw,hashlib.sha256).hexdigest()
 req=urllib.request.Request('http://127.0.0.1:8882/api/aireception/meta/'+str(channel or c['id']),data=raw,headers={'Content-Type':'application/json','X-Hub-Signature-256':signature if valid else 'sha256=invalid'})
 try:
  r=opener.open(req,timeout=60);return r.status,json.loads(r.read())
 except urllib.error.HTTPError as e:return e.code,None
payload={'object':'whatsapp_business_account','entry':[{'id':c['waba_id'],'changes':[{'field':'messages','value':{'metadata':{'phone_number_id':c['phone_id']},'messages':[{'id':'wamid.fake.'+uuid.uuid4().hex+'=','from_user_id':'US.fictitious-'+uuid.uuid4().hex,'timestamp':'1795200000','type':'text','text':{'body':'Solicitud ficticia de cita'}}]}}]}]}
code,body=post(payload);check(code==200 and body['accepted']==1,'signed webhook queued')
code,body=post(payload);check(code==200 and body['accepted']==0,'message ID deduplicated before dispatch')
invalid=post(payload,False)[0];print('Invalid signature HTTP',invalid,flush=True);check(invalid==403,'invalid HMAC rejected')
check(post(payload,channel=999999)[0]==403,'unknown registered channel rejected')
payload['entry'][0]['changes'][0]['value']['metadata']['phone_number_id']='99000000099';check(post(payload)[0]==403,'signed wrong business channel rejected')
payload['entry'][0]['changes'][0]['value']['metadata']['phone_number_id']=c['phone_id'];payload['entry'][0]['id']='99000000099';check(post(payload)[0]==403,'signed wrong WABA rejected')
payload['entry'][0]['id']=c['waba_id']
value=payload['entry'][0]['changes'][0]['value'];value.pop('messages');mid='wamid.receipt.'+uuid.uuid4().hex+'='
value['statuses']=[{'id':mid,'status':'read','timestamp':'1795200100','recipient_id':'fictional-not-routing'}]
code,body=post(payload);check(code==200 and body['receipts']==1,'signed delivery receipt audited')
code,body=post(payload);check(code==200 and body['receipts']==0,'duplicate delivery receipt ignored')
value['statuses'][0].update(status='sent',timestamp='1795200000');code,body=post(payload);check(code==200 and body['receipts']==1,'out of order delivery receipt retained as audit')
value['statuses'][0]['status']='unrecognized';code,body=post(payload);check(code==200 and body['receipts']==0,'unsupported delivery state ignored')
print('RESULT',n,'webhook HTTP assertions; synthetic signed events only')
