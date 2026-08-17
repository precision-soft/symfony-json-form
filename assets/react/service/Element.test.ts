import assert from 'node:assert/strict';
import {describe, it} from 'node:test';
import {computeInitialValues, createPrototypeCollectionElementValues, requireElementProperty} from './Element.ts';

describe('requireElementProperty', () => {
    it('returns the value when the property is present', () => {
        const element = {type: 'array', name: 'status', options: {1: 'one'}};

        assert.deepEqual(requireElementProperty(element, 'options'), {1: 'one'});
    });

    it('returns falsy values that are not null or undefined', () => {
        const element = {type: 'number', name: 'quantity', min: 0, format: ''};

        assert.equal(requireElementProperty(element, 'min'), 0);
        assert.equal(requireElementProperty(element, 'format'), '');
    });

    it('names the element and the property when the value is missing', () => {
        const element = {type: 'prototypeCollection', name: 'rows', key: undefined};

        assert.throws(
            () => requireElementProperty(element, 'key'),
            /the "prototypeCollection" element "rows" is missing "key"/
        );
    });

    it('rejects a null value as well as an absent one', () => {
        const element = {type: 'autocomplete', name: 'city', route: null};

        assert.throws(
            () => requireElementProperty(element, 'route'),
            /the "autocomplete" element "city" is missing "route"/
        );
    });
});

describe('createPrototypeCollectionElementValues', () => {
    it('puts the collection key beside the row values', () => {
        assert.deepEqual(
            createPrototypeCollectionElementValues('rowId', 7, {label: 'first'}),
            {rowId: 7, label: 'first'}
        );
    });

    it('lets a row value of the same name win over the key', () => {
        assert.deepEqual(
            createPrototypeCollectionElementValues('rowId', 7, {rowId: 9}),
            {rowId: 9}
        );
    });
});

describe('computeInitialValues', () => {
    it('takes the first selected value for a single mode array and null when nothing is selected', () => {
        const elements = {
            picked: {type: 'array', name: 'picked', label: null, mode: 'single', value: ['b', 'c']},
            empty: {type: 'array', name: 'empty', label: null, mode: 'single', value: []},
            unset: {type: 'array', name: 'unset', label: null, mode: 'single', value: null}
        };

        assert.deepEqual(computeInitialValues(elements), {picked: 'b', empty: null, unset: null});
    });

    it('does not throw when a single mode array carries no value key at all', () => {
        const elements = {picked: {type: 'array', name: 'picked', label: null, mode: 'single'}};

        assert.deepEqual(computeInitialValues(elements), {picked: null});
    });

    it('keeps a multiple mode array as an array', () => {
        const elements = {
            picked: {type: 'array', name: 'picked', label: null, mode: 'multiple', value: ['b', 'c']},
            unset: {type: 'array', name: 'unset', label: null, mode: 'multiple', value: null}
        };

        assert.deepEqual(computeInitialValues(elements), {picked: ['b', 'c'], unset: []});
    });

    it('defaults a bool to false and every other type to an empty string', () => {
        const elements = {
            flag: {type: 'bool', name: 'flag', label: null, value: null},
            set: {type: 'bool', name: 'set', label: null, value: true},
            text: {type: 'string', name: 'text', label: null, value: null},
            date: {type: 'date', name: 'date', label: null, value: '2021-02-28'}
        };

        assert.deepEqual(
            computeInitialValues(elements),
            {flag: false, set: true, text: '', date: '2021-02-28'}
        );
    });

    it('keeps a false bool rather than defaulting it', () => {
        const elements = {flag: {type: 'bool', name: 'flag', label: null, value: false}};

        assert.deepEqual(computeInitialValues(elements), {flag: false});
    });

    it('recurses into a collection', () => {
        const elements = {
            group: {
                type: 'collection',
                name: 'group',
                label: null,
                elements: {
                    text: {type: 'string', name: 'text', label: null, value: 'inner'},
                    flag: {type: 'bool', name: 'flag', label: null, value: null}
                }
            }
        };

        assert.deepEqual(computeInitialValues(elements), {group: {text: 'inner', flag: false}});
    });

    it('builds one keyed row per prototype collection entry', () => {
        const elements = {
            rows: {
                type: 'prototypeCollection',
                name: 'rows',
                label: null,
                key: 'rowId',
                elements: {
                    7: {text: {type: 'string', name: 'text', label: null, value: 'first'}},
                    9: {text: {type: 'string', name: 'text', label: null, value: 'second'}}
                }
            }
        };

        assert.deepEqual(
            computeInitialValues(elements),
            {rows: [{rowId: '7', text: 'first'}, {rowId: '9', text: 'second'}]}
        );
    });

    it('reports the element by name when a prototype collection with rows has no key', () => {
        const elements = {
            rows: {
                type: 'prototypeCollection',
                name: 'rows',
                label: null,
                elements: {7: {text: {type: 'string', name: 'text', label: null, value: 'first'}}}
            }
        };

        assert.throws(
            () => computeInitialValues(elements),
            /the "prototypeCollection" element "rows" is missing "key"/
        );
    });

    it('needs no key for a prototype collection with no rows', () => {
        const elements = {
            rows: {type: 'prototypeCollection', name: 'rows', label: null, elements: {}}
        };

        assert.deepEqual(computeInitialValues(elements), {rows: []});
    });
});
