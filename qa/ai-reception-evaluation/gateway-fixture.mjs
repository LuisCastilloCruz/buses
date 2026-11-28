// Dedicated transport fixture. Never used by production gateway startup.
import fs from 'node:fs';import {Gateway} from '../../gateway/core.mjs';import {createGatewayServer} from '../../gateway/server.mjs';
const config=JSON.parse(fs.readFileSync(process.env.AR_GATEWAY_CONFIG,'utf8'));if(config.purpose!=='v7-loopback-transport-fixture'||config.port!==8897||config.live!==false)throw Error('Fixture identity');
const gateway=new Gateway({...config,localResourceProbe:async()=>{},encryptionKey:Buffer.from(config.encryptionKey,'base64'),fetcher:async url=>{if(url!=='http://127.0.0.1:11434/api/chat')throw Error('No external calls');return new Response(JSON.stringify({done:true,done_reason:'stop',message:{content:JSON.stringify({name:'list_services',arguments:{}}),thinking:'not forwarded'},prompt_eval_count:200,eval_count:20}));}});
createGatewayServer(gateway,config).listen(config.port,'127.0.0.1');
