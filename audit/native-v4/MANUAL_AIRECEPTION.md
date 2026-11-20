# AiReception nativo

Requiere modulo de empresa, suscripcion compatible, modulo del usuario y nivel de cada ruta. Ocultar menus no sustituye permisos backend.

- Calendario: agenda unica; filtros y estados; crear, confirmar, reprogramar, cancelar, atender; propuestas asistidas esperan confirmacion explicita.
- Conversaciones: mensajes cifrados, cliente asociado, responsable, pausa humana, reanudacion y respuesta manual SIMULADA. No se envia esa respuesta a Meta.
- Bandeja: eventos firmados recibidos. El administrador vincula remitente a cliente con evidencia; no se infiere identidad por telefono. Una identidad vinculada no se reasigna silenciosamente.
- Configuracion de agente: modo simulado o sandbox, modelo y limites, clave privada sin devolucion al frontend. La bandera global externa sigue false.
- WhatsApp: configuracion privada del canal. Guardar secretos no habilita envio real ni prueba conexion.
- Recepcion y consumo: estado de citas, reservas de presupuesto, uso y auditoria. Datos de pruebas no equivalen a cargos reales.
- Automatizaciones: consentimientos, intervalos, pausa y ventana permitida; resultados simulados e intentos.

El modelo solo puede proponer herramientas administrativas registradas. No se ejecuta SQL ni texto arbitrario. Consultas clinicas, errores y respuestas invalidas se derivan a personas. La validacion semantica del modelo real sigue pendiente. No habilitar acceso publico con las vulnerabilidades heredadas actuales.
