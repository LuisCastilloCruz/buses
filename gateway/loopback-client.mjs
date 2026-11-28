// Fixed loopback bridge for a legacy PHP runtime with outbound functions disabled.
// No URL, command or filesystem path is accepted from the request body.
import http from 'node:http';
const port=Number(process.argv[2]);if(!Number.isInteger(port)||port<1024||port>65535)throw Error('Invalid gateway port');
let input='';for await(const chunk of process.stdin){input+=chunk;if(Buffer.byteLength(input)>70000)throw Error('Input bound');}
const value=JSON.parse(input);if(typeof value.body!=='string'||Buffer.byteLength(value.body)>65536)throw Error('Invalid body');
const headers={'Content-Type':'application/json'};for(const key of ['X-AR-Client','X-AR-Time','X-AR-Nonce','X-AR-Signature']){const v=value.headers?.[key];if(typeof v!=='string'||v.length>200||/[\r\n]/.test(v))throw Error('Invalid header');headers[key]=v;}
const req=http.request({hostname:'127.0.0.1',port,path:'/internal/v1/execute',method:'POST',headers,agent:false},res=>{let size=0,parts=[];res.on('data',chunk=>{size+=chunk.length;if(size>262144){res.destroy();req.destroy();process.exitCode=1;return;}parts.push(chunk);});res.on('end',()=>{console.log(JSON.stringify({status:res.statusCode,signature:res.headers['x-ar-response-signature']||'',body:Buffer.concat(parts).toString('utf8')}));});res.on('error',()=>{process.exitCode=1;});});req.setTimeout(70000,()=>req.destroy());req.on('error',()=>{process.stderr.write('Loopback gateway unavailable\n');process.exitCode=1;});req.end(value.body);
