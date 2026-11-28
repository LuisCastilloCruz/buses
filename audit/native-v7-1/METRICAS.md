# Recursos y rendimiento observado

GTX1650,4096MiB VRAM. Qwen3:1.7b con contexto4096 observado100%GPU; captura puntual1721MiBVRAM. No se descargo4B ni se actualizaron drivers/Ollama.

Evaluacion1410 turnos: latencia p50=605.13ms,p95=1525.87ms,p99=6358.02ms. Incluye cargas de modelo entre lotes. Guardas3.25GiB en frio,2.3GiB cargado,2GiB corte; descarga cada5 conversaciones.

Benchmark directo de API:solo6 respuestas de12 tokens, contexto2048. Nivel1 con carga inicial6207.50ms (primer texto5958.65ms); nivel2 173.31/334.02ms; nivel3 174.57/336.05/498.81ms. Generacion80.97-82.75tokens/s. El servidor serializa trabajo: tres solicitudes concurrentes no significan tres inferencias GPU paralelas. No es benchmark de conversaciones completas ni promesa de capacidad comercial.

El gateway conserva concurrencia limitada y cola/derivacion; no se amplio para obtener mejores cifras. Los seis resultados no permiten estimar percentiles robustos de carga. No se ejecuto soak prolongado V7.1.

Concurrencia MySQL CLI:2,5,10,25 escritores; exactamente una cita por horario. p95 por nivel:124.05,314.44,648.25,1702.49ms.50 no aprobado: intento de bootstrap excedio conexiones; preflight final lo bloquea por techo64. Se restauro20. No es capacidad HTTP productiva.

Muestras WMI18 durante evaluacion: CPU/RAM/VRAM en RESOURCE_SAMPLES.jsonl; no son monitorizacion continua de toda la ejecucion. Cerrar Opera,Lively,Taskmgr y Settings estuvo autorizado; no se cerraron servicios SQL ni procesos ajenos.

En esas18 muestras: RAM libre min4991MiB,max6083MiB; CPU min16%,max46%.
