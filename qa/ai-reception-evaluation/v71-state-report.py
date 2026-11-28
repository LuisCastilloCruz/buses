from pathlib import Path
import json
root=Path('audit/native-v7-1')
final={}
for p in (root/'coherence-iteration-3').glob('*.json'):
 r=json.loads(p.read_text(encoding='utf-8'))
 if 'rows' in r:final[p.name]=r
comparisons=[]
for folder in ['coherence-iteration-0','coherence','coherence-iteration-2']:
 old={p.name:json.loads(p.read_text(encoding='utf-8')) for p in (root/folder).glob('*.json') if p.name in final}
 old={k:v for k,v in old.items() if 'rows' in v}
 comparisons.append({'before':folder,'same_conversations':len(old),'turns':sum(len(r['rows']) for r in old.values()),'before_passed':sum(x['passed'] for r in old.values() for x in r['rows']),'after_passed':sum(x['passed'] for k in old for x in final[k]['rows'])})
checks=[]
for name,r in final.items():
 scenario=r['scenario'];last=r['rows'][-1]['state'];sel=last['selection'];pro=2 if scenario in ['professional_change','long','change_mind'] else 1
 day={'booking':'2026-12-04','date_change':'2026-12-08','reschedule':'2026-12-09','long':'2026-12-08','change_mind':'2026-12-09'}.get(scenario,'2026-12-07')
 expected={'service_id':1,'professional_id':pro,'day':day}
 checks.append({'case':name,'expected':expected,'actual':sel,'passed':all(sel.get(k)==v for k,v in expected.items())})
report={'same_case_comparison':comparisons,'memory_assertions':checks,'memory_passed':sum(x['passed'] for x in checks),'memory_total':len(checks),'limitations':'Post-run independent deterministic state audit; no language rating, no database booking claims. Expected values derived from explicit fixture user turns.'}
(root/'STATE_AND_COMPARISON.json').write_text(json.dumps(report,indent=2),encoding='utf-8')
print(json.dumps({k:v for k,v in report.items() if k!='memory_assertions'},indent=2))
