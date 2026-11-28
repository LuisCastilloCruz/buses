# MultiFactura V7.1: continuacion verificada

Estado: laboratorio funcional con reservas completas mediante Ollama real. Calidad comercial y produccion NO aprobadas.

Base V7:44ff5486e6a4898a725f1b8d06f71699d24f428e, preservada limpia. Nuevo worktree `C:\Users\luis castillo\AiReception-lab\native-v7-1`, rama `feature/multifactura-ollama-coherence-v7-1`. Hitos:547e45d2 (estado/E2E),36ec1322 (confirmaciones vigentes/recuperacion). No se modificaron V6,V7,main,produccion ni credenciales de clientes.

## Implementacion

Se reutilizaron BookingService,SlotPlanner,adaptadores Tattoo/Odontologia,login/layout/permisos/Hyn y gateway existentes. Se agregaron DialogueState y DialogueSession cifrado con TTL24h, contexto confiable de identidad, campos pendientes, seleccion y propuesta; validacion de argumentos contra preferencias; invalidacion de propuestas al cambiar fecha/profesional/servicio; confirmaciones ligadas al estado actual; recuperacion de nueva solicitud tras cancelar. El modelo propone herramientas; el backend consulta y escribe bajo sus permisos y transacciones.

Migraciones nuevas000005 (estado cifrado) y000006 (conversation_id en borradores), aplicadas solamente a tenants mf_v71_* mediante marcadores. No se sustituyeron migraciones antiguas ni modulos Tattoo/TattooInbox. Interfaz y facturador originales conservados.

E2E real final:21 comprobaciones dental y21 Tattoo, incluyendo crear, confirmar, reprogramar, cancelar, idempotencia, nueva solicitud y outbox simulada. Chrome20+7 comprobaciones con22 capturas reales. HTTP75. Concurrencia hasta25 escritores, una cita fisica por horario.

## Coherencia y limites

230 conversaciones,1410 turnos,97.09% de herramientas esperadas/autorizadas; reservado99.39%; memoria230/230. No equivalen a97% de naturalidad. Persisten41 errores de enrutamiento y la muestra final de solicitudes repetidas queda85%. No se declara cerrado ese criterio. Rubrica humana sin puntuar en HUMAN_REVIEW.csv. No se entreno con el conjunto reservado.

Solo Qwen3:1.7b evaluado. Modelo4B no descargado; guardas de RAM preservadas. Benchmark6 respuestas cortas con1/2/3 solicitudes, no capacidad de produccion. Ollama nunca expuesto a Internet. Worker privado ensayado localmente; no conexion al VPS.

## Evidencia y uso

- [Resultados](TEST_RESULTS.md)
- [Coherencia por giro y comparacion](MATRIZ_COHERENCIA.md)
- [Metricas](METRICAS.md)
- [Errores y correcciones](ERRORS.md)
- [Pendientes](PENDIENTES.md)
- [Arranque y comandos](RUNBOOK.md)
- [Checkpoint](CHECKPOINT.md)
- [Capturas reales](screenshots/)

Abrir http://localhost:8881 o tattoo.localhost:8881 / dental.localhost:8881 / comercio.localhost:8881. Cuentas ficticias en `.native-runtime/ACCESS.txt`, privado. No reinicializar el laboratorio: `native-lab/resume-v6.ps1` esta adaptado a V7.1 y solo reanuda servicios propios. Para repetir E2E:`native-lab/run-v71-validation.ps1 -Profile e2e`.

Avisos criticos del monolito, revision clinica/legal y sandboxOpenAI/Meta pendientes. No merge, despliegue ni validacion productiva. Publicacion y respaldo final se documentan en ENTREGA.md.
