# Regularización DEMO: tres vínculos realizados y 69 altas propuestas

> Foto histórica antes de aprobar las 69 altas. Estas ya fueron autorizadas, ejecutadas y verificadas. Estado vigente: `docs/informe-final-regularizacion-demo-2026-10-19.md`; procedimiento de credenciales: `docs/guia-credenciales-demo-defensa.md`.

## Estado vigente

El responsable confirmó que esta instalación es DEMO y autorizó **solo los vínculos 7→1, 8→2 y 9→3** con actor Adriana Choque Mamani, SA 1, y motivo «Conciliación global DEMO autorizada por el responsable de la instalación».

**Los tres vínculos están realizados y verificados. Las 69 altas están preparadas y probadas, pero NO ejecutadas en la principal: requieren otra aprobación explícita. La recuperación completa no está terminada.** Este estado sustituye la foto previa a autorización del informe de conciliación global.

## 1. Operaciones autorizadas ya realizadas

Se comprobó que el respaldo previo seguía intacto y que sus 41 tablas coincidían con principal antes de escribir. Se estableció en el proceso CLI el contexto explícito de la SA 1 activa y verificada (`Auth::onceUsingId(1)`), sin simular una sesión web de Adriana ni utilizar su contraseña. Se ejecutó **`intelecta:vincular-postulante`** para cada par, con sus confirmaciones y actor/motivo autorizados, dentro de una transacción exterior común de `CuentaService`. No se actualizaron las FK por SQL.

| Cuenta / expediente | Titular | Bitácora durable | Portal real | Inicio de evaluación real |
|---|---|---:|---|---|
| 7 → 1 | Valeria Nina Choque | 44 | Login, ficha, ranking y evaluaciones OK | 302, permitido |
| 8 → 2 | Diego Mamani Flores | 45 | Login, ficha, ranking y evaluaciones OK | 302, permitido |
| 9 → 3 | Mariana Quispe Rojas | 46 | Login, ficha, ranking y evaluaciones OK | 422, restricción conservada |

Las tres entradas `vincular_identidad` contienen user_id=1, nombre Adriana Choque Mamani y el motivo exacto autorizado. Se comprobó que el resto de tablas no cambió. Una nueva ejecución del comando individual sobre cada par fue rechazada por vínculo existente, sin sobrescribir ni generar nuevas bitácoras de vínculo.

`AccesoTresCuentasRealTest` comprobó las rutas HTTP mediante el kernel Laravel **sobre la base principal**, con login por credenciales DEMO existentes: 1 prueba, 75 aserciones aprobadas. Las comprobaciones HTTP estuvieron dentro de una transacción revertida; no quedaron intentos de evaluación, cambios de contraseña ni efectos de login/logout persistidos. No se afirma una comprobación visual en navegador. Las secuencias PostgreSQL pueden consumir IDs en pruebas revertidas; un salto no representa un registro académico nuevo.

Mariana conserva la cuota vencida, la matrícula observada y la habilitación restringida. No se modificaron pagos, cuotas, permisos, estados académicos ni habilitaciones de ninguno de los 72 expedientes.

## 2. Propuesta completa de las 69 altas

| Concepto | Actual en principal | Después de la propuesta, si se aprueba |
|---|---:|---:|
| Usuarios totales | 9 | 78 |
| Cuentas Estudiante | 3 | 72 |
| Expedientes existentes | 72 | 72, sin crear duplicados |
| Expedientes vinculados | 3 | 72 |
| Expedientes pendientes de cuenta | 69 | 0 |
| Estudiantes con evaluación permitida | 2 con cuenta; 60 sin cuenta | 62 |
| Estudiantes restringidos legítimamente | 1 con cuenta; 9 sin cuenta | 10 |

La propuesta comprende exclusivamente **id_post 4 a 72**, con nombres y correos existentes. No presupone los futuros IDs de usuario: los genera PostgreSQL y la acción vincula por los IDs resultantes. Entre estas 69 altas, 66 expedientes están activos y 3 inactivos. Las cuentas podrán acceder a su portal; los expedientes inactivos seguirán sin poder evaluar. No se activa ni regulariza artificialmente su situación académica.

Detalle privado de **las 69 filas**, pendiente de revisión:

- `storage/app/private/regularizacion-demo-2026-10-19/altas-69-resumen.md`: nombre, correo, CI DEMO, matrícula, estado y decisión de evaluación por fila.
- `storage/app/private/regularizacion-demo-2026-10-19/altas-69-propuestas.json`: manifiesto ejecutable, con huella de expediente, origen y `revision=pendiente` en las 69 filas.
- `altas-69-propuestas.json.dry-run.json`: comprobante del dry-run de ese contenido y conexión.
- `inventario.json` y `matriz.md` en el mismo directorio: la población completa actual, incluidas las tres cuentas ya vinculadas.

SHA256 del manifiesto pendiente:
`e1ca9feda22d72376ab82d0fe160d0d56d8c9eca28305ad289d39ea6a9f3dcd3`.

No se aprobaron automáticamente las filas. Tras la autorización se registra la revisión aprobada, cambia el hash y se exige **otro dry-run del contenido aprobado** antes de aplicar. No se considera autorización de altas la confirmación anterior de los tres vínculos.

## 3. Mecanismo existente analizado y adaptación mínima

`RolesAndUsersSeeder` solo crea tres estudiantes y en reejecuciones reasigna roles y contraseñas: NO es una herramienta de regularización. `BaseLimpiaAvalanchaSeeder` asocia cuentas solo a expedientes nuevos. No se ejecutó ni modificó ningún seeder.

`CuentaService::create()` crea una cuenta pendiente y envía una activación. En esta instalación `MAIL_MAILER=log`: llamar indiscriminadamente a esa ruta enviaría los enlaces secretos al log. Por ello se añadió el método **específico y restringido `createDemo()`**, que reutiliza la autorización SA, el mutex/transacción y la auditoría del mismo servicio, pero no envía notificaciones. Está prohibido fuera de local/testing y exige contexto SA explícito. No se cambió el flujo ordinario de cuentas reales.

La nueva cuenta DEMO se activa administrativamente y se marca el correo como validado **solo en calidad de fixture autorizado**, sin fingir la verificación de un buzón real. Recibe una contraseña aleatoria de 64 caracteres que jamás se entrega ni registra. Después:

1. Se utiliza `VincularUsuarioPostulanteAction`, la misma acción auditada del comando existente, con la cuenta todavía sin roles y el expediente ya aprobado.
2. `CuentaService::assignRoles()` asigna exclusivamente Estudiante, validando que el perfil ya está vinculado. No asigna permisos directos ni modifica el catálogo de roles.
3. El broker nativo de Laravel crea un token de establecimiento/restablecimiento de contraseña: en BD solo queda su hash, caduca en **60 minutos** y se consume una sola vez.
4. Se guarda bitácora `alta_demo_estudiante` con actor, motivo, user_id, id_post e identificador de lote, sin token ni contraseña. Se exige además la persistencia de las bitácoras de creación, vínculo y asignación de roles.
5. Los accesos se escriben únicamente en un paquete cifrado con `Crypt`/APP_KEY, nuevo y dentro de `storage/app/private`, excluido de Git. El comando nunca imprime secretos ni utiliza mailer/log para entregarlos.

Esto implementa credenciales **temporales de establecimiento de contraseña**, no una contraseña compartida ni una contraseña temporal sin caducidad. El estudiante escoge su contraseña mediante el flujo nativo; una vez establecida podrá ingresar normalmente durante la defensa. El secreto del enlace no debe compartirse con otros estudiantes.

## 4. Ejecución propuesta, todavía no autorizada

Comando de propuesta/dry-run, ya ejecutado sin escrituras en BD:

```powershell
php artisan intelecta:altas-demo-estudiantes storage/app/private/regularizacion-demo-2026-10-19/altas-69-propuestas.json
```

La opción `--preparar` genera una propuesta nueva sin aprobarla. `--resumen=RUTA_PRIVADA_NUEVA` exporta sus filas a Markdown. No se admiten preparar y aplicar simultáneamente.

Para aplicar posteriormente se requieren: manifiesto revisado, dry-run exacto de menos de 24 horas, certificado de respaldo íntegro de esta conexión, SA explícitamente autorizado, motivo trazable, destino nuevo de paquete cifrado y confirmación interactiva que muestra hash, base y actor. No hay actor por defecto. **No ejecutar este ejemplo antes de la nueva aprobación:**

```powershell
php artisan intelecta:altas-demo-estudiantes RUTA_MANIFIESTO_APROBADO --aplicar --actor=ID_SA_AUTORIZADO --motivo="REFERENCIA_DE_AUTORIZACION_DE_LAS_69_ALTAS" --respaldo=RUTA_CERTIFICADO_VERIFICADO --entrega=RUTA_PRIVADA_NUEVA.enc
```

Se valida cada expediente, huella, nombre, CI, correo, ausencia de archivo y colisiones de correo contra usuarios y expedientes, incluidos archivados. Una cuenta ya existente sin vínculo no se adopta por coincidencia: requiere conciliación manual. Solo una pareja previamente creada y auditada por **el mismo lote** puede responder `ya_creada_sin_cambios`.

Todo el lote comparte la transacción/lock de cuentas. Ante un conflicto tardío o una bitácora no persistida, se revierten cuentas, vínculos, roles, tokens y auditorías del lote completo. Si falla la creación del paquete, también se revierte; no se sobrescribe un archivo existente. Reejecutar un lote correcto no cambia contraseñas, tokens, cuentas, roles ni paquete. No se invoca ningún servicio de sincronización de cuotas o habilitaciones.

## 5. Entrega y restablecimiento controlados

Después de las altas autorizadas, un SA puede entregar **un acceso individual**, nunca volcar el paquete completo en consola:

```powershell
php artisan intelecta:entregar-acceso-demo ID_POST --actor=ID_SA_AUTORIZADO --paquete=RUTA_PAQUETE_CIFRADO --salida=RUTA_PRIVADA_NUEVA.html --motivo="Entrega individual al titular DEMO aprobado"
```

Exige confirmación y registra `entregar_acceso_demo` sin secretos. Verifica que la cuenta fue creada por un lote auditado, sigue activa, tiene exclusivamente Estudiante y que el token no caducó, se consumió o sustituyó. Genera un HTML individual privado con el enlace. **El HTML sí contiene un secreto temporal:** entregarlo solamente al destinatario/responsable, no publicarlo, no adjuntarlo a Git y eliminarlo tras uso. Abrir el enlace con sesión cerrada o en ventana privada; establecer contraseña, iniciar sesión y entrar al portal. El origen configurado actualmente es `http://127.0.0.1:8000`; usar el servidor correcto de esta instalación.

Si no se usa durante los 60 minutos, puede reemitirse **solo ese acceso**, sin resetear su contraseña ni afectar las otras cuentas:

```powershell
php artisan intelecta:entregar-acceso-demo ID_POST --actor=ID_SA_AUTORIZADO --reemitir --salida=RUTA_PRIVADA_NUEVA.html --motivo="Reemisión individual autorizada por caducidad"
```

La reemisión invalida el token anterior de ese correo. Un enlace consumido no vuelve a funcionar. El paquete cifrado necesita la APP_KEY de esta instalación: no rotarla ni publicarla; ante pérdida del paquete, recurrir a reemisión individual auditada, no a recrear cuentas ni a repetir seeders. Las restricciones académicas siguen aplicándose después de establecer cualquier contraseña.

## 6. Nuevo respaldo verificable

Respaldo **posterior a los tres vínculos** y anterior a cualquier alta:
`storage/app/private/regularizacion-demo-2026-10-19/intelecta-tras-tres-vinculos.dump`.

SHA256:
`07b403bb81ebae344673e79d7acceab5ad4fa243eaa80b36971286091781ea1e`.

Certificado: el mismo nombre con `.verified.json`. Fue generado con snapshot de solo lectura y restaurado en **otra instancia PostgreSQL, 127.0.0.1:55439, base nueva `intelecta_isolated_demo_postlinks_20261019`**. Las **41/41 tablas coinciden íntegramente**. No se restauró sobre principal ni se aplicaron migraciones. Se conservan tanto este respaldo como el anterior a los tres vínculos.

Antes de aplicar las altas se revisará otra vez que no haya cambios desde el respaldo/propuesta; si cambiaron datos o expiró el comprobante, se renovará la evidencia correspondiente. El dump contiene información sensible y debe permanecer privado.

## 7. Pruebas y evidencia del conjunto completo

Pruebas focalizadas: altas y consola, ciclo de vida de cuentas, restablecimiento de contraseñas, identidad estable y resultados trazables. Comprueban: ausencia de notificaciones, secretos cifrados y no incluidos en bitácora, rol exclusivo, contexto SA, prohibición en producción, aprobación requerida, archivo previo no sobrescrito, conflictos tardíos, fallo de auditoría, rollback e idempotencia, caducidad/consumo/entrega/renovación individual.

Resultado: **66/66 pruebas y 487 aserciones, tanto en SQLite en memoria como en PostgreSQL aislado**. No se ejecutó la suite completa. La prueba adicional sobre principal aprobó 75 aserciones; la del conjunto completo sobre restauración aprobó **1 960 aserciones**.

`AltasDemoPoblacionRestauradaTest` se ejecutó sobre **la restauración exacta posterior a los tres vínculos**, sin migraciones ni seeders y con rollback obligatorio. Se prepararon y aplicaron las 69 altas solo dentro de esa copia transaccional, se comprobó la reejecución sin cambios y luego se probaron las **72 cuentas**, no solo una muestra:

- 69 nuevos tokens consumidos por el endpoint nativo para establecer contraseña; las tres cuentas anteriores utilizaron sus credenciales existentes.
- 72/72 login y acceso a ficha, ranking y módulo de evaluaciones correctos.
- 72 perfiles, 72 cuentas Estudiante y 72 vínculos únicos; ningún expediente duplicado.
- 62 inicios de evaluación permitidos y 10 correctamente denegados.
- Matrículas, inscripciones, cuotas/pagos, habilitaciones, permisos financieros y migraciones intactos.
- Ninguna cuenta, token ni contraseña del ensayo quedó en principal. El paquete de prueba se eliminó al terminar y la copia volvió a su contenido previo mediante rollback.

La prueba es opt-in mediante `INTELECTA_DEMO_69_RESTORED_DB`, restringida a nombres `intelecta_isolated_*` y puerto 55439. **El éxito de la copia no sustituye la aprobación de las altas ni la verificación posterior de las 72 cuentas reales.**

## 8. Recuperación, estado de Git y siguiente autorización

Un fallo anterior al commit transaccional revierte el lote íntegro. No se confirma un alta sin conservar sus accesos cifrados. Una reejecución idempotente no regenera secretos; una reemisión individual requiere SA, motivo y confirmación. Después de un commit, cualquier compensación o restauración total exige autorización adicional y evaluación de operaciones posteriores al snapshot; nunca desvincular por SQL improvisado ni restaurar encima de pagos nuevos.

Rama actual: `codex/corregir-elegibilidad-evaluaciones`, HEAD `e1aef62bf522f4799850427dcb5151fff013561d`. Se mantienen los cambios de autorización de la fase previa y se añaden los servicios/comandos de altas/entrega y las pruebas específicas. No hay staging, commit, push ni merge. El commit final de corrección permanece pendiente de completar y verificar la regularización de las 72 cuentas. No se modificó CAT/TRI/EAP, ninguna migración, seeder ni módulo financiero.

Archivos de esta fase: `CuentaService.php` (método DEMO acotado), `AltasDemoEstudianteService.php`, comandos `AltasDemoEstudiantes.php` y `EntregarAccesoDemo.php`, pruebas `AccesoTresCuentasRealTest.php`, `AltasDemoCommandTest.php`, `AltasDemoEstudianteTest.php`, `AltasDemoPoblacionRestauradaTest.php` y este informe. Se añadió también la referencia de actualización al informe anterior. Los artefactos privados de propuesta/respaldo están ignorados por Git. Pint y `git diff --check` aprobados; la instancia aislada se detiene al terminar, conservando los respaldos.

**Autorización requerida ahora:** aprobar las **69 filas id_post 4–72** del manifiesto/resumen, designar expresamente al SA responsable y un motivo para estas altas, y aceptar el procedimiento de accesos individuales caducables con activación administrativa exclusivamente DEMO. La SA 1 está disponible, pero la autorización anterior se limitó a los tres vínculos; no se le atribuyeron las 69 altas.
