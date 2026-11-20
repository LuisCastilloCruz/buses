# Integración HP — 20 noviembre 2026

La fundación de `1a28ed75031e4e874d0da619a1f3453343154152` está integrada
selectivamente junto con el desarrollo local de Tattoo y TattooInbox. El informe
y las instrucciones reproducibles están en `audit/20261120/INFORME.md`.

## Estado operativo

- `module.json` mantiene `active: 0`; no se añadió el proveedor a `config/app.php`.
- No se registran rutas, permisos, migraciones ni trabajos de AiReception.
- El proveedor rechaza conexiones OpenAI en producción incluso con flags activos.
  En local/testing exige ambos flags `AI_RECEPTION_ENABLED` y
  `AI_RECEPTION_EXTERNAL_CONNECTIONS_ENABLED`. Ambos tienen valor predeterminado falso.
- Las pruebas inyectan HTTP falso; no necesitan claves ni cargan `.env`.
- `MetaWebhook` ahora usa el namespace PSR-4 `Services`. `verifiedMessages`
  verifica firma antes de analizar; `messages` sigue siendo un parser puro y no
  debe usarse directamente como puerta de entrada HTTP.
- Ningún webhook nuevo está expuesto ni se envían mensajes Meta.
- `BookingGateway` se resuelve como `DisabledBookingGateway`: reservar, cancelar
  y reprogramar lanzan una excepción. No hay persistencia de reservas de IA.

## Agenda existente

`TattooAvailability` recibe un `TattooCalendarSource` y un callback de autorización
que debe devolver exactamente `true`. La autorización precede cualquier lectura.
El callback debe validar actor autenticado, permiso sobre empresa y profesional;
no debe basarse en identificadores aportados por el modelo o el mensaje WhatsApp.

`HynTattooCalendarSource` exige que el `Environment::tenant()` activo coincida con
el identificador de negocio (ID de Website). No cambia el tenant. Usa los modelos
existentes `TattooArtist`, `TattooAppointment` y `TattooSession`, que heredan
`ModelTenant`/`UsesTenantConnection`. Lee exclusivamente el profesional seleccionado
y los registros del día y del día anterior, para cubrir arrastre de ocupación.

La política de horarios se pasa explícitamente desde código confiable. No se
inventó una tabla de horarios: el esquema observado no almacena duración de cita
ni de sesión. `legacy_duration_minutes` es una decisión explícita del negocio,
entre 5 y 720 minutos, aplicable a los registros heredados. Si hay una ocupación
sin duración configurada o sin hora, se devuelve disponibilidad vacía. Una cita
cancelada no ocupa; otros estados, incluidos desconocidos, bloquean conservadoramente.
No interpretar una lista de slots como una reserva ni como permiso para escribir.
La política uniforme de duración debe sustituirse por duraciones verificadas por
servicio antes de habilitar reservas reales.

## Contexto Hyn de TattooInbox

Se sustituyó la llamada inexistente `Environment::end()` por `TenantContext`.
Guarda el tenant previo, cambia dentro de un `try`, purga la conexión y restaura
el contexto en `finally`. Sin tenant previo, elimina el binding y la configuración
de conexión residual; también restaura el disco tenant. Los errores de limpieza
no se silencian. La transacción del inbox usa explícitamente la conexión tenant.
La prueba de ciclo de contexto usa dobles de Hyn; la prueba de lectura utiliza
Eloquent real y dos SQLite en memoria. No certifica listeners completos de Hyn
ni concurrencia MySQL.

## WhatsApp por empresa

Se verificó que `WhatsAppApi` obtiene `ws_api_phone_number_id` y `ws_api_token` de
`Company` del tenant, y que TattooInbox dispone de un registro central
`tattoo_inbox_meta_channels`. No se conectaron esos servicios automáticamente.
La futura entrada debe verificar firma, resolver el canal en el registro central,
validar coherencia Website/Hostname/canal y obtener credenciales de ese tenant.
No usar el remitente ni un ID de empresa del payload como autoridad. No reutilizar
los fallbacks de tokens globales de TattooInbox para una integración multiempresa.

## Pendiente antes de producción

Autorización de todas las rutas Tattoo; auditoría de endpoints existentes;
firmas obligatorias del inbox existente; retención y minimización de logs;
deduplicación persistente de mensajes; duraciones por servicio; servicio de dominio
de reservas con transacción en la conexión tenant, bloqueo de agenda por profesional,
revalidación de disponibilidad e idempotencia; cancelación/reprogramación autorizadas;
cuotas; pruebas MySQL concurrentes; entorno completo de prueba por empresa;
rotación de secretos históricos. No ejecutar migraciones globales a ciegas: hay
migraciones Tattoo duplicadas en `database/migrations` y dentro del módulo.
