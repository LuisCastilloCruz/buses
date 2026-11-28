# Estado conversacional V7.1

Una sola aplicacion MultiFactura, con su login, menus y BookingService existentes. No cambia el diseno.

RequestSelection extrae preferencias explicitas del catalogo del tenant. ConversationMemory conserva dimensiones validas e invalida el horario al cambiar servicio, profesional o fecha. DialogueState registra intencion, fase, seleccion, datos faltantes, horarios ofrecidos y propuesta pendiente; DialogueSession cifra el estado y lo vincula a identidad autorizada y sede cuando hay una unica sede verificable.

Ollama recibe herramientas restringidas por el estado. No recibe SQL, credenciales ni autoridad para confirmar. El backend comprueba que los argumentos correspondan a la seleccion del cliente y que la hora haya sido ofrecida. BookingProposals vuelve a consultar disponibilidad y entrega un codigo. Solo el mensaje explicito CONFIRMAR codigo permite pasar a BookingService; la transaccion y los locks originales siguen siendo la autoridad final.

Cambio de seleccion o rechazo explicito invalida confirmacion; pregunta informativa la conserva. Cancelar y reprogramar requieren cita perteneciente a la persona autorizada y una nueva confirmacion. La respuesta se persiste con outbox; en este laboratorio el envio es simulado.

El worker privado solo produce propuestas. Al expirar una ejecucion tras suspension, el resultado queda unknown y requiere conciliacion humana. No se repite automaticamente una inferencia incierta ni se afirma que exista una reserva. No se conecto VPS ni se expuso11434.

Limites: seleccion de varias sedes no validada; adaptadores de otros giros no se inventan. La memoria tiene TTL24h y purga explicita por tenant. Revision humana de naturalidad y vulnerabilidades heredadas siguen siendo requisitos de aprobacion.
