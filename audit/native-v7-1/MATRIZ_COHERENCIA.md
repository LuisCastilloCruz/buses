# Matriz de coherencia V7.1

Modelo real: qwen3:1.7b; thinking desactivado, contexto4096, temperatura0. Diez perfiles ficticios,23 escenarios por giro,6-9 turnos por conversacion. Datos y disponibilidad simulados en esta suite; E2E MySQL separado.

| Giro | Turnos correctos / evaluados | Porcentaje | Reservados |
|---|---:|---:|---:|
| barberia | 138/141 | 97.87% | 33/33 |
| dental | 135/141 | 95.74% | 31/33 |
| estetica | 136/141 | 96.45% | 33/33 |
| hotel | 136/141 | 96.45% | 33/33 |
| peluqueria | 137/141 | 97.16% | 33/33 |
| restaurante | 139/141 | 98.58% | 33/33 |
| taller | 137/141 | 97.16% | 33/33 |
| tattoo | 137/141 | 97.16% | 33/33 |
| tienda | 137/141 | 97.16% | 33/33 |
| veterinaria | 137/141 | 97.16% | 33/33 |

Total1369/1410 (97.09%); desarrollo1041/1080 (96.39%); reservado328/330 (99.39%). Los nombres esperados de herramientas quedaron fijados antes de ejecutar. No se ajustaron prompts con los casos reservados.

Memoria:230/230 estados finales conservan servicio, profesional y fecha derivados de las solicitudes explicitas. Auditoria deterministica posterior en STATE_AND_COMPARISON.json. Esto no prueba todos los estados intermedios posibles.

Comparacion de mismos casos: inicial23/54 frente52/54 final; iteracion1 53/66 frente64/66 final; iteracion2 160/168 frente161/168 final. Las muestras parciales tienen tamanos distintos; no comparar sus porcentajes como si fueran poblaciones iguales.

Hay41 fallos de enrutamiento conservados. Resumir cuando correspondia consultar disponibilidad/derivar es un patron residual. La muestra de regresion final booking, posterior al ultimo cambio, obtuvo51/60 (85%): ese segmento NO alcanza95%. No se elimina ni relaja el escenario. Los cambios especificos de nueva cita tras cancelacion si pasaron E2E real.

El objetivo global de calidad NO queda aprobado. La eleccion de herramientas usa restricciones del backend; no es calidad del modelo aislado. Las respuestas visibles de evaluacion provienen de fixtures del backend, no de redaccion libre del modelo. No se infiere90% de naturalidad de estas mediciones. HUMAN_REVIEW.csv queda sin puntuaciones: una persona debe revisar claridad, continuidad, tono y naturalidad con rubrica1-5 y justificar casos dudosos.

Rubrica humana:1=incorrecto/incomprensible;2=deficiencia importante;3=usable con correccion;4=claro y apropiado;5=natural y preciso. Revisar todos los fallos y al menos una muestra estratificada por giro/escenario. Identificar revisor y acuerdo entre revisores; no promediar una infraccion de seguridad como si fuera solo estilo.

Limitaciones:un seed de desarrollo7 y uno reservado71; perfiles y aperturas compartidos, no muestra estadistica de clientes reales. Sin diagnosticos, prescripciones, envios externos ni reservas para los ocho giros sin adaptador.
