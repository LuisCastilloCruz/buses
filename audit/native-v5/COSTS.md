# Costos estimados y l?mites

No hubo consumo facturable de OpenAI/Meta durante estas pruebas. No se asign? una tarifa comercial definitiva al c?digo.

F?rmula de texto: (tokens de entrada ? tarifa entrada + tokens de salida ? tarifa salida) / 1 000 000. Se cuentan entradas antes de generar, se reserva el m?ximo de salida y se liquida el uso informado. Timeout conserva la reserva. Contexto y cat?logo cuentan como entrada.

Ejemplo de planificaci?n, NO cotizaci?n de un modelo: con 1 000 turnos de 1 500 tokens de entrada y 250 de salida, suponiendo USD 1/M entrada y USD 5/M salida, el texto costar?a USD 2.75. Con 10 000 turnos ser?an USD 27.50, antes de audio, Meta e infraestructura. Reemplazar esas tarifas ilustrativas por las del modelo seleccionado y vigente.

La referencia consultada de [GPT-Transcribe](https://developers.openai.com/api/docs/models/gpt-transcribe) indica USD 0.0045/minuto: 100 minutos ser?an USD 0.45 y 1 000 minutos USD 4.50. Verificar disponibilidad, formato y tarifa de la cuenta antes de activar. [Precios oficiales OpenAI](https://developers.openai.com/api/docs/pricing). Consulta: 2026-11-21.

Meta depende de categor?a, pa?s, condiciones de ventana y plantilla; no se verific? una tarifa concreta de la cuenta. Estimaci?n = mensajes facturables ? tarifa vigente de Meta. No asumir gratuidad de todas las plantillas. La documentaci?n de Meta respondi? 429 durante la consulta; validar tarifas y aprobaci?n en la cuenta autorizada.

Infraestructura: laboratorio usa recursos locales existentes; no se contrat? hosting. Un despliegue aislado requerir?a cotizar c?mputo, almacenamiento cifrado, backups, monitorizaci?n y tr?fico. El techo operativo del gateway limita n?mero/costo m?ximo asignado a intentos; el ledger PHP es quien aplica el presupuesto monetario verificado del tenant. Configurar adem?s un l?mite duro en el proyecto proveedor como defensa independiente.
