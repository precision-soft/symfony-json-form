/*
 * Copyright (c) Precision Soft
 */

import React from 'react';
import {Form as FormV1} from '../../../assets/react/formV1/Form';
import {ButtonTypeEnum, Form as FormV2} from '../../../assets/react/formV2/Form';
import type {ButtonListType, FormDataType} from '../../../assets/react/formV2/Form';

type ProductEditorProps = {
    data: FormDataType
    version: 'v1' | 'v2'
};

const buttons: ButtonListType = {
    [ButtonTypeEnum.SUBMIT]: [<React.Fragment>save</React.Fragment>, 'submit'],
    [ButtonTypeEnum.RESET]: [<React.Fragment>reset</React.Fragment>, 'reset']
};

/** the json `ProductEditForm::render()` produced, rendered by either react version; `data` comes untyped from the http layer */
export const ProductEditor: React.FunctionComponent<ProductEditorProps> = (props) => {
    if ('v1' === props.version) {
        return (
            <FormV1 data={props.data}
                    buttons={buttons}
            />
        );
    }

    return (
        <FormV2 data={props.data}
                buttons={buttons}
        />
    );
};
