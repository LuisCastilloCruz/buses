// Headless Chrome regression tests with a NEW disposable profile. No user's browser profile.
import fs from 'node:fs/promises';
import path from 'node:path';
import {spawn} from 'node:child_process';
import {fileURLToPath} from 'node:url';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'../..');
const chrome=process.env.AGENDA_TEST_CHROME||'C:/Program Files/Google/Chrome/Application/chrome.exe';
const port=19227;
const clientName='Cliente ficticio UI '+Date.now();
const artifacts=path.join(root,'audit/agenda-v1/screenshots');await fs.mkdir(artifacts,{recursive:true});
const profile=path.join(root,'.agenda-runtime/browser-'+Date.now());
const proc=spawn(chrome,['--headless=new','--disable-gpu','--no-first-run','--no-default-browser-check','--disable-background-networking','--remote-debugging-address=127.0.0.1','--remote-debugging-port='+port,'--user-data-dir='+profile,'about:blank'],{windowsHide:true,stdio:'ignore'});
const delay=ms=>new Promise(r=>setTimeout(r,ms));let socket;let seq=0;const pending=new Map();const errors=[];let count=0;
async function command(method,params={}){const id=++seq;return new Promise((resolve,reject)=>{const timer=setTimeout(()=>{pending.delete(id);reject(new Error('Timeout '+method));},15000);pending.set(id,{resolve:r=>{clearTimeout(timer);resolve(r);},reject});socket.send(JSON.stringify({id,method,params}));});}
async function evaluate(expression){const r=await command('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw new Error(r.exceptionDetails.text+' '+(r.exceptionDetails.exception?.description||''));return r.result.value;}
async function wait(expression){for(let i=0;i<100;i++){if(await evaluate(expression))return;await delay(100);}throw new Error('DOM wait failed: '+expression+' | '+await evaluate('document.body.innerText'));}
async function click(selector){await wait(`!!document.querySelector(${JSON.stringify(selector)})`);await evaluate(`document.querySelector(${JSON.stringify(selector)}).click()`);}
async function value(selector,v){await evaluate(`(()=>{const e=document.querySelector(${JSON.stringify(selector)});e.value=${JSON.stringify(v)};e.dispatchEvent(new Event('change',{bubbles:true}));})()`);}
async function shot(name){const r=await command('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});await fs.writeFile(path.join(artifacts,name+'.png'),Buffer.from(r.data,'base64'));}
function check(ok,label){if(!ok)throw new Error(label);console.log('PASS '+label);count++;}
try{
 let list;for(let i=0;i<100;i++){try{list=await(await fetch('http://127.0.0.1:'+port+'/json/list')).json();break;}catch{await delay(100);}}
 if(!list)throw new Error('Headless Chrome did not start');
 socket=new WebSocket(list.find(t=>t.type==='page').webSocketDebuggerUrl);await new Promise((resolve,reject)=>{socket.onopen=resolve;socket.onerror=reject;});
 socket.onmessage=e=>{const m=JSON.parse(e.data);if(m.id){const p=pending.get(m.id);pending.delete(m.id);if(p)m.error?p.reject(new Error(m.error.message)):p.resolve(m.result);}if(m.method==='Runtime.exceptionThrown')errors.push(m.params.exceptionDetails.text);};
 await command('Page.enable');await command('Runtime.enable');await command('Emulation.setDeviceMetricsOverride',{width:1440,height:1000,deviceScaleFactor:1,mobile:false});
 await command('Page.navigate',{url:'http://127.0.0.1:8874/login'});await wait("!!document.querySelector('input[name=email]')");
 const accounts=JSON.parse(await fs.readFile(path.join(root,'.agenda-runtime/accounts.json'),'utf8'));const account=accounts.find(a=>a.id===1);
 await value('input[name=email]',account.email);await value('input[name=password]',account.password);await click('button[type=submit]');
 await wait("document.body.dataset.ready==='true' && document.querySelector('#business-name')?.textContent.includes('Tinta')");check(true,'browser login and initialized JS calendar');
 const testDay=await evaluate("(()=>{let d=new Date(document.querySelector('#calendar-date').value+'T12:00:00');if(d.getDay()===0)d.setDate(d.getDate()+1);return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');})()");await value('#calendar-date',testDay);
 await click('#new-booking');await wait("document.querySelector('#booking-dialog').open");await value('#booking-client',clientName);await click('#load-slots');await wait("document.querySelectorAll('#slot-list button').length>0");await click('#slot-list button');await shot('01-availability');await click('#save-booking');await wait("!document.querySelector('#booking-dialog').open && document.querySelector('#calendar-grid').textContent.includes('Cliente ficticio UI')");
 const id=await evaluate(`[...document.querySelectorAll('.booking-card')].find(e=>e.textContent.includes(${JSON.stringify(clientName)})).dataset.bookingId`);check(!!id,'browser creates persistent booking');await shot('02-tattoo-calendar');
 await click(`[data-booking-id="${id}"]`);await wait("document.querySelector('#detail-dialog').open");await evaluate("[...document.querySelectorAll('#detail-actions button')].find(b=>b.textContent==='Reprogramar').click()");await click('#load-slots');await wait("document.querySelectorAll('#slot-list button').length>1");await evaluate("document.querySelectorAll('#slot-list button')[1].click()");await click('#save-booking');await wait("!document.querySelector('#booking-dialog').open");await click(`[data-booking-id="${id}"]`);await wait("document.querySelector('#booking-history').textContent.includes('Reprogramada')");check(true,'browser rescheduling and history');
 await evaluate("[...document.querySelectorAll('#detail-actions button')].find(b=>b.textContent==='Cancelar cita').click()");await wait(`document.querySelector('[data-booking-id="${id}"]')?.classList.contains('cancelled')`);check(true,'browser cancellation');
 await click('#week-view');await wait("document.querySelectorAll('.day-column').length===7");check(true,'weekly calendar');await shot('03-week');
 await value('#business','2');await wait("document.body.dataset.ready==='true' && document.querySelector('#business-name')?.textContent.includes('Sonrisa')");check(!(await evaluate("document.querySelector('#calendar-grid').textContent.includes('Cliente ficticio UI')")),'browser business switch isolates tattoo data');await shot('04-dental-calendar');
 await click('[data-nav=agent]');await wait("document.body.dataset.ready==='true' && document.querySelector('#agent-page') && !document.querySelector('#agent-page').hidden");await value('#chat-text','Quiero una cita dental');await click('#chat-form button');await wait("document.querySelectorAll('.bubble').length>=3");await click('#agent-book');await value('#booking-client','Paciente ficticio navegador');await click('#load-slots');await wait("document.querySelectorAll('#slot-list button').length>0");await click('#slot-list button');await click('#save-booking');await wait("!!document.querySelector('#messages a')");check(true,'simulated WhatsApp choice creates real dental booking');await shot('05-agent');
 await click('#messages a');await wait("document.querySelector('#calendar-grid')?.textContent.includes('Paciente ficticio navegador')");check(true,'agent booking visible after navigation');await shot('06-dental-booking');
 await click('[data-nav=settings]');await wait("document.querySelectorAll('#professional-list .config-row').length>=2");await click('#professional-list button');await wait("document.querySelectorAll('#weekly-hours input').length===7");check(true,'professional working-hours editor');await shot('07-settings');
 const oldHours=await evaluate("document.querySelector('#hours-1').value");const newHours=oldHours.startsWith('08:00')?'07:00-12:00, 14:00-17:00':'08:00-12:00, 14:00-17:00';await value('#hours-1',newHours);await click('#config-form button.primary');await wait("!document.querySelector('#config-dialog').open");await click('#professional-list button');await wait(`document.querySelector('#config-dialog').open && document.querySelector('#hours-1').value===${JSON.stringify(newHours)}`);check(true,'browser saves and reloads working hours');await value('#hours-1',oldHours);await click('#config-form button.primary');await wait("!document.querySelector('#config-dialog').open");
 check(errors.length===0,'no browser JavaScript exceptions');
 console.log('RESULT '+count+' real headless Chrome assertions passed');
}finally{if(socket){try{await command('Browser.close');}catch{}socket.close();}else proc.kill();}
