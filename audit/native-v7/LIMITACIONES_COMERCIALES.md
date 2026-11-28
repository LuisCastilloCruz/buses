# Limitaciones comerciales

V7 es un laboratorio, no un producto certificado para venta o producción.

1. Auditoría Composer actual: 117 avisos en 23 paquetes y 15 abandonados. El gateway no elimina los riesgos del monolito Laravel 5.7/PHP 7.4.
2. No está aprobada la coherencia general ni la naturalidad. Falta revisión humana y regresión real de las correcciones finales.
3. La HP no demostró capacidad de inferencia concurrente. No hay ensayo Qwen3 4B, thinking comparado, larga duración ni suspensión real del servicio.
4. El transporte Laravel/gateway/MySQL fue probado con respuesta de modelo simulada; los modelos reales se evaluaron separadamente. Falta cerrar el E2E conjunto con Ollama real y reservas.
5. No hay validación de OpenAI/Meta reales, VPS ni uso clínico profesional/legal.
6. La recuperación documental es léxica, con límite de 100 fuentes administrativas. No incluye embeddings, PDF ni adjuntos clínicos.
7. La personalidad está configurada; parte de las respuestas visibles usa plantillas seguras del backend. No se promete personalización lingüística libre completa.
8. Los adaptadores comerciales de hotel/restaurante/taller y otros giros no se inventaron. Las simulaciones de esos negocios no ejecutaron reservas.

El backend heredado debe permanecer en loopback. No abrir 11434 a Internet ni interpretar el éxito de mocks como autorización productiva.