# Seguridad V6

Auditoria Composer ejecutada con --no-plugins --no-scripts audit --locked --format=json: 117 avisos, 23 paquetes afectados, 15 abandonados. Resultado privado conservado. No se actualizaron paquetes globales.

AUTH-001: entrada no textual llegaba al proveedor TokenGuard heredado. Mitigacion en app/Auth/StrictTokenGuard.php y AuthServiceProvider: rechazo previo para api/system_api, con 19 checks y 12 verificaciones Laravel/MySQL. No prueba por si sola explotabilidad de todos los avisos.

GW-001: carrera de reintento corregida dentro de transaccion SQLite. Audio validado con binarios cuyo ZIP SHA256 es b9bbbb3acece000cbeec994f856649354e52c773807841e78908374b24d658fd. No se usa red real en tests.

Listeners inspeccionados: solo 127.0.0.1. No tuneles ni exposicion publica. Credenciales aleatorias V6 permanecen en .native-runtime privado. No se copio .env productivo ni se revocaron secretos historicos.

Pendiente: mitigar avisos aplicables criticos/altos, modernizar con regresion fiscal, sandbox externo y revision clinica/legal. No certificacion legal ni seguridad productiva.
