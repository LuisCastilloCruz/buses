import {transcribeMedia} from './audio.mjs';
import {createHmac,timingSafeEqual,createCipheriv,createDecipheriv,randomBytes} from 'node:crypto';
import {DatabaseSync} from 'node:sqlite';
export function mac(key,value){return createHmac('sha256',key).update(value).digest('hex');}
function equal(a,b){return typeof a==='string'&&typeof b==='string'&&a.length===b.length&&timingSafeEqual(Buffer.from(a),Buffer.from(b));}
export class Gateway {
 constructor({database,keys,channels,encryptionKey,live=false,fetcher=fetch}){
  if(!Buffer.isBuffer(encryptionKey)||encryptionKey.length!==32)throw Error('32-byte encryption key required');
  for(const value of Object.values(keys)){if(typeof value.secret!=='string'||value.secret.length<32||!Array.isArray(value.tenants)||value.tenants.some(t=>!Number.isSafeInteger(t)||t<1))throw Error('Invalid internal credentials');}
  this.keys=keys;this.channels=channels;this.key=encryptionKey;this.live=live;this.fetcher=fetcher;
  this.db=new DatabaseSync(database);this.db.exec('PRAGMA journal_mode=WAL; PRAGMA busy_timeout=5000; CREATE TABLE IF NOT EXISTS nonces (client TEXT, nonce TEXT, expires INTEGER, PRIMARY KEY(client,nonce)); CREATE TABLE IF NOT EXISTS ingress (id INTEGER PRIMARY KEY, channel TEXT, hash TEXT, payload TEXT, state TEXT, owner TEXT, lease INTEGER, UNIQUE(channel,hash)); CREATE TABLE IF NOT EXISTS caps (tenant INTEGER, period TEXT, used INTEGER NOT NULL, PRIMARY KEY(tenant,period)); CREATE TABLE IF NOT EXISTS requests (scope TEXT, id TEXT, hash TEXT, state TEXT, response TEXT, attempts INTEGER NOT NULL DEFAULT 0, PRIMARY KEY(scope,id));');
 }
 encrypt(value){const iv=randomBytes(12),c=createCipheriv('aes-256-gcm',this.key,iv);const data=Buffer.concat([c.update(JSON.stringify(value)),c.final()]);return Buffer.concat([iv,c.getAuthTag(),data]).toString('base64');}
 decrypt(value){const b=Buffer.from(value,'base64'),d=createDecipheriv('aes-256-gcm',this.key,b.subarray(0,12));d.setAuthTag(b.subarray(12,28));return JSON.parse(Buffer.concat([d.update(b.subarray(28)),d.final()]).toString());}
 authenticate(headers,body,now=Math.floor(Date.now()/1000)){
  const client=headers['x-ar-client'],key=this.keys[client],stamp=headers['x-ar-time'],nonce=headers['x-ar-nonce'];
  if(!key||!/^\d{10}$/.test(stamp)||Math.abs(now-Number(stamp))>60||!/^[a-f0-9]{32}$/.test(nonce)||!equal(headers['x-ar-signature'],mac(key.secret,`${client}\n${stamp}\n${nonce}\n${body}`)))throw Error('Unauthorized');
  this.db.prepare('DELETE FROM nonces WHERE expires < ?').run(now);this.db.prepare('INSERT INTO nonces VALUES(?,?,?)').run(client,nonce,now+120);return key;
 }
 async execute(principal,request){
  const {tenant,channel,operation,id,payload}=request;
  if(!principal.tenants.includes(tenant)||!['responses','input_tokens','whatsapp','transcription','ingress_pull','ingress_ack'].includes(operation)||!/^[A-Za-z0-9_-]{8,80}$/.test(id))throw Error('Forbidden');
  const cfg=this.channels[String(channel)];if(!cfg||cfg.tenant!==tenant)throw Error('Channel mismatch');
  if(operation==='ingress_pull')return this.pull(channel,id);
  if(operation==='ingress_ack'){const row=this.db.prepare('SELECT * FROM ingress WHERE id=? AND channel=? AND owner=?').get(payload.id,String(channel),payload.owner);if(!row)throw Error('Lease mismatch');this.db.prepare("UPDATE ingress SET state='done',payload='' WHERE id=?").run(row.id);return {acknowledged:true};}
  const scope=`${tenant}:${channel}:${operation}`,hash=mac(this.key,JSON.stringify(payload));
  // Persist the attempt BEFORE network I/O. Crash/timeout never repeats a paid POST.
  this.db.exec('BEGIN IMMEDIATE');
  try{
   // Re-read under the write lock: another process may have claimed a retry while BEGIN waited.
   const old=this.db.prepare('SELECT * FROM requests WHERE scope=? AND id=?').get(scope,id);
   if(old){if(old.hash!==hash)throw Error('Idempotency conflict');if(old.state==='completed'){const result=this.decrypt(old.response);this.db.exec('COMMIT');return result;}if(old.state!=='retryable'||old.attempts>=3)throw Error('Reconciliation required');}
   if(this.live){const period=new Date().toISOString().slice(0,7);if(!Number.isSafeInteger(cfg.monthlyCapUnits)||cfg.monthlyCapUnits<1||!Number.isSafeInteger(cfg.maxRequestUnits)||cfg.maxRequestUnits<1)throw Error('Sandbox spending cap missing');const used=this.db.prepare('SELECT used FROM caps WHERE tenant=? AND period=?').get(tenant,period)?.used||0;if(used+cfg.maxRequestUnits>cfg.monthlyCapUnits)throw Error('Sandbox cap exhausted');this.db.prepare('INSERT INTO caps VALUES(?,?,?) ON CONFLICT(tenant,period) DO UPDATE SET used=excluded.used').run(tenant,period,used+cfg.maxRequestUnits);}
   if(old)this.db.prepare("UPDATE requests SET state='pending',attempts=attempts+1 WHERE scope=? AND id=?").run(scope,id);else this.db.prepare('INSERT INTO requests(scope,id,hash,state,response,attempts) VALUES(?,?,?,?,NULL,1)').run(scope,id,hash,'pending');this.db.exec('COMMIT');
  }catch(e){this.db.exec('ROLLBACK');throw e;}
  try{
   if(!this.live||!cfg.sandboxAuthorized)throw Error('External sandbox disabled');
   if(operation==='transcription'){const result=await transcribeMedia(cfg,payload.media_id,{fetcher:this.fetcher});this.db.prepare('UPDATE requests SET state=?,response=? WHERE scope=? AND id=?').run('completed',this.encrypt(result),scope,id);return result;}
   let url,token,body;
   if(operation==='whatsapp'){
    if(!/^v\d{2}\.0$/.test(cfg.version)||!/^\d{5,30}$/.test(cfg.phone)||!cfg.recipients.includes(payload.to)||payload.messaging_product!=='whatsapp'||!['text','template'].includes(payload.type))throw Error('Unapproved recipient or message');
    if(payload.type==='template'&&!cfg.templates.includes(payload.template?.name+':'+payload.template?.language?.code))throw Error('Unapproved template');
    url=`https://graph.facebook.com/${cfg.version}/${cfg.phone}/messages`;token=cfg.metaToken;body=payload;
   }else{
    if(!cfg.models.includes(payload.model)||payload.store===true||payload.previous_response_id||payload.conversation||payload.background||payload.stream||!Array.isArray(payload.input)||payload.input.length>30)throw Error('Unapproved model or context');
    if(operation==='responses'&&(!Number.isInteger(payload.max_output_tokens)||payload.max_output_tokens<128||payload.max_output_tokens>4096))throw Error('Output bound required');
    if((payload.tools||[]).some(t=>t.type!=='function'||t.strict!==true))throw Error('Hosted tools forbidden');
    url='https://api.openai.com/v1/responses'+(operation==='input_tokens'?'/input_tokens':'');token=cfg.openaiKey;body=payload;
   }
   const response=await this.fetcher(url,{method:'POST',headers:{Authorization:`Bearer ${token}`,'Content-Type':'application/json'},body:JSON.stringify(body),redirect:'error',signal:AbortSignal.timeout(25000)});
   const reader=response.body.getReader();let bytes=0,parts=[];try{while(true){const {done,value}=await reader.read();if(done)break;bytes+=value.length;if(bytes>262144)throw Error('Response too large');parts.push(Buffer.from(value));}}finally{await reader.cancel();}
   if(operation==='whatsapp'&&response.status===429){this.db.prepare("UPDATE requests SET state='retryable' WHERE scope=? AND id=?").run(scope,id);const e=Error('Retryable rejection');e.retryAfter=30;throw e;}
   if(response.status!==200)throw Error('Provider failure');const result=JSON.parse(Buffer.concat(parts));
   this.db.prepare('UPDATE requests SET state=?,response=? WHERE scope=? AND id=?').run('completed',this.encrypt(result),scope,id);return result;
  }catch(e){if(e.retryAfter)throw e;this.db.prepare('UPDATE requests SET state=? WHERE scope=? AND id=?').run('unknown',scope,id);throw Error('Provider outcome unavailable; reconcile');}
 }

 ingest(channel,body,signature){
  const cfg=this.channels[String(channel)];if(!cfg||cfg.receiver!=='aireception'||!cfg.appSecret||Buffer.byteLength(body)>65536||!equal(signature,'sha256='+mac(cfg.appSecret,body)))throw Error('Webhook rejected');
  const value=JSON.parse(body);if(value.object!=='whatsapp_business_account'||!Array.isArray(value.entry)||value.entry.length>20)throw Error('Webhook shape');
  for(const e of value.entry){if(e.id!==cfg.waba||!Array.isArray(e.changes)||e.changes.length>20)throw Error('WABA mismatch');for(const c of e.changes){if(c.field!=='messages'||c.value?.metadata?.phone_number_id!==cfg.phone)throw Error('Phone mismatch');}}
  const hash=mac(this.key,body);this.db.prepare("INSERT OR IGNORE INTO ingress(channel,hash,payload,state,owner,lease) VALUES(?,?,?,'pending','',0)").run(String(channel),hash,this.encrypt({body,signature}));return {accepted:true};
 }
 pull(channel,owner){
  const now=Math.floor(Date.now()/1000);this.db.exec('BEGIN IMMEDIATE');
  try{const row=this.db.prepare("SELECT * FROM ingress WHERE channel=? AND (state='pending' OR (state='leased' AND lease<?)) ORDER BY id LIMIT 1").get(String(channel),now);if(!row){this.db.exec('COMMIT');return {event:null};}this.db.prepare("UPDATE ingress SET state='leased',owner=?,lease=? WHERE id=?").run(owner,now+120,row.id);this.db.exec('COMMIT');return {event:{id:row.id,owner,...this.decrypt(row.payload)}};}catch(e){this.db.exec('ROLLBACK');throw e;}
 }

 close(){this.db.close();}
}