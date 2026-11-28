# Modelos y documentación comprobada

| Modelo | Estado real | Observación |
|---|---|---|
| qwen3:1.7b | Instalado y ejecutado | Digest mostrado `8f68893c685c`; GGUF Q4_K_M. `/api/show` informa `parameter_size: 2.0B`, aunque el tag es 1.7b. |
| qwen3:4b | No descargado ni ejecutado | Catálogo oficial: Q4_K_M, 2,5 GB, Apache 2.0. La RAM disponible no permite aprobar su ensayo. |

Qwen3 instalado declara completion, tools y thinking; admite `think=false/true` y el valor predeterminado es true. Las pruebas de atención usaron `think=false`. Contexto probado: 4096. El metadato 40960 no demuestra que ese contexto sea viable en esta GPU.

Fuentes consultadas: [JSON Schema de Ollama](https://docs.ollama.com/capabilities/structured-outputs), [thinking](https://docs.ollama.com/capabilities/thinking), [Qwen3 4B y licencia](https://ollama.com/library/qwen3:4b).

No se actualizaron Ollama, CUDA, drivers ni paquetes globales. No se descargaron modelos 8B o mayores.