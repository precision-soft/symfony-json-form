import {TextField as TextFieldBase} from '@mui/material';
import type {InputLabelProps} from '@mui/material';
import type {InputProps as StandardInputProps} from '@mui/material/Input/Input';
import React from 'react';
import type {HTMLInputTypeAttribute} from 'react';
import type {FieldType} from './Form';

type TextFieldProps = FieldType & {
    type: HTMLInputTypeAttribute
    error: boolean
    helperText: any
    inputProps?: StandardInputProps
    inputLabelProps?: InputLabelProps
}

export const TextField: React.FunctionComponent<TextFieldProps> = (props) => {
    const inputRef = React.useRef<any>(null);

    React.useEffect(() => {
        const autoFocus = props.autoFocus;

        if (inputRef.current !== null && undefined !== autoFocus && autoFocus.current === true) {
            if (0 < props.value.length && true === props.selectOnFocus) {
                inputRef.current.select();
            } else {
                inputRef.current.focus();
            }

            autoFocus.current = false;
        }
    });

    return (
        <TextFieldBase type={props.type}
                       id={props.name}
                       name={props.name}
                       label={props.label}
                       value={props.value}
                       required={props.required}
                       aria-readonly={props.readonly}
                       InputProps={{
                           readOnly: props.readonly,
                           ...props.inputProps
                       }}
                       onChange={props.onChange}
                       error={props.error}
                       helperText={props.helperText}
                       InputLabelProps={props.inputLabelProps}
                       inputRef={inputRef}
        />
    );
};
