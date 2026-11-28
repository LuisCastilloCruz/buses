import {Gateway} from './core.mjs';
import {appendFileSync} from 'node:fs';
const cfg=JSON.parse(process.env.AR_QA_CONFIG);
const gateway=new Gateway({...cfg,encryptionKey:Buffer.from(cfg.encryptionKey,'base64'),fetcher:async()=>{appendFileSync(cfg.calls,'provider-call\n');await new Promise(r=>setTimeout(r,200));return new Response('{"fixture":true}');}});
process.send({ready:true});
process.once('message',async message=>{if(message!=='start')return;try{await gateway.execute({tenants:[1]},cfg.request);process.send({result:'completed'});}catch(e){process.send({result:'controlled',error:e.message});}finally{gateway.close();process.disconnect();}});
