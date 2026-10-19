# Informe final: recuperación administrativa DEMO de INTELECTA

## Resultado

**Regularización administrativa completada en la base principal `intelecta`: 72 cuentas Estudiante y 72 vínculos únicos, sin expedientes duplicados, referencias huérfanas ni cambios financieros.**

Las 69 cuentas nuevas tienen accesos individuales de establecimiento de contraseña válidos al comprobarse, cifrados y no publicados. La entrega/elección de contraseña corresponde a cada destinatario. No se afirma que las 69 personas hayan elegido ya una contraseña.

| Control | Resultado verificado |
|---|---:|
| Usuarios totales, incluidos 6 institucionales | 78 |
| Cuentas exclusivamente Estudiante | 72 |
| Expedientes / vínculos únicos | 72 / 72 |
| Cuentas Estudiante sin expediente | 0 |
| Expedientes sin usuario | 0 |
| Conflictos reales de identidad detectados | 0 |
| Login, ficha, ranking y evaluaciones comprobados en principal | 72 / 72 |
| Inicio de evaluación permitido / correctamente denegado | 62 / 10 |
| Credenciales previas operativas verificadas | 3 |
| Nuevas cuentas pendientes de elegir contraseña | 69 |
| Errores de integridad referencial en los controles ejecutados | 0 |

## Autorización y ejecución

El responsable confirmó el carácter DEMO y autorizó expresamente las cuentas de **id_post 4–72**, el actor **Adriana Choque Mamani, SA user_id=1**, y el motivo exacto:

> Regularización DEMO de 69 cuentas autorizada por el responsable de la instalación

Antes de escribir se comprobó que las 41 tablas de principal coincidían con el respaldo verificado posterior a los tres vínculos. Se conservó el manifiesto propuesto y se generó una copia aprobada, manteniendo nombres, correos, documentos, expedientes, huellas e identificador del lote. Las 69 revisiones quedaron `aprobado` y se añadió la referencia de autorización.

SHA256 del manifiesto aprobado y sometido a un **nuevo dry-run obligatorio**:
`2102e996b6d1aca50099303056189ff3cda0e35772b123364449c92a122813a4`.

El dry-run validó 69 filas sin escribir en BD: 60 con evaluación permitida y 9 restringidas, además de las tres cuentas ya vinculadas. Se ejecutó exclusivamente `intelecta:altas-demo-estudiantes --aplicar`, con actor, motivo, certificado de respaldo, destino cifrado y confirmación interactiva del hash/base/SA. El comando estableció el contexto CLI administrativo autorizado; no simuló una sesión web de Adriana.

Se crearon **solo 69 usuarios**, IDs 10–78 en esta ejecución, vinculados mediante la acción auditada a los expedientes 4–72. La correspondencia no se decidió por igualdad numérica ni por correo: cada fila identificaba su expediente aprobado. Los 72 expedientes existentes se conservaron. Las tres cuentas anteriores, incluidos sus datos y contraseñas, no se modificaron.

Hay **69 entradas `alta_demo_estudiante`** con actor/nombre/motivo correctos y bitácoras durables de creación, vínculo y asignación de rol. No se registraron operaciones a otro responsable. Las cuentas nuevas recibieron exclusivamente Estudiante, sin permisos directos ni privilegios financieros.

## Preservación e integridad PostgreSQL

Respecto al respaldo anterior a las 69 altas, cambiaron únicamente las tablas previstas:
`users`, `postulantes`, `model_has_roles`, `password_reset_tokens` y `bitacora_sistema`.

Las otras **36 tablas** permanecen idénticas. Se compararon todas las filas y columnas con orden binario estable. Los nueve usuarios previos permanecen idénticos al respaldo, incluidas sus credenciales y estados. Las huellas de expediente aprobadas no permitían cambiar datos personales/estado al vincular.

Se conservaron íntegramente:

- 72 inscripciones, 72 matrículas, 72 habilitaciones y 180 cuotas, incluidos importes, fechas, estados y pagos.
- Catálogos de roles/permisos y permisos financieros; únicamente se añadieron las 69 asignaciones de Estudiante necesarias.
- Estado de los 69 postulantes activos y 3 inactivos, incluidas todas sus restricciones.
- Historial de migraciones: ninguna migración pendiente se aplicó en principal.

Controles con resultado **cero**: usuario inexistente en el vínculo, estudiante sin expediente, vínculo repetido, matrícula sin inscripción/coherencia de titular-programa-grupo, habilitación incoherente con matrícula/inscripción, cuota sin matrícula y FK no validadas en el esquema público. Se mantiene UNIQUE(user_id) y la FK de identidad con ON DELETE RESTRICT.

La reejecución del mismo comando y manifiesto devolvió **69 `ya_creada_sin_cambios`**. No duplicó usuarios, no cambió contraseñas, no regeneró tokens ni añadió bitácoras de alta. Las 41 tablas siguieron coincidiendo con el respaldo final.

El auditor mostraba inicialmente falsos conflictos por homónimos DEMO. Se corrigió solo su clasificación: compartir nombre hace ambigua una búsqueda sin vínculo, pero no invalida una FK única ya confirmada. Una regresión cubre este caso y se conservan los rechazos por candidatos ambiguos sin vínculo. El inventario final clasifica **72 A**, sin B/C/D/E. No se alteraron nombres ni identidades para ocultar el aviso.

## Verificación real de las 72 cuentas

`RegularizacionDemoRealTest` es opt-in, sin RefreshDatabase, migraciones, seeders ni altas. Trabaja sobre **la principal ya regularizada** con una transacción exterior y logging de prueba desactivado. Recibe la credencial previa solo por entorno privado del proceso; no incorpora contraseñas operativas al código.

Se recorrieron las 72 cuentas, no una muestra:

1. Comprobación de rol exclusivo, vínculo, matrícula e inscripción propios.
2. En las 69 nuevas, comprobación y consumo temporal del token nativo para elegir una contraseña de ensayo; prueba de que el token deja de ser utilizable. Las tres anteriores utilizaron su contraseña existente.
3. Login mediante `/login`, acceso HTTP real a ficha, ranking y evaluaciones, atravesando los middleware normales.
4. Comparación de la decisión del portal con estado y habilitación administrativa.
5. Inicio de evaluación: 62 respuestas 302 permitidas y 10 respuestas 422 legítimas. Mariana conserva específicamente la denegación por habilitación y cuota vencida.
6. Rollback explícito de todos los efectos de ensayo, seguido de comparación íntegra de las **41 tablas** con el estado previo.

No quedaron contraseñas de ensayo, consumos de tokens, cambios de sesiones, nuevos resultados ni bitácoras de ensayo persistidos. Las secuencias PostgreSQL pueden consumir IDs dentro de un rollback; eso no crea filas ni afecta pagos. La evidencia corresponde al kernel HTTP de Laravel contra principal, no a una comprobación visual en navegador.

## Cuentas habilitadas, restringidas y credenciales

Listado individual completo, **sin secretos**, excluido de Git:
`storage/app/private/regularizacion-demo-2026-10-19/estado-final-72-cuentas.md`, con copia JSON del mismo nombre.

Incluye user_id, id_post, nombre, correo, estado académico, matrícula, habilitación, decisión de evaluación y estado de credencial:

- Expedientes 1, 2 y 3: contraseña previa operativa verificada por login. Son fixtures preexistentes; no se afirma que la haya elegido el titular.
- Expedientes 4–72: pendientes de establecer contraseña. La prueba de establecimiento se revirtió expresamente, por lo que sus 69 accesos continúan reservados para la entrega real.
- Los accesos caducan a los 60 minutos desde emisión; la cuenta no desaparece al caducar el enlace. La entrega puede reemitirse individualmente justo antes de usarla.

Restricciones conservadas:

| id_post | user_id | Condición que impide evaluar |
|---|---|---|
| 3 | 9 | Cuota vencida; Mariana |
| 9 | 15 | Cuota vencida |
| 19 | 25 | Cuota vencida |
| 20 | 26 | Expediente inactivo |
| 29 | 35 | Cuota vencida |
| 39 | 45 | Expediente inactivo y cuota vencida |
| 49 | 55 | Cuota vencida |
| 58 | 64 | Expediente inactivo |
| 59 | 65 | Cuota vencida |
| 69 | 75 | Cuota vencida |

Las otras 62 cuentas pueden evaluar. Las 10 restringidas también pueden ingresar a su propio portal: una denegación de evaluación no significa fallo de identidad.

Procedimiento operativo: **`docs/guia-credenciales-demo-defensa.md`**. Se utiliza el comando auditado de entrega/renovación individual, con SA, motivo, confirmación y HTML privado de un solo destinatario. No se publicaron contraseñas ni enlaces de acceso en informes, logs o Git. El paquete original de los 69 accesos permanece cifrado en almacenamiento privado; los tokens de BD están hasheados. No se utilizó el mailer `log` para entregarlos.

## Respaldos conservados

En `storage/app/private/regularizacion-demo-2026-10-19/`, fuera de Git:

| Respaldo | SHA256 |
|---|---|
| `intelecta-tras-tres-vinculos.dump` — estado previo a las altas | `07b403bb81ebae344673e79d7acceab5ad4fa243eaa80b36971286091781ea1e` |
| `intelecta-final-72-cuentas.dump` — estado regularizado | `9b930963b482bfd9557814ab34a6914e870a0d3d901479718685d1bddd1b026d` |

Ambos tienen certificado `.verified.json`: cada uno se restauró en una base nueva de otra instancia local, puerto 55439, y sus **41 tablas coincidieron íntegramente** con el snapshot de origen. Se conserva también el respaldo anterior a los tres vínculos. No se restauró ni reconstruyó la principal. El último respaldo se restauró en `intelecta_isolated_demo_final_20261019`.

Un fallo antes del commit transaccional revierte el lote. Tras la confirmación, no usar SQL improvisado para deshacer identidades ni restaurar encima de operaciones nuevas: preservar evidencia, validar el dump en otra base y obtener autorización para una recuperación/compensación controlada. Ante pérdida/caducidad de un acceso, reemitir solo el individual, no recrear cuentas ni repetir el seeder.

## Pruebas, Git y siguiente prioridad

Se ejecutaron pruebas focalizadas de altas/entrega, conciliación, ciclo de vida, restablecimiento, identidad y autorización de evaluaciones en SQLite en memoria y PostgreSQL aislado. Se verificó el conjunto completo en principal mediante la prueba opt-in descrita, además de build y validaciones frontend, Pint y revisión del diff. No se ejecutó la suite completa.

| Verificación del código final | Resultado |
|---|---|
| Pruebas focalizadas SQLite en memoria | 78 aprobadas, 665 aserciones |
| Las mismas pruebas en PostgreSQL aislado | 78 aprobadas, 665 aserciones |
| Todas las cuentas sobre principal, con rollback | 1 aprobada, 2 246 aserciones; 72/72 cuentas |
| Validaciones frontend | 20/20 aprobadas |
| Build de producción | Correcto, 3 451 módulos |
| Pint y revisión de whitespace/diff | Aprobados |

Los cambios de código, pruebas e informes se registran en un commit descriptivo de **`codex/corregir-elegibilidad-evaluaciones`**, sin merge ni push. Los manifiestos, inventarios individuales, dumps, certificados y accesos cifrados permanecen fuera de Git; ninguna contraseña operativa se añade a las pruebas nuevas. Las pruebas que requieren credenciales reales opt-in las reciben por entorno privado y las eliminan del entorno al terminar.

La recuperación administrativa está confirmada. El siguiente trabajo es la integración de **INTELECTA LAB con el portal estudiantil** para TRI 3PL, EAP y CAT. Este cierre no implementa ni modifica ese motor, no reconstruye módulos y no añade nuevas funcionalidades administrativas ajenas a la recuperación.
