# Agenda AiReception v1: laboratorio HP

Esta instalacion es una aplicacion Laravel 5.7 aislada que utiliza las dependencias y contratos del proyecto. No arranca la aplicacion fiscal original ni sus proveedores Hyn, colas, webhooks o integraciones externas. No copies `.env` ni ejecutes `artisan migrate` del proyecto original.

## Abrir la demostracion existente

En PowerShell, como tu usuario habitual de Windows:

```powershell
Set-Location 'C:\Users\luis castillo\AiReception-lab\agenda-v1'
powershell -NoProfile -File .\demo-agenda\start.ps1
```

Abre **http://127.0.0.1:8874**. Las contrasenas aleatorias estan solo en `.agenda-runtime/ACCESS.txt`. Abre ese archivo localmente; no lo publiques ni lo pegues en un chat.

- `manager@example.test`: administrador de ambos negocios; cambia de negocio en el selector lateral.
- `tattoo@example.test`: administrador solo de Tinta Sur.
- `dental@example.test`: administrador solo de Sonrisa Clara.
- `viewer@example.test`: consulta sin escrituras, solo Tinta Sur.
- `reception@example.test`: reservas sin configuracion, solo Tinta Sur.

En Calendario elige fecha, profesional y vista Dia/Semana. Nueva cita permite consultar horarios y guardar una cita pendiente o confirmada. Al abrir una tarjeta puedes confirmar, reprogramar o cancelar; el historial conserva cada cambio. Cancelar libera disponibilidad sin borrar la reserva.

En Profesionales y servicios puedes crear o editar servicios, duraciones y margenes; habilitar servicios por profesional; definir tramos semanales y excepciones por fecha. Un dia sin tramos esta cerrado. `09:00-13:00, 14:00-18:00` incluye una hora de descanso. Las excepciones admiten `YYYY-MM-DD: cerrado` o tramos. No se admiten turnos nocturnos que crucen medianoche en esta version.

En Agente de demostracion escribe una solicitud, pulsa Elegir un horario, selecciona y guarda. La conversacion es deterministica; la reserva se guarda en MySQL y el enlace abre el calendario. No se envia ningun mensaje a WhatsApp ni a OpenAI.

## Dependencias verificadas en la HP

- PHP `C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe`, version 7.4.33.
- Laravel 5.7.29; Hyn 5.3.1 conservado, no activado en este bootstrap.
- MySQL `C:\laragon\bin\mysql\mysql-8.4.3-winx64`, version 8.4.3.
- Node 22.11.0 en el proceso de pruebas; Python 3.13; Chrome ya instalado.
- `vendor` es una copia independiente de las dependencias verificadas de la HP. No se actualizaron paquetes ni se ejecuto Composer install/update.

La base desechable es `aireception_lab_v1`, puerto **13317**, solo `127.0.0.1`. Sus archivos estan en `.agenda-runtime/mysql-data`. El usuario SQL se genera con privilegios exclusivamente sobre esa base. `LabSafety` comprueba puerto, nombre de base y ruta real de datos antes de usar el motor o aplicar la migracion. Las sesiones, caches, registros, claves y perfiles del navegador de pruebas estan en `.agenda-runtime`, ignorado por Git y protegido por ACL local.

El script es idempotente: conserva datos y contrasenas existentes. Si un puerto esta ocupado por otra instancia, se detiene ante la comprobacion de identidad; no mata procesos ajenos. No configura servicios de Windows ni arranque automatico. Tras reiniciar Windows, vuelve a ejecutar `start.ps1`.

## Reproducir las pruebas

```powershell
Set-Location 'C:\Users\luis castillo\AiReception-lab\agenda-v1'
powershell -NoProfile -File .\demo-agenda\test.ps1
```

El runner inicia/verifica el laboratorio, comprueba sintaxis y ejecuta suites existentes, motor Laravel/MySQL, HTTP y Chrome headless con perfil nuevo. No usa el perfil habitual del navegador. Los ensayos SQL crean recursos propios y los limpian al finalizar; los flujos HTTP/navegador conservan citas ficticias como evidencia. Las capturas se actualizan en `audit/agenda-v1/screenshots`. Los logs estan en `.agenda-runtime/test-logs`.

`-SkipBrowser` omite Chrome explicitamente y no cuenta sus pruebas como aprobadas. Puedes indicar un Chrome compatible mediante `AGENDA_TEST_CHROME`; no se descarga ningun navegador.

## Preparar otro worktree local, sin tocar la instalacion original

El laboratorio usa puertos fijos: solo una instancia puede estar activa a la vez. Conserva esta instalacion si no necesitas otra.

1. Crea otra rama/worktree desde el commit publicado, con nombre y ruta nuevos; nunca sobrescribas uno existente.
2. Copia **solo** la carpeta `vendor` verificada a ese worktree, sin enlaces simbolicos, `.env`, `storage` ni caches originales. No copies `.agenda-runtime`: las claves y la ruta pertenecen a cada instalacion.
3. Con puertos 13317 y 8874 disponibles, ejecuta `demo-agenda/start.ps1`. Se inicializa una instancia MySQL independiente, se genera configuracion privada y se aplica exclusivamente la migracion nueva.
4. Ejecuta `demo-agenda/test.ps1`. Para una instalacion distinta a la HP actual, prepara y valida las dependencias exactas de `composer.lock` por separado. No se garantiza una instalacion nueva desde Internet de este Laravel antiguo.

## Contrato del motor y limites

`BookingService` implementa `BookingGateway` y ofrece `execute(actor, business, action, payload, key)`. Actor y empresa son identidades confiables resueltas por el servidor; el controlador nunca acepta esos valores del cuerpo para autorizar. Todos los comandos requieren membresia activa. Solo admin/recepcion escriben; solo admin configura. La clave de idempotencia pertenece a empresa + actor; reutilizarla con otro payload devuelve 409.

Las escrituras usan transacciones InnoDB, bloqueo de membresia y profesional, consulta de disponibilidad dentro de la transaccion, version optimista y registros de auditoria. Lecturas bloqueantes antes del profesional evitan snapshots obsoletos bajo REPEATABLE READ. Los margenes de ambas citas deben caber entre ellas. Toda escritura futura debe pasar por este servicio; escrituras SQL externas podrian eludir la regla de solapamiento.

Cada reserva guarda su duracion y margen, que se conservan al confirmar/reprogramar aunque cambie el servicio. Servicios/profesionales desactivados impiden nuevas reservas y reprogramaciones; las citas existentes siguen visibles y pueden cancelarse.

`SlotPlanner` y `TattooAvailability` se reutilizan sin modificarlos. Los registros heredados sin duracion verificable bloquean disponibilidad mediante `LegacyCalendarSource`; no se infiere duracion a partir del servicio solicitado. La tabla de sombras es un mecanismo nuevo del laboratorio, no una tabla preexistente de Tattoo. No hay importacion automatica de datos de clientes.

No se ha conectado este motor a las bases Hyn reales. Antes de hacerlo se necesita mapear empresa autenticada, profesional y servicios por tenant, verificar migraciones aplicadas, resolver duraciones antiguas y encaminar TODAS las escrituras de ambas agendas por la misma exclusion transaccional. No activar en paralelo con escrituras heredadas que eludan el bloqueo.

## OpenAI y Meta: siguiente fase

El laboratorio no tiene clientes externos ni rutas de webhook. `FutureMetaIngress` exige secreto y firma HMAC valida sobre el cuerpo crudo antes de resolver un mapa de telefonos confiable del servidor. No admite una opcion para omitir firmas. La configuracion historica de TattooInbox conserva su comportamiento original; no se ha cambiado produccion.

Para una futura integracion real: provisionar credenciales de sandbox nuevas por negocio; registrar identificadores de canal en servidor; exigir firmas y rechazar IDs desconocidos; deduplicar mensajes; aplicar limites y redaccion de datos; usar IA solo para propuestas estructuradas; pedir confirmacion explicita y ejecutar comandos con identidad/permisos verificados. Validar todo primero en otro entorno de pruebas. Ninguna de esas conexiones esta habilitada aqui.
