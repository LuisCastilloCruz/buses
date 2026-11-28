import {Gateway} from './core.mjs';
import {appendFileSync} from 'node:fs';
const cfg=JSON.parse(process.env.AR_QA_CONFIG);
const g=new Gateway({...cfg,encryptionKey:Buffer.from(cfg.encryptionKey,'base64'),fetcher:async()=>{
 appendFileSync(cfg.calls,'provider-attempt\n');
 process.exit(17); // Simulate process death after durable pending state, before provider confirmation.
}});
await g.execute({tenants:[1]},cfg.request);
process.exit(2);
