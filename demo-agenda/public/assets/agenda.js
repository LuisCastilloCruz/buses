"use strict";
const $ = id => document.getElementById(id);
const state = {catalog: null, bookings: [], days: 1, modal: null, config: null};
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const key = () => crypto.randomUUID();
function node(tag, text, cls) { const e=document.createElement(tag); if(text!==undefined)e.textContent=text;if(cls)e.className=cls;return e; }
function notice(message,error=false){const n=$('notice');n.textContent=message;n.className=error?'error':'';n.hidden=false;clearTimeout(state.noticeTimer);state.noticeTimer=setTimeout(()=>n.hidden=true,7000);}
async function api(path,method='GET',body=null,idempotency=null){
 const headers={'Accept':'application/json','X-CSRF-TOKEN':csrf};if(body!==null)headers['Content-Type']='application/json';if(idempotency)headers['Idempotency-Key']=idempotency;
 const r=await fetch(path,{method,headers,credentials:'same-origin',body:body===null?undefined:JSON.stringify(body)});
 const data=await r.json();if(!r.ok){if(r.status===401)location.assign('/login');throw new Error(data.errors?Object.values(data.errors).flat().join(' '):data.message||'No se pudo completar la operación.');}return data;
}
function attempt(fn){return async e=>{if(e)e.preventDefault();try{await fn(e);}catch(error){notice(error.message,true);}};}
function options(select,rows,all){select.replaceChildren();if(all)select.append(new Option(all,''));for(const r of rows)select.append(new Option(r.name,String(r.id)));}
function nameOf(type,id){return state.catalog[type].find(x=>Number(x.id)===Number(id))?.name||'?';}
function datePlus(day,offset){const d=new Date(day+'T12:00:00');d.setDate(d.getDate()+offset);return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');}
function labelDay(day){return new Date(day+'T12:00:00').toLocaleDateString('es-PE',{weekday:'long',day:'numeric',month:'long'});}
const statuses={confirmed:'Confirmada',pending:'Pendiente',cancelled:'Cancelada'};
async function refreshCalendar(){
 const day=$('calendar-date').value, pro=$('professional-filter').value;
 state.bookings=await api('/api/calendar?'+new URLSearchParams({day,days:state.days,...(pro?{professional_id:pro}:{})}));
 $('calendar-title').textContent=state.days===1?labelDay(day):'Semana del '+new Date(day+'T12:00:00').toLocaleDateString('es-PE');
 $('calendar-total').textContent=state.bookings.length+' reservas';
 $('metric-active').textContent=state.bookings.filter(x=>x.status!=='cancelled').length;
 $('metric-pending').textContent=state.bookings.filter(x=>x.status==='pending').length;
 $('metric-pros').textContent=state.catalog.professionals.filter(x=>x.active).length;
 const grid=$('calendar-grid');grid.replaceChildren();grid.className=state.days===7?'week-grid':'day-list';
 for(let i=0;i<state.days;i++){
  const dayKey=datePlus(day,i),rows=state.bookings.filter(x=>x.day===dayKey);const column=state.days===7?node('section',undefined,'day-column'):grid;
  if(state.days===7){column.append(node('div',labelDay(dayKey),'day-label'));grid.append(column);}
  if(!rows.length){const empty=node('div',undefined,'empty');empty.append(node('b',state.days===7?'Sin citas':'Un espacio para lo que viene.'));if(state.days===1)empty.append(node('span','Crea una cita o prueba el agente de demostración.'));column.append(empty);}
  for(const b of rows){const card=node('button',undefined,'booking-card '+b.status);card.type='button';card.dataset.bookingId=b.id;card.setAttribute('aria-label','Cita '+b.client_name+' '+b.start);card.append(node('span',b.start+' - '+b.end,'booking-time'),node('strong',b.client_name),node('small',nameOf('services',b.service_id)+' - '+nameOf('professionals',b.professional_id)),node('span',statuses[b.status]||b.status,'badge'));card.addEventListener('click',attempt(()=>detail(b)));column.append(card);}
 }
}
function resetSlots(){if(state.modal){state.modal.start=null;state.modal.key=key();}$('slot-list').replaceChildren();$('save-booking').disabled=true;$('slot-caption').textContent='Consulta los horarios disponibles.';}
function updateServices(){const id=Number($('booking-professional').value);const ids=state.catalog.assignments.filter(a=>Number(a.professional_id)===id).map(a=>Number(a.service_id));options($('booking-service'),state.catalog.services.filter(s=>s.active&&ids.includes(Number(s.id))));resetSlots();}
function openBooking(existing=null,agent=false){
 state.modal={existing,agent,start:null,key:key()};options($('booking-professional'),state.catalog.professionals.filter(p=>p.active));
 if(existing)$('booking-professional').value=existing.professional_id;updateServices();if(existing)$('booking-service').value=existing.service_id;
 $('booking-date').value=existing?.day||$('calendar-date').value||datePlus(state.catalog.today,1);
 $('booking-client').value=existing?.client_name||(agent?'Cliente demo':'');$('booking-client').readOnly=!!existing;
 $('booking-professional').disabled=!!existing;$('booking-service').disabled=!!existing;
 $('booking-status-label').hidden=!!existing;$('booking-status').value='confirmed';
 $('booking-title').textContent=existing?'Reprogramar cita':'Nueva cita';$('save-booking').textContent=existing?'Guardar nuevo horario':'Guardar cita';
 $('booking-dialog').showModal();
}
async function loadSlots(){
 const params={professional_id:$('booking-professional').value,service_id:$('booking-service').value,day:$('booking-date').value};
 resetSlots();const rows=await api((state.modal.existing?'/api/bookings/'+state.modal.existing.id+'/availability?':'/api/availability?')+new URLSearchParams(params));$('slot-caption').textContent=rows.length?'Selecciona un horario. La disponibilidad se verifica nuevamente al guardar.':'No hay horarios disponibles. Prueba otro día o revisa horarios y registros heredados.';
 for(const s of rows){const b=node('button',s.start+' - '+s.end);b.type='button';b.dataset.start=s.start;b.addEventListener('click',()=>{document.querySelectorAll('#slot-list button').forEach(x=>x.classList.remove('selected'));b.classList.add('selected');state.modal.start=s.start;state.modal.key=key();$('save-booking').disabled=false;});$('slot-list').append(b);}
}
async function saveBooking(){
 if(!state.modal.start)return;const m=state.modal;
 const data=m.existing?{booking_id:m.existing.id,version:m.existing.version,day:$('booking-date').value,start:m.start}:{professional_id:Number($('booking-professional').value),service_id:Number($('booking-service').value),day:$('booking-date').value,start:m.start,client_name:$('booking-client').value,status:$('booking-status').value};
 $('save-booking').disabled=true;
 try{const result=await api('/api/commands/'+(m.existing?'reschedule':'book'),'POST',data,m.key);$('calendar-date').value=result.booking.day;await refreshCalendar();$('booking-dialog').close();notice('Cita #'+result.booking.id+' guardada en la agenda.');if(m.agent){bubble('Tu cita #'+result.booking.id+' está guardada para '+result.booking.day+' a las '+result.booking.start+'.','incoming');const link=node('a','Ver cita en el calendario');link.href='/calendar?day='+result.booking.day;link.className='primary';$('messages').append(link);$('messages').scrollTop=$('messages').scrollHeight;}}
 finally{$('save-booking').disabled=false;}
}
async function detail(b){
 const box=$('booking-detail');box.replaceChildren();for(const [label,value] of [['Cliente ficticio',b.client_name],['Servicio',nameOf('services',b.service_id)],['Profesional',nameOf('professionals',b.professional_id)],['Horario',b.day+' - '+b.start+' - '+b.end],['Estado',statuses[b.status]],['Duración / margen',b.duration_minutes+' min / '+b.buffer_minutes+' min']]){const row=node('div',undefined,'detail-line');row.append(node('span',label),node('strong',value));box.append(row);}
 $('detail-actions').replaceChildren();if(state.catalog.role!=='viewer'&&b.status!=='cancelled'){
  if(b.status==='pending'){const btn=node('button','Confirmar cita','primary');btn.onclick=attempt(async()=>{await api('/api/commands/confirm','POST',{booking_id:b.id,version:b.version},key());await refreshCalendar();$('detail-dialog').close();notice('Cita confirmada.');});$('detail-actions').append(btn);}
  const move=node('button','Reprogramar');move.onclick=()=>{$('detail-dialog').close();openBooking(b);};
  const cancel=node('button','Cancelar cita','danger');cancel.onclick=attempt(async()=>{await api('/api/commands/cancel','POST',{booking_id:b.id,version:b.version},key());await refreshCalendar();$('detail-dialog').close();notice('Cita cancelada. El horario vuelve a estar disponible.');});$('detail-actions').append(move,cancel);
 }
 const events=await api('/api/bookings/'+b.id+'/events');$('booking-history').replaceChildren();const verbs={book:'Creada',confirm:'Confirmada',cancel:'Cancelada',reschedule:'Reprogramada'};for(const e of events)$('booking-history').append(node('div',e.created_at+' - '+(verbs[e.action]||e.action)+' - Usuario '+e.actor_id,'history-line'));
 $('detail-dialog').showModal();
}
function bubble(text,side){const e=node('div',text,'bubble '+side);$('messages').append(e);$('messages').scrollTop=$('messages').scrollHeight;}
function renderSettings(){
 for(const [type,id] of [['professionals','professional-list'],['services','service-list']]){
  $(id).replaceChildren();for(const item of state.catalog[type]){const row=node('div',undefined,'config-row');const copy=node('div');copy.append(node('strong',item.name),node('small',type==='services'?item.duration_minutes+' minutos - margen '+item.buffer_minutes+' min':(item.active?'Activo':'Inactivo')));const edit=node('button','Editar');edit.disabled=state.catalog.role!=='admin';edit.onclick=()=>openConfig(type==='services'?'service':'professional',item);row.append(copy,edit);$(id).append(row);}
 }
 $('add-professional').disabled=$('add-service').disabled=state.catalog.role!=='admin';
}
function openConfig(kind,item=null){state.config={kind,item};$('config-title').textContent=(item?'Editar ':'Añadir ')+(kind==='service'?'servicio':'profesional');$('config-name').value=item?.name||'';$('config-active').value=item?.active===0?'0':'1';$('service-fields').hidden=kind!=='service';$('professional-fields').hidden=kind!=='professional';$('config-duration').required=$('config-buffer').required=kind==='service';$('config-duration').value=item?.duration_minutes||30;$('config-buffer').value=item?.buffer_minutes||0;options($('config-services'),state.catalog.services);const ids=item?state.catalog.assignments.filter(a=>Number(a.professional_id)===Number(item.id)).map(a=>Number(a.service_id)):[];for(const o of $('config-services').options)o.selected=ids.includes(Number(o.value));const policy=item?.policy?JSON.parse(item.policy):{weekdays:{},exceptions:{},slot_minutes:15,min_notice_minutes:0};state.config.policy=policy;
 $('weekly-hours').replaceChildren();['Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo'].forEach((name,i)=>{const label=node('label',name);const input=node('input');input.id='hours-'+(i+1);input.placeholder='Cerrado';input.value=(policy.weekdays[i+1]||[]).map(w=>w.start+'-'+w.end).join(', ');label.append(input);$('weekly-hours').append(label);});
 $('config-exceptions').value=Object.entries(policy.exceptions||{}).map(([d,w])=>d+': '+(w.length?w.map(x=>x.start+'-'+x.end).join(', '):'cerrado')).join('\n');$('config-step').value=policy.slot_minutes||15;$('config-notice').value=policy.min_notice_minutes||0;
 $('config-dialog').showModal();}
function parseWindows(text){if(!text.trim()||text.trim().toLowerCase()==='cerrado')return [];return text.split(',').map(part=>{const m=part.trim().match(/^(\d{2}:\d{2})-(\d{2}:\d{2})$/);if(!m)throw new Error('Usa horarios HH:MM-HH:MM separados por comas.');return {start:m[1],end:m[2]};});}
function readPolicy(){const p={...state.config.policy,weekdays:{},exceptions:{},slot_minutes:Number($('config-step').value),min_notice_minutes:Number($('config-notice').value)};for(let i=1;i<=7;i++)p.weekdays[i]=parseWindows($('hours-'+i).value);for(const line of $('config-exceptions').value.split('\n').filter(x=>x.trim())){const m=line.match(/^(\d{4}-\d{2}-\d{2}):\s*(.*)$/);if(!m)throw new Error('Excepcion: YYYY-MM-DD: cerrado o tramos horarios.');p.exceptions[m[1]]=parseWindows(m[2]);}return p;}
async function saveConfig(){const c=state.config;const d={name:$('config-name').value,active:Number($('config-active').value)};if(c.item)d.id=c.item.id;if(c.kind==='service'){d.duration_minutes=Number($('config-duration').value);d.buffer_minutes=Number($('config-buffer').value);}else{d.policy=readPolicy();d.service_ids=[...$('config-services').selectedOptions].map(x=>Number(x.value));}await api('/api/configure/'+c.kind,'POST',d);await loadCatalog();renderSettings();$('config-dialog').close();notice('Configuración guardada.');}
async function loadCatalog(){state.catalog=await api('/api/bootstrap');options($('business'),state.catalog.businesses);$('business').value=state.catalog.business.id;$('business-name').textContent=state.catalog.business.name;$('user-name').textContent=state.catalog.user.name;options($('professional-filter'),state.catalog.professionals,'Todos los profesionales');$('new-booking').disabled=$('agent-book').disabled=state.catalog.role==='viewer';}
async function init(){
 await loadCatalog();const page=document.body.dataset.page;$(page+'-page').hidden=false;document.querySelector('[data-nav="'+page+'"]').classList.add('active');$('calendar-date').value=new URLSearchParams(location.search).get('day')||datePlus(state.catalog.today,1);await refreshCalendar();renderSettings();
 $('business').onchange=attempt(async()=>{await api('/api/business','POST',{business_id:Number($('business').value)});location.reload();});
 $('calendar-date').onchange=$('professional-filter').onchange=attempt(refreshCalendar);
 $('day-view').onclick=attempt(async()=>{state.days=1;$('day-view').classList.add('selected');$('week-view').classList.remove('selected');await refreshCalendar();});
 $('week-view').onclick=attempt(async()=>{state.days=7;$('week-view').classList.add('selected');$('day-view').classList.remove('selected');await refreshCalendar();});
 for(const [id,delta]of [['previous',-1],['next',1]])$(id).onclick=attempt(async()=>{$('calendar-date').value=datePlus($('calendar-date').value,delta*state.days);await refreshCalendar();});
 $('today').onclick=attempt(async()=>{$('calendar-date').value=state.catalog.today;await refreshCalendar();});
 $('new-booking').onclick=()=>openBooking();$('agent-book').onclick=()=>openBooking(null,true);
 $('booking-professional').onchange=updateServices;$('booking-service').onchange=$('booking-date').onchange=resetSlots;
 $('load-slots').onclick=attempt(loadSlots);$('booking-form').onsubmit=attempt(saveBooking);
 $('chat-form').onsubmit=attempt(async()=>{const text=$('chat-text').value;bubble(text,'outgoing');$('chat-text').value='';const r=await api('/api/agent','POST',{text});bubble(r.reply,'incoming');});
 $('add-professional').onclick=()=>openConfig('professional');$('add-service').onclick=()=>openConfig('service');$('config-form').onsubmit=attempt(saveConfig);
 document.querySelectorAll('[data-close]').forEach(b=>b.onclick=()=>$(b.dataset.close).close());
 document.body.dataset.ready='true';
}
init().catch(e=>notice(e.message,true));
