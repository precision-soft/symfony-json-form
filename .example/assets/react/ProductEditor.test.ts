/*
 * Copyright (c) Precision Soft
 */

import assert from 'node:assert/strict';
import {describe, it} from 'node:test';
import {computeInitialValues} from '../../../assets/react/service/Element.ts';
import productEditForm from '../../tests/Fixture/product-edit-form.json' with {type: 'json'};

describe('the product editor', () => {
    it('starts from the values the backend rendered', () => {
        const values = computeInitialValues(productEditForm.elements);

        assert.equal(values.id, 7);
        assert.equal(values.name, 'Desk lamp');
        assert.equal(values.price, 149.9);
        assert.equal(values.currency, 'RON');
        assert.deepEqual(values.channels, ['web', 'store']);
        assert.deepEqual(values.categoryId, [3]);
        assert.equal(values.active, true);
        assert.equal(values.availableFrom, '2026-09-01');
        assert.equal(values.publishedAt, '2026-08-30 14:25');
        assert.deepEqual(values.dimensions, {width: 40, height: 55, depth: 20});
        assert.equal(values.prices.length, 2);
        assert.equal(values.prices[0].currency, 'EUR');
        assert.equal(values.prices[0].amount, 30.5);
        assert.equal(values.image, '');
    });

    it('posts to the route the backend declared for this product', () => {
        assert.equal(productEditForm.method, 'POST');
        assert.deepEqual(productEditForm.action, {route: 'catalogue-product-edit', parameters: {id: 7}});
    });
});
