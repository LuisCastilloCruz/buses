# Plan de despliegue: NO ejecutado

Despliegue bloqueado: avisos criticos de dependencias, proveedores externos no validados, requisitos clinicos/comerciales pendientes. No hay autorizacion para desplegar desde esta mision.

Secuencia futura: mitigar dependencias en rama aislada; repetir regresion completa y pruebas tributarias autorizadas; revision profesional/legal de datos clinicos; configurar sandbox y validar E2E; auditar permisos y limites; aprobar cambios/migraciones por tenant; respaldo completo con restauracion verificada; ensayo en staging con datos sinteticos; revision operativa y aprobacion expresa; activacion opt-in piloto con monitoreo y mecanismo de suspension. No habilitar todos los tenants por activar nwidart.

Rollback debe recuperar codigo y estado de DB coherentes. Las migraciones de auditoria no hacen down destructivo automatico. No aplicar reset hard ni restaurar dumps encima de bases de clientes. El plan no es un despliegue realizado ni garantiza compatibilidad fiscal.
