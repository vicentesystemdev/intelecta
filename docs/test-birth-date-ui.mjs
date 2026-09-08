import assert from 'node:assert/strict';
import fs from 'node:fs';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const browser = await chromium.connectOverCDP('http://127.0.0.1:9223');
const context = await browser.newContext({ viewport: { width: 1440, height: 1120 }, locale: 'en-US', timezoneId: 'America/La_Paz' });
const page = await context.newPage();
const base = 'http://127.0.0.1:8017';
const output = new URL('./evidencia-fecha-nacimiento/', import.meta.url);
fs.mkdirSync(output, { recursive: true });
const errors = [];
page.on('pageerror', error => errors.push(error.message));
let assertions = 0;
function check(condition, message) {
    assertions++;
    assert.ok(condition, message);
}
async function screenshot(name) {
    await page.screenshot({ path: new URL(name + '.png', output).pathname.replace(/^\/(.:)/, '$1'), fullPage: true });
}
try {
    await page.goto(base + '/login');
    await page.locator('#email').fill('marco.torrez@avalancha.edu.bo');
    await page.locator('#password').fill('Avalancha#2026');
    await page.getByRole('button', { name: 'Ingresar al sistema' }).click();
    await page.waitForURL('**/dashboard');

    await page.goto(base + '/postulantes/crear');
    const date = page.locator('#fecha_nacimiento_post');
    await date.waitFor();
    await page.locator('#nombres_post').fill('José Luis');
    await page.locator('#apellidos_post').fill('Calendario Prueba');
    await date.fill('15/08/2005');
    check(await date.inputValue() === '15/08/2005', 'CREATE must display DD/MM/AAAA even in en-US.');
    check(await date.getAttribute('type') === 'text', 'Must not use locale-dependent native date input.');
    check(await date.getAttribute('required') !== null, 'CREATE requires a birth date.');
    check(await page.getByText('Formato: DD/MM/AAAA', { exact: true }).isVisible(), 'Visible format help.');
    check(await page.locator('input[name="edad_post"]').count() === 0, 'No editable age.');
    await screenshot('01-crear');

    await date.fill('31/02/2005');
    await page.getByRole('button', { name: 'Registrar postulante', exact: true }).click();
    await page.getByText('La fecha de nacimiento no es válida: ese día no existe en el calendario.', { exact: true }).waitFor();
    check(await date.getAttribute('aria-invalid') === 'true', 'Accessible invalid calendar error.');
    await screenshot('02-fecha-invalida');
    await date.fill('15/08/2005');
    const sent = page.waitForRequest(request => request.method() === 'POST' && request.url() === base + '/postulantes');
    await page.getByRole('button', { name: 'Registrar postulante', exact: true }).click();
    const payload = (await sent).postDataJSON();
    check(payload.fecha_nacimiento_post === '15/08/2005', 'UI sends the Latin American date for Laravel normalization.');
    check(!Object.hasOwn(payload, 'edad_post'), 'UI does not send manual age.');
    await page.waitForURL(base + '/postulantes');

    await page.goto(base + '/postulantes/2/editar');
    await date.waitFor();
    check(await date.inputValue() === '15/08/2005', 'EDIT preloads Latin American date, not ISO or US.');
    await screenshot('03-editar-fecha');
    await page.goto(base + '/postulantes/2');
    await page.getByText('15/08/2005', { exact: true }).waitFor();
    check(await page.getByText('21', { exact: true }).isVisible(), 'Server-calculated age appears in details.');

    await page.goto(base + '/postulantes/1/editar');
    await date.waitFor();
    check(await date.inputValue() === '', 'Legacy birth date stays empty.');
    check(await date.getAttribute('required') === null, 'Legacy unrelated updates do not require missing date.');
    check(await page.getByText('Fecha de nacimiento pendiente de completar', { exact: true }).isVisible(), 'Legacy pending notice visible.');
    await screenshot('04-legacy-pendiente');
    await page.locator('#observaciones_post').fill('Edición de prueba sin inventar fecha.');
    await page.getByRole('button', { name: 'Guardar cambios' }).click();
    await page.waitForURL(base + '/postulantes');
    await page.goto(base + '/postulantes/1/editar');
    await date.waitFor();
    check(await date.inputValue() === '', 'Legacy unrelated edit preserved NULL.');
    check(await page.locator('#observaciones_post').inputValue() === 'Edición de prueba sin inventar fecha.', 'Legacy unrelated edit persisted.');
    await date.fill('01/01/2000');
    await page.getByRole('button', { name: 'Guardar cambios' }).click();
    await page.waitForURL(base + '/postulantes');
    await page.goto(base + '/postulantes/1/editar');
    await date.waitFor();
    check(await date.inputValue() === '01/01/2000', 'Legacy can later receive the real date.');
    check(errors.length === 0, 'No browser JavaScript errors: ' + errors.join('; '));
    const result = { passed: assertions, failed: 0, assertions, browser: 'Chrome headless', locale: 'en-US', timezone: 'America/La_Paz', database: 'isolated SQLite', postgres_modified: false, scenarios: ['create', 'invalid calendar', 'edit preloaded date', 'legacy pending', 'legacy unrelated edit', 'legacy completion'], browser_errors: errors };
    fs.writeFileSync(new URL('resultado.json', output), JSON.stringify(result, null, 2) + '\n');
    console.log(JSON.stringify(result));
} catch (error) {
    await screenshot('error');
    console.error((await page.locator('body').innerText()).slice(0, 2200));
    throw error;
} finally {
    await context.close();
    await browser.close();
}
