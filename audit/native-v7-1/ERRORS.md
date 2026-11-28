# V7.1 observed errors and corrections

1. PowerShell removed quotes from Node -e preflight: replaced with explicit .mjs file. Preflight then passed.
2. Fresh lab had no plan quota. First E2E made zero model calls; configured the fictional plan through original Superadmin HTTP (11 checks). E2E then passed 17 assertions each in dental and Tattoo with real Ollama, MySQL and simulated outbox.
3. Model asked for already known day: clarify_selection enum restricted to actually missing data; known date regression passed.
4. Model chose business_info for price despite description: descriptions clarified, same case still failed; narrowed explicit price requests to catalogue/knowledge/human tools. Repeated cases now choose correct catalogue. Expected answers unchanged.
5. Evaluation stopped on RAM guard after 19 complete conversations in iteration2. Opera had been closed normally with user authorization; Ollama itself used about3GB host RAM. Closed authorized idle desktop utilities and resumed completed checkpoints without replay.

Earlier failed transcripts retained. Global coherence thresholds and subjective naturalness are not approved merely from these corrections.

## Continuacion V7.1
- Nueva cita tras cancelacion retenia intent=cancel y booking_id anterior. Reproducido con v71-new-request-test.php; transicion explicita corregida y regresion aprobada.
- Confirmacion nativa no ligaba el borrador al estado conversacional. Nueva migracion 000006 y comprobacion transaccional del token vigente; 6 comprobaciones MySQL aprobadas en dental y Tattoo, incluido rechazo 409 de token obsoleto e idempotencia.
- Fixture de persistencia suponia user_id=3. Ahora crea usuario temporal sin permisos y revierte transaccion; 20/20 aprobadas sin cambiar la expectativa 403.
- Concurrencia: 2/5/10 pasaron; 25 fallo al inicializar por max_connections=20. Repetido con limite temporal64, restauracion20 en finally: 25 escritores, una reserva fisica, 24 conflictos, p95=1702.49ms. No se redujo la carga.

- E2E extendido fallo en paso19: el modelo elegia request_summary al pedir otra cita tras cancelar. Correccion minima: una nueva cita explicita expone disponibilidad/clarificacion/derivacion; no confirma. Repetido mismo E2E:21/21 dental y21/21 Tattoo. Regresion nueva-solicitud2/2. La muestra multigiro posterior51/60 conserva fallo residual de solicitudes repetidas ordinarias, que no se oculta.
- El intento de50 workers excedio64 conexiones (dos conexiones por proceso). Preflight ahora calcula2*n+4 y bloquea ese nivel antes de lanzar;25 ya aprobado. No afirmar50 aprobado.
- Capturas iniciales de dashboard se tomaban antes de acabar AJAX; se agrego comprobacion de loaders finalizados y capturas25-loaded-dashboard. No se cambio el dashboard para ocultar cargas.
