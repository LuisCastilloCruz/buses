import {mkdtemp,writeFile,readFile,rm} from 'node:fs/promises';
import {tmpdir} from 'node:os';
import path from 'node:path';
import {execFile} from 'node:child_process';
import {promisify} from 'node:util';
import {createHash} from 'node:crypto';
const exec=promisify(execFile);
async function bounded(response,limit){if(!response.ok)throw Error('Media unavailable');const chunks=[];let size=0;try{for await(const b of response.body){size+=b.length;if(size>limit)throw Error('Media size exceeded');chunks.push(Buffer.from(b));}}catch(e){throw Error('Media unavailable or too large');}return Buffer.concat(chunks);}
export async function transcribeMedia(cfg,mediaId,{fetcher=fetch,runner=exec,tempRoot=tmpdir()}={}){
 if(!cfg.sandboxAuthorized||!cfg.ffmpeg||!cfg.ffprobe||!/^\d{5,64}$/.test(mediaId)||!cfg.mediaIds?.includes(mediaId))throw Error('Audio disabled or unauthorized');
 if(!/^v\d{2}\.0$/.test(cfg.version)||!cfg.transcriptionModel||!Number.isInteger(cfg.maxAudioSeconds)||cfg.maxAudioSeconds<1||cfg.maxAudioSeconds>120)throw Error('Audio configuration');
 const options={headers:{Authorization:`Bearer ${cfg.metaToken}`},redirect:'error',signal:AbortSignal.timeout(15000)};
 const metadata=JSON.parse(await bounded(await fetcher(`https://graph.facebook.com/${cfg.version}/${mediaId}`,options),16384));
 if(!Number.isInteger(metadata.file_size)||metadata.file_size<40||metadata.file_size>2097152||!['audio/ogg','audio/ogg; codecs=opus'].includes(metadata.mime_type))throw Error('Invalid media metadata');
 const url=new URL(metadata.url);if(url.protocol!=='https:'||url.username||url.password||url.port||!['lookaside.fbsbx.com','mmg.whatsapp.net'].includes(url.hostname))throw Error('Media origin forbidden');
 const ogg=await bounded(await fetcher(url,options),2097152);
 if(ogg.length!==metadata.file_size||ogg.subarray(0,4).toString()!=='OggS'||!ogg.subarray(0,256).includes(Buffer.from('OpusHead'))||createHash('sha256').update(ogg).digest('hex')!==metadata.sha256)throw Error('Media integrity failed');
 const folder=await mkdtemp(path.join(tempRoot,'ar-audio-'));
 try{
  const input=path.join(folder,'input.ogg'),output=path.join(folder,'output.wav');await writeFile(input,ogg,{mode:0o600});
  const limits={timeout:10000,maxBuffer:65536,windowsHide:true,shell:false};
  const probe=await runner(cfg.ffprobe,['-v','error','-max_alloc','33554432','-threads','1','-f','ogg','-protocol_whitelist','file,pipe','-show_entries','format=duration:stream=codec_name,codec_type,channels','-of','json',input],limits);const info=JSON.parse(probe.stdout);
  const seconds=Number(info.format?.duration);if(!Number.isFinite(seconds)||seconds<=0||seconds>cfg.maxAudioSeconds||info.streams?.length!==1||info.streams[0].codec_name!=='opus'||info.streams[0].codec_type!=='audio'||![1,2].includes(info.streams[0].channels))throw Error('Unsupported audio duration or stream');
  await runner(cfg.ffmpeg,['-nostdin','-v','error','-max_alloc','33554432','-threads','1','-f','ogg','-protocol_whitelist','file,pipe','-i',input,'-map','0:a:0','-t',String(cfg.maxAudioSeconds),'-ac','1','-ar','16000','-c:a','pcm_s16le','-fs','4000000',output],limits);
  const wav=await readFile(output);if(wav.length<44||wav.length>4000000||wav.subarray(0,4).toString()!=='RIFF'||wav.subarray(8,12).toString()!=='WAVE')throw Error('Invalid decoded audio');
  const form=new FormData();form.append('model',cfg.transcriptionModel);form.append('file',new Blob([wav],{type:'audio/wav'}),'voice.wav');
  const result=JSON.parse(await bounded(await fetcher('https://api.openai.com/v1/audio/transcriptions',{method:'POST',headers:{Authorization:`Bearer ${cfg.openaiKey}`},body:form,redirect:'error',signal:AbortSignal.timeout(30000)}),65536));
  if(typeof result.text!=='string'||result.text.length>2000)throw Error('Invalid transcript');return {untrusted_transcript:result.text,seconds};
 }finally{await rm(folder,{recursive:true,force:true});}
}