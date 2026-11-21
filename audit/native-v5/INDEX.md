# MultiFactura V5 ? entrega verificable

Base V4: `52acc7a9a0fec09b41559a2472df023abc4663cf`.
Rama: `feature/multifactura-aireception-segura-v5`.
Worktree: `C:/Users/luis castillo/AiReception-lab/native-v5`.

El circuito local firmado ? contrato Responses simulado ? disponibilidad MySQL ? confirmaci?n textual ? reserva transaccional ? outbox ? recordatorio est? probado en Tattoo y Odontolog?a. No se ejecutaron OpenAI ni Meta reales y no se despleg? ning?n componente.

- [Pruebas y evidencias](TEST_RESULTS.md)
- [Manual de configuraci?n y operaci?n](CONFIGURATION.md)
- [Sandbox y bloqueos](SANDBOX.md)
- [Seguridad: 113 avisos individualizados](SECURITY_DEPENDENCIES.md)
- [Matriz de funcionalidades](FUNCTION_MATRIX.md)
- [Estimaciones de costo](COSTS.md)
- [Capturas reales](screenshots/)

V4 permanece en su worktree y commit originales. V5 usa HTTP 127.0.0.1:8877 y MySQL 127.0.0.1:13320, directorio privado y bases mf_v5_*; no comparte bases con V4 ni producci?n. Los gateways de las pruebas HTTP se levantan y cierran exclusivamente en loopback.
