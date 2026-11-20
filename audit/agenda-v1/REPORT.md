# Informe de entrega: agenda AiReception v1

Fecha: 20 de noviembre de 2026. Estado: laboratorio funcional; no habilitado para produccion.

## Preservacion y alcance

- Original: `C:\laragon\www\multifacturaperunew`.
- Rama original y referencia conservadas: `integration/ai-reception-tattoo-20261120-safe`, `e87fcdce227a7993fe5da96507b6417b980202ed`.
- Nuevo worktree: `C:\Users\luis castillo\AiReception-lab\agenda-v1`.
- Nueva rama: `feature/ai-reception-agenda-v1`.
- Se leyeron primero INTEGRATION_HP.md, RESUMEN.md y el INFORME.md privado de la fase anterior, ademas de modelos, controladores, rutas y migraciones Tattoo/TattooInbox.
- Original sin cambios tracked al inicio y al final de la verificacion; permanecen sus seis archivos sin seguimiento originales. No se copiaron al laboratorio ni se publicaron.
- No hay diferencias en Tattoo, TattooInbox, config/app.php ni modules_statuses.json respecto a la referencia segura. No hubo conflictos de merge: implementacion aditiva en el nuevo worktree.
- No se cargaron `.env`, conexiones, caches ni proveedores de la aplicacion original. No hubo acceso a servidores productivos, migraciones sobre bases de clientes, merge a main/master ni despliegue.

## Respaldos

El respaldo privado previo `audit-20261120-134940` se conserva fuera de ambos proyectos, en `C:\Users\luis castillo\MultiFactura-private-backups`. Se comprobaron el bundle de 514423443 bytes y el manifiesto: 54983 archivos, cero discrepancias. La fase anterior documento tambien la recuperacion Git y su comprobacion.

La ubicacion y hash del bundle final de esta rama se registran en el cierre privado `PRIVATE.md`, ignorado por Git. Configuracion, contrasenas, base desechable, sesiones, logs y perfiles de Chrome permanecen en `.agenda-runtime`, ignorado y con ACL restringida al usuario HP, SYSTEM y Administradores. No se publican estos archivos.

## Implementacion y archivos

| Ubicacion | Cambio |
|---|---|
| `modules/AiReception/Agenda/BookingService.php` | Motor compartido, autorizacion, disponibilidad, transacciones, idempotencia, version, confirmacion/cancelacion/reprogramacion y configuracion |
| `Agenda/Database/2026_11_20_000001_create_aireception_agenda_v1.php` | Esquema nuevo aislado, claves compuestas por empresa y checksum de migracion |
| `Agenda/LegacyCalendarSource.php` | Adaptador al contrato TattooCalendarSource para bloqueos ficticios de duracion desconocida |
| `Agenda/FutureMetaIngress.php` | Politica futura de firma obligatoria; ninguna ruta registrada |
| `Agenda/Http`, `routes.php`, `AgendaServiceProvider.php`, `User.php`, `LabSafety.php` | Sesiones, CSRF, roles, empresa activa en servidor, aislamiento de rutas y guardia de base |
| `Agenda/Views`, `demo-agenda/public/assets` | Calendario dia/semana, filtros, editor de horarios/servicios, historial y chat deterministico |
| `demo-agenda/bootstrap.php`, `config`, `public/index.php`, `router.php` | Bootstrap Laravel independiente sin descubrimiento de proveedores del sistema original |
| `demo-agenda/start.ps1`, `init-mysql.php`, `setup.php` | Arranque local, credenciales aleatorias, migracion y semillas ficticias |
| `demo-agenda/test.ps1`, `tests` | Pruebas de motor/MySQL, concurrencia multiproceso, HTTP y Chrome |
| `.gitignore`, `.gitattributes` | Exclusion de privados y finales de linea estables para checksums |
| `.github/workflows/ai-reception-standalone-tests.yml` | Activacion de checks puros y sintaxis para esta rama; sin afirmar pruebas MySQL en CI |
| `audit/agenda-v1` | Este informe, inventario, resultados y capturas ficticias |

## Datos y migraciones

Dos negocios ficticios: estudio Tinta Sur y clinica Sonrisa Clara, con profesionales y servicios propios. Una cuenta administradora puede alternarlos; cuentas individuales no pueden acceder al otro negocio. La clinica usa exactamente el mismo motor.

MySQL 8.4.3 real, InnoDB, `127.0.0.1:13317`, base `aireception_lab_v1`. Configuracion y archivos del servidor independientes de Laragon existente. La guardia verifica base, puerto y datadir antes de migrar o reservar.

Se crearon exclusivamente tablas nuevas `aireception_`: migrations, businesses, users, memberships, professionals, services, professional_services, bookings, commands, events y legacy_blocks. No se alteraron tablas Tattoo/TattooInbox.

Inventario: 28 archivos de migracion heredados. Siete clases tienen dos copias byte a byte identicas: artistas, estilos, citas, sesiones, consentimientos, photo_path y portfolios. `migration-inventory.md` contiene rutas y hashes. Son redundancias de fuente; no se borraron ni reescribieron. **Estado aplicado en las bases originales: no determinado**, porque no se consultaron bases cuyo uso productivo no esta descartado. En el laboratorio no se ejecutaron esas migraciones: solo la nueva migracion independiente. La nueva migracion aplicada no se modifico; el arranque valida su SHA-256. Futuras migraciones deben tener identidad propia, ser aditivas y ejecutarse con la misma guardia, nunca con el migrador general original.

## Seguridad y concurrencia

Autenticacion por sesion Laravel, cookies HttpOnly/SameSite, CSRF, CSP, acceso loopback y Host local. El negocio activo se obtiene de la sesion y se verifica contra membresias en cada operacion. IDs de profesional, servicio, cita e historial se consultan siempre dentro de la empresa autorizada. Claves foraneas compuestas refuerzan la separacion.

Reservar y modificar exige admin/recepcion. Configurar exige admin; viewer solo consulta. Cuerpo HTTP con otra empresa no cambia la identidad del comando. No hay ruta que permita a la IA escribir directamente.

Reserva, auditoria e idempotencia se guardan en la misma transaccion. Se bloquea el profesional y se revalida disponibilidad dentro de ella. El control de version evita actualizaciones perdidas; comandos repetidos identicos devuelven el resultado almacenado y claves reutilizadas con otros datos fallan. Las pruebas usan dos procesos PHP y conexiones MySQL reales, tanto para competir por un horario como para repetir una misma clave.

Duracion y margen son snapshots explicitos de cada cita. Cambiar un servicio afecta reservas nuevas; confirmar/reprogramar conserva los snapshots. Datos heredados sin duracion verificable cierran disponibilidad; nunca se deduce una duracion de sus importes o del nuevo servicio.

Las rutas Tattoo historicas tienen grupos `web` sin `auth` explicito en el archivo revisado. TattooInbox usa `web, auth`, pero su firma Meta es opcional por defecto. **No se montan estas rutas en el laboratorio ni se cambio su comportamiento original.** Habilitarlas exige auditar el middleware global y permisos del despliegue real, ademas de hacer obligatoria la firma antes de conectar un canal nuevo.

Secretos historicos ya senalados en la fase anterior siguen pendientes de rotacion por su propietario: credenciales de proveedores/API y material criptografico previamente versionado, ademas de tokens de integraciones que continuen vigentes. No se imprimieron ni republicaron valores. Borrar un secreto del tip no lo revoca del historial. Revocarlos sin coordinar sustitutos puede interrumpir consultas o servicios existentes; no se deshabilito ninguno durante esta fase.

## Pruebas y evidencias

Ver `test-results.md` para resultados finales y `screenshots/` para capturas reales. El runner completo es `demo-agenda/test.ps1`.

Se detectaron y corrigieron problemas durante los ensayos: proveedores minimos requeridos por Laravel 5.7; nombre de prueba Python que ocultaba el paquete http; codificacion de textos; conservacion de duracion al reprogramar; cierre anticipado de dialogos que permitia abrir tarjetas/configuracion obsoletas antes de refrescar; verificaciones Windows de puertos que devolvian PID cero. El arranque usa ahora identidad real SQL/HTTP. Un intento de instancia MySQL duplicada fue rechazado por el bloqueo de sus archivos; no se afecto la instancia activa ni la integridad de las pruebas.

La herramienta de escritorio/navegador integrado no pudo inicializar su kernel (ruta inexistente). Se uso Chrome instalado, en modo headless con perfil exclusivo y nuevo, mediante un ensayo reproducible. Las capturas no son mockups. No se uso ni modifico el perfil normal del navegador.

No se ejecuto la suite completa fiscal del Laravel original ni extremo a extremo de pantallas Tattoo/TattooInbox con clientes. Compatibilidad aqui significa fuentes conservadas, sintaxis, pruebas de clases/adaptadores y aislamiento verificados; no certificacion del sistema productivo.

## Ejecucion, pendientes y limites

Instrucciones exactas, cuentas ficticias (sin contrasenas), permisos y comandos en `demo-agenda/README.md`. URL local: **http://127.0.0.1:8874**. Archivo privado de acceso: `.agenda-runtime/ACCESS.txt`.

Pendiente para produccion: integracion controlada con Hyn y autenticacion/roles existentes; inventario real de migraciones por tenant; politica de duraciones heredadas y reconciliacion de agendas; hacer que todos los escritores respeten la misma exclusion; revision de seguridad y versiones obsoletas PHP/Laravel; servidor web apto para produccion, HTTPS, limites de abuso, monitoreo y recuperacion. Este servidor de desarrollo solo es local. No hay soporte de ventanas que crucen medianoche ni gestion de cuentas desde la interfaz.

OpenAI/WhatsApp reales quedan para otra fase. Primero crear credenciales sandbox por negocio, registrar el mapa de canales en servidor, exigir firmas sobre el cuerpo crudo, deduplicar eventos, redactar datos personales y comprobar permisos antes de ejecutar comandos. La IA solo debe proponer; el motor autorizado confirma. Validar sandbox y consentimiento antes de habilitar cualquier negocio real.

## GitHub

Rama publicada y verificada: [feature/ai-reception-agenda-v1](https://github.com/LuisCastilloCruz/buses/tree/feature/ai-reception-agenda-v1). Commit de implementacion probado: `4c3a67bcd1b1a37ce93e8e1e1368bc00612ff268`. `git ls-remote` devolvio exactamente ese SHA tras el push. El commit posterior de cierre solo actualiza documentacion; su SHA final se registra localmente en PRIVATE.md para evitar una referencia circular dentro del propio commit.

Escaneo previo: 51 archivos del indice contrastados con marcadores y credenciales privadas del laboratorio, sin coincidencias. Escaneo de 190 blobs nuevos alcanzables respecto a github/main, incluidos secretos historicos conocidos: PASS. Las credenciales originales historicas no se revocaron y requieren el tratamiento documentado arriba.

GitHub Actions [44809188315](https://github.com/LuisCastilloCruz/buses/actions/runs/44809188315): **success**, dos jobs (PHP 7.4 y 8.2) y todos sus pasos aprobados. Son pruebas puras y de sintaxis, no arranque completo de Laravel 5.7 en PHP 8.2 ni MySQL/navegador en la nube. MySQL, HTTP y Chrome se ejecutaron en la HP y sus evidencias estan en esta rama. No se creo PR, no se hizo merge y no se desplego.
