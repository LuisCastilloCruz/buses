import test from 'node:test';
import assert from 'node:assert/strict';
import {execFile} from 'node:child_process';
import {promisify} from 'node:util';
import {mkdtemp,readFile,readdir,rm} from 'node:fs/promises';
import {tmpdir} from 'node:os';
import path from 'node:path';
import {createHash} from 'node:crypto';
import {transcribeMedia} from './audio.mjs';

const exec=promisify(execFile);
const ffmpeg=process.env.AR_TEST_FFMPEG;
const ffprobe=process.env.AR_TEST_FFPROBE;
test('real OGG/Opus decoding with synthetic audio and mocked providers', {skip: !ffmpeg || !ffprobe}, async()=>{
 const folder=await mkdtemp(path.join(tmpdir(),'ar-decoder-test-'));
 try{
  const input=path.join(folder,'synthetic.ogg');
  await exec(ffmpeg,['-nostdin','-v','error','-f','lavfi','-i','sine=frequency=440:duration=2','-c:a','libopus',input],{windowsHide:true,timeout:10000});
  const ogg=await readFile(input);
  let transcriptions=0;
  const cfg={sandboxAuthorized:true,ffmpeg,ffprobe,mediaIds:['12345678'],version:'v99.0',maxAudioSeconds:5,transcriptionModel:'fixture',metaToken:'fake',openaiKey:'fake'};
  const fetcher=async(url,options)=>{
   const value=String(url);
   if(value.startsWith('https://graph.facebook.com/'))return Response.json({file_size:ogg.length,mime_type:'audio/ogg',sha256:createHash('sha256').update(ogg).digest('hex'),url:'https://lookaside.fbsbx.com/fixture'});
   if(value==='https://lookaside.fbsbx.com/fixture')return new Response(ogg);
   assert.equal(value,'https://api.openai.com/v1/audio/transcriptions');
   const wav=Buffer.from(await options.body.get('file').arrayBuffer());
   assert.equal(wav.subarray(0,4).toString(),'RIFF');
   assert.ok(wav.length>60000 && wav.length<70000);
   transcriptions++;return Response.json({text:'Solicitud ficticia de cita'});
  };
  const result=await transcribeMedia(cfg,'12345678',{fetcher,tempRoot:folder});
  assert.equal(result.untrusted_transcript,'Solicitud ficticia de cita');
  assert.ok(result.seconds>=2 && result.seconds<2.1);
  assert.equal(transcriptions,1);
  await assert.rejects(transcribeMedia({...cfg,maxAudioSeconds:1},'12345678',{fetcher,tempRoot:folder}),/duration/);
  assert.equal(transcriptions,1,'overlong audio must never reach transcription');
  await assert.rejects(transcribeMedia(cfg,'12345678',{fetcher:async(url,options)=>{
   if(String(url).includes('openai.com'))throw Error('simulated provider timeout');
   return fetcher(url,options);
  },tempRoot:folder}),/simulated provider timeout/);
  assert.deepEqual(await readdir(folder),['synthetic.ogg'],'temporary decoded media cleaned after success and failure');
 }finally{
  assert.ok(path.resolve(folder).startsWith(path.resolve(tmpdir())+path.sep+'ar-decoder-test-'));
  await rm(folder,{recursive:true,force:true});
 }
});
