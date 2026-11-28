import test from 'node:test';
import assert from 'node:assert/strict';
import {mkdtemp,rm} from 'node:fs/promises';
import {tmpdir} from 'node:os';
import path from 'node:path';
import {randomBytes} from 'node:crypto';
import {Gateway,mac} from './core.mjs';

test('bounded 650 synthetic ingress attempts survive reopen, dedup and expired worker leases',async()=>{
 const folder=await mkdtemp(path.join(tmpdir(),'ar-stress-'));
 const cfg={database:path.join(folder,'queue.sqlite'),keys:{},channels:{10:{tenant:1,receiver:'aireception',appSecret:'fixture-only',waba:'12345',phone:'12345678'},20:{tenant:2,receiver:'aireception',appSecret:'other-fixture',waba:'54321',phone:'87654321'}},encryptionKey:randomBytes(32),live:false};
 let g=new Gateway(cfg);let accepted=0,rejected=0;const latencies=[];const started=performance.now();
 try{
  for(let i=0;i<250;i++){
   const body=JSON.stringify({object:'whatsapp_business_account',entry:[{id:'12345',changes:[{field:'messages',value:{metadata:{phone_number_id:'12345678'},messages:[{id:'wamid.synthetic.'+i,text:{body:'FICTITIOUS '+i}}]}}]}]});
   for(let repeat=0;repeat<2;repeat++){const t=performance.now();g.ingest(10,body,'sha256='+mac('fixture-only',body));latencies.push(performance.now()-t);accepted++;}
   if(i<100){assert.throws(()=>g.ingest(10,body,'sha256=invalid'));rejected++;}
   if(i<50){assert.throws(()=>g.ingest(20,body,'sha256='+mac('fixture-only',body)));rejected++;}
   if(i%25===0)await new Promise(r=>setTimeout(r,10));
  }
  assert.equal(g.db.prepare('SELECT count(*) n FROM ingress').get().n,250);
  const first=g.pull(10,'worker-before-crash');
  assert.ok(first.event);
  g.close();g=new Gateway(cfg);
  assert.equal(g.pull(20,'other-tenant').event,null);
  const second=g.pull(10,'worker-after-reopen');assert.notEqual(second.event.id,first.event.id);
  g.db.prepare('UPDATE ingress SET lease=0 WHERE id=?').run(first.event.id);
  const recovered=g.pull(10,'replacement-worker');assert.equal(recovered.event.id,first.event.id);
  await assert.rejects(g.execute({tenants:[2]},{tenant:1,channel:10,operation:'ingress_ack',id:'forbidden-ack',payload:{id:first.event.id,owner:recovered.event.owner}}));
  await assert.rejects(g.execute({tenants:[1]},{tenant:1,channel:10,operation:'ingress_ack',id:'stale-ack-123',payload:{id:first.event.id,owner:first.event.owner}}));
  await g.execute({tenants:[1]},{tenant:1,channel:10,operation:'ingress_ack',id:'valid-ack-123',payload:{id:first.event.id,owner:recovered.event.owner}});
  assert.equal(g.db.prepare("SELECT count(*) n FROM ingress WHERE state='done'").get().n,1);
  latencies.sort((a,b)=>a-b);
  console.log(JSON.stringify({attempts:accepted+rejected,accepted,rejected,persisted_unique:250,elapsed_ms:performance.now()-started,p50_ms:latencies[Math.ceil(latencies.length*.5)-1],p95_ms:latencies[Math.ceil(latencies.length*.95)-1],p99_ms:latencies[Math.ceil(latencies.length*.99)-1],external_calls:0}));
 }finally{g.close();assert.ok(path.resolve(folder).startsWith(path.resolve(tmpdir())+path.sep+'ar-stress-'));await rm(folder,{recursive:true,force:true});}
});
