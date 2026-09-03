import {Box} from '@mui/material';
import {FastField} from 'formik';
import type {FormikValues} from 'formik';
import React from 'react';
import {computeInitialValues, createPrototypeCollectionElementValues, requireElementProperty} from '../service/Element';
import {clone} from '../service/Utility';
import type {StringArrayType} from '../type/Array';
import type {MapType} from '../type/Map';
import type {StringNumberType} from '../type/Scalar';
import {FormFields} from './FormField';
import type {ElementListType, ElementType, FormCallbacksType, FormFieldCallbacksType, FormFieldRenderPropsType, FormFieldValueType, FormType, PrototypeCollectionType} from './Types';

export class FormBuilder {
    static computeDateFormat = (elementFormat: string): string => {
        let format = 'YYYY-MM-DD';

        switch (elementFormat) {
            case 'd-m-Y':
                format = 'DD-MM-YYYY';
                break;
        }

        return format;
    };

    static computeDateTimeFormat = (elementFormat: string): string => {
        let format = 'YYYY-MM-DD HH:mm';

        switch (elementFormat) {
            case 'd-m-Y H:i':
                format = 'DD-MM-YYYY HH:mm';
                break;
        }

        return format;
    };

    static computeInitialValues = (elements: ElementListType): MapType => {
        return computeInitialValues(elements);
    };

    static initCallbacks = (callbacks?: FormCallbacksType): void => {
        if (undefined === callbacks) {
            return;
        }

        callbacks.elements = {};
    };

    static createPrototypeCollectionElementValues = (keyName: string, keyValue: StringNumberType, values: MapType): MapType => {
        return createPrototypeCollectionElementValues(keyName, keyValue, values);
    };

    static renderPrototypeCollection = (
        name: string,
        elementClassName: string,
        values: FormFieldValueType,
        form: FormType,
        element: ElementType,
        parents: StringArrayType,
        renderProps: FormFieldRenderPropsType | undefined,
        callbacks: FormFieldCallbacksType | undefined
    ) => {
        const collectionElements: PrototypeCollectionType[] = [];
        values.map((itemData, index) => collectionElements.push({
            key: itemData[requireElementProperty(element, 'key')],
            parents: [...parents, element.name, index]
        }));

        const elements = clone<ElementListType>(requireElementProperty(element, 'prototype'));

        if (undefined !== renderProps?.prototypeCollectionRender) {
            collectionElements.map((collectionElement) => {
                collectionElement.values = FormBuilder.getNestedValuesByPath(form.values, collectionElement.parents);
            });

            return renderProps.prototypeCollectionRender(
                collectionElements,
                elements,
                form,
                renderProps,
                callbacks
            );
        }

        return (
            <Box key={name + 'PrototypeCollection'} className={'d-flex flex-column gap-1 w-100' + elementClassName}>
                {collectionElements.map((collectionElement) => (
                    <Box key={collectionElement.key} className="d-flex align-items-center gap-1">
                        <FastField name={name + collectionElement.key}>
                            {() => (
                                <FormFields form={form}
                                            elements={elements}
                                            parents={collectionElement.parents}
                                            renderProps={renderProps?.elements}
                                            callbacks={callbacks?.elements}
                                />
                            )}
                        </FastField>
                    </Box>
                ))}
            </Box>
        );
    };

    static sliceElements = (elements: ElementListType, names: StringArrayType): ElementListType => {
        const slice: ElementListType = {};

        names.map((name) => {
            const element = elements[name];

            slice[element.name] = element;

            delete elements[name];
        });

        return slice;
    };

    static getNestedValuesByPath = (values: FormikValues, path?: StringArrayType): FormikValues => {
        if (undefined === path || 0 === path.length) {
            return values;
        }

        let result: FormikValues = values;

        for (let i = 0; i < path.length; i++) {
            if (undefined === result) {
                break;
            }

            result = result[path[i]];
        }

        return result;
    };
}
