# Evidencias de pruebas locales

Salidas reales, sin credenciales. La bateria inicial fue seguida por verificaciones del formulario opt-in y de servicios tras recompilar.

## final-tests.log

```text
PASS syntax: 171 PHP files
OK: 4 pruebas de clasificacion, privacidad y manejo de errores.
OK: 9 verificaciones Meta webhook, firma y aislamiento, sin tráfico real.
OK: 9 defensive validation checks (fake HTTP only).
OK: 26 comprobaciones SlotPlanner.
OK: 18 Tattoo adapter checks (fixtures; no database or network).
PHPUnit 7.5.20 by Sebastian Bergmann and contributors.

......                                                              6 / 6 (100%)

Time: 83 ms, Memory: 10.00 MB

OK (6 tests, 20 assertions)
PHPUnit 7.5.20 by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: 103 ms, Memory: 12.00 MB

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
PASS 28 original tattoo.localhost:8875 /dashboard
PASS 29 original tattoo.localhost:8875 /documents
PASS 30 original tattoo.localhost:8875 /pos
PASS 31 original tattoo.localhost:8875 /persons/customers
PASS 32 original dental.localhost:8875 /dashboard
PASS 33 original dental.localhost:8875 /documents
PASS 34 original dental.localhost:8875 /pos
PASS 35 original dental.localhost:8875 /persons/customers
PASS 36 original comercio.localhost:8875 /dashboard
PASS 37 original comercio.localhost:8875 /documents
PASS 38 original comercio.localhost:8875 /pos
PASS 39 original comercio.localhost:8875 /persons/customers
PASS 40 TattooInbox existing interface retained
PASS 41 unlicensed business cannot open original Inbox
PASS 42 unsigned legacy webhook fails closed in native lab
RESULT 42 native HTTP assertions
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
PASS all six business presets retained
PASS independent native module selectors in original create form
JS_ERRORS []
RESULT 14 real Chrome assertions
PASS all native local suites. No external providers.
```

## final-superadmin.log

```text
PASS system original MultiFactura login and sidebar
PASS new modules default to opt-in in create form
PASS all six business presets retained
PASS independent native module selectors in original create form
PASS tattoo original MultiFactura login and sidebar
PASS dental original MultiFactura login and sidebar
PASS comercio original MultiFactura login and sidebar
JS_ERRORS []
RESULT 7 real Chrome assertions
```

## final-settings.log

```text
PASS dental original MultiFactura login and sidebar
PASS service name comes from existing MultiFactura item
PASS duration configuration persists from native browser
PASS no settings JavaScript exceptions
RESULT 4 real settings Chrome assertions
```
