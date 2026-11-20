# Integracion universal nativa

`Agenda/BookingService` conserva el motor V1 (transacciones, idempotencia, versiones y snapshots de duracion) y depende de `BookingAuthority` y `CalendarGuard`. No importa modelos Tattoo ni Odontologia. Los adaptadores de dominio viven en sus respectivos modulos. El alias `Services/HynTattooCalendarSource` solo mantiene compatibilidad con consumidores V1; la implementacion se traslado a Tattoo.

`Native/Authority` comprueba Hyn, usuario autenticado, habilitacion de empresa y nivel por operacion. `Native/CalendarGuard` serializa recursos compartidos; los adaptadores `DomainCalendar` aportan restricciones de cada negocio. Tattoo reutiliza `TattooAvailability` y `HynTattooCalendarSource`; sin duracion heredada verificable falla cerrado. La clinica usa sus profesionales y sillones, nunca modelos Tattoo.

MySQL nativo usa READ COMMITTED por conexion al construir el motor, antes de iniciar transacciones. Esto evita que las consultas de permisos creen un snapshot REPEATABLE READ anterior al bloqueo del profesional. Las escrituras bloquean actor, comando idempotente/registro, recursos ordenados y profesional; revalidan disponibilidad dentro de la transaccion. El motor reintenta deadlocks tres veces. Los escritores heredados de citas y sesiones Tattoo toman el mismo bloqueo profesional y hacen lectura bloqueante de ocupacion canonica.

La administracion de permisos reemplaza pivotes dentro de una transaccion. Las licencias nuevas quedan temporalmente cerradas y solo se habilitan al terminar. Fallar al actualizar no autoriza usuarios de otros tenants. Las sesiones incluyen contexto de empresa; copiar una cookie entre hosts se rechaza. Sesiones previas sin contexto requieren autenticarse de nuevo al activar esta capa.

Rutas nuevas: `/aireception/{calendar,professionals,services,settings,conversations,agent}` y `/odontologia` con sus rutas de calendario y configuracion. Todas usan autenticacion original, bloqueo de tenant y niveles explicitos, tambien AJAX. Las vistas extienden `tenant.layouts.app`; no hay auth ni sidebar alternativos.

AiReception y Odontologia comparten `aireception_bookings` dentro de cada base Hyn. Los clientes proceden de `persons`; el snapshot de nombre conserva trazabilidad. Los servicios enlazan `items`. Conversaciones, mensajes, derivaciones y recordatorios simulados se almacenan en la misma base tenant.

Default: `AIRECEPTION_NATIVE_ENABLED=false`, `aireception.enabled=false`, conexiones externas desactivadas. Solo el bootstrap del laboratorio habilita la interfaz nativa. El gateway destinado a IA sigue desactivado; la IA no recibe acceso de escritura a tablas.

## Antes de proveedores reales

1. Validar configuracion en sandbox, nunca copiar credenciales historicas.
2. Exigir firma HMAC Meta sobre el cuerpo original, secreto no vacio, identificadores de canal vinculados del lado servidor a un unico tenant y deduplicacion de eventos.
3. Definir permisos de derivacion, aprobacion humana y limites. Toda reserva pasa por el motor, nunca por SQL generado por IA.
4. Rotar las credenciales que el titular identifique como expuestas en auditorias historicas: revisar tokens Meta/WhatsApp, clave OpenAI, SMTP y certificados sin imprimirlos. No se revoco ninguna en esta fase.
5. El `verify_token` por defecto de TattooInbox no debe reutilizarse en una conexion real. En el laboratorio la firma es obligatoria y el webhook sin secreto se rechaza. No se cambio el default productivo heredado ni se interrumpio un canal existente.
6. Implementar y probar entrega/reintentos/retencion de recordatorios antes de habilitar envios. La bandeja actual es exclusivamente simulada.
