# Sandbox: desactivado, no aprobado externamente

No se suministraron credenciales/endpoints de sandbox autorizados. No se buscaron secretos productivos ni se reutilizaron. No existe t?nel o webhook p?blico, y Laravel 5.7 contin?a solo en loopback. Las pruebas de proveedor son falsas y no generan cargos.

## Gateway interno

`gateway/server.mjs` requiere Node de rama soportada con node:sqlite (probado con C:/Program Files/nodejs/node.exe 26.7.0). Es infraestructura interna sin panel. Siempre escucha 127.0.0.1; el puerto privado por defecto es 8890. No sirve archivos ni enruta HTTP al monolito. Antes de cualquier uso externo, validar el parche del runtime y desplegar un ingress aislado con TLS, controles de red, cuotas y revisi?n de seguridad. No cambiar el bind ni publicar Laravel como atajo.

AR_GATEWAY_CONFIG apunta a un JSON privado, fuera de Git, que contiene:

| Campo | Contenido requerido |
|---|---|
| database | Ruta absoluta privada de SQLite |
| encryptionKey | 32 bytes aleatorios en base64, propios del sandbox |
| keys | cliente interno ? secret de al menos 32 caracteres y lista de tenants num?ricos |
| live | false por defecto; true solo tras autorizaci?n |
| channels | ID interno ? tenant, receiver=aireception, phone, waba, appSecret, verifyToken |
| sandboxAuthorized | autorizaci?n expl?cita por canal |
| models / openaiKey | modelos permitidos y clave de proyecto sandbox |
| version / metaToken | versi?n Graph verificada y token de n?mero de prueba |
| recipients | lista cerrada de tel?fonos de prueba autorizados |
| templates | nombres:idioma aprobados para ese canal |
| monthlyCapUnits / maxRequestUnits | techo operativo independiente del gateway; no sustituye el presupuesto monetario PHP |
| mediaIds | IDs de medios autorizados del canal, nunca URL suministrada por cliente |
| ffmpeg / ffprobe | rutas absolutas de binarios parcheados y verificados, ejecutados con cuenta aislada |
| transcriptionModel / maxAudioSeconds | modelo autorizado y duraci?n m?xima 1?120 segundos |

Los par?metros por canal pertenecen al objeto channels[ID]. Las claves solo existen en configuraci?n privada; GatewayTransport no transmite la clave OpenAI almacenada en V4. Mantener tambi?n las credenciales de canal requeridas por el validador V4 dentro de su almacenamiento cifrado, con el mismo appSecret/WABA de sandbox.

## Autorizaci?n local caducable

Solo despu?s de aprobaci?n del entorno y cuentas: crear de forma privada `.native-runtime/sandbox-authorization.json` con purpose=`multifactura-v5-authorized-sandbox`, authorized=true, expires_at como Unix timestamp futuro de hasta 7 d?as y objetos gateway (client/secret/port), gateway_channels (empresa?canal), channel_actors (empresa?canal?usuario), audio y reminder_templates. Esta entrega NO crea ese archivo. El bootstrap solo lo aplica a CLI; el servidor web no recibe permiso de salida.

El agente del tenant debe estar habilitado en modo sandbox, tener modelo permitido y una tarifa vigente en aireception_model_rates. La credencial real del proyecto est? en el gateway; el campo heredado debe contener un marcador privado de integraci?n de al menos 16 caracteres para satisfacer el validador V4. La tarifa usa micro-USD por mill?n de tokens; el plan limita el presupuesto entero por empresa. Para audio configurar enabled, max_units, units_per_second y valid_until; max_units debe cubrir 120 segundos completos. No reutilizar una tarifa vencida ni activar un modelo solo por estar en el selector.

Comandos despu?s de autorizaci?n:

```powershell
# Gateway: AR_GATEWAY_CONFIG contiene ?nicamente la ruta del archivo privado.
& 'C:/Program Files/nodejs/node.exe' gateway/server.mjs
php native-lab/artisan.php aireception:gateway-pull EMPRESA CANAL
php native-lab/artisan.php queue:work aireception --queue=aireception --once --tries=3 --sleep=0
php native-lab/artisan.php aireception:transport EMPRESA --sandbox
```

El puente extrae eventos cifrados mediante HMAC y los entrega al WebhookController V4 existente, que vuelve a verificar firma, canal y WABA. Acknowledgement solo despu?s de persistir; un corte provoca redelivery deduplicado. No configurar el mismo n?mero en TattooInbox y AiReception.

## Pendientes externos y pruebas necesarias

- Cuenta/proyecto OpenAI sandbox, modelo y tarifas vigentes, l?mite duro del proyecto y presupuesto peque?o autorizado.
- N?mero Meta de prueba, WABA, token, app secret, destinatarios y plantillas aprobados.
- Endpoint externo de ingress aislado y autorizado; TLS/mTLS o controles equivalentes revisados. Actualmente no hay ninguno.
- ffmpeg/ffprobe no disponibles/validados en esta instalaci?n. El pipeline se prob? con decoder y proveedores falsos; no se certifica OGG real.
- Probar mensajes reales entrantes/salientes, recibos, plantilla fuera de ventana y audio ficticio. Guardar IDs de proveedor y consumo; solo entonces marcar esa columna aprobada.

No hay autorizaci?n para producci?n, pacientes reales, cobros, revocaciones ni actualizaci?n global de Laravel/PHP. Ante fallo externo, conservar estados unknown/reservas de gasto y derivar a recepci?n humana.
