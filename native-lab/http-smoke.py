import json,re,socket,urllib.request,urllib.parse,urllib.error,http.cookiejar,os
from pathlib import Path
root=Path(__file__).resolve().parents[1]
original=socket.getaddrinfo
def local_dns(host,*a,**k):
 if host=='localhost' or host.endswith('.localhost'):host='127.0.0.1'
 return original(host,*a,**k)
socket.getaddrinfo=local_dns
class Client:
 def __init__(self,account):
  self.base=account['url']
  port=os.environ.get('MULTIFACTURA_TEST_PORT')
  if port:
   if port not in ('8878','8888'):raise ValueError('Only V6 loopback test ports allowed')
   u=urllib.parse.urlsplit(self.base)
   if u.scheme!='http' or not (u.hostname=='localhost' or u.hostname.endswith('.localhost')):raise ValueError('Only local fixture hosts allowed')
   self.base=urllib.parse.urlunsplit((u.scheme,u.hostname+':'+port,u.path,u.query,u.fragment))
  self.jar=http.cookiejar.CookieJar();self.opener=urllib.request.build_opener(urllib.request.ProxyHandler({}),urllib.request.HTTPCookieProcessor(self.jar));self.csrf=''
  code,body=self.request('/login');assert code==200,(self.base,code)
  token=re.search(r'name="_token" value="([^"]+)"',body).group(1)
  code,body=self.request('/login',{'_token':token,'email':account['email'],'password':account['password']},form=True)
  if code!=200:raise RuntimeError('Login '+self.base+' '+str(code)+' '+body.splitlines()[3][:180])
  m=re.search(r'name="csrf-token" content="([^"]+)"',body);self.csrf=m.group(1) if m else ''
 def request(self,path,data=None,form=False,key=None):
  headers={'Accept':'text/html' if data is None or form else 'application/json','X-CSRF-TOKEN':self.csrf}
  if key:headers['Idempotency-Key']=key
  payload=None
  if data is not None:
   payload=(urllib.parse.urlencode(data) if form else json.dumps(data)).encode();headers['Content-Type']='application/x-www-form-urlencoded' if form else 'application/json'
  try:
   r=self.opener.open(urllib.request.Request(self.base+path,data=payload,headers=headers),timeout=900);return r.status,r.read().decode()
  except urllib.error.HTTPError as e:
   body=e.read().decode();(root/'.native-runtime/last-http-error.html').write_text(body,encoding='utf-8');return e.code,body
if __name__=='__main__':
 accounts=json.loads((root/'.native-runtime/accounts.json').read_text())
 for name,account in accounts.items():
  try:
   c=Client(account)
   paths=['/clients'] if name=='system' else ['/dashboard','/documents','/pos','/persons/customers','/aireception/calendar','/odontologia']
   if name=='tattoo':paths+=['/tattoo-dashboard','/tattoo-appointments','/tattoo-inbox']
   for path in paths:
    code,body=c.request(path);print(name,path,code,body.splitlines()[3][:200] if code==500 else '')
  except Exception as e:print(type(e).__name__,str(e)[:250])
