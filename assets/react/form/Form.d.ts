/*
 * Copyright (c) Precision Soft
 */

/** the host re-exports whichever form version it uses, so `service/HttpClient.ts` stays version agnostic. */

export type FormDataType = {
    name: string
    method: string
    action: {
        route: string
        parameters: { [name: string]: any } | null
    }
    elements: { [name: string]: any }
};
