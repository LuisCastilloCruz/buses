from pathlib import Path
import json,csv
root=Path(__file__).resolve().parents[1];out=root/'audit/native-v6';rows=[]
for mode,name in [('single','http-load-20261127-234252.json'),('pool4','http-load-20261127-234548.json')]:
 r=json.loads((root/'.native-runtime'/name).read_text())
 for v in r['levels']:
  rows.append(dict(mode=mode,users=v['users'],requests=v['requests'],rps=v['rps'],p50_ms=v['p50_ms'],p95_ms=v['p95_ms'],p99_ms=v['p99_ms'],errors=v['errors'],slo_pass=v['slo_pass']))
with (out/'PERFORMANCE_METRICS.csv').open('w',newline='') as f:
 w=csv.DictWriter(f,fieldnames=rows[0].keys());w.writeheader();w.writerows(rows)
md='# Comparacion HTTP V6\n\nMisma carga de lectura autenticada (catalogo, calendario y disponibilidad), niveles 1/5/10 durante 20 segundos por nivel, pausa de 250 ms y timeout 15 s. Objetivo previo p95 <= 2000 ms, cero errores. No representa carga mixta completa ni capacidad productiva.\n\n| Entorno | Usuarios | Requests | RPS | p50 ms | p95 ms | p99 ms | Errores | SLO |\n|---|---:|---:|---:|---:|---:|---:|---:|---|\n'
for r in rows:md+='| '+' | '.join(str(round(v,2)) if isinstance(v,float) else str(v) for v in r.values())+' |\n'
md+='\nCausa de degradacion: servidor PHP monoproceso. Cambio local: nginx loopback 8888 con cuatro PHP CGI 19061-19064, mismo codigo, bases y autorizacion. No se redujeron carga ni objetivos. Pool cumple hasta 10 usuarios en este ensayo breve. 20/50 HTTP no ejecutados.\n'
(out/'PERFORMANCE_COMPARISON.md').write_text(md,encoding='utf-8')
print('Published sanitized CSV and comparison; raw evidence remains private.')
