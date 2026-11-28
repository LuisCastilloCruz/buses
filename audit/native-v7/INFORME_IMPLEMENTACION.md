# MultiFactura V7 — laboratorio en validación

Base preservada: V6 `a88b7555f81710b3703edc90405af59c206d31c3`. Worktree independiente `C:\Users\luis castillo\AiReception-lab\native-v7`. Rama `feature/multifactura-ollama-universal-v7`. Primer hito: `b80199d513e194710352cbf1eb98a5bd60c8c7c2`.

**No es una versión aprobada para producción ni una aprobación de coherencia comercial.** No se conectaron OpenAI, Meta ni un VPS. No se modificaron V6, la instalación original ni bases productivas.

## Implementado

- Proveedor Ollama mediante `/api/chat` local, con modelo permitido, JSON Schema, validación de argumentos, contexto acotado, temperatura, timeout y consumo. Descarta thinking y texto libre no verificado.
- Reutilización del gateway firmado, separación tenant/canal, respuestas cifradas e idempotencia. Lease persistente limita la inferencia concurrente. Los estados inciertos requieren conciliación.
- Integración opt-in con ChannelReception y ConversationCoordinator. ReceptionTools y BookingService conservan autorización, disponibilidad, transacciones y confirmación explícita.
- Puente Node de destino fijo para llegar al gateway sin reactivar las funciones de red externas bloqueadas en PHP.
- Personalidad administrativa cifrada y editable en el layout original. FAQ y políticas se indexan como fuentes aprobadas.
- Recuperación documental léxica por tenant: fuentes, versiones, índices con HMAC, fragmentos relevantes, borrado del contenido de todas las versiones y auditoría. No es búsqueda vectorial ni ingesta de documentos clínicos.
- Memoria cifrada de preferencias y selección, con caducidad de 24 horas. Cambiar fecha conserva servicio/profesional y descarta el horario anterior. El resumen del backend distingue una preferencia de una reserva.
- Cuota local separada del dinero: solicitudes y tokens, usando el límite del plan; no se inventa una tarifa USD de Ollama.
- Cola y worker privado saliente preparados: HTTPS, HMAC, roles, leases, resultados durables, reanudación del acuse sin repetir inferencia. El enlace VPS no fue conectado.

## Evidencia principal

100 conversaciones ficticias reales con Qwen3 1.7B, 300 turnos y diez giros. El primer protocolo de herramientas falló 30/30; JSON Schema pasó los mismos 30 casos en la iteración siguiente. La evaluación de texto libre reveló defectos de continuidad y falsas implicaciones de reserva. Las correcciones posteriores de codificación y memoria aún requieren repetir la evaluación real con suficiente RAM.

Consultar TEST_RESULTS.md, MATRIZ_COHERENCIA.md, ERRORES_CORREGIDOS.md y PENDIENTES.md. Los resultados de V6 no se cuentan como pruebas ejecutadas en V7.

## Actualizacion tras liberar memoria

Regresion final UTF-8: 30/30 herramientas con Ollama real aprobadas. Pipeline Laravel-gateway-HTTP-Ollama-MySQL: 8 comprobaciones por tenant (Tattoo y dental), incluida continuidad al cambiar fecha. Un fallo real anterior eligio 08:00 sin seleccion del cliente; el backend lo rechazo. Se restringio prepare_booking hasta disponer de hora explicita y se repitieron ambos casos, aprobados. No equivale a aprobar la rubrica linguistica global ni reservas completas por IA.

Chrome dividido por negocio: Tattoo, dental, comercio, sistema y V7 aprobados. Inferencia breve con niveles 1/2/3: seis respuestas, sin errores; primera 5202 ms, calientes 170-491 ms, 80.7-83.6 tokens/s. Sin prueba sostenida ni comparacion 4B.
