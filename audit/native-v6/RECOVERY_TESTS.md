# Recuperacion comprobada

- Servicios existentes reanudados sin migraciones/fixtures: MySQL13321, HTTP8878.
- Respaldo V5 de 5418 archivos sigue presente con manifiesto verificado; V5 intacta.
- Dump V6 exclusivamente ficticio: cinco bases, 1409 tablas.
- Restauracion real en instancia nueva MySQL13322 con datadir independiente. Todos los conteos y hashes SHA256 de filas coinciden. Instancia cerrada; puerto13322 sin listener despues.
- SHA256 SQL: d14d28ec431687af2093a57740d296d5faa80129adad564152c436fdefa8d051.
- Evidencia privada .native-runtime/recovery-20261127-235738; contiene SQL, manifiesto y digests. No publicar.
- Worker gateway interrumpido despues de claim durable: no repeticion de operacion ni presupuesto. 17 pruebas gateway aprobadas, incluye reabrir SQLite y recuperar lease expirado.
- Checkpoints append-only, lock exclusivo liberado por Windows al terminar proceso, reanudacion de concurrencia con mismas claves aprobada.

Limites: no se provoco un corte de energia fisico ni caida real del MySQL fuente. Matriz completa de fallos del motor de reservas aun no agotada. Backup logicamente restaurado no equivale a validar toda la aplicacion sobre el servidor restaurado.

## Respaldo actualizado despues de carreras
Segunda restauracion aprobada: 5 bases, 1409 tablas; todos los conteos/hashes coinciden. Evidencia privada recovery-20261128-002146. SHA256 SQL 529756ec5c44862707fa69c95aaf0a06d4a50834c3134aedbe998e5b4616605e.
