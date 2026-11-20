# Superadministrador

Abrir http://localhost:8876 y usar la cuenta ficticia de `.native-runtime/ACCESS.txt`. El login y la pantalla Clientes son los originales.

1. Clientes > crear/editar: seleccionar giro (los seis giros existentes), plan, aplicaciones y niveles individualmente. AiReception y Odontologia son independientes. El giro sugiere; no sustituye la seleccion manual. No se activan en clientes existentes por migrar el catalogo.
2. Para la cuarta empresa de esta HP: ejecutar primero `php native-lab/reserve-fourth.php` con el PHP indicado en README. Reserva exclusivamente mf_v4_cuarta en el MySQL 13319 comprobado. El formulario original Guardar crea el tenant, administrador y datos iniciales; la prueba reproducible `python native-lab/fourth-company-test.py` realiza o verifica ese recorrido con datos ficticios. No ampliar permisos del usuario real.
3. Abrir `/aireception-management`: asociar controles al plan existente, permitir los dos modulos, fijar limite de profesionales, mensajes y presupuesto. Precio addon editable; no se cobra automaticamente. El limite de mensajes aun no gobierna todos los canales: ver matriz.
4. Estado de suscripcion: activar/suspender/vencer con fecha. Las rutas nuevas verifican estado y permisos; ventas/POS no se suspenden por quitar AiReception.
5. Tarifas de modelos: solo cargar valores verificados, moneda USD por millon y fecha de caducidad. Los valores falsos usados en tests NO son precios de OpenAI. Configurar tarifa no habilita trafico externo.

Pruebas: 11 assertions HTTP de controles, alta cuarta 6, Chrome de formulario original. No se validaron cobros ni provision arbitraria de tenants productivos. Basico/Profesional/Empresarial no son planes comerciales definitivos precargados por esta fase.
