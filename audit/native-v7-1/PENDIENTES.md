# Pendientes y bloqueos V7.1

- Calidad comercial no aprobada:41 errores de rutas en1410 turnos y segmento booking85% en regresion. Tres iteraciones de enrutamiento conservadas; no ocultar ni sobreajustar el conjunto reservado.
- Naturalidad90% requiere revision humana independiente. La suite usa respuestas verificadas del backend; no certifica expresividad libre.
- Registros antiguos sin duracion siguen protegidos por politicas heredadas; no se inventaron duraciones.
- Varios locales/sedes:estado conserva una sede cuando existe exactamente una; seleccion multiple completa no validada.
- Calendario real solo en adaptadores comprobados Tattoo/Odontologia. Otros giros informan o derivan.
- Qwen4B no evaluado; memoria compartida con servicios de la HP, no descargar para forzar una comparacion.
- Concurrencia50 no aprobada; benchmark IA seis respuestas cortas, no capacidad comercial ni soak.
- Worker privado probado localmente con SQLite persistente, desconexion/reinicio y resultados inciertos. Sin VPS real, tunel externo o recuperacion real de suspension Windows ensayada en V7.1.
- OpenAI,Meta,WhatsApp externos desactivados. Outbox simulada; sandbox real pendiente de credenciales y autorizacion.
- Avisos criticos del monolito heredado siguen abiertos. El inventario V7/V6 de117 avisos/23 paquetes/15 abandonados es antecedente, NO auditoria nueva V7.1. Laravel5.7/PHP7.4 no deben exponerse publicamente.
- No aptitud clinica ni certificacion legal; pacientes ficticios exclusivamente.
