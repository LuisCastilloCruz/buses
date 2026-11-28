# Alcance de rendimiento V7

INFERENCE_BENCHMARK.json contiene seis solicitudes reales, niveles 1/2/3. Se mide primera respuesta y duracion; no es una muestra suficiente para inferir percentiles estables ni capacidad comercial. La primera solicitud incluye calentamiento; no debe mezclarse con las calientes para prometer latencia sostenida. No se ejecutaron 5 clientes, soak ni pruebas masivas contra proveedores.

La muestra inicial de 300 turnos obtuvo p50 546 ms, p95 983 ms y p99 2442 ms; carga secuencial. Mediana de generacion 72.52 tokens/s. El modelo estuvo 100% GPU. Son escenarios distintos al microbenchmark.

Guarda: 3.25 GiB libres en frio, 2.3 cargado, corte vivo a 2 GiB. No se bajaron umbrales para aprobar.
