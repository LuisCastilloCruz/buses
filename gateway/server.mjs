import http from 'node:http';
import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import {Gateway,mac} from './core.mjs';
// Configuration is an ACL-protected file outside the repository. No public bind option.
export function createGatewayServer(gateway,config){
const server=http.createServer(async(req,res)=>{
 let principal=null;res.setHeader('Cache-Control','no-store');res.setHeader('Content-Type','application/json');
 try{
  const parsed=new URL(req.url,'http://127.0.0.1');const webhook=/^\/webhooks\/meta\/(\d+)$/.exec(parsed.pathname);
  if(webhook&&req.method==='GET'){const cfg=config.channels[webhook[1]],challenge=parsed.searchParams.get('hub.challenge');if(!cfg||cfg.receiver!=='aireception'||!cfg.verifyToken||parsed.searchParams.get('hub.mode')!=='subscribe'||parsed.searchParams.get('hub.verify_token')!==cfg.verifyToken||!/^\d{1,128}$/.test(challenge))throw Error('Verification rejected');res.setHeader('Content-Type','text/plain');res.end(challenge);return;}
  if(req.method!=='POST'||(!webhook&&req.url!=='/internal/v1/execute')||req.headers['content-type']!=='application/json')throw Error('Request rejected');
  let size=0,parts=[];for await(const chunk of req){size+=chunk.length;if(size>65536)throw Error('Request too large');parts.push(chunk);}
  const body=Buffer.concat(parts).toString('utf8');if(webhook){res.end(JSON.stringify(gateway.ingest(webhook[1],body,req.headers['x-hub-signature-256'])));return;}principal=gateway.authenticate(req.headers,body);
  const result=await gateway.execute(principal,JSON.parse(body));const encoded=JSON.stringify(result);res.setHeader('X-AR-Response-Signature',mac(principal.secret,req.headers['x-ar-nonce']+'\n'+encoded));res.end(encoded);
 }catch(e){res.statusCode=e.retryAfter?429:409;const encoded=JSON.stringify(e.retryAfter?{error:'retryable_provider_rejection',retry_after:e.retryAfter}:{error:'request_rejected_or_reconciliation_required'});if(principal)res.setHeader('X-AR-Response-Signature',mac(principal.secret,req.headers['x-ar-nonce']+'\n'+encoded));res.end(encoded);}
});
server.requestTimeout=30000;server.headersTimeout=5000;server.maxHeadersCount=20;server.maxConnections=32;
return server;
}
if(process.argv[1]&&path.resolve(process.argv[1])===fileURLToPath(import.meta.url)){const config=JSON.parse(fs.readFileSync(process.env.AR_GATEWAY_CONFIG,'utf8'));const gateway=new Gateway({...config,encryptionKey:Buffer.from(config.encryptionKey,'base64')});createGatewayServer(gateway,config).listen(config.port||8890,'127.0.0.1');}