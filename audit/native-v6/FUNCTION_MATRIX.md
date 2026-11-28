# Matriz V6

| Capacidad | Estado comprobado | Limite |
|---|---|---|
| MultiFactura nativo, menus, login | HTTP y Chrome V6 ejecutados | Regresion fiscal integral pendiente |
| Alta cuarta empresa | HTTP original, base separada y admin | Solo fixture local |
| Agenda/confirmacion/cancelacion/reprogramacion | MySQL, HTTP, Chrome | Matriz ampliada de carreras pendiente |
| Idempotencia y recurso compartido | Dos workers reales; mismo profesional hasta 25 | 50 condicionado a RAM |
| OpenAI Responses y contexto | Contratos y flujos simulados reproducidos | Sin proveedor real |
| Meta HMAC/WABA/canal/outbox | Gateway y MySQL simulados, dedup y recibos | Sin Meta real |
| Audio OGG/Opus | Decoder FFmpeg real y transporte falso | Sin audio WhatsApp real; aislamiento OS del decoder no certificado |
| Recordatorios | Politicas, workers, obsolescencia V5 reproducidos | Sin envio real |
| Planes/suspension | 11 HTTP y guardas de negocio | Sin cobros reales |
| Clinica/adjuntos | Persistencia ficticia y acceso probado | No aptitud clinica/legal |
| Seguridad token | 19 unitarias + 12 Laravel/MySQL | Avisos restantes abiertos |
| Concurrencia gateway | Fix persistido, 15 pruebas previas | Regresion CI nueva pendiente |
| Carga | Pool 1/5/10 usuarios cumple SLO | Ensayo breve de lectura local |

Actualizacion: gateway 17 pruebas aprobadas; 650 eventos sinteticos con dedup y recuperacion. Dashboard completo 9 checks adicionales. Resistencia 10 minutos y restauracion de cinco bases verificadas. 50 workers bloqueados por margen de RAM, no ejecutados.

Cierre: 27 pruebas combinadas gateway/decoder PASS; 13 checks de guard Laravel/MySQL; siete carreras adicionales PASS; segunda resistencia 10 minutos PASS; pool8+OPcache cumple 20 clientes HTTP en ensayo breve y 42 regresiones HTTP. Sigue sin aptitud productiva, clinica ni sandbox externo.
