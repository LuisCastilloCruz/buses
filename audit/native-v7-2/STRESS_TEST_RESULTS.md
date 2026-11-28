# Conversaciones concurrentes y recuperacion

One serial inference worker; complete conversations queued concurrently. No production capacity claim.

| Clientes | Turnos | Pertinencia | p50 ms | p95 ms | p99 ms | Cola p95 ms | Tokens/s mediana |
|---:|---:|---:|---:|---:|---:|---:|---:|
|1|18|18|1115.78|6066.0|6066.0|1017.46|66.94|
|2|36|36|2222.78|2839.66|2899.81|1252.94|67.06|
|3|54|54|3505.14|4116.77|4211.57|2363.8|65.65|

RAM libre minima muestreada: 2900 MiB. GPU maxima: 1719 MiB. CPU maxima muestreada: 33.24%.

108 inferencias y108 acuses, seis conversaciones completas de18turnos. Perdida simulada de acuse: SQLite cerrado/reabierto, resultado persistido reutilizado, sin segunda inferencia. No se simulo suspension real de Windows ni se conecto un VPS. Latencia incluye cola y coordinacion por rondas; no es tiempo hasta primer token. Muestreo por ronda, no captura todos los picos. No se certifica capacidad ilimitada ni inferencia GPU paralela.

Las respuestas visibles se construyen con hechos ficticios controlados y politica PHP real; esta suite no escribe reservas. La persistencia de citas se verifica separadamente en los E2E nativos.
