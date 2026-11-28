# Seguridad V6

Auditoria Composer ejecutada con --no-plugins --no-scripts audit --locked --format=json: 117 avisos, 23 paquetes afectados, 15 abandonados. Resultado privado conservado. No se actualizaron paquetes globales.

AUTH-001: entrada no textual llegaba al proveedor TokenGuard heredado. Mitigacion en app/Auth/StrictTokenGuard.php y AuthServiceProvider: rechazo previo para api/system_api, con 19 checks y 12 verificaciones Laravel/MySQL. No prueba por si sola explotabilidad de todos los avisos.

GW-001: carrera de reintento corregida dentro de transaccion SQLite. Audio validado con binarios cuyo ZIP SHA256 es b9bbbb3acece000cbeec994f856649354e52c773807841e78908374b24d658fd. No se usa red real en tests.

Listeners inspeccionados: solo 127.0.0.1. No tuneles ni exposicion publica. Credenciales aleatorias V6 permanecen en .native-runtime privado. No se copio .env productivo ni se revocaron secretos historicos.

Pendiente: mitigar avisos aplicables criticos/altos, modernizar con regresion fiscal, sandbox externo y revision clinica/legal. No certificacion legal ni seguridad productiva.

## Referencias normativas verificadas (revision tecnica limitada)
La ANPD identifica el D.S. 016-2024-JUS como nuevo reglamento de la Ley 29733 e informa su vigencia desde el 31 de marzo de 2025. Fuentes oficiales: [norma](https://www.gob.pe/institucion/anpd/normas-legales/6554453-n-016-2024-jus) y [vigencia](https://www.gob.pe/institucion/anpd/campa%C3%B1as/128319-nuevo-reglamento-de-proteccion-de-datos-personales).

MINSA publica la NTS 139 para gestion, conservacion y eliminacion de historias clinicas, con su modificatoria. [Repositorio oficial MINSA](https://repositorio.minsa.gob.pe/handle/MINSA/81631).

Decision tecnica: no fijar borrado automatico de historias ni plazos arbitrarios; mantener datos reales deshabilitados hasta que responsables legales/clinicos definan obligaciones aplicables, autorizaciones, retencion, ejercicio de derechos, encargados y transferencias internacionales. La verificacion de estas referencias no certifica cumplimiento ni sustituye revision profesional de la implementacion.

## Decoder endurecido
Hashes binarios fijados en native-lab/verify-decoder-v6.ps1 y comprobados. FFmpeg/FFprobe forzados a demuxer OGG, un hilo, max_alloc 32 MiB por asignacion, protocolos file/pipe, timeout 10 s, salida acotada y limpieza. Esto no equivale a un limite total de memoria ni aislamiento OS con cuenta separada. Suite combinada final: 27/27 PASS, incluido decoder real y proveedores simulados.
