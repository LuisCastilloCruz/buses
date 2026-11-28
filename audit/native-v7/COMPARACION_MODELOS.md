# Comparación de modelos

La comparación 1.7B frente a 4B está **pendiente**, no empatada ni aprobada.

| Medida | Qwen3 1.7B | Qwen3 4B |
|---|---:|---|
| Conversaciones iniciales | 100 / 300 turnos | No ejecutado |
| Tokens/s mediana de generación | 72,52 | No medido |
| Latencia p50/p95/p99 inicial | 546 / 983 / 2442 ms | No medido |
| Primera llamada en frío | 11027 ms total; 9887 ms carga | No medido |
| Offload observado | 100 % GPU | No medido |
| VRAM observada tras carga | 1715 MiB | No medido |
| Coherencia comercial aprobada | No | No evaluado |

No hay medición de tiempo al primer token en el ensayo inicial no streaming. El benchmark streaming preparado se bloqueó por RAM. Las cifras no prueban capacidad multiusuario ni tiempos de conversaciones largas.

## Actualizacion tras liberar memoria

Regresion final UTF-8: 30/30 herramientas con Ollama real aprobadas. Pipeline Laravel-gateway-HTTP-Ollama-MySQL: 8 comprobaciones por tenant (Tattoo y dental), incluida continuidad al cambiar fecha. Un fallo real anterior eligio 08:00 sin seleccion del cliente; el backend lo rechazo. Se restringio prepare_booking hasta disponer de hora explicita y se repitieron ambos casos, aprobados. No equivale a aprobar la rubrica linguistica global ni reservas completas por IA.

Chrome dividido por negocio: Tattoo, dental, comercio, sistema y V7 aprobados. Inferencia breve con niveles 1/2/3: seis respuestas, sin errores; primera 5202 ms, calientes 170-491 ms, 80.7-83.6 tokens/s. Sin prueba sostenida ni comparacion 4B.
