# Resultados ejecutados en la HP

Fecha: 2026-11-20. Comando final: `powershell -NoProfile -File demo-agenda/test.ps1`. Salida 0.

| Categoria | Resultado real |
|---|---|
| Sintaxis PHP | 115 archivos de AiReception/Tattoo/TattooInbox y todos los PHP nuevos de demo-agenda: PASS |
| Sintaxis JavaScript | node --check: PASS |
| Clasificador OpenAI simulado | 4 comprobaciones: PASS; cero llamadas externas |
| Meta simulado | 9 comprobaciones: PASS |
| SlotPlanner puro | 26 comprobaciones: PASS |
| TattooAvailability con fixtures | 18 comprobaciones: PASS |
| Validacion defensiva | 9 comprobaciones: PASS |
| LaravelCompatibilityTest | PHPUnit 7.5.20: 6 tests, 20 assertions: PASS |
| CalendarPersistenceTest | PHPUnit: 1 test, 7 assertions (SQLite en memoria): PASS |
| Nuevo motor Laravel + MySQL 8.4.3 | 31 comprobaciones, incluidas dos carreras multiproceso: PASS |
| HTTP real con sesiones/CSRF | 20 comprobaciones: PASS |
| Navegador Chrome headless real | 11 comprobaciones: PASS; 7 capturas |
| Suite fiscal completa / E2E original Tattoo | NO EJECUTADA: no se cargan bases ni proveedores originales |
| OpenAI/Meta reales | NO EJECUTADOS y desactivados |

El arranque repetido conserva las semillas, contrasenas y reservas. No se actualizaron dependencias. Los logs privados estan en `.agenda-runtime/test-logs`.

## Motor MySQL (salida final)

```text
PASS separate catalogues

PASS cross-business read forbidden

PASS viewer cannot book

PASS foreign professional forbidden

PASS foreign service forbidden

PASS duration and opening margin

PASS lunch and margin

PASS closed Sunday

PASS pending reservation persists

PASS idempotent retry

PASS idempotency payload conflict

PASS occupied rejected

PASS failed command rolled back

PASS sum of appointment margins

PASS confirmation

PASS reschedule availability uses booking snapshot

PASS reschedule preserves explicit original duration

PASS foreign cancellation forbidden

PASS stale version forbidden

PASS authorized cancellation and complete audit

PASS date exception closed

PASS unknown legacy duration blocks day

PASS reception cannot configure

PASS real concurrent MySQL requests: one winner

PASS one persisted concurrent booking

PASS simultaneous idempotent retries replay one booking

PASS future ingress rejects missing or invalid signature

PASS future ingress rejects missing or invalid signature

PASS future ingress accepts valid simulated signature

PASS original disabled provider not activated

PASS only disposable database configured

RESULT 31 assertions passed; actual MySQL 8.4.3
```

## HTTP (salida final)

```text
PASS unauthenticated API denied

PASS session authenticated in business 1

PASS CSRF mandatory

PASS deterministic chat without providers

PASS HTTP availability

PASS booking uses server session, ignores body tenant

PASS HTTP retry idempotent

PASS persisted calendar read

PASS dental calendar cannot read tattoo booking

PASS cross-business audit denied

PASS cross-business mutation denied

PASS unauthorized business switch denied

PASS viewer HTTP write denied

PASS HTTP reschedule

PASS HTTP cancellation

PASS HTTP audit trail

PASS authorized switch isolates business 2

PASS render /calendar

PASS render /agent

PASS render /settings

RESULT 20 HTTP assertions passed
```

## BROWSER (salida final)

```text
PASS browser login and initialized JS calendar

PASS browser creates persistent booking

PASS browser rescheduling and history

PASS browser cancellation

PASS weekly calendar

PASS browser business switch isolates tattoo data

PASS simulated WhatsApp choice creates real dental booking

PASS agent booking visible after navigation

PASS professional working-hours editor

PASS browser saves and reloads working hours

PASS no browser JavaScript exceptions

RESULT 11 real headless Chrome assertions passed
```

## Capturas reales

- [01-availability.png](screenshots/01-availability.png) ? SHA-256 `02c94a3bbab257328bed538a9f0d3cd11656ee63503ce130a792c0bcc1a3b823`
- [02-tattoo-calendar.png](screenshots/02-tattoo-calendar.png) ? SHA-256 `eea8611b761b9adaa8b3860483b9ebb4260c84be758d8680c94362e17b0a2d2c`
- [03-week.png](screenshots/03-week.png) ? SHA-256 `b773ad779204eeb8798aec4a4b060edcd54d476c8e293a34e7718db891354040`
- [04-dental-calendar.png](screenshots/04-dental-calendar.png) ? SHA-256 `1dab5900603fffb43d418a87700c5b431c5d85ce348f2deca0095652af994efb`
- [05-agent.png](screenshots/05-agent.png) ? SHA-256 `fa8cbc0447c2cfda562566bf6fad6691365f26151ed29f78f9b10c9784e160c9`
- [06-dental-booking.png](screenshots/06-dental-booking.png) ? SHA-256 `699b66a781abcae01badad4f2578a9d6a70eddb29d7e8db01f54b1826340e0cf`
- [07-settings.png](screenshots/07-settings.png) ? SHA-256 `e76aceb18c99076f0cc910fb7022a3a2bd52e334d366cbbfd7e636287cfe102f`

Chrome se lanzo con un perfil desechable separado. Las capturas muestran solo datos ficticios. Se inspeccionaron calendario, agente y editor de horarios; no contienen contrasenas.
