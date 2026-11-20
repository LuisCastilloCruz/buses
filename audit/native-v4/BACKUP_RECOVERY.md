# Respaldo y recuperacion

Los respaldos estan fuera del worktree, en `C:/Users/luis castillo/MultiFactura-private-backups`, con ACL privada. Nunca publicarlos. Base V3: native-v3-20261120-final contiene bundle completo e incremental. V4 checkpoint-20261120-181130 contiene bundle hasta f6451188, configuracion privada necesaria para descifrar datos, adjuntos y cinco dumps. Se verifico bundle y se recupero la referencia en repo bare; los cinco dumps se restauraron en bases locales nuevas. No se sustituyeron bases de trabajo.

Para recuperacion de codigo: crear directorio NUEVO, clonar el bundle completo V3, fetch del incremental V3 y del bundle V4, verificar `git rev-parse` contra el manifiesto. No aplicar comandos destructivos al original. Un bundle incremental requiere sus prerrequisitos.

Para datos: revisar manifiesto/hash; iniciar MySQL de recuperacion aislado o usar una base nueva `mf_v4_restore_*`; comprobar puerto/datadir antes de importar. Restaurar los dumps y comparar esquema/conteos/contenido. Copiar la configuracion privada SOLO al entorno de recuperacion autorizado para conservar APP_KEY; sin la clave los campos/adjuntos cifrados no son recuperables. No imprimir la clave.

Checkpoint antiguo no incluye cambios posteriores. DELIVERY_STATUS registra el ultimo snapshot verificado cuando se complete. Prueba de rollback productivo no realizada. Proteger tambien copias externas cifradas y evaluar retencion antes de uso real.
