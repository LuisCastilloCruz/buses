import importlib,json
m=importlib.import_module('http-smoke');accounts=json.loads((m.root/'.native-runtime/accounts.json').read_text());a=m.Client(accounts['system']);d=m.Client(accounts['dental']);c=m.Client(accounts['comercio']);n=0
def check(x,label):
 global n
 assert x,label
 n+=1;print('PASS',n,label,flush=True)
check(a.request('/aireception-management')[0]==200,'native superadmin plan screen')
plan={'plan_id':1,'allow_reception':True,'allow_dental':True,'addon_price':0,'monthly_units':100000,'professional_limit':1000,'message_limit':1000}
try:
 plan['allow_reception']=False
 code,body=a.request('/aireception-management/plan',plan);check(code==200 and json.loads(body)['success'],'superadmin restricts existing plan')
 check(d.request('/aireception/api/bootstrap')[0]==403,'plan denial enforced in tenant API')
 check(d.request('/odontologia')[0]==200,'independent dental plan capability retained')
 plan['allow_reception']=True;check(a.request('/aireception-management/plan',plan)[0]==200,'plan restored')
 check(c.request('/aireception/api/bootstrap')[0]==403,'plan does not grant unselected module')
 code,body=a.request('/aireception-management/subscription',{'client_id':2,'status':'suspended','expires_at':None});check(code==200,'subscription suspended')
 check(d.request('/aireception/api/bootstrap')[0]==403 and d.request('/odontologia')[0]==403,'suspension enforced for new modules')
 check(d.request('/documents')[0]==200,'original invoicing unaffected by add-on suspension')
 check(d.request('/aireception-management/plan',plan)[0] in [401,403],'tenant cannot change commercial policy')
finally:
 plan['allow_reception']=True;assert a.request('/aireception-management/plan',plan)[0]==200
 assert a.request('/aireception-management/subscription',{'client_id':2,'status':'active','expires_at':None})[0]==200
check(d.request('/aireception/api/bootstrap')[0]==200,'reactivation restores authorized access')
print('RESULT',n,'plan/subscription HTTP assertions')
