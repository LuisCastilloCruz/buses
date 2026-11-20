# Resultados reales V4

Los resultados siguientes corresponden a ejecuciones locales. No incluyen API real OpenAI ni Meta sandbox externo. Las pruebas de webhook usan eventos ficticios firmados.

## v4-block2-regression.log

```text
PASS syntax: 195 PHP files
PASS 1 duration derived from PCM bytes
PASS 2 transcript is explicitly untrusted
PASS 3 unverified compressed format rejected
PASS 4 truncated file rejected
PASS 5 duration limit enforced
PASS 6 size limit before provider
PASS 7 invalid files never reach provider
RESULT 7 audio mock checks; no network.
PASS 1 structured tool proposal
PASS 2 usage recorded in result
PASS 3 no provider storage or redirects
PASS 4 disabled provider
PASS 5 untrusted system instructions rejected
PASS 6 unregistered tool rejected
PASS 7 malformed arguments
PASS 8 provider timeout sanitized
PASS 9 rate limit is not retried blindly
PASS 10 official message response identifier
PASS 11 consent required
PASS 12 24 hour boundary
PASS 13 future message timestamp rejected
PASS 14 approved template outside window
PASS 15 unapproved template
PASS 16 channel path injection
PASS 17 ambiguous delivery sanitized, no blind retry
RESULT 17 provider contract assertions; mocks only, no network.
OK: 4 pruebas de clasificacion, privacidad y manejo de errores.
OK: 9 verificaciones Meta webhook, firma y aislamiento, sin tráfico real.
OK: 9 defensive validation checks (fake HTTP only).
OK: 26 comprobaciones SlotPlanner.
OK: 18 Tattoo adapter checks (fixtures; no database or network).
OK (6 tests, 20 assertions)
OK (1 test, 7 assertions)
PASS 1 availability respects configured duration
PASS 2 break excluded
PASS 3 closed Sunday
PASS 4 pending persisted with duration snapshot
PASS 5 idempotent retry returns original
PASS 6 idempotency payload mismatch
PASS 7 occupied slot rejected
PASS 8 confirmation persists
PASS 9 reschedule uses duration snapshot
PASS 10 stale version rejected
PASS 11 cancellation persists
PASS 12 complete write audit
PASS 13 viewer cannot write
PASS 14 viewer can read
PASS 15 cross-company context rejected
PASS 16 foreign or missing professional rejected
PASS 17 two real MySQL workers: one professional, exactly one booking
PASS 18 simultaneous same idempotency key creates once
PASS 19 two real MySQL workers: different professionals, one chair
PASS 20 disabled company denied despite assigned user permissions
PASS 21 external AI and Meta remain disabled
PASS 22 Hyn database credentials cannot read another tenant
PASS 23 different service duration changes offered slots
PASS 24 dated exception closes availability
RESULT 24 real native Laravel/MySQL checks
PASS 1 legacy unknown duration closes entire day
PASS 2 unknown previous-day duration cannot leak availability
PASS 3 legacy cancellation releases native availability
PASS 4 legacy writer rejects canonical occupied day under lock
PASS 5 original artist retained
PASS 6 original TattooInbox schema retained
RESULT 6 real legacy compatibility checks
PASS 1 fixture dental context
PASS 2 independent clinical permission exists
PASS 1 proposal does not reserve
PASS 2 explicit confirmation required
PASS 3 tenant injection rejected
PASS 4 proposal bound to authenticated actor
PASS 5 confirmed only after real backend write
PASS 6 confirmation retry idempotent
PASS 7 availability revalidated at confirmation
PASS 8 expired proposal rejected
RESULT 8 proposal MySQL checks.
PASS 1 clinical attachment metadata persisted
PASS 2 attachment encrypted outside public
PASS 3 authorized attachment decrypted
PASS 4 download audited
PASS 5 nonclinical role denied attachment
PASS 6 content MIME verified, filename not trusted
RESULT 6 private attachment MySQL checks.
PASS 1 no consent suppresses reminder
PASS 2 repeat processing is idempotent
PASS 3 duplicate real MySQL queue jobs persisted
RESULT 3 reminder setup assertions; worker verification follows.
PASS real database worker processed duplicate jobs exactly once; no external delivery.
PASS 1 /clients/tables HTTP 200
PASS 2 real system catalogs retain Tattoo and add independent modules
PASS 3 /clients/record/2 HTTP 200
PASS 4 superadmin actual update disables AiReception
PASS 5 disabled tenant blocked despite prior authenticated session
PASS 6 independent Odontologia remains enabled
PASS 7 traditional documents retained after module change
PASS 8 superadmin actual update enables AiReception
PASS 9 enabled tenant regains access
PASS 10 traditional company denied new modules
PASS 11 unauthorized business rejected /odontologia
PASS 12 unauthorized business rejected /tattoo-appointments
PASS 13 unauthorized business rejected /tattoo-appointments
PASS 14 copied tenant session cannot authenticate another business
PASS 15 /aireception/api/bootstrap HTTP 200
PASS 16 /aireception/api/bootstrap HTTP 200
PASS 17 Hyn identifies separate businesses
PASS 18 tattoo catalog excludes dental professionals
PASS 19 /aireception/api/availability?professional_id=1&service_id=1&day=2026-12-10 HTTP 200
PASS 20 HTTP availability
PASS 21 HTTP persistent booking
PASS 22 HTTP idempotent retry
PASS 23 /odontologia/api/calendar?day=2026-12-10 HTTP 200
PASS 24 Odontologia and AiReception read same canonical booking
PASS 25 /aireception/api/calendar?day=2026-12-10 HTTP 200
PASS 26 other tenant cannot read dental booking
PASS 27 authorized HTTP cancellation
PASS 28 original tattoo.localhost:8876 /dashboard
PASS 29 original tattoo.localhost:8876 /documents
PASS 30 original tattoo.localhost:8876 /pos
PASS 31 original tattoo.localhost:8876 /persons/customers
PASS 32 original dental.localhost:8876 /dashboard
PASS 33 original dental.localhost:8876 /documents
PASS 34 original dental.localhost:8876 /pos
PASS 35 original dental.localhost:8876 /persons/customers
PASS 36 original comercio.localhost:8876 /dashboard
PASS 37 original comercio.localhost:8876 /documents
PASS 38 original comercio.localhost:8876 /pos
PASS 39 original comercio.localhost:8876 /persons/customers
PASS 40 TattooInbox existing interface retained
PASS 41 unlicensed business cannot open original Inbox
PASS 42 unsigned legacy webhook fails closed in native lab
RESULT 42 native HTTP assertions
PASS 1 native superadmin plan screen
PASS 2 superadmin restricts existing plan
PASS 3 plan denial enforced in tenant API
PASS 4 independent dental plan capability retained
PASS 5 plan restored
PASS 6 plan does not grant unselected module
PASS 7 subscription suspended
PASS 8 suspension enforced for new modules
PASS 9 original invoicing unaffected by add-on suspension
PASS 10 tenant cannot change commercial policy
PASS 11 reactivation restores authorized access
RESULT 11 plan/subscription HTTP assertions
PASS tattoo original MultiFactura login and sidebar
PASS native calendar keeps original shell
PASS browser creates real native booking
PASS browser reschedules persisted native booking
PASS browser cancels native booking
PASS dental original MultiFactura login and sidebar
PASS dental administration uses original shell
PASS simulated agent creates persistent dental calendar booking
PASS comercio original MultiFactura login and sidebar
PASS traditional business has no unauthorized new menus
PASS traditional POS screen retained
PASS system original MultiFactura login and sidebar
PASS new modules default to opt-in in create form
PASS all six business presets retained
PASS independent native module selectors in original create form
JS_ERRORS []
RESULT 15 real Chrome assertions
PASS dental original MultiFactura login and sidebar
PASS service name comes from existing MultiFactura item
PASS duration configuration persists from native browser
PASS no settings JavaScript exceptions
RESULT 4 real settings Chrome assertions
PASS dental original MultiFactura login and sidebar
PASS monthly native calendar
PASS clinical persistence in original shell
PASS cuarta original MultiFactura login and sidebar
PASS fourth company native modules visible
PASS no V4 JavaScript exceptions
RESULT 6 V4 real Chrome assertions
PASS all native local suites. No external providers.
```

## webhook-http-tests.log

```text
PASS 1 signed webhook queued
PASS 2 message ID deduplicated before dispatch
Invalid signature HTTP 403
PASS 3 invalid HMAC rejected
PASS 4 unknown registered channel rejected
PASS 5 signed wrong business channel rejected
PASS 6 signed wrong WABA rejected
RESULT 6 webhook HTTP assertions; synthetic signed events only
```

## attachment-tests.log

```text
PASS 1 clinical attachment metadata persisted
PASS 2 attachment encrypted outside public
PASS 3 authorized attachment decrypted
PASS 4 download audited
PASS 5 nonclinical role denied attachment
PASS 6 content MIME verified, filename not trusted
RESULT 6 private attachment MySQL checks.
```

## proposal-tests.log

```text
PASS 1 proposal does not reserve
PASS 2 explicit confirmation required
PASS 3 tenant injection rejected
PASS 4 proposal bound to authenticated actor
PASS 5 confirmed only after real backend write
PASS 6 confirmation retry idempotent
PASS 7 availability revalidated at confirmation
PASS 8 expired proposal rejected
RESULT 8 proposal MySQL checks.
```

## plan-http-tests.log

```text
PASS 1 native superadmin plan screen
PASS 2 superadmin restricts existing plan
PASS 3 plan denial enforced in tenant API
PASS 4 independent dental plan capability retained
PASS 5 plan restored
PASS 6 plan does not grant unselected module
PASS 7 subscription suspended
PASS 8 suspension enforced for new modules
PASS 9 original invoicing unaffected by add-on suspension
PASS 10 tenant cannot change commercial policy
PASS 11 reactivation restores authorized access
RESULT 11 plan/subscription HTTP assertions
```


# Tercer bloque: verificacion ejecutada

Regresion `native-lab/run-tests.ps1`: exit 0, todas las suites locales incluidas pasaron. Proveedores externos desactivados.

## v4-block3-regression.log

```text
PASS syntax: 216 PHP files
PASS 1 duration derived from PCM bytes
PASS 2 transcript is explicitly untrusted
PASS 3 unverified compressed format rejected
PASS 4 truncated file rejected
PASS 5 duration limit enforced
PASS 6 size limit before provider
PASS 7 invalid files never reach provider
RESULT 7 audio mock checks; no network.
PASS 1 structured tool proposal
PASS 2 usage recorded in result
PASS 3 no provider storage or redirects
PASS 4 disabled provider
PASS 5 untrusted system instructions rejected
PASS 6 unregistered tool rejected
PASS 7 malformed arguments
PASS 8 provider timeout sanitized
PASS 9 rate limit is not retried blindly
PASS 10 official message response identifier
PASS 11 consent required
PASS 12 24 hour boundary
PASS 13 future message timestamp rejected
PASS 14 approved template outside window
PASS 15 unapproved template
PASS 16 channel path injection
PASS 17 ambiguous delivery sanitized, no blind retry
RESULT 17 provider contract assertions; mocks only, no network.
OK: 4 pruebas de clasificacion, privacidad y manejo de errores.
OK: 12 verificaciones Meta webhook, firma y aislamiento, sin tráfico real.
OK: 9 defensive validation checks (fake HTTP only).
OK: 26 comprobaciones SlotPlanner.
OK: 18 Tattoo adapter checks (fixtures; no database or network).
OK (6 tests, 20 assertions)
OK (1 test, 7 assertions)
PASS 1 availability respects configured duration
PASS 2 break excluded
PASS 3 closed Sunday
PASS 4 pending persisted with duration snapshot
PASS 5 idempotent retry returns original
PASS 6 idempotency payload mismatch
PASS 7 occupied slot rejected
PASS 8 confirmation persists
PASS 9 reschedule uses duration snapshot
PASS 10 stale version rejected
PASS 11 cancellation persists
PASS 12 complete write audit
PASS 13 viewer cannot write
PASS 14 viewer can read
PASS 15 cross-company context rejected
PASS 16 foreign or missing professional rejected
PASS 17 two real MySQL workers: one professional, exactly one booking
PASS 18 simultaneous same idempotency key creates once
PASS 19 two real MySQL workers: different professionals, one chair
PASS 20 disabled company denied despite assigned user permissions
PASS 21 external AI and Meta remain disabled
PASS 22 Hyn database credentials cannot read another tenant
PASS 23 different service duration changes offered slots
PASS 24 dated exception closes availability
RESULT 24 real native Laravel/MySQL checks
PASS 1 legacy unknown duration closes entire day
PASS 2 unknown previous-day duration cannot leak availability
PASS 3 legacy cancellation releases native availability
PASS 4 legacy writer rejects canonical occupied day under lock
PASS 5 original artist retained
PASS 6 original TattooInbox schema retained
RESULT 6 real legacy compatibility checks
PASS 1 fixture dental context
PASS 2 independent clinical permission exists
PASS 1 proposal does not reserve
PASS 2 explicit confirmation required
PASS 3 tenant injection rejected
PASS 4 proposal bound to authenticated actor
PASS 5 confirmed only after real backend write
PASS 6 confirmation retry idempotent
PASS 7 availability revalidated at confirmation
PASS 8 expired proposal rejected
PASS 9 assisted booking origin stored
PASS 10 AI cancellation cannot target another customer
PASS 11 rescheduling proposal does not change booking
PASS 12 confirmed proposal reschedules through booking engine
PASS 13 cancellation proposal waits for confirmation
PASS 14 confirmed cancellation idempotent
PASS 15 old successful proposal cannot report stale state
RESULT 15 proposal MySQL checks.
PASS 1 clinical attachment metadata persisted
PASS 2 attachment encrypted outside public
PASS 3 authorized attachment decrypted
PASS 4 download audited
PASS 5 nonclinical role denied attachment
PASS 6 content MIME verified, filename not trusted
RESULT 6 private attachment MySQL checks.
PASS 1 provider credential encrypted
PASS 2 global external disable prevents all provider calls
PASS 3 counted generation returns proposal only
PASS 4 idempotent retry uses encrypted response without API
PASS 5 actual usage settled from explicit fake tariff
PASS 6 provider failure sanitized
PASS 7 ambiguous paid request retains budget reservation
PASS 8 ambiguous generation never retried automatically
PASS 9 budget exhausted blocks further generation
RESULT 9 budgeted provider MySQL/mock checks; zero external traffic.
PASS 1 deterministic reply identified as simulation
PASS 2 conversation does not create appointment
PASS 3 turn idempotency no duplicate messages
PASS 4 idempotency key rejects different payload
PASS 5 message encrypted at rest
PASS 6 clinical request routed to human without provider
PASS 7 human pause persists on next message
PASS 8 tool rejects tenant supplied by model
PASS 9 unknown model tool rejected
PASS 10 cancellation intent precedes generic appointment intent
PASS 11 conversation cannot switch customer identity
RESULT 11 conversation MySQL checks; providers disabled.
PASS 1 no consent suppresses reminder
PASS 2 repeat processing is idempotent
PASS 3 duplicate real MySQL queue jobs persisted
RESULT 3 reminder setup assertions; worker verification follows.
PASS real database worker processed duplicate jobs exactly once; no external delivery.
PASS 1 /clients/tables HTTP 200
PASS 2 real system catalogs retain Tattoo and add independent modules
PASS 3 /clients/record/2 HTTP 200
PASS 4 superadmin actual update disables AiReception
PASS 5 disabled tenant blocked despite prior authenticated session
PASS 6 independent Odontologia remains enabled
PASS 7 traditional documents retained after module change
PASS 8 superadmin actual update enables AiReception
PASS 9 enabled tenant regains access
PASS 10 traditional company denied new modules
PASS 11 unauthorized business rejected /odontologia
PASS 12 unauthorized business rejected /tattoo-appointments
PASS 13 unauthorized business rejected /tattoo-appointments
PASS 14 copied tenant session cannot authenticate another business
PASS 15 /aireception/api/bootstrap HTTP 200
PASS 16 /aireception/api/bootstrap HTTP 200
PASS 17 Hyn identifies separate businesses
PASS 18 tattoo catalog excludes dental professionals
PASS 19 /aireception/api/availability?professional_id=1&service_id=1&day=2026-12-10 HTTP 200
PASS 20 HTTP availability
PASS 21 HTTP persistent booking
PASS 22 HTTP idempotent retry
PASS 23 /odontologia/api/calendar?day=2026-12-10 HTTP 200
PASS 24 Odontologia and AiReception read same canonical booking
PASS 25 /aireception/api/calendar?day=2026-12-10 HTTP 200
PASS 26 other tenant cannot read dental booking
PASS 27 authorized HTTP cancellation
PASS 28 original tattoo.localhost:8876 /dashboard
PASS 29 original tattoo.localhost:8876 /documents
PASS 30 original tattoo.localhost:8876 /pos
PASS 31 original tattoo.localhost:8876 /persons/customers
PASS 32 original dental.localhost:8876 /dashboard
PASS 33 original dental.localhost:8876 /documents
PASS 34 original dental.localhost:8876 /pos
PASS 35 original dental.localhost:8876 /persons/customers
PASS 36 original comercio.localhost:8876 /dashboard
PASS 37 original comercio.localhost:8876 /documents
PASS 38 original comercio.localhost:8876 /pos
PASS 39 original comercio.localhost:8876 /persons/customers
PASS 40 TattooInbox existing interface retained
PASS 41 unlicensed business cannot open original Inbox
PASS 42 unsigned legacy webhook fails closed in native lab
RESULT 42 native HTTP assertions
PASS 1 native superadmin plan screen
PASS 2 superadmin restricts existing plan
PASS 3 plan denial enforced in tenant API
PASS 4 independent dental plan capability retained
PASS 5 plan restored
PASS 6 plan does not grant unselected module
PASS 7 subscription suspended
PASS 8 suspension enforced for new modules
PASS 9 original invoicing unaffected by add-on suspension
PASS 10 tenant cannot change commercial policy
PASS 11 reactivation restores authorized access
RESULT 11 plan/subscription HTTP assertions
PASS 1 native page /aireception/inbox
PASS 2 unlicensed route rejected /aireception/inbox
PASS 3 native page /aireception/channels
PASS 4 unlicensed route rejected /aireception/channels
PASS 5 native page /aireception/agent-settings
PASS 6 unlicensed route rejected /aireception/agent-settings
PASS 7 native page /aireception/overview
PASS 8 unlicensed route rejected /aireception/overview
PASS 9 native page /aireception/conversations
PASS 10 unlicensed route rejected /aireception/conversations
PASS 11 agent authenticated HTTP
PASS 12 simulation explicitly labeled
PASS 13 HTTP retry returns same conversation result
PASS 14 HTTP key reuse mismatch denied
PASS 15 HTTP human handoff
PASS 16 HTTP pause respected
PASS 17 unlicensed business cannot consume agent
RESULT 17 agent HTTP checks; no external providers
PASS tattoo original MultiFactura login and sidebar
PASS native calendar keeps original shell
PASS browser creates real native booking
PASS browser reschedules persisted native booking
PASS browser cancels native booking
PASS dental original MultiFactura login and sidebar
PASS dental administration uses original shell
PASS agent requires explicit confirmation before booking
PASS simulated agent creates persistent dental calendar booking
PASS comercio original MultiFactura login and sidebar
PASS traditional business has no unauthorized new menus
PASS traditional POS screen retained
PASS system original MultiFactura login and sidebar
PASS new modules default to opt-in in create form
PASS all six business presets retained
PASS independent native module selectors in original create form
JS_ERRORS []
RESULT 16 real Chrome assertions
PASS dental original MultiFactura login and sidebar
PASS service name comes from existing MultiFactura item
PASS duration configuration persists from native browser
PASS no settings JavaScript exceptions
RESULT 4 real settings Chrome assertions
PASS dental original MultiFactura login and sidebar
PASS monthly native calendar
PASS clinical persistence in original shell
PASS cuarta original MultiFactura login and sidebar
PASS fourth company native modules visible
PASS no V4 JavaScript exceptions
RESULT 6 V4 real Chrome assertions
PASS all native local suites. No external providers.
```
## v4-new-screens.log

```text
PASS dental original MultiFactura login and sidebar
PASS monthly native calendar
PASS clinical persistence in original shell
PASS native agent-settings renders
PASS native channels renders
PASS native inbox renders
PASS native overview renders
PASS native automations renders
PASS cuarta original MultiFactura login and sidebar
PASS fourth company native modules visible
PASS no V4 JavaScript exceptions
RESULT 11 V4 real Chrome assertions
```

## Comprobaciones posteriores dirigidas
- 128 archivos PHP: sintaxis PASS.
- Propuestas MySQL: 15 PASS (incluye cancelacion/reprogramacion, origen asistido y rechazo de resultado obsoleto).
- Conversaciones MySQL: 11 PASS; presupuesto proveedor MySQL+mock: 9 PASS.
- Identidad de contacto MySQL: 5 PASS.
- Webhook HTTP sintetico: 10 PASS; procesamiento real de cola y aislamiento MySQL: 12 PASS.
- Meta parser/firma standalone ampliado: 16 PASS.
- Reglas recordatorios: 4 PASS; tick dedicado ejecutado, sin trafico externo.
- Chrome ampliado V4: 11 PASS, nuevas capturas 20-24; sin errores JavaScript. La primera corrida adicional encontro espera fragil por nota repetida; se corrigio usando nota unica y layout cargado, se repitio satisfactoriamente. No se desactivo ninguna prueba.

## No aprobado / no ejecutado
API OpenAI real, Meta sandbox externo, transcripcion OGG/Opus, salida de WhatsApp, E2E externo, cobros, SUNAT, uso clinico real, despliegue, regresion exhaustiva de cada modulo historico. Auditoria Composer encontro avisos criticos; no se considera una suite de seguridad aprobada.

Ultima correccion de acceso: 13 checks de conversaciones PASS, incluyendo rechazo de usuario bloqueado aunque tenga modulo/nivel asignado. No se modifica su registro real: el test usa el objeto de usuario ficticio del proceso.
