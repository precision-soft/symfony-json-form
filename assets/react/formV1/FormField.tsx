import {Box, Checkbox, FormControlLabel} from '@mui/material';
import {FastField, FieldArray, FormikValues} from 'formik';
import React from 'react';
import LanguageContext from '../context/LanguageContext';
import Exception from '../exception/Exception';
import {StringArrayType} from '../type/Array';
import {BooleanRefType} from '../type/React';
import {NullableNumberType, NullableStringType} from '../type/Scalar';
import {AutocompleteField} from './AutocompleteField';
import {DateField} from './DateField';
import {DateTimeField} from './DateTimeField';
import {FormBuilder} from './FormBuilder';
import {FormControl} from './FormControl';
import {SelectField} from './SelectField';
import {TextField} from './TextField';
import {ElementListType, ElementModeEnum, ElementType, ElementTypeEnum, FormFieldCallbacksType, FormFieldRenderPropsType, FormFieldsCallbacksType, FormFieldsRenderPropsType, FormFieldValueType, FormType, OnChangeCallbackType} from './Types';

type FormFieldProps = {
    form: FormType
    element: ElementType
    value?: FormFieldValueType
    renderProps?: FormFieldRenderPropsType
    callbacks?: FormFieldCallbacksType
    parents?: StringArrayType
}

export const FormField: React.FunctionComponent<FormFieldProps> = (props) => {
    const languageContext = React.useContext(LanguageContext);
    const defaultAutoFocus = React.useRef<boolean>(false);

    const buildName = (element: ElementType, parents: StringArrayType): string => {
        const prefix = undefined === parents ? [] : parents;

        return [...prefix, element.name].join('.');
    };

    const formControlClassNames: StringArrayType = ['form-control'];
    if (undefined !== props.renderProps?.formControlClassName) {
        formControlClassNames.push(props.renderProps?.formControlClassName);
    }

    const name: string = buildName(props.element, props.parents);
    const label: NullableStringType = null !== props.element.label ? languageContext.translate(props.element.label) : null;
    const readonly: boolean = (undefined !== props.element.readonly ? props.element.readonly : false) || props.form.isSubmitting;
    const required: boolean = undefined !== props.element.required ? props.element.required : false;
    const autoFocus: BooleanRefType = undefined !== props.renderProps?.autoFocus ? props.renderProps.autoFocus : defaultAutoFocus;
    const selectOnFocus: boolean = undefined !== props.renderProps?.selectOnFocus ? props.renderProps.selectOnFocus : false;
    const onChange: OnChangeCallbackType = (event, value) => {
        let processedValue = undefined;

        switch (props.element.type) {
            case ElementTypeEnum.ARRAY:
                switch (props.element.mode) {
                    case ElementModeEnum.SINGLE:
                        processedValue = null !== value ? value.id : null;
                        break;
                }
                break;
            case ElementTypeEnum.AUTOCOMPLETE:
                processedValue = [];

                if (null !== value) {
                    if (undefined !== value.id) {
                        processedValue.push(value.id);
                    } else if (Array.isArray(value) === true) {
                        value.map(selectedOption => processedValue.push(selectedOption.id));
                    } else {
                        throw new Exception(`invalid value for "${name}"`);
                    }
                }
                break;
            case ElementTypeEnum.DATE:
                const dateFormat = FormBuilder.computeDateFormat(props.element.format);

                processedValue = event?.format(dateFormat);
                break;
            case ElementTypeEnum.DATE_TIME:
                const dateTimeFormat = FormBuilder.computeDateTimeFormat(props.element.format);

                processedValue = event?.format(dateTimeFormat);
                break;
        }

        if (undefined !== processedValue) {
            props.form.setFieldValue(name, processedValue);
        } else {
            props.form.handleChange(event);
        }

        null !== props.renderProps?.onChange && props.renderProps.onChange(
            event,
            processedValue !== undefined ? processedValue : event.target.value
        );
    };
    const error: boolean = true === props.form.touched[name] && Boolean(props.form.errors[name]);
    const helperText = true === props.form.touched[name] && props.form.errors[name];
    const elementClassName: string = undefined !== props.renderProps?.className ? ' ' + props.renderProps.className : '';

    switch (props.element.type) {
        case ElementTypeEnum.ARRAY:
            return (
                <FastField name={name}>
                    {() => (
                        <FormControl key={name}
                                     required={required}
                                     className={formControlClassNames.join(' ')}
                        >
                            <SelectField name={name}
                                         label={label}
                                         options={props.element.options}
                                         value={props.value}
                                         mode={props.element.mode}
                                         required={required}
                                         readonly={readonly}
                                         autoFocus={autoFocus}
                                         onChange={onChange}
                            />
                        </FormControl>
                    )}
                </FastField>
            );
        case ElementTypeEnum.AUTOCOMPLETE:
            return (
                <FastField name={name}>
                    {() => (
                        <FormControl key={name}
                                     required={required}
                                     className={formControlClassNames.join(' ')}
                        >
                            <AutocompleteField name={name}
                                               label={label}
                                               value={props.value}
                                               route={props.element.route}
                                               routeParameter={props.element.parameter}
                                               mode={props.element.mode}
                                               required={required}
                                               readonly={readonly}
                                               autoFocus={autoFocus}
                                               onChange={onChange}
                            />
                        </FormControl>
                    )}
                </FastField>
            );
        case ElementTypeEnum.BOOL:
            return (
                <FastField name={name}>
                    {() => (
                        <FormControl key={name}
                                     required={required}
                                     className={formControlClassNames.join(' ')}
                        >
                            <FormControlLabel control={
                                <Checkbox id={name}
                                          name={name}
                                          checked={props.value as boolean}
                                          required={required}
                                          readOnly={readonly}
                                          onChange={onChange}
                                          inputProps={{'aria-label': 'controlled'}}
                                />
                            }
                                              label={label}
                            />
                        </FormControl>
                    )}
                </FastField>
            );
        case ElementTypeEnum.COLLECTION:
            formControlClassNames.push('col-10');

            if (undefined !== props.callbacks) {
                props.callbacks.elements = {};
            }

            return (
                <FastField name={name}>
                    {() => (
                        <Box key={name + 'Collection'} className={'d-flex gap-1 w-100' + elementClassName}>
                            <FormFields form={props.form}
                                        elements={props.element.elements as ElementListType}
                                        parents={[...props.parents, props.element.name]}
                                        renderProps={props.renderProps?.elements}
                                        callbacks={props.callbacks?.elements}
                            />
                        </Box>
                    )}
                </FastField>
            );
        case ElementTypeEnum.DATE:
            return (
                <FastField name={name}>
                    {() => (
                        <FormControl key={name}
                                     required={required}
                                     className={formControlClassNames.join(' ')}
                        >
                            <DateField label={label}
                                       format={props.element.format}
                                       value={props.value}
                                       onChange={onChange}
                                       min={props.element.min}
                                       max={props.element.max}
                                       readonly={readonly}
                                       autoFocus={autoFocus}
                                       name={name}
                                       required={required}
                            />
                        </FormControl>
                    )}
                </FastField>
            );
        case ElementTypeEnum.DATE_TIME:
            return (
                <FastField name={name}>
                    {() => (
                        <FormControl key={name}
                                     required={required}
                                     className={formControlClassNames.join(' ')}
                        >
                            <DateTimeField label={label}
                                           format={props.element.format}
                                           value={props.value}
                                           onChange={onChange}
                                           min={props.element.min}
                                           max={props.element.max}
                                           readonly={readonly}
                                           autoFocus={autoFocus}
                                           name={name}
                                           required={required}
                            />
                        </FormControl>
                    )}
                </FastField>
            );
        case ElementTypeEnum.FILE:
            return (
                <FastField name={name}>
                    {() => (
                        <FormControl key={name}
                                     required={required}
                                     className={formControlClassNames.join(' ')}
                        >
                            <TextField type="file"
                                       name={name}
                                       label={label}
                                       value={props.value}
                                       required={required}
                                       readonly={readonly}
                                       autoFocus={autoFocus}
                                       selectOnFocus={selectOnFocus}
                                       onChange={onChange}
                                       error={error}
                                       helperText={helperText}
                                       inputLabelProps={{shrink: true}}
                            />
                        </FormControl>
                    )}
                </FastField>
            );
        case ElementTypeEnum.HIDDEN:
            formControlClassNames.push('hidden');

            return (
                <FastField name={name}>
                    {() => (
                        <FormControl key={name}
                                     required={required}
                                     className={formControlClassNames.join(' ')}
                        >
                            <TextField type="hidden"
                                       name={name}
                                       label={label}
                                       value={props.value}
                                       required={required}
                                       readonly={readonly}
                                       autoFocus={autoFocus}
                                       selectOnFocus={selectOnFocus}
                                       onChange={onChange}
                                       error={error}
                                       helperText={helperText}
                            />
                        </FormControl>
                    )}
                </FastField>
            );
        case ElementTypeEnum.LABEL:
            return (
                <FastField name={name}>
                    {() => (
                        <FormControl key={name}
                                     required={required}
                                     className={formControlClassNames.join(' ')}
                        >
                            {null !== props.value ? props.value : label}
                        </FormControl>
                    )}
                </FastField>
            );
        case ElementTypeEnum.NUMBER:
            return (
                <FastField name={name}>
                    {() => (
                        <FormControl key={name}
                                     required={required}
                                     className={formControlClassNames.join(' ')}
                        >
                            <TextField type="number"
                                       name={name}
                                       label={label}
                                       value={props.value}
                                       required={required}
                                       readonly={readonly}
                                       autoFocus={autoFocus}
                                       selectOnFocus={selectOnFocus}
                                       onChange={onChange}
                                       error={error}
                                       helperText={helperText}
                                       inputProps={{inputProps: {min: props.element.min, max: props.element.max, step: props.element.step}}}
                            />
                        </FormControl>
                    )}
                </FastField>
            );
        case ElementTypeEnum.PASSWORD:
            return (
                <FastField name={name}>
                    {() => (
                        <FormControl key={name}
                                     required={required}
                                     className={formControlClassNames.join(' ')}
                        >
                            <TextField type="password"
                                       name={name}
                                       label={label}
                                       value={props.value}
                                       required={required}
                                       readonly={readonly}
                                       autoFocus={autoFocus}
                                       selectOnFocus={selectOnFocus}
                                       onChange={onChange}
                                       error={error}
                                       helperText={helperText}
                            />
                        </FormControl>
                    )}
                </FastField>
            );
        case ElementTypeEnum.PROTOTYPE_COLLECTION:
            const renderPrototypeCollection = (arrayHelpers) => {
                if (undefined !== props.callbacks) {
                    const findByKey = (key: unknown): [NullableNumberType, FormikValues | null] => {
                        for (const [index, elementValues] of props.value.entries()) {
                            if (elementValues[props.element.key] === key) {
                                return [index, elementValues];
                            }
                        }

                        return [null, null];
                    };

                    props.callbacks.remove = (key): void => {
                        const [index] = findByKey(key);

                        if (index === null) {
                            return null;
                        }

                        arrayHelpers.remove(index);
                    };

                    props.callbacks.get = (key) => {
                        const [unusedIndex, elementValues] = findByKey(key);

                        return elementValues;
                    };

                    props.callbacks.set = (key, values): void => {
                        const [index] = findByKey(key);

                        const elementValues = FormBuilder.createPrototypeCollectionElementValues(
                            props.element.key,
                            key,
                            {...values}
                        );

                        if (index !== null) {
                            arrayHelpers.replace(index, elementValues);
                        } else {
                            arrayHelpers.push(elementValues);
                        }
                    };

                    props.callbacks.elements = {};
                }

                return FormBuilder.renderPrototypeCollection(
                    name,
                    elementClassName,
                    props.value,
                    props.form,
                    props.element,
                    props.parents,
                    props.renderProps,
                    props.callbacks
                );
            };

            return (
                <FastField name={name}>
                    {() => (
                        <FieldArray name={props.element.name}
                                    render={renderPrototypeCollection}
                        />
                    )}
                </FastField>
            );
        case ElementTypeEnum.STRING:
            return (
                <FastField name={name}>
                    {() => (
                        <FormControl key={name}
                                     required={required}
                                     className={formControlClassNames.join(' ')}
                        >
                            <TextField type="text"
                                       name={name}
                                       label={label}
                                       value={props.value}
                                       required={required}
                                       readonly={readonly}
                                       autoFocus={autoFocus}
                                       selectOnFocus={selectOnFocus}
                                       onChange={onChange}
                                       error={error}
                                       helperText={helperText}
                            />
                        </FormControl>
                    )}
                </FastField>
            );
        default:
            throw new Exception(`invalid form element type "${props.element.type}" for "${name}"`);
    }
};

type FormFieldsProps = {
    form: FormType
    elements: ElementListType
    renderProps?: FormFieldsRenderPropsType
    callbacks?: FormFieldsCallbacksType
    parents?: StringArrayType
}

export const FormFields: React.FunctionComponent<FormFieldsProps> = (props) => {
    const parents: StringArrayType = undefined === props.parents ? [] : props.parents;
    const values: FormikValues = FormBuilder.getNestedValuesByPath(props.form.values, parents);

    return (
        <React.Fragment>
            {Object.entries(props.elements).map(([, element]) => {
                    if (undefined !== props.callbacks) {
                        props.callbacks[element.name] = {};
                    }

                    return (
                        <FormField key={element.name}
                                   form={props.form}
                                   element={element}
                                   value={undefined !== values?.[element.name] ? values?.[element.name] : ''}
                                   renderProps={props.renderProps?.[element.name]}
                                   callbacks={props.callbacks?.[element.name]}
                                   parents={parents}
                        />
                    );
                }
            )}
        </React.Fragment>
    );
};

type FormFieldsContainerProps = React.PropsWithChildren & {
    className?: string
}

export const FormFieldsContainer: React.FunctionComponent<FormFieldsContainerProps> = (props) => {
    const classNames: StringArrayType = ['form-fields-container', 'd-flex flex-wrap gap-1 align-items-center'];
    if (undefined !== props.className) {
        classNames.push(props.className);
    }

    return (
        <Box className={classNames.join(' ')}>
            {props.children}
        </Box>
    );
};