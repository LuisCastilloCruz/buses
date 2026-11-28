import test from 'node:test';
import assert from 'node:assert/strict';
import {randomBytes} from 'node:crypto';
import {Gateway} from './core.mjs';

for(const scenario of [400,401,403,429,500,502,503,'timeout','malformed']){
 test('provider '+scenario+' preserves uncertain state and prevents duplicate paid retry',async()=>{
  let calls=0;
  const g=new Gateway({database:':memory:',keys:{},encryptionKey:randomBytes(32),live:true,channels:{10:{tenant:1,sandboxAuthorized:true,monthlyCapUnits:1000,maxRequestUnits:100,models:['fixture'],openaiKey:'fake'}},fetcher:async()=>{
   calls++;
   if(scenario==='timeout')throw Error('private simulated provider timeout');
   return new Response(scenario==='malformed'?'not JSON':'{"error":"fixture"}',{status:typeof scenario==='number'?scenario:200});
  }});
  const command={tenant:1,channel:10,operation:'responses',id:'failure-fixture-123',payload:{model:'fixture',store:false,input:[{role:'user',content:'FICTITIOUS'}],max_output_tokens:128,tools:[]}};
  try{
   await assert.rejects(g.execute({tenants:[1]},command),/reconcile/);
   assert.equal(g.db.prepare('SELECT state FROM requests').get().state,'unknown');
   await assert.rejects(g.execute({tenants:[1]},command),/Reconciliation/);
   assert.equal(calls,1);assert.equal(g.db.prepare('SELECT used FROM caps').get().used,100);
  }finally{g.close();}
 });
}
