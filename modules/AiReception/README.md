# AiReception (fase 0, DESACTIVADO)

Base segura para agregar un recepcionista de IA a la plataforma multiempresa.
Se ha escrito en una rama separada porque la version de GitHub NO incluye
necesariamente los cambios locales posteriores de Tattoo / TattooInbox.

## Estado

- `module.json` tiene `active: 0`: no se registra en produccion.
- No tiene endpoints HTTP, migraciones, colas, cron ni webhooks.
- No usa datos de pacientes ni modifica citas.
- El clasificador clasifica la intencion; NO envia mensajes al cliente.
- Una futura capa autenticada debe resolver el tenant antes de consultar OpenAI.
- La clave `OPENAI_API_KEY` se configura mediante variables de entorno; nunca en Git.

## Prueba sin costos y sin Internet

```bash
php modules/AiReception/Tests/IntakeClassifierSmokeTest.php
```

El cliente HTTP se simula; no genera cargos de OpenAI ni Meta.

## Siguiente desarrollo (solo tras recuperar cambios de la HP)

1. Conciliar la rama con los modulos locales `Tattoo` y `TattooInbox` y la version del servidor.
2. Verificar aislamiento Hyn Tenancy y mapeo numero WhatsApp -> empresa autenticada.
3. Crear agenda reutilizable por negocio y profesionales, con control de solapamiento,
   zona horaria, transacciones e idempotencia.
4. Implementar la reserva solo a traves de servicios de dominio autorizados;
   no permitir que el modelo escriba directamente en la base de datos.
5. Integrar WhatsApp webhook firmado, deduplicacion, ventanas y plantillas Meta.
6. Agregar cuotas por empresa, presupuesto OpenAI, reintentos y derivacion humana.
7. Para negocios de salud: consentimiento, minimizacion de PII, retencion,
   auditoria y revision de requisitos legales peruanos.
8. Compatibilidad: el repositorio actual usa Laravel 5.7 y PHP 7.4 declarados;
   evaluar el servicio moderno aislado antes de exponer datos reales.

## Requisitos de seguridad

- No activar este modulo ni desplegarlo hasta probar en staging.
- No reutilizar un token global de WhatsApp entre empresas.
- No asumir que las agendas de Tattoo se encuentran ya en esta rama.
- No cargar historiales medicos en pruebas de clasificacion.
- Revisar la situacion de licencia de la base antes de comercializar:
  LICENSE indica MIT y readme.md incluye condiciones comerciales adicionales.

Referencia de API: https://platform.openai.com/docs/api-reference/responses
