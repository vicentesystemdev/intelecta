# Credenciales DEMO para la defensa

## Qué está listo

La base principal tiene **72 cuentas Estudiante, 72 expedientes y 72 vínculos únicos**, verificados mediante las rutas del portal.

- **62 cuentas** pueden iniciar evaluaciones.
- **10 cuentas** ingresan al portal, pero no pueden evaluar por restricciones legítimas.
- **3 cuentas anteriores** conservan su contraseña operativa: expedientes 1, 2 y 3 (Valeria, Diego y Mariana). No se cambiaron sus credenciales. Esto no acredita que el titular hubiese elegido personalmente la contraseña del fixture.
- **69 cuentas nuevas**, expedientes 4–72, deben establecer su contraseña antes del primer ingreso normal. Su acceso temporal está cifrado; no existe una contraseña compartida para ellas.

Listado individual sin secretos:
`storage/app/private/regularizacion-demo-2026-10-19/estado-final-72-cuentas.md`.
Incluye correo de ingreso, expediente, usuario, matrícula, decisión de evaluación y estado de credencial. Es una foto del cierre: después de una entrega, confirmar el login y registrar administrativamente ese resultado, nunca la contraseña ni el enlace.

Para mostrar un flujo positivo, elegir una fila **permitida**; por ejemplo, expediente 4, Alejandro Poma Choque. Para mostrar la regla negativa, usar Mariana, expediente 3. Un sorteo entre las 72 cuentas puede seleccionar legítimamente una de las 10 restringidas: eso no es un fallo de identidad.

## Activar una de las 69 cuentas nuevas

Desde `C:\laragon\www\intelecta`, por el operador administrativo autorizado como Adriana, SA 1:

1. Elegir el `id_post` en el listado; no confundirlo con `user_id`.
2. Emitir **un acceso individual justo antes de entregarlo**, incluso si el paquete original ya caducó. Ejemplo para el expediente 4:

```powershell
php artisan intelecta:entregar-acceso-demo 4 --actor=1 --reemitir --salida="C:/laragon/www/intelecta/storage/app/private/regularizacion-demo-2026-10-19/entrega-post-4-01.html" --motivo="Entrega individual DEMO autorizada para la defensa, expediente 4"
```

3. Confirmar los IDs mostrados. El comando actúa en el contexto CLI del SA indicado, valida la autorización y registra `entregar_acceso_demo`. No muestra el secreto en consola ni lo envía por el mailer `log`.
4. Abrir localmente el HTML privado con sesión cerrada o en ventana privada. Entregarlo solo al destinatario/responsable autorizado. Seguir su botón para **elegir una contraseña propia** y después entrar con el correo del listado y esa contraseña.
5. Comprobar ficha y evaluaciones. Anotar que se completó el acceso en el acta de entrega, sin guardar la contraseña. Eliminar el HTML de entrega tras su uso; contiene un secreto temporal y no es un informe.

El enlace vence a los **60 minutos** y es de **un solo uso**. La reemisión invalida el enlace anterior de esa cuenta, no su contraseña actual ni los accesos de otros estudiantes. Si ya se estableció una contraseña y funciona, no es necesario reemitir. Si debe repetirse la entrega, usar un nombre de archivo nuevo (`-02.html`, etc.); no sobrescribir una entrega anterior.

El portal usa el origen configurado actualmente, `http://127.0.0.1:8000`. Asegurarse de que el servidor Laravel está disponible allí. No publicar enlaces de establecimiento en informes, capturas, chats, logs o Git. Tampoco abrir el HTML mientras se comparte pantalla con todo el curso.

## Usar un acceso original todavía vigente

Se puede extraer **solo una entrega individual** del paquete cifrado ya generado, en lugar de reemitir:

```powershell
php artisan intelecta:entregar-acceso-demo 4 --actor=1 --paquete="C:/laragon/www/intelecta/storage/app/private/regularizacion-demo-2026-10-19/accesos-69-cifrados.enc" --salida="C:/laragon/www/intelecta/storage/app/private/regularizacion-demo-2026-10-19/entrega-post-4-original.html" --motivo="Entrega individual DEMO autorizada del acceso vigente, expediente 4"
```

El comando rechaza accesos caducados, consumidos o sustituidos. En esos casos usar la reemisión individual anterior. No descifrar ni mostrar el paquete completo: contiene los secretos de 69 cuentas y necesita la APP_KEY de esta instalación. Custodiar ambos por separado y nunca subirlos a Git.

## Restricciones que deben conservarse

No regularizar cuotas ni activar expedientes para facilitar una demostración. Los expedientes restringidos son **3, 9, 19, 20, 29, 39, 49, 58, 59 y 69**:

- Cuota vencida: 3, 9, 19, 29, 39, 49, 59 y 69.
- Expediente inactivo: 20, 39 y 58.
- El expediente 39 pertenece a ambos conjuntos: el total son 10, no 11.

Todos conservan acceso a su propio portal; el backend debe rechazar el inicio de evaluación. No es necesario probar la denegación creando pagos ficticios.

## Qué no hacer

- No ejecutar seeders, `migrate:fresh`, `db:wipe` ni migraciones pendientes sobre la principal.
- No cambiar FK por SQL ni crear expedientes nuevos para recuperar una cuenta.
- No compartir una contraseña única ni imprimir el contenido del paquete cifrado.
- No restaurar un dump sobre una base que reciba nuevas operaciones. Ante un incidente, conservar evidencia y solicitar recuperación controlada usando los respaldos verificados.
- No usar las pruebas opt-in sobre principal durante la exposición: mantienen transacciones y ejecutan intentos temporales que luego revierten. Para la defensa se usa el portal normal.

La regularización administrativa está completada. La elección individual de contraseña de las 69 cuentas nuevas sigue siendo un paso de entrega deliberado, no un vínculo pendiente. No se incorporó ninguna función nueva al motor CAT.
