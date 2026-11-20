# Informe: MultiFactura modular + AiReception + Odontologia

Fecha: 20/11/2026. Estado: demostracion nativa local; no version productiva.

## Estado y preservacion

- Original: `C:\laragon\www\multifacturaperunew`, rama `integration/ai-reception-tattoo-20261120-safe`, commit `e87fcdce227a7993fe5da96507b6417b980202ed`. Conserva los mismos seis archivos sin seguimiento; no se cambiaron sus archivos.
- V1: `C:\Users\luis castillo\AiReception-lab\agenda-v1`, rama `feature/ai-reception-agenda-v1`, commit `0be20fb42ae346c2a84e658b195e659f11bdc34b`, limpio y preservado.
- Nuevo worktree: `C:\Users\luis castillo\AiReception-lab\native-v3`.
- Nueva rama: `feature/multifactura-native-aireception-odontologia-v3`, basada en V1; no merge a main/master ni despliegue.
- Respaldo previo privado: `MultiFactura-private-backups\agenda-v1-20261120-final\agenda-v1.bundle`, verificado; SHA256 `6873594cd2fd4e6db2b42e0569a34b050fae33bee5d7be6cfcfff8e69741dd25`. Se verifico restauracion del respaldo SQL V1 antes de esta fase. Los detalles de cierre y respaldo de esta rama se registran en `PRIVATE.md`, excluido de Git.

## Lo reutilizado y agregado

Se conservaron layout, sidebar, dashboard, guards, usuarios, catalogos de modulos/niveles, formulario de clientes del Superadministrador, facturacion, POS y pantallas Tattoo/TattooInbox. La unica incorporacion al sidebar es un partial con autorizacion explicita. Se recompilo el conjunto completo de assets originales.

Se reutilizaron SlotPlanner, TattooAvailability, contratos, pruebas y motor V1. Se extrajeron autoridad y restricciones de calendario; el motor no depende de modelos de un giro. Odontologia tiene su propio modulo, modelos, adaptador y tablas especializadas. Sus citas y las de AiReception son la misma fuente canonica por tenant.

Se agregaron: profesionales, servicios con duracion/margen, horarios/descansos/excepciones, recursos/sillones, reserva/confirmacion/cancelacion/reprogramacion, idempotencia, trazabilidad, conversaciones ficticias persistidas, derivacion humana y recordatorios simulados. La demostracion deterministica crea reservas reales en la base local.

Personas y servicios comerciales se reutilizan desde `persons` e `items`, sin duplicar pacientes ni facturas. La facturacion sigue su formulario original; no hay facturacion automatica de citas.

## Catalogos, permisos y migraciones

Nuevos catalogos AiReception y Odontologia se registran sin asignarlos a clientes existentes. `client_module_entitlements` en SYSTEM controla habilitacion de empresa; `module_user` y `module_level_user` siguen siendo los permisos del usuario. Una app Laravel activa no equivale a licencia. El formulario conserva Basico, Farmacia, Hotel, Restaurante y Tattoo, agrega Odontologia y seleccion individual.

Se recupero tambien el catalogo `tattooinbox` y los valores `tattoo_inbox`/`tattoo_inbox_settings` que las vistas existentes requieren pero no estaban registrados en las migraciones disponibles. Sus rutas se refuerzan solamente al habilitar la integracion nativa.

Migraciones nuevas, fechadas 2026_11_21 para quedar despues de las historicas:

- SYSTEM `000001_register_native_reception_modules`: licencias y catalogos nuevos.
- TENANT `000001_register_native_reception_catalog`: modulos/niveles.
- TENANT `000002_create_shared_reception_agenda`: 12 tablas canonicas de agenda, conversaciones y recordatorios.
- TENANT `000003_create_odontologia_admin_tables`: especialidades, odontologos y sillones.
- SYSTEM y TENANT `000004_register_existing_inbox_catalog`: recuperar catalogo del modulo existente, sin asignaciones automaticas.

No se editaron migraciones historicas. Las siete copias de migraciones Tattoo se conservaron; el instalador registra por nombre, evita ejecutar dos veces las copias tenant y no aplica los dos archivos Tattoo mal ubicados en el directorio SYSTEM. La migracion heredada de canales Meta crea una tabla SYSTEM, por lo que solo se ejecuta una vez.

Inventario real: SYSTEM 23 tablas/79 marcas de migracion; cada tenant 329 tablas. Tattoo 970 marcas, dental/comercio 969: la diferencia corresponde al registro de la migracion compartida de canales Meta. Las tres bases tienen `send_auto=0` y cero documentos emitidos.

## Problemas detectados y soluciones

- Permisos originales omiten ciertas rutas AJAX y el composer puede mostrar todos los modulos sin pivotes: las rutas nuevas usan una comprobacion explicita de empresa y usuario.
- `extract()` heredado reemplaza la variable cliente: se conserva la referencia al modelo al sincronizar licencias. El reemplazo de permisos ahora usa transaccion y falla cerrado. Tambien se maneja el catalogo DIGEMID ausente y se usa el ID real al insertar el administrador.
- Lecturas de autorizacion antes del bloqueo podian fijar un snapshot viejo: READ COMMITTED y revalidacion dentro de transaccion, probados con procesos distintos.
- Citas Tattoo sin duracion: no se inventan minutos; bloqueo conservador y bloqueo compartido de escritores heredados.
- MySQL 8.4 rechaza ciertas FK heredadas no unicas: compatibilidad configurada solo en el servidor desechable. El primer esquema parcial se conserva sin usar.
- Node 22 es incompatible con node-sass fijado: compilacion portable con Node 14.21.3, sin actualizar paquetes globales ni Laravel.
- La prueba visual inicial necesitaba desplegar la seccion Modulos y esperar la navegacion real. Las pruebas corregidas usan el DOM real; no se sustituyeron capturas por maquetas.

## Pruebas reales

- Sintaxis: 171 archivos PHP en la bateria final inicial; 67 archivos PHP del conjunto final tambien pasaron sintaxis antes del commit.
- Standalone: 66 comprobaciones, proveedores simulados/sin red.
- PHPUnit: 6 pruebas/20 aserciones de compatibilidad y 1 prueba/7 aserciones SQLite. No arrancan la configuracion original.
- Laravel/Hyn/MySQL nativo: 24 comprobaciones; persistencia, simultaneidad con dos procesos, idempotencia, permisos, aislamiento fisico por credenciales, recursos compartidos, duraciones y excepciones.
- Compatibilidad Tattoo/TattooInbox: 6 comprobaciones con MySQL real; datos antiguos sin duracion y rechazo desde el escritor heredado.
- HTTP nativo: 42 aserciones, incluido guardar activacion/desactivacion por el controlador original, tres empresas, formularios originales, misma reserva desde AiReception/Odontologia, cookie cruzada rechazada y webhook sin firma rechazado.
- Chrome real: 14 aserciones del recorrido completo, sin excepciones JavaScript; 7 comprobaciones adicionales de Superadministrador y default opt-in pasaron tras la ultima recompilacion. Las 4 comprobaciones Chrome adicionales de configuracion de servicios tambien pasaron; ver TEST_RESULTS.md. Capturas en `screenshots/`.
- Arranque `native-lab/start.ps1`: ejecutado sobre la instalacion preparada; no repitio migraciones ni reemplazo fixtures.

Las pruebas del panel tradicional son smoke tests de dashboard/documentos/POS/clientes; no equivalen a una regresion exhaustiva de todos los modulos ni a una emision fiscal. Las pruebas de concurrencia son MySQL reales, no mocks. Las conversaciones de IA y los recordatorios siguen siendo simulaciones. No se conecto ningun proveedor real.

## Pendientes y riesgos

- Alta automatica de una cuarta empresa por HTTP no validada: el usuario del laboratorio esta limitado a las cuatro bases predefinidas. La edicion de las tres empresas y los selectores de alta si estan probados.
- No hay historia clinica, odontograma ni prescripcion/diagnostico IA; requieren diseno de permisos y datos clinicos separado.
- Incorporacion de otros artistas heredados exige revisar enlaces y duraciones; no se hace migracion automatica de citas antiguas.
- No hay entrega real de recordatorios, OAuth/canales Meta ni consumo OpenAI.
- Laravel/PHP y dependencias de frontend heredadas requieren un proyecto separado de compatibilidad/actualizacion antes de considerar produccion.
- Habilitar o revocar credenciales historicas puede interrumpir servicios existentes. No se rotaron ni deshabilitaron; las instrucciones de sandbox y firma obligatoria estan en `modules/AiReception/NATIVE_INTEGRATION.md`.

## Ejecucion y publicacion

Instrucciones exactas: `native-lab/README.md`. URLs: `http://localhost:8875`, `http://tattoo.localhost:8875`, `http://dental.localhost:8875`, `http://comercio.localhost:8875`. Credenciales solo en `.native-runtime/ACCESS.txt` privado.

Destino autorizado: https://github.com/LuisCastilloCruz/buses/tree/feature/multifactura-native-aireception-odontologia-v3 . La verificacion del commit remoto y Actions se registra al cierre. Consultar `git rev-parse HEAD` para el commit final del informe y `PRIVATE.md` para el cierre local completo.

## Cierre verificado

Commit de implementacion publicado y confirmado por `git ls-remote`: `80c8a3ffbe225ce34017c74eb3150dcf0316d0e2`. La rama contiene 112 archivos revisados; el escaneo no encontro secretos ni credenciales del runtime.

Respaldo privado nuevo: `MultiFactura-private-backups/native-v3-20261120-final`. Bundle completo verificado por clonacion bare y fsck; SHA256 `b2b7004899d751ceba77f861262ba4522b89ca74d89663deaaa34503258a3cab`. Se restauraron las cuatro bases a nombres nuevos en el servidor desechable: 1010 tablas y 13270 filas, todos los hashes coincidentes. No se sobrescribieron las bases de demostracion.

GitHub Actions: ejecucion [44824472616](https://github.com/LuisCastilloCruz/buses/actions/runs/44824472616), **aprobada** en ambos jobs, PHP 7.4 y PHP 8.2. Corresponde al commit de implementacion; este cierre solo agrega documentacion. La CI cubre pruebas aisladas PHP; MySQL, HTTP y Chrome se ejecutaron localmente como consta en TEST_RESULTS.md.
