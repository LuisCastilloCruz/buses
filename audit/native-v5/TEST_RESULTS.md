# Pruebas V5 ejecutadas

Entorno: PHP 7.4.33 local aislado, MySQL 8.4.3 puerto 13320, Node 26.7.0 expl?cito, Chrome real con perfil temporal y solicitudes externas bloqueadas. Todas las personas y empresas son ficticias.

| Suite | Resultado observado |
|---|---|
| gateway/core.test.mjs + http.test.mjs | 14 pruebas aprobadas, incluido HTTP real en loopback |
| v5-flow-tests.php tattoo | 33 comprobaciones MySQL aprobadas |
| v5-flow-tests.php dental | 33 comprobaciones MySQL aprobadas |
| v5-manual-http-tests.py | HTTP real: dos env?os del formulario producen una sola outbox y un solo mensaje |
| diagnose-fourth.php cuarta | Administrador protegido conserva acceso; suspensi?n espec?fica rechaza acceso |
| mysql-tests.php dental | 24 comprobaciones, incluidas 3 carreras con procesos MySQL reales |
| legacy-tests.php tattoo | 6 compatibilidad Tattoo/TattooInbox |
| v4-persistence-tests.php dental | 19 persistencia cl?nica, permisos, presupuesto, calendario |
| proposal-tests.php dental | 15 propuestas, confirmaci?n, cancelaci?n/reprogramaci?n, expiraci?n |
| attachment-tests.php dental | 6 adjuntos cifrados, permisos y MIME |
| budgeted-provider-tests.php dental | 9 presupuesto OpenAI con transporte falso |
| conversation-tests.php dental | 13 contexto, idempotencia, inyecci?n y suspensi?n |
| reminder-policy-tests.php dental | 4 reglas/pausa/quiet hours |
| reminder-tests + worker + reminder-verify | 3 setup + ejecuci?n real de dos jobs con un ?nico procesamiento |
| http-tests.py | 42 HTTP de m?dulos, cookies, reservas y aislamiento |
| plan-http-tests.py | 11 planes/suscripciones |
| agent-http-tests.py | 17 rutas y conversaciones |
| webhook-http-tests.py | 10 firma, dedup, WABA, canal y recibos |
| Chrome browser.mjs | 16 interacciones y layouts originales |
| Chrome browser-settings.mjs | 4 configuraci?n persistente |
| Chrome browser-v4.mjs | 11 pantallas V4 y cuarta empresa, sin excepciones JS |
| Contratos PHP independientes | Audio 7, proveedores 17, clasificador 4, Meta 16, seguridad 9, slots 26, Tattoo 18 |
| PHPUnit compatibilidad/calendario | 7 tests, 27 assertions |

La corrida completa `run-v5-tests.ps1 -Full` termin? con exit 0; despu?s se aprobaron las comprobaciones dirigidas de respuesta manual y sincronizaci?n de estado de recordatorios. Los fallos iniciales se corrigieron y se repitieron las partes afectadas: updateOrInsert vac?o de un fixture, prerequisitos de plan/cita ausentes, fecha de navegador en domingo, reutilizaci?n indebida de una conversaci?n cl?nica de test conflicto entre locked heredado y suspensi?n, y una cola de prueba que compart?a mensajes anteriores. Los recordatorios de test usan ahora una cola espec?fica. Se corrigi? el manejador CLI para que las excepciones de pruebas devuelvan exit 1; no se acept? el antiguo c?digo cero como ?xito.

## Reproducci?n

Desde el worktree V5:

```powershell
./native-lab/start.ps1
./native-lab/run-v5-tests.ps1
# Incluye tambi?n las regresiones heredadas y Chrome:
./native-lab/run-v5-tests.ps1 -Full
```

El alta de cuarta empresa se hace una sola vez con `reserve-fourth.php` y `fourth-company-test.py`; no reintentar ciegamente un alta parcial. `start.ps1` no copia .env ni credenciales; requiere vendor local verificado. Para una instalaci?n nueva, el plan y los fixtures son preparados expl?citamente por `prepare-regression.php`.

## Capturas

26 PNG de ejecuciones Chrome nuevas en `screenshots/`, incluyendo 08 (confirmaci?n y cita guardada), 09 (agenda dental), 19 (cuarta empresa), 20?24 (agente/canal/bandeja/consumo/automatizaciones). Se revis? la captura de recepci?n contra la de V4: se conserva el dise?o. Las capturas documentan la aplicaci?n local, no una conversaci?n en WhatsApp ni una conexi?n externa.

No ejecutado: OpenAI real, WhatsApp real, Meta sandbox remoto, decodificaci?n real OGG/Opus, cobros, SUNAT, pacientes reales, despliegue ni auditor?a exhaustiva de todos los flujos del monolito.
