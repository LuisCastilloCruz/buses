# Worker privado futuro

No se conectó ningún VPS. Ollama continúa en loopback y no debe publicar 11434.

Arquitectura preparada: MultiFactura conserva el panel y operaciones autorizadas; el gateway mantiene trabajos cifrados; la HP solicita trabajo mediante conexión HTTPS saliente firmada. El worker solo devuelve propuestas del modelo. BookingService permanece en el backend.

Archivos: gateway/inference-jobs.mjs, private-worker.mjs y run-private-worker.mjs. Operaciones de cola exigen capacidades `inference_submit` o `inference_worker` además del tenant y canal permitidos. El worker usa un único archivo de estado para serializar procesos y guardar resultados antes del acuse.

Si se interrumpe antes de empezar, el lease puede recuperarse. Si se interrumpe después de empezar, queda incierto y no se repite automáticamente. Un resultado ya guardado se vuelve a acusar sin regenerarlo. No produce confirmaciones de citas.

La configuración privada requiere purpose `multifactura-v7-private-worker`, authorized=true, origin HTTPS, client, secret, tenant, channel, model, database absoluto y encryptionKey de 32 bytes en base64. No incluirla en Git. El origen debe ser una infraestructura privada autorizada con TLS válido; no usar certificados inseguros ni redirects.

Ejecución manual futura: establecer AR_PRIVATE_WORKER_CONFIG a ese archivo y ejecutar `node gateway/run-private-worker.mjs`. Cada invocación procesa o concilia un trabajo; no se instaló un servicio ni tarea Windows.

Pendiente antes de conectar: integración completa de cuotas del plan en la cola remota, política de retención/purga de trabajos, operación multi-HP, despliegue TLS privado y ensayo real de caída/recuperación VPS. Los tests actuales usan transporte remoto simulado.