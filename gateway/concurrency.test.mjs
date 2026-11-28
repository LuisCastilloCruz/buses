import test from 'node:test';
import assert from 'node:assert/strict';
import {fork} from 'node:child_process';
import {mkdtemp,readFile,rm} from 'node:fs/promises';
import {tmpdir} from 'node:os';
import path from 'node:path';
import {randomBytes} from 'node:crypto';
import {Gateway,mac} from './core.mjs';
test('two processes cannot concurrently retry the same rejected provider request',async()=>{
 const folder=await mkdtemp(path.join(tmpdir(),'ar-race-'));const key=randomBytes(32);const request={tenant:1,channel:10,operation:'whatsapp',id:'same-retry-12345',payload:{messaging_product:'whatsapp',to:'51999999999',type:'text',text:{body:'FICTITIOUS'}}};
 const cfg={database:path.join(folder,'gateway.sqlite'),calls:path.join(folder,'calls.txt'),keys:{worker:{secret:'fake-internal-secret-at-least-32-characters',tenants:[1]}},channels:{10:{tenant:1,sandboxAuthorized:true,monthlyCapUnits:10000,maxRequestUnits:100,version:'v99.0',phone:'12345678',recipients:['51999999999'],templates:[]}},live:true,request};
 const gateway=new Gateway({...cfg,encryptionKey:key});gateway.db.prepare('INSERT INTO requests(scope,id,hash,state,response,attempts) VALUES(?,?,?,?,NULL,1)').run('1:10:whatsapp',request.id,mac(key,JSON.stringify(request.payload)),'retryable');
 const children=[];try{
  const ready=[];const results=[];
  for(let i=0;i<2;i++){
   const child=fork(new URL('./concurrency-worker.mjs',import.meta.url),[],{windowsHide:true,stdio:['ignore','ignore','pipe','ipc'],env:{...process.env,AR_QA_CONFIG:JSON.stringify({...cfg,encryptionKey:key.toString('base64')})}});children.push(child);
   ready.push(new Promise((resolve,reject)=>{child.on('message',m=>{if(m.ready)resolve();});child.once('error',reject);}));
   results.push(new Promise((resolve,reject)=>{child.on('message',m=>{if(m.result)resolve(m);});child.once('error',reject);}));
  }
  await Promise.all(ready);gateway.db.exec('BEGIN IMMEDIATE');for(const child of children)child.send('start');await new Promise(r=>setTimeout(r,300));gateway.db.exec('COMMIT');
  await Promise.all(results);await Promise.all(children.map(child=>new Promise(resolve=>child.exitCode!==null?resolve():child.once('exit',resolve))));
  const calls=(await readFile(cfg.calls,'utf8')).trim().split('\n');assert.equal(calls.length,1,'a rejected request must be claimed atomically before retry');
 }finally{gateway.close();for(const child of children)if(child.exitCode===null)child.kill();assert.ok(folder.startsWith(path.resolve(tmpdir())+path.sep+'ar-race-'));await rm(folder,{recursive:true,force:true});}
});
