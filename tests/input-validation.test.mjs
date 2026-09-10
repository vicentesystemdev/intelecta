import assert from 'node:assert/strict';
import fs from 'node:fs';
import test from 'node:test';

// Vite's JSON import is replaced in memory only; production source is never rewritten.
const source = fs.readFileSync(new URL('../resources/js/lib/inputValidation.js', import.meta.url), 'utf8')
    .replace(/^import options.*$/m, 'const options = {};');
const { validationProps, inputConstraints } = await import('data:text/javascript;base64,' + Buffer.from(source).toString('base64'));

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
    assert.equal(validationProps('capacidad_grupo').max, 500);
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
