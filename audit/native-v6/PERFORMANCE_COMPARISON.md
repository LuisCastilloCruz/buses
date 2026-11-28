# Comparacion HTTP V6

Misma carga de lectura autenticada (catalogo, calendario y disponibilidad), niveles 1/5/10 durante 20 segundos por nivel, pausa de 250 ms y timeout 15 s. Objetivo previo p95 <= 2000 ms, cero errores. No representa carga mixta completa ni capacidad productiva.

| Entorno | Usuarios | Requests | RPS | p50 ms | p95 ms | p99 ms | Errores | SLO |
|---|---:|---:|---:|---:|---:|---:|---:|---|
| single | 1 | 25 | 1.22 | 558.56 | 620.43 | 626.32 | 0 | True |
| single | 5 | 32 | 1.41 | 2497.77 | 5906.17 | 6432.28 | 0 | False |
| single | 10 | 51 | 2.08 | 4495.46 | 4611.67 | 4644.19 | 0 | False |
| pool4 | 1 | 24 | 1.19 | 559.11 | 696.25 | 705.88 | 0 | True |
| pool4 | 5 | 110 | 5.3 | 656.95 | 735.06 | 954.52 | 0 | True |
| pool4 | 10 | 119 | 5.51 | 1454.91 | 1939.66 | 2026.6 | 0 | True |
| pool4 | 20 | 119 | 4.98 | 3575.86 | 3915.72 | 3951.1 | 0 | False |
| pool4-opcache | 1 | 31 | 1.54 | 364.58 | 589.34 | 599.44 | 0 | True |
| pool4-opcache | 5 | 147 | 7.14 | 424.78 | 497.34 | 527.04 | 0 | True |
| pool4-opcache | 10 | 179 | 8.48 | 788.65 | 1203.4 | 1290.45 | 0 | True |
| pool4-opcache | 20 | 191 | 8.55 | 1959.51 | 2272.03 | 2326.68 | 0 | False |
| pool8-opcache | 20 | 259 | 11.87 | 1331.67 | 1634.12 | 1739.94 | 0 | True |

Causa inicial: servidor PHP monoproceso. Ciclo 1: nginx con cuatro PHP CGI pasa 1/5/10, falla 20. Ciclo 2: OPcache privado mejora 20 pero p95 2272 ms sigue fuera de objetivo. Ciclo 3: ocho workers con OPcache pasa 20 (p95 1634 ms). Mismo codigo de negocio, datos y autorizacion; no se redujeron carga ni objetivos.

Se repitieron 42 comprobaciones HTTP de permisos/escrituras sobre el pool optimizado, todas aprobadas. Maximo verificado: 20 clientes HTTP concurrentes compartiendo una cuenta ficticia, no 20 identidades ni sesiones independientes. Solo lectura en el ensayo de rendimiento. Pico CPU del ultimo nivel: 92.51%, no sostenido; por falta de margen no se intenta 50 HTTP. No representa escalabilidad productiva.
