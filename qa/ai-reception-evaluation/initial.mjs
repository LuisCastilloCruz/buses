import {OllamaProvider} from '../../gateway/ollama.mjs';
import fs from 'node:fs';
const provider=new OllamaProvider();
const result=await provider.propose({messages:[{role:'user',content:'Hola, ¿cuánto cuesta un corte de cabello?'}],instructions:'Negocio ficticio: Peluquería Aurora. Servicio autorizado: corte de cabello S/ 35. Horario lunes a viernes 09:00 a 18:00. No existe adaptador de reservas: derivar solicitudes a una persona.'});
fs.writeFileSync('audit/native-v7/INITIAL_INFERENCE.json',JSON.stringify({date:new Date().toISOString(),model:provider.model,result},null,2));
console.log(JSON.stringify(result));
