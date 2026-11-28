# Linea base V6 en ejecucion

Base limpia V5 d597fa32d914b6b5be4b99381c3c1a6a8b60bddf. Sin cambios locales pendientes. V5 y anteriores preservados.

HP i7-9750H, 6 nucleos/12 hilos, 16 GB RAM; inicial libre aproximadamente 4.7 GB, disco 16.3 GB. PHP 7.4.33, Node 26.7.0, Composer 2.7.1. Ningun PHP/MySQL de laboratorios activo al inicio.

Respaldo privado native-v5-before-v6-20261127-225829: bundle incremental V5 sobre V4 y ZIP offline de 5418 archivos verificados por SHA256, incluye datadir detenido y configuracion privada. Intento inicial interrumpido por reinicio; segundo detecto fecha anterior a 1980; copia verificada conserva timestamp original en manifiesto. No se borraron intentos ni archivos originales. Restauracion de servicio aun pendiente.

Las 14 pruebas Node del gateway V5 se ejecutaron de nuevo y pasaron antes de cambios. La bateria completa V5 se reproducira en V6 con bases/credenciales nuevas para no alterar V5. Solo se adaptaron identidad, rutas de evidencias y puertos de los wrappers: MySQL13321, HTTP8878, CDP19231. Esto no cuenta como pruebas aprobadas hasta ejecutar.

Umbrales iniciales: detener carga si RAM libre < 2 GB, disco < 8 GB, CPU global > 90% sostenido o conexiones > 80% del limite. Ningun generador acepta destinos externos. No inferir capacidad productiva del servidor de desarrollo.
