import {Box, Checkbox, FormControl as FormControlBase, FormControlLabel} from '@mui/material';
import {FastField, FormikValues} from 'formik';
import React from 'react';
import {useLanguageContext} from '../context/LanguageContext';
import Exception from '../exception/Exception';
import {StringArrayType} from '../type/Array';
import {BooleanRefType} from '../type/React';
import {NullableStringType} from '../type/Scalar';
import {AutocompleteField, DateField, DateTimeField, ElementListType, ElementModeEnum, ElementType, ElementTypeEnum, FormBuilder, FormContext, FormFieldRenderPropsType, FormFieldsRenderPropsType, FormFieldValueType, OnChangeCallbackType, SelectField, TextField} from './Form';
import {PrototypeCollectionField} from './PrototypeCollectionField';

type FormControlProps = React.PropsWithChildren & {
    className?: string
    required?: boolean
}

export const FormControl: React.FunctionComponent<FormControlProps> = (props) => {
    return (
        <FormControlBase fullWidth className={props.className} required={props.required}>
            {props.children}
        </FormControlBase>
    );
};

type FormFieldProps = {
    element: ElementType
    value?: FormFieldValueType
    renderProps?: FormFieldRenderPropsType
    parents?: StringArrayType
}

export const FormField: React.FunctionComponent<FormFieldProps> = (props) => {
    const formContext = React.useContext(FormContext);
    const languageContext = useLanguageContext();

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
    const readonly: boolean = (undefined !== props.element.readonly ? props.element.readonly : false) || formContext.form.isSubmitting;
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
            formContext.form.setFieldValue(name, processedValue);
        } else {
            formContext.form.handleChange(event);
        }

        null !== props.renderProps?.onChange && props.renderProps.onChange(
            event,
            processedValue !== undefined ? processedValue : event.target.value
        );
    };
    const error: boolean = true === formContext.form.touched[name] && Boolean(formContext.form.errors[name]);
    const helperText = true === formContext.form.touched[name] && formContext.form.errors[name];
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
            formControlClassNames.push('checkbox-form-control');

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
                                          icon={props.renderProps.checkboxIcon}
                                          checkedIcon={props.renderProps.checkboxCheckedIcon}
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

            return (
                <FormControl key={name}
                             className={formControlClassNames.join(' ')}
                >
                    <Box key={name + 'Collection'} className={'d-flex gap-1 w-100' + elementClassName}>
                        <FormFields elements={props.element.elements as ElementListType}
                                    parents={[...props.parents, props.element.name]}
                                    renderProps={props.renderProps?.elements}
                        />
                    </Box>
                </FormControl>
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
            return (
                <PrototypeCollectionField name={name}
                                          elementClassName={elementClassName}
                                          value={props.value}
                                          element={props.element}
                                          parents={props.parents}
                                          renderProps={props.renderProps}
                />
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
    elements: ElementListType
    renderProps?: FormFieldsRenderPropsType
    parents?: StringArrayType
}

export const FormFields: React.FunctionComponent<FormFieldsProps> = (props) => {
    const formContext = React.useContext(FormContext);

    const parents: StringArrayType = undefined === props.parents ? [] : props.parents;
    const values: FormikValues = FormBuilder.getNestedValuesByPath(formContext.form.values, parents);

    return (
        <React.Fragment>
            {Object.entries(props.elements).map(([, element]) => {
                    return (
                        <FormField key={element.name}
                                   element={element}
                                   value={undefined !== values?.[element.name] ? values?.[element.name] : ''}
                                   renderProps={props.renderProps?.[element.name]}
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
