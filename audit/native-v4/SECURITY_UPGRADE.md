# Seguridad, privacidad y modernizacion

Ver SECURITY_DEPENDENCIES.md: hay avisos criticos, no apto para produccion. No se actualizo globalmente PHP/Laravel ni se revocaron credenciales en uso. Preparar compatibilidad en worktree independiente, con pruebas PDF/XML/firma/SUNAT/POS/Hyn; no asumir que los adaptadores nuevos mitigan el framework antiguo.

Aislamiento probado: Hyn website y DB coincidentes, licencia y nivel por usuario, session vinculada al tenant, propiedad de canal, cliente de propuesta y datos tenant. Adjuntos clinicos cifrados fuera de public. APP_KEY compartida por instalacion no sustituye gestion de claves/KMS y rotacion: planificar versionado y recifrado antes de adopcion clinica.

Ley peruana 29733 y reglamento DS016-2024-JUS: fuente oficial https://www.gob.pe/institucion/anpd/normas-legales/6554453-n-016-2024-jus . Esta revision es tecnica, NO certificacion legal. Requiere determinar base legal, consentimiento, obligaciones de informacion, registros/bancos, transferencias internacionales, contratos de encargados, seguridad, derechos y conservacion con asesor especializado. No usar pacientes reales hasta validar.

Inventario de secretos historicos debe mantenerse privado con proveedor, archivo/referencia, alcance, responsable, dependencia y plan de rotacion; nunca valores. Rotar exige coordinacion con clientes, prueba de nueva credencial, cambio gradual y revocacion expresamente autorizada. El escaneo de publicacion evita secretos nuevos; no certifica ausencia historica en todo Git.

Pendientes: politica operativa de incidentes, respaldo cifrado externo, exportacion/eliminacion clinica conforme a obligaciones, limites de almacenaje, auditoria de todas las rutas historicas y mitigacion de avisos aplicables.
