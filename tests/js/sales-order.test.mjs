import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';
import ts from 'typescript';

const source = await readFile(new URL('../../resources/js/lib/sales.ts', import.meta.url), 'utf8');
const compiled = ts.transpileModule(source, { compilerOptions: { module: ts.ModuleKind.ESNext } }).outputText;
const { totalQuantity } = await import(`data:text/javascript,${encodeURIComponent(compiled)}`);

test('quantities from JSON are added numerically, including decimal strings', () => {
    assert.equal(totalQuantity([{ quantity: '2' }, { quantity: '3.5' }, { quantity: 1 }]), 6.5);
    assert.equal(totalQuantity([]), 0);
});
