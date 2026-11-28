# Ejecucion y recuperacion V6

Desde C:\Users\luis castillo\AiReception-lab\native-v6, PowerShell:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File native-lab/resume-v6.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File native-lab/run-v6-validation.ps1 -Profile smoke
```

Abrir http://localhost:8878 para Superadministrador; tattoo.localhost:8878, dental.localhost:8878, comercio.localhost:8878 y cuarta.localhost:8878 para empresas ficticias. Credenciales solo en .native-runtime/ACCESS.txt y accounts.json; nunca publicar.

resume-v6 no migra ni siembra. Comprueba configuracion/datadir y marcador existente. No ejecutar start.ps1 para recuperar una interrupcion sin inspeccionar estado. No borrar runtime ni repetir alta cuarta a ciegas.

Perfiles: smoke, functional, integration, security, load, stress, soak, recovery, full. Runner usa lock exclusivo y checkpoints antes/despues; fallos conservan logs. full no declara release completa mientras falte matriz de aceptacion. stress actualmente ejecuta concurrencia de reservas, no miles de eventos.

Pool opcional: native-lab/start-load-v6.ps1; comprobar puertos libres. Carga: run-v6-validation.ps1 -Profile load -Pool. Resistencia: -Profile soak -Pool (600 segundos, no se ejecuta al iniciar la app). Umbrales RAM libre 2 GiB, disco 8 GiB, CPU sostenida 90 por ciento. Nunca destinos externos.

Checkpoints .native-runtime/checkpoints incluyen hashes, branch/commit, pruebas y proximo paso. Un PID guardado no acredita propiedad: verificar ejecutable y fecha de inicio. Concurrencia puede reanudarse con python qa/performance/concurrency.py --resume ID; usa mismas claves e inspecciona reservas persistidas antes de omitir niveles aprobados. Los tests antiguos generan nuevos escenarios, no se reejecutan automaticamente tras un reinicio.
