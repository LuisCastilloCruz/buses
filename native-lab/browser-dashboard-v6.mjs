import os from 'node:os';
// Headless Chrome regression tests with a NEW disposable profile. No user's browser profile.
import fs from 'node:fs/promises';
import path from 'node:path';
import {spawn} from 'node:child_process';
import {fileURLToPath} from 'node:url';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');
const chrome=process.env.AGENDA_TEST_CHROME||'C:/Program Files/Google/Chrome/Application/chrome.exe';
const port=19231;
const clientName='Cliente ficticio UI '+Date.now();
const artifacts=path.join(root,'audit/native-v7/screenshots');await fs.mkdir(artifacts,{recursive:true});
const profile=path.join(root,'.native-runtime/browser-'+Date.now());
if(os.freemem()<2400*1024**2)throw Error('RESOURCE_BLOCKED: Chrome requires headroom');
const proc=spawn(chrome,['--headless=new','--disable-gpu','--no-first-run','--no-default-browser-check','--disable-background-networking','--remote-debugging-address=127.0.0.1','--remote-debugging-port='+port,'--user-data-dir='+profile,'about:blank'],{windowsHide:true,stdio:'ignore'});
const resourceTimer=setInterval(()=>{if(os.freemem()<2*1024**3){console.error('RESOURCE_BLOCKED: Chrome memory guard');proc.kill();process.exitCode=2;}},1000);resourceTimer.unref();
const delay=ms=>new Promise(r=>setTimeout(r,ms));let socket;let seq=0;const pending=new Map();const errors=[];let count=0;
async function command(method,params={}){const id=++seq;return new Promise((resolve,reject)=>{const timer=setTimeout(()=>{pending.delete(id);reject(new Error('Timeout '+method));},15000);pending.set(id,{resolve:r=>{clearTimeout(timer);resolve(r);},reject});socket.send(JSON.stringify({id,method,params}));});}
async function evaluate(expression){const r=await command('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw new Error(r.exceptionDetails.text+' '+(r.exceptionDetails.exception?.description||''));return r.result.value;}
async function wait(expression){for(let i=0;i<300;i++){try{if(await evaluate(expression))return;}catch(e){if(!String(e).includes('context')&&!String(e).includes('null'))throw e;}await delay(100);}throw new Error('DOM wait failed: '+expression+' | '+await evaluate('document.body.innerText'));}
async function click(selector){await wait(`!!document.querySelector(${JSON.stringify(selector)})`);await evaluate(`document.querySelector(${JSON.stringify(selector)}).click()`);}
async function value(selector,v){await evaluate(`(()=>{const e=document.querySelector(${JSON.stringify(selector)});e.value=${JSON.stringify(v)};e.dispatchEvent(new Event('change',{bubbles:true}));})()`);}
async function shot(name){const r=await command('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});await fs.writeFile(path.join(artifacts,name+'.png'),Buffer.from(r.data,'base64'));}
function check(ok,label){if(!ok)throw new Error(label);console.log('PASS '+label);count++;}
try{
 let tabs;for(let i=0;i<60;i++){try{tabs=await(await fetch('http://127.0.0.1:'+port+'/json')).json();break;}catch{await delay(200);}}
 socket=new WebSocket(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);await new Promise(r=>socket.onopen=r);
 socket.onmessage=e=>{const m=JSON.parse(e.data);if(m.id){const p=pending.get(m.id);pending.delete(m.id);if(p)m.error?p.reject(new Error(m.error.message)):p.resolve(m.result);}if(m.method==='Runtime.exceptionThrown')errors.push(m.params.exceptionDetails.exception?.description||m.params.exceptionDetails.text);if(m.method==='Fetch.requestPaused'){const u=new URL(m.params.request.url);command(u.hostname==='localhost'||u.hostname.endsWith('.localhost')||u.hostname==='127.0.0.1'?'Fetch.continueRequest':'Fetch.failRequest',{requestId:m.params.requestId,...(!(u.hostname==='localhost'||u.hostname.endsWith('.localhost')||u.hostname==='127.0.0.1')?{errorReason:'BlockedByClient'}:{})}).catch(()=>{});}};
 await command('Page.enable');await command('Runtime.enable');await command('Fetch.enable',{patterns:[{urlPattern:'*'}]});await command('Emulation.setDeviceMetricsOverride',{width:1500,height:1050,deviceScaleFactor:1,mobile:false});
 const accounts=JSON.parse(await fs.readFile(path.join(root,'.native-runtime/accounts.json'),'utf8'));
 async function navigate(url,condition){await command('Page.navigate',{url});await wait(condition);}
 async function login(name){const a=accounts[name];await navigate(a.url+'/login',"!!document.querySelector('input[name=email]')");await value('input[name=email]',a.email);await value('input[name=password]',a.password);await evaluate("document.querySelector('form').submit()");await wait("!!document.querySelector('#sidebar-left')");await delay(1500);check(true,name+' original MultiFactura login and sidebar');}
 for(const name of ['tattoo','dental','comercio','cuarta']){
  await login(name);
  await wait(`(()=>{const seen=new Set();function visit(v){if(!v||seen.has(v))return false;seen.add(v);if(v.loaders&&Object.keys(v.loaders).length>=7)return Object.values(v.loaders).every(x=>x===false);return (v.$children||[]).some(visit);}return [...document.querySelectorAll('*')].some(e=>visit(e.__vue__));})()`);
  check(true,name+' dashboard asynchronous data fully loaded');
  await shot('25-loaded-dashboard-'+name);
 }
 check(errors.length===0,'no dashboard runtime errors');console.log('RESULT '+count+' complete-dashboard Chrome assertions');
}catch(e){console.error(String(e));console.error('JS_ERRORS '+JSON.stringify(errors));process.exitCode=1;}finally{clearInterval(resourceTimer);if(socket){try{await command('Browser.close');}catch{}socket.close();}else proc.kill();}
