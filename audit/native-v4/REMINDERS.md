# Recordatorios y ejecucion local

Pantalla `/aireception/automations`: offsets en minutos (por defecto 1440 y 120), hasta cinco valores, pausa y ventana horaria. Los cambios de offsets afectan confirmaciones/reprogramaciones futuras; no reescriben historiales procesados. La pausa y la ventana se eval?an al procesar. Zona del laboratorio America/Lima.

Solo citas confirmadas y consentimiento permitido/no vencido pueden producir resultado `simulated_sent`. Citas canceladas se excluyen, duplicados no generan segundo intento. Ausencia de consentimiento produce `suppressed_no_consent`. Son estados simulados, no entregas Meta.

Desde el worktree V4:
```powershell
$php='C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe'
& $php native-lab/artisan.php aireception:tick --simulate
& $php native-lab/artisan.php queue:work aireception --queue=aireception --once --tries=3 --sleep=0
```
Tick recorre tenants licenciados y preserva contexto Hyn. La cola SYSTEM es aireception_jobs. El worker usa tries=3; no se certifica backoff externo ni entrega exactamente una vez a un proveedor. El procesador local es idempotente. No ejecutar `schedule:run` original ni instalar tareas Windows/servicios productivos. No hay tarea periodica instalada por esta entrega.
