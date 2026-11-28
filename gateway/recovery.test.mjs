import test from 'node:test';
import assert from 'node:assert/strict';
import {spawn} from 'node:child_process';
import {mkdtemp,readFile,rm} from 'node:fs/promises';
import {tmpdir} from 'node:os';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import {randomBytes} from 'node:crypto';
import {Gateway} from './core.mjs';

test('real worker death after durable claim never repeats provider operation or budget charge',async()=>{
 const folder=await mkdtemp(path.join(tmpdir(),'ar-recovery-'));let g;
 const key=randomBytes(32);
 const cfg={database:path.join(folder,'queue.sqlite'),calls:path.join(folder,'calls.txt'),keys:{},channels:{10:{tenant:1,sandboxAuthorized:true,monthlyCapUnits:1000,maxRequestUnits:100,models:['fixture'],openaiKey:'fake'}},encryptionKey:key.toString('base64'),live:true,request:{tenant:1,channel:10,operation:'responses',id:'crash-request-123',payload:{model:'fixture',store:false,input:[{role:'user',content:'FICTITIOUS'}],max_output_tokens:128,tools:[]}}};
 try{
  const child=spawn(process.execPath,[fileURLToPath(new URL('./crash-worker.mjs',import.meta.url))],{env:{...process.env,AR_QA_CONFIG:JSON.stringify(cfg)},windowsHide:true,stdio:'ignore'});
  const code=await new Promise((resolve,reject)=>{child.once('error',reject);child.once('exit',resolve);});assert.equal(code,17);
  let calls=0;g=new Gateway({...cfg,encryptionKey:key,fetcher:async()=>{calls++;throw Error('Must not retry');}});
  assert.equal(g.db.prepare('SELECT state FROM requests').get().state,'pending');
  await assert.rejects(g.execute({tenants:[1]},cfg.request),/Reconciliation/);
  assert.equal(calls,0);assert.equal((await readFile(cfg.calls,'utf8')).trim(),'provider-attempt');
  assert.equal(g.db.prepare('SELECT used FROM caps').get().used,100);
 }finally{g?.close();assert.ok(path.resolve(folder).startsWith(path.resolve(tmpdir())+path.sep+'ar-recovery-'));await rm(folder,{recursive:true,force:true});}
});
