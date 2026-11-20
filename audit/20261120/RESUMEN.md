# Integración MultiFactura + AiReception en HP

Integración local, sin despliegue y sin cambios en bases de datos existentes.
El informe completo local (con ubicación privada de respaldos y estado remoto
final) se encuentra en `INFORME.md`, excluido de Git deliberadamente.

Base original: `0521b35c013059e50376ccbec9737bcdb5750ed3` (`master` local,
`origin/master` de GitLab y `main` de GitHub al auditar).
Fundación AiReception: `1a28ed75031e4e874d0da619a1f3453343154152`.
Rama publicable: `integration/ai-reception-tattoo-20261120-safe`.

Se preservaron primero 111 archivos locales de código y dependencias en un commit
local y se importaron únicamente los 12 archivos añadidos por AiReception.
La publicación se construye desde el árbol final saneado, con padre `github/main`,
para no reenviar los commits intermedios que contenían copias de secretos heredados.
La rama local original de integración y el respaldo conservan esos estados.
No se reescribió ni publicó `main`, ni se usó push forzado.

## Verificación

Ejecutar en PowerShell desde la raíz:

```powershell
.\audit\20261120\run-local-checks.ps1
```

Resultados registrados en `local-checks.txt`: sintaxis de 101 archivos PHP;
4 escenarios de clasificación, 9 comprobaciones Meta, 26 de SlotPlanner,
18 del adaptador Tattoo y 9 de validación defensiva;
6 pruebas PHPUnit de compatibilidad (20 aserciones) y una prueba de persistencia
de lectura en dos SQLite en memoria (7 aserciones). Todo sin cargar `.env`.

El workflow incluido ejecuta las cinco suites independientes en PHP 7.4 y 8.2.
Las pruebas PHPUnit necesitan las dependencias locales y se ejecutan en la HP.
No se declaró probado el flujo completo HTTP/Laravel, la autenticación de los
módulos previos, MySQL concurrente, reservas reales ni proveedores externos.

## Límites y riesgos

AiReception permanece desactivado y sin endpoints. Solo ofrece clasificación
simulada, validación de webhook y cálculo de disponibilidad; no reserva.
Ver `modules/AiReception/INTEGRATION_HP.md` para contratos, contexto Hyn y pendientes.
No se ejecutaron migraciones. Las migraciones Tattoo duplicadas y la protección
de rutas existentes necesitan una auditoría de ejecución en un entorno desechable.

Se retiraron del árbol publicable el certificado con clave privada y 14 informes
de diagnóstico heredados, conservándolos en el disco local y el respaldo. Se
vaciaron credenciales de plantillas de entorno y se retiró la clave de firma
incrustada en `public/js/sign-message.js`; esta firma requiere un firmante seguro
antes de volver a utilizarse. La consulta JNE exige configuración privada explícita.
IntegratedQuery utiliza ahora los campos de credenciales de Company ya existentes.
Estas integraciones externas no se ejecutaron. Los secretos del historial remoto
preexistente no fueron eliminados: deben tratarse como expuestos y rotarse.

La revisión de secretos es una comprobación por patrones y valores heredados
conocidos, no una certificación de ausencia universal de secretos ni de aptitud
para producción. Nunca publicar la rama local de preservación ni los respaldos.
