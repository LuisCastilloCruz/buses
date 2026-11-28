# Pendientes y bloqueos

## Por recursos/revisión
- Repetir con Ollama real la corrección final de memoria y codificación; ampliar a los 23 escenarios, conversaciones largas, interrupción/reanudación y evaluación reservada amplia.
- Cerrar E2E real modelo → herramientas → confirmación explícita → MySQL en Tattoo y Odontología.
- Inferencia 1/2/3/5, TTFT, series CPU/GPU, resistencia y comparación thinking; Qwen3 4B solo con margen suficiente.
- Revisión humana independiente de naturalidad y coherencia. No sustituirla por autoevaluación del mismo modelo.

## Funcionalidades parciales
- Personalización completa del texto visible; actualmente se privilegian respuestas autorizadas del backend.
- Ingesta documental avanzada y búsqueda vectorial; hoy hay texto administrativo e índice léxico.
- Purga periódica de memoria y conversaciones integrada con una política operativa de retención; la memoria caduca y tiene borrado explícito.
- Cola VPS: cuotas comerciales, retención, multi-HP y despliegue TLS probado.
- Adaptadores de reservas de otros giros: no implementados ni fingidos.

## Seguridad heredada/externalidades
- 117 avisos Composer, 23 paquetes afectados, 15 abandonados.
- Sandbox real OpenAI/Meta, uso clínico legal/profesional y despliegue: no validados ni autorizados en esta entrega.

Los resultados anteriores no equivalen a aprobación de producción. Consultar los logs y checkpoints antes de reanudar pruebas que escriben datos.

## Actualizacion tras liberar memoria

Regresion final UTF-8: 30/30 herramientas con Ollama real aprobadas. Pipeline Laravel-gateway-HTTP-Ollama-MySQL: 8 comprobaciones por tenant (Tattoo y dental), incluida continuidad al cambiar fecha. Un fallo real anterior eligio 08:00 sin seleccion del cliente; el backend lo rechazo. Se restringio prepare_booking hasta disponer de hora explicita y se repitieron ambos casos, aprobados. No equivale a aprobar la rubrica linguistica global ni reservas completas por IA.

Chrome dividido por negocio: Tattoo, dental, comercio, sistema y V7 aprobados. Inferencia breve con niveles 1/2/3: seis respuestas, sin errores; primera 5202 ms, calientes 170-491 ms, 80.7-83.6 tokens/s. Sin prueba sostenida ni comparacion 4B.
