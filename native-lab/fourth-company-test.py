import importlib,json,secrets,copy
m=importlib.import_module('http-smoke')
accounts=json.loads((m.root/'.native-runtime/accounts.json').read_text())
if 'cuarta' in accounts:
 c=m.Client(accounts['cuarta'])
 for path in ['/dashboard','/documents','/pos','/odontologia','/aireception/calendar']:
  assert c.request(path)[0]==200,path
 print('PASS existing fourth company retained and verified; no creation repeated',flush=True)
 raise SystemExit(0)
a=m.Client(accounts['system'])
code,body=a.request('/clients/record/2');assert code==200
payload=copy.deepcopy(json.loads(body)['data'])
password=secrets.token_hex(16)
payload.update(id=None,subdomain='cuarta',number='20000000044',name='Cuarta clinica FICTICIA V4',email='cuarta@example.test',password=password,type='admin',soap_type_id='01',soap_send_id='01',socket=False,temp_path=None,production=False,port=None,locked_emission=True,config_system_env=False)
(m.root/'.native-runtime/fourth-account.json').write_text(json.dumps({'url':'http://cuarta.localhost:8879','email':payload['email'],'password':password}))
code,body=a.request('/clients',payload)
(m.root/'.native-runtime/fourth-create-result.txt').write_text(body,encoding='utf-8')
assert code==200 and json.loads(body).get('success') is True,'Creation failed; inspect private result, do not retry blindly'
print('PASS fourth company created through original superadmin HTTP',flush=True)
account=json.loads((m.root/'.native-runtime/fourth-account.json').read_text());c=m.Client(account)
for path in ['/dashboard','/documents','/pos','/odontologia','/aireception/calendar']:
 assert c.request(path)[0]==200,path
 print('PASS fourth company '+path,flush=True)
accounts['cuarta']=account;(m.root/'.native-runtime/accounts.json').write_text(json.dumps(accounts))
print('RESULT 6 fourth-company HTTP checks')
