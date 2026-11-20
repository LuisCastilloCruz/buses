# AiReception (fase 0, DESACTIVADO)

**Actualización HP:** consultar [INTEGRATION_HP.md](INTEGRATION_HP.md) y el informe
`audit/20261120/INFORME.md`. Las notas siguientes describen la fundación original;
la integración local añade un adaptador de lectura y pruebas sin activar el módulo.

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

## Avance del 20/11/2026: calculadora de disponibilidad

Archivo: `Services/SlotPlanner.php` (PHP 7.4 compatible, sin dependencias externas).

- Calcula espacios libres por empresa, profesional y duracion de servicio.
- Acepta horarios separados (incluida pausa de almuerzo), cierres y horarios excepcionales.
- Respeta tiempo minimo de aviso, intervalo entre opciones y margenes antes/despues de citas.
- No bloquea espacios entre empresas ni entre profesionales.
- Excluye citas confirmadas y pendientes; ignora las canceladas.
- Solo calcula disponibilidad: **no autoriza usuarios, no consulta datos, no reserva**.
- El adaptador de Laravel debe imponer autenticacion, roles y tenant confiable.
- La escritura real requerira transacciones, locks, idempotencia y controles en la BD.

Prueba reproducible:

```bash
php modules/AiReception/Tests/SlotPlannerTest.php
```

**Resultado local:** 26 comprobaciones OK y sintaxis PHP correcta (entorno de validacion PHP 8.4). El flujo de GitHub Actions `.github/workflows/ai-reception-standalone-tests.yml` esta agregado para comprobar PHP 7.4 y PHP 8.2; **la ejecucion remota no esta aun confirmada**.

No cambiar `active: 0` ni intentar desplegar en produccion antes de conciliar cambios locales de la HP.
