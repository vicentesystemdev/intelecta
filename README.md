# INTELECTA

Plataforma de gestión académica para la **Academia Universitaria Avalancha**. Apoya la preparación preuniversitaria mediante registro de postulantes, organización de programas y grupos, evaluaciones y seguimiento académico.

**Actualizado en octubre de 2026.** El [repositorio oficial](https://github.com/vicentesystemdev/intelecta) mantiene la versión actual del sistema en la rama `main`. El alcance comprobado y las limitaciones se detallan en [Estado actual](docs/estado_actual.md).

## Funcionalidades disponibles

- Autenticación, verificación de correo, recuperación de contraseña, ciclo de vida de cuentas, roles y permisos; bitácora y exportación.
- Postulantes con fecha de nacimiento y vínculo explícito a su cuenta mediante `user_id`.
- Cargos, personal institucional y tutores vinculados a personal; programas, grupos, inscripciones y asignaciones tutoriales.
- Matrículas, cuotas, habilitación académica y asistencia individual o por grupo.
- Materias, áreas, temas, preguntas con alternativas y plantillas con ponderaciones.
- Calendario de simulacros, portal del postulante y aplicación de evaluaciones.
- Respuestas persistidas, cálculo de puntajes, resultados por materia, ficha y ranking académico.
- Reportes descriptivos con exportación PDF y Excel e indicadores de seguimiento.

Los resultados operativos se calculan desde `respuestas_evaluacion`: no son cifras fijadas en la interfaz. Los seeders generan **datos ficticios**, incluyendo respuestas con las que se ejecuta el mismo cálculo. La pantalla **Learning Analytics** y algunas portadas son demostraciones con datos estáticos; no constituyen analítica predictiva operativa.

## Tecnologías y arquitectura

| Componente | Tecnología confirmada |
| --- | --- |
| Backend | PHP 8.3+, Laravel 13, Eloquent |
| Interfaz | React 18, Inertia.js 2, Vite 8, Tailwind CSS 3 |
| Base institucional | PostgreSQL; SQLite en memoria para la suite de pruebas |
| Seguridad | Laravel Breeze y Spatie Permission 8 |
| Exportación | Laravel Dompdf y Laravel Excel |
| Pruebas | PHPUnit 12 y pruebas de validación con Node.js |

Las versiones exactas están en `composer.lock` y `package-lock.json`. No se necesitan Redis ni servicios externos para la demostración con la configuración de ejemplo.

La aplicación mantiene la estructura de Laravel y organiza el negocio en `app/Domains`: `Academico`, `Evaluaciones`, `Institucional`, `Postulantes`, `Resultados`, `Reportes` y `Seguridad`. `LearningAnalytics` reserva el espacio de evolución futura. Los adaptadores HTTP están en `app/Http` y las páginas React en `resources/js/Pages`. El flujo orientativo es Controller → FormRequest → DTO → Action → Service → Repository → Model; cada caso utiliza las capas que necesita.

Consulte [README_ARQUITECTURA.md](README_ARQUITECTURA.md) para responsabilidades y convenciones.

## Instalación local desde GitHub

Requisitos: Git, PHP 8.3 o superior, Composer 2, Node.js **22.12+ de la serie 22** con npm y PostgreSQL. PHP debe disponer de PDO PostgreSQL, PDO SQLite para pruebas, mbstring, XML/DOM, fileinfo, curl, zip, GD y las extensiones que comprueba Composer.

```bash
git clone https://github.com/vicentesystemdev/intelecta.git
cd intelecta
git switch main
composer install
npm ci --ignore-scripts
```

Copie el ejemplo **solo si no existe `.env`**:

```bash
# Linux/macOS
cp .env.example .env
```

```powershell
# PowerShell
if (!(Test-Path .env)) { Copy-Item .env.example .env }
```

Cree una base PostgreSQL **nueva y exclusiva para demostración** y un usuario con permiso para crear sus tablas. Configure en `.env` `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` con sus valores locales. El ejemplo usa `intelecta_demo` y no incluye contraseñas ni claves reales. Ajuste `APP_URL` si cambia la dirección del servidor.

```bash
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Abra `http://127.0.0.1:8000`. Para desarrollo con recarga automática, ejecute `npm run dev` en otra terminal; puede usar también `composer run dev` para iniciar servidor, cola y Vite juntos.

`DatabaseSeeder` carga `BaseLimpiaAvalanchaSeeder`. **Ejecute el seeder únicamente en una base de demostración**, con `APP_ENV=local` o `testing`: sincroniza roles, actualiza registros coincidentes y restablece las contraseñas de las cuentas ficticias. No es un procedimiento para actualizar una base con datos reales. No utilice `migrate:fresh` ni `db:wipe` en la base de trabajo.

La [guía de instalación](docs/guia_instalacion.md) incluye creación de la base, orden del seeder, correo local y actualización de instalaciones existentes.

## Roles y rutas principales

| Rol técnico | Acceso de referencia |
| --- | --- |
| Super Administrador | Todos los módulos; usuarios, permisos y bitácora |
| Administrador | Gestión académica e institucional; sin administración técnica de seguridad |
| Docente | Consultas y asistencia de sus asignaciones; preguntas y resultados según permisos y ámbito |
| Estudiante | Portal de su propio expediente y evaluaciones; requiere vínculo y habilitación |

El nombre institucional del `Docente` es tutor académico y el del `Estudiante` es postulante. El menú depende de los permisos efectivos y del estado de la cuenta; un cargo institucional no otorga un rol automáticamente. Las cuentas exclusivamente ficticias están documentadas en [Usuarios de demostración](docs/usuarios_prueba_avalancha.md).

| Ruta | Función |
| --- | --- |
| `/login` | Ingreso; no existe registro público de cuentas |
| `/dashboard` | Panel administrativo |
| `/postulantes` | Expedientes |
| `/admin/institucional/programas` y `/admin/institucional/grupos` | Organización académica |
| `/admin/institucional/personal` y `/admin/institucional/tutores` | Personal y tutoría |
| `/preguntas` y `/plantillas-evaluacion` | Banco e instrumentos |
| `/admin/evaluaciones/resultados` | Resultados persistidos |
| `/reportes-academicos` | Informes y exportación |
| `/estudiante/evaluaciones` | Aplicación de evaluaciones |

Más rutas y funciones por rol en el [manual de usuario](docs/manual_usuario.md). Inventario exacto: `php artisan route:list`.

## Verificación

```bash
composer validate
php artisan route:list
npm run build
php artisan test
node --test tests/input-validation.test.mjs
```

Espere a que termine el build antes de ejecutar las pruebas HTTP; no reconstruya los assets mientras la suite está en curso. `phpunit.xml` configura SQLite en memoria, correo y sesiones en memoria. Antes de probar, compruebe que no hay una configuración cacheada que apunte a una base real (`bootstrap/cache/config.php`), ni variables del shell que sobrescriban la conexión de pruebas. Las pruebas de concurrencia PostgreSQL se omiten por defecto y requieren un entorno expresamente aislado. Consulte [Verificación de INTELECTA](docs/verificacion.md) para los resultados de la actualización.

## Limitaciones y documentación

Learning Analytics es conceptual; no se implementan predicción validada, TRI 3PL, EAP ni CAT. El laboratorio independiente `intelecta_lab` queda fuera de esta entrega. La habilitación recalculada desde cuotas puede sustituir un ajuste manual; el rendimiento general agrega las evaluaciones finalizadas del postulante y se asocia a la inscripción más reciente. Estos límites requieren revisión antes de un uso institucional con múltiples cohortes.

- [Guía de instalación](docs/guia_instalacion.md)
- [Manual de usuario](docs/manual_usuario.md)
- [Estado actual y pendientes](docs/estado_actual.md)
- [Recorrido de demostración](docs/guia_demostracion.md)
- [Pruebas y verificaciones](docs/verificacion.md)
- [Índice técnico y auditorías históricas](docs/README.md)

Las auditorías anteriores se conservan como evidencia histórica; el estado de octubre se describe en los documentos actuales y se sustenta en el código y las pruebas.
