# Negocio Tattoo ficticio

URL http://tattoo.localhost:8876; cuenta privada en ACCESS.txt. Mantiene Tattoo, TattooInbox, ventas, POS y menus originales.

AiReception > Calendario: elegir dia/semana/mes y artista. Nueva cita usa cliente de persons, servicio con duracion configurada y disponibilidad real. Abrir una tarjeta para confirmar pendiente, marcar atendida, cancelar o reprogramar. Cada cambio requiere version/idempotencia y queda auditado.

Profesionales/horarios y Servicios usan el motor compartido. No cambiar arbitrariamente el enlace de un artista historico. TattooAvailability incorpora citas/sesiones originales y bloquea registros sin duracion conocida; corregirlos requiere evidencia, no una duracion inventada. Los escritores heredados guardados estan sujetos a la guardia de calendario.

Demostracion del agente: seleccionar cliente ficticio, escribir solicitud, elegir horario y confirmar explicitamente. Primera seleccion crea propuesta; segunda confirmacion registra la cita. Un error no equivale a reserva confirmada.

TattooInbox se conserva. Un Phone Number ID no puede tener dos receptores propietarios entre Inbox y AiReception. No conectar numeros reales ni desactivar credenciales historicas para probar.
