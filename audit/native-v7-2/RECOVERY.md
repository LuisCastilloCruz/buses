# Respaldo y recuperacion V7.2

Restauracion ejecutada de cuatro bases ficticias en13328:1083tablas, conteos/digests coincidentes. SHA256 del dump privado:9d5c6dedcf0464919f77093fb5a6f2a70b56a763f3a2403036b10c9d2a83447f. MySQL fuente13327 reanudado sin migraciones. SQL y credenciales permanecen privados, fuera de Git.

La exportacion privada se registra en GIT_STATUS.md. El bundle incremental requiere la base241e4ce8, conservada en el repositorio privado verify.git y respaldo V7.1 verificado. No restaurar encima de un directorio existente. Importar el bundle en un repositorio de recuperacion que contenga esa base; verificar commit/tree antes de crear un nuevo worktree. Usar credenciales privadas solo en el laboratorio aislado y restaurar SQL unicamente en una instancia vacia marcada como desechable.
