# Matriz V5

| Funci?n | Estado verificable | L?mite / pendiente |
|---|---|---|
| Login, sidebar y m?dulos originales | Regresi?n HTTP y Chrome aprobada | No equivale a probar toda la facturaci?n hist?rica |
| Cuarta empresa desde Superadministrador | Creada por HTTP original, dashboard/POS/documentos/m?dulos verificados | Datos ficticios; sin emisi?n ni cobros |
| Comercio sin AiReception | Acceso y men?s nuevos rechazados | Mantiene funciones originales |
| Ingreso firmado WhatsApp | HMAC, WABA, phone, dedup, cola MySQL; HTTP sint?tico aprobado | Meta real no ejecutado |
| Gateway interno | Node, HMAC antirreplay, respuesta firmada, cola cifrada, l?mites y destinos fijos | Solo loopback; no aptitud p?blica certificada |
| Conexi?n Responses API | Transporte oficial v?a gateway, modelo configurable, store=false, herramientas estrictas | Contratos simulados; calidad del modelo real pendiente |
| Contexto | M?ximo 12 mensajes de canal + cat?logo acotado; por conversaci?n verificada | Una herramienta por turno; no agente abierto de m?ltiples pasos |
| Disponibilidad/precios | Respuestas construidas desde cat?logo/backend | Sin hechos afirmados desde texto libre del proveedor |
| Reserva y cancelaci?n | E2E local por canal, confirmaci?n CONFIRMAR + c?digo, motor V4 | Sin autonom?a del modelo para confirmar |
| Reprogramaci?n | Propuestas V4 y confirmaci?n transaccional probadas | Herramienta disponible; sin prueba Meta externa |
| Pausa/derivaci?n cl?nica | Persistente; diagn?stico/prescripci?n nunca se produce con texto del modelo | Clasificador conservador m?s respuestas limitadas a herramientas administrativas |
| Outbox | Cifrada, persistente, dedup, dos workers reales, consentimiento y ventana | Simulaci?n por defecto |
| Respuesta manual | Bandeja original encola con clave estable | Entrega real requiere sandbox autorizado |
| Reintentos | Backoff y m?ximo 3 solo ante HTTP 429 de proveedor a trav?s del gateway autenticado | Timeout/resultado incierto requiere conciliaci?n, nunca reenv?o ciego |
| Recibos | accepted separado de delivered/read; fuera de orden sin regresi?n | Recibos sint?ticos; ninguno acredita entrega real |
| Plantillas | Lista aprobada por canal y nombre/idioma | Solo plantillas est?ticas sin componentes din?micos; aprobaci?n Meta no realizada |
| Recordatorios | Programaci?n V4, consentimiento, quiet hours, outbox, versi?n y estado de cita | No se despachan si cancelada/modificada antes de reclamar; un env?o ya en vuelo no puede retirarse |
| Audio | Descarga acotada, origen permitido, hash, OGG/Opus, decoder aislado, WAV, transcripci?n, limpieza, presupuesto | Decoder y proveedores simulados; ffmpeg/ffprobe reales no instalados/validados |
| Presupuesto | Reservas/uso entero por empresa, idempotencia, timeout retenido | Tarifas reales y l?mites sandbox deben configurarse y verificarse |
| Dependencias | 113 avisos / 23 paquetes / 15 abandonados auditados | Siguen abiertos; Laravel/PHP no actualizados globalmente |
| Producci?n | Intacta | Sin despliegue, merge, t?neles, pacientes reales, cobros ni revocaciones |

Identidades `bsuid:` no se convierten en tel?fonos ni se usan para responder: se requiere un destinatario telef?nico verificado. Los clientes desconocidos pasan a vinculaci?n humana. La suspensi?n de AiReception usa `users.aireception_suspended`, separada de `locked` heredado, que el alta original asigna al administrador.
