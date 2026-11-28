# Resultados reales V7.1

Fecha:2026-11-28. Todas las bases son ficticias y todos los procesos locales. No se atribuyen a V7.1 pruebas antiguas sin reproducir.

| Suite | Resultado | Alcance |
|---|---|---|
| Sintaxis PHP cambiados |37 archivos,0 errores|SYNTAX_FINAL.json|
| Unitarias heredadas V7 |15 aprobadas|perfil smoke|
| Estado V7.1 |15 aprobadas|v71-state-tests.php; la linea RESULT8 antecede7 aserciones adicionales|
| Nueva solicitud |2 aprobadas|regresion tras cancelacion|
| Persistencia memoria/RAG/roles |20 aprobadas|MySQL, transporte inferencia simulado|
| Estado cifrado/identidad/TTL |8 aprobadas|MySQL dental|
| Token ligado a conversacion |6 dental+6 Tattoo|MySQL real; token obsoleto rechazado; replay|
| Recuperacion conversacional |4 aprobadas|canal real, proveedor mock|
| Flujo heredado canales |33 Tattoo+33 dental|MySQL, Meta/OpenAI simulados|
| Gateway y worker |41 aprobadas|SQLite, HTTP loopback, mocks;650 eventos sinteticos|
| E2E Ollama final |21 dental+21 Tattoo|modelo real, BookingService/MySQL, outbox simulada|
| Coherencia diez giros |1369/1410;230 conversaciones|modelo real; backend de disponibilidad fixture|
| Evaluacion reservada |328/330;50 conversaciones|no usada para ajustar instrucciones|
| Regresion final diez giros |51/60;10 conversaciones|fallos residuales conservados; NO95%|
| Memoria final |230/230|servicio/profesional/fecha; auditoria deterministica|
| HTTP |42+17+16 aprobadas|login, modulos, permisos, agente y conocimientos|
| Chrome |20+7 aprobadas|5 fases nativas mas dashboards completamente cargados;22 PNG reales|
| Concurrencia MySQL |2/5/10/25 aprobados|una reserva persistida por horario;50 bloqueado, no aprobado|
| Inferencia simultanea |1/2/3,6 respuestas cortas|modelo real, no benchmark de conversaciones completas|

Fallos iniciales conservados en VALIDATION_RUNS.json, E2E_dental_*.json y ERROR(S).md: fixture user3 ausente; configuracion local del agente no restaurada; bootstrap de25/50 workers por conexiones; nueva cita tras cancelar detenida en resumen. Los reintentos exitosos no borran esas evidencias.

E2E final: E2E_dental_20261128_032735.json y E2E_tattoo_20261128_032751.json. El backend verifica servicio/profesional/fecha/hora, exige codigo explicito, verifica idempotencia y person/business en MySQL. Ninguna escritura SQL procede del modelo. Las respuestas se encolan, pero no se envia WhatsApp real.

Chrome usa perfil nuevo, headless real y bloquea peticiones fuera de localhost. Capturas iniciales de menus pueden mostrar placeholders por AJAX;25-loaded-dashboard-* demuestra carga finalizada. No se afirma regresion fiscal/SUNAT ni exhaustividad de todos los modulos.

Pendiente:revision humana de naturalidad, proveedores externos,50 workers,soak prolongado,calidad comercial y mitigacion de vulnerabilidades heredadas. No produccion.
