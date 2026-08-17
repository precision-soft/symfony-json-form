/*
 * Copyright (c) Precision Soft
 */

/** `react` and `JQuery` are spelled out rather than shorthand: a shorthand collapses to `any` and every component's `props` with it. */

declare module '@mui/material' {
    export type InputLabelProps = { [name: string]: any };
    export const Autocomplete: any;
    export const Box: any;
    export const Button: any;
    export const Checkbox: any;
    export const Chip: any;
    export const FormControl: any;
    export const FormControlLabel: any;
    export const InputLabel: any;
    export const ListItemText: any;
    export const ListSubheader: any;
    export const MenuItem: any;
    export const OutlinedInput: any;
    export const Select: any;
    export const TextField: any;
}

declare module '@mui/material/Backdrop';
declare module '@mui/material/CircularProgress';
declare module '@mui/x-date-pickers';
declare module '@mui/x-date-pickers/AdapterDayjs';
declare module 'bazinga-translator';
declare module 'jquery';

/** a type is imported from here, so the exports are declared: a shorthand exports namespaces, unusable in type position. */
declare module 'formik' {
    export type FormikValues = { [name: string]: any };
    export type FormikProps<Values> = { [name: string]: any } & { values: Values };
    export const FastField: any;
    export const FieldArray: any;
    export const Form: any;
    export const Formik: any;

    export function useFormikContext<Values = FormikValues>(): FormikProps<Values>;
}

declare module '@mui/material/Select/SelectInput' {
    export type SelectChangeEvent<Value = any> = { target: { value: Value, name: string } };
}

declare module '@mui/material/Input/Input' {
    export type InputProps = { [name: string]: any };
}

declare module '@mui/material/useAutocomplete' {
    export type AutocompleteValue<T, Multiple, DisableClearable, FreeSolo> = any;
    export type AutocompleteChangeReason = string;
    export type AutocompleteChangeDetails<T> = { option: T };
    export type AutocompleteInputChangeReason = string;
}

declare module '@mui/base/AutocompleteUnstyled/useAutocomplete' {
    export * from '@mui/material/useAutocomplete';
}

/** The test files use node's own runner; the `node:` builtins are not otherwise part of this package. */
declare module 'node:test';
declare module 'node:assert/strict';

declare function require(path: string): any;

declare namespace React {
    type Key = string | number;
    type ReactNode = any;
    type HTMLInputTypeAttribute = string;
    type PropsWithChildren<P = {}> = P & { children?: ReactNode };

    interface ReactElement {
    }

    interface MutableRefObject<T> {
        current: T;
    }

    interface Context<T> {
        Provider: any;
        Consumer: any;
    }

    interface FunctionComponent<P = {}> {
        (props: P, context?: any): ReactElement | null;

        displayName?: string;
    }

    type FC<P = {}> = FunctionComponent<P>;

    interface SyntheticEvent<T = any> {
        target: any;
        currentTarget: T;

        preventDefault(): void;

        stopPropagation(): void;
    }

    interface ChangeEvent<T = any> extends SyntheticEvent<T> {
    }

    const Fragment: any;
    const StrictMode: any;

    function createContext<T>(defaultValue: T): Context<T>;

    function useContext<T>(context: Context<T>): T;

    function useState<T>(initialState: T | (() => T)): [T, (value: T | ((previous: T) => T)) => void];

    function useRef<T>(initialValue: T): MutableRefObject<T>;

    function useEffect(effect: () => void | (() => void), dependencies?: readonly any[]): void;

    function useMemo<T>(factory: () => T, dependencies?: readonly any[]): T;

    function useCallback<T>(callback: T, dependencies?: readonly any[]): T;

    function createElement(...args: any[]): ReactElement;
}

declare module 'react' {
    export = React;
}

/** `responseJSON` is deliberately optional: jQuery only sets it when the body parses as json, which is the distinction `HttpClient` depends on. */
declare namespace JQuery {
    interface jqXHR {
        responseJSON?: { [key: string]: any };
        status: number;
        statusText: string;

        abort(statusText?: string): void;

        getResponseHeader(name: string): string | null;
    }

    interface AjaxSettings {
        [key: string]: any;
    }
}

declare namespace JSX {
    interface Element extends React.ReactElement {
    }

    interface ElementClass {
    }

    /** `key` and `ref` are consumed by react itself and never reach the component's own props. */
    interface IntrinsicAttributes {
        key?: React.Key;
    }

    interface IntrinsicClassAttributes<T> {
        ref?: any;
    }

    interface ElementAttributesProperty {
        props: {};
    }

    interface ElementChildrenAttribute {
        children: {};
    }

    interface IntrinsicElements {
        [name: string]: any;
    }
}
