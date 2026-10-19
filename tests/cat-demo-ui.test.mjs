import assert from 'node:assert/strict';
import fs from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';
import { transformWithOxc } from 'vite';
import { crearRelojCat } from '../resources/js/Utils/relojCat.mjs';

// Ejecuta el componente real, sus efectos y eventos; reloj y transporte controlados.
const source = fs.readFileSync(new URL('../resources/js/Pages/Estudiante/CatDemo.jsx', import.meta.url), 'utf8')
    .replace(/^import\s+[\s\S]*?\s+from\s+['"][^'"]+['"];\s*/gm, '')
    .replace('export default function CatDemo', 'function CatDemo');
const { code } = await transformWithOxc(source, 'CatDemo.jsx', { jsx: { runtime: 'classic' } });

function nodes(tree) {
    if (Array.isArray(tree)) return tree.flatMap(nodes);
    if (!tree || typeof tree !== 'object') return [];
    return [tree, ...nodes(tree.children)];
}

function page(overrides = {}) {
    let time = 0, cursor = 0, dirty = false, tree;
    const hooks = [], effects = [], intervals = new Map(), reloads = [], posts = [];
    let props = { disponible: true, acceso: { permitido: true }, intento: {
        id_intento_cat: 42, nombre: 'Matemática CAT DEMO', estado: 'en_progreso', revision: 0,
        calculo_pendiente: false, maximum: 10, segundos_restantes: 0,
        vence_en: '2026-10-19T17:51:02Z', servidor_ahora: '2026-10-19T17:51:01.500Z',
        pregunta: { id_preg: 8, enunciado: 'Ejercicio', alternativas: [
            { id_alt: 36, letra: 'A', texto: 'Primera' }, { id_alt: 37, letra: 'B', texto: 'Segunda' },
        ] }, ...overrides,
    } };
    const context = {
        AuthenticatedLayout: 'layout', StudentTrackingNav: 'nav', Head: 'head', Link: 'link',
        React: { createElement: (type, attrs, ...children) => ({ type, props: attrs ?? {}, children }) },
        usePage: () => ({ props: { errors: {} } }), route: (name, id) => `${name}/${id}`,
        crearRelojCat: (fin, ahora) => crearRelojCat(fin, ahora, () => time),
        useState(initial) {
            const i = cursor++;
            if (!hooks[i]) hooks[i] = { value: typeof initial === 'function' ? initial() : initial };
            return [hooks[i].value, value => {
                if (!Object.is(value, hooks[i].value)) { hooks[i].value = value; dirty = true; }
            }];
        },
        useRef(initial) { const i = cursor++; return hooks[i] ??= { current: initial }; },
        useEffect(callback, deps) {
            const i = cursor++, old = hooks[i];
            if (!old || deps.some((value, n) => !Object.is(value, old.deps[n]))) {
                hooks[i] = { deps, cleanup: old?.cleanup };
                effects.push(() => { hooks[i].cleanup?.(); hooks[i].cleanup = callback(); });
            }
        },
        setInterval(callback) { const id = Symbol(); intervals.set(id, callback); return id; },
        clearInterval(id) { intervals.delete(id); },
        router: {
            reload(options) { reloads.push(options); options.onStart(); },
            post(url, data, options) { posts.push({ url, data, options }); options.onStart(); },
        },
    };
    vm.createContext(context);
    vm.runInContext(code, context);
    const render = () => {
        let iterations = 0;
        do {
            assert.ok(++iterations < 20, 'El componente no entra en un bucle de render');
            dirty = false; cursor = 0;
            tree = context.CatDemo(props);
            while (effects.length) effects.shift()();
        } while (dirty);
    };
    render();
    return {
        reloads, posts, render,
        nodes: () => nodes(tree), text: () => JSON.stringify(tree),
        tick(ms) { time += ms; [...intervals.values()].forEach(callback => callback()); render(); },
        server(changes) { props = { ...props, intento: { ...props.intento, ...changes } }; render(); },
    };
}

test('expiration consults Laravel once at the actual boundary and renders its real state', () => {
    const p = page();
    p.tick(499);
    assert.equal(p.reloads.length, 0);
    p.tick(1);
    assert.equal(p.reloads.length, 1);
    assert.deepEqual(Array.from(p.reloads[0].only), ['intento', 'acceso']);
    assert.equal(p.posts.length, 0);
    p.server({ estado: 'vencida', pregunta: null, revision: 1, calculo_pendiente: false });
    p.reloads[0].onFinish(); p.render(); p.tick(60000);
    assert.equal(p.reloads.length, 1);
    assert.match(p.text(), /Estado: .*vencida/);
    assert.match(p.text(), /respuestas aceptadas antes del plazo/);
    assert.equal(p.nodes().filter(node => node.type === 'input').length, 0);
});

test('failure or an unchanged server response cannot produce a reload loop', () => {
    const p = page({ servidor_ahora: '2026-10-19T17:51:03Z' });
    assert.equal(p.reloads.length, 1);
    p.reloads[0].onFinish(); p.render(); // Fin de una consulta fallida, sin props nuevos.
    p.tick(60000);
    p.server({ servidor_ahora: '2026-10-19T17:52:03Z', calculo_pendiente: true });
    p.tick(60000);
    assert.equal(p.reloads.length, 1);
    assert.equal(p.posts.length, 0);
    assert.match(p.text(), /Si el estado no se actualiza/);
});

test('expiration waits for a submitted answer, including a pending Python calculation', () => {
    const p = page();
    p.nodes().find(node => node.type === 'input').props.onChange(); p.render();
    p.nodes().find(node => node.type === 'button' && node.children.includes('Confirmar respuesta')).props.onClick();
    p.render(); p.tick(500);
    assert.equal(p.posts.length, 1);
    assert.equal(p.reloads.length, 0);
    assert.deepEqual(JSON.parse(JSON.stringify(p.posts[0].data)), { id_preg: 8, id_alt: 36, revision: 0 });
    p.server({ revision: 1, pregunta: null, calculo_pendiente: true, servidor_ahora: '2026-10-19T17:51:02Z' });
    assert.equal(p.reloads.length, 0);
    p.posts[0].options.onFinish(); p.render(); p.tick(250);
    assert.equal(p.reloads.length, 1);
    p.tick(10000);
    assert.equal(p.posts.length, 1);
    assert.equal(p.reloads.length, 1);
});

test('a final result received after submission is never replaced by an expiry query', () => {
    const p = page();
    p.nodes().find(node => node.type === 'input').props.onChange(); p.render();
    p.nodes().find(node => node.type === 'button' && node.children.includes('Confirmar respuesta')).props.onClick();
    p.tick(500);
    p.server({ estado: 'finalizada', revision: 10, pregunta: null,
        resultado_cat: { theta: 1.227, sd: 0.592, intervalo: [0, 2], motivo: 'Máximo' } });
    p.posts[0].options.onFinish(); p.render(); p.tick(60000);
    assert.equal(p.reloads.length, 0);
    assert.match(p.text(), /1\.227/);
});

test('selection changes border and background while preserving native radios and focus', () => {
    const p = page();
    const first = () => p.nodes().find(node => node.type === 'input');
    first().props.onChange(); p.render();
    const labels = p.nodes().filter(node => node.type === 'label');
    assert.match(labels[0].props.className, /border-brand-secondary bg-brand-secondary\/10/);
    assert.match(labels[1].props.className, /border-brand-border bg-brand-card/);
    assert.match(labels[0].props.className, /focus-within:ring-2/);
    assert.equal(first().props.type, 'radio');
    assert.equal(first().props.name, 'alternativa-cat');
    assert.equal(first().props.checked, true);
    const second = p.nodes().filter(node => node.type === 'input')[1];
    second.props.onChange(); p.render();
    assert.equal(first().props.checked, false);
    assert.equal(p.nodes().filter(node => node.type === 'input')[1].props.checked, true);
});
