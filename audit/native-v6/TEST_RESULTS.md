# Resultados V6 reproducidos

run-v5-tests.ps1 -Full ejecutado en bases V6: exit 0.

- Sintaxis: 235 PHP. PHPUnit: 6 tests/20 assertions y 1 test/7 assertions.
- MySQL: 24 agenda/seguridad, 6 compatibilidad heredada, 19 V4, 15 propuestas, 6 adjuntos, 9 presupuesto, 13 conversaciones, 4 politica de recordatorios y 3 preparacion; workers y verificacion ejecutados.
- HTTP: 42 nativo, 11 planes/suscripciones, 17 agente; respuesta manual idempotente aprobada.
- Circuito V5: Tattoo 32 y dental 33 checks con proveedores simulados. Diferencia por canal ajeno disponible en fixture.
- Chrome: 16 + 4 + 11 comprobaciones, 26 capturas reales. No acredita toda la facturacion ni que las capturas tempranas del dashboard terminaran de cargar sus datos.
- Cuarta empresa: 6 comprobaciones HTTP luego de alta por Superadministrador.
- Credenciales: original 19 checks/10 fallos; corregido 19/0. Integracion Laravel/MySQL 12/0.
- Audio: FFmpeg 9.1 real, OGG/Opus sintetico, conversion PCM, limite de duracion y limpieza tras timeout simulado. Un test ejecutado, cero llamadas externas.
- Concurrencia: 2/5/10/25 workers, una reserva persistida por horario. Primer intento interferido por suite de suspension; repeticion secuencial aprobada.
- HTTP: ver PERFORMANCE_COMPARISON.md; se conservan fallos monoproceso.

Evidencia detallada privada .native-runtime/*.log y *.json. Soak, restauracion y dashboard completamente cargado se registran por separado al finalizar; no aprobados por adelantado.
