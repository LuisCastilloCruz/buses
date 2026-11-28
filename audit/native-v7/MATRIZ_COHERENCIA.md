# Coherencia: no aprobada todavía

Muestra inicial: 10 conversaciones por giro, 3 turnos por conversación, 100 conversaciones/300 turnos. Ocho escenarios por giro se marcaron desarrollo y dos reservados. No cubre aún los 23 escenarios completos ni conversaciones prolongadas.

Las aserciones independientes de `COHERENCE_ASSERTIONS.json` encontraron:

| Comprobación | Resultado |
|---|---:|
| Sin importe numérico desconocido detectado | 100/100 conversaciones |
| Sin hora numérica desconocida detectada | 100/100 |
| Precio conocido contestado | 10/10 |
| Servicio conservado en casos evaluados | 19/20 |
| Profesional conservado | 9/10 |
| Cambio de fecha conservado | 6/10 |

Los detectores numéricos no certifican todas las afirmaciones en lenguaje natural. La continuidad de fecha quedó muy por debajo del objetivo. También se detectaron insinuaciones de reserva y respuestas a extracción de instrucciones.

La corrección evita usar texto libre del modelo como confirmación: JSON Schema propone herramientas y el backend produce hechos autorizados. Se añadió memoria explícita y resumen de preferencias. Los ensayos de herramientas pasaron 30/30 tras cambiar el protocolo, pero la regresión lingüística final quedó bloqueada por RAM después de corregir codificación y memoria.

La rúbrica humana está en HUMAN_REVIEW.csv: intención, continuidad, claridad, tono, alucinaciones, confirmaciones indebidas y consejo clínico. No se han rellenado puntuaciones subjetivas ficticias. El umbral global de 95 %/90 % no se declara alcanzado.

## Actualizacion tras liberar memoria

Regresion final UTF-8: 30/30 herramientas con Ollama real aprobadas. Pipeline Laravel-gateway-HTTP-Ollama-MySQL: 8 comprobaciones por tenant (Tattoo y dental), incluida continuidad al cambiar fecha. Un fallo real anterior eligio 08:00 sin seleccion del cliente; el backend lo rechazo. Se restringio prepare_booking hasta disponer de hora explicita y se repitieron ambos casos, aprobados. No equivale a aprobar la rubrica linguistica global ni reservas completas por IA.

Chrome dividido por negocio: Tattoo, dental, comercio, sistema y V7 aprobados. Inferencia breve con niveles 1/2/3: seis respuestas, sin errores; primera 5202 ms, calientes 170-491 ms, 80.7-83.6 tokens/s. Sin prueba sostenida ni comparacion 4B.
