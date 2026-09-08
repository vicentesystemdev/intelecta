# INTELECTA — Validaciones básicas de entrada

Fecha: 7 de septiembre de 2026. Implementación local en `C:/laragon/www/intelecta`.

## 1. Resumen

Implementación completada sin cambiar la arquitectura React → Form Request → DTO → Action/Service → Repository → Eloquent. Laravel valida y normaliza; React incorpora ayudas y errores por campo; PostgreSQL protege invariantes simples.

- 28 Form Requests existentes revisados; 4 nuevos de autenticación y una base compartida. Resultado: 32 requests concretos y una clase base.
- 42 archivos React inspeccionados (formularios, variantes de edición y componentes asociados); 28 archivos JSX modificados.
- Aproximadamente 139 claves de entrada revisadas, incluidos subcampos de arrays y credenciales. El inventario por clave aparece en el anexo.
- 85 archivos de esta tarea, contando nuevos archivos, este informe, la evidencia JSON y el `.env` local no versionado. `composer.json` tiene un cambio anterior y no pertenece a esta tarea.
- 4 archivos nuevos de tests: 3 PHPUnit y 1 Node. Se añadieron 87 casos PHPUnit y 4 casos Node. Además, un script ejecuta 83 casos de integración PostgreSQL.
- 1.268 registros revisados para formatos y límites en 16 tablas. Cero incompatibilidades pendientes según estas comprobaciones.
- No se instalaron paquetes, ni se modificaron DTOs, fillable, Services, Repositories, autorización o reglas académicas excluidas.

No existe fecha de nacimiento, sexo, dirección, complemento o expedido separado en los formularios de postulantes. Se conservó `edad_post`, con el límite institucional existente de 14–80 años; no se inventaron columnas. La gestión sigue siendo dinámica: 2000 hasta el año actual + 1.

## 2. Validaciones implementadas

| Módulo | Campo | Antes | Ahora | Frontend | Backend | DB |
| --- | --- | --- | --- | --- | --- | --- |
| Postulantes, usuarios, perfil, tutores, responsable de grupo | Nombres/apellidos | Texto con máximo | Unicode, mínimo 2; letras, marcas, espacios, guion y apóstrofes; máximo según campo | Patrón Unicode y longitudes | Regla compartida | Columnas existentes |
| Todos los correos | Email/correo | Reglas y máximos desiguales | RFC + dominio completo; máximo 254 (tutor conserva 180); unique existente e ignore en edición | type=email y máximo | Normalización + reglas compartidas | Unique de usuarios existente; no se agregó unicidad al email del postulante |
| Postulantes y tutores | CI | string/max/unique | 4–30 caracteres, al menos un dígito y separadores controlados | Patrón y longitud | Regla compartida + unique existente | Unique existente |
| Postulantes y tutores | Celular | Texto libre | 7–15 dígitos, + inicial opcional; presentación eliminada al normalizar | type=tel, inputMode, patrón; admite presentación | Formato nacional/internacional básico; sin asegurar que el número exista | Columna existente |
| Postulantes | Edad/gestión/turno | Límites presentes; turno abierto | Edad 14–80 centralizada; gestión dinámica; turno finito incluyendo Fin de Semana | Límites y opciones consistentes | Constantes y allowlist | Edad y rango de almacenamiento de gestión |
| Áreas, temas, programas, grupos, plantillas, simulacros | Nombres/títulos | Algunos mínimos ausentes | Trim, espacios compactados, mínimo 2 y máximos de 120/160/180 según campo | Longitudes y required | Reglas compatibles create/update | Columnas existentes |
| Programas, grupos, matrículas | Código | Texto libre con unique | Mayúsculas; letras, números, guion y guion bajo; 2–60 caracteres | Patrón y longitud | Formato + unique existente | Unique existente |
| Grupos | Capacidad | 1–500 en backend; UX parcial | Entero 1–500; se rechaza booleano | min/max/step | integer + límites | CHECK 1–500; no controla ocupación |
| Evaluaciones | Enunciado, explicaciones, alternativas | Máximos presentes; errores parciales | Límites preservados, caracteres de control rechazados, saltos internos conservados | maxLength y errores individuales | Límites y estructura | Columnas existentes |
| Evaluaciones | Alternativas/preguntas/ponderaciones | Callbacks podían lanzar TypeError | Lista y estructura de cada fila, límites, distinct, decimales 0–2; callbacks solo tras validar forma | Errores por fila/campo y controles numéricos | 422 para estructuras manipuladas; se conserva suma 100 y cantidad de alternativas | Puntajes, órdenes y duración |
| Preguntas/temas | Clasificaciones | Selects no incluían valores reales | Se conservan opciones actuales y valores legítimos de BD; nivel preuniversitario aceptado | Opciones ampliadas | Allowlist finita | Sin nueva restricción de estados |
| Programas, asignaciones, habilitación, simulacros | Fechas/horas | Comparaciones no defensivas ante otro campo malformado | Fecha calendario YYYY-MM-DD y hora HH:mm; final ≥ inicial, horas de simulacro final > inicial | date/time y límites existentes | Comparación solo con referencia válida; reglas de fechas pasadas existentes conservadas | CHECK de intervalos |
| Matrículas/cuotas | Montos/número | numeric sin escala; UX parcial | 0–99.999.999,99, máximo 2 decimales; número de cuota 1–120 | min/max/step | numeric/decimal/integer | CHECK monto y número |
| Habilitación | Switches | boolean backend; errores no visibles en cada switch | Valores booleanos Laravel; mensajes al lado del control | Errores individuales | boolean | Tipo boolean existente |
| Asistencia | Sesión, fecha, lista de registros | Cast de array a string; consulta unique antes de fecha válida | Normalización segura; sesión predeterminada General; estructura defensiva | Errores en cabecera y por postulante | Unique compuesta consultada solo después de validar campos; no cambia inscripción activa exigida anteriormente | FK/unique existentes |
| Roles/respuestas | Arrays e IDs | Cobertura parcial de forma y límites | Lista, máximo, distinct, filas conocidas y límites de almacenamiento | Errores por permiso y respuesta | integer/exists, límites y 422 | FK existentes |
| Registro, usuarios, cambio/reset | Contraseñas | Reglas desiguales | string, estándar Password::defaults (mínimo 8), confirmed, máximo 72 bytes al establecer nueva contraseña | minLength y errores; opcional al editar usuario | No se normalizan credenciales; confirmación y contraseña actual rechazan arrays | Hash existente |
| Transversal | Opcionales, textos, IDs y estados | Normalización desigual; mensajes en inglés | Vacíos semánticos a null, límites existentes, control de tipos, enums disponibles y español | Ayudas y errores por campo | Base compartida, bail, reglas y traducciones | Sin duplicar regex |

Los IDs conservan sus `exists` y las condiciones previas del proyecto; no se añadieron filtros de actividad/compatibilidad. Los IDs y enteros no aceptan JSON true como 1. Los tiempos reportados y los intentos usan límites de almacenamiento (2.147.483.647 segundos y 32.767 intentos), sin implementar expiración ni un límite académico nuevo. Arrays: alternativas ≤ 5; preguntas/respuestas ≤ 1.000; asistencia/permisos ≤ 500. Los límites de texto largo siguen el contexto: 1.000, 2.000, 3.000 o 5.000 caracteres.

## 3. Normalizaciones implementadas

- Nombres y textos cortos: trim Unicode y compactación de espacios internos.
- Correo: solo trim + minúsculas; no se compacta la parte local ni se eliminan +alias o puntos.
- Códigos: trim + mayúsculas; no se aplica a nombres humanos.
- Teléfonos: se eliminan espacios, paréntesis y guiones de presentación; se conserva +.
- Opcionales vacíos: null. La sesión vacía conserva el predeterminado General existente.
- Textos largos y alternativas: trim exterior; se conservan espacios/saltos interiores.
- Arrays: normalización recursiva sin convertir estructuras inválidas a strings.
- Contraseñas, confirmación, contraseña actual y tokens: excluidos de normalización.

No se ejecutó una limpieza de datos reales.

## 4. Reglas reutilizables creadas

- [app/Http/Requests/NormalizedFormRequest.php](C:/laragon/www/intelecta/app/Http/Requests/NormalizedFormRequest.php): prepareForValidation, bail, rechazo de controles y booleanos en enteros.
- [app/Support/Validation/InputNormalizer.php](C:/laragon/www/intelecta/app/Support/Validation/InputNormalizer.php): normalización recursiva.
- [app/Support/Validation/InputRules.php](C:/laragon/www/intelecta/app/Support/Validation/InputRules.php): reglas comunes, edades, límites de listas y comparación defensiva de fechas.
- [app/Rules/InputFormat.php](C:/laragon/www/intelecta/app/Rules/InputFormat.php): formatos Unicode, documento, teléfono, código, dominio completo y límite de bytes de contraseña.
- [resources/js/lib/inputValidation.js](C:/laragon/www/intelecta/resources/js/lib/inputValidation.js): ayudas HTML compartidas; overrides para campos requeridos según contexto.
- [resources/input-options.json](C:/laragon/www/intelecta/resources/input-options.json): opciones de turno y clasificación compartidas por Laravel y React.
- Reutilizados los enums EstadoRegistro y EstadoSimulacro. UpdatePostulante, UpdateTema y UpdateAreaConocimiento heredan las reglas de creación, manteniendo su autorización e ignore de unicidad.

## 5. PostgreSQL

Aplicada únicamente [database/migrations/2026_09_07_000000_add_basic_input_checks.php](C:/laragon/www/intelecta/database/migrations/2026_09_07_000000_add_basic_input_checks.php). Los 18 CHECK aparecen con `convalidated = true`. La migración comprueba IDs incompatibles antes de crear restricciones y falla sin corregir registros. PostgreSQL ejecuta la migración transaccionalmente; SQLite omite estos CHECK específicos. La reversión elimina solo estos CHECK.

| Tabla | CHECK | Condición |
| --- | --- | --- |
| postulantes | intelecta_postulantes_edad_check | edad_post BETWEEN 14 AND 80 |
| postulantes | intelecta_postulantes_gestion_check | gestion_post BETWEEN 2000 AND 32767 |
| grupos_academicos | intelecta_grupos_capacidad_check | capacidad_grupo BETWEEN 1 AND 500 |
| preguntas | intelecta_preguntas_puntaje_check | puntaje_preg BETWEEN 0.01 AND 100 |
| preguntas | intelecta_preguntas_tiempo_check | tiempo_estimado_seg_preg BETWEEN 15 AND 7200 |
| alternativas | intelecta_alternativas_orden_check | orden_alt BETWEEN 1 AND 5 |
| plantillas_evaluacion | intelecta_plantillas_duracion_check | duracion_minutos_plan BETWEEN 1 AND 480 |
| plantilla_preguntas | intelecta_plantilla_preguntas_orden_check | orden_pp BETWEEN 1 AND 1000 |
| plantilla_preguntas | intelecta_plantilla_preguntas_puntaje_check | puntaje_pp BETWEEN 0.01 AND 100 |
| programas_academicos | intelecta_programas_fechas_check | fecha_fin_prog >= fecha_inicio_prog |
| asignaciones_tutores | intelecta_asignaciones_fechas_check | fecha_fin_asig >= fecha_inicio_asig |
| habilitaciones_academicas | intelecta_habilitaciones_fechas_check | fecha_fin_hab >= fecha_inicio_hab |
| simulacros_programados | intelecta_simulacros_horas_check | hora_fin_sim > hora_inicio_sim |
| matriculas_academicas | intelecta_matriculas_monto_check | monto_matricula_mat BETWEEN 0 AND 99999999.99 |
| cuotas_academicas | intelecta_cuotas_monto_check | monto_cuota BETWEEN 0 AND 99999999.99 |
| cuotas_academicas | intelecta_cuotas_numero_check | nro_cuota BETWEEN 1 AND 120 |
| evaluaciones_aplicadas | intelecta_evaluaciones_porcentaje_check | porcentaje_eval_apl BETWEEN 0 AND 100 |
| rendimientos_postulante | intelecta_rendimientos_asistencia_check | asistencia_porcentaje_rend BETWEEN 0 AND 100 |

No se añadieron UNIQUE, NOT NULL, FK ni regex SQL. Se conservaron las restricciones existentes. Los CHECK permiten null donde la columna ya era opcional; la escala decimal sigue protegida por Laravel y por los tipos decimal existentes (PostgreSQL puede redondear escrituras directas).

## 6. Tests

Resultados finales:

| Ejecución | Ejecutados | Pasados | Fallidos | Assertions | Duración |
| --- | ---: | ---: | ---: | ---: | ---: |
| PHPUnit, suite completa | 163 | 163 | 0 | 725 | 20,108 s |
| PostgreSQL, casos de los 18 CHECK | 83 | 83 | 0 | 83 | 0,434 s |
| Node, ayudas HTML/patrones | 4 | 4 | 0 | No informado por node:test | 0,127 s |

Comandos (usar el PHP/Node instalados en Laragon):

```text
php vendor/phpunit/phpunit/phpunit --colors=never
node --test tests/input-validation.test.mjs
php docs/test-postgres-input-checks.php
php docs/audit-input-data.php
npm run build
php vendor/bin/pint --dirty --test
git diff --check
```

Build final correcto: 3.444 módulos, 26,42 s. Pint: passed. git diff --check: sin problemas. Análisis de referencias de 28 JSX: cero identificadores sin enlace. La verificación de React fue por código, compilación y tests, no una revisión visual manual de cada modal.

La primera suite falló en páginas por ausencia de public/build/manifest.json; se resolvió compilando. Las pruebas nuevas detectaron un patrón HTML mal escapado, corregido y probado en modo Unicode v. Un caso de prueba se ajustó para colocar el byte nulo dentro de una fecha, porque el middleware existente ya recorta bytes nulos exteriores. Las ejecuciones finales no presentan fallos.

## 7. Datos existentes incompatibles

La evidencia final está en [docs/validaciones-datos-2026-09-07.json](C:/laragon/www/intelecta/docs/validaciones-datos-2026-09-07.json). La revisión de formatos cubre 1.268 registros en 16 tablas; las 18 condiciones SQL se comprobaron en sus tablas completas. Cero incompatibilidades pendientes y cero registros que requieran la normalización evaluada.

Se detectaron incompatibilidades anteriores entre catálogo real y opciones de entrada, resueltas conservando valores legítimos:

- Temas IDs 1–51: nivel preuniversitario, omitido por las reglas y selects anteriores. Ahora permitido; no se alteraron esos registros.
- Turno Fin de Semana y clasificaciones existentes de exigencia/habilidad: incorporadas a las listas compartidas, sin convertir valores o reescribir registros.

El auditor no comprueba reglas de negocio, historial, identidad o elegibilidad excluidas; cero incompatibilidades aquí no significa una auditoría global de seguridad sin hallazgos.

## 8. Problemas no modificados

Permanecen para fases posteriores: apropiación/vinculación de identidad por correo; autorización y Policies; último Super Administrador; snapshot/versionado e integridad histórica; eliminación de alternativas; respuesta corta; expiración y concurrencia de evaluaciones; ocupación/cupos de grupos; compatibilidad de simulacros y tutor/grupo; transiciones de estados; inscripción en entidades inactivas; reportes y sus fórmulas; colas; dependencias vulnerables. No se eliminaron comprobaciones académicas que ya existían.

## 9. Archivos modificados

Lista completa de archivos de esta tarea, incluidos nuevos archivos y el .env local:

- [.env](C:/laragon/www/intelecta/.env)
- [.env.example](C:/laragon/www/intelecta/.env.example)
- [app/Http/Controllers/Auth/ConfirmablePasswordController.php](C:/laragon/www/intelecta/app/Http/Controllers/Auth/ConfirmablePasswordController.php)
- [app/Http/Controllers/Auth/NewPasswordController.php](C:/laragon/www/intelecta/app/Http/Controllers/Auth/NewPasswordController.php)
- [app/Http/Controllers/Auth/PasswordController.php](C:/laragon/www/intelecta/app/Http/Controllers/Auth/PasswordController.php)
- [app/Http/Controllers/Auth/PasswordResetLinkController.php](C:/laragon/www/intelecta/app/Http/Controllers/Auth/PasswordResetLinkController.php)
- [app/Http/Controllers/Auth/RegisteredUserController.php](C:/laragon/www/intelecta/app/Http/Controllers/Auth/RegisteredUserController.php)
- [app/Http/Controllers/ProfileController.php](C:/laragon/www/intelecta/app/Http/Controllers/ProfileController.php)
- [app/Http/Requests/Admin/StoreUsuarioRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Admin/StoreUsuarioRequest.php)
- [app/Http/Requests/Admin/UpdateRolPermisosRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Admin/UpdateRolPermisosRequest.php)
- [app/Http/Requests/Admin/UpdateUsuarioRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Admin/UpdateUsuarioRequest.php)
- [app/Http/Requests/Auth/LoginRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Auth/LoginRequest.php)
- [app/Http/Requests/Auth/PasswordEmailRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Auth/PasswordEmailRequest.php)
- [app/Http/Requests/Auth/RegisterRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Auth/RegisterRequest.php)
- [app/Http/Requests/Auth/ResetPasswordRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Auth/ResetPasswordRequest.php)
- [app/Http/Requests/Auth/UpdatePasswordRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Auth/UpdatePasswordRequest.php)
- [app/Http/Requests/Evaluaciones/StoreAreaConocimientoRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Evaluaciones/StoreAreaConocimientoRequest.php)
- [app/Http/Requests/Evaluaciones/StorePlantillaEvaluacionRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Evaluaciones/StorePlantillaEvaluacionRequest.php)
- [app/Http/Requests/Evaluaciones/StorePreguntaRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Evaluaciones/StorePreguntaRequest.php)
- [app/Http/Requests/Evaluaciones/StoreTemaRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Evaluaciones/StoreTemaRequest.php)
- [app/Http/Requests/Evaluaciones/UpdateAreaConocimientoRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Evaluaciones/UpdateAreaConocimientoRequest.php)
- [app/Http/Requests/Evaluaciones/UpdateTemaRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Evaluaciones/UpdateTemaRequest.php)
- [app/Http/Requests/Institucional/AsignacionTutorRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Institucional/AsignacionTutorRequest.php)
- [app/Http/Requests/Institucional/AsistenciaAcademicaRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Institucional/AsistenciaAcademicaRequest.php)
- [app/Http/Requests/Institucional/AsistenciaGrupoRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Institucional/AsistenciaGrupoRequest.php)
- [app/Http/Requests/Institucional/CuotaAcademicaRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Institucional/CuotaAcademicaRequest.php)
- [app/Http/Requests/Institucional/GrupoAcademicoRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Institucional/GrupoAcademicoRequest.php)
- [app/Http/Requests/Institucional/HabilitacionAcademicaRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Institucional/HabilitacionAcademicaRequest.php)
- [app/Http/Requests/Institucional/InscripcionAcademicaRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Institucional/InscripcionAcademicaRequest.php)
- [app/Http/Requests/Institucional/MatriculaAcademicaRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Institucional/MatriculaAcademicaRequest.php)
- [app/Http/Requests/Institucional/ProgramaAcademicoRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Institucional/ProgramaAcademicoRequest.php)
- [app/Http/Requests/Institucional/SimulacroProgramadoRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Institucional/SimulacroProgramadoRequest.php)
- [app/Http/Requests/Institucional/TutorAcademicoRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Institucional/TutorAcademicoRequest.php)
- [app/Http/Requests/NormalizedFormRequest.php](C:/laragon/www/intelecta/app/Http/Requests/NormalizedFormRequest.php)
- [app/Http/Requests/Postulantes/StorePostulanteRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Postulantes/StorePostulanteRequest.php)
- [app/Http/Requests/Postulantes/UpdatePostulanteRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Postulantes/UpdatePostulanteRequest.php)
- [app/Http/Requests/ProfileUpdateRequest.php](C:/laragon/www/intelecta/app/Http/Requests/ProfileUpdateRequest.php)
- [app/Http/Requests/Resultados/EnviarRespuestasEvaluacionRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Resultados/EnviarRespuestasEvaluacionRequest.php)
- [app/Http/Requests/Resultados/IniciarEvaluacionAplicadaRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Resultados/IniciarEvaluacionAplicadaRequest.php)
- [app/Rules/InputFormat.php](C:/laragon/www/intelecta/app/Rules/InputFormat.php)
- [app/Support/Validation/InputNormalizer.php](C:/laragon/www/intelecta/app/Support/Validation/InputNormalizer.php)
- [app/Support/Validation/InputRules.php](C:/laragon/www/intelecta/app/Support/Validation/InputRules.php)
- [config/app.php](C:/laragon/www/intelecta/config/app.php)
- [database/migrations/2026_09_07_000000_add_basic_input_checks.php](C:/laragon/www/intelecta/database/migrations/2026_09_07_000000_add_basic_input_checks.php)
- [docs/audit-input-data.php](C:/laragon/www/intelecta/docs/audit-input-data.php)
- [docs/auditoria-validaciones-basicas-2026-09-07.md](C:/laragon/www/intelecta/docs/auditoria-validaciones-basicas-2026-09-07.md)
- [docs/test-postgres-input-checks.php](C:/laragon/www/intelecta/docs/test-postgres-input-checks.php)
- [docs/validaciones-datos-2026-09-07.json](C:/laragon/www/intelecta/docs/validaciones-datos-2026-09-07.json)
- [lang/es/auth.php](C:/laragon/www/intelecta/lang/es/auth.php)
- [lang/es/passwords.php](C:/laragon/www/intelecta/lang/es/passwords.php)
- [lang/es/validation.php](C:/laragon/www/intelecta/lang/es/validation.php)
- [resources/input-options.json](C:/laragon/www/intelecta/resources/input-options.json)
- [resources/js/Components/Evaluaciones/AlternativasEditor.jsx](C:/laragon/www/intelecta/resources/js/Components/Evaluaciones/AlternativasEditor.jsx)
- [resources/js/Components/Evaluaciones/PlantillaEvaluacionForm.jsx](C:/laragon/www/intelecta/resources/js/Components/Evaluaciones/PlantillaEvaluacionForm.jsx)
- [resources/js/Components/Evaluaciones/PreguntaForm.jsx](C:/laragon/www/intelecta/resources/js/Components/Evaluaciones/PreguntaForm.jsx)
- [resources/js/Components/Postulantes/PostulanteForm.jsx](C:/laragon/www/intelecta/resources/js/Components/Postulantes/PostulanteForm.jsx)
- [resources/js/Components/Sistema/UsuarioForm.jsx](C:/laragon/www/intelecta/resources/js/Components/Sistema/UsuarioForm.jsx)
- [resources/js/Pages/Auth/ConfirmPassword.jsx](C:/laragon/www/intelecta/resources/js/Pages/Auth/ConfirmPassword.jsx)
- [resources/js/Pages/Auth/ForgotPassword.jsx](C:/laragon/www/intelecta/resources/js/Pages/Auth/ForgotPassword.jsx)
- [resources/js/Pages/Auth/Login.jsx](C:/laragon/www/intelecta/resources/js/Pages/Auth/Login.jsx)
- [resources/js/Pages/Auth/Register.jsx](C:/laragon/www/intelecta/resources/js/Pages/Auth/Register.jsx)
- [resources/js/Pages/Auth/ResetPassword.jsx](C:/laragon/www/intelecta/resources/js/Pages/Auth/ResetPassword.jsx)
- [resources/js/Pages/Estudiante/Evaluaciones.jsx](C:/laragon/www/intelecta/resources/js/Pages/Estudiante/Evaluaciones.jsx)
- [resources/js/Pages/Evaluaciones/Areas/Create.jsx](C:/laragon/www/intelecta/resources/js/Pages/Evaluaciones/Areas/Create.jsx)
- [resources/js/Pages/Evaluaciones/Areas/Index.jsx](C:/laragon/www/intelecta/resources/js/Pages/Evaluaciones/Areas/Index.jsx)
- [resources/js/Pages/Evaluaciones/Temas/Create.jsx](C:/laragon/www/intelecta/resources/js/Pages/Evaluaciones/Temas/Create.jsx)
- [resources/js/Pages/Evaluaciones/Temas/Index.jsx](C:/laragon/www/intelecta/resources/js/Pages/Evaluaciones/Temas/Index.jsx)
- [resources/js/Pages/Institucional/AsignacionTutores/Index.jsx](C:/laragon/www/intelecta/resources/js/Pages/Institucional/AsignacionTutores/Index.jsx)
- [resources/js/Pages/Institucional/Asistencia/Index.jsx](C:/laragon/www/intelecta/resources/js/Pages/Institucional/Asistencia/Index.jsx)
- [resources/js/Pages/Institucional/Grupos/Index.jsx](C:/laragon/www/intelecta/resources/js/Pages/Institucional/Grupos/Index.jsx)
- [resources/js/Pages/Institucional/HabilitacionAcademica/Index.jsx](C:/laragon/www/intelecta/resources/js/Pages/Institucional/HabilitacionAcademica/Index.jsx)
- [resources/js/Pages/Institucional/Inscripciones/Index.jsx](C:/laragon/www/intelecta/resources/js/Pages/Institucional/Inscripciones/Index.jsx)
- [resources/js/Pages/Institucional/MatriculasCuotas/Index.jsx](C:/laragon/www/intelecta/resources/js/Pages/Institucional/MatriculasCuotas/Index.jsx)
- [resources/js/Pages/Institucional/Programas/Index.jsx](C:/laragon/www/intelecta/resources/js/Pages/Institucional/Programas/Index.jsx)
- [resources/js/Pages/Institucional/Simulacros/Index.jsx](C:/laragon/www/intelecta/resources/js/Pages/Institucional/Simulacros/Index.jsx)
- [resources/js/Pages/Institucional/Tutores/Index.jsx](C:/laragon/www/intelecta/resources/js/Pages/Institucional/Tutores/Index.jsx)
- [resources/js/Pages/Profile/Partials/DeleteUserForm.jsx](C:/laragon/www/intelecta/resources/js/Pages/Profile/Partials/DeleteUserForm.jsx)
- [resources/js/Pages/Profile/Partials/UpdatePasswordForm.jsx](C:/laragon/www/intelecta/resources/js/Pages/Profile/Partials/UpdatePasswordForm.jsx)
- [resources/js/Pages/Profile/Partials/UpdateProfileInformationForm.jsx](C:/laragon/www/intelecta/resources/js/Pages/Profile/Partials/UpdateProfileInformationForm.jsx)
- [resources/js/Pages/Sistema/RolesPermisos/Index.jsx](C:/laragon/www/intelecta/resources/js/Pages/Sistema/RolesPermisos/Index.jsx)
- [resources/js/lib/inputValidation.js](C:/laragon/www/intelecta/resources/js/lib/inputValidation.js)
- [tests/Feature/Validation/AuthInputValidationTest.php](C:/laragon/www/intelecta/tests/Feature/Validation/AuthInputValidationTest.php)
- [tests/Feature/Validation/InputRulesTest.php](C:/laragon/www/intelecta/tests/Feature/Validation/InputRulesTest.php)
- [tests/Feature/Validation/RequestValidationTest.php](C:/laragon/www/intelecta/tests/Feature/Validation/RequestValidationTest.php)
- [tests/input-validation.test.mjs](C:/laragon/www/intelecta/tests/input-validation.test.mjs)

Cambio anterior preservado, fuera de esta tarea: [composer.json](C:/laragon/www/intelecta/composer.json) (retirada de Pail del script dev). No fue editado por esta implementación.

Artefactos de ejecución no versionados: public/build y caches habituales de pruebas/formato. Se inició PostgreSQL de Laragon con autorización; el registro de arranque está en C:/laragon/data/postgresql/validation-startup.log. La migración añadió restricciones y su entrada en migrations, no actualizó datos académicos/personales.

## 10. Estado Git final

Rama main, sin commit ni push. Todos los archivos listados en la sección 9 pertenecen a esta tarea; composer.json es anterior. El .env local está ignorado por Git y solo se cambiaron APP_LOCALE y APP_FALLBACK_LOCALE a es. Se excluyen secretos de este informe.

```text
## main...origin/main
 M .env.example
 M app/Http/Controllers/Auth/ConfirmablePasswordController.php
 M app/Http/Controllers/Auth/NewPasswordController.php
 M app/Http/Controllers/Auth/PasswordController.php
 M app/Http/Controllers/Auth/PasswordResetLinkController.php
 M app/Http/Controllers/Auth/RegisteredUserController.php
 M app/Http/Controllers/ProfileController.php
 M app/Http/Requests/Admin/StoreUsuarioRequest.php
 M app/Http/Requests/Admin/UpdateRolPermisosRequest.php
 M app/Http/Requests/Admin/UpdateUsuarioRequest.php
 M app/Http/Requests/Auth/LoginRequest.php
 M app/Http/Requests/Evaluaciones/StoreAreaConocimientoRequest.php
 M app/Http/Requests/Evaluaciones/StorePlantillaEvaluacionRequest.php
 M app/Http/Requests/Evaluaciones/StorePreguntaRequest.php
 M app/Http/Requests/Evaluaciones/StoreTemaRequest.php
 M app/Http/Requests/Evaluaciones/UpdateAreaConocimientoRequest.php
 M app/Http/Requests/Evaluaciones/UpdateTemaRequest.php
 M app/Http/Requests/Institucional/AsignacionTutorRequest.php
 M app/Http/Requests/Institucional/AsistenciaAcademicaRequest.php
 M app/Http/Requests/Institucional/AsistenciaGrupoRequest.php
 M app/Http/Requests/Institucional/CuotaAcademicaRequest.php
 M app/Http/Requests/Institucional/GrupoAcademicoRequest.php
 M app/Http/Requests/Institucional/HabilitacionAcademicaRequest.php
 M app/Http/Requests/Institucional/InscripcionAcademicaRequest.php
 M app/Http/Requests/Institucional/MatriculaAcademicaRequest.php
 M app/Http/Requests/Institucional/ProgramaAcademicoRequest.php
 M app/Http/Requests/Institucional/SimulacroProgramadoRequest.php
 M app/Http/Requests/Institucional/TutorAcademicoRequest.php
 M app/Http/Requests/Postulantes/StorePostulanteRequest.php
 M app/Http/Requests/Postulantes/UpdatePostulanteRequest.php
 M app/Http/Requests/ProfileUpdateRequest.php
 M app/Http/Requests/Resultados/EnviarRespuestasEvaluacionRequest.php
 M app/Http/Requests/Resultados/IniciarEvaluacionAplicadaRequest.php
 M composer.json
 M config/app.php
 M resources/js/Components/Evaluaciones/AlternativasEditor.jsx
 M resources/js/Components/Evaluaciones/PlantillaEvaluacionForm.jsx
 M resources/js/Components/Evaluaciones/PreguntaForm.jsx
 M resources/js/Components/Postulantes/PostulanteForm.jsx
 M resources/js/Components/Sistema/UsuarioForm.jsx
 M resources/js/Pages/Auth/ConfirmPassword.jsx
 M resources/js/Pages/Auth/ForgotPassword.jsx
 M resources/js/Pages/Auth/Login.jsx
 M resources/js/Pages/Auth/Register.jsx
 M resources/js/Pages/Auth/ResetPassword.jsx
 M resources/js/Pages/Estudiante/Evaluaciones.jsx
 M resources/js/Pages/Evaluaciones/Areas/Create.jsx
 M resources/js/Pages/Evaluaciones/Areas/Index.jsx
 M resources/js/Pages/Evaluaciones/Temas/Create.jsx
 M resources/js/Pages/Evaluaciones/Temas/Index.jsx
 M resources/js/Pages/Institucional/AsignacionTutores/Index.jsx
 M resources/js/Pages/Institucional/Asistencia/Index.jsx
 M resources/js/Pages/Institucional/Grupos/Index.jsx
 M resources/js/Pages/Institucional/HabilitacionAcademica/Index.jsx
 M resources/js/Pages/Institucional/Inscripciones/Index.jsx
 M resources/js/Pages/Institucional/MatriculasCuotas/Index.jsx
 M resources/js/Pages/Institucional/Programas/Index.jsx
 M resources/js/Pages/Institucional/Simulacros/Index.jsx
 M resources/js/Pages/Institucional/Tutores/Index.jsx
 M resources/js/Pages/Profile/Partials/DeleteUserForm.jsx
 M resources/js/Pages/Profile/Partials/UpdatePasswordForm.jsx
 M resources/js/Pages/Profile/Partials/UpdateProfileInformationForm.jsx
 M resources/js/Pages/Sistema/RolesPermisos/Index.jsx
?? app/Http/Requests/Auth/PasswordEmailRequest.php
?? app/Http/Requests/Auth/RegisterRequest.php
?? app/Http/Requests/Auth/ResetPasswordRequest.php
?? app/Http/Requests/Auth/UpdatePasswordRequest.php
?? app/Http/Requests/NormalizedFormRequest.php
?? app/Rules/InputFormat.php
?? app/Support/Validation/InputNormalizer.php
?? app/Support/Validation/InputRules.php
?? database/migrations/2026_09_07_000000_add_basic_input_checks.php
?? docs/audit-input-data.php
?? docs/test-postgres-input-checks.php
?? lang/es/auth.php
?? lang/es/passwords.php
?? lang/es/validation.php
?? resources/input-options.json
?? resources/js/lib/inputValidation.js
?? tests/Feature/Validation/AuthInputValidationTest.php
?? tests/Feature/Validation/InputRulesTest.php
?? tests/Feature/Validation/RequestValidationTest.php
?? tests/input-validation.test.mjs
?? docs/auditoria-validaciones-basicas-2026-09-07.md
?? docs/validaciones-datos-2026-09-07.json
```

## Anexo — Inventario de claves y ayudas HTML

139 claves de datos, incluidas claves auxiliares de selección y subcampos de arrays. Para required condicional y credenciales, prevalecen los overrides explícitos del formulario; los selects ofrecen sus opciones y los booleanos usan switches/checkboxes. No todos los campos son columnas independientes: por ejemplo id_mat depende del módulo, y la universidad del postulante se relaciona a través de la carrera. La tabla anterior documenta la validación Laravel y su última barrera SQL.

| Clave | Ayudas compartidas React |
| --- | --- |
| apellidos_post | required=true; minLength=2; maxLength=120; format=person |
| apellidos_tutor | required=true; minLength=2; maxLength=120; format=person |
| aula_grupo | maxLength=80 |
| capacidad_grupo | required=true; inputMode=numeric; step=1; min=1; max=500 |
| carrera_area_prog | maxLength=180 |
| celular_post | maxLength=32; format=phone; type=tel; inputMode=tel |
| celular_tutor | maxLength=32; format=phone; type=tel; inputMode=tel |
| ci_post | minLength=4; maxLength=30; format=document |
| ci_tutor | minLength=4; maxLength=30; format=document |
| codigo_grupo | minLength=2; maxLength=60; format=code |
| codigo_mat | minLength=2; maxLength=60; format=code |
| codigo_prog | minLength=2; maxLength=60; format=code |
| concepto_cuota | maxLength=180 |
| correo_tutor | maxLength=180; format=email; type=email |
| current_password | Según tipo/opciones/contexto del formulario |
| descripcion_area | maxLength=2000 |
| descripcion_plan | maxLength=3000 |
| descripcion_prog | maxLength=3000 |
| descripcion_tem | maxLength=2000 |
| dificultad_plan | Según tipo/opciones/contexto del formulario |
| dificultad_preg | Según tipo/opciones/contexto del formulario |
| duracion_minutos_plan | inputMode=numeric; step=1; min=1; max=480 |
| edad_post | inputMode=numeric; step=1; min=14; max=80 |
| email | required=true; maxLength=254; format=email; type=email |
| email_post | maxLength=254; format=email; type=email |
| enunciado_preg | required=true; maxLength=5000 |
| es_correcta_alt | Según tipo/opciones/contexto del formulario |
| especialidad_tutor | maxLength=160 |
| estado_alt | Según tipo/opciones/contexto del formulario |
| estado_area | required=true |
| estado_asig | required=true |
| estado_asist | required=true |
| estado_cuota | required=true |
| estado_grupo | required=true |
| estado_hab | required=true |
| estado_inscripcion | required=true |
| estado_matricula_mat | required=true |
| estado_plan | required=true |
| estado_post | required=true |
| estado_preg | required=true |
| estado_prog | required=true |
| estado_sim | required=true |
| estado_tem | required=true |
| estado_tutor | required=true |
| exigencia_preg | Según tipo/opciones/contexto del formulario |
| experiencia_tutor | maxLength=3000 |
| explicacion_preg | maxLength=5000 |
| fecha_asist | required=true |
| fecha_fin_asig | Según tipo/opciones/contexto del formulario |
| fecha_fin_hab | Según tipo/opciones/contexto del formulario |
| fecha_fin_prog | Según tipo/opciones/contexto del formulario |
| fecha_inicio_asig | Según tipo/opciones/contexto del formulario |
| fecha_inicio_hab | Según tipo/opciones/contexto del formulario |
| fecha_inicio_prog | Según tipo/opciones/contexto del formulario |
| fecha_inscripcion | Según tipo/opciones/contexto del formulario |
| fecha_matricula_mat | Según tipo/opciones/contexto del formulario |
| fecha_pago_cuota | Según tipo/opciones/contexto del formulario |
| fecha_sim | Según tipo/opciones/contexto del formulario |
| fecha_vencimiento_cuota | Según tipo/opciones/contexto del formulario |
| formacion_tutor | maxLength=220 |
| gestion_post | required=true; inputMode=numeric; step=1; min=2000 |
| habilidad_preg | Según tipo/opciones/contexto del formulario |
| habilitado_evaluaciones_hab | Según tipo/opciones/contexto del formulario |
| habilitado_reportes_hab | Según tipo/opciones/contexto del formulario |
| habilitado_simulacros_hab | Según tipo/opciones/contexto del formulario |
| hora_fin_sim | Según tipo/opciones/contexto del formulario |
| hora_inicio_sim | Según tipo/opciones/contexto del formulario |
| id_alt | inputMode=numeric; step=1 |
| id_area | inputMode=numeric; step=1 |
| id_car | inputMode=numeric; step=1 |
| id_col | inputMode=numeric; step=1 |
| id_grupo | inputMode=numeric; step=1 |
| id_insc | inputMode=numeric; step=1 |
| id_mat | inputMode=numeric; step=1 |
| id_plantilla | inputMode=numeric; step=1 |
| id_post | inputMode=numeric; step=1 |
| id_preg | inputMode=numeric; step=1 |
| id_prog | inputMode=numeric; step=1 |
| id_sim | inputMode=numeric; step=1 |
| id_tem | inputMode=numeric; step=1 |
| id_tutor | inputMode=numeric; step=1 |
| id_uni | inputMode=numeric; step=1 |
| intentos | inputMode=numeric; step=1; min=1; max=32767 |
| letra_alt | Según tipo/opciones/contexto del formulario |
| materia_referencia_asig | maxLength=160 |
| metodo_pago_cuota | maxLength=120 |
| modalidad_prog | maxLength=100 |
| modalidad_sim | maxLength=100 |
| monto_cuota | required=true; inputMode=decimal; step=0.01; min=0; max=99999999.99 |
| monto_matricula_mat | required=true; inputMode=decimal; step=0.01; min=0; max=99999999.99 |
| motivo_hab | maxLength=220 |
| name | required=true; minLength=2; maxLength=255; format=person |
| nivel_grupo | maxLength=100 |
| nivel_tem | Según tipo/opciones/contexto del formulario |
| nombre_area | required=true; maxLength=120; minLength=2 |
| nombre_grupo | required=true; minLength=2; maxLength=160 |
| nombre_plan | required=true; minLength=2; maxLength=180 |
| nombre_prog | required=true; minLength=2; maxLength=180 |
| nombre_tem | required=true; maxLength=120; minLength=2 |
| nombres_post | required=true; minLength=2; maxLength=120; format=person |
| nombres_tutor | required=true; minLength=2; maxLength=120; format=person |
| nro_cuota | inputMode=numeric; step=1; min=1; max=120 |
| objetivo_plan | maxLength=3000 |
| observacion_asig | maxLength=2000 |
| observacion_asist | maxLength=2000 |
| observacion_cuota | maxLength=2000 |
| observacion_hab | maxLength=2000 |
| observacion_inscripcion | maxLength=2000 |
| observacion_mat | maxLength=2000 |
| observacion_sim | maxLength=2000 |
| observacion_tutor | maxLength=2000 |
| observaciones_post | maxLength=2000 |
| orden_alt | inputMode=numeric; step=1; min=1; max=5 |
| orden_pp | required=true; inputMode=numeric; step=1; min=1; max=1000 |
| password | Según tipo/opciones/contexto del formulario |
| password_confirmation | Según tipo/opciones/contexto del formulario |
| puntaje_pp | required=true; inputMode=decimal; step=0.01; min=0.01; max=100 |
| puntaje_preg | required=true; inputMode=decimal; step=0.01; min=0.01; max=100 |
| relacion_ingenieria_preg | maxLength=2000 |
| remember | Según tipo/opciones/contexto del formulario |
| respuesta_texto | maxLength=5000 |
| rol_asig | maxLength=120 |
| role | required=true |
| sesion_asist | required=true; minLength=2; maxLength=120 |
| subtema_preg | maxLength=255 |
| texto_alt | required=true; maxLength=2000 |
| tiempo_estimado_seg_preg | inputMode=numeric; step=1; min=15; max=7200 |
| tiempo_segundos | inputMode=numeric; step=1; min=0; max=2147483647 |
| tiempo_total_segundos | inputMode=numeric; step=1; min=0; max=2147483647 |
| tipo_beneficio_mat | maxLength=120 |
| tipo_eval_apl | maxLength=120 |
| tipo_preg | required=true |
| titulo_sim | required=true; minLength=2; maxLength=180 |
| token | required=true; maxLength=255 |
| turno_grupo | maxLength=80 |
| turno_post | Según tipo/opciones/contexto del formulario |
| tutor_responsable_grupo | minLength=2; maxLength=180; format=person |
| universidad_objetivo_prog | maxLength=180 |
| user_id | inputMode=numeric; step=1 |
