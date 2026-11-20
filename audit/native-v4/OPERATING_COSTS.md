# Estimacion de costos por empresa

No hay consumo externo real ni precios definitivos establecidos. La tarifa del modelo y fecha de vigencia son editables en Superadmin; el presupuesto se expresa en USD y se guarda en micro-USD enteros. Las tarifas fake de tests NO sirven como estimacion comercial.

Formula mensual: `(tokens_entrada/1e6 * tarifa_entrada) + (tokens_salida/1e6 * tarifa_salida) + minutos_audio * tarifa_transcripcion + mensajes_plantilla_por_categoria * tarifa_Meta_aplicable + infraestructura + almacenamiento + soporte`. Considerar cache, redondeo, moneda/tipo de cambio e impuestos segun contratos vigentes. No se conocen volumen, modelo, pais/categorias ni tarifas de la cuenta: no se puede dar un total verificable.

Implementado: reserva maxima de Responses, liquidacion de uso y panel por tenant. Pendiente contabilidad integral de audio/Meta/almacenamiento, cuotas comerciales completas y cobro addon mediante modulos originales. Modelar escenarios con datos sinteticos y tarifas oficiales vigentes antes de vender planes. No hubo cobros ni llamadas pagadas en esta validacion.
