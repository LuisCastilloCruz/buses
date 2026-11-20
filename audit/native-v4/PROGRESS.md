# V4: trabajo en curso, no apto para produccion

Base: `4d11628ee6d3d52f371af5073c155e9d5cc785ef`. Rama `feature/multifactura-aireception-odontologia-completo-v4`.
Worktree independiente `C:/Users/luis castillo/AiReception-lab/native-v4`.
MySQL 13319, HTTP 8876, credenciales nuevas privadas en `.native-runtime`. V3 13318/8875 intacto. No se copio .env.
Respaldo de base: bundles V3 completo e incremental verificados antes del worktree; originales fuera del proyecto en MultiFactura-private-backups/native-v3-20261120-final.

## Implementado en el primer bloque
- Calendario mensual nativo y estado atendido por motor transaccional compartido.
- Transportes oficiales Responses API y WhatsApp Cloud API, sin rutas conectadas ni credenciales reales. Propuestas de herramientas no ejecutadas automaticamente. Preservar `unverified_text`: no presentarlo como confirmacion de reserva.
- Libro de reservas y liquidaciones de presupuesto con bloqueo MySQL, idempotencia y comprobacion de contexto. Todavia NO enlazado al transporte ni al Superadministrador.
- Historia, antecedentes, alergias, evolucion, planes, constancias de consentimiento y odontograma FDI cifrados, permiso clinico independiente, versiones optimistas, auditoria de lectura y cambios. Formulario nativo inicial; adjuntos, exportacion, retencion y editor grafico aun pendientes. No equivale a historia clinica validada legalmente.
- Reserva CLI de una cuarta base vacia y usuario limitado; adaptador Hyn solo laboratorio sin privilegios globales para la aplicacion.

## Resultados ejecutados
- 17 assertions de contratos OpenAI/Meta con transporte falso, sin red: PASS.
- 19 assertions nuevas Laravel/MySQL: cifrado, auditoria, versionado, permisos, diente invalido, presupuesto, aislamiento, mes y atendida: PASS.
- Regresion: 7 pruebas PHPUnit / 27 assertions, 24 comprobaciones MySQL y 6 compatibilidad heredada: PASS.
- HTTP exploratorio original: dashboard/documentos/POS/clientes de tres empresas 200; modulos no autorizados 403.
- Primera corrida HTTP completa: TIMEOUT al coincidir con instalacion de empresa en servidor PHP monohilo. Pendiente repeticion secuencial; no aprobada.
- Alta cuarta: interrumpida por limite PHP de 60 segundos. 292 migraciones registradas, cero filas empresa/website. Una ALTER TABLE terminada sin marcador se verifico contra su migracion exacta (quotations.terms_condition varchar(255) nullable) y se recupero solo el marcador local. No se editaron migraciones historicas ni se borro la base. Reanudacion en curso con tiempo local ampliado.

## Fuentes oficiales consultadas 2026-11-20
- https://developers.openai.com/api/reference/resources/responses/methods/create
- https://developers.openai.com/api/docs/guides/function-calling
- https://developers.facebook.com/documentation/business-messaging/whatsapp/messages/send-messages
- https://www.gob.pe/institucion/anpd/normas-legales/6554453-n-016-2024-jus
La norma requiere analisis de aplicabilidad y validacion legal; esta revision tecnica no es certificacion.

## Pendiente
Integracion conversacional autorizada, webhooks/colas, presupuestos conectados a proveedores, consumo/suscripciones UI, transcripcion, recordatorios reales, capacidades universales, controles clinicos completos, pruebas navegador V4, alta cuarta terminada, CI/publicacion y documentacion final. Proveedores reales, SUNAT y produccion permanecen desconectados.


## Segundo bloque: resultado verificado
- Alta cuarta empresa completada por Superadministrador original: 6 comprobaciones HTTP. Usuario MySQL limitado a `mf_v4_cuarta`; sin privilegios globales para la aplicacion. Se agrego compatibilidad del nombre de clase de la migracion historica de catalogo TattooInbox sin editar ese archivo ni sus marcadores existentes.
- Regresion secuencial completa previa al segundo bloque: 42 HTTP, 15 Chrome y 4 Chrome configuracion, sin errores JavaScript.
- 6 comprobaciones Chrome adicionales: mes, persistencia clinica, cuarto dashboard y menus; capturas reales 16-19 (incluye movil).
- 8 comprobaciones MySQL de propuestas: confirmacion explicita, actor vinculado, no reserva anticipada, idempotencia, caducidad y revalidacion de disponibilidad.
- 11 comprobaciones HTTP de planes/suscripciones: disponibilidad independiente, suspension, reactivacion y facturacion original conservada. Sin cobros. Precio y limites editables sobre planes existentes. Limite de profesionales aplicado en altas nativas/dentales; cuotas de mensajes/unidades aun no vinculadas a proveedores.
- Registro extensible de adaptadores DomainCalendar sin enumerar giros en el motor. Solo informa dominios realmente vinculados a profesionales del tenant.
- Recordatorios 24h/2h compartidos entre reservas manuales y propuestas; procesamiento SIMULADO con consentimiento, auditoria, cola MySQL real y jobs Laravel. Tres checks de preparacion y verificacion del worker: dos jobs duplicados producen un solo intento, sin envio externo.
- 6 comprobaciones de adjuntos clinicos: archivo cifrado fuera de public, metadatos en MySQL, descarga autorizada auditada, rol no clinico y MIME falso rechazados.
- 7 comprobaciones de audio con transporte falso: WAV PCM canonico, limite de tamano y duracion calculada desde bytes. No hay ffprobe/ffmpeg validado: OGG/Opus de WhatsApp se rechaza, no se presume duracion. Adaptador de transcripcion implementado pero aun no conectado a conversaciones/costos ni probado con API real.
- Verificacion de TattooInbox endurecida: tokens vacios rechazados, hash_equals y errores publicos sin detalles sensibles.
- Pendiente unir proveedores, canal, identidad del paciente, contexto conversacional y presupuesto en un flujo unico; el flujo externo completo NO esta aprobado. Scheduler automatico, reintentos de proveedor, cuotas de mensajes, consumo y herramientas conversacionales completas siguen pendientes.

Fuente adicional de audio: https://developers.openai.com/api/docs/guides/speech-to-text (consulta 2026-11-20). Modelo requerido por configuracion, sin seleccionar un modelo antiguo por defecto.


## Recepcion de eventos, bloque posterior
Se implementaron canales privados por empresa, propiedad exclusiva del canal frente a TattooInbox, endpoint HMAC obligatorio, validacion WABA/Phone Number ID, deduplicacion y cola MySQL. Seis pruebas HTTP firmadas ficticias pasaron y tres jobs ProcessInbound se ejecutaron correctamente. Los mensajes quedan cifrados y en `awaiting_identity` (audio: `manual_audio`); NO se crean pacientes ni reservas desde un remitente no vinculado. Sender identity se indexa mediante HMAC, no telefono plano. El parser admite BSUID sin telefono y IDs wamid con padding; 12 comprobaciones de parser/firma.
Se corrigio solo para APIs nuevas el Handler heredado que devolvia 500 ante abort(403) y revelaba archivo/linea. Pendientes: recibos de entrega, orden conversacional, vinculacion verificada de identidad, interfaz completa de canal y flujo de respuesta/proveedores. No equivale a conexion WhatsApp validada.

## Tercer bloque
Conversaciones cifradas y coordinador idempotente; bandeja/canales/configuracion/consumo nativos; propuestas de cancelacion/reprogramacion con confirmacion; presupuestos conectados a Responses con transporte mock, conteo previo y tarifas editables; recibos Meta auditados; vinculacion de identidad verificada; offsets/pausa/ventana de recordatorios y tick dedicado. Ver INFORME_V4.md y FUNCTION_MATRIX.md para estado actual que sustituye pendientes historicos de este diario. Auditoria Composer detecto avisos criticos: produccion bloqueada.
