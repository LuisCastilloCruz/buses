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
