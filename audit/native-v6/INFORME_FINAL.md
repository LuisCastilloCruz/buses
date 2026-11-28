# Informe V6 ? recuperacion y validacion local

Estado: laboratorio funcional, NO apto para produccion. Este informe registra pruebas ejecutadas; no declara completada la mision comercial ni la matriz total de fallos.

## Procedencia y preservacion

Ruta de trabajo C:\Users\luis castillo\AiReception-lab\native-v6. Rama feature/multifactura-ai-reception-release-candidate-v6. Base V5 d597fa32d914b6b5be4b99381c3c1a6a8b60bddf. V5, versiones anteriores, instalacion original y produccion no modificadas. Se recupero el worktree existente, no se recreo.

Log inicial confirmo migraciones y fixtures terminados. Servicios detenidos se reanudaron sin migraciones ni nuevas semillas. Respaldo V5 de 5418 archivos conservado. Backups y credenciales quedan privados, nunca en Git.

## Cambios implementados

- StrictTokenGuard y registro del driver original token: rechaza credenciales no textuales para api/system_api antes de consultar usuarios; sesiones web originales conservadas.
- Gateway: lectura de idempotencia y transicion bajo BEGIN IMMEDIATE; elimina doble reintento entre procesos.
- Recuperacion V6 sin reinstalar; checkpoints append-only con hashes y lock de ejecucion exclusiva.
- Pruebas MySQL con barrera de inicio, validacion de filas finales y reanudacion con mismas claves.
- Pool nginx local configurable 4/8 PHP CGI, OPcache opcional por proceso, parada con verificacion de PID/ejecutable/fecha. No cambia php.ini global.
- Pruebas de carga, recursos, resistencia, decoder OGG/Opus real, fallos de proveedor y caida de worker gateway.
- Restauracion MySQL aislada con conteos y hashes de filas; scanner de cambios staged y CI ampliada.
- Regresion adicional de Chrome espera que todos los datos del dashboard terminen de cargar.

No se agregaron migraciones de negocio, no se reemplazaron Tattoo/TattooInbox, ni se actualizaron Laravel o paquetes globales. La cuarta empresa se creo mediante Superadministrador en bases ficticias V6.

## Evidencia

Ver TEST_RESULTS.md, STRESS_TEST_RESULTS.md, PERFORMANCE_COMPARISON.md, RECOVERY_TESTS.md, PERFORMANCE_METRICS.csv y screenshots/. Suite completa previa reproducida en V6 exit 0; 42 pruebas HTTP adicionales del pool; 9 comprobaciones de dashboard completo. Treinta capturas reales.

Carga breve: 20 clientes HTTP concurrentes sobre una cuenta ficticia, p95 1634 ms con pool8/OPcache; CPU pico 92.51%, no sostenido. No se extrapola a produccion ni a 20 identidades independientes. Se conservan los ensayos anteriores fallidos. 50 HTTP no se ejecuta por falta de margen; 50 workers MySQL bloqueados por preflight de RAM.

Concurrencia MySQL 2/5/10/25 workers: una reserva persistida por horario. Gateway: 650 intentos sinteticos, 250 eventos unicos, cero llamadas externas; caida de worker no repite operacion ni presupuesto. Matriz proveedor 9 casos aprobados.

Primera resistencia: 600.768 s, 385 solicitudes, cero errores. Segunda resistencia posterior a optimizacion: 601.227 s, 437 solicitudes, cero errores, p95 454.27 ms. Matriz ampliada: siete carreras/recovery MySQL aprobadas. Gateway/decoder completo: 27/27; credenciales: 19 unitarias y 13 Laravel/MySQL.

Restauracion inicial: cinco bases, 1409 tablas, todos los hashes/conteos coinciden; MySQL13322 cerrado despues. Ver manifiestos privados para puntos de recuperacion y hashes exactos.

## Seguridad y pendientes

117 avisos Composer / 23 paquetes / 15 abandonados siguen siendo un bloqueo de seguridad. Mitigar TokenGuard no resuelve los restantes avisos. Sin exposicion publica del backend, despliegue, merge a main/master, cobros, SUNAT real, pacientes reales ni revocaciones de credenciales.

OpenAI y Meta solo simulados; decoder real no certifica WhatsApp real. Faltan sandbox autorizado, credenciales nuevas limitadas, validacion de costos/plantillas/consentimiento y revision clinica/legal. Ver OPEN_BLOCKERS.md y SANDBOX_VALIDATION.md. La modernizacion integral, validacion fiscal y matriz completa de fallos siguen pendientes.

## Uso y trazabilidad

RUNBOOK.md contiene comandos de arranque, perfiles, puertos, parada y recuperacion. URL principal http://localhost:8878; empresas tattoo.localhost, dental.localhost, comercio.localhost y cuarta.localhost en el mismo puerto. Credenciales ficticias solo en .native-runtime privado.

Commits de hitos: 48a78ffd (seguridad/gateway) y 36bceda6 (laboratorio/mediciones). Commit de cierre y publicacion se registran en el checkpoint privado de entrega y respuesta final; el commit que contiene este informe no puede incluir su propio hash sin cambiarlo.

Publicacion pendiente de revision final: solo rama V6, sin merge ni despliegue. GitHub Actions valida suites aisladas; no sustituye MySQL/Chrome/carga de la HP.
