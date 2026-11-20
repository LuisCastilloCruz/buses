# AiReception — Avance verificable 20/11/2026

## Entregables

- El módulo `AiReception` sigue desactivado y no añade rutas de producción.
- `Services/OpenAiIntakeClassifier.php`: clasificador de intención de solo lectura.
- `Services/MetaWebhook.php`: verificación del challenge, firma HMAC SHA-256 y resolución de empresa mediante identificador de número de teléfono conocido.
- `Tests/MetaWebhookMockTest.php`: pruebas sin conexión externa.
- **Paquete ZIP de demostración local** entregado en la conversación del 20/11/2026, separado de este repositorio y NO desplegado.

## Resultados de ejecución en entorno aislado (PHP 8.4)

- Motor de citas: 33 pruebas correctas.
- API HTTP local: 20 verificaciones correctas.
- Integración OpenAI con respuesta falsa: 3 verificaciones correctas.
- Validación del webhook Meta con cargas ficticias: 9 verificaciones correctas.
- TOTAL: 65 verificaciones correctas.
- Validación de sintaxis PHP del servidor de prueba y JS mediante Node.
- Se verificó la integridad del archivo ZIP.
- No se validó interfaz visual en un navegador real: el intento con Chromium se bloqueó por el entorno.

**Los 65 controles son sobre el prototipo local y mocks, NO sobre el producto Laravel de producción.**

## Qué ofrece la demo ZIP (no es un módulo instalable directamente)

- Dos negocios ficticios independientes: tatuajes y odontología.
- Listado de servicios, profesionales, horarios y reservas.
- Registrar, cancelar, impedir solapamientos y repetir solicitudes sin duplicados.
- Recepcionista de reglas determinísticas y opción de OpenAI real **apagada por defecto**.
- Almacenamiento JSON con bloqueos de archivo. Solo es apto para demostración local.
- Un servidor PHP disponible en `127.0.0.1:8791` y archivos HTML/CSS/JS sin dependencias.

## Qué falta para puesta en producción

1. Recuperar y reconciliar cambios locales no publicados de `Tattoo` / `TattooInbox`.
2. Auditar tenant DB, autorizaciones y privacidad del servidor en marcha.
3. Convertir el demo en módulos Laravel migrables, con tablas específicas por tenant y políticas de acceso.
4. Añadir transacciones y claves únicas en base de datos para citas concurrentes.
5. Alta y verificación de teléfonos oficiales por negocio; webhooks públicos seguros y procesamiento idempotente en cola.
6. Audio, gestión de sesiones, consentimiento, recordatorios y transferencias a agentes.
7. Pruebas reales con OpenAI, Meta sandbox y staging, load tests y revisión legal.
8. Plan de compatibilidad de Laravel/PHP y licencia del código base antes de vender a terceros.

## Seguridad

No ejecutar el demo en interfaces públicas; contiene datos ficticios solamente, carece de autenticación de usuarios y no sustituye las políticas de acceso de Hyn Tenancy. Mantener `active: 0`. No subir credenciales ni archivos de datos locales.
