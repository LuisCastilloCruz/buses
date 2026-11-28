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

Causa de degradacion: servidor PHP monoproceso. Cambio local: nginx loopback 8888 con cuatro PHP CGI 19061-19064, mismo codigo, bases y autorizacion. No se redujeron carga ni objetivos. Pool cumple hasta 10 usuarios en este ensayo breve. 20/50 HTTP no ejecutados.
