# Documentación técnica de INTELECTA

## Entrega vigente: Hito 5, octubre de 2026

- [Instalación y ejecución](guia_instalacion.md)
- [Manual de usuario por rol](manual_usuario.md)
- [Estado actual, evidencia y pendientes](estado_actual_hito5.md)
- [Guía de demostración](guia_demostracion.md)
- [Verificación de la entrega](verificacion_hito5.md)
- [Arquitectura principal](../README_ARQUITECTURA.md)
- [Escenario ficticio Avalancha](base_limpia_avalancha.md)
- [Cuentas exclusivamente demo](usuarios_prueba_avalancha.md)

## Auditorías y decisiones históricas

Estos documentos conservan el contexto y las comprobaciones de su fecha. Sus hallazgos pueden haber sido resueltos posteriormente; para el alcance vigente consulte el estado del Hito 5.

- [Auditoría inicial](auditoria_final_sistema_intelecta.md) y [auditoría v2](auditoria_final_sistema_intelecta_v2.md)
- [Base de datos y tercera forma normal](auditoria_base_datos_3fn_intelecta.md)
- [Validaciones básicas de septiembre](auditoria-validaciones-basicas-2026-09-07.md)
- [Fecha de nacimiento](auditoria-fecha-nacimiento-2026-09-07.md)
- [Identidad estudiantil estable](identidad-estable-bloque-1.md)
- [Ciclo de vida de cuentas](ciclo-vida-cuentas-bloque-2.md)
- [Cargos y personal](cargos-personal-bloque-3.md)
- [Identidad de tutores](tutor-personal-bloque-4.md)
- [Gobierno RBAC](rbac-bloque-5.md) y [matriz de permisos](rbac-matriz-bloque-5.md)
- [Plan de respaldo incremental](plan_backup_incremental_intelecta.md)

Los JSON y capturas de septiembre son evidencias históricas, no resultados de las pruebas de octubre. Los scripts `test-postgres-*.php` y `test-birth-date-ui.*` son utilidades de auditoría: algunos reconstruyen bases expresamente aisladas o modifican fixtures. No los ejecute contra la base de trabajo. La suite normal se ejecuta con `php artisan test`, configurada en memoria; consulte las precauciones de instalación.
