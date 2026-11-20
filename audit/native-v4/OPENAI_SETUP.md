# Integracion OpenAI: implementada con mocks, real pendiente

API oficial Responses `/v1/responses`, conteo `/v1/responses/input_tokens`; transcripcion `/v1/audio/transcriptions`. Fuentes consultadas: https://developers.openai.com/api/reference/resources/responses/methods/create , https://developers.openai.com/api/docs/guides/function-calling , https://developers.openai.com/api/docs/guides/speech-to-text . No se eligio automaticamente un modelo ni una tarifa actual.

Implementado: transporte con timeouts, redirects desactivados, store=false, herramientas estrictas, propuestas separadas de confirmacion, contexto cifrado por conversacion, presupuesto transaccional, tarifa vigente y cache idempotente. Timeout mantiene reserva para conciliacion, no repite generacion ciegamente. Respuestas malformadas/error se derivan a humano. No enviar informacion clinica: el filtro actual es defensivo y NO garantiza deteccion perfecta; requiere evaluacion de privacidad antes de trafico real.

Prerequisitos de validacion real, aun no cumplidos:
1. Resolver riesgos de dependencias aplicables y aprobar entorno aislado con salida HTTPS limitada.
2. Crear proyecto API de pruebas, credencial de minimo alcance, limite de gasto y modelo disponible verificado; no usar credenciales productivas ni pegarlas en chat/Git.
3. Configurar plan y tarifa vigente en Superadmin; clave/modelo en pantalla privada del tenant. Preparar solo datos sinteticos.
4. En una configuracion de sandbox revisada, habilitar explicitamente la bandera externa y permitir transporte HTTP. El bootstrap HP actual fuerza false y bloquea funciones de red: cambiar solo un checkbox no activa llamadas.
5. Ejecutar conversaciones conocidas y comparar herramientas, tokens/costo y reservas reales locales; probar timeout, presupuesto, prompt injection y aislamiento. Conservar auditoria sin secretos.
6. El flujo WhatsApp automatico y salida externa aun no esta unido a este servicio; implementarlo y validar identidad/consentimiento antes del E2E.

Audio: WAV PCM validado por bytes, tamano y duracion; mocks pasan. OGG/Opus de WhatsApp, descarga segura y contabilidad de transcripcion no estan completos. No habilitar llamadas automaticas.
