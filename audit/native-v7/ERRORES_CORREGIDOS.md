# Fallos conservados y correcciones

| Fallo | Causa/evidencia | Corrección y reintento |
|---|---|---|
| Herramientas reales 0/30 | Texto libre y argumentos inesperados del protocolo nativo | JSON Schema más validación; mismos 30 casos 30/30. Regresión final posterior pendiente por RAM. |
| Fecha/profesional perdidos | Texto libre: fecha 6/10, profesional 9/10 | Preferencias explícitas del backend, memoria cifrada y resumen sin confirmación; unitarias y MySQL. Falta repetir lingüística real. |
| POST sin permiso devolvía 500 y ruta interna | Handler heredado trataba JSON fuera de `/api` de forma distinta | 403 sanitizado para AiReception/Odontología; regresión HTTP comprueba ausencia de file/line/trace. |
| Índice documental largo fallaba | TEXT no admitía hashes de mil términos; índice truncado en MySQL no estricto | Nueva migración MEDIUMTEXT, validación del índice y fragmento centrado en coincidencia; caso largo aprobado. |
| Checkbox de aprobación invisible | CSS heredado ocultaba el input sin su componente visual | Selector nativo visible; captura y validación HTTP pendientes de actualización final. |
| Fixture de permiso incorrecto | Elegía usuario por type, no por niveles | Lector #3 verificado sin configure; rechazo 403 aprobado. No se cambió autorización. |
| Fixture documental interfería con demo HTTP | Consulta genérica también encontraba otra fuente válida | Término sintético exclusivo, sin borrar documentos de demo; mismos invariantes aprobados. |
| Codificación Windows | Pipe/lectura predeterminada convirtió caracteres en `?` o Windows-1252 | Fuentes normalizadas UTF-8; prueba de mañana/viernes y léxico aprobada. Regresión real final pendiente. |
| Primer runner de restauración no terminaba | Pipe heredado al iniciar MySQL desde PowerShell | Salida a archivo; restauración completa repetida, hash idéntico y reanudación exitosa. Solo se detuvo el runner propio identificado. |
| Lectura HTTP de consumo sin tabla nueva | Suite se inició mientras aún se agregaba migración | Se aplicó solo el marcador pendiente y se repitió HTTP; no se ocultó el intento inicial. |

Los cortes por RAM de Chrome, inferencia y 10 escritores son bloqueos de recursos, no aprobaciones ni errores reparados bajando umbrales.

## Actualizacion tras liberar memoria

Regresion final UTF-8: 30/30 herramientas con Ollama real aprobadas. Pipeline Laravel-gateway-HTTP-Ollama-MySQL: 8 comprobaciones por tenant (Tattoo y dental), incluida continuidad al cambiar fecha. Un fallo real anterior eligio 08:00 sin seleccion del cliente; el backend lo rechazo. Se restringio prepare_booking hasta disponer de hora explicita y se repitieron ambos casos, aprobados. No equivale a aprobar la rubrica linguistica global ni reservas completas por IA.

Chrome dividido por negocio: Tattoo, dental, comercio, sistema y V7 aprobados. Inferencia breve con niveles 1/2/3: seis respuestas, sin errores; primera 5202 ms, calientes 170-491 ms, 80.7-83.6 tokens/s. Sin prueba sostenida ni comparacion 4B.

Fallo real adicional: prepare_booking proponia 08:00 sin eleccion del cliente; backend rechazo y paso a humano, interrumpiendo continuidad. Correccion: availableDefinitions excluye esa herramienta sin hora explicita, disponibilidad queda permitida con servicio/profesional/fecha. Mismo caso dental y Tattoo: 8/8 cada uno. Prueba unitaria inicialmente no cargaba ReceptionTools; se agrego require y paso la regresion completa.
