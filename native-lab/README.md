# MultiFactura nativo: laboratorio HP

Este worktree ejecuta **la aplicacion Laravel original completa**. `native-lab` contiene solamente el arranque aislado y las pruebas; no define otro login, dashboard ni sidebar. Las paginas nuevas extienden `tenant.layouts.app`.

## Abrir en esta HP

```powershell
Set-Location 'C:\Users\luis castillo\AiReception-lab\native-v3'
.\native-lab\start.ps1
```

- Superadministrador: http://localhost:8875
- Estudio ficticio: http://tattoo.localhost:8875
- Clinica ficticia: http://dental.localhost:8875
- Comercio ficticio: http://comercio.localhost:8875

Los usuarios y claves aleatorias estan exclusivamente en `.native-runtime\ACCESS.txt`. Abrir ese archivo localmente; no subirlo ni copiarlo a un informe publico. Cada negocio usa el login original y una cookie vinculada a su empresa. Para cambiar de negocio, abrir su URL e iniciar sesion con su cuenta.

Tattoo conserva su agenda y pantallas originales. AiReception muestra la agenda compartida. Odontologia y AiReception consultan exactamente las mismas reservas de la clinica; no son dos calendarios independientes. En el comercio no aparecen ni se autorizan los modulos nuevos.

Superadministrador > clientes > editar > Modulos permite habilitar apps y niveles individualmente. El giro Odontologia sugiere sus modulos; una seleccion manual compatible se conserva. Los modulos nuevos no quedan seleccionados automaticamente al abrir el formulario de alta.

## Recorrido de demostracion

1. Entrar a Tattoo y abrir AiReception > Calendario. Elegir fecha y profesional, Nueva cita, cliente existente y servicio, consultar horarios y confirmar uno.
2. Abrir la tarjeta para reprogramar o cancelar. El historial registra cada operacion y usuario.
3. Entrar a Dental. Odontologia administra especialidades, odontologos y sillones. Profesionales/horarios y Servicios permiten configurar agenda y duraciones.
4. AiReception > Conversaciones permite persistir mensajes ficticios y derivarlos a una persona. La demostracion del agente ofrece respuestas deterministicas y usa el mismo formulario de reserva; su enlace conduce a la reserva persistida.
5. Los recordatorios quedan en una bandeja simulada. No se envia correo, Meta ni OpenAI.
6. Entrar a Comercio y comprobar dashboard, ventas, clientes y POS originales. Esta validacion no emite comprobantes fiscales.

## Entorno y datos

Laravel 5.7.29; Hyn 5.3.1; nwidart/laravel-modules 5.1.0; PHP 7.4.33; MySQL 8.4.3 privado en 127.0.0.1:13318. No se actualizaron paquetes PHP ni instalaciones globales.

El proceso MySQL usa `--no-defaults`, un datadir propio, mysqlx desactivado y `--restrict-fk-on-non-standard-key=OFF`, necesario para claves foraneas heredadas. El arranque comprueba puerto, datadir, raiz del worktree y una lista exacta de bases. Nunca cargar el `.env` original. No ejecutar `php artisan migrate` directamente contra la instalacion original.

Bases: `mf_native_system`, `mf_native_tattoo_v3`, `mf_native_dental`, `mf_native_commerce`. Cada tenant tiene su usuario MySQL Hyn limitado a su base. El intento inicial incompleto `mf_native_tattoo` se conserva como evidencia y no se usa.

`start.ps1` crea el entorno vacio cuando no existe, ejecuta las migraciones verificadas y genera fixtures. En esta HP se verifico tambien su ejecucion repetida sin recrear datos. Una reconstruccion completa desde un checkout vacio en otra maquina no se certifica con esta entrega. Se requiere `vendor` compatible: copiar **solo vendor** desde el laboratorio V1 verificado, nunca `.env`, almacenamiento ni credenciales. El script no instala dependencias automaticamente.

La provision automatica de una cuarta empresa mediante el boton Guardar de alta no forma parte de la prueba completada: el usuario instalador del laboratorio solo dispone de las cuatro bases predefinidas. No ampliar permisos ni apuntar a otro servidor para sortear esta limitacion. La edicion y activacion real de las tres empresas esta probada.

## Pruebas

```powershell
.\native-lab\run-tests.ps1
```

Necesita Python, Node 22 para CDP y Chrome instalado. Usa perfil Chrome nuevo, bloquea solicitudes web externas y guarda capturas en `audit/native-v3/screenshots`. MySQL/HTTP deben estar arrancados. Las pruebas solo agregan registros ficticios de QA; no son pruebas destructivas de clientes reales.

Comandos individuales:

```powershell
$php = 'C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe'
& $php native-lab/mysql-tests.php dental
& $php native-lab/legacy-tests.php tattoo
python -u -X utf8 native-lab/http-tests.py
node native-lab/browser.mjs
```

La suite V1 permanece intacta en su worktree. Se reutilizan sus 66 comprobaciones standalone, 7 pruebas PHPUnit y el motor. Las suites MySQL/HTTP/Chrome nativas ejercitan el sistema completo con Hyn; no se presentan como una ejecucion de los scripts antiguos contra su base V1.

## Assets originales

Se compilaron todos los componentes originales, no solo el formulario nuevo. `webpack.native.js` conserva las mismas entradas, Sass y aliases, sin mapas de fuentes. Node 22 no puede cargar el node-sass 4.14.1 fijado por este proyecto. Se uso Node **portable 14.21.3**, exclusivamente para compilar, con el binding oficial correspondiente; Node global y package-lock no se actualizaron.

```powershell
npm ci --ignore-scripts --legacy-peer-deps --no-audit --no-fund
# Colocar Node 14.21.3 portable en .native-runtime/node14.exe
# y el binding node-sass 4.14.1 win32-x64-83 en su directorio vendor.
python -u -X utf8 native-lab/build-assets.py
```

Origen de herramientas: https://nodejs.org/dist/v14.21.3/win-x64/node.exe y https://github.com/sass/node-sass/releases/download/v4.14.1/win32-x64-83_binding.node . No son servicios de ejecucion de la aplicacion.

## Limites de esta version

- Odontologia es administrativa: especialidades, odontologos, sillones, clientes/pacientes existentes y citas. No hay historia clinica, odontograma, diagnosticos ni prescripcion automatica.
- Facturacion usa `persons` e `items` existentes y el formulario de documentos original. No se generan facturas automaticamente desde citas ni se certifica emision SUNAT.
- Los dos tatuadores ficticios estan enlazados al calendario canonico. La incorporacion de otros artistas heredados requiere revisar y registrar su enlace; no se infiere por nombres.
- No se inventa duracion para citas/sesiones Tattoo antiguas. Se bloquea conservadoramente el dia afectado y el posible arrastre del anterior.
- No se ejecutaron pruebas exhaustivas de todos los modulos historicos ni provision de nuevas empresas desde cero por HTTP. Ver informe para el alcance real.
