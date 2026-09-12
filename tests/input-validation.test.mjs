import assert from 'node:assert/strict';
import fs from 'node:fs';
import test from 'node:test';

// Vite's JSON import is replaced in memory only; production source is never rewritten.
const source = fs.readFileSync(new URL('../resources/js/lib/inputValidation.js', import.meta.url), 'utf8')
    .replace(/^import options.*$/m, 'const options = {};');
const { constraintsWhenChanged, validationProps, inputConstraints } = await import('data:text/javascript;base64,' + Buffer.from(source).toString('base64'));

test('account editing is role-free and role assignment is a separate full-set operation', () => {
    const account = fs.readFileSync(new URL('../resources/js/Components/Sistema/UsuarioForm.jsx', import.meta.url), 'utf8');
    assert.doesNotMatch(account, /role:\s|roles:\s|roles\?\.\[0\]|setData\('role'/);
    const roles = fs.readFileSync(new URL('../resources/js/Components/Sistema/UsuarioRolesForm.jsx', import.meta.url), 'utf8');
    assert.match(roles, /roles: usuario\.roles\.map/);
    assert.match(roles, /admin\.sistema\.usuarios\.roles/);
    assert.match(roles, /form\.data\.roles\.filter/);
    assert.match(roles, /\[\.\.\.form\.data\.roles, name\]/);
});

test('layout and landing use the whole role set and backend context, never the first role', () => {
    const layout = fs.readFileSync(new URL('../resources/js/Layouts/AdminLayout.jsx', import.meta.url), 'utf8');
    assert.doesNotMatch(layout, /roles\?\.\[0\]/);
    assert.match(layout, /roles\?\.join/);
    const landing = fs.readFileSync(new URL('../resources/js/Pages/Welcome.jsx', import.meta.url), 'utf8');
    assert.doesNotMatch(landing, /route\('dashboard'\)|roles\.includes/);
    assert.equal((landing.match(/href=\{auth\.homeUrl\}/g) || []).length, 3);
    const menu = fs.readFileSync(new URL('../resources/js/Components/AppSidebar.jsx', import.meta.url), 'utf8');
    assert.match(menu, /auth\.security === true/);
});

test('tutor uses explicit Personal and a separate opt-in identity edit, never legacy identity fields', () => {
    assert.equal(validationProps('personal_id').required, true);
    for (const field of ['nombres_tutor', 'apellidos_tutor', 'ci_tutor', 'celular_tutor', 'correo_tutor']) {
        assert.equal(inputConstraints[field], undefined);
    }
    const ui = fs.readFileSync(new URL('../resources/js/Pages/Institucional/Tutores/Index.jsx', import.meta.url), 'utf8');
    assert.match(ui, /capacidades\.editarPersonal/);
    assert.match(ui, /form\.setData\('personal'/);
    assert.match(ui, /personal_id: String\(tutor\.personal_id\)/);
    assert.doesNotMatch(ui, /form\.setData\('user_id'/);
    assert.doesNotMatch(ui, /nombres_tutor|apellidos_tutor|ci_tutor|celular_tutor|correo_tutor/);
});

test('organization fields reuse shared constraints and cargo names require letters', () => {
    const re = new RegExp('^(?:' + validationProps('nombre_cargo').pattern + ')$', 'v');
    for (const name of ['Secretaría', 'Director de Carrera', 'Coordinador Académico', 'Docente 2']) assert.ok(re.test(name));
    for (const name of ['123456', '@@', '']) assert.equal(re.test(name), false);
    assert.equal(validationProps('nombres').pattern, validationProps('nombres_post').pattern);
    assert.equal(validationProps('ci').pattern, validationProps('ci_post').pattern);
    assert.equal(validationProps('celular').pattern, validationProps('celular_post').pattern);
    assert.equal(validationProps('correo_contacto').type, 'email');
});

test('HTML v-mode patterns accept Unicode names and reject digits/symbols', () => {
    const re = new RegExp('^(?:' + validationProps('nombres_post').pattern + ')$', 'v');
    for (const name of ['Vicente', 'José Luis', 'María José', 'Ana-María', "O'Connor", 'Muñoz', 'Álvarez', 'Peña', 'D’Angelo', ' José   Ángel ']) assert.ok(re.test(name), name);
    for (const name of ['Vicente123', '123456', '@Vicente', 'Vicente!!!']) assert.equal(re.test(name), false, name);
});
test('phone, CI and code patterns compile and accept their intended presentation', () => {
    for (const [field, values] of Object.entries({ celular_post: ['+591 (777)-12345', '77712345'], ci_post: ['1234567-1A LP', 'AB12345'], codigo_prog: ['fis-001', 'AB_12'] })) {
        const re = new RegExp('^(?:' + validationProps(field).pattern + ')$', 'v');
        for (const value of values) assert.ok(re.test(value), field + ': ' + value);
        assert.equal(re.test('@@@@'), false);
    }
});
test('integer, money, percent and dynamic academic-year limits match backend boundaries', () => {
    assert.equal(validationProps('fecha_nacimiento_post').maxLength, 10);
    assert.equal(validationProps('gestion_post').max, new Date().getFullYear() + 1);
    assert.equal(validationProps('capacidad_grupo').max, 20);
    assert.equal(validationProps('duracion_minutos_plan').max, 480);
    assert.equal(validationProps('monto_cuota').step, 0.01);
    assert.equal(validationProps('monto_cuota').min, 0);
    assert.equal(validationProps('preguntas.0.puntaje_pp').max, 100);
    assert.equal(validationProps('email_post').maxLength, 254);
    assert.equal(validationProps('observaciones_post').maxLength, 2000);
    assert.equal(inputConstraints.nombre_tem.minLength, 2);
});
test('optional and context-dependent fields remain optional unless explicitly required', () => {
    assert.equal(validationProps('ci_post').required, undefined);
    assert.equal(validationProps('id_grupo').required, undefined);
    assert.equal(validationProps('id_grupo', { required: true }).required, true);
    assert.equal(validationProps('password', { required: false }).required, false);
});

test('new Personal and Tutor inputs expose the strict capture constraints', () => {
    for (const field of ['nombres', 'apellidos', 'ci', 'celular', 'correo_contacto']) {
        assert.equal(validationProps(field).required, true, field);
    }
    assert.equal(validationProps('celular').type, 'tel');
    assert.equal(validationProps('correo_contacto').type, 'email');
    for (const field of ['especialidad_tutor', 'formacion_tutor', 'experiencia_tutor']) {
        assert.equal(validationProps(field).required, true, field);
    }
    assert.equal(validationProps('formacion_tutor').minLength, 10);
    assert.equal(validationProps('experiencia_tutor').minLength, 10);
});

test('academic planning date helper makes end dates and times strictly dependent on start', async () => {
    const { nextCivilDate, clearInvalidEndDate, nextMinute } = await import('../resources/js/lib/academicDates.js');
    assert.equal(nextCivilDate('2026-09-12'), '2026-09-13');
    assert.equal(nextCivilDate('2028-02-28'), '2028-02-29');
    assert.equal(clearInvalidEndDate('2026-09-20', '2026-09-20'), '');
    assert.equal(clearInvalidEndDate('2026-09-20', '2026-09-19'), '');
    assert.equal(clearInvalidEndDate('2026-09-20', '2026-09-21'), '2026-09-21');
    assert.equal(nextMinute('08:00'), '08:01');
    assert.equal(nextMinute('23:58'), '23:59');
    assert.equal(nextMinute('23:59'), '');
});

test('administrative fact forms do not submit editable dates or generated matricula code', () => {
    const files = {
        inscripcion: fs.readFileSync(new URL('../resources/js/Pages/Institucional/Inscripciones/Index.jsx', import.meta.url), 'utf8'),
        matricula: fs.readFileSync(new URL('../resources/js/Pages/Institucional/MatriculasCuotas/Index.jsx', import.meta.url), 'utf8'),
        asistencia: fs.readFileSync(new URL('../resources/js/Pages/Institucional/Asistencia/Index.jsx', import.meta.url), 'utf8'),
    };
    assert.doesNotMatch(files.inscripcion.slice(files.inscripcion.indexOf('const emptyEnrollment'), files.inscripcion.indexOf('export default')), /fecha_inscripcion|estado_inscripcion/);
    assert.doesNotMatch(files.matricula.slice(files.matricula.indexOf('const emptyMatricula'), files.matricula.indexOf('const emptyCuota')), /codigo_mat|fecha_matricula_mat/);
    assert.doesNotMatch(files.asistencia.slice(files.asistencia.indexOf('const emptyAttendance'), files.asistencia.indexOf('const localToday')), /fecha_asist/);
    assert.match(files.inscripcion, /Asignada automáticamente por el servidor/);
    assert.match(files.matricula, /Se genera automáticamente/);
    assert.match(files.asistencia, /Asignada automáticamente por el servidor/);
});

test('group and catalog UIs use controlled values and permission-gated materia actions', () => {
    const groups = fs.readFileSync(new URL('../resources/js/Pages/Institucional/Grupos/Index.jsx', import.meta.url), 'utf8');
    assert.match(groups, /\['Mañana', 'Tarde', 'Noche', 'Fin de Semana'\]/);
    assert.match(groups, /<option value="Preuniversitario">/);
    assert.match(groups, /id_tutor_responsable/);
    assert.match(groups, /max:.*20/);
    assert.match(groups, /codigo_grupo.*required:/);
    const materias = fs.readFileSync(new URL('../resources/js/Pages/Catalogos/Index.jsx', import.meta.url), 'utf8');
    for (const ability of ['permisos.crear', 'permisos.editar', 'permisos.cambiarEstado']) assert.match(materias, new RegExp(ability.replace('.', '\\.')));
    const applicants = fs.readFileSync(new URL('../resources/js/Components/Postulantes/PostulanteForm.jsx', import.meta.url), 'utf8');
    for (const label of ['Otro colegio', 'Otra universidad', 'Otra carrera']) assert.match(applicants, new RegExp(label));
    const simulations = fs.readFileSync(new URL('../resources/js/Pages/Institucional/Simulacros/Index.jsx', import.meta.url), 'utf8');
    for (const field of ['fecha_sim', 'hora_inicio_sim', 'hora_fin_sim']) assert.match(simulations, new RegExp(`${field}', \\{ required:`));
    const assignments = fs.readFileSync(new URL('../resources/js/Pages/Institucional/AsignacionTutores/Index.jsx', import.meta.url), 'utf8');
    for (const field of ['fecha_inicio_asig', 'fecha_fin_asig']) assert.match(assignments, new RegExp(`${field}', \\{ required:`));
});

test('group native constraints stay strict for new values without blocking unchanged legacy values', () => {
    const aulaConstraints = { maxLength: 80, inputMode: 'numeric', pattern: '[0-9]+' };
    const capacidadConstraints = { inputMode: 'numeric', min: 1, max: 20, step: 1 };
    const aulaLegacy = validationProps('aula_grupo', {
        required: true,
        ...constraintsWhenChanged('Aula 101', 'Aula 101', aulaConstraints),
    });
    const laboratorioLegacy = validationProps('aula_grupo', {
        required: true,
        ...constraintsWhenChanged('Laboratorio 1', 'Laboratorio 1', aulaConstraints),
    });
    const capacidadLegacy = validationProps('capacidad_grupo', {
        required: true,
        ...constraintsWhenChanged(30, '30', capacidadConstraints),
    });

    for (const props of [aulaLegacy, laboratorioLegacy]) {
        assert.equal(props.pattern, undefined);
        assert.equal(props.maxLength, undefined);
    }
    for (const constraint of ['min', 'max', 'step']) {
        assert.equal(capacidadLegacy[constraint], undefined);
    }

    const aulaNueva = validationProps('aula_grupo', {
        required: true,
        ...constraintsWhenChanged(undefined, '', aulaConstraints),
    });
    const capacidadModificada = validationProps('capacidad_grupo', {
        required: true,
        ...constraintsWhenChanged(30, '21', capacidadConstraints),
    });
    assert.equal(aulaNueva.pattern, '[0-9]+');
    assert.equal(capacidadModificada.min, 1);
    assert.equal(capacidadModificada.max, 20);
    assert.equal(capacidadModificada.step, 1);
});

test('postulante UI requires a career whenever a university is indicated', () => {
    const applicants = fs.readFileSync(new URL('../resources/js/Components/Postulantes/PostulanteForm.jsx', import.meta.url), 'utf8');
    assert.match(applicants, /const universidadIndicada = Boolean\(data\.id_uni \|\| data\.crear_otra_universidad\)/);
    assert.match(applicants, /validationProps\('id_car', \{ required: universidadIndicada \}\)/);
    assert.match(applicants, /disabled=\{!universidadIndicada\}/);
    assert.match(applicants, /Carrera postulada\{universidadIndicada \? ' \*' : ''\}/);
});

test('simulacro UI compares null and empty temporal values semantically', () => {
    const simulations = fs.readFileSync(new URL('../resources/js/Pages/Institucional/Simulacros/Index.jsx', import.meta.url), 'utf8');
    assert.match(simulations, /const timeInputValue = \(value\) => value\?\.slice\(0, 5\) \|\| ''/);
    assert.equal((simulations.match(/required: reprogramming/g) || []).length, 3);
});

test('date-only formatter round-trips Latin American dates without timezone conversion', async () => {
    const { formatDateLatam, parseDateLatam } = await import('../resources/js/lib/dateOnly.js');
    for (const [ui, iso] of [['15/08/2005', '2005-08-15'], ['01/01/2000', '2000-01-01'], ['29/02/2008', '2008-02-29'], ['29/02/2012', '2012-02-29'], ['07/09/2005', '2005-09-07']]) {
        assert.equal(parseDateLatam(ui), iso);
        assert.equal(formatDateLatam(iso), ui);
        assert.equal(formatDateLatam(parseDateLatam(ui)), ui);
    }
    assert.equal(formatDateLatam(null), '');
    assert.equal(parseDateLatam(''), null);
});

test('date-only utilities reject impossible dates, ambiguous formats and timestamps', async () => {
    const { formatDateLatam, parseDateLatam } = await import('../resources/js/lib/dateOnly.js');
    for (const input of ['31/02/2005', '29/02/2005', '29/02/2100', '31/04/2005', '32/01/2005', '00/12/2005', '15/13/2005', '15/00/2005', '15/08/0000', '2005/08/15', '08-15-2005', '15-08-2005', '2005-08-15', null, true, ['15/08/2005']]) {
        assert.equal(parseDateLatam(input), null, String(input));
    }
    for (const input of ['2005-02-29', '2100-02-29', '2005-08-15T00:00:00Z', '15/08/2005', '0000-01-01']) assert.equal(formatDateLatam(input), '');
    const pattern = new RegExp('^(?:' + validationProps('fecha_nacimiento_post').pattern + ')$', 'v');
    assert.ok(pattern.test('15/08/2005'));
    assert.equal(pattern.test('08-15-2005'), false);
    assert.equal(pattern.test('2005-08-15'), false);
});

test('postulante form uses the shared formatter and never submits editable age', () => {
    const form = fs.readFileSync(new URL('../resources/js/Components/Postulantes/PostulanteForm.jsx', import.meta.url), 'utf8');
    assert.ok(form.includes('fecha_nacimiento_post: formatDateLatam(postulante?.fecha_nacimiento_post)'));
    assert.ok(form.includes('Formato: DD/MM/AAAA'));
    assert.ok(form.includes('Fecha de nacimiento pendiente de completar'));
    assert.ok(form.includes('aria-describedby="fecha_nacimiento_ayuda fecha_nacimiento_error"'));
    assert.equal(form.includes('edad_post:'), false);
    assert.equal(form.includes('type="date"'), false);
});

test('matricula editing builds an explicit payload without derived academic ids', () => {
    const form = fs.readFileSync(
        new URL('../resources/js/Pages/Institucional/MatriculasCuotas/Index.jsx', import.meta.url),
        'utf8',
    );
    const editor = form.slice(form.indexOf('const openMatricula'), form.indexOf('const openCuota'));

    assert.doesNotMatch(editor, /\.\.\.matricula/);
    assert.doesNotMatch(editor, /id_post|id_prog|id_grupo/);
    assert.doesNotMatch(editor, /codigo_mat|fecha_matricula_mat/);
    for (const field of [
        'id_insc',
        'monto_matricula_mat',
        'estado_matricula_mat',
        'tipo_beneficio_mat',
        'observacion_mat',
    ]) {
        assert.match(editor, new RegExp(`${field}:`));
    }
});

test('planning editors copy only writable fields instead of spreading server resources', () => {
    for (const [path, forbidden] of [
        ['../resources/js/Pages/Institucional/Programas/Index.jsx', /\.\.\.programa/],
        ['../resources/js/Pages/Institucional/AsignacionTutores/Index.jsx', /\.\.\.asignacion/],
    ]) {
        const form = fs.readFileSync(new URL(path, import.meta.url), 'utf8');
        assert.doesNotMatch(form, forbidden);
    }
});
