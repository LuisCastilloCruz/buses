# MultiFactura V4: informe tecnico de avance verificable

**Estado: desarrollo integrado local, NO listo para produccion.** La rama no reemplaza main/master ni las instalaciones anteriores. Este informe describe lo implementado y enumera lo pendiente; no presenta el flujo externo como aprobado.

## Base y aislamiento
Repositorio LuisCastilloCruz/buses; base V3 `4d11628ee6d3d52f371af5073c155e9d5cc785ef`. Rama `feature/multifactura-aireception-odontologia-completo-v4`, worktree `C:/Users/luis castillo/AiReception-lab/native-v4`. Original y V1/V3 conservados. HTTP 8876 y MySQL 13319, datadir privado, cinco bases desechables. No se copio .env ni credenciales originales. Los wrappers verifican raiz, proposito, puerto, datadir y lista de bases antes de escribir.

## Arquitectura conservada
Laravel original 5.7.29, Hyn Tenancy 5.3.1, nwidart 5.1, PHP 7.4.33 y MySQL 8.4.3. Mismo login, sidebar, dashboard y catalogos. Vistas tenant nuevas extienden tenant.layouts.app. Sistema de planes existente ampliado; no se crearon cobros ni un segundo administrador. Vistas Superadmin usan su layout original.

Se reutilizaron BookingService, SlotPlanner, TattooAvailability, interfaces, adaptadores, autoridad de tenant y pruebas V1/V3. El registro de adaptadores solo declara capacidades comprobadas. Tattoo/TattooInbox no fueron reemplazados. La compatibilidad de nombres de clases de catalogo Inbox no edita migraciones historicas. Se conserva el bloqueo de registros Tattoo sin duracion conocida.

## Desarrollo V4
- Calendario mensual y estado atendido, reservas transaccionales/idempotentes y propuestas con confirmacion explicita, expiracion y propiedad del actor/cliente. Cancelacion y reprogramacion propuestas usan el mismo motor; no se confirma una cita desde texto del modelo.
- Configuracion privada de agente y canal, bandeja firmada, vinculacion administrativa de remitentes, conversaciones cifradas, asignacion, pausa/reanudacion y respuestas manuales claramente simuladas.
- Responses API con herramientas estrictas; contexto por conversacion, rechazo de herramientas desconocidas y argumentos de tenant, derivacion humana ante consulta clinica/error. El texto libre del proveedor no se trata como hecho confirmado.
- Presupuesto por plan y tenant: conteo de entrada, reserva de costo maximo, liquidacion del uso e idempotencia. Timeout retiene reserva pendiente y no repite automaticamente la generacion. Tarifas/modelos requieren configuracion verificada; no se fijaron precios comerciales definitivos.
- Meta: HMAC obligatorio, WABA/Phone Number ID registrados, propiedad exclusiva frente a TattooInbox, deduplicacion, jobs en MySQL, mensajes cifrados, auditoria de recibos de entrega incluso fuera de orden. No se resuelve tenant desde remitente. Los recibos no prueban envio desde este sistema: no hay salida real conectada.
- Recordatorios compartidos: offsets editables, consentimiento auditado, pausa, ventana horaria, jobs y comando tick dedicado. Envios SIMULADOS, no servicio Meta real.
- Clinica dental: registros cifrados, odontograma FDI por pieza/superficie, versionado, rol clinico, lectura/modificacion auditada y adjuntos cifrados con descarga autorizada. Solo pacientes ficticios; validacion clinica/legal pendiente.
- Cuarta empresa creada mediante Superadministrador original, base independiente y permisos MySQL limitados. La reserva de base es especifica del laboratorio, no un permiso global de aprovisionamiento.

## Migraciones y conflictos
Migraciones nuevas aditivas `2026_11_22_*` en system/tenant. No se alteraron archivos historicos ni se ejecutaron migraciones productivas. Incluyen permisos clinicos, registros/adjuntos, planes/suscripciones, presupuestos, colas/canales/eventos, contactos, agente/turnos, tarifas, recibos y reglas de recordatorios. El alta cuarta encontro un limite PHP y una incompatibilidad de nombre de clase heredada; se verifico el ALTER terminado antes de recuperar su marcador local y se agrego un alias acotado al modo nativo. No se elimino ninguna tabla para resolverlo.

## Evidencias y limites
Ver TEST_RESULTS.md y screenshots/*.png (Chrome real, perfil aislado, datos ficticios). HTTP 200 de dashboard/POS/ventas/clientes prueba apertura de paginas, no todos sus flujos ni emision SUNAT. MySQL incluye concurrencia real. Los contratos OpenAI/Meta/transcripcion usan transporte falso; ningun test equivale a sandbox externo.

La auditoria Composer encontro avisos criticos: ver SECURITY_DEPENDENCIES.md. El sistema no es comercializable aun con garantias de seguridad. No se ejecutaron cobros, emision SUNAT, llamadas, revocaciones de secretos ni despliegues.

## Respaldos
Base V3 en MultiFactura-private-backups/native-v3-20261120-final. Checkpoint V4 privado native-v4-checkpoint-20261120-181130 incluye bundle incremental recuperado en repo bare, configuracion privada, adjuntos cifrados y dumps de las cinco bases restaurados en bases de recuperacion locales. No se publican respaldos ni secretos. Consultar BACKUP_RECOVERY.md; el checkpoint pertenece a f6451188, no a cambios posteriores.

## Publicacion
Solo rama V4 en https://github.com/LuisCastilloCruz/buses/tree/feature/multifactura-aireception-odontologia-completo-v4 . Remoto GitHub se llama github; origin es otro servidor y no se usa para publicar. Commits por etapas y escaneo de contenido antes de push. Commit/runs finales se registran en DELIVERY_STATUS.md al verificar; no inferir CI de una ejecucion anterior.
