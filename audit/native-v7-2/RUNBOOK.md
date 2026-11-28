# Arranque y validacion local V7.2

En PowerShell, desde la HP:

```powershell
Set-Location 'C:\Users\luis castillo\AiReception-lab\native-v7-2'
powershell -NoProfile -File native-lab/resume-v6.ps1
```

El nombre heredado resume-v6 esta adaptado a V7.2. Verifica purpose/root y marcador MySQL; no inicializa ni repite migraciones. MySQL127.0.0.1:13327, HTTP127.0.0.1:8882. No reutilizar bases ni credenciales de versiones anteriores.

Abrir http://tattoo.localhost:8882 , http://dental.localhost:8882 o http://comercio.localhost:8882 . Credenciales ficticias privadas: .native-runtime/ACCESS.txt. Superadministrador: http://localhost:8882 . No publicar ese archivo.

```powershell
powershell -NoProfile -File native-lab/run-v72-validation.ps1 -Profile smoke
powershell -NoProfile -File native-lab/run-v72-validation.ps1 -Profile functional
powershell -NoProfile -File native-lab/run-v72-validation.ps1 -Profile e2e
powershell -NoProfile -File native-lab/run-v72-validation.ps1 -Profile browser
powershell -NoProfile -File native-lab/run-v72-validation.ps1 -Profile independent
powershell -NoProfile -File native-lab/run-v72-validation.ps1 -Profile stress
powershell -NoProfile -File native-lab/run-v72-validation.ps1 -Profile recovery
```

Ejecutar secuencialmente; no concurrir E2E con tests que cambian planes/configuracion. independent reanuda omitiendo casos completos ya guardados; para una nueva medicion usar v72-conversations.mjs con --manifest=INDEPENDENT_MANIFEST y --attempt=nuevo-nombre. No confundir relectura con ejecucion nueva. stress genera un nombre con fecha para preservar evidencia. No se ejecuta al iniciar el sistema.

Ollama local qwen3:1.7b, puerto11434 sin exposicion publica, think=false, contexto4096. Guardas:3.25GiB libres en arranque frio,2.3GiB cargado/cola,2GiB durante turnos. No se descargaron modelos grandes. Node26 explicito:C:/Program Files/nodejs/node.exe. PHP7.4.33 y MySQL8.4.3 de Laragon.

El E2E habilita Ollama solo en el proceso de prueba y usa outbox simulada. La configuracion global de autorizacion queda desactivada. No hay envios reales ni VPS. Recuperacion offline apaga solamente el MySQL V7.2 verificado, restaura en13328 y vuelve a iniciar13327. No ejecutar junto con otras pruebas.
