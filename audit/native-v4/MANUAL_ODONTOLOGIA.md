# Negocio Odontologia ficticio

URL http://dental.localhost:8876. Login y sidebar originales. Odontologia permite especialidades, odontologos y sillones, enlazados a profesionales/recursos del motor universal. Servicios comerciales reutilizan items; pacientes administrativos reutilizan persons. La agenda de Odontologia y AiReception muestra las mismas reservas.

Configurar profesional, horarios y servicio/duracion. Consultar disponibilidad antes de crear; abrir la cita para reprogramar/cancelar/atender. No se emiten comprobantes automaticamente. Usar el modulo original de ventas para operaciones autorizadas; el enlace trazable automatico cita-ingreso sigue pendiente.

Historia clinica de laboratorio: requiere permiso `odontologia_clinical` y bandera clinica (false por defecto, true solo bootstrap ficticio). Seleccionar paciente, tipo de registro, nota y, si corresponde, pieza FDI/superficie. Guardar genera version y auditoria. Editar usa version optimista: un conflicto exige recargar. No hay diagnostico ni prescripcion IA.

Adjuntos: PNG/JPEG/PDF hasta 2 MB, contenido MIME verificado, cifrados fuera de public. Descargar requiere permiso y registra acceso. No se aceptan datos reales, ni se considera historia clinica legalmente validada. Faltan validacion profesional, retencion/exportacion/eliminacion y editor grafico avanzado.
