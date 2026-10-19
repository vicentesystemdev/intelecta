import assert from 'node:assert/strict';
import fs from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';
import { transformWithOxc } from 'vite';

// Execute the actual page function and all JSX expressions, including closed
// modal children. Visual dependencies and Inertia hooks are isolated in memory.
const source = fs.readFileSync(new URL('../resources/js/Pages/Institucional/MatriculasCuotas/Index.jsx', import.meta.url), 'utf8');
const imports = [...source.matchAll(/^import\s+([\s\S]*?)\s+from\s+['"][^'"]+['"];$/gm)];
const context = {};
for (const [, bindings] of imports) {
    for (const name of bindings.replace(/[{}]/g, '').split(',').map(value => value.trim()).filter(Boolean)) {
        context[name] = name;
    }
}
Object.assign(context, {
    React: { createElement: (type, props, ...children) => ({ type, props: props ?? {}, children }) },
    usePage: () => ({ props: { flash: {} } }),
    useState: initial => [initial, () => {}],
    useMemo: callback => callback(),
    useForm: data => ({ data, errors: {}, processing: false }),
    validationProps: () => ({}),
    formatDateLatam: value => value,
    route: name => name,
});
const executable = source.replace(/^import\s+[\s\S]*?\s+from\s+['"][^'"]+['"];\s*/gm, '')
    .replace('export default function Index', 'function Index');
const { code } = await transformWithOxc(executable, 'MatriculasCuotas.jsx', { jsx: { runtime: 'classic' } });
vm.createContext(context);
vm.runInContext(code, context);

function nodes(tree) {
    if (Array.isArray(tree)) return tree.flatMap(nodes);
    if (!tree || typeof tree !== 'object') return [];
    return [tree, ...nodes(tree.children)];
}

for (const [name, data] of [
    ['empty list', []],
    ['matricula with pending and overdue cuotas', [{
        id_mat: 1, id_insc: 1, codigo_mat: 'QA-001', estado_matricula_mat: 'activa',
        postulante: { nombres_post: 'Estudiante', apellidos_post: 'De prueba' },
        cuotas: [
            { id_cuota: 1, estado_cuota: 'pendiente', monto_cuota: '100.00' },
            { id_cuota: 2, estado_cuota: 'vencida', monto_cuota: '50.00' },
        ],
    }]],
]) {
    test(`page renders ${name} and payment date uses the server's Bolivia date`, () => {
        const tree = context.Index({ matriculas: { data, links: [], total: data.length }, fechaHoy: '2026-10-19' });
        const date = nodes(tree).find(node => node.props.label === 'Fecha de pago');
        assert.ok(date, 'Payment date JSX was evaluated even with the modal closed');
        assert.equal(date.props.max, '2026-10-19');
        assert.ok(nodes(tree).some(node => node.type === 'Head' && node.props.title === 'Matrículas y Cuotas'));
    });
}

test('editing a cuota normalizes nullable optional inputs without losing amounts or dates', () => {
    let formData;
    context.useForm = data => ({ data, errors: {}, clearErrors() {}, setData(value) { formData = value; } });
    const tree = context.Index({
        fechaHoy: '2026-10-19',
        matriculas: { links: [], data: [{
            id_mat: 1, cuotas: [{
                id_cuota: 2, id_mat: 1, nro_cuota: null, concepto_cuota: null,
                monto_cuota: '150.50', estado_cuota: 'vencida',
                fecha_vencimiento_cuota: '2026-10-18T00:00:00.000000Z', fecha_pago_cuota: null,
                metodo_pago_cuota: null, observacion_cuota: null,
            }],
        }] },
    });
    nodes(tree).find(node => node.props['aria-label'] === 'Editar cuota').props.onClick();
    for (const [field, value] of Object.entries(formData)) {
        assert.notEqual(value, null, `${field} must not turn a controlled input into a null value`);
    }
    assert.equal(formData.monto_cuota, '150.50');
    assert.equal(formData.fecha_vencimiento_cuota, '2026-10-18');
    assert.equal(formData.fecha_pago_cuota, '');
    assert.equal(formData.id_mat, '1');
});
