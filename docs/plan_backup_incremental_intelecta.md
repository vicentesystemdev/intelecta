# Plan de Backup Incremental - INTELECTA

## 1. Alcance

Este documento define una estrategia técnica recomendada para respaldos de INTELECTA. El sistema no debe ejecutar backups desde pantallas web normales ni almacenar credenciales sensibles dentro de la aplicación.

El objetivo es preparar una política institucional para PostgreSQL, archivos operativos y restauración, dejando claro que la automatización real debe implementarse en una fase posterior desde infraestructura controlada.

## 2. Estrategia recomendada

- Backup completo semanal de PostgreSQL usando herramientas administrativas como `pg_dump` o mecanismos equivalentes del proveedor de infraestructura.
- Archivado WAL diario o continuo para recuperación incremental y reducción de pérdida de datos.
- Respaldo de `storage/app` si el sistema almacena archivos cargados o generados.
- Respaldo de archivos de configuración no sensibles, manuales operativos y documentación técnica.
- No respaldar `.env` real en repositorio ni en ubicaciones compartidas sin cifrado.
- Rotación diaria, semanal y mensual de respaldos.
- Prueba de restauración periódica para validar que el respaldo es utilizable.

## 3. Política de seguridad

- Cifrar respaldos en reposo.
- Restringir acceso a personal autorizado.
- Separar credenciales de backup de las credenciales de aplicación.
- Validar integridad de archivos respaldados.
- Registrar fecha, responsable, tamaño, ubicación segura y resultado de cada respaldo.
- Evitar exponer rutas internas, secretos o archivos `.env` desde la interfaz web.

## 4. Automatización futura

Una fase posterior puede incorporar:

- Scheduler del servidor o herramienta de orquestación externa.
- Comando Artisan opcional para registrar estado de backup, no para almacenar secretos.
- Logs técnicos de ejecución.
- Alertas ante fallos de respaldo.
- Verificación automática de integridad.
- Registro del resultado en `bitacora_sistema`.

No se recomienda que el botón de una pantalla administrativa ejecute `pg_dump` directamente. La interfaz puede mostrar estado, pero la ejecución debe estar controlada por infraestructura.

## 5. Vista futura

Una vista futura de monitoreo de backups podría mostrar:

- Último backup completo.
- Último WAL o incremental registrado.
- Estado de ejecución.
- Tamaño del respaldo.
- Ruta segura o identificador externo.
- Responsable o proceso ejecutor.
- Resultado de la última prueba de restauración.
- Observaciones técnicas.

La vista no debe mostrar secretos, contraseñas, tokens ni rutas sensibles completas.

## 6. Relación con bitácora

Toda ejecución futura de backup debe registrar un evento en `bitacora_sistema`.

Acciones sugeridas:

- `backup_completo_generado`.
- `backup_incremental_generado`.
- `backup_fallido`.
- `restauracion_probada`.

Datos sugeridos para registrar:

- tipo de backup.
- fecha y hora.
- resultado.
- tamaño aproximado.
- responsable o proceso.
- observación técnica.

No se deben guardar credenciales ni contenido completo de archivos respaldados en la bitácora.
