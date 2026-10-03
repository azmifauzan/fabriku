import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';

const applicationScript = await readFile(new URL('../../resources/js/app.ts', import.meta.url), 'utf8');

test('axios does not pin the initial CSRF token', () => {
    assert.doesNotMatch(applicationScript, /axios\.defaults\.headers\.common\[['"]X-CSRF-TOKEN['"]\]/);
    assert.match(applicationScript, /axios\.defaults\.headers\.common\[['"]X-Requested-With['"]\]/);
});
