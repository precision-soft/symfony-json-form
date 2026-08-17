export type ElementLikeType = {
    type: string
    name: string
};

export type ElementValuesType = { [name: string]: any };

/* the wire strings, not either version's enum: this module is shared by formV1 and formV2 and must couple to neither. */
const TYPE_ARRAY = 'array';
const TYPE_BOOL = 'bool';
const TYPE_COLLECTION = 'collection';
const TYPE_PROTOTYPE_COLLECTION = 'prototypeCollection';
const MODE_SINGLE = 'single';

/** a plain `Error` rather than the host's `Exception`, so this module stays importable with no dependencies. */
export const requireElementProperty = <ET extends ElementLikeType, PT extends keyof ET>(
    element: ET,
    property: PT
): NonNullable<ET[PT]> => {
    const value = element[property];

    if (undefined === value || null === value) {
        throw new Error(`the "${element.type}" element "${element.name}" is missing "${String(property)}"`);
    }

    return value as NonNullable<ET[PT]>;
};

export const createPrototypeCollectionElementValues = (
    keyName: string,
    keyValue: string | number,
    values: ElementValuesType
): ElementValuesType => {
    return {
        [keyName]: keyValue,
        ...values
    };
};

export const computeInitialValues = <ET extends ElementLikeType>(
    elements: { [name: string]: ET }
): ElementValuesType => {
    const initialValues: ElementValuesType = {};

    Object.entries(elements).forEach(([name, element]) => {
        const value = (element as ElementLikeType & { value?: any }).value;

        switch (element.type) {
            case TYPE_ARRAY:
                if (MODE_SINGLE === (element as ElementLikeType & { mode?: string }).mode) {
                    /* `value` is an array of selected values even in single mode; the field holds the first one */
                    initialValues[name] = undefined === value || null === value || 0 === value.length ? null : value[0];

                    break;
                }

                initialValues[name] = undefined === value || null === value ? [] : value;
                break;
            case TYPE_BOOL:
                initialValues[name] = undefined === value || null === value ? false : value;
                break;
            case TYPE_COLLECTION:
                initialValues[name] = computeInitialValues(
                    requireElementProperty(element, 'elements' as keyof ET) as { [name: string]: ET }
                );
                break;
            case TYPE_PROTOTYPE_COLLECTION:
                initialValues[name] = Object.entries(
                    requireElementProperty(element, 'elements' as keyof ET) as { [key: string]: { [name: string]: ET } }
                ).map(([key, elementsCollection]) => createPrototypeCollectionElementValues(
                    requireElementProperty(element, 'key' as keyof ET) as string,
                    key,
                    computeInitialValues(elementsCollection)
                ));
                break;
            default:
                initialValues[name] = undefined === value || null === value ? '' : value;
        }
    });

    return initialValues;
};
