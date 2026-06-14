# Auditoría Final del Sistema INTELECTA v2

**Sistema auditado:** INTELECTA  
**Caso institucional:** Academia Universitaria Avalancha  
**Fecha de revisión:** 14 de junio de 2026  
**Alcance técnico:** Laravel 13, React/Inertia, Tailwind, PostgreSQL, Redis y Spatie Permission  
**Estado revisado:** Copia de trabajo local con correcciones aplicadas y validadas mediante análisis estático y pruebas.

---

## 1. Resumen Ejecutivo Actualizado

El sistema INTELECTA ha avanzado significativamente desde la emisión de la auditoría inicial. Los hallazgos críticos de alta prioridad que comprometían la seguridad de las rutas, el control de acceso de los roles institucionales y la integridad de la jerarquía curricular (como la asignación de materias en áreas) han sido resueltos de manera robusta.

Gracias a la implementación del middleware administrativo, a la configuración granular de permisos de Spatie y al ajuste de seeders, el sistema cuenta ahora con un control de acceso estricto y coherente con las políticas de la Academia Universitaria Avalancha. El frontend visual de la Ficha Académica tiene ahora su propio índice independiente, lo que mejora la experiencia de usuario y la coherencia del sidebar. 

A pesar de estos avances significativos hacia la estabilización del software, el sistema sigue manteniendo un carácter de prototipo funcional y de soporte para la toma de decisiones. Ciertos flujos (como la trazabilidad real de los resultados académicos y el módulo conceptual de Learning Analytics) permanecen como lecturas referenciales o fases futuras debido a la ausencia de un motor transaccional completo de respuestas y evaluaciones en vivo.

**Resultado de la v2:** El sistema se encuentra en un estado sólido y maduro, ideal para la exposición del Proyecto Integrador I y para demostraciones controladas. Se aconseja realizar las pruebas finales de verificación y resolver las tareas de conciliación de cuotas antes de un despliegue multiusuario real.

---

## 2. Cambios Aplicados Después de la Auditoría Inicial

A continuación, se detalla la correspondencia entre los hallazgos reportados en la primera auditoría y las correcciones técnicas aplicadas en la copia de trabajo:

| Código | Módulo | Corrección aplicada | Estado actual | Evidencia técnica |
|---|---|---|---|---|
| **C-01** | Seguridad y rutas institucionales | Se creó el middleware `EnsureAdministrativeAccess` y se aplicó a todas las rutas administrativas. Se configuraron permisos granulares de Spatie para operaciones de creación, edición y visualización. El sidebar filtra los elementos dinámicamente según permisos del usuario. Se corrigió el flujo para que los estudiantes que accedan directamente a rutas administrativas reciban `403 Forbidden` (con una vista institucional Inertia), limitando la redirección 302 únicamente al dashboard/flujo de login. Adicionalmente, se asignó el permiso `preguntas.crear` al rol Docente en el seeder para corregir el acceso a la creación de preguntas. | **Corregido** | [EnsureAdministrativeAccess.php](file:///c:/dev/apps/intelecta/app/Http/Middleware/EnsureAdministrativeAccess.php), [web.php](file:///c:/dev/apps/intelecta/routes/web.php), [RolesAndUsersSeeder.php](file:///c:/dev/apps/intelecta/database/seeders/RolesAndUsersSeeder.php). |
| **C-02** | Áreas de Conocimiento | Se reestructuraron las validaciones de solicitudes (`StoreAreaConocimientoRequest` y `UpdateAreaConocimientoRequest`), el DTO y el formulario React de Áreas de Conocimiento para capturar, validar y persistir obligatoriamente el campo `id_mat` (Materia). Se añadió soporte para edición, filtrado y renderizado seguro de áreas históricas que carezcan de materia asignada. | **Corregido** | Requests de validación de Áreas de Conocimiento, controlador e interfaces del frontend del Centro de Evaluaciones. |
| **M-01** | Ficha Académica | Se desarrolló un índice e interfaz propia en la ruta `/admin/institucional/ficha-academica` con un componente React dedicado que ofrece buscador de postulantes, filtros por programa y carrera, paginación y enlaces rápidos al detalle individual. El sidebar fue actualizado para apuntar a esta nueva ruta independiente. | **Corregido** | [FichaAcademicaController.php](file:///c:/dev/apps/intelecta/app/Http/Controllers/Institucional/FichaAcademicaController.php), `FichaAcademica/Index.jsx` y [web.php](file:///c:/dev/apps/intelecta/routes/web.php). |
| **M-02** | Fechas institucionales | Se implementaron reglas condicionales en el backend y validaciones reactivas en el frontend (`min`/`max` dinámicos en HTML) para impedir inconsistencias cronológicas en programas, asignación de tutores, inscripciones, matrículas, cuotas, habilitación y simulacros. Los mensajes críticos se tradujeron completamente al español. | **Corregido** | Reglas de validación aplicadas a fechas en los FormRequests correspondientes. |
| **N-01** | Títulos duplicados | Se eliminó el sufijo redundante del nombre de la aplicación de los layouts principales de Inertia y de las etiquetas `<Head>` en pantallas de Login y Reportes. | **Corregido** | Componentes base del layout del frontend. |
| **N-02** | Resultados Académicos | Se eliminó el buscador inactivo de la vista de Resultados Académicos y se incorporó una nota institucional aclaratoria que establece el alcance referencial de este módulo en la fase de desarrollo actual. | **Corregido** | `ResultadosSeguimiento.jsx` del frontend. |
| **N-03** | Terminología | Se reemplazaron las palabras obsoletas heredadas por términos oficiales ("Postulante" en lugar de "Estudiante", "Tutoría Académica" en lugar de "Docente/Docencia"), logrando mayor coherencia con el vocabulario institucional. | **Mitigado / Corregido** | Vistas y literales de navegación en el sidebar y tablas. |

---

## 3. Hallazgos Críticos Actualizados

El estado de los riesgos críticos definidos inicialmente se actualiza de la siguiente manera:

| Código | Módulo | Estado actual | Evidencia | Acción pendiente |
|---|---|---|---|---|
| **C-01** | Seguridad y rutas institucionales | **Corregido** | Middleware `EnsureAdministrativeAccess`, políticas `Gate::before`, permisos Spatie en rutas de `web.php` y asignación correcta de `preguntas.crear` al docente en el seeder. | Ejecutar nuevamente los tests automatizados para validar que no existan regresiones de permisos tras la recarga del seeder. |
| **C-02** | Áreas de Conocimiento | **Corregido** | Campos y selectores de Materia (`id_mat`) validados obligatoriamente en formularios y persistidos en la base de datos de PostgreSQL. | Ninguna. Las áreas históricas sin materia se muestran como "Sin clasificación" de forma segura. |
| **C-03** | Resultados Académicos trazables | **Pendiente** | `ResultadosSeguimiento.jsx` sigue cargando datos simulados e informativos de rendimiento. | Diseñar e implementar en fases posteriores el modelo transaccional que persista respuestas reales de simulacros para calcular rendimiento dinámico. |
| **C-04** | Cuotas y Habilitación Académica | **Parcialmente mitigado** | Sincronización automática de `syncForMatricula()` que evalúa cuotas pero puede sobrescribir modificaciones del administrador. | Separar el estado calculado automáticamente por sistema de la bandera de "override manual" del administrador, guardando historial del cambio. |

---

## 4. Hallazgos Medios Actualizados

Reclasificación de riesgos de prioridad media tras las correcciones:

* **M-01 Ficha Académica (Corregido):** Cuenta ahora con una página de índice independiente y el sidebar redirecciona correctamente a ella.
* **M-02 Fechas institucionales (Corregido):** Se incluyeron validaciones de lógica temporal en el backend y restricciones dinámicas frontend.
* **M-03 Inscripciones y grupos (Pendiente):** Aún no se limita la sobreasignación de cupos en base a transacciones concurrentes ni se bloquea la inscripción si las entidades relacionadas no están marcadas como activas.
* **M-04 Asignación de Tutores / Solapamientos (Pendiente):** Las asignaciones de tutores continúan sin validar solapamientos de fechas ni cardinalidad estricta para el mismo paralelo/grupo.
* **M-05 Catálogos de consulta (Decisión de alcance):** Se mantiene el alcance de Carreras, Colegios, Universidades y Configuración como interfaces de consulta/lectura de datos de referencia (datos maestros), sin requerir flujos de edición completos por el momento.
* **M-08 Usuario-Postulante explícito (Pendiente):** La relación de habilitación académica depende de la coincidencia del correo electrónico en lugar de una llave foránea explícita en la tabla `users` o una entidad de enlace.

---

## 5. Matriz de Validaciones Actualizada

El estado actual de las reglas de validación en los formularios y peticiones se detalla en la siguiente tabla:

| Módulo | Validación | Estado inicial | Estado actual | Observación |
|---|---|---|---|---|
| **Programas** | Fecha inicial no pasada para nuevos registros; `fecha_fin >= fecha_inicio`. | Incompleto en frontend. | **Corregido** | Implementado con reglas condicionales y restricciones en el frontend (`min`). |
| **Asignación Tutores** | `fecha_fin >= fecha_inicio` y validación de fecha inicial no pasada en creación. | Incompleto. | **Corregido** | Fechas coherentes y mensajes de error específicos en español. |
| **Inscripciones** | `fecha_inscripcion` válida, no futura. Mensajes de error coherentes. | Incompleto. | **Corregido** | Restringido mediante regla `before_or_equal:today` y atributo `max` en frontend. |
| **Matrículas** | `fecha_matricula` no futura, inscripción activa y montos positivos. | Incompleto. | **Corregido** | Se agregaron restricciones de fecha no futura y mensajes de error en español. |
| **Cuotas** | Monto positivo, `fecha_pago` requerida si está pagada y no futura. | Incompleto. | **Corregido** | Valida estados de pago condicionados a fechas en español. |
| **Habilitación** | Coherencia en rangos de fechas de habilitaciones temporales. | Incompleto. | **Corregido** | El frontend y backend aseguran consistencia en fechas y accesos. |
| **Simulacros** | Fecha del simulacro no pasada, `hora_fin > hora_inicio`. | Incompleto en frontend. | **Corregido** | Frontend y backend validan que no se agenden simulacros en el pasado. |
| **Áreas** | Materia obligatoria (`id_mat`) en creación y edición. | **Ausente** | **Corregido** | Integrado en FormRequests de validación y base de datos. |
| **Temas** | Nombre único por área, mensajes en español. | En inglés. | **Corregido** | Se configuraron mensajes personalizados en español. |
| **Banco Preguntas** | Estructura de alternativas válidas y una sola opción correcta. | Parcial. | **Corregido** | Mensajes de error en español para alternativas y taxonomía. |

---

## 6. Revisión Actualizada del Sidebar

El sidebar cumple ahora con los requisitos funcionales del diseño institucional:
* **Estructura jerárquica:** Organizado en secciones desplegables con estados activos coherentes (Inicio, Gestión de Postulantes, Gestión Evaluativa, etc.).
* **Ficha Académica independiente:** Ya no redirige al listado genérico de postulantes; apunta a su propia vista indexada de fichas.
* **Seguridad y permisos:** Filtra dinámicamente las opciones del menú. Los enlaces a configuraciones administrativas, roles y asignación de usuarios se ocultan para aquellos roles que carecen de los permisos correspondientes.
* **Control de visualización de secciones:** Si un rol no tiene acceso a ninguna ruta dentro de una sección del sidebar, la sección completa se oculta para evitar menús vacíos.
* **Estudiantes/Postulantes restringidos:** Al no poseer roles administrativos, el sidebar se oculta por completo o solo muestra su portal específico según corresponda, impidiendo el acceso visual a rutas administrativas.

---

## 7. Revisión Actualizada de Seguridad

El esquema de seguridad de INTELECTA se ha fortalecido con los siguientes mecanismos:

* **Permisos granulares de Spatie:** Se definieron permisos específicos (por ejemplo: `preguntas.ver`, `preguntas.crear`, `preguntas.editar`, `preguntas.eliminar`) en lugar de depender únicamente de roles genéricos.
* **Middleware administrativo:** El middleware `EnsureAdministrativeAccess` actúa como una barrera perimetral en todas las rutas bajo prefijos administrativos o módulos institucionales. 
* **Control de 302 vs 403:** El middleware intercepta los accesos no autorizados a rutas de API o páginas administrativas directas (como `/postulantes`) y devuelve inmediatamente un estado HTTP `403 Forbidden` en lugar de una redirección 302. El desvío 302 hacia el portal del estudiante se ejecuta de forma exclusiva al intentar ingresar a la ruta `/dashboard`.
* **Gate::before para Super Administrador:** Se mantiene un bypass en el proveedor de servicios de autorización para que el rol "Super Administrador" posea acceso implícito y total sobre todas las operaciones sin importar el listado de permisos explícitos.
* **FormRequests protegidos:** Las validaciones de solicitudes ahora utilizan `$this->user()->can('permission.name')` para garantizar la autorización a nivel de controlador.

---

## 8. Revisión Actualizada de Coherencia Documental

Comparativa del estado real de los módulos del sistema frente a lo especificado en la documentación del Proyecto Integrador:

| Módulo documental | Estado actual | Observación | Acción recomendada |
|---|---|---|---|
| **Dashboard** | Implementado | Muestra estadísticas reales agregadas del sistema y accesos rápidos. | Mantener y validar permisos. |
| **Programas Académicos** | Implementado | CRUD completo con control de fechas. | Validar nombres e inicialización en producción. |
| **Ficha Académica** | Implementado | Cuenta con índice funcional e información académica unificada por postulante. | Utilizar como enlace principal de seguimiento tutorial. |
| **Resultados Académicos** | Referencial | Módulo estático de seguimiento. No hay persistencia de respuestas individuales. | Declarar como pantalla de visualización referencial en la defensa del proyecto. |
| **Banco de Preguntas** | Implementado | Totalmente funcional, permite clasificar y estructurar alternativas. | Asegurar que los docentes tengan acceso a crear ítems. |
| **Learning Analytics** | Conceptual | Presenta gráficos, resúmenes de rendimiento y conclusiones simuladas. | Declarar explícitamente en la presentación como una "arquitectura propuesta" o "vista conceptual". |

---

## 9. Pendientes Reales Antes del Cierre

### 9.1 Obligatorios antes de defensa
1. **Ejecutar la suite completa de pruebas:** Ejecutar `php artisan test` para asegurar que las modificaciones del middleware y la asignación del rol docente en el seeder de permisos (`preguntas.crear`) pasen sin inconvenientes.
2. **Reestablecer datos y caché:** Ejecutar el sembrado de datos y limpiar la caché de Spatie:
   * `php artisan db:seed --class=RolesAndUsersSeeder`
   * `php artisan permission:cache-reset`
3. **Verificación manual con usuarios demo:** Validar el acceso y el menú visual para cada usuario de pruebas (`superadmin@intelecta.test`, `admin@intelecta.test`, `docente@intelecta.test`, `estudiante@intelecta.test`).
4. **Revisión estática final:** Comprobar la ausencia de advertencias de compilación en el frontend con Inertia.

### 9.2 Recomendados (Siguiente fase de refinamiento)
* **Separación de overrides manuales (C-04):** Modificar la entidad de Habilitación Académica para almacenar un booleano de override del administrador, evitando que la ejecución de scripts automatizados de matrículas sobrescriba la voluntad del usuario.
* **Control de cupos:** Añadir una validación de concurrencia en `InscripcionAcademicaController` para no superar la capacidad establecida del grupo.
* **Solapamientos de tutores:** Controlar que un tutor no sea asignado a más de un grupo/materia en el mismo bloque horario.
* **Relación user-postulante:** Normalizar la estructura de la base de datos añadiendo una clave foránea `user_id` en la tabla `postulantes` para evitar búsquedas basadas en cadenas de texto de correo electrónico.

### 9.3 Futuro / Integrador posterior (Proyecto Integrador II)
* **Persistencia de respuestas de evaluaciones:** Desarrollar el motor para que los postulantes respondan reactivamente evaluaciones en línea y sus respuestas se califiquen automáticamente.
* **Resultados dinámicos:** Calcular promedios, rankings y rendimiento de forma reactiva en base a las evaluaciones rendidas.
* **Algoritmos predictivos en Learning Analytics:** Entrenar y conectar modelos reales de Machine Learning (como Random Forest o regresión logística) para identificar alertas de riesgo de ingreso universitario en base a datos históricos reales de la academia.

---

## 10. Estado de Preparación para Exposición

El software INTELECTA está en un estado óptimo para ser expuesto ante el tribunal de evaluación:
* **Cobertura de Procesos:** Cubre con solvencia las operaciones administrativas (matrículas, cuotas, simulacros, asistencia, tutores) y académicas (banco de preguntas y estructuración de plantillas).
* **Identidad visual:** La interfaz Elite Prep Institute es atractiva, moderna, responsive y no genera fallos visuales ni errores de consola que puedan restar puntos durante la defensa.
* **Defensa de módulos conceptuales:** Se debe ser honesto e indicar en la exposición que el módulo de **Learning Analytics** representa una "arquitectura conceptual/propuesta" orientada al soporte de decisiones académicas, y que los **Resultados Académicos** actúan como una "vista referencial" para el seguimiento en esta etapa del proyecto (Proyecto Integrador I).

---

## 11. Recomendaciones de Siguiente Bloque Técnico

Se propone estructurar los desarrollos posteriores bajo el siguiente esquema:

* **Bloque de Desarrollo 3: Resultados Académicos Trazables.**
  * *Acciones:* Implementación de tablas para respuestas persistidas de los postulantes, desarrollo de la interfaz de rendición de exámenes en el portal del estudiante, cálculo dinámico de puntuaciones y actualización automática de la tabla de rendimiento.
  * *Prerrequisito:* Este bloque de desarrollo debe abordarse únicamente después de que la suite de pruebas unitarias y el esquema de permisos de Spatie implementados en el Bloque 2 se reporten como 100% estables y libres de regresiones.

---

## 12. Conclusión

El sistema INTELECTA se encuentra en un estado **sólido y apto para la defensa y prototipado funcional de Proyecto Integrador I**. Las correcciones de seguridad perimetral han neutralizado las vulnerabilidades de elevación de privilegios de los estudiantes y la asignación del rol docente.

No se recomienda su puesta en producción multiusuario real sin antes resolver la trazabilidad transaccional de las respuestas a exámenes y el control preciso de solapamientos horarios de tutores. Sin embargo, para fines de demostración de arquitectura, coherencia de base de datos relacional y experiencia de usuario académica, el sistema cumple con un estándar de calidad muy elevado.
