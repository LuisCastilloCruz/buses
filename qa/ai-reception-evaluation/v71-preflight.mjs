import {localHeadroom} from '../../gateway/ollama.mjs';
try{await localHeadroom('qwen3:1.7b');console.log('Local inference health and memory preflight passed');}catch(e){console.error(e.message);process.exitCode=2;}
