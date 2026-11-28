// Explicit foreground worker. Never installed as a Windows service automatically.
import fs from 'node:fs';import path from 'node:path';import os from 'node:os';import {Gateway} from './core.mjs';import {OllamaProvider} from './ollama.mjs';import {PrivateGatewayClient,OutboundInferenceWorker} from './private-worker.mjs';
const file=process.env.AR_PRIVATE_WORKER_CONFIG;if(!file||!path.isAbsolute(file))throw Error('Private configuration path required');const cfg=JSON.parse(fs.readFileSync(file,'utf8'));
if(cfg.purpose!=='multifactura-v7-private-worker'||cfg.authorized!==true||!path.isAbsolute(cfg.database)||!Number.isInteger(cfg.tenant)||cfg.tenant<1||!Number.isInteger(cfg.channel)||cfg.channel<1)throw Error('Invalid private worker configuration');
const vault=new Gateway({database:cfg.database,keys:{},channels:{},encryptionKey:Buffer.from(cfg.encryptionKey,'base64')}),remote=new PrivateGatewayClient(cfg),provider=new OllamaProvider({model:cfg.model,context:2048}),worker=new OutboundInferenceWorker({vault,remote,provider});
try{if(os.freemem()<3.25*1024**3)throw Error('Insufficient inference headroom');const state=await worker.once();console.log(JSON.stringify({state}));}finally{vault.close();}
// Invoke again through an explicitly configured scheduler. Each invocation resumes saved results first.
