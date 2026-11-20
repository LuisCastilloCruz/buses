# Odontologia administrativa V1

Modulo nwidart integrado en MultiFactura. Registro en `config/app.php`; rutas solo cuando `aireception.native_enabled` esta habilitado. La presencia del provider no concede acceso: se exige habilitacion de empresa en SYSTEM y pivotes de usuario en TENANT.

Tablas especializadas nuevas: `odontologia_specialties`, `odontologia_dentists`, `odontologia_chairs`. Los odontologos referencian profesionales canonicos y los sillones recursos compartidos de AiReception. Pacientes son `persons`; servicios facturables son `items` tipo 02. No se utiliza ninguna tabla Tattoo para odontologia.

Permisos: `odontologia_calendar`, `odontologia_book`, `odontologia_manage`, `odontologia_patients`, `odontologia_billing`. El administrador de usuarios existente permite asignar niveles; la licencia de empresa nunca se deduce solamente del provider activo.

Historia clinica y odontograma quedan fuera de esta version. Antes de implementarlos: definir roles clinicos separados, consentimiento, auditoria de consulta y modificacion, adjuntos protegidos, politica de conservacion y pruebas con datos ficticios. No habilitar diagnostico ni prescripcion por IA.
