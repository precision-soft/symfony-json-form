export const clone = <T>(value: T): T => {
    if ('function' === typeof structuredClone) {
        return structuredClone(value);
    }

    return JSON.parse(JSON.stringify(value)) as T;
};
