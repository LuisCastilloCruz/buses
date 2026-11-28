# Ejecucion V7.1 en HP

Directorio: C:\Users\luis castillo\AiReception-lab\native-v7-1.
PHP7.4: C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe.
Node26: C:\Program Files\nodejs\node.exe (no usar el Node22 de nvm del PATH para el gateway SQLite).

El laboratorio ya esta instalado. No inicializar otra vez. Para reanudar servicios propios: .\native-lab\resume-v6.ps1 (nombre historico; su contenido verifica V7.1, puerto13325 y HTTP8881; no migra).

URLs: http://localhost:8881, http://tattoo.localhost:8881, http://dental.localhost:8881, http://comercio.localhost:8881. Credenciales ficticias en .native-runtime/ACCESS.txt, nunca en Git.

Runner: .\native-lab\run-v71-validation.ps1 -Profile smoke|functional|browser|e2e|coherence|worker|benchmark.
Para casos reservados, despues de congelar codigo: & 'C:\Program Files\nodejs\node.exe' qa/ai-reception-evaluation/v71-coherence.mjs --run=coherence-iteration-3 --held-out.
Para reanudar desarrollo: mismo comando sin --held-out. Los JSON completos se conservan y se omiten; no confundir resultados parciales con230 conversaciones aprobadas.

Antes del primer E2E sobre una instalacion nueva, configurar el plan ficticio desde Superadministrador (runner reproducible: python -X utf8 native-lab/plan-http-tests.py). El runner e2e prepara identidades ficticias y configuracion privada nueva, abre temporalmente solo8899 en loopback y lo cierra al terminar. OpenAI/Meta siguen apagados y outbox simulada. Los ajustes de agente se restauran al terminar el test.

Una reserva requiere el codigo explicito mostrado por backend. Un si ambiguo no confirma. No copiar .env, tokens o bases de clientes. No abrir Ollama a Internet. No interpretar estos ensayos como aprobacion productiva.
