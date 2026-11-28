import importlib,json,uuid
m=importlib.import_module('http-smoke');a=json.loads((m.root/'.native-runtime/accounts.json').read_text());d=m.Client(a['dental']);t=m.Client(a['tattoo']);c=m.Client(a['comercio']);n=0

def check(ok,label):
 global n
 if not ok:raise AssertionError(label)
 n+=1;print('PASS',n,label,flush=True)

code,body=d.request('/aireception/agent-settings');check(code==200 and 'profile[business_name]' in body and 'value="local"' in body,'native receptionist configuration')
check(c.request('/aireception/knowledge')[0]==403,'unlicensed knowledge route denied')
check(d.request('/aireception/knowledge')[0]==200,'authorized native knowledge page')
payload={'source':'v7-public-parking','title':'Estacionamiento ficticio V7','body':'Estacionamiento gratuito en el patio de la sede ficticia.','classification':'administrative','approved':True}
check(d.request('/aireception/knowledge',payload)[0]==200,'approved administrative source persisted')
code,body=d.request('/aireception/knowledge');check('Estacionamiento ficticio V7' in body,'source visible after reload')
check(t.request('/aireception/knowledge/remove',{'source':payload['source']})[0]==404,'another tenant cannot delete dental source')
denied_code,denied_body=c.request('/aireception/knowledge',payload);check(denied_code==403,'unlicensed source write denied');check(not any(k in json.loads(denied_body) for k in ['file','line','trace']),'permission error does not disclose server paths')
check(d.request('/aireception/knowledge',dict(payload,approved=False))[0]==422,'unapproved document rejected')
check(d.request('/aireception/knowledge',dict(payload,classification='clinical'))[0]==422,'clinical knowledge rejected')
check(d.request('/aireception/knowledge',dict(payload,body='Contraseña: prueba ficticia no indexable'))[0]==422,'secret-shaped content rejected')
settings={'enabled':False,'mode':'simulated','model':'fixture-model','max_output_tokens':512,'profile':{'business_name':'Clinica ficticia V7','receptionist_name':'Lucia','activity':'Atencion administrativa dental','locale':'es-PE','tone':'cordial','greeting':'Hola, soy Lucia. ¿En que puedo ayudarte?'}}
check(d.request('/aireception/agent-settings',settings)[0]==200,'personality saved through native authenticated route')
check('Clinica ficticia V7' in d.request('/aireception/agent-settings')[1],'personality survives reload')
check('Clinica ficticia V7' not in t.request('/aireception/agent-settings')[1],'personality isolated from tattoo')
check(d.request('/aireception/agent-settings',dict(settings,profile={'api_key':'not-a-credential'}))[0]==422,'profile rejects credential injection')
check(c.request('/aireception/agent-settings',settings)[0]==403,'unlicensed profile update denied')
print('RESULT',n,'V7 HTTP checks; no model or external provider called')
