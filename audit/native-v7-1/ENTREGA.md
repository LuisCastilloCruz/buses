# Entrega V7.1

Rama:feature/multifactura-ollama-coherence-v7-1. Publicar exclusivamente esta rama; nunca merge ni despliegue.

Respaldo privado externo:`C:\Users\luis castillo\MultiFactura-private-backups\native-v7-1-20261128-033455`.11 hashes verificados; codigo36ec1322614541bfba7e149299b5bc60ae06189f y configuracion privada nueva. Base del bundle44ff5486. No subir SQL,configuracion privada ni bundles.

Restauracion real:4 bases,1083 tablas, todos los conteos y hashes de filas iguales. SHA256 del dump68eaab962a4155f5e6120a27df7f527baaf55bd01459d5558508cad8eaded34b. Fuente13325,restauracion13326; servidor temporal detenido y fuente reanudada. HTTP200 tras recuperacion; max_connections20 restaurado.

Para recuperar codigo usar el bundle con su base44ff5486 ya conservada en respaldoV7; verificar `git bundle verify` e importar en un repositorio NUEVO, nunca sobrescribir worktrees. Recuperar SQL solo en un MySQL nuevo loopback con nombre/puerto/datos comprobados. El runner `native-lab/backup-restore-v6.py --offline-source` implementa la prueba reproducible. Los secretos privados del respaldo permiten arrancar ese laboratorio, no son configuracion para produccion.

Estado:reservas E2E reales aprobadas; coherencia comercial pendiente; sandboxexterno no validado; no apto para produccion. Ver PENDIENTES.md.
