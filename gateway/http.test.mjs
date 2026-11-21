import test from 'node:test';import assert from 'node:assert/strict';import {mkdtemp,writeFile,rm} from 'node:fs/promises';import {tmpdir} from 'node:os';import path from 'node:path';import {spawn} from 'node:child_process';import {randomBytes} from 'node:crypto';import {mac} from './core.mjs';
test('real loopback HTTP gateway validates webhook and authenticates leased delivery',async()=>{
 const dir=await mkdtemp(path.join(tmpdir(),'ar-gateway-test-')),port=18890,secret=randomBytes(32).toString('hex'),appSecret=randomBytes(32).toString('hex');
 const config={database:path.join(dir,'queue.sqlite'),keys:{worker:{secret,tenants:[1]}},channels:{10:{tenant:1,receiver:'aireception',appSecret,verifyToken:'fixture-verification-token',waba:'12345',phone:'12345678'}},encryptionKey:randomBytes(32).toString('base64'),live:false,port};const file=path.join(dir,'config.json');await writeFile(file,JSON.stringify(config),{mode:0o600});
 const child=spawn(process.execPath,[path.resolve('gateway/server.mjs')],{env:{...process.env,AR_GATEWAY_CONFIG:file},windowsHide:true,stdio:'pipe'});let errors='';child.stderr.on('data',b=>errors+=b);const base=`http://127.0.0.1:${port}`;
 try{
  let ready=false;for(let i=0;i<60;i++){if(child.exitCode!==null)throw Error(errors);try{const r=await fetch(base+'/');if(r.status===409){ready=true;break;}}catch{}await new Promise(r=>setTimeout(r,50));}assert.ok(ready,'gateway started');
  const challenge=await fetch(base+'/webhooks/meta/10?hub.mode=subscribe&hub.verify_token=fixture-verification-token&hub.challenge=12345');assert.equal(await challenge.text(),'12345');
  const body=JSON.stringify({object:'whatsapp_business_account',entry:[{id:'12345',changes:[{field:'messages',value:{metadata:{phone_number_id:'12345678'},messages:[]}}]}]});
  assert.equal((await fetch(base+'/webhooks/meta/10',{method:'POST',headers:{'Content-Type':'application/json','X-Hub-Signature-256':'invalid'},body})).status,409);
  assert.equal((await fetch(base+'/webhooks/meta/10',{method:'POST',headers:{'Content-Type':'application/json','X-Hub-Signature-256':'sha256='+mac(appSecret,body)},body})).status,200);
  const request=JSON.stringify({tenant:1,channel:10,operation:'ingress_pull',id:'http-lease-1234',payload:{}}),stamp=String(Math.floor(Date.now()/1000)),nonce=randomBytes(16).toString('hex');
  const headers={'Content-Type':'application/json','X-AR-Client':'worker','X-AR-Time':stamp,'X-AR-Nonce':nonce,'X-AR-Signature':mac(secret,`worker\n${stamp}\n${nonce}\n${request}`)};
  const response=await fetch(base+'/internal/v1/execute',{method:'POST',headers,body:request}),text=await response.text();assert.equal(response.status,200);assert.equal(response.headers.get('x-ar-response-signature'),mac(secret,nonce+'\n'+text));assert.equal(JSON.parse(text).event.body,body);
  assert.equal((await fetch(base+'/internal/v1/execute',{method:'POST',headers,body:request})).status,409);
 }finally{child.kill();await new Promise(r=>child.once('exit',r));await rm(dir,{recursive:true,force:true});}
});