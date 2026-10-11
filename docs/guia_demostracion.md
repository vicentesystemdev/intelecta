# Guía de demostración para la defensa — Hito 5

Use una instalación dedicada con datos ficticios, siguiendo la [guía de instalación](guia_instalacion.md). No recargue seeders sobre la base de trabajo para preparar la defensa. Las cuentas y su contraseña exclusivamente demo se encuentran en [usuarios de demostración](usuarios_prueba_avalancha.md).

## Preparación

Compruebe `php artisan route:list`, ejecute `npm run build` y, cuando termine, `php artisan test`. Inicie el servidor con `php artisan serve --host=127.0.0.1 --port=8000`. Revise antes el calendario y la disponibilidad de una plantilla: varias fechas se ajustan al día en que se cargan los fixtures.

Si los assets ya están compilados, Vite no necesita permanecer activo. Para cambios durante preparación, abra otra terminal con `npm run dev`. Mantenga una ventana privada o cierre sesión al cambiar de rol para evitar confundir permisos.

## Recorrido sugerido

1. **Repositorio como entrega oficial.** Abra [GitHub](https://github.com/vicentesystemdev/intelecta), seleccione `main`, muestre el README, `docs/`, los archivos de bloqueo y la historia de commits. Explique la estructura Laravel + dominios y páginas React/Inertia.
2. **Administración.** Ingrese con Adriana (Super Administrador), abra `/dashboard` y explique que las cifras de la base demo representan registros ficticios. Muestre personal/cargos y la distinción entre cargo institucional y rol técnico.
3. **Proceso académico.** En programas, grupos e inscripciones muestre relaciones, capacidad, turnos y validación de datos. Abra un postulante y su ficha; explique la fecha de nacimiento y el vínculo explícito de cuenta.
4. **Tutoría y administración.** Abra `/admin/institucional/tutores`, asignaciones y asistencia. Muestre matrículas, cuotas y habilitación, indicando que el estado puede recalcularse al cambiar cuotas.
5. **Instrumento de evaluación.** Recorra materias, áreas, temas, banco de preguntas y una plantilla con sus preguntas y ponderaciones. Use `/preguntas` y `/plantillas-evaluacion`, no las tarjetas estáticas de portada.
6. **Aplicación real.** Ingrese con Valeria (rol Estudiante) en `/estudiante/evaluaciones`. Inicie una plantilla disponible, responda y finalice. Muestre el puntaje y el detalle de respuestas; no prometa una nota fija, pues depende de las selecciones y la plantilla.
7. **Trazabilidad.** Vuelva a la cuenta administrativa y abra `/admin/evaluaciones/resultados`; identifique el intento recién finalizado y contraste respuestas, puntaje y ficha. En el código muestre `EvaluacionAplicadaService::enviar()` y `ResultadoAcademicoService::recalculate()`.
8. **Permisos.** Ingrese con Rodrigo (Docente), muestre grupos/asistencia de su ámbito y la ausencia de administración de seguridad. La matriz actual no le concede reportes globales. Con Mariana, muestre la restricción académica preparada en la demo.
9. **Informes.** Con la cuenta administrativa muestre ranking, filtros y una descarga PDF o Excel en `/reportes-academicos`. Evite presentar la clasificación como pronóstico de admisión.
10. **Alcance honesto.** Abra Learning Analytics solo para explicar su diseño conceptual: sus gráficos son estáticos. Finalice mostrando las pruebas y los pendientes del Hito 5.

## Evidencias útiles

- Pantalla de resultados con un intento generado durante la demostración.
- Detalle de respuestas y relación con plantilla/postulante.
- Permisos efectivos por rol y restricción del expediente ajeno.
- Migraciones, seeders y guías en GitHub.
- [Verificación actual](verificacion_hito5.md), con pruebas omitidas identificadas.

Los nombres y contraseñas publicados son exclusivamente ficticios para desarrollo. No muestre `.env`, logs privados con enlaces de activación, credenciales reales ni respaldos de la base de trabajo.
