# Manual de usuario — INTELECTA

Alcance comprobado en octubre de 2026. La interfaz muestra únicamente opciones permitidas para la cuenta; una URL directa también se valida en el servidor.

## Ingreso y navegación

Abra `/login` e ingrese con una cuenta activa. No hay registro público. La creación de cuentas, asignación de roles y operaciones de bloqueo corresponden a la administración de seguridad. La recuperación de contraseña requiere correo configurado; en la demostración local se registra en el log privado.

El panel administrativo agrupa Gestión Institucional, Gestión de Postulantes, Gestión Evaluativa, Reportes y Análisis y Administración del Sistema. El portal del postulante ofrece evaluaciones, ficha y ranking. Los mensajes de prohibición de acceso indican permisos, estado de cuenta o vínculo académico insuficientes; no se resuelven cambiando la URL.

## Super Administrador

Dispone de acceso global y administración técnica. En `/admin/sistema/usuarios` crea cuentas, asigna roles y gestiona bloqueo, desbloqueo y reenvío de activación. En `/admin/sistema/roles-permisos` revisa la matriz autorizada. En `/admin/sistema/bitacora` consulta eventos y exporta, según permisos. El sistema protege la continuidad del último Super Administrador activo.

En `/admin/institucional/cargos` y `/admin/institucional/personal` administra la organización y vincula personal a cuentas elegibles. Un cargo expresa responsabilidad institucional; el acceso depende de roles y permisos, no del cargo por sí solo.

## Administrador

Gestiona la operación académica; no tiene por defecto administración técnica de seguridad.

| Ruta | Operación principal |
| --- | --- |
| `/admin/institucional/programas` | Crear y editar programas |
| `/admin/institucional/grupos` | Crear y editar paralelos, capacidad y horario |
| `/postulantes` | Registrar, consultar y editar expedientes y estados |
| `/admin/institucional/inscripciones` | Inscribir postulantes en contextos académicos activos |
| `/admin/institucional/tutores` | Gestionar tutores vinculados a personal institucional |
| `/admin/institucional/asignacion-tutores` | Asignar tutor, materia y grupo |
| `/admin/institucional/matriculas-cuotas` | Registrar matrículas y cuotas y sus estados |
| `/admin/institucional/habilitacion-academica` | Consultar y ajustar habilitación |
| `/admin/institucional/asistencia` | Registrar asistencia individual o del grupo |
| `/admin/institucional/simulacros` | Programar simulacros con plantilla y contexto |
| `/admin/institucional/ficha-academica` | Buscar expediente y abrir su ficha |
| `/admin/institucional/ranking` | Consultar clasificación y filtros académicos |

Los formularios validan fechas, valores y referencias. Las nuevas inscripciones deben respetar cupos y entidades activas; las matrículas se derivan de la inscripción. El cambio de cuotas recalcula la habilitación y puede sustituir un ajuste manual anterior: revise el estado resultante.

## Docente / tutor académico

Trabaja dentro de sus asignaciones activas. Puede consultar grupos, postulantes, fichas y resultados de su ámbito y registrar asistencia autorizada. Puede consultar el banco y crear preguntas conforme a la matriz vigente. El rol no ofrece acceso global a reportes, ranking, usuarios ni permisos. Los filtros del servidor limitan los recursos aunque un permiso permita entrar al módulo.

La ruta operativa de tutoría es `/admin/institucional/tutores`. La portada antigua `/admin/gestion-academica/docentes` muestra ejemplos estáticos y está identificada como simulada.

## Estudiante / postulante

La cuenta debe estar vinculada al expediente mediante `postulantes.user_id`. El correo de contacto por sí solo no establece identidad. Se requiere una cuenta activa y habilitación académica para iniciar evaluaciones.

1. En `/estudiante/evaluaciones`, revise las plantillas y simulacros disponibles.
2. Inicie una evaluación habilitada y seleccione alternativas.
3. Envíe las respuestas; el sistema guarda respuestas, finaliza el intento y calcula el resultado. Las omisiones reciben cero; el resultado finalizado no admite un segundo envío.
4. Consulte el resultado y sus respuestas desde el portal.
5. Revise `/estudiante/mi-ficha` y `/estudiante/ranking` para seguimiento personal.

Una cuenta sin vínculo o un postulante restringido ve mensajes de estado y no puede acceder a expedientes ajenos. Las plantillas aplicables exigen preguntas de alternativas con consistencia validada; no se ofrece corrección automática de respuestas abiertas.

## Evaluaciones y reportes

El banco sigue Materia → Área → Tema → Pregunta. Materias: `/admin/evaluaciones/materias`; áreas: `/areas-conocimiento`; temas: `/temas`; preguntas: `/preguntas`; plantillas: `/plantillas-evaluacion`. Los permisos separan consulta, creación y edición. La gestión de materias se reserva por defecto al Super Administrador.

`/admin/evaluaciones/resultados` muestra resultados operativos y `/reportes-academicos` proporciona consultas y descargas PDF/Excel. Las cifras de una instalación demo proceden de fixtures ficticios, aun cuando el cálculo y la persistencia sean reales.

`/admin/analisis/riesgo-academico` consulta rendimientos guardados y limita la vista a 24 registros. Sus categorías son reglas descriptivas. `/admin/analisis/learning-analytics` muestra gráficos simulados; no consulta la base ni genera predicciones. Configuración y catálogos de consulta conservan funciones limitadas; no deben presentarse como módulos completos de edición.

Para una exposición organizada consulte la [guía de demostración](guia_demostracion.md) y los [pendientes conocidos](estado_actual.md).
