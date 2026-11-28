# Arquitectura conservada

Una sola aplicacion Laravel 5.7 con autenticacion y layout originales, Hyn Tenancy y modulos nwidart. AiReception usa BookingService y contratos de adaptacion; Tattoo y Odontologia conservan sus entidades y consumen la agenda compartida. No se reemplazo ningun modulo.

V6 mantiene MySQL aislado 13321 y HTTP 8878. Pool adicional de ensayo: nginx 8888 y cuatro PHP CGI 19061-19064, todos loopback, mismo bootstrap con verificacion de datadir y conexiones externas desactivadas. No es otro SaaS ni despliegue.

Gateway SQLite serializa lectura de idempotencia y transicion dentro de BEGIN IMMEDIATE. StrictTokenGuard mantiene driver token y proveedores originales, rechazando credenciales no textuales antes de consultar MySQL. Web/admin mantienen sesiones originales.
