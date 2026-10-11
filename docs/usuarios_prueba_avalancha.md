# Usuarios de acceso - Academia Universitaria Avalancha

Estas cuentas, nombres y correos son **fixtures exclusivamente ficticios de desarrollo**.
Todas usan la contraseña demo **`Avalancha#2026`**. No deben utilizarse en producción;
`RolesAndUsersSeeder` rechaza la carga fuera de `local` y `testing`. No se ha auditado
ningún despliegue externo ni se han cambiado credenciales de la base de trabajo.

| Rol | Nombre | Correo | Contraseña | Ruta recomendada para prueba |
|---|---|---|---|---|
| Super Administrador | Adriana Choque Mamani | `adriana.choque@avalancha.edu.bo` | `Avalancha#2026` | `/dashboard` para revisar todos los módulos y permisos |
| Administrador | Marco Antonio Torrez Quispe | `marco.torrez@avalancha.edu.bo` | `Avalancha#2026` | `/admin/institucional/programas` para gestión institucional |
| Tutor Académico - Matemática | Rodrigo Salazar Condori | `rodrigo.salazar@avalancha.edu.bo` | `Avalancha#2026` | `/admin/institucional/asistencia` para seguimiento de grupos |
| Tutor Académico - Física | Carla Mendoza Rojas | `carla.mendoza@avalancha.edu.bo` | `Avalancha#2026` | `/admin/evaluaciones/resultados` para lectura de resultados |
| Tutor Académico - Química | Luis Fernando Arce Huanca | `luis.arce@avalancha.edu.bo` | `Avalancha#2026` | `/admin/institucional/asistencia` para seguimiento de su ámbito |
| Postulante habilitado | Valeria Nina Choque | `valeria.nina@postulante.avalancha.edu.bo` | `Avalancha#2026` | `/estudiante/evaluaciones` para iniciar una evaluación |
| Postulante observado | Diego Mamani Flores | `diego.mamani@postulante.avalancha.edu.bo` | `Avalancha#2026` | `/estudiante/evaluaciones` para verificar acceso con seguimiento |
| Postulante restringido | Mariana Quispe Rojas | `mariana.quispe@postulante.avalancha.edu.bo` | `Avalancha#2026` | `/estudiante/evaluaciones` para verificar restricción institucional |

## Cuenta académica adicional

| Rol | Nombre | Correo | Contraseña | Uso recomendado |
|---|---|---|---|---|
| Tutor Académico | Patricia Vargas Choque | `patricia.vargas@avalancha.edu.bo` | `Avalancha#2026` | Consultar grupos y asistencia de sus asignaciones; sin permisos globales de coordinación |

Los perfiles de tutor utilizan el rol técnico existente `Docente`; los perfiles de postulante utilizan el rol técnico existente `Estudiante`. No se crean roles alternativos que compitan con la configuración de Spatie Permission.

La matriz vigente limita el Docente a su ámbito académico y no le concede reportes
globales. Para gestión de programas y asignaciones use Administrador o Super
Administrador. Consulte el [manual por rol](manual_usuario.md) y la
[matriz RBAC](../app/Domains/Seguridad/Support/MatrizRbac.php).
