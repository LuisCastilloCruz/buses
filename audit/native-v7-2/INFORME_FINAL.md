# MultiFactura V7.2: laboratorio validado parcialmente

Base:241e4ce8bf38e3447fb2c97fe32cc723aa57af51. Rama nueva:feature/multifactura-ollama-quality-v7-2. Worktree:C:/Users/luis castillo/AiReception-lab/native-v7-2. V7.1 permanece limpio y en su commit original. No se modificaron produccion ni los otros laboratorios.

## Resultado

Se reprodujeron los nueve fallos heredados (51/60), se corrigieron las decisiones de siguiente paso y se obtuvo60/60 en la segunda iteracion. La regresion de seis dialogos largos afectados aprobo78/78. La evaluacion nueva e independiente obtuvo168/196 (85.71%): NO alcanza95%. Revision linguistica humana pendiente, sin porcentaje subjetivo inventado. Ver MATRIZ_COHERENCIA.md y HUMAN_REVIEW.csv.

Se ejecutaron E2E nativos con Ollama real y MySQL:26 comprobaciones por negocio Tattoo/Odontologia,52 en total. Incluyen disponibilidad, eleccion, confirmacion explicita, persistencia, outbox simulada, cancelacion, reprogramacion, idempotencia y nueva reserva tras cancelar. Recuperar una conversacion utiliza una instancia nueva del servicio; no equivale a reiniciar Windows.

Se completaron seis conversaciones concurrentes (108 turnos) con uno/dos/tres clientes mediante cola persistente y un worker de inferencia serial. La perdida simulada de acuse no repitio inferencia tras reabrir SQLite. Detalles y limites en STRESS_TEST_RESULTS.md.

## Implementacion

Se reutilizaron BookingService, DialogueSession/DialogueState, RequestSelection, RAG, gateway Ollama y worker privado. ReceptionAct clasifica el acto actual; ReceptionTools limita herramientas por capacidades verificadas, campos y estado; DomainRegistry comprueba adaptadores reales; ReceptionReply preserva hechos y avisos clinicos. Un si ambiguo no confirma una cita: solicita el codigo vigente. No hay SQL generado por IA ni nuevo calendario.

No se modifico el layout/login/sidebar. Se conservan24 capturas reales en screenshots/, incluidas conversaciones persistidas de ambos negocios dentro de MultiFactura. No hay nuevas migraciones V7.2: el laboratorio desechable se inicializo con migraciones existentes y credenciales nuevas, sin copiar.env.

## Limites de la evidencia

Las evaluaciones de diez giros usan Ollama real para proponer herramientas y PHP real para estado/autorizacion, con catalogos/documentos/respuestas ficticios controlados. No certifican prosa libre ni reservas reales en esos diez giros. Los E2E MySQL son separados y solo Tattoo/Odontologia. El comercio conserva AiReception deshabilitado. Los otros ocho giros no reciben capacidades transaccionales inventadas.

La personalizacion de tono sigue limitada por respuestas controladas. Persiste texto de estado confirmed en confirmaciones visibles; requiere localizacion. Los28 errores independientes no se ocultaron ni se entrenaron con sus respuestas esperadas. Tres ciclos fundamentados; siguiente version debe mejorar clasificacion semantica informativa y volver a congelar una muestra nueva.

No se validaron OpenAI/Meta reales, uso clinico, VPS, suspension fisica de Windows ni capacidad productiva. Los117 avisos en23 paquetes y15 abandonados son hallazgos heredados, no una auditoria Composer nueva de V7.2. No apto para produccion ni calidad comercial aprobada.

Publicacion:ver GIT_STATUS.md. Ejecucion:RUNBOOK.md. Recuperacion:RECOVERY.md. Resultados:TEST_RESULTS.md.
