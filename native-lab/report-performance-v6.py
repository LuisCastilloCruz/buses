from pathlib import Path
import json,csv
root=Path(__file__).resolve().parents[1];out=root/'audit/native-v7';rows=[]
for mode,name in [('single','http-load-20261127-234252.json'),('pool4','http-load-20261127-234548.json'),('pool4','http-load-20261128-000221.json'),('pool4-opcache','http-load-20261128-000505.json'),('pool8-opcache','http-load-20261128-000758.json')]:
 r=json.loads((root/'.native-runtime'/name).read_text())
 for v in r['levels']:
  rows.append(dict(mode=mode,users=v['users'],requests=v['requests'],rps=v['rps'],p50_ms=v['p50_ms'],p95_ms=v['p95_ms'],p99_ms=v['p99_ms'],errors=v['errors'],slo_pass=v['slo_pass']))
with (out/'PERFORMANCE_METRICS.csv').open('w',newline='') as f:
 w=csv.DictWriter(f,fieldnames=rows[0].keys());w.writeheader();w.writerows(rows)
md='# Comparacion HTTP V6\n\nMisma carga de lectura autenticada (catalogo, calendario y disponibilidad), niveles 1/5/10 durante 20 segundos por nivel, pausa de 250 ms y timeout 15 s. Objetivo previo p95 <= 2000 ms, cero errores. No representa carga mixta completa ni capacidad productiva.\n\n| Entorno | Usuarios | Requests | RPS | p50 ms | p95 ms | p99 ms | Errores | SLO |\n|---|---:|---:|---:|---:|---:|---:|---:|---|\n'
for r in rows:md+='| '+' | '.join(str(round(v,2)) if isinstance(v,float) else str(v) for v in r.values())+' |\n'
md+='\nCausa inicial: servidor PHP monoproceso. Ciclo 1: nginx con cuatro PHP CGI pasa 1/5/10, falla 20. Ciclo 2: OPcache privado mejora 20 pero p95 2272 ms sigue fuera de objetivo. Ciclo 3: ocho workers con OPcache pasa 20 (p95 1634 ms). Mismo codigo de negocio, datos y autorizacion; no se redujeron carga ni objetivos.\n\nSe repitieron 42 comprobaciones HTTP de permisos/escrituras sobre el pool optimizado, todas aprobadas. Maximo verificado: 20 clientes HTTP concurrentes compartiendo una cuenta ficticia, no 20 identidades ni sesiones independientes. Solo lectura en el ensayo de rendimiento. Pico CPU del ultimo nivel: 92.51%, no sostenido; por falta de margen no se intenta 50 HTTP. No representa escalabilidad productiva.\n'
(out/'PERFORMANCE_COMPARISON.md').write_text(md,encoding='utf-8')
print('Published sanitized CSV and comparison; raw evidence remains private.')
