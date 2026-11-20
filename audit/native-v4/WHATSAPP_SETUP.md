# WhatsApp Business Cloud API

Fuentes: https://developers.facebook.com/documentation/business-messaging/whatsapp/messages/send-messages , https://developers.facebook.com/documentation/business-messaging/whatsapp/webhooks/overview . Cuenta/numeros sandbox autorizados no disponibles en esta ejecucion; ninguna entrega real aprobada.

Implementado: canales privados SYSTEM ligados a website, WABA y Phone Number ID; credenciales cifradas; propiedad exclusiva respecto de TattooInbox; GET challenge y POST `/api/aireception/meta/{id}` con HMAC SHA256 obligatorio. El tenant viene del canal registrado, no del remitente. Eventos deduplicados y jobs MySQL; mensajes tenant cifrados. Identidad BSUID/telefono indexada mediante HMAC. Remitente sin vinculo queda awaiting_identity; audio manual_audio. Recibos de entrega conservan eventos sin sobrescribir estados por orden de llegada.

El adaptador de salida valida consentimiento, ventana de texto y plantilla aprobada, pero no existe outbox externo completo ni envio desde la bandeja. Las respuestas manuales y recordatorios son simulados. No configurar doble webhook con TattooInbox.

Antes de prueba real: resolver vulnerabilidades, disponer app Meta/WABA/numero de sandbox, permisos y token limitado; configurar secretos en formulario privado; establecer endpoint HTTPS aislado revisado y su verificacion; validar HMAC, allowlist y propiedad del canal; completar outbox/cola de salida, reconciliacion de fallos y estados, consentimiento y plantillas. Solo entonces probar con destinatarios autorizados. No exponer el PHP built-in de esta HP mediante tunel.

Fuera de ventana permitida usar plantilla aprobada con autorizacion aplicable. No asumir que guardar una plantilla o un token certifica aprobacion/conexion. Credenciales historicas no se revocaron.
