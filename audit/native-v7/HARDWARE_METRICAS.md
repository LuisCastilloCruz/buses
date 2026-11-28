# Hardware y límites

- Intel Core i7-9750H, 6 núcleos y 12 procesadores lógicos.
- RAM: 16225 MiB. Al inicio aproximadamente 3780 MiB libres; durante el trabajo descendió hasta activar guardas de 2048 MiB.
- NVIDIA GTX 1650: 4096 MiB VRAM, driver 592.82, compute capability 7.5. Esto no certifica una instalación de toolkit CUDA.
- Ollama 0.40.17. Ningún modelo cargado al empezar; Qwen3 1.7B fue cargado para los ensayos y luego descargado de memoria.
- Disco libre observado variable: 13,68 GB al inicio y más de 18 GB en una lectura posterior. No se atribuye esa variación a una acción de limpieza de este trabajo.

Guardas: 2 GiB mínimos durante ensayos; para inferencia, 3,25 GiB antes de carga en frío o 2,3 GiB con el modelo ya cargado. Chrome y los escritores PHP tienen reservas propias. No se cerraron procesos ajenos para obtener mejores cifras.

No se dispone de una serie completa de CPU/GPU del ensayo inicial. Se conservan observaciones reales de `ollama ps`, `nvidia-smi`, latencia y RAM por conversación. No se inventaron percentiles de recursos faltantes.

## Actualizacion tras liberar memoria

Regresion final UTF-8: 30/30 herramientas con Ollama real aprobadas. Pipeline Laravel-gateway-HTTP-Ollama-MySQL: 8 comprobaciones por tenant (Tattoo y dental), incluida continuidad al cambiar fecha. Un fallo real anterior eligio 08:00 sin seleccion del cliente; el backend lo rechazo. Se restringio prepare_booking hasta disponer de hora explicita y se repitieron ambos casos, aprobados. No equivale a aprobar la rubrica linguistica global ni reservas completas por IA.

Chrome dividido por negocio: Tattoo, dental, comercio, sistema y V7 aprobados. Inferencia breve con niveles 1/2/3: seis respuestas, sin errores; primera 5202 ms, calientes 170-491 ms, 80.7-83.6 tokens/s. Sin prueba sostenida ni comparacion 4B.
