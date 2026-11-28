from pathlib import Path
import json,csv,statistics,math
root=Path(__file__).resolve().parents[2]/'audit/native-v7-1'
reports=[];human=[]
for directory in ['coherence-iteration-0','coherence','coherence-iteration-2','coherence-iteration-3']:
 business={};failures=[];cases={};latencies=[]
 for file in (root/directory).glob('*.json'):
  r=json.loads(file.read_text(encoding='utf-8'))
  if 'rows' not in r:continue
  key=r['business']+'-'+r['scenario'];cases[key]=r
  group=business.setdefault(r['business'],{'conversations':0,'turns':0,'passed':0,'held_out_turns':0,'held_out_passed':0})
  group['conversations']+=1
  for i,row in enumerate(r['rows']):
   group['turns']+=1;group['passed']+=int(row['passed'])
   if r['split']=='held-out':group['held_out_turns']+=1;group['held_out_passed']+=int(row['passed'])
   if not row['passed']:failures.append({'case':key,'turn':i,'split':r['split'],'user':row['user'],'expected':row['expected'],'proposal':row.get('proposal'),'error':row.get('error')})
   metric=row.get('metrics') or {}
   if metric.get('elapsed_ms') is not None:latencies.append(metric['elapsed_ms'])
  if directory=='coherence-iteration-3':human.append([r['business'],r['scenario'],r['split'],str(file.relative_to(root)),'','','','','',''])
 total=sum(v['turns'] for v in business.values());passed=sum(v['passed'] for v in business.values());latencies.sort()
 reports.append({'run':directory,'businesses':business,'conversations':len(cases),'turns':total,'passed':passed,'routing_percent':100*passed/total if total else None,'failures':failures,'latency_ms':{q:latencies[min(len(latencies)-1,math.ceil(len(latencies)*n)-1)] for q,n in [('p50',.5),('p95',.95),('p99',.99)]} if latencies else {},'quality_approved':False,'scope':'Combined real model plus backend tool restrictions. Fictional availability backend; native E2E is separate. Human language rubric not scored.'})
(root/'COHERENCE_RESULTS.json').write_text(json.dumps(reports,indent=2,ensure_ascii=False),encoding='utf-8')
with (root/'HUMAN_REVIEW.csv').open('w',newline='',encoding='utf-8') as f:
 w=csv.writer(f);w.writerow(['business','scenario','split','file','intent_1_5','continuity_1_5','clarity_1_5','tone_1_5','reviewer','notes']);w.writerows(human)
with (root/'BUSINESS_RESULTS.csv').open('w',newline='',encoding='utf-8') as f:
 w=csv.writer(f);w.writerow(['business','conversations','turns','passed','routing_percent','held_out_turns','held_out_passed'])
 for name,v in reports[-1]['businesses'].items():w.writerow([name,v['conversations'],v['turns'],v['passed'],100*v['passed']/v['turns'],v['held_out_turns'],v['held_out_passed']])
print(json.dumps([{k:r[k] for k in ['run','conversations','turns','passed','routing_percent']} for r in reports]))
