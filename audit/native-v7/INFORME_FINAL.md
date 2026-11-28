# V7: laboratorio funcional, calidad comercial no aprobada

Codigo publicado: 57b96f68c3588eebc01aec77c1bb0cec5907f255.
Rama: feature/multifactura-ollama-universal-v7.
https://github.com/LuisCastilloCruz/buses/tree/feature/multifactura-ollama-universal-v7

MultiFactura mantiene su login, sidebar y modulos. Ollama local usa gateway firmado, herramientas limitadas, memoria cifrada y conocimiento administrativo separado por tenant. No hay conexion a proveedores externos ni VPS. El modo local queda desactivado salvo activacion explicita del laboratorio.

## Evidencia

- 100 conversaciones / 300 turnos reales, diez giros. El texto libre no alcanzo continuidad suficiente.
- Herramientas JSON Schema: 30/30 en regresion final real.
- Integracion Laravel, gateway, Ollama y MySQL: 8/8 dental y 8/8 Tattoo. Correccion real de hora inventada: no ofrecer preparar reserva sin hora seleccionada.
- Chrome: cinco fases aprobadas. Pantallas originales y nuevas capturadas.
- Unitarias, gateway, HTTP y persistencia ejecutados; VALIDATION_RUNS.json distingue fallos, bloqueos y aprobaciones.
- MySQL: 2, 5 y 10 escritores; una cita por horario. Nivel 25 bloqueado por recursos.
- Inferencia: seis solicitudes en niveles 1/2/3; no equivale a carga sostenida.
- Recuperacion final: cinco bases, 1421 tablas, hashes y conteos coincidentes. Bundle recuperado en referencia privada nueva y SHA confirmado.

## Respaldo privado

Fuera del worktree: C:\Users\luis castillo\MultiFactura-private-backups\native-v7-20261128-021657.
13 archivos con hashes verificados, configuracion privada y bundle incremental sobre V6 a88b7555f81710b3703edc90405af59c206d31c3. La restauracion se probo; no se publican SQL ni credenciales. RECOVERY_FINAL.json contiene solo evidencia no sensible.

## Uso local

Superadministrador: http://localhost:8879
Dental: http://dental.localhost:8879
Tattoo: http://tattoo.localhost:8879
Comercio: http://comercio.localhost:8879
Credenciales ficticias: .native-runtime/ACCESS.txt, privado.
Instrucciones: MANUAL_RECEPCIONISTA.md. Nunca copiar credenciales productivas.

## Pendiente y bloqueo de aprobacion

La rubrica humana no esta completada y no se acredita el objetivo global 95%/90%. Falta ampliar los 23 escenarios, conversaciones prolongadas, reservas completas con Ollama y confirmacion, evaluacion reservada amplia, stress sostenido y comparacion 4B. Las pruebas nativas reales actuales consultan catalogo/disponibilidad y memoria; no certifican el ciclo completo de reserva por IA.

Persisten 117 avisos Composer en 23 paquetes y 15 abandonados. No apto para exposicion publica o produccion. VPS, WhatsApp/OpenAI reales y validacion clinica/legal siguen pendientes. No se hizo merge, despliegue ni cambio en V6/produccion.

Consultar TEST_RESULTS.md, ERRORES_CORREGIDOS.md, MATRIZ_COHERENCIA.md, PENDIENTES.md y GITHUB_CI.md.
