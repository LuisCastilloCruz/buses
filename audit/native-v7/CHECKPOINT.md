# Checkpoint V7 inicial

Base V6: a88b7555f81710b3703edc90405af59c206d31c3. Rama V7: feature/multifactura-ollama-universal-v7.

Implementado, todavía bajo validación:
- Proveedor Ollama loopback, límites, JSON Schema, descarte de thinking y texto no verificado.
- Gateway autenticado existente: permiso por canal/tenant, idempotencia cifrada, cuota y lease global de inferencia.
- Integración opt-in en ChannelReception y ConversationCoordinator; usa ReceptionTools y BookingService existentes.
- Perfil administrativo cifrado y pantalla original de configuración. No habilitación automática.

Ejecutado:
- 100 conversaciones reales Qwen3 1.7B, 300 turnos, diez giros. 74 completadas antes de corte por RAM; reanudación sin duplicar completó 100.
- Primer ensayo de herramientas: 0/30; argumentos inesperados y texto libre. Evidencia original conservada.
- Corrección JSON Schema, mismos escenarios: 30/30. No certifica todo el motor de reservas.
- Pruebas Node del proveedor/gateway y regresión previa, resultados guardados.

En curso:
- Instalación V7 nueva: MySQL 13323, HTTP 8879, bases mf_v7_*, credenciales nuevas privadas. No se copió .env.
- No reutilizar puertos ni bases V6. Verificar logs y marcadores antes de reanudar instalador.

Pendiente: integración MySQL/HTTP/Chrome, memoria estructurada, RAG versionado, worker remoto, evaluación ampliada y revisión humana, concurrencia real de inferencia, comparación 4B condicionada a RAM, auditoría final y publicación.

No aprobado para producción. Persisten bloqueos de dependencias V6 y fallos lingüísticos del texto libre. La evaluación lingüística no escribió reservas ni envió mensajes.
