# Fallos, causas y correcciones V7.2

1. Muestra heredada51/60 reproducida con Qwen real. Los nueve fallos repetian request_summary despues de una nueva solicitud. Resumen disponible sin solicitud actual explicita. Se clasifico el acto del ultimo mensaje; datos de catalogo reconocidos determinan el siguiente paso. Iteracion1:58/60, dos derivaciones innecesarias; iteracion2:60/60. Expectativas intactas.
2. Dialogos largos nuevos: politicas de cancelacion confundidas con cancelaciones; modulos sin agenda derivaban antes de consultar documentos. Clasificacion informativa anterior a acciones y conocimiento como siguiente paso.
3. Se exponian herramientas de modificar citas sin booking_id autorizado; el modelo inventaba id1. Backend ya rechazaba la accion. Ahora no se ofrece esa herramienta sin estado e identidad de cita correctos.
4. Saludos repetidos y si ambiguo: saludo solo para saludo actual; aclaracion de hora cuando ya hay opciones; un si ante propuesta pendiente muestra el codigo de confirmacion, sin crear cita.
5. Presentacion de derivacion empatica preserva avisos clinicos y hechos/confirmaciones del backend. Pruebas de regresion13 aprobadas.
6. Runner nuevo tuvo dos errores de sintaxis antes de inferencia; corregidos y node --check aprobado. Un healthcheck Ollama agoto2segundos; ejecucion parcial conservada, reintento independiente, sin repetir operaciones externas ni afirmar suite aprobada.

Tercer ciclo para los casos largos afectados:78/78 (seis conversaciones). El conjunto inicial parcial dejo de ser reservado al usarse para diagnostico. INDEPENDENT_MANIFEST.json contiene12 conversaciones/196turnos nuevos congelados despues; no se usan para afinar esta version. Resultados pendientes de consolidar.

Las reglas no contienen los nombres de los diez negocios ni precios/frases completas de tests. Seleccionan capacidades a partir de intencion informativa, campos verificados y estado. La clasificacion deterministica sigue siendo limitada; casos no comprendidos no autorizan escrituras.
