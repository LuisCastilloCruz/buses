# Resultados ejecutados V7.2

| Suite | Resultado | Alcance |
|---|---|---|
| Regresion original |51/60 inicial,58/60,60/60 |Ollama real, mismas expectativas |
| Dialogos largos afectados |78/78 |6 conversaciones de desarrollo |
| Independiente |168/196; no aprobado |12 conversaciones,10 giros |
| E2E nativo |26 dental+26 Tattoo |Ollama real, MySQL, outbox simulada |
| Concurrencia conversacional |108/108 turnos pertinentes |6 conversaciones completas, worker serial |
| Recuperacion worker |aprobada |acuse perdido, SQLite reabierto, sin inferencia duplicada |
| Politicas PHP V72 |13 |reglas de herramientas/presentacion |
| Estado PHP V71 |15+2 nueva solicitud |regresion reutilizada ejecutada |
| Smoke unitario |15 |PHP |
| Gateway |41;13 pruebas dirigidas |mocks, no validacion linguistica |
| Persistencia V7 |20 |MySQL/RAG/aislamiento |
| Estado MySQL |8 |memoria |
| Borradores MySQL |6 |confirmacion |
| Recuperacion canal |4 |persistencia |
| Flujos V5 |33 Tattoo+33 dental |proveedores simulados, permisos, colas |
| HTTP |42+17+16 |75 comprobaciones |
| Planes HTTP |11 |fixtures desechables |
| Perfil/documentos/aislamiento V72 |14 |6 dental+6 Tattoo+2 comercio |
| Chrome |20+9 |panel original, modulos, conversaciones reales |
| Sintaxis |37 PHP sin errores |SYNTAX.json |
| Restauracion |4 bases,1083 tablas |conteos y digests exactos |

No se suman estas suites como una tasa global: algunas se superponen. Los resultados anteriores V6/V7.1 no cuentan sin ejecucion. No se repitio en V7.2 una carga de50 reservas simultaneas ni una resistencia de60min. El estres de esta entrega es conversacional acotado, no capacidad productiva.

Evidencia publica:JSON de conversaciones, E2E_dental/E2E_tattoo, STRESS_METRICS.json, screenshots. Logs privados del runtime no se publican. Las salidas de suites auxiliares fueron observadas en esta sesion; no todas generaron artefactos individuales persistentes.
