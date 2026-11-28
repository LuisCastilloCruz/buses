# Errores y ciclos V6

## GW-001: doble reintento entre procesos
Sintoma: misma solicitud retryable produjo 2 llamadas al proveedor falso. Reproduccion: gateway/concurrency.test.mjs, dos procesos, bloqueo SQLite inicial comun. Prueba original FAIL, 2 != 1, duracion 862.0 ms.

Causa: lectura de estado anterior a BEGIN IMMEDIATE; dos procesos conservaban la misma lectura retryable. Solucion: volver a leer y validar idempotencia/estado dentro del bloqueo, incluyendo devolucion del cache completado. Archivo gateway/core.mjs. Regresion agrega concurrency.test.mjs y concurrency-worker.mjs.

Ciclo 1: repeticion identica PASS, una sola llamada, 807.4 ms. Suite gateway completa: 15/15 PASS (incluye HTTP loopback). La diferencia de una muestra no es una mejora de rendimiento demostrada; carga y soak aun pendientes. Commit pendiente del hito.

## ENV-001: respaldo interrumpido / timestamp ZIP
Reinicio dejo primer ZIP incompleto; segundo detecto fechas anteriores a 1980. Se preservaron ambos, copia nueva admite fechas ZIP y conserva originales en manifiesto. 5418 archivos verificados SHA256. No se altero fuente. No es fallo funcional de MultiFactura.

## AUTH-001: credenciales no textuales en TokenGuard heredado
La prueba original ejecuto 19 verificaciones: 10 fallaron porque booleanos verdaderos, numeros, arrays y objetos llegaron al proveedor de usuarios. El fixture no aceptaba estos valores: el defecto verificado era la ausencia de validacion de tipo antes de consultar, no una demostracion de acceso a una cuenta real.

Correccion: App\Auth\StrictTokenGuard rechaza valores no string en getTokenForRequest y validate; AuthServiceProvider registra el driver token para api y system_api. No se modifica vendor ni la autenticacion por sesiones.

Repeticion exacta: 19/19 PASS. Integracion Laravel y MySQL V6: 12/12 PASS, ambos guards registrados, cero consultas para valores invalidos, token valido aceptado y desconocido rechazado. El token ficticio temporal se revierte mediante rollback. No se redujeron expectativas. Auditoria Composer sigue pendiente de mitigaciones adicionales; esto no corrige todos sus avisos.

## QA-001: interferencia entre suites
Primer ensayo ampliado de concurrencia fallo con permiso denegado mientras otra suite suspendia temporalmente el mismo tenant. No se modifico autorizacion. Se ejecutaron secuencialmente y se agrego lock exclusivo de archivo al runner maestro. Repeticion: 2/5/10/25 workers, exactamente una reserva persistida en cada nivel. Evidencia original conservada en concurrency-v6-first.log.

## PERF-001: cola del servidor HTTP monoproceso
Objetivo p95 <= 2000 ms. Primer ensayo: 5 y 10 usuarios incumplen, sin errores HTTP. Causa: serializacion del servidor PHP -S. Pool local nginx/cuatro PHP CGI, mismo codigo/datos/SLO/carga, pasa niveles 1/5/10. No se alteraron permisos ni reglas de negocio. Configuracion nginx tuvo dos fallos previos: marcador ROOT colisionaba con DOCUMENT_ROOT y faltaba directorio temporal; ambos corregidos antes de iniciar procesos. Ver PERFORMANCE_COMPARISON.md.

## QA-002: percentiles de muestras pequenas
El runner inicial usaba indice floor((n-1)*q), que subestimaba p95/p99 con n pequeno. Se cambio a nearest-rank ceil(n*q)-1. Se conservaron muestras originales y se genero resultado corregido separado. No se repitieron escrituras para recalcular estadisticas.

## QA-003: alcance visual del dashboard
Capturas tempranas mostraban placeholders de carga aun con sidebar presente. Se agrego browser-dashboard-v6.mjs que espera todos los loaders del componente Vue. Estado de ejecucion pendiente hasta su registro separado; las pruebas previas siguen acreditando solamente lo que comprobaron.

## PERF-002: saturacion de cuatro workers con 20 clientes
Misma carga y SLO p95 <= 2000 ms: pool4 3915.72 ms FAIL; pool4+OPcache 2272.03 ms FAIL; pool8+OPcache 1634.12 ms PASS. No se cambiaron reglas ni dependencias globales. Recursos previos permitian ocho workers; pico final CPU 92.51%, no sostenido. No se ensaya 50 HTTP por falta de margen. Las 42 verificaciones HTTP nativas repetidas sobre pool8 pasaron, incluyendo permisos, idempotencia y cancelacion. Resistencia posterior se registra al concluir, no por anticipado.

## QA-004: nivel bloqueado no debe aparentar aprobado
Concurrencia de 50 workers fue omitida por el guard de RAM. Se corrigio el estado de salida del runner a 2 y mensaje BLOCKED; reanudar niveles anteriores comprueba filas sin duplicarlas. No se reduce la carga para aparentar PASS.

## AUTH-001 ? confirmacion adicional en MySQL
La reproduccion posterior con una fila ficticia demostro ACCEPTED en TokenGuard heredado para la credencial numerica 1. StrictTokenGuard rechazo el mismo valor. La prueba se ejecuto dentro de transaccion y el token temporal se revirtio. Resultado actualizado: 13 comprobaciones Laravel/MySQL aprobadas, ademas de las mismas 19 unitarias. Esto confirma un defecto del codigo/consulta heredados, no del fixture. Ningun token se imprimio.
