import assert from 'node:assert/strict';
import { test } from 'node:test';
import { pageTitle } from '../../resources/js/lib/pageTitle.ts';

test('appends the app name when it fits in 60 characters', () => {
    assert.equal(pageTitle('Harga Fabriku', 'Fabriku'), 'Harga Fabriku');
    assert.equal(pageTitle('Cara Hitung HPP', 'Fabriku'), 'Cara Hitung HPP - Fabriku');
});

test('skips the suffix when it would pass 60 characters', () => {
    const title = 'Cara Menentukan Harga Jual Makanan per Pcs dan Komisi'; // 53 chars
    assert.equal(pageTitle(title, 'Fabriku'), title);
});

test('falls back to the app name for an empty title', () => {
    assert.equal(pageTitle(undefined, 'Fabriku'), 'Fabriku');
    assert.equal(pageTitle('', 'Fabriku'), 'Fabriku');
});
