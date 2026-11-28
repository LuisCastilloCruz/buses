# Concurrencia y estres V6

Prueba MySQL real con barrera READY/GO, claves distintas y mismo profesional/horario. Verificacion final de tabla: exactamente una reserva por nivel. No son metricas HTTP.

| Workers | Reservas finales | p50 ms | p95 ms | p99 ms | Resultado |
|---:|---:|---:|---:|---:|---|
| 2 | 1 | 34.04 | 53.67 | 53.67 | PASS |
| 5 | 1 | 103.67 | 165.69 | 165.69 | PASS |
| 10 | 1 | 111.04 | 204.41 | 204.41 | PASS |
| 25 | 1 | 665.87 | 911.22 | 942.65 | PASS |

Percentiles nearest-rank recalculados desde muestras originales; evidencia original preservada. 50 procesos aun no ejecutados; requieren margen de 80 MiB por worker mas 2 GiB libres. Primer intento abortado por interferencia con test de suspension, sin escrituras no autorizadas; repeticion secuencial aprobada.

Dos workers con sillones compartidos y claves idempotentes cubiertos por suite MySQL original reproducida. Miles de eventos, picos y todas las combinaciones cancelacion/reprogramacion simultanea no ejecutadas. No declarar estres integral aprobado.

## Resistencia ejecutada
Pool cuatro workers, un usuario con pausa de un segundo, 600.768 segundos, 385 solicitudes, cero errores. p50 545.25 ms, p95 636.64 ms, p99 674.66 ms. RAM libre minima 2765.8 MiB; CPU maxima observada 53.19%. No se excedieron umbrales. Esto no acredita 30/60 minutos ni una mezcla completa de escrituras/colas.

## Gateway sintetico y recuperacion
650 intentos: 500 aceptados (250 duplicados), 150 rechazados (firma/canal), 250 filas unicas. Tiempo 1233.65 ms, latencia ingest p50 1.73 ms/p95 4.25 ms/p99 5.20 ms. SQLite real, reapertura y lease expirado, rechazo de ack ajeno/obsoleto. Proveedores externos: cero llamadas. No es rendimiento HTTP ni flujo completo de pacientes.

Suite gateway ampliada 17/17 PASS. Un worker real termina con codigo 17 despues de persistir pending: recuperacion rechaza reintento, conserva una sola llamada ficticia y una sola reserva de presupuesto de 100 unidades. No se mataron procesos ajenos.

Nivel 50 MySQL NO EJECUTADO: 3716.5 MiB libres al preflight, insuficientes para 50*80 MiB mas 2048 MiB de margen. Runner devuelve 2, no PASS. Reanudar niveles 2/5/10/25 comprobo filas existentes sin repetir reservas.

Segunda resistencia pool8+OPcache: 601.227 s, 437 requests, 0 errores, p95 454.27 ms. Telemetria MySQL y checkpoints parciales disponibles. Siete escenarios MySQL adicionales aprobados, incluida caida tras commit con recuperacion idempotente; no equivale a agotar todas las variantes posibles de deadlock o corte de red.
