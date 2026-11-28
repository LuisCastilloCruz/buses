# Coherencia V7.2: resultado no aprobado globalmente

Muestra heredada:51/60 reproducido; primera correccion58/60; segunda60/60. Seis conversaciones largas usadas para diagnostico:78/78 en regresion final.

Conjunto independiente nuevo:12 conversaciones,196 turnos,168 correctos (85.71%). No alcanza95%. No se cambiaron las expectativas ni se ajusto el producto con esos resultados.

| Giro | Aciertos/turnos |
|---|---:|
|barberia|15/18|
|dental|24/26|
|estetica|15/18|
|hotel|15/18|
|peluqueria|15/18|
|restaurante|15/18|
|taller|15/18|
|tattoo|24/26|
|tienda|15/18|
|veterinaria|15/18|

Hay28 errores de seleccion/pertinencia conservados en QUALITY_RESULTS.json. Incluyen consultas de personal interpretadas como deseo de reservar en negocios sin adaptador, y consultas de servicios desviadas a documentos o respuestas generales. La recepcionista aun no generaliza suficientemente.

Los controles automaticos sobre respuestas visibles comprueban hechos acotados, repeticion de saludo y nombres de negocios; no certifican naturalidad. Los textos de hechos son respuestas controladas del backend/fixtures y no prosa libre generada por Ollama. El modelo real decide las propuestas. Las citas reales se prueban separadamente en MySQL.

La revision humana independiente de los196 turnos esta preparada en HUMAN_REVIEW.csv sin puntuaciones inventadas. No se afirma90% linguistico ni aptitud comercial.

Repeticion con producto final congelado:60/60, evidencia final-original-regression/. No cambia el85.71% independiente.
