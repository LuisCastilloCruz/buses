# Matriz de alcance V4

| Funcion | Estado verificable | Limite pendiente |
|---|---|---|
| MultiFactura nativo, login/sidebar/modulos | Integrado y probado HTTP/Chrome | Regresion exhaustiva de cada modulo historico no ejecutada |
| Cuarta empresa por Superadmin | Alta HTTP y acceso Chrome comprobados | Reserva local especifica de DB; aprovisionamiento productivo no validado |
| Activacion, niveles, planes y suspension | Pruebas HTTP y permiso backend | Todos los limites comerciales y tiers editables completos pendientes |
| Agenda dia/semana/mes, recursos, concurrencia | MySQL y Chrome | Zona horaria nativa actualmente America/Lima; no editor de zona por tenant |
| Propuestas crear/cancelar/reprogramar | MySQL; confirmacion explicita y version | Flujo completo WhatsApp externo pendiente |
| Tattoo legado | Adaptador y pruebas regresion | Incorporacion de artistas reales requiere revision de enlaces y duraciones |
| Dental administrativo | Integrado con persons/items y agenda unica | Seguimientos administrativos avanzados y vinculo contable cita-ingreso pendientes |
| Historia/odontograma/consentimiento/adjuntos | Cifrados, permisos y tests ficticios | Editor grafico avanzado, retencion/exportacion/eliminacion legal, validacion profesional pendientes |
| Agente conversacional | Simulacion y herramientas autorizadas | No validacion con modelo real; no garantia de comprension multi-turno con proveedor |
| OpenAI Responses/presupuesto | Contratos y MySQL con mocks | Credenciales/modelo/tarifas/sandbox real y limites de todos los consumos pendientes |
| Audio | Adaptador WAV validado con mocks | OGG/Opus, descarga Meta, cola de audio y presupuesto integrado pendientes |
| WhatsApp entrada | HMAC HTTP sintetico, dedup, cola, identidad | Cuenta sandbox y webhook publico seguro pendientes |
| WhatsApp salida | Adaptador oficial probado con mocks | Outbox, entrega real, reconciliacion, respuesta humana real pendientes |
| Recordatorios | Politica, consentimiento, scheduler dedicado y cola simulada | Plantillas/consentimientos reales, envio/reintentos externos y cuotas pendientes |
| Consumo y planes | Libro de presupuesto y paneles nativos | Limites de conversaciones/audio/almacenamiento/sedes y facturacion addon completa pendientes |
| Seguridad | Contencion local y aislamiento probado | Avisos criticos de dependencias sin mitigar bloquean produccion |
| GitHub | Rama V4 publicada por etapas | Ver DELIVERY_STATUS para commit y CI exactos |

No se activan capacidades Hotel/Restaurante sin adaptadores comprobados. No hay diagnostico, prescripcion ni escritura SQL desde IA. No se ejecutan pagos reales ni emision SUNAT automatica.
