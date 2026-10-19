import assert from 'node:assert/strict';
import test from 'node:test';
import { crearRelojCat } from '../resources/js/Utils/relojCat.mjs';

test('zero is reached at the actual deadline, never in the preceding fraction of a second', () => {
    let monotono = 0;
    const restante = crearRelojCat('2026-10-19T17:51:02Z', '2026-10-19T17:51:01.500Z', () => monotono);
    assert.equal(restante(), 1);
    monotono = 499;
    assert.equal(restante(), 1);
    monotono = 500;
    assert.equal(restante(), 0);
    monotono = 1000;
    assert.equal(restante(), 0);
});

for (const zone of ['UTC', 'America/La_Paz']) {
    test(`30 minutes and reload keep the same deadline in ${zone}`, () => {
        const previous = process.env.TZ;
        process.env.TZ = zone;
        try {
            let monotono = 1000;
            const restante = crearRelojCat('2026-10-19T17:51:02.000Z', '2026-10-19T17:21:11.000Z', () => monotono);
            assert.equal(restante(), 1791); // 29:51, nunca 269:51
            monotono += 60000;
            assert.equal(restante(), 1731);
            const recargado = crearRelojCat('2026-10-19T13:51:02-04:00', '2026-10-19T17:22:11Z', () => monotono);
            assert.equal(recargado(), 1731);
            monotono += 1731000;
            assert.equal(restante(), 0);
            assert.equal(recargado(), 0);
        } finally {
            if (previous === undefined) delete process.env.TZ;
            else process.env.TZ = previous;
        }
    });
}

test('invalid or ambiguous dates fail closed, and expired deadlines remain zero', () => {
    for (const [fin, ahora] of [
        ['2026-10-19T17:51:02', '2026-10-19T17:21:11Z'],
        ['badZ', '2026-10-19T17:21:11Z'],
        [null, null],
        ['2026-10-19T17:51:02Z', '2026-10-19T17:51:03Z'],
    ]) assert.equal(crearRelojCat(fin, ahora, () => 0)(), 0);
});

test('client wall clock changes do not increase time', () => {
    let monotono = 0;
    const restante = crearRelojCat('2026-10-19T17:51:02Z', '2026-10-19T17:21:02Z', () => monotono);
    const originalNow = Date.now;
    try {
        Date.now = () => 0;
        monotono = 10000;
        assert.equal(restante(), 1790);
        Date.now = () => 9999999999999;
        monotono = 20000;
        assert.equal(restante(), 1780);
    } finally {
        Date.now = originalNow;
    }
});
