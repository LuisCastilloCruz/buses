# Configuración local

Proyecto: `C:\Users\luis castillo\AiReception-lab\native-v7`.

1. Usar el login original de `http://dental.localhost:8879` o `http://tattoo.localhost:8879`. Credenciales ficticias: archivo privado `.native-runtime/ACCESS.txt`; la cuarta empresa también figura en `.native-runtime/accounts.json`.
2. Abrir AiReception → Configuración del agente. Configurar nombre, actividad, saludo, español del Perú, tono, sedes, horarios informativos, políticas y FAQ. No introducir secretos ni información clínica.
3. Abrir Fuentes de conocimiento autorizadas. Revisar y aprobar texto administrativo. Mismo identificador crea una versión nueva; contenido idéntico no duplica versiones. La eliminación borra el contenido de todas las versiones de esa fuente.
4. Los servicios/precios vienen del catálogo; la disponibilidad viene de BookingService. Un perfil no autoriza reservas ni sustituye esos datos.
5. Modo simulado conserva la demostración determinística. Modo local acepta qwen3:1.7b o qwen3:4b, hasta 1024 tokens, temperatura 0–0,7 y contexto 2048/4096. Elegir un modelo no lo descarga.

## Activación local protegida

La configuración privada de Ollama fue preparada y queda desactivada. Primero disponer del margen de RAM documentado. Desde V7 ejecutar:

```powershell
& 'C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe' native-lab/configure-ollama-v7.php dental 1 enable
$env:AR_GATEWAY_CONFIG = Join-Path (Get-Location) '.native-runtime/ollama-gateway.json'
& 'C:\Program Files\nodejs\node.exe' gateway/server.mjs
```

La autorización local caduca en 24 horas; si falla la guarda de recursos no se activa. El gateway usa 127.0.0.1:8898. OpenAI y Meta siguen desactivados. Después seleccionar modo local y habilitar únicamente el negocio ficticio deseado desde el panel.

Esta activación está preparada, pero el E2E final con Ollama real todavía debe validarse. No se debe presentar la demostración simulada como conversación real del modelo.

Para reanudar MySQL/HTTP existentes sin reinstalar: ejecutar `native-lab/resume-v6.ps1` **desde el worktree V7**. Ese archivo reutilizado comprueba purpose V7, MySQL 13323 y HTTP 8879. No ejecutar nuevamente `start.ps1` sin revisar marcadores.