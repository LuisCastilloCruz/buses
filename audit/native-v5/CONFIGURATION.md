# Configuraci?n y operaci?n V5

## Instalaci?n aislada

Conservar V4. Usar el worktree native-v5, PHP 7.4.33 existente sin actualizaci?n global y MySQL separado administrado por native-lab/start.ps1. Los wrappers verifican prop?sito, ra?z, datadir, puerto 13320 y lista cerrada de bases mf_v5_*. Los datos privados est?n en .native-runtime con ACL restringida y excluidos de Git.

Migraciones nuevas, aditivas: 2026_11_23_000001 (outbox, intentos, turnos, confirmaciones) 000002 (suspensi?n espec?fica) y 000003 (v?nculo de estado de entrega del recordatorio). No ejecutar sobre producci?n. La rama contiene las mismas vistas de V4; solo cambia un texto de estado y un campo oculto de idempotencia en conversaciones.

## Flujo autorizado

1. Canal registrado por su negocio en la pantalla original. Respetar ChannelOwnership: un n?mero no puede pertenecer tambi?n a TattooInbox.
2. Verificar y vincular el remitente a un cliente ficticio desde la bandeja original. Sin vinculaci?n no hay reserva autom?tica.
3. Registrar consentimiento con evidencia y vencimiento; habilitar servicio/profesional real y reglas de agenda.
4. Configurar un usuario t?cnico del propio tenant con niveles aireception_conversations, aireception_calendar y aireception_book; sin permiso cl?nico ni configuraci?n. Mapearlo de forma privada por empresa/canal. No aceptar ese actor ni tenant desde mensajes o herramientas.
5. Reanudar la conversaci?n desde el panel. El ingreso se guarda cifrado y el modelo propone una herramienta permitida. Se entrega texto construido por el backend.
6. Una propuesta devuelve CONFIRMAR seguido de 8 caracteres y vence a los 5 minutos. Solo el mensaje textual exacto del mismo cliente/conversaci?n confirma; el modelo y una transcripci?n no pueden hacerlo.
7. BookingProposals y BookingService mantienen autorizaci?n, bloqueos, versiones e idempotencia. La salida entra en outbox; el recordatorio usa la misma outbox y revalida estado/versi?n/consentimiento al despachar.

## Operaci?n local simulada

```powershell
php native-lab/artisan.php aireception:transport 1 --simulate
php native-lab/artisan.php queue:work aireception --queue=aireception --once --tries=3 --sleep=0
```

Usar la ruta PHP 7.4 expl?cita si `php` en PATH apunta a otra instalaci?n. Los identificadores de empresa son los del laboratorio; comprobarlos antes de cada comando. La cola aireception es independiente de los dem?s jobs. ProcessInbound reutiliza el webhook V4; ProcessOutbox encapsula el contexto de tenant y lo restaura en finally.

Estados de salida: pending ? sending ? simulated_sent (local) o accepted (proveedor); los recibos pueden llevar a sent/delivered/read. suppressed/cancelled no se env?an. HTTP 429 autenticado ? retry con espera 30/60 segundos y m?ximo 3 intentos. Timeout, corte del worker o respuesta incierta ? unknown; no reenviar sin evidencia del proveedor. El gateway conserva resultados cifrados e impide repetir un POST pagado incierto.

Para conciliaci?n de una salida con provider_id, Outbox::reconcile cruza canal e ID con los recibos ya verificados. Un turno pending tras un corte requiere revisar el draft y su resultado transaccional antes de intervenir: no inventar una confirmaci?n ni volver a consumir IA autom?ticamente. No se proporciona un bot?n para forzar reenv?o de unknown.

## Seguridad de datos

Mantener almacenamiento temporal y SQLite del gateway fuera de public, con ACL de cuenta dedicada; cifrado AES-256-GCM para payloads del gateway. No registrar bodies de proveedores, tokens ni conversaciones en logs generales. Pol?tica de retenci?n y respaldo debe definirse antes de un entorno externo. El listado de plantillas autorizado es por canal, nunca global. En esta entrega se soportan plantillas est?ticas (nombre/idioma), no componentes variables.

La suspensi?n espec?fica del agente utiliza users.aireception_suspended. No reinterpretar ni quitar locked del administrador heredado. Los m?dulos/planes y la suspensi?n de empresa siguen siendo autoridad obligatoria.
