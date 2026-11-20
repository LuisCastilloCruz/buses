# Auditoria previa: integracion nativa

Base: 0be20fb42ae346c2a84e658b195e659f11bdc34b. Original y laboratorio V1 preservados.

- El layout tenant.layouts.app y su sidebar son la interfaz final; no se reemplazan.
- Superadmin ClientController.tables agrupa modules por sort (<14 modulos, >=14 apps), con module_levels. form.vue permite arboles individuales y presets de giro.
- ClientController.store/update traduce IDs system a valores estables y luego IDs tenant, asignando module_user y module_level_user al administrador. No hay una licencia tenant independiente que proteja los modulos nuevos.
- ModuleViewComposer muestra todos los modulos cuando no hay pivotes; RedirectModule y RedirectModuleLevel omiten AJAX. Los nuevos permisos deben negar por defecto y comprobar empresa habilitada mas pivotes del usuario en todas las solicitudes.
- Hyn resuelve hostnames/websites y bases separadas; auth web usa App\Models\Tenant\User y auth admin usa App\Models\System\User. Se conservaran estos guards.
- Clientes/pacientes: persons; servicios vendibles: items (item_type_id 02); documentos/ventas: Document, SaleNote y flujos existentes. No se duplicaran personas ni facturas.
- TattooAppointment y TattooSession no guardan duracion verificable. TattooAvailability ya los bloquea de forma conservadora. Migrar escritores sin duraciones demostrables seria inseguro: mantener bloqueo o encaminar la nueva agenda por una fuente unica, nunca mezclar reservas independientes.
- Agenda V1 contiene transacciones, bloqueo profesional, idempotencia, snapshots, auditoria y pruebas; actualmente autoriza con membresias propias y depende del adaptador Tattoo. Se extraeran contratos para autorizacion y ocupacion, manteniendo compatibilidad de pruebas V1.
- WhatsAppApi y TattooInbox tienen rutas/clientes externos; el entorno nativo de pruebas necesita bloqueo de salida y configuracion sin credenciales. OpenAI/Meta no se activan.
- Migraciones heredadas: no se ejecutaran sobre bases actuales. La instalacion nueva usara MySQL independiente y un registro de ejecucion/dependencias; duplicados se auditan, no se borran.
