# Base institucional de Academia Universitaria Avalancha

## Enfoque

`BaseLimpiaAvalanchaSeeder` prepara una base coherente para presentar INTELECTA como plataforma académica de nivelación preuniversitaria. El escenario principal es el ingreso a la Facultad de Ingeniería de la UMSA y el contexto secundario incluye UPEA, EMI, UCB y UPB.

La información es ficticia, institucional y no identifica a personas reales. Los registros se relacionan mediante claves foráneas y no se generan resultados académicos aislados.

## Modalidades académicas

### Prefacultativo Ingeniería UMSA

- Gestión I/2026 con duración semestral.
- Tres grupos: mañana, tarde y noche.
- Matemática, Física y Química como materias centrales.
- Tres plantillas parciales por materia.
- Parciales históricos con evaluaciones aplicadas y respuestas registradas.
- Terceros parciales y simulacro final disponibles en el calendario.

### Prueba de Suficiencia Ingeniería UMSA

- Programa intensivo.
- Grupo de fin de semana.
- Evaluación Integral Ingeniería UMSA con distribución:
  - Matemática: 40%.
  - Física: 30%.
  - Química: 30%.
- Simulacro integral con resultados trazables.

## Volumen preparado

| Componente | Contenido |
|---|---|
| Universidades | 5 |
| Colegios | 10 |
| Postulantes | 72 |
| Orientación UMSA Ingeniería | 51 postulantes, aproximadamente 71% |
| Materias | Matemática, Física, Química y Razonamiento Lógico / Aptitud Académica |
| Banco de preguntas | 125 reactivos: 40 Matemática, 35 Física, 35 Química y 15 Aptitud Académica |
| Alternativas | 5 por pregunta, con una respuesta correcta |
| Plantillas | 9 parciales, evaluación integral, diagnóstico y simulacros integrales |
| Programas | 4 |
| Grupos | 6 |
| Asistencia | 8 sesiones recientes por inscripción activa |
| Cuotas | 3 para el ciclo semestral y 2 para programas intensivos |

## Coherencia transaccional

1. Cada postulante pertenece a un colegio y a una carrera vinculada con universidad.
2. Cada inscripción referencia un programa y un grupo sin superar su capacidad.
3. Cada matrícula nace de una inscripción y sus cuotas determinan una habilitación coherente.
4. La asistencia usa sesiones reales del grupo y tutor según materia.
5. Cada simulacro referencia programa, grupo y plantilla.
6. Cada evaluación aplicada referencia postulante y plantilla.
7. Cada resultado se calcula desde respuestas asociadas a preguntas y alternativas reales.
8. El rendimiento y ranking se actualizan desde evaluaciones finalizadas; la asistencia se incorpora al rendimiento.

## Orden de carga

1. Roles, permisos y usuarios de acceso.
2. Universidades, carreras y colegios.
3. Materias, áreas y temas.
4. Preguntas y alternativas.
5. Plantillas y ponderaciones.
6. Programas y grupos.
7. Tutores y asignaciones.
8. Postulantes.
9. Inscripciones.
10. Matrículas, cuotas y habilitaciones.
11. Asistencia.
12. Simulacros.
13. Evaluaciones aplicadas y respuestas.
14. Rendimientos derivados y porcentaje de asistencia.

## Estados administrativos preparados

- Valeria Nina Choque: matrícula al día y acceso habilitado.
- Diego Mamani Flores: cuotas vigentes pendientes y estado observado.
- Mariana Quispe Rojas: cuota vencida y acceso temporalmente restringido.
- El resto mantiene una distribución aproximada de 60% al día, 20% pendiente, 10% vencida y 10% con beca o exención.

## Recorrido sugerido para la defensa

1. Ingresar como Adriana Choque y abrir `/dashboard`.
2. Mostrar `/admin/institucional/programas` y los grupos del Prefacultativo y Prueba de Suficiencia.
3. Abrir `/admin/institucional/asistencia` para evidenciar seguimiento tutorial.
4. Revisar `/admin/evaluaciones/preguntas` y filtrar por Matemática, Física o Química.
5. Mostrar `/admin/evaluaciones/plantillas` y la composición de parciales e integral.
6. Abrir `/admin/evaluaciones/resultados` para evidenciar trazabilidad de respuestas y puntajes.
7. Continuar con `/admin/institucional/ranking` y una ficha académica.
8. Ingresar con Valeria para recorrer el portal habilitado.
9. Ingresar con Mariana para mostrar la restricción académica no sancionatoria.

## Ejecución

Para reconstruir toda la base:

```bash
php artisan migrate:fresh --seed
```

Para ejecutar solamente el escenario Avalancha sobre una estructura ya migrada:

```bash
php artisan db:seed --class=BaseLimpiaAvalanchaSeeder
```

El seeder usa claves naturales, `updateOrCreate`, `firstOrCreate` y sincronización de relaciones. No usa `truncate` ni elimina registros.
