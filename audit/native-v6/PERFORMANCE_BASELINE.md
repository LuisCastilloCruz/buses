# Objetivos previos a carga V6

No hay mediciones de capacidad aun. HP 6 cores/12 hilos y 16 GB. Umbrales: RAM libre minima 2 GiB, disco libre 8 GiB; detener nuevas solicitudes si CPU global supera 90% durante 10 segundos o conexiones MySQL alcanzan 80% del maximo. No terminar procesos ajenos.

Se ejecutara carga progresiva 1,5,10,20 clientes; 50 solo con margen. Objetivo exploratorio inicial con 1 y 5 clientes: p95 de lectura <=2000 ms y escritura <=3000 ms, timeout 15s, cero errores inesperados. Los conflictos 409 esperados de carreras se cuentan separados; nunca como reservas exitosas. Escalones superiores se mediran sin prometer SLA previo.

Concurrencia transaccional: 2,5,10,25 y 50 si recursos permiten. Un ganador por profesional/recurso y cero solapamientos segun MySQL. CLI multiproceso no equivale a HTTP. Servidor PHP monoproceso se identifica como baseline; carga HTTP representativa requiere pool local aislado.

Soak inicial 600s, tasa limitada y mismos criterios de invariantes; no ampliar si existe riesgo de recursos. Toda operacion externa usa mocks.
