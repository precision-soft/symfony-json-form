import assert from 'node:assert/strict';
import {describe, it} from 'node:test';
import {clone} from './Utility.ts';

describe('clone', () => {
    it('returns a structurally equal deep copy', () => {
        const source = {name: 'precision', nested: {values: [1, 2, 3]}};

        const copy = clone(source);

        assert.deepEqual(copy, source);
    });

    it('does not share nested references with the source', () => {
        const source = {nested: {values: [1, 2, 3]}};

        const copy = clone(source);
        copy.nested.values.push(4);

        assert.deepEqual(source.nested.values, [1, 2, 3]);
    });

    it('clones primitives unchanged', () => {
        assert.equal(clone(5), 5);
        assert.equal(clone('text'), 'text');
        assert.equal(clone(null), null);
    });

    it('falls back to JSON cloning when structuredClone is unavailable', () => {
        const originalStructuredClone = globalThis.structuredClone;
        // @ts-expect-error force the JSON fallback branch
        globalThis.structuredClone = undefined;

        try {
            const source = {nested: {value: 'x'}};

            const copy = clone(source);

            assert.deepEqual(copy, source);
            assert.notEqual(copy.nested, source.nested);
        } finally {
            globalThis.structuredClone = originalStructuredClone;
        }
    });
});
